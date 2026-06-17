<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('sss', 12, 2)->default(0)->after('deductions');
            $table->decimal('philhealth', 12, 2)->default(0)->after('sss');
            $table->decimal('pagibig', 12, 2)->default(0)->after('philhealth');
            $table->decimal('tax', 12, 2)->default(0)->after('pagibig');
            $table->decimal('other_deductions', 12, 2)->default(0)->after('tax');
        });

        // Remove any duplicate attendance rows before enforcing uniqueness.
        $dupes = DB::table('attendances')
            ->select('employee_id', 'attendance_date', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as c'))
            ->groupBy('employee_id', 'attendance_date')
            ->having('c', '>', 1)
            ->get();

        foreach ($dupes as $d) {
            DB::table('attendances')
                ->where('employee_id', $d->employee_id)
                ->where('attendance_date', $d->attendance_date)
                ->where('id', '!=', $d->keep_id)
                ->delete();
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['employee_id', 'attendance_date'], 'attendances_emp_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_emp_date_unique');
        });
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['sss', 'philhealth', 'pagibig', 'tax', 'other_deductions']);
        });
    }
};
