<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Workdays
{
    /**
     * Count working days between two dates (inclusive), excluding
     * weekends and any dates listed in the holidays table.
     */
    public static function between(string $start, string $end): int
    {
        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->startOfDay();

        if ($to->lt($from)) {
            return 0;
        }

        $holidays = DB::table('holidays')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->all();

        $days = 0;
        for ($cursor = $from->copy(); $cursor->lte($to); $cursor->addDay()) {
            if ($cursor->isWeekend()) {
                continue;
            }
            if (in_array($cursor->toDateString(), $holidays, true)) {
                continue;
            }
            $days++;
        }

        return $days;
    }
}
