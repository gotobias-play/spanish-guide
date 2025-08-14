<?php

namespace App\Events;

use App\Models\QuizRoom;
use App\Models\QuizRoomParticipant;
use App\Models\QuizRoomAnswer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantAnswered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quizRoom;
    public $participant;
    public $answer;

    public function __construct(QuizRoom $quizRoom, QuizRoomParticipant $participant, QuizRoomAnswer $answer)
    {
        $this->quizRoom = $quizRoom;
        $this->participant = $participant;
        $this->answer = $answer;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('quiz-room.' . $this->quizRoom->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->quizRoom->id,
            'question_number' => $this->answer->question_number,
            'participant' => [
                'id' => $this->participant->id,
                'user_id' => $this->participant->user_id,
                'user_name' => $this->participant->user->name,
                'total_score' => $this->participant->total_score,
                'current_rank' => $this->participant->getCurrentRank(),
            ],
            'answer_details' => [
                'is_correct' => $this->answer->is_correct,
                'points_earned' => $this->answer->points_earned,
                'speed_bonus' => $this->answer->speed_bonus,
                'response_time' => $this->answer->response_time,
            ],
            'answered_at' => $this->answer->answered_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'participant.answered';
    }
}
