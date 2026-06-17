<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Notify
{
    /**
     * Create an in-app notification and email the recipient (best-effort).
     */
    public static function send(int $userId, string $message, ?string $url = null): void
    {
        DB::table('notifications')->insert([
            'user_id' => $userId,
            'message' => $message,
            'url' => $url,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = DB::table('users')->where('id', $userId)->first();

        if ($user && ! empty($user->email)) {
            try {
                $link = $url ? rtrim(config('app.url'), '/') . $url : config('app.url');
                Mail::raw($message . "\n\nOpen HRIS: " . $link, function ($mail) use ($user) {
                    $mail->to($user->email)->subject('Imprint HRIS Notification');
                });
            } catch (\Throwable $e) {
                // Never let a mail failure break the action.
            }
        }
    }
}
