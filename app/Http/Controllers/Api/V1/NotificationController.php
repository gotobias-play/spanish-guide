<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $subscription = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string'
        ]);

        // Store subscription in database (you may want to create a push_subscriptions table)
        $user->update([
            'push_subscription' => json_encode($subscription)
        ]);

        return response()->json(['success' => true, 'message' => 'Subscribed to notifications']);
    }

    /**
     * Send a test notification
     */
    public function sendTest(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->push_subscription) {
            return response()->json(['error' => 'No subscription found'], 404);
        }

        $this->sendPushNotification($user, [
            'title' => '🎉 ¡Notificación de prueba!',
            'body' => 'Las notificaciones push están funcionando correctamente.',
            'icon' => '/images/icon-192x192.png',
            'badge' => '/images/badge-72x72.png',
            'data' => [
                'url' => '/gamification',
                'type' => 'test'
            ]
        ]);

        return response()->json(['success' => true, 'message' => 'Test notification sent']);
    }

    /**
     * Send daily reminder notifications
     */
    public function sendDailyReminder()
    {
        $users = DB::table('users')
            ->whereNotNull('push_subscription')
            ->whereDate('updated_at', '<', now()->subDays(1))
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $this->sendPushNotification($user, [
                'title' => '📚 ¡Es hora de practicar inglés!',
                'body' => 'No olvides continuar con tu aprendizaje diario. ¡Tu racha te está esperando!',
                'icon' => '/images/icon-192x192.png',
                'badge' => '/images/badge-72x72.png',
                'data' => [
                    'url' => '/quiz-selector',
                    'type' => 'daily_reminder'
                ]
            ]);
            $count++;
        }

        return response()->json([
            'success' => true, 
            'message' => "Daily reminders sent to {$count} users"
        ]);
    }

    /**
     * Send achievement notification
     */
    public function sendAchievementNotification($userId, $achievement)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user || !$user->push_subscription) {
            return false;
        }

        $this->sendPushNotification($user, [
            'title' => '🏆 ¡Nuevo logro desbloqueado!',
            'body' => "Has conseguido: {$achievement['name']}",
            'icon' => '/images/icon-192x192.png',
            'badge' => '/images/badge-72x72.png',
            'data' => [
                'url' => '/gamification',
                'type' => 'achievement',
                'achievementId' => $achievement['id']
            ]
        ]);

        return true;
    }

    /**
     * Send quiz completion milestone notification
     */
    public function sendMilestoneNotification($userId, $milestone)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user || !$user->push_subscription) {
            return false;
        }

        $this->sendPushNotification($user, [
            'title' => '🎯 ¡Hito alcanzado!',
            'body' => "Has completado {$milestone} quizzes. ¡Sigue así!",
            'icon' => '/images/icon-192x192.png',
            'badge' => '/images/badge-72x72.png',
            'data' => [
                'url' => '/analytics',
                'type' => 'milestone'
            ]
        ]);

        return true;
    }

    /**
     * Send streak reminder notification
     */
    public function sendStreakReminder($userId, $streak)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user || !$user->push_subscription) {
            return false;
        }

        $this->sendPushNotification($user, [
            'title' => '🔥 ¡Mantén tu racha!',
            'body' => "Llevas {$streak} días consecutivos. ¡No la pierdas hoy!",
            'icon' => '/images/icon-192x192.png',
            'badge' => '/images/badge-72x72.png',
            'data' => [
                'url' => '/quiz-selector',
                'type' => 'streak_reminder'
            ]
        ]);

        return true;
    }

    /**
     * Send social activity notification
     */
    public function sendSocialNotification($userId, $activity)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user || !$user->push_subscription) {
            return false;
        }

        $messages = [
            'friend_request' => '👥 Tienes una nueva solicitud de amistad',
            'friend_accepted' => '🤝 Tu solicitud de amistad fue aceptada',
            'challenge_received' => '⚔️ Te han retado a una competencia',
            'achievement_shared' => '🏆 Un amigo ha conseguido un nuevo logro',
            'message_received' => '💬 Tienes un nuevo mensaje'
        ];

        $this->sendPushNotification($user, [
            'title' => '🌟 Actividad social',
            'body' => $messages[$activity['type']] ?? 'Tienes nueva actividad social',
            'icon' => '/images/icon-192x192.png',
            'badge' => '/images/badge-72x72.png',
            'data' => [
                'url' => '/social',
                'type' => 'social',
                'activityType' => $activity['type']
            ]
        ]);

        return true;
    }

    /**
     * Send new content notification
     */
    public function sendNewContentNotification($contentType, $contentTitle)
    {
        $users = DB::table('users')
            ->whereNotNull('push_subscription')
            ->get();

        $urls = [
            'video' => '/video-lessons',
            'conversation' => '/conversation-practice',
            'pronunciation' => '/pronunciation-practice',
            'listening' => '/listening-comprehension'
        ];

        $icons = [
            'video' => '🎬',
            'conversation' => '💬',
            'pronunciation' => '🎤',
            'listening' => '🎧'
        ];

        $count = 0;
        foreach ($users as $user) {
            $this->sendPushNotification($user, [
                'title' => $icons[$contentType] . ' ¡Nuevo contenido disponible!',
                'body' => "Se ha añadido: {$contentTitle}",
                'icon' => '/images/icon-192x192.png',
                'badge' => '/images/badge-72x72.png',
                'data' => [
                    'url' => $urls[$contentType] ?? '/',
                    'type' => 'new_content',
                    'contentType' => $contentType
                ]
            ]);
            $count++;
        }

        return response()->json([
            'success' => true, 
            'message' => "New content notification sent to {$count} users"
        ]);
    }

    /**
     * Unsubscribe from notifications
     */
    public function unsubscribe(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $user->update(['push_subscription' => null]);

        return response()->json(['success' => true, 'message' => 'Unsubscribed from notifications']);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $preferences = json_decode($user->notification_preferences ?? '{}', true);
        
        // Default preferences
        $defaultPreferences = [
            'daily_reminders' => true,
            'achievements' => true,
            'social_activities' => true,
            'new_content' => true,
            'streak_reminders' => true,
            'milestones' => true
        ];

        return response()->json(array_merge($defaultPreferences, $preferences));
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $preferences = $request->validate([
            'daily_reminders' => 'boolean',
            'achievements' => 'boolean',
            'social_activities' => 'boolean',
            'new_content' => 'boolean',
            'streak_reminders' => 'boolean',
            'milestones' => 'boolean'
        ]);

        $user->update(['notification_preferences' => json_encode($preferences)]);

        return response()->json(['success' => true, 'preferences' => $preferences]);
    }

    /**
     * Send push notification using Web Push
     */
    private function sendPushNotification($user, $payload)
    {
        try {
            $subscription = json_decode($user->push_subscription, true);
            
            if (!$subscription) {
                return false;
            }

            // In a real implementation, you would use a package like minishlink/web-push
            // For this simulation, we'll just log the notification
            \Log::info('Push notification sent', [
                'user_id' => $user->id,
                'payload' => $payload,
                'endpoint' => $subscription['endpoint']
            ]);

            // Simulate successful sending
            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to send push notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
