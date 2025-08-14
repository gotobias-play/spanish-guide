<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'room_id',
        'message_type',
        'message',
        'metadata',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->is_read = true;
            $this->read_at = now();
            $this->save();
        }
    }

    /**
     * Get conversation between two users
     */
    public static function getConversation(int $userId1, int $userId2, int $limit = 50)
    {
        return self::where(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })
        ->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)->where('receiver_id', $userId1);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get()
        ->reverse()
        ->values();
    }

    /**
     * Get room messages
     */
    public static function getRoomMessages(string $roomId, int $limit = 50)
    {
        return self::where('room_id', $roomId)
            ->where('message_type', 'room')
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(int $userId): int
    {
        return self::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get user's conversations with last message
     */
    public static function getUserConversations(int $userId)
    {
        return self::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
        })
        ->where('message_type', 'direct')
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy(function ($message) use ($userId) {
            // Group by the other user in the conversation
            return $message->sender_id === $userId ? $message->receiver_id : $message->sender_id;
        })
        ->map(function ($messages) {
            return $messages->first(); // Get the latest message in each conversation
        })
        ->values();
    }
}
