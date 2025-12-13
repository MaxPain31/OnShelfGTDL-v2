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
        Schema::create('book_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('reserve_date'); // Date user wants to reserve the book
            $table->date('due_date'); // Date user wants to return the book
            $table->date('claim_deadline'); // 3 days from reservation creation
            $table->enum('status', ['pending', 'claimed', 'voided'])->default('pending');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['user_id', 'status']);
            $table->index(['book_id', 'status']);
            $table->index('claim_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_reservations');
    }
};
