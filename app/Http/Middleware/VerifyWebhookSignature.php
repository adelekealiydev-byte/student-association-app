<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $expected = hash_hmac('sha256', $request->getContent(), (string) config('association.webhook_secret'));

        abort_unless(hash_equals($expected, (string) $request->header('X-Signature')), 401);

        return $next($request);
    }
}

