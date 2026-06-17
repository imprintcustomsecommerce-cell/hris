<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->foreignId('reviewer_id')->nullable(); // users.id
            $table->string('reviewer_name')->nullable();
            $table->string('period');                      // e.g. "2026 H1", "Q2 2026"
            $table->unsignedTinyInteger('rating_quality')->default(3);
            $table->unsignedTinyInteger('rating_productivity')->default(3);
            $table->unsignedTinyInteger('rating_teamwork')->default(3);
            $table->unsignedTinyInteger('rating_punctuality')->default(3);
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('comments')->nullable();
            $table->string('status')->default('Draft');    // Draft / Finalized
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
