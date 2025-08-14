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
        Schema::create('social_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('activity_type', [
                'quiz_completed', 
                'achievement_earned', 
                'certificate_earned', 
                'streak_milestone', 
                'challenge_sent', 
                'challenge_won',
                'friend_added'
            ]);
            $table->string('title'); // "completed Quiz X"
            $table->string('description')->nullable(); // Additional details
            $table->json('activity_data')->nullable(); // Score, points, etc.
            $table->boolean('is_public')->default(true); // Can friends see this?
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            
            // Index for performance - activities by user and recency
            $table->index(['user_id', 'occurred_at']);
            $table->index(['activity_type', 'occurred_at']);
            $table->index(['is_public', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_activities');
    }
};
