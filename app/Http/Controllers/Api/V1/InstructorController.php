<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\InstructorRole;
use App\Models\ClassModel;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\ClassEnrollment;
use App\Models\User;

class InstructorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Instructor Role Management
    public function createInstructorRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_type' => 'required|in:instructor,head_instructor,department_admin',
            'department' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $instructorRole = InstructorRole::create([
            'user_id' => $request->user_id,
            'tenant_id' => auth()->user()->tenant_id,
            'role_type' => $request->role_type,
            'department' => $request->department,
            'permissions' => $request->permissions ?? [],
            'is_active' => true,
            'appointed_at' => now(),
            'expires_at' => $request->expires_at,
        ]);

        return response()->json([
            'message' => 'Instructor role created successfully',
            'instructor_role' => $instructorRole->load('user'),
        ], 201);
    }

    public function getInstructorRoles(): JsonResponse
    {
        $roles = InstructorRole::with(['user', 'tenant'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->active()
            ->orderBy('appointed_at', 'desc')
            ->get();

        return response()->json([
            'instructor_roles' => $roles,
        ]);
    }

    // Class Management
    public function createClass(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_students' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'settings' => 'nullable|array',
        ]);

        $class = ClassModel::create([
            'tenant_id' => auth()->user()->tenant_id,
            'instructor_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'max_students' => $request->max_students,
            'is_active' => true,
            'settings' => $request->settings ?? [],
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'message' => 'Class created successfully',
            'class' => $class->load('instructor'),
        ], 201);
    }

    public function getInstructorClasses(): JsonResponse
    {
        $classes = ClassModel::with(['instructor', 'enrollments.student'])
            ->where('instructor_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'description' => $class->description,
                    'class_code' => $class->class_code,
                    'max_students' => $class->max_students,
                    'enrollment_count' => $class->getEnrollmentCount(),
                    'available_spots' => $class->getAvailableSpots(),
                    'is_active' => $class->is_active,
                    'is_full' => $class->isFull(),
                    'start_date' => $class->start_date,
                    'end_date' => $class->end_date,
                    'created_at' => $class->created_at,
                ];
            });

        return response()->json([
            'classes' => $classes,
        ]);
    }

    public function getClassDetails(ClassModel $class): JsonResponse
    {
        // Check if user is instructor of this class
        if ($class->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $class->load([
            'students',
            'assignments' => function ($query) {
                $query->orderBy('due_date', 'desc');
            },
            'enrollments.student'
        ]);

        return response()->json([
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'description' => $class->description,
                'class_code' => $class->class_code,
                'max_students' => $class->max_students,
                'enrollment_count' => $class->getEnrollmentCount(),
                'available_spots' => $class->getAvailableSpots(),
                'is_active' => $class->is_active,
                'start_date' => $class->start_date,
                'end_date' => $class->end_date,
                'students' => $class->students,
                'assignments' => $class->assignments,
                'recent_enrollments' => $class->enrollments()->with('student')->latest()->limit(5)->get(),
            ],
        ]);
    }

    // Assignment Management
    public function createAssignment(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,essay,project,presentation,discussion',
            'content' => 'nullable|array',
            'max_points' => 'required|integer|min:1|max:1000',
            'due_date' => 'required|date|after:today',
            'allow_late_submission' => 'boolean',
            'late_penalty_percent' => 'integer|min:0|max:100',
            'settings' => 'nullable|array',
        ]);

        // Verify user is instructor of this class
        $class = ClassModel::findOrFail($request->class_id);
        if ($class->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignment = Assignment::create([
            'class_id' => $request->class_id,
            'instructor_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'content' => $request->content ?? [],
            'max_points' => $request->max_points,
            'assigned_at' => now(),
            'due_date' => $request->due_date,
            'allow_late_submission' => $request->allow_late_submission ?? false,
            'late_penalty_percent' => $request->late_penalty_percent ?? 10,
            'is_published' => false,
            'settings' => $request->settings ?? [],
        ]);

        return response()->json([
            'message' => 'Assignment created successfully',
            'assignment' => $assignment->load(['class', 'instructor']),
        ], 201);
    }

    public function getClassAssignments(ClassModel $class): JsonResponse
    {
        // Check if user is instructor of this class
        if ($class->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignments = $class->assignments()
            ->with(['submissions.student', 'grades'])
            ->orderBy('due_date', 'desc')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'type' => $assignment->type,
                    'max_points' => $assignment->max_points,
                    'due_date' => $assignment->due_date,
                    'is_published' => $assignment->is_published,
                    'is_overdue' => $assignment->isOverdue(),
                    'submission_count' => $assignment->getSubmissionCount(),
                    'graded_count' => $assignment->getGradedCount(),
                    'average_grade' => $assignment->getAverageGrade(),
                    'created_at' => $assignment->created_at,
                ];
            });

        return response()->json([
            'assignments' => $assignments,
        ]);
    }

    // Grading System
    public function getAssignmentSubmissions(Assignment $assignment): JsonResponse
    {
        // Check if user is instructor of this assignment's class
        if ($assignment->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $submissions = $assignment->submissions()
            ->with(['student', 'grade'])
            ->where('status', '!=', 'draft')
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'student' => $submission->student,
                    'content' => $submission->content,
                    'notes' => $submission->notes,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at,
                    'is_late' => $submission->is_late,
                    'grade' => $submission->grade,
                ];
            });

        return response()->json([
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'max_points' => $assignment->max_points,
                'due_date' => $assignment->due_date,
            ],
            'submissions' => $submissions,
        ]);
    }

    public function gradeSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission): JsonResponse
    {
        // Check if user is instructor of this assignment's class
        if ($assignment->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'points_earned' => 'required|numeric|min:0|max:' . $assignment->max_points,
            'feedback' => 'nullable|string',
            'rubric_scores' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        // Create or update grade
        $grade = Grade::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $submission->student_id,
            ],
            [
                'graded_by' => auth()->id(),
                'points_earned' => $request->points_earned,
                'points_possible' => $assignment->max_points,
                'feedback' => $request->feedback,
                'rubric_scores' => $request->rubric_scores ?? [],
                'graded_at' => now(),
                'is_published' => $request->is_published ?? false,
            ]
        );

        if ($grade->is_published) {
            $grade->publish();
        }

        return response()->json([
            'message' => 'Grade saved successfully',
            'grade' => $grade->load(['student', 'grader']),
        ]);
    }

    // Class Enrollment Management
    public function enrollStudent(Request $request, ClassModel $class): JsonResponse
    {
        // Check if user is instructor of this class
        if ($class->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        // Check if class can accept more students
        if ($class->isFull()) {
            return response()->json(['message' => 'Class is full'], 400);
        }

        // Check if student is already enrolled
        $existingEnrollment = ClassEnrollment::where('class_id', $class->id)
            ->where('student_id', $request->student_id)
            ->first();

        if ($existingEnrollment) {
            return response()->json(['message' => 'Student is already enrolled'], 400);
        }

        $enrollment = ClassEnrollment::create([
            'class_id' => $class->id,
            'student_id' => $request->student_id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Student enrolled successfully',
            'enrollment' => $enrollment->load(['student', 'class']),
        ], 201);
    }

    public function getInstructorDashboard(): JsonResponse
    {
        $instructorId = auth()->id();

        $classes = ClassModel::where('instructor_id', $instructorId)->get();
        $totalStudents = ClassEnrollment::whereIn('class_id', $classes->pluck('id'))
            ->where('status', 'active')
            ->count();

        $assignments = Assignment::where('instructor_id', $instructorId)->get();
        $pendingGrades = Grade::whereIn('assignment_id', $assignments->pluck('id'))
            ->where('is_published', false)
            ->count();

        $recentSubmissions = AssignmentSubmission::whereIn('assignment_id', $assignments->pluck('id'))
            ->with(['assignment', 'student'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'dashboard' => [
                'total_classes' => $classes->count(),
                'total_students' => $totalStudents,
                'total_assignments' => $assignments->count(),
                'pending_grades' => $pendingGrades,
                'recent_submissions' => $recentSubmissions,
                'active_classes' => $classes->where('is_active', true)->count(),
            ],
        ]);
    }
}
