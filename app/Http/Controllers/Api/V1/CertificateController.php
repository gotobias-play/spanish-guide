<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    protected CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Get all certificates for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->certificateService->getUserCertificateStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get specific certificate by ID
     */
    public function show(Request $request, Certificate $certificate): JsonResponse
    {
        // Ensure user can only access their own certificates
        if ($certificate->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este certificado.',
            ], 403);
        }

        $certificate->load('course');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $certificate->id,
                'certificate_code' => $certificate->certificate_code,
                'course_title' => $certificate->course_title,
                'user_name' => $certificate->user_name,
                'average_score' => $certificate->average_score,
                'grade_level' => $certificate->grade_level,
                'completion_percentage' => $certificate->completion_percentage,
                'total_points' => $certificate->total_points_earned,
                'course_started_at' => $certificate->course_started_at?->format('F j, Y'),
                'course_completed_at' => $certificate->course_completed_at->format('F j, Y'),
                'quiz_results' => $certificate->quiz_results,
                'certificate_data' => $certificate->certificate_data,
            ],
        ]);
    }

    /**
     * Check for new certificates (called after quiz completion)
     */
    public function checkEligibility(Request $request): JsonResponse
    {
        $user = $request->user();
        $newCertificates = $this->certificateService->checkAllCoursesForUser($user);

        return response()->json([
            'success' => true,
            'data' => [
                'new_certificates' => count($newCertificates),
                'certificates' => $newCertificates->map(function ($cert) {
                    return [
                        'id' => $cert->id,
                        'certificate_code' => $cert->certificate_code,
                        'course_title' => $cert->course_title,
                        'grade_level' => $cert->grade_level,
                        'completed_at' => $cert->course_completed_at->format('F j, Y'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Check specific course completion
     */
    public function checkCourse(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        $certificate = $this->certificateService->checkCourseCompletion($user, $course);

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Aún no has completado todos los cuestionarios de este curso.',
                'data' => [
                    'eligible' => false,
                    'course_title' => $course->title,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $certificate->wasRecentlyCreated ? 
                '¡Felicidades! Has obtenido un nuevo certificado.' : 
                'Ya tienes un certificado para este curso.',
            'data' => [
                'eligible' => true,
                'certificate_id' => $certificate->id,
                'certificate_code' => $certificate->certificate_code,
                'course_title' => $certificate->course_title,
                'grade_level' => $certificate->grade_level,
                'is_new' => $certificate->wasRecentlyCreated,
            ],
        ]);
    }
}
