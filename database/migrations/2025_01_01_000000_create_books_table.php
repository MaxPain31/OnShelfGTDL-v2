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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->unique();
            $table->string('book_name');
            $table->string('category')->nullable();
            $table->string('authors_name')->nullable();
            $table->string('book_shelf')->nullable();
            $table->string('copyright')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('publication_name')->nullable();
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

