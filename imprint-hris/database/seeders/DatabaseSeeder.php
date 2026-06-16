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
    }
}
