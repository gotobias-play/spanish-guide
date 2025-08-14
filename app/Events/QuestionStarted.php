<?php

namespace App\Events;

use App\Models\QuizRoom;
use App\Models\Question;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quizRoom;
    public $question;

    public function __construct(QuizRoom $quizRoom, Question $question)
    {
        $this->quizRoom = $quizRoom;
        $this->question = $question;
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
            'question_number' => $this->quizRoom->current_question,
            'total_questions' => $this->quizRoom->getTotalQuestions(),
            'time_limit' => $this->quizRoom->question_time_limit,
            'question' => [
                'id' => $this->question->id,
                'question_text' => $this->question->question_text,
                'question_type' => $this->question->question_type,
                'options' => $this->question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->option_text,
                    ];
                }),
            ],
            'started_at' => now()->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'question.started';
    }
}
