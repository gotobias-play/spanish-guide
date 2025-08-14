<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\UserOnlineStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'room_id' => 'nullable|string',
            'message_type' => ['required', Rule::in(['direct', 'room', 'system'])],
            'message' => 'required|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        // Validate message type requirements
        if ($validatedData['message_type'] === 'direct' && !$validatedData['receiver_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Receiver ID is required for direct messages',
            ], 400);
        }

        if ($validatedData['message_type'] === 'room' && !$validatedData['room_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Room ID is required for room messages',
            ], 400);
        }

        try {
            // Create message
            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $validatedData['receiver_id'] ?? null,
                'room_id' => $validatedData['room_id'] ?? null,
                'message_type' => $validatedData['message_type'],
                'message' => $validatedData['message'],
                'metadata' => $validatedData['metadata'] ?? null,
            ]);

            // Load relationships for broadcasting
            $message->load(['sender', 'receiver']);

            // Broadcast the message
            broadcast(new MessageSent($message));

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message sent successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversation between current user and another user
     */
    public function getConversation(Request $request, int $userId): JsonResponse
    {
        $currentUser = Auth::user();

        try {
            $messages = Message::getConversation($currentUser->id, $userId);

            // Mark messages as read
            Message::where('sender_id', $userId)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => $messages,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching conversation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get room messages
     */
    public function getRoomMessages(Request $request, string $roomId): JsonResponse
    {
        try {
            $messages = Message::getRoomMessages($roomId);

            return response()->json([
                'success' => true,
                'data' => $messages,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching room messages',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $conversations = Message::getUserConversations($user->id);

            // Add unread count for each conversation
            $conversationsWithUnread = $conversations->map(function ($message) use ($user) {
                $otherUserId = $message->sender_id === $user->id 
                    ? $message->receiver_id 
                    : $message->sender_id;

                $unreadCount = Message::where('sender_id', $otherUserId)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                $otherUser = $message->sender_id === $user->id 
                    ? $message->receiver 
                    : $message->sender;

                return [
                    'other_user' => $otherUser,
                    'last_message' => $message,
                    'unread_count' => $unreadCount,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $conversationsWithUnread,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching conversations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, int $messageId): JsonResponse
    {
        $user = Auth::user();

        try {
            $message = Message::where('id', $messageId)
                ->where('receiver_id', $user->id)
                ->firstOrFail();

            $message->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking message as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $count = Message::getUnreadCount($user->id);

            return response()->json([
                'success' => true,
                'data' => ['unread_count' => $count],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching unread count',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user online status
     */
    public function updateOnlineStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        $isOnline = $request->boolean('is_online', true);

        try {
            // Update user's last seen timestamp
            $user->update(['last_seen_at' => now()]);

            // Broadcast status change
            broadcast(new UserOnlineStatusChanged($user, $isOnline));

            return response()->json([
                'success' => true,
                'message' => 'Online status updated',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating online status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Join a chat room
     */
    public function joinRoom(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'room_id' => 'required|string',
        ]);

        try {
            // Create system message for room join
            $message = Message::create([
                'sender_id' => $user->id,
                'room_id' => $validatedData['room_id'],
                'message_type' => 'system',
                'message' => "{$user->name} se unió al chat",
                'metadata' => ['action' => 'user_joined'],
            ]);

            $message->load(['sender']);

            // Broadcast join message
            broadcast(new MessageSent($message));

            return response()->json([
                'success' => true,
                'message' => 'Joined room successfully',
                'data' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error joining room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Leave a chat room
     */
    public function leaveRoom(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'room_id' => 'required|string',
        ]);

        try {
            // Create system message for room leave
            $message = Message::create([
                'sender_id' => $user->id,
                'room_id' => $validatedData['room_id'],
                'message_type' => 'system',
                'message' => "{$user->name} salió del chat",
                'metadata' => ['action' => 'user_left'],
            ]);

            $message->load(['sender']);

            // Broadcast leave message
            broadcast(new MessageSent($message));

            return response()->json([
                'success' => true,
                'message' => 'Left room successfully',
                'data' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error leaving room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}