<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckLoginPermission
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->can_login) {
            Auth::logout();
            return redirect()->route('login')->withErrors('Your account is not allowed to log in.');
        }

        return $next($request);
    }
}
