<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('manager_id');        // users.id of the assigned manager
            $table->foreignId('created_by')->nullable(); // users.id of the CEO/admin
            $table->string('creator_name')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('Pending'); // Pending / In Progress / Completed
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
        Schema::dropIfExists('projects');
    }
};
