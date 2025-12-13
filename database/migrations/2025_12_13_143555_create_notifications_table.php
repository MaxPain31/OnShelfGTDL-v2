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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // 'book_borrowed', 'book_reserved', 'due_date_approaching', 'claim_deadline_approaching', 'reservation_expired'
            $table->string('title');
            $table->text('message');
            $table->nullableMorphs('related'); // related_id, related_type (for BookBorrow, BookReservation, etc.)
            $table->json('data')->nullable(); // Additional data like book name, dates, etc.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
