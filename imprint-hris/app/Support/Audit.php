<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Audit
{
    /**
     * Record an action in the audit trail.
     */
    public static function log(string $action, string $description): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => Auth::id(),
            'actor_name' => Auth::user()->name ?? 'System',
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
