<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Challenge extends Model
{
    protected $fillable = [
        'challenger_id',
        'challenged_id',
        'challenge_type',
        'status',
        'title',
        'description',
        'challenge_config',
        'challenger_result',
        'challenged_result',
        'winner_id',
        'expires_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'challenge_config' => 'array',
        'challenger_result' => 'array',
        'challenged_result' => 'array',
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function challenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    public function challenged(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenged_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Accept the challenge
     */
    public function accept(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'accepted';
        $this->started_at = now();
        
        return $this->save();
    }

    /**
     * Decline the challenge
     */
    public function decline(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'declined';
        return $this->save();
    }

    /**
     * Record a participant's result
     */
    public function recordResult(User $user, array $result): bool
    {
        if ($user->id === $this->challenger_id) {
            $this->challenger_result = $result;
        } elseif ($user->id === $this->challenged_id) {
            $this->challenged_result = $result;
        } else {
            return false; // User not part of this challenge
        }

        // Check if challenge is complete
        if ($this->challenger_result && $this->challenged_result) {
            $this->determineWinner();
        }

        return $this->save();
    }

    /**
     * Determine the winner based on challenge type and results
     */
    protected function determineWinner(): void
    {
        if (!$this->challenger_result || !$this->challenged_result) {
            return;
        }

        $challengerScore = $this->getScoreFromResult($this->challenger_result);
        $challengedScore = $this->getScoreFromResult($this->challenged_result);

        if ($challengerScore > $challengedScore) {
            $this->winner_id = $this->challenger_id;
        } elseif ($challengedScore > $challengerScore) {
            $this->winner_id = $this->challenged_id;
        }
        // If tied, winner_id remains null

        $this->status = 'completed';
        $this->completed_at = now();
    }

    /**
     * Extract score from result based on challenge type
     */
    protected function getScoreFromResult(array $result): float
    {
        switch ($this->challenge_type) {
            case 'quiz_duel':
                return $result['score'] ?? 0;
                
            case 'points_race':
                return $result['points'] ?? 0;
                
            case 'streak_competition':
                return $result['streak_days'] ?? 0;
                
            case 'course_race':
                // First to complete wins, or higher completion percentage
                return $result['completion_percentage'] ?? 0;
                
            default:
                return $result['score'] ?? 0;
        }
    }

    /**
     * Check if challenge has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    /**
     * Get the opponent for a given user
     */
    public function getOpponent(User $user): ?User
    {
        if ($user->id === $this->challenger_id) {
            return $this->challenged;
        } elseif ($user->id === $this->challenged_id) {
            return $this->challenger;
        }
        
        return null;
    }

    /**
     * Get display-friendly challenge type name
     */
    public function getChallengeTypeNameAttribute(): string
    {
        $names = [
            'quiz_duel' => 'Duelo de Quiz',
            'streak_competition' => 'Competencia de Rachas',
            'points_race' => 'Carrera de Puntos',
            'course_race' => 'Carrera de Cursos',
        ];

        return $names[$this->challenge_type] ?? $this->challenge_type;
    }

    /**
     * Get status in Spanish
     */
    public function getStatusNameAttribute(): string
    {
        $names = [
            'pending' => 'Pendiente',
            'accepted' => 'Aceptado',
            'declined' => 'Rechazado',
            'active' => 'Activo',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
        ];

        return $names[$this->status] ?? $this->status;
    }
}