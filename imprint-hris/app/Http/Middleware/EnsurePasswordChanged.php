<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Force users issued a temporary password to set a new one before
     * accessing anything else (except the password page and logout).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password
            && ! $request->is('portal/password')
            && ! $request->is('logout')) {
            return redirect('/portal/password');
        }

        return $next($request);
    }
}
