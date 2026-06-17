<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // HRIS system accounts
        $accounts = [
            ['name' => 'System Administrator', 'email' => 'admin@imprintcustoms.ph', 'role' => 'Admin'],
            ['name' => 'HR Officer', 'email' => 'hr@imprintcustoms.ph', 'role' => 'HR'],
            ['name' => 'Department Manager', 'email' => 'manager@imprintcustoms.ph', 'role' => 'Manager'],
            ['name' => 'Employee Portal', 'email' => 'employee@imprintcustoms.ph', 'role' => 'Employee'],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }

        // Default departments
        $departments = [
            ['name' => 'Admin', 'description' => 'Administration and management'],
            ['name' => 'HR', 'description' => 'Human Resources'],
            ['name' => 'Marketing', 'description' => 'Marketing and promotions'],
            ['name' => 'Production', 'description' => 'Production and operations'],
            ['name' => 'Sales', 'description' => 'Sales and client relations'],
            ['name' => 'Accounting', 'description' => 'Finance and accounting'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['name' => $department['name']],
                [
                    'description' => $department['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Demo employee record linked to the self-service account, with sample data.
        DB::table('employees')->updateOrInsert(
            ['employee_id' => 'EMP-0001'],
            [
                'name' => 'Employee Portal',
                'email' => 'employee@imprintcustoms.ph',
                'contact_number' => '0917 000 0000',
                'department' => 'Production',
                'position' => 'Press Operator',
                'date_hired' => '2024-01-15',
                'employment_type' => 'Regular',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $employeeId = DB::table('employees')->where('employee_id', 'EMP-0001')->value('id');

        // Link the Employee account to its record.
        User::where('email', 'employee@imprintcustoms.ph')->update(['employee_id' => $employeeId]);

        // Sample self-service data (only seed once).
        if ($employeeId && DB::table('attendances')->where('employee_id', $employeeId)->doesntExist()) {
            foreach ([0, 1, 2] as $offset) {
                DB::table('attendances')->insert([
                    'employee_id' => $employeeId,
                    'attendance_date' => now()->subDays($offset)->toDateString(),
                    'time_in' => '08:00:00',
                    'time_out' => '17:00:00',
                    'status' => $offset === 1 ? 'Late' : 'Present',
                    'remarks' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('leaves')->insert([
                'employee_id' => $employeeId,
                'leave_type' => 'Sick Leave',
                'start_date' => now()->addDays(3)->toDateString(),
                'end_date' => now()->addDays(4)->toDateString(),
                'total_days' => 2,
                'reason' => 'Medical check-up',
                'status' => 'Pending',
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('payrolls')->insert([
                'employee_id' => $employeeId,
                'payroll_month' => now()->subMonth()->format('F'),
                'payroll_year' => (int) now()->format('Y'),
                'basic_salary' => 20000,
                'allowances' => 2000,
                'overtime_pay' => 500,
                'deductions' => 1500,
                'gross_pay' => 22500,
                'net_pay' => 21000,
                'status' => 'Paid',
                'payment_date' => now()->subDays(2)->toDateString(),
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
