<?php

namespace App\Support;

use Carbon\Carbon;

class Shift
{
    /**
     * Compute late / undertime / overtime minutes for a day's time logs
     * against the configured shift. Times are "H:i" or "H:i:s" strings (nullable).
     *
     * @return array{late:int, undertime:int, overtime:int}
     */
    public static function metrics(?string $timeIn, ?string $timeOut): array
    {
        $start = Carbon::parse(Setting::get('shift_start', '08:00'));
        $end = Carbon::parse(Setting::get('shift_end', '17:00'));
        $grace = (int) Setting::get('grace_minutes', 0);

        $late = $undertime = $overtime = 0;

        if ($timeIn) {
            $in = Carbon::parse($timeIn);
            $latest = $start->copy()->addMinutes($grace);
            if ($in->gt($latest)) {
                $late = $start->diffInMinutes($in); // minutes past scheduled start
            }
        }

        if ($timeOut) {
            $out = Carbon::parse($timeOut);
            if ($out->lt($end)) {
                $undertime = $out->diffInMinutes($end);
            } elseif ($out->gt($end)) {
                $overtime = $end->diffInMinutes($out);
            }
        }

        return [
            'late' => (int) $late,
            'undertime' => (int) $undertime,
            'overtime' => (int) $overtime,
        ];
    }

    /** Whether a given time-in counts as Late (past start + grace). */
    public static function isLate(?string $timeIn): bool
    {
        if (! $timeIn) {
            return false;
        }
        $latest = Carbon::parse(Setting::get('shift_start', '08:00'))
            ->addMinutes((int) Setting::get('grace_minutes', 0));
        return Carbon::parse($timeIn)->gt($latest);
    }
}
