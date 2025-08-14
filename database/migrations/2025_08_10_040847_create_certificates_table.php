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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('certificate_code')->unique(); // Unique identifier like "CERT-ENG-001-2025"
            $table->string('course_title');
            $table->string('user_name');
            $table->integer('total_quizzes');
            $table->integer('completed_quizzes');
            $table->decimal('average_score', 5, 2);
            $table->integer('total_points_earned');
            $table->timestamp('course_started_at')->nullable();
            $table->timestamp('course_completed_at');
            $table->json('quiz_results'); // Detailed results for each quiz
            $table->json('certificate_data')->nullable(); // Additional certificate metadata
            $table->timestamps();
            
            // Ensure user can only get one certificate per course
            $table->unique(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
