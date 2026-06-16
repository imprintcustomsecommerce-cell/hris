<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->date('birthdate')->nullable();
            $table->text('address')->nullable();

            // Employment Details
            $table->string('employee_id')->unique();
            $table->string('department');
            $table->string('position');
            $table->date('date_hired');
            $table->string('employment_type')->nullable();
            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};