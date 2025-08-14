<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizRoomParticipant extends Model
{
    protected $fillable = [
        'quiz_room_id',
        'user_id',
        'total_score',
        'correct_answers',
        'total_questions',
        'position',
        'average_response_time',
        'speed_bonus',
        'status',
        'joined_at',
        'finished_at',
        'performance_data'
    ];

    protected $casts = [
        'performance_data' => 'array',
        'joined_at' => 'datetime',
        'finished_at' => 'datetime',
        'average_response_time' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($participant) {
            if (empty($participant->joined_at)) {
                $participant->joined_at = now();
            }
        });
    }

    public function quizRoom(): BelongsTo
    {
        return $this->belongsTo(QuizRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizRoomAnswer::class, 'participant_id');
    }

    public function updateScore(int $points, int $speedBonus = 0, bool $isCorrect = false): void
    {
        $this->increment('total_score', $points + $speedBonus);
        $this->increment('speed_bonus', $speedBonus);
        
        if ($isCorrect) {
            $this->increment('correct_answers');
        }
        
        $this->increment('total_questions');
        $this->updateAverageResponseTime();
    }

    public function updateAverageResponseTime(): void
    {
        $averageTime = $this->answers()
            ->whereNotNull('response_time')
            ->avg('response_time');
        
        if ($averageTime) {
            $this->update(['average_response_time' => round($averageTime, 2)]);
        }
    }

    public function getAccuracy(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }
        
        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }

    public function finishQuiz(): void
    {
        $this->update([
            'status' => 'finished',
            'finished_at' => now()
        ]);
        
        $this->updateAverageResponseTime();
    }

    public function getCurrentRank(): int
    {
        return $this->quizRoom->participants()
            ->where('total_score', '>', $this->total_score)
            ->count() + 1;
    }

    public function getPerformanceStats(): array
    {
        return [
            'total_score' => $this->total_score,
            'correct_answers' => $this->correct_answers,
            'total_questions' => $this->total_questions,
            'accuracy' => $this->getAccuracy(),
            'speed_bonus' => $this->speed_bonus,
            'average_response_time' => $this->average_response_time,
            'current_rank' => $this->getCurrentRank(),
            'status' => $this->status
        ];
    }

    public function hasAnsweredQuestion(int $questionId): bool
    {
        return $this->answers()
            ->where('question_id', $questionId)
            ->exists();
    }

    public function getAnswerForQuestion(int $questionId): ?QuizRoomAnswer
    {
        return $this->answers()
            ->where('question_id', $questionId)
            ->first();
    }

    public function addPerformanceData(array $data): void
    {
        $currentData = $this->performance_data ?? [];
        $currentData[] = array_merge($data, ['timestamp' => now()->toISOString()]);
        
        $this->update(['performance_data' => $currentData]);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function isDisconnected(): bool
    {
        return $this->status === 'disconnected';
    }
}
