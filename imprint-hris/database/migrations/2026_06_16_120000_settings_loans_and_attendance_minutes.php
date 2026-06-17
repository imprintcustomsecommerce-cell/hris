<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->string('type')->default('Loan');          // Loan / Cash Advance
            $table->decimal('principal', 12, 2);
            $table->decimal('monthly_amortization', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->string('status')->default('Active');       // Active / Paid
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedSmallInteger('late_minutes')->default(0)->after('status');
            $table->unsignedSmallInteger('undertime_minutes')->default(0)->after('late_minutes');
            $table->unsignedSmallInteger('overtime_minutes')->default(0)->after('undertime_minutes');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('loan_deduction', 12, 2)->default(0)->after('other_deductions');
        });

        // Default settings.
        $defaults = [
            'company_name' => 'Imprint Customs',
            'shift_start' => '08:00',
            'shift_end' => '17:00',
            'grace_minutes' => '15',
            'default_vacation_credits' => '15',
            'default_sick_credits' => '15',
        ];
        foreach ($defaults as $k => $v) {
            DB::table('settings')->updateOrInsert(['key' => $k], ['value' => $v]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('loans');
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['late_minutes', 'undertime_minutes', 'overtime_minutes']);
        });
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('loan_deduction');
        });
    }
};
