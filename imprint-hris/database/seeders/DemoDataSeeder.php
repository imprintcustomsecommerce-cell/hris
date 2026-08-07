<?php

namespace Database\Seeders;

use App\Support\PayrollCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rich demonstration dataset: ~26 employees across all departments with
 * two months of attendance, leaves, payroll runs, loans, projects, tasks,
 * performance reviews, a recruitment pipeline, announcements and more.
 *
 * Idempotent: re-running updates in place (keyed on employee_id / natural keys).
 * Run AFTER DatabaseSeeder:  php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Demo data is for live demos only — keep the test database minimal.
        if (app()->environment('testing')) {
            return;
        }

        mt_srand(20260806); // deterministic demo data

        $now = now();

        // ---------------------------------------------------------------
        // Employees (name, dept, position, salary, hired, type)
        // ---------------------------------------------------------------
        $roster = [
            // Production
            ['Ramon Villanueva',   'Production', 'Production Supervisor', 32000, '2021-03-01', 'Regular'],
            ['Jerome Bautista',    'Production', 'Press Operator',        19000, '2022-06-13', 'Regular'],
            ['Marvin Dela Cruz',   'Production', 'Press Operator',        18500, '2023-02-06', 'Regular'],
            ['Alvin Santos',       'Production', 'Heat Press Operator',   17500, '2023-08-21', 'Regular'],
            ['Joana Reyes',        'Production', 'Embroidery Specialist', 20000, '2022-01-17', 'Regular'],
            ['Carlo Mendoza',      'Production', 'Quality Inspector',     19500, '2023-05-02', 'Regular'],
            ['Dennis Aquino',      'Production', 'Production Assistant',  15000, '2025-11-10', 'Probationary'],
            ['Rowena Garcia',      'Production', 'Sublimation Operator',  18000, '2024-04-15', 'Regular'],
            // Sales
            ['Katrina Lim',        'Sales',      'Sales Supervisor',      30000, '2021-09-06', 'Regular'],
            ['Paolo Fernandez',    'Sales',      'Account Executive',     22000, '2023-01-09', 'Regular'],
            ['Bianca Torres',      'Sales',      'Account Executive',     21500, '2023-10-02', 'Regular'],
            ['Miguel Navarro',     'Sales',      'Sales Associate',       16000, '2026-01-12', 'Probationary'],
            // Marketing
            ['Andrea Salazar',     'Marketing',  'Marketing Lead',        28000, '2022-02-14', 'Regular'],
            ['Joshua Ramos',       'Marketing',  'Graphic Designer',      21000, '2023-07-03', 'Regular'],
            ['Nicole Chua',        'Marketing',  'Content Creator',       19000, '2024-09-16', 'Regular'],
            ['Patrick Uy',         'Marketing',  'Social Media Specialist', 18000, '2025-06-02', 'Regular'],
            // Accounting
            ['Cristina Ocampo',    'Accounting', 'Senior Accountant',     33000, '2020-11-09', 'Regular'],
            ['Ferdinand Cruz',     'Accounting', 'Bookkeeper',            20000, '2023-03-20', 'Regular'],
            ['Liza Manalo',        'Accounting', 'Billing Assistant',     16500, '2025-02-03', 'Regular'],
            // HR / Admin
            ['Grace Domingo',      'HR',         'HR Assistant',          18000, '2023-11-06', 'Regular'],
            ['Roberto Pascual',    'Admin',      'Office Administrator',  22000, '2021-05-10', 'Regular'],
            ['Sheryl Ignacio',     'Admin',      'Purchasing Officer',    21000, '2022-08-01', 'Regular'],
            ['Eduardo Marquez',    'Admin',      'Company Driver',        15500, '2022-10-17', 'Regular'],
            ['Maricel Flores',     'Admin',      'Utility / Maintenance', 14000, '2024-01-08', 'Regular'],
            // Departures / inactive for realism
            ['Arnold Sison',       'Production', 'Press Operator',        18000, '2021-07-05', 'Regular', 'Resigned'],
            ['Jasmine Coronel',    'Sales',      'Sales Associate',       16000, '2024-05-20', 'Regular', 'Resigned'],
        ];

        $slug = fn ($n) => strtolower(str_replace(' ', '.', preg_replace('/[^A-Za-z ]/', '', $n)));
        $empIds = [];
        $salary = [];

        foreach ($roster as $i => $r) {
            [$name, $dept, $pos, $basic, $hired, $type] = $r;
            $status = $r[6] ?? 'Active';
            $code = sprintf('EMP-%04d', $i + 100);
            DB::table('employees')->updateOrInsert(['employee_id' => $code], [
                'name' => $name,
                'email' => $slug($name) . '@imprintcustoms.ph',
                'contact_number' => '0917 ' . mt_rand(100, 999) . ' ' . mt_rand(1000, 9999),
                'birthdate' => $now->copy()->subYears(mt_rand(22, 48))->subDays(mt_rand(0, 364))->toDateString(),
                'address' => mt_rand(1, 999) . ' ' . ['Mabini St', 'Rizal Ave', 'Bonifacio Rd', 'Katipunan Ave', 'Sampaguita St'][mt_rand(0, 4)] . ', ' . ['Quezon City', 'Caloocan', 'Marikina', 'Pasig', 'Valenzuela'][mt_rand(0, 4)],
                'department' => $dept,
                'position' => $pos,
                'date_hired' => $hired,
                'employment_type' => $type,
                'status' => $status,
                'vacation_credits' => mt_rand(6, 15),
                'sick_credits' => mt_rand(8, 15),
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $id = DB::table('employees')->where('employee_id', $code)->value('id');
            $empIds[$name] = $id;
            $salary[$id] = $basic;
        }

        // Reporting lines: supervisors report to the seeded Production Manager, staff to their supervisor.
        $mgrId = DB::table('employees')->where('employee_id', 'EMP-MGR1')->value('id');
        $sup = [
            'Production' => $empIds['Ramon Villanueva'],
            'Sales'      => $empIds['Katrina Lim'],
            'Marketing'  => $empIds['Andrea Salazar'],
            'Accounting' => $empIds['Cristina Ocampo'],
        ];
        foreach ($roster as $r) {
            $name = $r[0]; $dept = $r[1]; $id = $empIds[$name];
            $boss = ($id === ($sup[$dept] ?? null)) ? $mgrId : ($sup[$dept] ?? $mgrId);
            DB::table('employees')->where('id', $id)->update(['manager_id' => $boss]);
        }

        $active = DB::table('employees')->whereIn('id', array_values($empIds))->where('status', 'Active')->pluck('id')->all();

        // ---------------------------------------------------------------
        // Attendance: last ~45 workdays for every active employee
        // ---------------------------------------------------------------
        $holidays = DB::table('holidays')->pluck('date')->map(fn ($d) => substr($d, 0, 10))->all();
        $rows = [];
        for ($d = 62; $d >= 0; $d--) {
            $date = $now->copy()->subDays($d);
            if ($date->isWeekend() || in_array($date->toDateString(), $holidays)) continue;
            foreach ($active as $eid) {
                $roll = mt_rand(1, 100);
                if ($roll <= 4) continue; // absent, no record
                $late = $roll <= 18 ? mt_rand(5, 55) : 0;
                $ot = $roll >= 85 ? mt_rand(30, 150) : 0;
                $in = sprintf('%02d:%02d:00', 8, min(59, $late));
                $out = $ot ? sprintf('%02d:%02d:00', 17 + intdiv($ot, 60), $ot % 60) : '17:00:00';
                $rows[] = [
                    'employee_id' => $eid,
                    'attendance_date' => $date->toDateString(),
                    'time_in' => $in, 'time_out' => $out,
                    'status' => $late ? 'Late' : 'Present',
                    'late_minutes' => $late, 'undertime_minutes' => 0, 'overtime_minutes' => $ot,
                    'remarks' => null, 'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('attendances')->upsert($chunk, ['employee_id', 'attendance_date'],
                ['time_in', 'time_out', 'status', 'late_minutes', 'overtime_minutes']);
        }

        // ---------------------------------------------------------------
        // Leaves: mix of past approved, pending, and rejected
        // ---------------------------------------------------------------
        if (DB::table('leaves')->count() < 10) {
            $types = ['Vacation Leave', 'Sick Leave', 'Emergency Leave', 'Vacation Leave', 'Sick Leave'];
            $reasons = ['Family occasion', 'Fever and flu', 'Personal errand', 'Out-of-town trip', 'Medical check-up', 'Child\'s school event'];
            foreach (array_slice($active, 0, 18) as $k => $eid) {
                $type = $types[$k % count($types)];
                $days = mt_rand(1, 3);
                $past = $k % 3 !== 0;
                $start = $past ? $now->copy()->subDays(mt_rand(5, 50)) : $now->copy()->addDays(mt_rand(2, 14));
                DB::table('leaves')->insert([
                    'employee_id' => $eid,
                    'leave_type' => $type,
                    'start_date' => $start->toDateString(),
                    'end_date' => $start->copy()->addDays($days - 1)->toDateString(),
                    'total_days' => $days,
                    'reason' => $reasons[$k % count($reasons)],
                    'status' => $past ? ($k % 7 === 5 ? 'Rejected' : 'Approved') : 'Pending',
                    'remarks' => $past && $k % 7 === 5 ? 'Insufficient coverage on requested dates' : null,
                    'created_at' => $start->copy()->subDays(3), 'updated_at' => $now,
                ]);
            }
        }

        // ---------------------------------------------------------------
        // Payroll: last 2 completed months (Paid) + current month (Pending)
        // ---------------------------------------------------------------
        foreach ([2, 1, 0] as $back) {
            $m = $now->copy()->subMonths($back);
            foreach ($active as $eid) {
                $basic = $salary[$eid];
                $ded = PayrollCalculator::deductions($basic);
                $allow = round($basic * 0.08, -2);
                $otPay = $back === 0 ? 0 : round(mt_rand(0, 30) * ($basic / 22 / 8) * 1.25, 2);
                $loanDed = 0.0;
                $statutory = $ded['sss'] + $ded['philhealth'] + $ded['pagibig'] + $ded['tax'];
                $gross = $basic + $allow + $otPay;
                DB::table('payrolls')->updateOrInsert(
                    ['employee_id' => $eid, 'payroll_month' => $m->format('F'), 'payroll_year' => (int) $m->format('Y')],
                    [
                        'basic_salary' => $basic, 'allowances' => $allow, 'overtime_pay' => $otPay,
                        'sss' => $ded['sss'], 'philhealth' => $ded['philhealth'], 'pagibig' => $ded['pagibig'],
                        'tax' => $ded['tax'], 'other_deductions' => 0, 'loan_deduction' => $loanDed,
                        'deductions' => round($statutory + $loanDed, 2),
                        'gross_pay' => round($gross, 2),
                        'net_pay' => round($gross - $statutory - $loanDed, 2),
                        'status' => $back === 0 ? 'Pending' : 'Paid',
                        'payment_date' => $back === 0 ? null : $m->copy()->endOfMonth()->toDateString(),
                        'remarks' => null, 'created_at' => $now, 'updated_at' => $now,
                    ]
                );
            }
        }

        // ---------------------------------------------------------------
        // Loans
        // ---------------------------------------------------------------
        if (DB::table('loans')->count() < 5) {
            $loanSpecs = [
                [$empIds['Jerome Bautista'], 'SSS Salary Loan', 20000, 1750],
                [$empIds['Joana Reyes'], 'Cash Advance', 5000, 1000],
                [$empIds['Paolo Fernandez'], 'Pag-IBIG Multi-Purpose Loan', 30000, 1400],
                [$empIds['Eduardo Marquez'], 'Cash Advance', 3000, 1500],
                [$empIds['Cristina Ocampo'], 'SSS Salary Loan', 32000, 1500, 'Paid'],
            ];
            foreach ($loanSpecs as $l) {
                [$eid, $type, $principal, $amort] = $l;
                $status = $l[4] ?? 'Active';
                DB::table('loans')->insert([
                    'employee_id' => $eid, 'type' => $type,
                    'principal' => $principal, 'monthly_amortization' => $amort,
                    'balance' => $status === 'Paid' ? 0 : $principal - $amort * mt_rand(2, 6),
                    'status' => $status, 'reason' => null,
                    'created_at' => $now->copy()->subMonths(mt_rand(3, 10)), 'updated_at' => $now,
                ]);
            }
        }

        // ---------------------------------------------------------------
        // Projects & tasks
        // ---------------------------------------------------------------
        $ceo = DB::table('users')->where('role', 'CEO')->first();
        $managerUser = DB::table('users')->where('email', 'manager@imprintcustoms.ph')->first();
        $hrUser = DB::table('users')->where('email', 'hr@imprintcustoms.ph')->first();

        $projects = [
            ['Barangay Fiesta Jersey Order — 350 pcs', 'Full sublimation jerseys for the San Isidro fiesta league. Deadline is hard; delivery is before opening parade.', 'In Progress', 12],
            ['Corporate Polo Rebrand — MetroBuild Inc.', 'Embroidered polos for MetroBuild\'s 120 field staff, new logo across 4 colorways.', 'In Progress', 25],
            ['School Intramurals Package — St. Agnes', 'Team kits for 8 departments; includes numbering and coach shirts.', 'Pending', 40],
            ['Christmas Corporate Giveaways 2025', 'Holiday tumbler + tote bundle for repeat corporate clients.', 'Completed', -160],
            ['E-commerce Product Photography Refresh', 'Reshoot the top 40 SKUs for the online catalog and marketplace listings.', 'Completed', -30],
        ];
        $projectIds = [];
        foreach ($projects as [$title, $desc, $status, $dueOffset]) {
            DB::table('projects')->updateOrInsert(['title' => $title], [
                'description' => $desc,
                'manager_id' => $managerUser->id,
                'created_by' => $ceo->id ?? null,
                'creator_name' => $ceo->name ?? 'CEO',
                'deadline' => $now->copy()->addDays($dueOffset)->toDateString(),
                'status' => $status,
                'created_at' => $now->copy()->addDays($dueOffset - 30), 'updated_at' => $now,
            ]);
            $projectIds[$title] = DB::table('projects')->where('title', $title)->value('id');
        }

        if (DB::table('tasks')->count() < 10) {
            $taskSpecs = [
                ['Finalize jersey layout v3 with client', 'Barangay Fiesta Jersey Order — 350 pcs', $empIds['Joshua Ramos'], 'High', 2, 'In Progress'],
                ['Print + press first 100 pcs batch', 'Barangay Fiesta Jersey Order — 350 pcs', $empIds['Jerome Bautista'], 'High', 5, 'Pending'],
                ['QC check on batch 1 output', 'Barangay Fiesta Jersey Order — 350 pcs', $empIds['Carlo Mendoza'], 'Medium', 6, 'Pending'],
                ['Digitize new MetroBuild logo for embroidery', 'Corporate Polo Rebrand — MetroBuild Inc.', $empIds['Joana Reyes'], 'High', 3, 'In Progress'],
                ['Order polo blanks — 120 pcs, 4 colors', 'Corporate Polo Rebrand — MetroBuild Inc.', $empIds['Sheryl Ignacio'], 'Medium', 4, 'Completed'],
                ['Prepare St. Agnes quotation and mockups', 'School Intramurals Package — St. Agnes', $empIds['Paolo Fernandez'], 'Medium', 7, 'In Progress'],
                ['Collect final balance — Christmas giveaways', 'Christmas Corporate Giveaways 2025', $empIds['Liza Manalo'], 'Low', -10, 'Completed'],
                ['Shoot SKUs 21–40', 'E-commerce Product Photography Refresh', $empIds['Nicole Chua'], 'Medium', -5, 'Completed'],
                ['Restock CMYK ink and transfer paper', null, $empIds['Sheryl Ignacio'], 'High', 1, 'In Progress'],
                ['Update price list for 2026 rates', null, $empIds['Katrina Lim'], 'Medium', 10, 'Pending'],
            ];
            foreach ($taskSpecs as [$title, $proj, $assignee, $prio, $dueOffset, $status]) {
                DB::table('tasks')->insert([
                    'project_id' => $proj ? $projectIds[$proj] : null,
                    'title' => $title, 'description' => null,
                    'assigned_to' => $assignee, 'assigned_by' => $managerUser->id,
                    'priority' => $prio,
                    'due_date' => $now->copy()->addDays($dueOffset)->toDateString(),
                    'status' => $status,
                    'created_at' => $now->copy()->subDays(mt_rand(3, 20)), 'updated_at' => $now,
                ]);
            }
        }

        // ---------------------------------------------------------------
        // Performance reviews (finalized last-half cycle + drafts)
        // ---------------------------------------------------------------
        if (DB::table('performance_reviews')->count() < 5) {
            $period = ($now->month <= 6 ? ($now->year - 1) . ' H2' : $now->year . ' H1');
            foreach (array_slice($active, 0, 12) as $k => $eid) {
                DB::table('performance_reviews')->insert([
                    'employee_id' => $eid,
                    'reviewer_id' => $managerUser->id,
                    'reviewer_name' => $managerUser->name,
                    'period' => $period,
                    'rating_quality' => mt_rand(3, 5),
                    'rating_productivity' => mt_rand(3, 5),
                    'rating_teamwork' => mt_rand(3, 5),
                    'rating_punctuality' => mt_rand(2, 5),
                    'strengths' => ['Consistent output and dependable under rush orders.', 'Great client communication and follow-through.', 'Detail-oriented; very low rework rate.'][$k % 3],
                    'improvements' => ['Punctuality on Monday shifts.', 'Documentation of job order changes.', 'Delegating during peak season.'][$k % 3],
                    'comments' => 'Keep it up. Discussed goals for the next cycle.',
                    'status' => $k < 9 ? 'Finalized' : 'Draft',
                    'created_at' => $now->copy()->subDays(mt_rand(10, 40)), 'updated_at' => $now,
                ]);
            }
        }

        // ---------------------------------------------------------------
        // Recruitment pipeline
        // ---------------------------------------------------------------
        $applicants = [
            ['Kevin Estrella', 'Screen Printer', 'For Interview'],
            ['Diana Robles', 'Graphic Designer', 'For Interview'],
            ['Mark Anthony Cruz', 'Sales Associate', 'Applied'],
            ['Charlene Dizon', 'Embroidery Operator', 'For Requirements'],
            ['Jonas Padilla', 'Delivery Driver', 'Applied'],
            ['Rachelle Soriano', 'Accounting Assistant', 'Hired'],
            ['Bryan Gutierrez', 'Press Operator', 'Rejected'],
        ];
        foreach ($applicants as [$name, $pos, $status]) {
            DB::table('applicants')->updateOrInsert(['name' => $name, 'position_applied' => $pos], [
                'email' => $slug($name) . '@gmail.com',
                'phone' => '0918 ' . mt_rand(100, 999) . ' ' . mt_rand(1000, 9999),
                'status' => $status,
                'notes' => $status === 'Rejected' ? 'Asking rate above range.' : null,
                'created_at' => $now->copy()->subDays(mt_rand(5, 45)), 'updated_at' => $now,
            ]);
        }
        if (DB::table('interviews')->count() < 2) {
            foreach ([['Kevin Estrella', 2, 'Onsite'], ['Diana Robles', 4, 'Online']] as [$name, $inDays, $mode]) {
                $aid = DB::table('applicants')->where('name', $name)->value('id');
                DB::table('interviews')->insert([
                    'applicant_id' => $aid,
                    'scheduled_at' => $now->copy()->addDays($inDays)->setTime(10, 0),
                    'mode' => $mode,
                    'location' => $mode === 'Online' ? 'https://meet.google.com/imprint-hr' : 'Main office, Quezon City',
                    'interviewer' => $hrUser->name,
                    'notes' => null, 'status' => 'Scheduled',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // ---------------------------------------------------------------
        // Announcements + a few audit-log entries
        // ---------------------------------------------------------------
        $posts = [
            ['Payroll schedule for this month', "Payroll will be credited on the 15th and 30th as usual. Please review your payslips in the portal and report discrepancies to HR within 3 days."],
            ['Rush season reminders', "Fiesta season orders are picking up. Overtime is open for Production — coordinate with your supervisor before rendering OT."],
            ['HMO enrollment window now open', "Regular employees may enroll dependents until the end of the month. See HR for premiums and forms."],
            ['New biometric device installed', "Please use the new fingerprint scanner at the main entrance starting Monday. Old logbook entries will no longer be accepted."],
        ];
        foreach ($posts as $k => [$title, $body]) {
            DB::table('announcements')->updateOrInsert(['title' => $title], [
                'body' => $body,
                'posted_by' => $hrUser->id, 'author_name' => $hrUser->name,
                'created_at' => $now->copy()->subDays(3 + $k * 6), 'updated_at' => $now,
            ]);
        }

        if (DB::table('audit_logs')->count() < 5) {
            foreach ([
                ['Payroll', 'Generated payroll run for ' . $now->copy()->subMonth()->format('F Y')],
                ['Leave', 'Approved 2-day vacation leave for Joana Reyes'],
                ['Employee', 'Added new employee Miguel Navarro (Sales Associate)'],
                ['Recruitment', 'Moved applicant Rachelle Soriano to Hired'],
            ] as $k => [$action, $desc]) {
                DB::table('audit_logs')->insert([
                    'user_id' => $hrUser->id, 'actor_name' => $hrUser->name,
                    'action' => $action, 'description' => $desc,
                    'created_at' => $now->copy()->subDays($k + 1),
                ]);
            }
        }

        $this->command?->info('Demo data seeded: ' . count($roster) . ' employees, ' . count($rows) . ' attendance rows, payroll for 3 months.');
    }
}
