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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('room_id')->nullable(); // For group chats/rooms
            $table->enum('message_type', ['direct', 'room', 'system'])->default('direct');
            $table->text('message');
            $table->json('metadata')->nullable(); // For attachments, reactions, etc.
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['sender_id', 'receiver_id']);
            $table->index(['room_id', 'created_at']);
            $table->index(['message_type', 'created_at']);
            $table->index(['is_read', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
