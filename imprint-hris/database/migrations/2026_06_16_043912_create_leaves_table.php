<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');

            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days')->default(1);

            $table->text('reason')->nullable();
            $table->string('status')->default('Pending');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};