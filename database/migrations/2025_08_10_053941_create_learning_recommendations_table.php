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
        Schema::create('learning_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('recommendation_type', ['skill_improvement', 'review_reminder', 'new_content', 'difficulty_adjustment', 'streak_motivation']);
            $table->string('title'); // "Practice Present Simple Tense"
            $table->text('description'); // Detailed recommendation explanation
            $table->string('action_type'); // quiz, lesson, review, practice
            $table->json('action_data'); // Quiz ID, lesson ID, skill topic, etc.
            $table->integer('priority')->default(1); // 1 (highest) to 5 (lowest)
            $table->decimal('confidence_score', 5, 2)->default(0); // AI confidence in recommendation
            $table->boolean('is_dismissed')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['user_id', 'is_dismissed', 'is_completed']);
            $table->index(['recommendation_type', 'priority']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_recommendations');
    }
};
