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
        Schema::create('user_skill_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('skill_category'); // grammar, vocabulary, listening, reading, etc.
            $table->string('skill_topic'); // present_simple, prepositions, food_vocabulary, etc.
            $table->enum('difficulty_level', ['beginner', 'elementary', 'intermediate', 'advanced'])->default('beginner');
            $table->decimal('mastery_score', 5, 2)->default(0); // 0-100 mastery percentage
            $table->integer('correct_answers')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->decimal('accuracy_rate', 5, 2)->default(0); // Calculated accuracy percentage
            $table->timestamp('last_practiced_at')->nullable();
            $table->integer('consecutive_correct')->default(0); // Streak of correct answers
            $table->json('performance_history')->nullable(); // Recent performance data
            $table->timestamps();
            
            // Unique constraint for user + skill combination
            $table->unique(['user_id', 'skill_category', 'skill_topic']);
            
            // Indexes for queries
            $table->index(['user_id', 'mastery_score']);
            $table->index(['user_id', 'last_practiced_at']);
            $table->index(['skill_category', 'difficulty_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_skill_levels');
    }
};
