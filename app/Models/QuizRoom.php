<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuizRoom extends Model
{
    protected $fillable = [
        'room_code',
        'name',
        'quiz_id',
        'host_id',
        'max_participants',
        'status',
        'current_question',
        'question_started_at',
        'question_time_limit',
        'is_public',
        'room_settings',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'room_settings' => 'array',
        'question_started_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($quizRoom) {
            if (empty($quizRoom->room_code)) {
                $quizRoom->room_code = static::generateRoomCode();
            }
        });
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(QuizRoomParticipant::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizRoomAnswer::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->whereIn('status', ['joined', 'ready', 'playing']);
    }

    public function getParticipantCount(): int
    {
        return $this->activeParticipants()->count();
    }

    public function canStart(): bool
    {
        return $this->status === 'waiting' && 
               $this->getParticipantCount() >= 2 &&
               $this->getParticipantCount() <= $this->max_participants;
    }

    public function isHost(User $user): bool
    {
        return $this->host_id === $user->id;
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function getLeaderboard()
    {
        return $this->participants()
            ->with('user')
            ->where('status', 'finished')
            ->orderBy('total_score', 'desc')
            ->orderBy('average_response_time', 'asc')
            ->get();
    }

    public function getCurrentQuestion()
    {
        if ($this->current_question === 0) {
            return null;
        }

        return $this->quiz->questions()
            ->with('options')
            ->skip($this->current_question - 1)
            ->first();
    }

    public function getTotalQuestions(): int
    {
        return $this->quiz->questions()->count();
    }

    public function nextQuestion(): bool
    {
        if ($this->current_question < $this->getTotalQuestions()) {
            $this->increment('current_question');
            $this->update(['question_started_at' => now()]);
            return true;
        }
        
        $this->update(['status' => 'completed', 'ended_at' => now()]);
        return false;
    }

    public function calculateFinalRankings()
    {
        $participants = $this->participants()
            ->where('status', 'finished')
            ->orderBy('total_score', 'desc')
            ->orderBy('average_response_time', 'asc')
            ->get();

        foreach ($participants as $index => $participant) {
            $participant->update(['position' => $index + 1]);
        }
    }

    protected static function generateRoomCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('room_code', $code)->exists());

        return $code;
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'waiting')
                    ->whereColumn('max_participants', '>', 
                        function ($subquery) {
                            $subquery->selectRaw('count(*)')
                                    ->from('quiz_room_participants')
                                    ->whereColumn('quiz_room_id', 'quiz_rooms.id')
                                    ->whereIn('status', ['joined', 'ready', 'playing']);
                        });
    }
}
