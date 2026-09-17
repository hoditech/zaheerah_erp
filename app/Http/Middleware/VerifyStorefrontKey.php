<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the storefront API with a single shared secret.
 *
 * The frontsite is a public app, so it never touches the ERP database directly.
 * It authenticates with a long random key that lives only in both .env files.
 */
class VerifyStorefrontKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.storefront.key');
        $provided = (string) $request->header('X-Storefront-Key', '');

        // A missing configured key must fail closed, never open.
        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
