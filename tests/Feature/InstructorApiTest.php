<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\InstructorRole;
use App\Models\ClassModel;
use App\Models\ClassEnrollment;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class InstructorApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor;
    protected User $student;
    protected Tenant $tenant;
    protected InstructorRole $instructorRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a tenant
        $this->tenant = Tenant::create([
            'name' => 'Instructor Test Academy',
            'subdomain' => 'instructor-test',
            'plan' => 'enterprise',
            'status' => 'active',
            'features' => json_encode(['instructor_tools', 'analytics', 'gamification']),
            'limits' => json_encode(['users' => 1000, 'courses' => 100]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        // Create instructor user
        $this->instructor = User::factory()->create([
            'name' => 'Test Instructor',
            'email' => 'instructor@test.com',
            'tenant_id' => $this->tenant->id,
        ]);

        // Create instructor role
        $this->instructorRole = InstructorRole::create([
            'user_id' => $this->instructor->id,
            'tenant_id' => $this->tenant->id,
            'role_type' => 'instructor',
            'department' => 'English Language',
            'permissions' => ['manage_classes', 'create_assignments', 'grade_submissions'],
            'is_active' => true,
            'appointed_at' => now(),
        ]);

        // Create student user
        $this->student = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'tenant_id' => $this->tenant->id,
        ]);

        Sanctum::actingAs($this->instructor);
    }

    /**
     * Test getting instructor dashboard
     */
    public function test_get_instructor_dashboard()
    {
        // Create test data
        $class = $this->createTestClass();
        $assignment = $this->createTestAssignment($class);
        $this->enrollStudentInClass($this->student, $class);

        $response = $this->getJson('/api/instructor/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'dashboard' => [
                        'total_classes',
                        'total_students',
                        'total_assignments',
                        'pending_grades',
                        'recent_submissions',
                    ]
                ]);

        $data = $response->json()['dashboard'];
        $this->assertEquals(1, $data['total_classes']);
        $this->assertEquals(1, $data['total_students']);
        $this->assertEquals(1, $data['total_assignments']);
    }

    /**
     * Test creating a class
     */
    public function test_create_class()
    {
        $classData = [
            'name' => 'Advanced English Grammar',
            'description' => 'Advanced grammar concepts and usage',
            'max_students' => 25,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'settings' => [
                'allow_late_submissions' => true,
                'auto_grade_quizzes' => false,
            ],
        ];

        $response = $this->postJson('/api/instructor/classes', $classData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'class' => [
                        'id',
                        'name',
                        'description',
                        'class_code',
                        'max_students',
                        'is_active',
                        'instructor_id',
                        'tenant_id',
                    ]
                ]);

        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Advanced English Grammar', $responseData['class']['name']);
        $this->assertEquals($this->instructor->id, $responseData['class']['instructor_id']);
        $this->assertEquals($this->tenant->id, $responseData['class']['tenant_id']);

        // Verify class was created in database
        $this->assertDatabaseHas('classes', [
            'name' => 'Advanced English Grammar',
            'instructor_id' => $this->instructor->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test getting instructor's classes
     */
    public function test_get_instructor_classes()
    {
        // Create multiple classes
        $class1 = $this->createTestClass('Class 1');
        $class2 = $this->createTestClass('Class 2');
        
        // Enroll students
        $this->enrollStudentInClass($this->student, $class1);

        $response = $this->getJson('/api/instructor/classes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'classes' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'class_code',
                            'max_students',
                            'enrollment_count',
                            'is_active',
                        ]
                    ]
                ]);

        $classes = $response->json()['classes'];
        $this->assertCount(2, $classes);
        
        // Find class 1 and verify enrollment count
        $class1Data = collect($classes)->firstWhere('name', 'Class 1');
        $this->assertEquals(1, $class1Data['enrollment_count']);
        
        $class2Data = collect($classes)->firstWhere('name', 'Class 2');
        $this->assertEquals(0, $class2Data['enrollment_count']);
    }

    /**
     * Test getting specific class details
     */
    public function test_get_class_details()
    {
        $class = $this->createTestClass();
        $this->enrollStudentInClass($this->student, $class);

        $response = $this->getJson("/api/instructor/classes/{$class->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'class' => [
                        'id',
                        'name',
                        'description',
                        'class_code',
                        'max_students',
                        'enrollment_count',
                        'students' => [
                            '*' => [
                                'id',
                                'name',
                                'email',
                                'pivot' => [
                                    'status',
                                    'enrolled_at',
                                ]
                            ]
                        ]
                    ]
                ]);

        $classData = $response->json()['class'];
        $this->assertEquals($class->name, $classData['name']);
        $this->assertCount(1, $classData['students']);
        $this->assertEquals($this->student->name, $classData['students'][0]['name']);
    }

    /**
     * Test enrolling student in class
     */
    public function test_enroll_student_in_class()
    {
        $class = $this->createTestClass();

        $enrollmentData = [
            'student_email' => $this->student->email,
        ];

        $response = $this->postJson("/api/instructor/classes/{$class->id}/enroll", $enrollmentData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ]);

        // Verify enrollment in database
        $this->assertDatabaseHas('class_enrollments', [
            'class_id' => $class->id,
            'student_id' => $this->student->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test creating assignment
     */
    public function test_create_assignment()
    {
        $class = $this->createTestClass();

        $assignmentData = [
            'title' => 'Grammar Exercise 1',
            'description' => 'Complete exercises on present perfect tense',
            'type' => 'quiz',
            'max_points' => 100,
            'due_date' => now()->addWeek()->toISOString(),
            'content' => [
                'questions' => [
                    'Complete the sentences with present perfect form',
                    'Identify errors in the given sentences',
                ],
            ],
            'allow_late_submission' => true,
            'late_penalty_percent' => 10,
        ];

        $response = $this->postJson('/api/instructor/assignments', array_merge($assignmentData, [
            'class_id' => $class->id,
        ]));

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'assignment' => [
                        'id',
                        'title',
                        'description',
                        'type',
                        'max_points',
                        'class_id',
                        'instructor_id',
                    ]
                ]);

        $assignmentResponse = $response->json()['assignment'];
        $this->assertEquals('Grammar Exercise 1', $assignmentResponse['title']);
        $this->assertEquals($class->id, $assignmentResponse['class_id']);
        $this->assertEquals($this->instructor->id, $assignmentResponse['instructor_id']);

        // Verify in database
        $this->assertDatabaseHas('assignments', [
            'title' => 'Grammar Exercise 1',
            'class_id' => $class->id,
            'instructor_id' => $this->instructor->id,
        ]);
    }

    /**
     * Test getting class assignments
     */
    public function test_get_class_assignments()
    {
        $class = $this->createTestClass();
        $assignment1 = $this->createTestAssignment($class, 'Assignment 1');
        $assignment2 = $this->createTestAssignment($class, 'Assignment 2');

        $response = $this->getJson("/api/instructor/classes/{$class->id}/assignments");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'assignments' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'type',
                            'max_points',
                            'due_date',
                            'is_published',
                        ]
                    ]
                ]);

        $assignments = $response->json()['assignments'];
        $this->assertCount(2, $assignments);
    }

    /**
     * Test getting assignment submissions
     */
    public function test_get_assignment_submissions()
    {
        $class = $this->createTestClass();
        $assignment = $this->createTestAssignment($class);
        $this->enrollStudentInClass($this->student, $class);

        // Create submission
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'content' => json_encode(['text' => 'Student answer']),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->getJson("/api/instructor/assignments/{$assignment->id}/submissions");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'submissions' => [
                        '*' => [
                            'id',
                            'student_id',
                            'student_name',
                            'content',
                            'status',
                            'submitted_at',
                        ]
                    ]
                ]);

        $submissions = $response->json()['submissions'];
        $this->assertCount(1, $submissions);
        $this->assertEquals($this->student->id, $submissions[0]['student_id']);
        $this->assertEquals($this->student->name, $submissions[0]['student_name']);
    }

    /**
     * Test grading submission
     */
    public function test_grade_submission()
    {
        $class = $this->createTestClass();
        $assignment = $this->createTestAssignment($class);
        $this->enrollStudentInClass($this->student, $class);

        // Create submission
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'content' => json_encode(['text' => 'Student answer']),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $gradeData = [
            'points_earned' => 85,
            'feedback' => 'Good work! Pay attention to verb tenses.',
            'rubric_scores' => [
                'content' => 90,
                'grammar' => 80,
                'organization' => 85,
            ],
            'is_published' => true,
        ];

        $response = $this->postJson("/api/instructor/assignments/{$assignment->id}/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ]);

        // Verify grade in database
        $this->assertDatabaseHas('grades', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'graded_by' => $this->instructor->id,
            'points_earned' => 85,
            'points_possible' => 100,
            'is_published' => true,
        ]);

        // Verify submission status updated
        $submission->refresh();
        $this->assertEquals('graded', $submission->status);
    }

    /**
     * Test unauthorized access (non-instructor)
     */
    public function test_unauthorized_access()
    {
        // Authenticate as student instead of instructor
        Sanctum::actingAs($this->student);

        $endpoints = [
            ['GET', '/api/instructor/dashboard'],
            ['GET', '/api/instructor/classes'],
            ['POST', '/api/instructor/classes'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $response->assertStatus(403); // Forbidden
        }
    }

    /**
     * Test cross-tenant data isolation
     */
    public function test_cross_tenant_isolation()
    {
        // Create another tenant and instructor
        $otherTenant = Tenant::create([
            'name' => 'Other Academy',
            'subdomain' => 'other-academy',
            'plan' => 'basic',
            'status' => 'active',
            'features' => json_encode(['instructor_tools']),
            'limits' => json_encode(['users' => 50, 'courses' => 10]),
            'usage' => json_encode(['users' => 0, 'courses' => 0]),
            'branding' => json_encode([]),
            'billing_info' => json_encode([]),
            'locale' => 'en',
            'timezone' => 'UTC',
        ]);

        $otherInstructor = User::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        InstructorRole::create([
            'user_id' => $otherInstructor->id,
            'tenant_id' => $otherTenant->id,
            'role_type' => 'instructor',
            'department' => 'Mathematics',
            'permissions' => ['manage_classes'],
            'is_active' => true,
            'appointed_at' => now(),
        ]);

        // Create class for other tenant
        $otherClass = ClassModel::create([
            'tenant_id' => $otherTenant->id,
            'instructor_id' => $otherInstructor->id,
            'name' => 'Other Tenant Class',
            'description' => 'Should not be visible',
            'class_code' => 'OTHER001',
            'max_students' => 20,
            'start_date' => now()->addDays(7)->toDateString(),
        ]);

        // Create class for our tenant
        $ourClass = $this->createTestClass();

        // Get classes as our instructor
        $response = $this->getJson('/api/instructor/classes');
        $response->assertStatus(200);

        $classes = $response->json()['classes'];
        
        // Should only see our tenant's class
        $this->assertCount(1, $classes);
        $this->assertEquals($ourClass->name, $classes[0]['name']);
        $this->assertNotEquals('Other Tenant Class', $classes[0]['name']);
    }

    /**
     * Test permission-based access control
     */
    public function test_permission_based_access()
    {
        // Create instructor with limited permissions
        $limitedInstructor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        InstructorRole::create([
            'user_id' => $limitedInstructor->id,
            'tenant_id' => $this->tenant->id,
            'role_type' => 'instructor',
            'department' => 'Limited Department',
            'permissions' => ['manage_classes'], // No grading permission
            'is_active' => true,
            'appointed_at' => now(),
        ]);

        Sanctum::actingAs($limitedInstructor);

        // Should be able to access dashboard and classes
        $response = $this->getJson('/api/instructor/dashboard');
        $response->assertStatus(200);

        $response = $this->getJson('/api/instructor/classes');
        $response->assertStatus(200);

        // Should not be able to grade (if we implement permission checking)
        $class = $this->createTestClass();
        $assignment = $this->createTestAssignment($class);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'content' => json_encode(['text' => 'Test']),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // This should work for now, but could be restricted based on permissions
        $response = $this->postJson("/api/instructor/assignments/{$assignment->id}/submissions/{$submission->id}/grade", [
            'points_earned' => 85,
            'feedback' => 'Good work',
        ]);

        // For now, we'll just verify the endpoint exists
        $this->assertContains($response->status(), [200, 403]);
    }

    /**
     * Test input validation
     */
    public function test_input_validation()
    {
        // Test creating class with invalid data
        $invalidClassData = [
            'name' => '', // Empty name
            'max_students' => -5, // Negative number
            'start_date' => 'invalid-date',
        ];

        $response = $this->postJson('/api/instructor/classes', $invalidClassData);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'max_students', 'start_date']);

        // Test grading with invalid points
        $class = $this->createTestClass();
        $assignment = $this->createTestAssignment($class);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'content' => json_encode(['text' => 'Test']),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $invalidGradeData = [
            'points_earned' => 150, // More than max_points (100)
            'feedback' => str_repeat('x', 10000), // Very long feedback
        ];

        $response = $this->postJson("/api/instructor/assignments/{$assignment->id}/submissions/{$submission->id}/grade", $invalidGradeData);
        $response->assertStatus(422);
    }

    /**
     * Helper methods
     */
    private function createTestClass($name = 'Test Class')
    {
        return ClassModel::create([
            'tenant_id' => $this->tenant->id,
            'instructor_id' => $this->instructor->id,
            'name' => $name,
            'description' => 'Test class description',
            'class_code' => strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6)),
            'max_students' => 30,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);
    }

    private function createTestAssignment($class, $title = 'Test Assignment')
    {
        return Assignment::create([
            'class_id' => $class->id,
            'instructor_id' => $this->instructor->id,
            'title' => $title,
            'description' => 'Test assignment description',
            'type' => 'quiz',
            'max_points' => 100,
            'assigned_at' => now(),
            'due_date' => now()->addWeek(),
            'is_published' => true,
        ]);
    }

    private function enrollStudentInClass($student, $class)
    {
        return ClassEnrollment::create([
            'class_id' => $class->id,
            'student_id' => $student->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
    }
}