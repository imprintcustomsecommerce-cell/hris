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
            ['name' => 'Chief Executive Officer', 'email' => 'ceo@imprintcustoms.ph', 'role' => 'CEO'],
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

        // Manager employee record (so the Manager account has a team).
        DB::table('employees')->updateOrInsert(
            ['employee_id' => 'EMP-MGR1'],
            [
                'name' => 'Department Manager',
                'email' => 'manager@imprintcustoms.ph',
                'department' => 'Production',
                'position' => 'Production Manager',
                'date_hired' => '2023-01-10',
                'employment_type' => 'Regular',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $managerEmployeeId = DB::table('employees')->where('employee_id', 'EMP-MGR1')->value('id');

        // Link accounts to their employee records.
        User::where('email', 'employee@imprintcustoms.ph')->update(['employee_id' => $employeeId]);
        User::where('email', 'manager@imprintcustoms.ph')->update(['employee_id' => $managerEmployeeId]);

        // Put the demo employee on the manager's team.
        DB::table('employees')->where('id', $employeeId)->update(['manager_id' => $managerEmployeeId]);

        // Philippine holidays (sample).
        foreach ([
            ['name' => 'New Year\'s Day', 'date' => now()->format('Y') . '-01-01', 'type' => 'Regular'],
            ['name' => 'Araw ng Kagitingan', 'date' => now()->format('Y') . '-04-09', 'type' => 'Regular'],
            ['name' => 'Labor Day', 'date' => now()->format('Y') . '-05-01', 'type' => 'Regular'],
            ['name' => 'Independence Day', 'date' => now()->format('Y') . '-06-12', 'type' => 'Regular'],
            ['name' => 'Bonifacio Day', 'date' => now()->format('Y') . '-11-30', 'type' => 'Regular'],
            ['name' => 'Christmas Day', 'date' => now()->format('Y') . '-12-25', 'type' => 'Regular'],
            ['name' => 'Rizal Day', 'date' => now()->format('Y') . '-12-30', 'type' => 'Regular'],
        ] as $holiday) {
            DB::table('holidays')->updateOrInsert(
                ['date' => $holiday['date']],
                ['name' => $holiday['name'], 'type' => $holiday['type'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

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

        // Rich demonstration dataset (idempotent).
        $this->call(DemoDataSeeder::class);
    }
}
