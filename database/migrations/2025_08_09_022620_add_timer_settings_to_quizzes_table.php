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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_timed')->default(false);
            $table->integer('time_limit_seconds')->nullable(); // Total time for the entire quiz
            $table->integer('time_per_question_seconds')->nullable(); // Time per individual question
            $table->boolean('show_timer')->default(true); // Whether to show countdown to user
            $table->json('timer_settings')->nullable(); // Additional timer configurations (warnings, penalties, etc.)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'is_timed',
                'time_limit_seconds', 
                'time_per_question_seconds',
                'show_timer',
                'timer_settings'
            ]);
        });
    }
};
