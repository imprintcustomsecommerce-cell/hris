<?php

namespace App\Support;

/**
 * Demo mode: lets anyone explore the system as any role without credentials.
 *
 * Enabled with DEMO_MODE=true. Intended for a public showcase seeded with
 * fabricated data — never for an installation holding real employee records.
 */
class Demo
{
    public static function enabled(): bool
    {
        return (bool) config('app.demo');
    }

    /**
     * The seeded accounts a visitor may sign in as, in presentation order.
     *
     * @return array<int, array{role:string, email:string, label:string, blurb:string}>
     */
    public static function roles(): array
    {
        return [
            ['role' => 'Admin',    'email' => 'admin@imprintcustoms.ph',    'label' => 'Administrator', 'blurb' => 'Full access — employees, payroll, settings, audit log'],
            ['role' => 'HR',       'email' => 'hr@imprintcustoms.ph',       'label' => 'HR Officer',    'blurb' => 'Recruitment, leave approvals, attendance, payroll'],
            ['role' => 'Manager',  'email' => 'manager@imprintcustoms.ph',  'label' => 'Manager',       'blurb' => 'Projects, tasks, reviews and their own team'],
            ['role' => 'CEO',      'email' => 'ceo@imprintcustoms.ph',      'label' => 'Executive',     'blurb' => 'Company-wide dashboard and project overview'],
            ['role' => 'Employee', 'email' => 'employee@imprintcustoms.ph', 'label' => 'Employee',      'blurb' => 'Self-service portal — payslips, leave, documents'],
        ];
    }

    /** Landing page for a role, mirroring the redirect on "/". */
    public static function homeFor(string $role): string
    {
        return match ($role) {
            'Employee' => '/portal',
            'Manager' => '/tasks',
            'Applicant' => '/apply',
            'CEO' => '/exec-dashboard',
            default => '/dashboard',
        };
    }
}
