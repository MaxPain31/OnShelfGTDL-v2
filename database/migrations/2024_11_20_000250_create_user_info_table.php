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
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('extension_name')->nullable();
            $table->string('lrn', 12)->nullable()->unique(); // For students
            $table->string('employee_number')->nullable()->unique(); // For teachers
            $table->string('grade')->nullable(); // For students
            $table->string('advisory_class')->nullable(); // For teachers
            $table->string('section')->nullable();
            $table->string('adviser')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->string('house_no')->nullable();
            $table->string('street_name')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->default('Philippines');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_info');
    }
};

