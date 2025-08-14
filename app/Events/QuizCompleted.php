<?php

namespace App\Events;

use App\Models\QuizRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quizRoom;

    public function __construct(QuizRoom $quizRoom)
    {
        $this->quizRoom = $quizRoom;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('quiz-room.' . $this->quizRoom->id),
        ];
    }

    public function broadcastWith(): array
    {
        $leaderboard = $this->quizRoom->getLeaderboard()->map(function ($participant, $index) {
            return [
                'position' => $index + 1,
                'user' => [
                    'id' => $participant->user->id,
                    'name' => $participant->user->name,
                ],
                'total_score' => $participant->total_score,
                'correct_answers' => $participant->correct_answers,
                'accuracy' => $participant->getAccuracy(),
                'speed_bonus' => $participant->speed_bonus,
                'average_response_time' => $participant->average_response_time,
            ];
        });

        return [
            'room_id' => $this->quizRoom->id,
            'room_code' => $this->quizRoom->room_code,
            'quiz_title' => $this->quizRoom->quiz->title,
            'total_questions' => $this->quizRoom->getTotalQuestions(),
            'duration' => $this->quizRoom->started_at && $this->quizRoom->ended_at 
                ? $this->quizRoom->started_at->diffInSeconds($this->quizRoom->ended_at)
                : null,
            'leaderboard' => $leaderboard,
            'completed_at' => $this->quizRoom->ended_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'quiz.completed';
    }
}