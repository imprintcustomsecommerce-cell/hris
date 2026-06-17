<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class LeaveBalance
{
    /**
     * Leave types that draw down a credit balance, mapped to the credit column.
     */
    public const TRACKED = [
        'Vacation Leave' => 'vacation_credits',
        'Sick Leave' => 'sick_credits',
    ];

    /**
     * Compute the remaining/used/allotted balance for an employee this year.
     *
     * @return array<string, array{type:string, allotted:int, used:int, remaining:int}>
     */
    public static function summary(object $employee): array
    {
        $year = (int) now()->format('Y');
        $out = [];

        foreach (self::TRACKED as $type => $column) {
            $allotted = (int) ($employee->{$column} ?? 0);

            $used = (int) DB::table('leaves')
                ->where('employee_id', $employee->id)
                ->where('leave_type', $type)
                ->where('status', 'Approved')
                ->whereYear('start_date', $year)
                ->sum('total_days');

            $out[$type] = [
                'type' => $type,
                'allotted' => $allotted,
                'used' => $used,
                'remaining' => max(0, $allotted - $used),
            ];
        }

        return $out;
    }

    /**
     * Remaining days for a single tracked leave type (null = untracked/unlimited).
     */
    public static function remainingFor(object $employee, string $type): ?int
    {
        if (! array_key_exists($type, self::TRACKED)) {
            return null;
        }

        return self::summary($employee)[$type]['remaining'];
    }
}
