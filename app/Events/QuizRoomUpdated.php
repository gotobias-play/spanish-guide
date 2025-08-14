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

class QuizRoomUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quizRoom;
    public $updateType;
    public $data;

    public function __construct(QuizRoom $quizRoom, string $updateType, array $data = [])
    {
        $this->quizRoom = $quizRoom;
        $this->updateType = $updateType;
        $this->data = $data;
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
            'room_code' => $this->quizRoom->room_code,
            'status' => $this->quizRoom->status,
            'current_question' => $this->quizRoom->current_question,
            'total_questions' => $this->quizRoom->getTotalQuestions(),
            'participants_count' => $this->quizRoom->getParticipantCount(),
            'update_type' => $this->updateType,
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'room.updated';
    }
}
