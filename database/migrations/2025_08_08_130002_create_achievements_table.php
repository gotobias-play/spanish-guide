<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('badge_icon')->default('🏆');
            $table->string('badge_color')->default('#FFD700');
            $table->integer('points_required')->default(0);
            $table->string('condition_type'); // 'points', 'quizzes_completed', 'streak', 'perfect_score', etc.
            $table->json('condition_value'); // Store condition parameters
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};