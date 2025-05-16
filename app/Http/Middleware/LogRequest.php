<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user) {
            $message = sprintf(
                "[%s] %s %s by user %s (%s)",
                now(), $request->method(), $request->path(), $user->id, $user->email
            );

            Log::channel('api_activity')->info($message);
        }

        return $next($request);
    }
}
