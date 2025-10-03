<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_cocs', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Reference to employee
            $table->foreignUlid('employee_id')
                  ->constrained('employees')
                  ->cascadeOnDelete();

            $table->string('type'); // salary_change, designation_change, address_change
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('effective_date')->nullable();

            // Approved by (optional), references users.id
            $table->foreignUlid('approved_by')
                  ->nullable()
                  ->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_cocs');
    }
};
