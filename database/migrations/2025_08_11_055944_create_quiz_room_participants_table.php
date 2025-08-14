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
        Schema::create('quiz_room_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_score')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('total_questions')->default(0);
            $table->integer('position')->nullable(); // Final ranking position
            $table->float('average_response_time')->nullable(); // Average time per question
            $table->integer('speed_bonus')->default(0);
            $table->enum('status', ['joined', 'ready', 'playing', 'finished', 'disconnected'])->default('joined');
            $table->timestamp('joined_at');
            $table->timestamp('finished_at')->nullable();
            $table->json('performance_data')->nullable(); // Per-question performance stats
            $table->timestamps();
            
            $table->unique(['quiz_room_id', 'user_id']);
            $table->index(['quiz_room_id', 'status']);
            $table->index(['quiz_room_id', 'total_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_room_participants');
    }
};
