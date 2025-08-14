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
        Schema::create('quiz_room_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('quiz_room_participants')->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('question_number'); // Question order in this room
            $table->text('answer'); // User's answer (selected option, text input, etc.)
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->integer('speed_bonus')->default(0);
            $table->float('response_time')->nullable(); // Time taken to answer in seconds
            $table->timestamp('answered_at');
            $table->json('answer_data')->nullable(); // Additional data like drag-drop positions
            $table->timestamps();
            
            $table->unique(['participant_id', 'question_id']);
            $table->index(['quiz_room_id', 'question_number']);
            $table->index(['participant_id', 'answered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_room_answers');
    }
};
