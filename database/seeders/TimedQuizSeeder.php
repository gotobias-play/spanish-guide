<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Lesson;

class TimedQuizSeeder extends Seeder
{
    public function run(): void
    {
        // Find existing lessons to add timed quizzes to
        $grammarLesson = Lesson::where('title', 'like', '%Grammar%')->first();
        $dailyLifeLesson = Lesson::where('title', 'like', '%Daily Life%')->first();

        if ($grammarLesson) {
            // Create a speed grammar quiz
            $speedGrammarQuiz = Quiz::create([
                'lesson_id' => $grammarLesson->id,
                'title' => '⚡ Speed Grammar Challenge',
                'is_timed' => true,
                'time_per_question_seconds' => 15, // 15 seconds per question
                'show_timer' => true,
                'timer_settings' => json_encode([
                    'warning_at_seconds' => 5, // Show warning when 5 seconds left
                    'auto_submit' => true, // Auto-submit when time runs out
                    'speed_bonus_enabled' => true // Enable speed bonus points
                ])
            ]);

            // Add quick grammar questions
            $questions = [
                [
                    'question_text' => '¿Cuál es la forma correcta? "I __ a student"',
                    'correct_answer' => 'am',
                    'wrong_answers' => ['is', 'are', 'be']
                ],
                [
                    'question_text' => '¿Cuál es la forma correcta? "She __ my sister"',
                    'correct_answer' => 'is',
                    'wrong_answers' => ['am', 'are', 'be']
                ],
                [
                    'question_text' => '¿Cuál es la forma correcta? "They __ happy"',
                    'correct_answer' => 'are',
                    'wrong_answers' => ['am', 'is', 'be']
                ],
                [
                    'question_text' => '¿Cuál es la forma correcta? "We __ friends"',
                    'correct_answer' => 'are',
                    'wrong_answers' => ['am', 'is', 'be']
                ],
            ];

            foreach ($questions as $questionData) {
                $question = Question::create([
                    'quiz_id' => $speedGrammarQuiz->id,
                    'question_text' => $questionData['question_text'],
                    'question_type' => 'multiple_choice'
                ]);

                // Create correct option
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $questionData['correct_answer'],
                    'is_correct' => true
                ]);

                // Create wrong options
                foreach ($questionData['wrong_answers'] as $wrongAnswer) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $wrongAnswer,
                        'is_correct' => false
                    ]);
                }
            }
        }

        if ($dailyLifeLesson) {
            // Create a full-quiz timer challenge
            $timedDailyQuiz = Quiz::create([
                'lesson_id' => $dailyLifeLesson->id,
                'title' => '🕐 Daily Life Time Challenge',
                'is_timed' => true,
                'time_limit_seconds' => 120, // 2 minutes for entire quiz
                'show_timer' => true,
                'timer_settings' => json_encode([
                    'warning_at_seconds' => 30, // Show warning when 30 seconds left
                    'auto_submit' => true,
                    'speed_bonus_enabled' => true,
                    'time_penalty' => false // No penalty for going over time
                ])
            ]);

            // Add daily life questions
            $dailyQuestions = [
                [
                    'question_text' => '¿Cómo dices "I wake up at 7 AM" en presente simple?',
                    'correct_answer' => 'I wake up at seven in the morning',
                    'wrong_answers' => ['I am waking up at 7 AM', 'I waked up at 7 AM', 'I will wake up at 7 AM']
                ],
                [
                    'question_text' => '¿Cuál es correcto para rutina diaria? "She __ coffee every morning"',
                    'correct_answer' => 'drinks',
                    'wrong_answers' => ['drink', 'drinking', 'drank']
                ],
                [
                    'question_text' => '¿Cómo se dice "Voy al trabajo en autobús"?',
                    'correct_answer' => 'I go to work by bus',
                    'wrong_answers' => ['I go to work in bus', 'I go to work with bus', 'I go to work on bus']
                ]
            ];

            foreach ($dailyQuestions as $questionData) {
                $question = Question::create([
                    'quiz_id' => $timedDailyQuiz->id,
                    'question_text' => $questionData['question_text'],
                    'question_type' => 'multiple_choice'
                ]);

                // Create correct option
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $questionData['correct_answer'],
                    'is_correct' => true
                ]);

                // Create wrong options
                foreach ($questionData['wrong_answers'] as $wrongAnswer) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $wrongAnswer,
                        'is_correct' => false
                    ]);
                }
            }
        }
    }
}