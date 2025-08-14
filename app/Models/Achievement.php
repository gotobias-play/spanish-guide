<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'badge_icon',
        'badge_color',
        'points_required',
        'condition_type',
        'condition_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'condition_value' => 'json',
    ];

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function isEarnedBy(User $user): bool
    {
        return $this->userAchievements()
            ->where('user_id', $user->id)
            ->exists();
    }
}