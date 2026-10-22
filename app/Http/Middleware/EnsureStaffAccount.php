<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only real staff accounts.
 *
 * A "staff" account has at least one role or direct permission. Accounts that
 * registered themselves while public registration was still open have neither,
 * so they are locked out of internal screens such as the dashboard even if they
 * still hold a session.
 */
class EnsureStaffAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->roles()->exists() && $user->getDirectPermissions()->isEmpty())) {
            abort(403);
        }

        return $next($request);
    }
}
