<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to prevent duplicates on re-seeding
        Course::truncate();
        Lesson::truncate();
        Quiz::truncate();
        Question::truncate();
        QuestionOption::truncate();

        // --- Basic English Grammar Course (Foundations) ---
        $courseFoundations = Course::create([
            'title' => 'Basic English Grammar',
            'description' => 'Fundamental concepts of English grammar, including verbs To Be and To Have.',
            'is_published' => true,
        ]);

        $lessonToBe = Lesson::create([
            'course_id' => $courseFoundations->id,
            'title' => 'The Verb To Be',
            'content' => 'The verb \'to be\' is one of the most important verbs in English. It can be used as a main verb or an auxiliary verb. Learn its conjugations and common uses.',
            'order' => 1,
            'is_published' => true,
            'interactive_data' => json_encode([
                ['type' => 'flashcard', 'data' => [['front' => 'Hello', 'back' => 'Hola'], ['front' => 'Goodbye', 'back' => 'Adiós']]],
            ]),
        ]);

        $quizToBe = Quiz::create([
            'lesson_id' => $lessonToBe->id,
            'title' => 'To Be Quiz',
        ]);

        $question1 = Question::create([
            'quiz_id' => $quizToBe->id,
            'question_text' => 'I ___ a student.',
            'question_type' => 'multiple_choice',
        ]);
        QuestionOption::create(['question_id' => $question1->id, 'option_text' => 'am', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question1->id, 'option_text' => 'is', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $question1->id, 'option_text' => 'are', 'is_correct' => false]);

        $question2 = Question::create([
            'quiz_id' => $quizToBe->id,
            'question_text' => 'She ___ happy.',
            'question_type' => 'multiple_choice',
        ]);
        QuestionOption::create(['question_id' => $question2->id, 'option_text' => 'is', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question2->id, 'option_text' => 'am', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $question2->id, 'option_text' => 'are', 'is_correct' => false]);

        $lessonToHave = Lesson::create([
            'course_id' => $courseFoundations->id,
            'title' => 'The Verb To Have',
            'content' => 'The verb \'to have\' is used to show possession, or to talk about experiences. Learn its conjugations and common uses.',
            'order' => 2,
        ]);

        $quizToHave = Quiz::create([
            'lesson_id' => $lessonToHave->id,
            'title' => 'To Have Quiz',
        ]);

        $question3 = Question::create([
            'quiz_id' => $quizToHave->id,
            'question_text' => 'They ___ a new car.',
            'question_type' => 'multiple_choice',
        ]);
        QuestionOption::create(['question_id' => $question3->id, 'option_text' => 'have', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question3->id, 'option_text' => 'has', 'is_correct' => false]);

        // --- Daily Life Course ---
        $courseDailyLife = Course::create([
            'title' => 'English for Daily Life',
            'description' => 'Practice building sentences for everyday situations using the present simple tense.',
            'is_published' => true,
        ]);

        $lessonDailyRoutine = Lesson::create([
            'course_id' => $courseDailyLife->id,
            'title' => 'My Daily Routine',
            'content' => 'Learn to describe your daily activities using the present simple tense. Focus on common verbs and time expressions.',
            'order' => 1,
            'is_published' => true,
        ]);

        $quizDailyRoutine = Quiz::create([
            'lesson_id' => $lessonDailyRoutine->id,
            'title' => 'Daily Routine Practice',
        ]);

        $question4 = Question::create([
            'quiz_id' => $quizDailyRoutine->id,
            'question_text' => 'I usually ___ up at 7 AM.',
            'question_type' => 'fill_in_the_blank',
        ]);
        QuestionOption::create(['question_id' => $question4->id, 'option_text' => 'wake', 'is_correct' => true]);

        // --- My City Course ---
        $courseCity = Course::create([
            'title' => 'Exploring My City',
            'description' => 'Learn prepositions of place and directions to navigate a city.',
            'is_published' => true,
        ]);

        $lessonPrepositions = Lesson::create([
            'course_id' => $courseCity->id,
            'title' => 'Prepositions of Place',
            'content' => 'Understand how to use prepositions like in, on, at, next to, between, etc., to describe locations.',
            'order' => 1,
            'is_published' => true,
        ]);

        $quizPrepositions = Quiz::create([
            'lesson_id' => $lessonPrepositions->id,
            'title' => 'City Prepositions Quiz',
        ]);

        $question5 = Question::create([
            'quiz_id' => $quizPrepositions->id,
            'question_text' => 'The park is ___ the library and the museum.',
            'question_type' => 'multiple_choice',
        ]);
        QuestionOption::create(['question_id' => $question5->id, 'option_text' => 'between', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question5->id, 'option_text' => 'on', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $question5->id, 'option_text' => 'in', 'is_correct' => false]);

        // --- At the Restaurant Course ---
        $courseRestaurant = Course::create([
            'title' => 'At the Restaurant',
            'description' => 'Learn vocabulary for food, ordering, and using quantifiers.',
            'is_published' => true,
        ]);

        $lessonQuantifiers = Lesson::create([
            'course_id' => $courseRestaurant->id,
            'title' => 'Quantifiers and Food Vocabulary',
            'content' => 'Practice using quantifiers like some, any, much, many, a lot of, and common food items.',
            'order' => 1,
            'is_published' => true,
        ]);

        $quizRestaurant = Quiz::create([
            'lesson_id' => $lessonQuantifiers->id,
            'title' => 'Restaurant Quantifiers Quiz',
        ]);

        $question6 = Question::create([
            'quiz_id' => $quizRestaurant->id,
            'question_text' => 'How ___ sugar do you want?',
            'question_type' => 'multiple_choice',
        ]);
        QuestionOption::create(['question_id' => $question6->id, 'option_text' => 'much', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question6->id, 'option_text' => 'many', 'is_correct' => false]);

        // --- Asking Questions Course ---
        $courseQuestions = Course::create([
            'title' => 'Asking Questions',
            'description' => 'A guide to forming \'Wh-\' questions in English.',
            'is_published' => true,
        ]);

        $lessonWhQuestions = Lesson::create([
            'course_id' => $courseQuestions->id,
            'title' => 'Understanding Wh- Questions',
            'content' => 'Learn how to use who, what, where, when, why, and how to ask questions effectively.',
            'order' => 1,
            'is_published' => true,
        ]);

        $quizWhQuestions = Quiz::create([
            'lesson_id' => $lessonWhQuestions->id,
            'title' => 'Wh- Questions Practice',
        ]);

        $question7 = Question::create([
            'quiz_id' => $quizWhQuestions->id,
            'question_text' => '___ is your name?',
            'question_type' => 'fill_in_the_blank',
        ]);
        QuestionOption::create(['question_id' => $question7->id, 'option_text' => 'What', 'is_correct' => true]);

        $question8 = Question::create([
            'quiz_id' => $quizWhQuestions->id,
            'question_text' => '___ do you live?',
            'question_type' => 'multiple_choice',
        ]);
        QuestionOption::create(['question_id' => $question8->id, 'option_text' => 'Where', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question8->id, 'option_text' => 'When', 'is_correct' => false]);
    }
}
