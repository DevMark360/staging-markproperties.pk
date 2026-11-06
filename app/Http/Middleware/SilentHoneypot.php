<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The public forms have a hidden "website" field that people never see. A bot that fills it gets the same
 * "received" reply as a real visitor, but nothing is saved and no email is sent (C6): a rejection would tell the bot
 * which field gave it away.
 */
class SilentHoneypot
{
    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->input('website'))) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Received'])
                : back();
        }

        return $next($request);
    }
}
