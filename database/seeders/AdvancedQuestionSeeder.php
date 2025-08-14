<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;

class AdvancedQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Advanced Learning Course
        $advancedCourse = Course::create([
            'title' => 'Advanced Interactive English',
            'description' => 'Interactive exercises with drag-drop, audio, images, and matching',
            'is_published' => true
        ]);

        // Create lesson for advanced questions
        $advancedLesson = Lesson::create([
            'course_id' => $advancedCourse->id,
            'title' => 'Advanced Question Types',
            'content' => 'Interactive learning with multimedia and advanced interactions',
            'order' => 1,
            'is_published' => true
        ]);

        // Create Advanced Quiz
        $advancedQuiz = Quiz::create([
            'lesson_id' => $advancedLesson->id,
            'title' => '🎯 Advanced Interactive Quiz'
        ]);

        // 1. Image-based Question
        $imageQuestion = Question::create([
            'quiz_id' => $advancedQuiz->id,
            'question_text' => 'What do you see in this picture?',
            'question_type' => 'image_based',
            'image_url' => 'https://images.unsplash.com/photo-1544427920-c49ccfb85579?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=60', // Classroom image
            'explanation' => 'This is a classroom with students and desks.',
            'difficulty_level' => 'easy'
        ]);

        QuestionOption::create(['question_id' => $imageQuestion->id, 'option_text' => 'A classroom', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $imageQuestion->id, 'option_text' => 'A library', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $imageQuestion->id, 'option_text' => 'A restaurant', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $imageQuestion->id, 'option_text' => 'A park', 'is_correct' => false]);

        // 2. Audio-based Question (simulated - would need actual audio files)
        $audioQuestion = Question::create([
            'quiz_id' => $advancedQuiz->id,
            'question_text' => 'Listen to the pronunciation and choose the correct word:',
            'question_type' => 'audio_based',
            'audio_url' => '/audio/hello.mp3', // Would need actual audio file
            'explanation' => 'The audio says "Hello" in English.',
            'difficulty_level' => 'easy'
        ]);

        QuestionOption::create(['question_id' => $audioQuestion->id, 'option_text' => 'Hello', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $audioQuestion->id, 'option_text' => 'Goodbye', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $audioQuestion->id, 'option_text' => 'Thank you', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $audioQuestion->id, 'option_text' => 'Please', 'is_correct' => false]);

        // 3. Drag and Drop Question
        $dragQuestion = Question::create([
            'quiz_id' => $advancedQuiz->id,
            'question_text' => 'Drag the verbs to the correct tense category:',
            'question_type' => 'drag_and_drop',
            'question_config' => [
                'dropZones' => [
                    ['id' => 'present', 'label' => 'Present Tense'],
                    ['id' => 'past', 'label' => 'Past Tense']
                ]
            ],
            'explanation' => 'Present tense verbs describe current actions, past tense verbs describe completed actions.',
            'difficulty_level' => 'medium'
        ]);

        QuestionOption::create(['question_id' => $dragQuestion->id, 'option_text' => 'play', 'is_correct' => true, 'option_group' => 'present']);
        QuestionOption::create(['question_id' => $dragQuestion->id, 'option_text' => 'played', 'is_correct' => true, 'option_group' => 'past']);
        QuestionOption::create(['question_id' => $dragQuestion->id, 'option_text' => 'eat', 'is_correct' => true, 'option_group' => 'present']);
        QuestionOption::create(['question_id' => $dragQuestion->id, 'option_text' => 'ate', 'is_correct' => true, 'option_group' => 'past']);

        // 4. Matching Question
        $matchingQuestion = Question::create([
            'quiz_id' => $advancedQuiz->id,
            'question_text' => 'Match the English words with their Spanish translations:',
            'question_type' => 'matching',
            'question_config' => [
                'correctPairs' => [
                    '1' => '5', // cat -> gato
                    '2' => '6', // dog -> perro
                    '3' => '7', // house -> casa
                    '4' => '8'  // car -> coche
                ]
            ],
            'explanation' => 'These are common English-Spanish word pairs.',
            'difficulty_level' => 'medium'
        ]);

        // Left column (English)
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'Cat', 'is_correct' => false, 'option_group' => 'left', 'position' => 1]);
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'Dog', 'is_correct' => false, 'option_group' => 'left', 'position' => 2]);
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'House', 'is_correct' => false, 'option_group' => 'left', 'position' => 3]);
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'Car', 'is_correct' => false, 'option_group' => 'left', 'position' => 4]);
        
        // Right column (Spanish)
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'Gato', 'is_correct' => false, 'option_group' => 'right', 'position' => 5]);
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'Perro', 'is_correct' => false, 'option_group' => 'right', 'position' => 6]);
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'Casa', 'is_correct' => false, 'option_group' => 'right', 'position' => 7]);
        QuestionOption::create(['question_id' => $matchingQuestion->id, 'option_text' => 'Coche', 'is_correct' => false, 'option_group' => 'right', 'position' => 8]);

        // 5. Ordering Question
        $orderingQuestion = Question::create([
            'quiz_id' => $advancedQuiz->id,
            'question_text' => 'Put these steps in the correct order to make a cup of tea:',
            'question_type' => 'ordering',
            'explanation' => 'This is the standard process for making tea.',
            'difficulty_level' => 'easy'
        ]);

        QuestionOption::create(['question_id' => $orderingQuestion->id, 'option_text' => 'Boil water', 'is_correct' => true, 'position' => 1]);
        QuestionOption::create(['question_id' => $orderingQuestion->id, 'option_text' => 'Add tea bag to cup', 'is_correct' => true, 'position' => 2]);
        QuestionOption::create(['question_id' => $orderingQuestion->id, 'option_text' => 'Pour hot water into cup', 'is_correct' => true, 'position' => 3]);
        QuestionOption::create(['question_id' => $orderingQuestion->id, 'option_text' => 'Let it steep for 3-5 minutes', 'is_correct' => true, 'position' => 4]);
        QuestionOption::create(['question_id' => $orderingQuestion->id, 'option_text' => 'Remove tea bag and enjoy', 'is_correct' => true, 'position' => 5]);

        echo "Advanced Question Types Seeded Successfully!\n";
        echo "- Created 1 course: Advanced Interactive English\n";
        echo "- Created 1 lesson: Advanced Question Types\n";
        echo "- Created 1 quiz with 5 advanced questions:\n";
        echo "  • Image-based question\n";
        echo "  • Audio-based question\n";
        echo "  • Drag and drop question\n";
        echo "  • Matching question\n";
        echo "  • Ordering question\n";
    }
}