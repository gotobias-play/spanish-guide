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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenger_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('challenged_id')->constrained('users')->onDelete('cascade');
            $table->enum('challenge_type', ['quiz_duel', 'streak_competition', 'points_race', 'course_race'])->default('quiz_duel');
            $table->enum('status', ['pending', 'accepted', 'declined', 'active', 'completed', 'cancelled'])->default('pending');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('challenge_config'); // Quiz ID, target points, duration, etc.
            $table->json('challenger_result')->nullable(); // Challenger's performance
            $table->json('challenged_result')->nullable(); // Challenged user's performance
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Index for performance
            $table->index(['challenger_id', 'status']);
            $table->index(['challenged_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
