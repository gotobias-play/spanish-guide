<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Instructor roles table
        Schema::create('instructor_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('role_type', ['instructor', 'head_instructor', 'department_admin']);
            $table->string('department')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('appointed_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'tenant_id']);
            $table->index(['role_type', 'tenant_id']);
        });

        // Classes/Groups table
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('class_code', 10)->unique();
            $table->integer('max_students')->default(30);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Class-specific settings
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'instructor_id']);
            $table->index('class_code');
        });

        // Class enrollments table
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'completed', 'withdrawn']);
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('enrollment_data')->nullable(); // Additional enrollment info
            $table->timestamps();
            
            $table->unique(['class_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        // Assignments table
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'essay', 'project', 'presentation', 'discussion']);
            $table->json('content')->nullable(); // Assignment-specific content/questions
            $table->integer('max_points')->default(100);
            $table->timestamp('assigned_at');
            $table->timestamp('due_date');
            $table->boolean('allow_late_submission')->default(false);
            $table->integer('late_penalty_percent')->default(10);
            $table->boolean('is_published')->default(false);
            $table->json('settings')->nullable(); // Assignment-specific settings
            $table->timestamps();
            
            $table->index(['class_id', 'is_published']);
            $table->index('due_date');
        });

        // Student assignment submissions table
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->json('content')->nullable(); // Student's submission content
            $table->text('notes')->nullable(); // Student's notes
            $table->json('attachments')->nullable(); // File attachments info
            $table->enum('status', ['draft', 'submitted', 'graded', 'returned']);
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->timestamps();
            
            $table->unique(['assignment_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        // Grades table
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('graded_by')->constrained('users')->onDelete('cascade');
            $table->integer('points_earned');
            $table->integer('points_possible');
            $table->decimal('percentage', 5, 2);
            $table->string('letter_grade', 2)->nullable();
            $table->text('feedback')->nullable();
            $table->json('rubric_scores')->nullable(); // Detailed rubric scoring
            $table->timestamp('graded_at');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            
            $table->unique(['assignment_id', 'student_id']);
            $table->index(['student_id', 'is_published']);
        });

        // Class announcements table
        Schema::create('class_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('send_notification')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['class_id', 'published_at']);
        });

        // Attendance tracking table
        Schema::create('class_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['class_id', 'student_id', 'attendance_date']);
            $table->index(['student_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_attendance');
        Schema::dropIfExists('class_announcements');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('class_enrollments');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('instructor_roles');
    }
};
