<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');

            $table->string('payroll_month');
            $table->integer('payroll_year');

            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);

            $table->decimal('gross_pay', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2)->default(0);

            $table->string('status')->default('Pending');
            $table->date('payment_date')->nullable();
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
        Schema::dropIfExists('payrolls');
    }
};