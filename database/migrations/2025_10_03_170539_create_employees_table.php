<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->ulid('id')->primary();  

            // Explicitly reference organizations table
            $table->foreignUlid('organization_id')
                  ->constrained('organizations')
                  ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('designation')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->date('joining_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
