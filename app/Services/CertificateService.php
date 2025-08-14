<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Models\UserQuizProgress;
use App\Models\Quiz;
use Carbon\Carbon;

class CertificateService
{
    /**
     * Check if user is eligible for course completion certificate
     */
    public function checkCourseCompletion(User $user, Course $course): ?Certificate
    {
        // Get all quizzes for this course
        $courseQuizzes = Quiz::whereHas('lesson', function($query) use ($course) {
            $query->where('course_id', $course->id);
        })->get();

        if ($courseQuizzes->isEmpty()) {
            return null; // No quizzes in course
        }

        // Get user's progress for all course quizzes
        $completedQuizzes = [];
        $totalPoints = 0;
        $totalScore = 0;
        $quizCount = 0;
        $firstQuizDate = null;

        foreach ($courseQuizzes as $quiz) {
            $progress = UserQuizProgress::where('user_id', $user->id)
                ->where('section_id', 'database-quiz-' . $quiz->id)
                ->first();

            if ($progress) {
                $completedQuizzes[] = [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'score' => $progress->score,
                    'completed_at' => $progress->updated_at->format('Y-m-d H:i:s'),
                ];

                $totalScore += $progress->score;
                $quizCount++;

                // Track earliest quiz attempt
                if (!$firstQuizDate || $progress->created_at < $firstQuizDate) {
                    $firstQuizDate = $progress->created_at;
                }

                // Get points from user points table
                $points = $user->userPoints()
                    ->where('quiz_id', $quiz->id)
                    ->sum('points');
                $totalPoints += $points;
            }
        }

        // Check if user completed ALL quizzes in the course
        if ($quizCount < $courseQuizzes->count()) {
            return null; // Not all quizzes completed
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingCertificate) {
            return $existingCertificate; // Already has certificate
        }

        // Calculate average score
        $averageScore = $quizCount > 0 ? round($totalScore / $quizCount, 2) : 0;

        // Create certificate
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_code' => '', // Will be generated below
            'course_title' => $course->title,
            'user_name' => $user->name,
            'total_quizzes' => $courseQuizzes->count(),
            'completed_quizzes' => $quizCount,
            'average_score' => $averageScore,
            'total_points_earned' => $totalPoints,
            'course_started_at' => $firstQuizDate,
            'course_completed_at' => now(),
            'quiz_results' => $completedQuizzes,
            'certificate_data' => [
                'course_description' => $course->description,
                'completion_date' => now()->format('F j, Y'),
                'grade_level' => $this->calculateGradeLevel($averageScore),
                'total_lessons' => $course->lessons()->count(),
            ],
        ]);

        // Generate and save certificate code
        $certificate->certificate_code = $certificate->generateCertificateCode();
        $certificate->save();

        return $certificate;
    }

    /**
     * Check for new course completion certificates for user
     */
    public function checkAllCoursesForUser(User $user): array
    {
        $newCertificates = [];
        
        $courses = Course::where('is_published', true)->get();
        
        foreach ($courses as $course) {
            $certificate = $this->checkCourseCompletion($user, $course);
            if ($certificate && $certificate->wasRecentlyCreated) {
                $newCertificates[] = $certificate;
            }
        }

        return $newCertificates;
    }

    /**
     * Calculate grade level based on average score
     */
    private function calculateGradeLevel(float $score): string
    {
        if ($score >= 95) return 'Excelente';
        if ($score >= 85) return 'Muy Bueno';
        if ($score >= 75) return 'Bueno';
        if ($score >= 60) return 'Satisfactorio';
        
        return 'En Progreso';
    }

    /**
     * Get certificate statistics for user
     */
    public function getUserCertificateStats(User $user): array
    {
        $certificates = $user->certificates()->with('course')->get();
        $totalCourses = Course::where('is_published', true)->count();

        return [
            'total_certificates' => $certificates->count(),
            'total_available_courses' => $totalCourses,
            'completion_percentage' => $totalCourses > 0 ? round(($certificates->count() / $totalCourses) * 100, 1) : 0,
            'certificates' => $certificates->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'certificate_code' => $cert->certificate_code,
                    'course_title' => $cert->course_title,
                    'average_score' => $cert->average_score,
                    'grade_level' => $cert->grade_level,
                    'completed_at' => $cert->course_completed_at->format('F j, Y'),
                    'total_points' => $cert->total_points_earned,
                ];
            }),
        ];
    }
}