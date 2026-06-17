<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Setting
{
    protected static ?array $cache = null;

    protected static function load(): array
    {
        if (self::$cache === null) {
            self::$cache = DB::table('settings')->pluck('value', 'key')->all();
        }
        return self::$cache;
    }

    public static function get(string $key, $default = null)
    {
        return self::load()[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
        self::$cache = null;
    }
}
