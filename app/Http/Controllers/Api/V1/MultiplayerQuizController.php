<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QuizRoom;
use App\Models\QuizRoomParticipant;
use App\Models\QuizRoomAnswer;
use App\Models\Quiz;
use App\Models\Question;
use App\Events\QuizRoomUpdated;
use App\Events\QuestionStarted;
use App\Events\ParticipantAnswered;
use App\Events\QuizCompleted;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MultiplayerQuizController extends Controller
{
    public function getRoomList(Request $request): JsonResponse
    {
        $rooms = QuizRoom::with(['quiz', 'host', 'participants.user'])
            ->public()
            ->available()
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_code' => $room->room_code,
                    'name' => $room->name,
                    'quiz' => [
                        'id' => $room->quiz->id,
                        'title' => $room->quiz->title,
                    ],
                    'host' => [
                        'id' => $room->host->id,
                        'name' => $room->host->name,
                    ],
                    'participants_count' => $room->getParticipantCount(),
                    'max_participants' => $room->max_participants,
                    'status' => $room->status,
                    'question_time_limit' => $room->question_time_limit,
                    'created_at' => $room->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    public function createRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quiz_id' => 'required|exists:quizzes,id',
            'max_participants' => 'integer|min:2|max:20',
            'question_time_limit' => 'integer|min:10|max:120',
            'is_public' => 'boolean',
            'room_settings' => 'array'
        ]);

        $quiz = Quiz::with('questions')->findOrFail($validated['quiz_id']);
        
        if ($quiz->questions->count() < 3) {
            throw ValidationException::withMessages([
                'quiz_id' => 'Quiz must have at least 3 questions for multiplayer mode.'
            ]);
        }

        $room = QuizRoom::create([
            'name' => $validated['name'],
            'quiz_id' => $validated['quiz_id'],
            'host_id' => $request->user()->id,
            'max_participants' => $validated['max_participants'] ?? 8,
            'question_time_limit' => $validated['question_time_limit'] ?? 30,
            'is_public' => $validated['is_public'] ?? true,
            'room_settings' => $validated['room_settings'] ?? [],
        ]);

        $room->load(['quiz', 'host']);

        broadcast(new QuizRoomUpdated($room, 'created'));

        return response()->json([
            'success' => true,
            'data' => [
                'room' => $this->formatRoomData($room),
                'message' => 'Quiz room created successfully! Room code: ' . $room->room_code
            ]
        ], 201);
    }

    public function joinRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_code' => 'required|string|size:6',
        ]);

        $room = QuizRoom::with(['quiz', 'host', 'participants.user'])
            ->where('room_code', strtoupper($validated['room_code']))
            ->firstOrFail();

        if ($room->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot join room - quiz already started or completed.'
            ], 400);
        }

        if ($room->getParticipantCount() >= $room->max_participants) {
            return response()->json([
                'success' => false,
                'message' => 'Room is full.'
            ], 400);
        }

        if ($room->hasParticipant($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are already in this room.'
            ], 400);
        }

        $participant = QuizRoomParticipant::create([
            'quiz_room_id' => $room->id,
            'user_id' => $request->user()->id,
            'status' => 'joined'
        ]);

        $room->refresh();
        
        broadcast(new QuizRoomUpdated($room, 'participant_joined', [
            'participant' => [
                'id' => $participant->id,
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                ]
            ]
        ]));

        return response()->json([
            'success' => true,
            'data' => [
                'room' => $this->formatRoomData($room),
                'participant' => $participant,
                'message' => 'Successfully joined the room!'
            ]
        ]);
    }

    public function leaveRoom(Request $request, QuizRoom $room): JsonResponse
    {
        $participant = $room->participants()
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'You are not in this room.'
            ], 400);
        }

        if ($room->status === 'in_progress' && !$participant->isFinished()) {
            $participant->update(['status' => 'disconnected']);
        } else {
            $participant->delete();
        }

        broadcast(new QuizRoomUpdated($room, 'participant_left', [
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Left the room successfully.'
        ]);
    }

    public function getRoomDetails(QuizRoom $room): JsonResponse
    {
        $room->load(['quiz.questions', 'host', 'participants.user']);

        return response()->json([
            'success' => true,
            'data' => $this->formatRoomData($room, true)
        ]);
    }

    public function setReady(Request $request, QuizRoom $room): JsonResponse
    {
        $participant = $room->participants()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($room->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set ready - quiz already started.'
            ], 400);
        }

        $participant->update(['status' => 'ready']);

        broadcast(new QuizRoomUpdated($room, 'participant_ready', [
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'You are now ready!'
        ]);
    }

    public function startQuiz(Request $request, QuizRoom $room): JsonResponse
    {
        if (!$room->isHost($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Only the host can start the quiz.'
            ], 403);
        }

        if (!$room->canStart()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot start quiz - need at least 2 participants.'
            ], 400);
        }

        $room->update([
            'status' => 'starting',
            'started_at' => now()
        ]);

        broadcast(new QuizRoomUpdated($room, 'quiz_starting'));

        // Start first question after 3 second countdown
        dispatch(function () use ($room) {
            sleep(3);
            $this->startNextQuestion($room);
        })->delay(now()->addSeconds(3));

        return response()->json([
            'success' => true,
            'message' => 'Quiz starting in 3 seconds!'
        ]);
    }

    public function submitAnswer(Request $request, QuizRoom $room): JsonResponse
    {
        $validated = $request->validate([
            'answer' => 'required',
            'response_time' => 'required|numeric|min:0',
            'answer_data' => 'array'
        ]);

        if ($room->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Quiz is not in progress.'
            ], 400);
        }

        $participant = $room->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', 'playing')
            ->firstOrFail();

        $currentQuestion = $room->getCurrentQuestion();
        if (!$currentQuestion) {
            return response()->json([
                'success' => false,
                'message' => 'No current question available.'
            ], 400);
        }

        if ($participant->hasAnsweredQuestion($currentQuestion->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already answered this question.'
            ], 400);
        }

        $isCorrect = $this->checkAnswer($currentQuestion, $validated['answer']);
        $points = $isCorrect ? 100 : 0;
        $speedBonus = $this->calculateSpeedBonus($validated['response_time'], $room->question_time_limit);

        $answer = QuizRoomAnswer::create([
            'quiz_room_id' => $room->id,
            'participant_id' => $participant->id,
            'question_id' => $currentQuestion->id,
            'question_number' => $room->current_question,
            'answer' => $validated['answer'],
            'is_correct' => $isCorrect,
            'points_earned' => $points,
            'speed_bonus' => $speedBonus,
            'response_time' => $validated['response_time'],
            'answer_data' => $validated['answer_data'] ?? null,
        ]);

        $participant->updateScore($points, $speedBonus, $isCorrect);

        broadcast(new ParticipantAnswered($room, $participant, $answer));

        return response()->json([
            'success' => true,
            'data' => [
                'is_correct' => $isCorrect,
                'points_earned' => $points,
                'speed_bonus' => $speedBonus,
                'total_points' => $points + $speedBonus,
                'current_rank' => $participant->getCurrentRank(),
                'performance' => $participant->getPerformanceStats()
            ]
        ]);
    }

    public function getLeaderboard(QuizRoom $room): JsonResponse
    {
        $leaderboard = $room->participants()
            ->with('user')
            ->orderBy('total_score', 'desc')
            ->orderBy('average_response_time', 'asc')
            ->get()
            ->map(function ($participant, $index) {
                return [
                    'position' => $index + 1,
                    'user' => [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ],
                    'total_score' => $participant->total_score,
                    'correct_answers' => $participant->correct_answers,
                    'total_questions' => $participant->total_questions,
                    'accuracy' => $participant->getAccuracy(),
                    'speed_bonus' => $participant->speed_bonus,
                    'average_response_time' => $participant->average_response_time,
                    'status' => $participant->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $leaderboard
        ]);
    }

    protected function startNextQuestion(QuizRoom $room): void
    {
        if ($room->nextQuestion()) {
            $room->participants()->where('status', 'joined')->update(['status' => 'playing']);
            $room->update(['status' => 'in_progress']);
            
            $currentQuestion = $room->getCurrentQuestion();
            
            broadcast(new QuestionStarted($room, $currentQuestion));
            
            // Auto-advance to next question after time limit
            dispatch(function () use ($room) {
                $this->handleQuestionTimeout($room);
            })->delay(now()->addSeconds($room->question_time_limit + 5));
        } else {
            $this->completeQuiz($room);
        }
    }

    protected function handleQuestionTimeout(QuizRoom $room): void
    {
        if ($room->status !== 'in_progress') {
            return;
        }

        // Auto-submit blank answers for participants who haven't answered
        $currentQuestion = $room->getCurrentQuestion();
        if ($currentQuestion) {
            $unansweredParticipants = $room->participants()
                ->where('status', 'playing')
                ->whereDoesntHave('answers', function ($query) use ($currentQuestion) {
                    $query->where('question_id', $currentQuestion->id);
                })
                ->get();

            foreach ($unansweredParticipants as $participant) {
                QuizRoomAnswer::create([
                    'quiz_room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'question_id' => $currentQuestion->id,
                    'question_number' => $room->current_question,
                    'answer' => '',
                    'is_correct' => false,
                    'points_earned' => 0,
                    'speed_bonus' => 0,
                    'response_time' => $room->question_time_limit,
                ]);

                $participant->updateScore(0, 0, false);
            }
        }

        // Continue to next question
        $this->startNextQuestion($room);
    }

    protected function completeQuiz(QuizRoom $room): void
    {
        $room->update(['status' => 'completed', 'ended_at' => now()]);
        $room->participants()->where('status', 'playing')->update(['status' => 'finished']);
        
        $room->calculateFinalRankings();
        
        broadcast(new QuizCompleted($room));
    }

    protected function checkAnswer(Question $question, $userAnswer): bool
    {
        switch ($question->question_type) {
            case 'multiple_choice':
                $correctOption = $question->options()->where('is_correct', true)->first();
                return $correctOption && $correctOption->option_text === $userAnswer;
                
            case 'fill_in_the_blank':
                $correctAnswers = $question->options()
                    ->where('is_correct', true)
                    ->pluck('option_text')
                    ->map(fn($answer) => strtolower(trim($answer)));
                    
                return $correctAnswers->contains(strtolower(trim($userAnswer)));
                
            default:
                return false;
        }
    }

    protected function calculateSpeedBonus(float $responseTime, int $timeLimit): int
    {
        if ($responseTime > ($timeLimit * 0.5)) {
            return 0;
        }

        $speedPercentage = 1 - ($responseTime / ($timeLimit * 0.5));
        return min(50, max(1, intval($speedPercentage * 50)));
    }

    protected function formatRoomData(QuizRoom $room, bool $includeQuestions = false): array
    {
        $data = [
            'id' => $room->id,
            'room_code' => $room->room_code,
            'name' => $room->name,
            'status' => $room->status,
            'current_question' => $room->current_question,
            'total_questions' => $room->getTotalQuestions(),
            'question_time_limit' => $room->question_time_limit,
            'max_participants' => $room->max_participants,
            'is_public' => $room->is_public,
            'created_at' => $room->created_at,
            'started_at' => $room->started_at,
            'ended_at' => $room->ended_at,
            'quiz' => [
                'id' => $room->quiz->id,
                'title' => $room->quiz->title,
            ],
            'host' => [
                'id' => $room->host->id,
                'name' => $room->host->name,
            ],
            'participants' => $room->participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'user' => [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                    ],
                    'status' => $participant->status,
                    'total_score' => $participant->total_score,
                    'correct_answers' => $participant->correct_answers,
                    'joined_at' => $participant->joined_at,
                ];
            }),
        ];

        if ($includeQuestions && $room->quiz->questions) {
            $data['questions'] = $room->quiz->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                ];
            });
        }

        return $data;
    }
}