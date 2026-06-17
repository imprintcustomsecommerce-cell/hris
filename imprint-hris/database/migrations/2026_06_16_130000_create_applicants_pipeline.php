<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position_applied');
            $table->string('status')->default('Applied'); // Applied / For Interview / For Requirements / Hired / Rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id');
            $table->dateTime('scheduled_at');
            $table->string('mode')->default('Onsite');   // Onsite / Online / Phone
            $table->string('location')->nullable();       // address or meeting link
            $table->string('interviewer')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Scheduled'); // Scheduled / Completed / Cancelled
            $table->timestamps();
        });

        Schema::create('applicant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id');
            $table->string('name');
            $table->string('category')->default('Requirement');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->foreignId('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('applicant_id')->nullable()->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('applicant_id');
        });
        Schema::dropIfExists('applicant_documents');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('applicants');
    }
};
