<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizRoomAnswer extends Model
{
    protected $fillable = [
        'quiz_room_id',
        'participant_id',
        'question_id',
        'question_number',
        'answer',
        'is_correct',
        'points_earned',
        'speed_bonus',
        'response_time',
        'answered_at',
        'answer_data'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'response_time' => 'float',
        'answered_at' => 'datetime',
        'answer_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($answer) {
            if (empty($answer->answered_at)) {
                $answer->answered_at = now();
            }
        });
    }

    public function quizRoom(): BelongsTo
    {
        return $this->belongsTo(QuizRoom::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(QuizRoomParticipant::class, 'participant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function calculatePoints(): int
    {
        $basePoints = $this->is_correct ? 100 : 0;
        return $basePoints + $this->speed_bonus;
    }

    public function getTotalPoints(): int
    {
        return $this->points_earned + $this->speed_bonus;
    }

    public function isQuickAnswer(): bool
    {
        $timeLimit = $this->quizRoom->question_time_limit;
        return $this->response_time <= ($timeLimit * 0.5);
    }

    public function getSpeedRating(): string
    {
        if (!$this->response_time) {
            return 'unknown';
        }

        $timeLimit = $this->quizRoom->question_time_limit;
        $percentage = ($this->response_time / $timeLimit) * 100;

        if ($percentage <= 25) return 'lightning';
        if ($percentage <= 50) return 'fast';
        if ($percentage <= 75) return 'normal';
        return 'slow';
    }

    public function getAnswerDetails(): array
    {
        return [
            'answer' => $this->answer,
            'is_correct' => $this->is_correct,
            'points_earned' => $this->points_earned,
            'speed_bonus' => $this->speed_bonus,
            'total_points' => $this->getTotalPoints(),
            'response_time' => $this->response_time,
            'speed_rating' => $this->getSpeedRating(),
            'is_quick_answer' => $this->isQuickAnswer(),
            'answered_at' => $this->answered_at,
            'answer_data' => $this->answer_data
        ];
    }
}
