<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    protected $fillable = [
        'requester_id',
        'addressee_id', 
        'status',
        'requested_at',
        'responded_at',
        'friendship_data',
    ];

    protected $casts = [
        'friendship_data' => 'array',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    /**
     * Accept the friendship request
     */
    public function accept(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'accepted';
        $this->responded_at = now();
        
        return $this->save();
    }

    /**
     * Decline the friendship request
     */
    public function decline(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        // We'll delete declined requests to keep the table clean
        return $this->delete();
    }

    /**
     * Block the user
     */
    public function block(): bool
    {
        $this->status = 'blocked';
        $this->responded_at = now();
        
        return $this->save();
    }

    /**
     * Get the "other" user in the friendship (not the current user)
     */
    public function getOtherUser(User $currentUser): ?User
    {
        if ($this->requester_id === $currentUser->id) {
            return $this->addressee;
        } elseif ($this->addressee_id === $currentUser->id) {
            return $this->requester;
        }
        
        return null;
    }

    /**
     * Check if a friendship exists between two users
     */
    public static function existsBetween(User $user1, User $user2): bool
    {
        return self::where(function ($query) use ($user1, $user2) {
            $query->where('requester_id', $user1->id)
                  ->where('addressee_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('requester_id', $user2->id)
                  ->where('addressee_id', $user1->id);
        })->exists();
    }

    /**
     * Get friendship between two users
     */
    public static function findBetween(User $user1, User $user2): ?self
    {
        return self::where(function ($query) use ($user1, $user2) {
            $query->where('requester_id', $user1->id)
                  ->where('addressee_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('requester_id', $user2->id)
                  ->where('addressee_id', $user1->id);
        })->first();
    }
}