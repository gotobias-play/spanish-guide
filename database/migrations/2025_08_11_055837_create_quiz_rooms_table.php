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
        Schema::create('quiz_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_code', 6)->unique(); // Unique 6-character room code
            $table->string('name'); // Room name
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->integer('max_participants')->default(8);
            $table->enum('status', ['waiting', 'starting', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->integer('current_question')->default(0);
            $table->timestamp('question_started_at')->nullable();
            $table->integer('question_time_limit')->default(30); // seconds per question
            $table->boolean('is_public')->default(true);
            $table->json('room_settings')->nullable(); // Additional settings like scoring rules, etc.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'is_public']);
            $table->index('room_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_rooms');
    }
};
