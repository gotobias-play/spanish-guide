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
        Schema::table('questions', function (Blueprint $table) {
            // Add support for media files (images, audio)
            $table->string('image_url')->nullable()->after('question_text');
            $table->string('audio_url')->nullable()->after('image_url');
            
            // Add JSON field for advanced question configuration
            $table->json('question_config')->nullable()->after('audio_url');
            
            // Add explanation field for detailed feedback
            $table->text('explanation')->nullable()->after('question_config');
            
            // Add difficulty level
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium')->after('explanation');
        });

        Schema::table('question_options', function (Blueprint $table) {
            // Add image support for options
            $table->string('image_url')->nullable()->after('option_text');
            
            // Add position for drag-drop and ordering questions
            $table->integer('position')->default(0)->after('image_url');
            
            // Add group for matching questions (left/right side)
            $table->string('option_group')->nullable()->after('position');
            
            // Add metadata for advanced interactions
            $table->json('option_config')->nullable()->after('option_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'audio_url', 'question_config', 'explanation', 'difficulty_level']);
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'position', 'option_group', 'option_config']);
        });
    }
};
