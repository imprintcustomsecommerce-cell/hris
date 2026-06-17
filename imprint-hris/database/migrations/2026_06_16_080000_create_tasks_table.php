<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to');           // employees.id (recipient)
            $table->foreignId('assigned_by');           // users.id (assigner: HR/Manager/Admin)
            $table->string('priority')->default('Medium'); // Low / Medium / High
            $table->date('due_date')->nullable();
            $table->string('status')->default('Pending');  // Pending / In Progress / Completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
