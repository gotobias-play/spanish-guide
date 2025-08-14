<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Friendship;
use App\Models\Challenge;
use App\Models\SocialActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SocialController extends Controller
{
    /**
     * Get social dashboard data for user
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $friends = $user->getFriends();
        $pendingRequests = $user->getPendingFriendRequests();
        $recentActivities = SocialActivity::getUserActivities($user, 5);
        $socialFeed = SocialActivity::getFeedForUser($user, 10);

        return response()->json([
            'success' => true,
            'data' => [
                'friends_count' => $friends->count(),
                'friends' => $friends->map(function ($friend) use ($user) {
                    return [
                        'id' => $friend->id,
                        'name' => $friend->name,
                        'total_points' => $friend->getTotalPoints(),
                        'current_streak' => $friend->getCurrentStreak(),
                        'certificates_count' => $friend->certificates()->count(),
                        'is_friend' => true,
                    ];
                }),
                'pending_requests_count' => $pendingRequests->count(),
                'pending_requests' => $pendingRequests->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'requester' => [
                            'id' => $request->requester->id,
                            'name' => $request->requester->name,
                            'total_points' => $request->requester->getTotalPoints(),
                        ],
                        'requested_at' => $request->requested_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'recent_activities' => $recentActivities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'type' => $activity->activity_type,
                        'icon' => $activity->icon,
                        'color' => $activity->color,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'time_ago' => $activity->time_ago,
                        'data' => $activity->activity_data,
                    ];
                }),
                'social_feed' => $socialFeed->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'user' => [
                            'id' => $activity->user->id,
                            'name' => $activity->user->name,
                        ],
                        'type' => $activity->activity_type,
                        'icon' => $activity->icon,
                        'color' => $activity->color,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'time_ago' => $activity->time_ago,
                        'data' => $activity->activity_data,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Search for users to add as friends
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $user = $request->user();
        $query = $request->input('query');

        $users = User::where('name', 'like', "%{$query}%")
            ->where('id', '!=', $user->id)
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($foundUser) use ($user) {
                $friendship = Friendship::findBetween($user, $foundUser);
                
                return [
                    'id' => $foundUser->id,
                    'name' => $foundUser->name,
                    'total_points' => $foundUser->getTotalPoints(),
                    'certificates_count' => $foundUser->certificates()->count(),
                    'friendship_status' => $friendship ? $friendship->status : null,
                    'can_send_request' => !$friendship,
                ];
            }),
        ]);
    }

    /**
     * Send friend request
     */
    public function sendFriendRequest(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $targetUser = User::findOrFail($request->input('user_id'));

        $friendship = $user->sendFriendRequest($targetUser);

        if (!$friendship) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar la solicitud de amistad.',
            ], 400);
        }

        // Create social activity
        SocialActivity::create_activity(
            $user,
            'friend_added',
            "envió solicitud de amistad a {$targetUser->name}",
            null,
            ['friend_id' => $targetUser->id],
            false // Private activity
        );

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de amistad enviada.',
            'data' => [
                'friendship_id' => $friendship->id,
                'status' => $friendship->status,
            ],
        ]);
    }

    /**
     * Respond to friend request
     */
    public function respondToFriendRequest(Request $request, Friendship $friendship): JsonResponse
    {
        $request->validate([
            'action' => ['required', Rule::in(['accept', 'decline'])],
        ]);

        $user = $request->user();

        // Ensure user is the addressee
        if ($friendship->addressee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para responder a esta solicitud.',
            ], 403);
        }

        $action = $request->input('action');
        $success = false;

        if ($action === 'accept') {
            $success = $friendship->accept();
            $message = 'Solicitud de amistad aceptada.';
            
            // Create social activities for both users
            if ($success) {
                SocialActivity::create_activity(
                    $user,
                    'friend_added',
                    "ahora es amigo/a de {$friendship->requester->name}",
                    null,
                    ['friend_id' => $friendship->requester->id]
                );
                
                SocialActivity::create_activity(
                    $friendship->requester,
                    'friend_added',
                    "ahora es amigo/a de {$user->name}",
                    null,
                    ['friend_id' => $user->id]
                );
            }
        } else {
            $success = $friendship->decline();
            $message = 'Solicitud de amistad rechazada.';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Remove friend
     */
    public function removeFriend(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $targetUserId = $request->input('user_id');
        $targetUser = User::findOrFail($targetUserId);

        $friendship = Friendship::findBetween($user, $targetUser);

        if (!$friendship || $friendship->status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => 'No hay una amistad activa con este usuario.',
            ], 400);
        }

        $friendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Amistad eliminada.',
        ]);
    }

    /**
     * Get friend leaderboard
     */
    public function friendLeaderboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $friendIds = $user->getFriendIds();
        
        if (empty($friendIds)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $friends = User::whereIn('id', $friendIds)
            ->get()
            ->map(function ($friend) {
                return [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'total_points' => $friend->getTotalPoints(),
                    'current_streak' => $friend->getCurrentStreak(),
                    'certificates_count' => $friend->certificates()->count(),
                    'achievements_count' => $friend->achievements()->count(),
                ];
            })
            ->sortByDesc('total_points')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $friends,
        ]);
    }
}