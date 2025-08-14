<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\InstructorRole;
use App\Models\ClassModel;
use App\Models\ClassEnrollment;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first tenant for demonstration
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->command->error('No tenants found. Please run TenantSeeder first.');
            return;
        }

        $this->command->info('Creating instructor data for tenant: ' . $tenant->name);

        // Create instructor users
        $instructorUsers = collect([
            [
                'name' => 'Prof. María García',
                'email' => 'maria.garcia@instructor.demo',
                'password' => bcrypt('password'),
                'tenant_id' => $tenant->id,
                'is_admin' => false,
            ],
            [
                'name' => 'Dr. Carlos Rodríguez',
                'email' => 'carlos.rodriguez@instructor.demo',
                'password' => bcrypt('password'),
                'tenant_id' => $tenant->id,
                'is_admin' => false,
            ],
            [
                'name' => 'Prof. Ana Martínez',
                'email' => 'ana.martinez@instructor.demo',
                'password' => bcrypt('password'),
                'tenant_id' => $tenant->id,
                'is_admin' => false,
            ],
        ])->map(function ($userData) {
            return User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        });

        // Create instructor roles
        $instructorRoles = [
            [
                'user_id' => $instructorUsers[0]->id,
                'role_type' => 'head_instructor',
                'department' => 'English Language',
                'permissions' => ['manage_classes', 'create_assignments', 'grade_submissions', 'manage_enrollments', 'view_analytics'],
            ],
            [
                'user_id' => $instructorUsers[1]->id,
                'role_type' => 'instructor',
                'department' => 'Grammar & Writing',
                'permissions' => ['manage_classes', 'create_assignments', 'grade_submissions'],
            ],
            [
                'user_id' => $instructorUsers[2]->id,
                'role_type' => 'instructor',
                'department' => 'Conversation & Speaking',
                'permissions' => ['manage_classes', 'create_assignments', 'grade_submissions'],
            ],
        ];

        foreach ($instructorRoles as $roleData) {
            InstructorRole::firstOrCreate(
                [
                    'user_id' => $roleData['user_id'],
                    'tenant_id' => $tenant->id,
                ],
                array_merge($roleData, [
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'appointed_at' => now()->subDays(30),
                ])
            );
        }

        // Create sample classes
        $classes = [
            [
                'instructor_id' => $instructorUsers[0]->id,
                'name' => 'Intermediate English Grammar',
                'description' => 'Comprehensive grammar course covering verb tenses, sentence structure, and advanced grammar rules.',
                'max_students' => 25,
                'start_date' => now()->subDays(20)->toDateString(),
                'end_date' => now()->addDays(40)->toDateString(),
                'settings' => ['allow_late_submissions' => true, 'auto_grade_quizzes' => false],
            ],
            [
                'instructor_id' => $instructorUsers[1]->id,
                'name' => 'Business English Writing',
                'description' => 'Professional writing skills for business communications, emails, reports, and presentations.',
                'max_students' => 20,
                'start_date' => now()->subDays(15)->toDateString(),
                'end_date' => now()->addDays(50)->toDateString(),
                'settings' => ['require_submission_approval' => true, 'feedback_required' => true],
            ],
            [
                'instructor_id' => $instructorUsers[2]->id,
                'name' => 'English Conversation Practice',
                'description' => 'Interactive speaking practice with focus on pronunciation, fluency, and confidence building.',
                'max_students' => 15,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(35)->toDateString(),
                'settings' => ['record_sessions' => true, 'peer_feedback' => true],
            ],
        ];

        $createdClasses = collect($classes)->map(function ($classData) use ($tenant) {
            return ClassModel::firstOrCreate(
                [
                    'instructor_id' => $classData['instructor_id'],
                    'name' => $classData['name'],
                ],
                array_merge($classData, [
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                ])
            );
        });

        // Create sample students and enroll them in classes
        $studentUsers = collect([
            ['name' => 'Juan Pérez', 'email' => 'juan.perez@student.demo'],
            ['name' => 'María González', 'email' => 'maria.gonzalez@student.demo'],
            ['name' => 'Carlos López', 'email' => 'carlos.lopez@student.demo'],
            ['name' => 'Ana Fernández', 'email' => 'ana.fernandez@student.demo'],
            ['name' => 'Luis Moreno', 'email' => 'luis.moreno@student.demo'],
            ['name' => 'Carmen Silva', 'email' => 'carmen.silva@student.demo'],
        ])->map(function ($userData) use ($tenant) {
            return User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => bcrypt('password'),
                    'tenant_id' => $tenant->id,
                    'is_admin' => false,
                ])
            );
        });

        // Enroll students in classes
        foreach ($createdClasses as $class) {
            $studentsToEnroll = $studentUsers->random(rand(3, 5));
            foreach ($studentsToEnroll as $student) {
                ClassEnrollment::firstOrCreate(
                    [
                        'class_id' => $class->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => 'active',
                        'enrolled_at' => now()->subDays(rand(1, 15)),
                        'enrollment_data' => ['enrollment_method' => 'instructor_added'],
                    ]
                );
            }
        }

        // Create sample assignments
        $assignments = [
            [
                'class_id' => $createdClasses[0]->id,
                'instructor_id' => $instructorUsers[0]->id,
                'title' => 'Grammar Exercise: Present Perfect Tense',
                'description' => 'Complete the exercises on present perfect tense usage and form.',
                'type' => 'quiz',
                'max_points' => 100,
                'due_date' => now()->addDays(7),
                'content' => [
                    'questions' => [
                        'Complete the sentences with present perfect form',
                        'Identify errors in the given sentences',
                        'Write three sentences using present perfect',
                    ],
                ],
            ],
            [
                'class_id' => $createdClasses[1]->id,
                'instructor_id' => $instructorUsers[1]->id,
                'title' => 'Business Email Writing',
                'description' => 'Write a professional business email following the provided scenario.',
                'type' => 'essay',
                'max_points' => 150,
                'due_date' => now()->addDays(10),
                'content' => [
                    'scenario' => 'You are requesting a meeting with a client to discuss a new project proposal.',
                    'requirements' => ['Professional tone', 'Clear subject line', 'Proper formatting', 'Call to action'],
                ],
            ],
            [
                'class_id' => $createdClasses[2]->id,
                'instructor_id' => $instructorUsers[2]->id,
                'title' => 'Pronunciation Practice Recording',
                'description' => 'Record yourself reading the provided text and submit for feedback.',
                'type' => 'presentation',
                'max_points' => 75,
                'due_date' => now()->addDays(5),
                'content' => [
                    'text' => 'The quick brown fox jumps over the lazy dog. She sells seashells by the seashore.',
                    'focus_areas' => ['Clear pronunciation', 'Natural rhythm', 'Proper intonation'],
                ],
            ],
        ];

        $createdAssignments = collect($assignments)->map(function ($assignmentData) {
            return Assignment::firstOrCreate(
                [
                    'class_id' => $assignmentData['class_id'],
                    'title' => $assignmentData['title'],
                ],
                array_merge($assignmentData, [
                    'assigned_at' => now()->subDays(rand(1, 5)),
                    'is_published' => true,
                    'allow_late_submission' => true,
                    'late_penalty_percent' => 10,
                    'settings' => ['auto_grade' => false, 'anonymous_grading' => false],
                ])
            );
        });

        // Create sample submissions and grades
        foreach ($createdAssignments as $assignment) {
            $enrolledStudents = ClassEnrollment::where('class_id', $assignment->class_id)
                ->where('status', 'active')
                ->with('student')
                ->get();

            foreach ($enrolledStudents->random(rand(2, 4)) as $enrollment) {
                $student = $enrollment->student;
                
                // Create submission
                $submission = AssignmentSubmission::firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'content' => [
                            'text' => 'Sample student submission for ' . $assignment->title,
                            'additional_notes' => 'Submitted on time with all requirements met.',
                        ],
                        'status' => 'submitted',
                        'submitted_at' => now()->subDays(rand(1, 3)),
                        'is_late' => false,
                        'notes' => 'Good effort on this assignment.',
                    ]
                );

                // Create grade (for some submissions)
                if (rand(1, 3) <= 2) { // 2/3 chance of being graded
                    $pointsEarned = rand(60, 100) / 100 * $assignment->max_points;
                    
                    Grade::firstOrCreate(
                        [
                            'assignment_id' => $assignment->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'graded_by' => $assignment->instructor_id,
                            'points_earned' => $pointsEarned,
                            'points_possible' => $assignment->max_points,
                            'feedback' => 'Good work! Keep focusing on the areas we discussed in class.',
                            'rubric_scores' => [
                                'content' => rand(70, 100),
                                'grammar' => rand(60, 95),
                                'organization' => rand(75, 100),
                                'effort' => rand(80, 100),
                            ],
                            'graded_at' => now()->subDays(rand(0, 2)),
                            'is_published' => true,
                        ]
                    );

                    $submission->update(['status' => 'graded']);
                }
            }
        }

        $this->command->info('✅ Instructor system seeded successfully!');
        $this->command->info('📊 Created:');
        $this->command->info('   - 3 Instructor users with roles');
        $this->command->info('   - 6 Student users');
        $this->command->info('   - 3 Classes with enrollments');
        $this->command->info('   - 3 Assignments with submissions');
        $this->command->info('   - Sample grades and feedback');
        $this->command->line('');
        $this->command->info('🔑 Instructor Login Credentials:');
        $this->command->info('   - maria.garcia@instructor.demo / password (Head Instructor)');
        $this->command->info('   - carlos.rodriguez@instructor.demo / password (Instructor)');
        $this->command->info('   - ana.martinez@instructor.demo / password (Instructor)');
    }
}
