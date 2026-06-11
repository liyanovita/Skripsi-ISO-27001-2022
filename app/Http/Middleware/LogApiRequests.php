<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Logging\AuditLogger;

/**
 * Log API Requests Middleware
 * 
 * Logs all API requests with timing and status information
 */
class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        // Only log API requests
        if ($request->wantsJson() || $request->is('api/*') || $request->is('webhook/*')) {
            AuditLogger::logApiRequest(
                $request->getMethod(),
                $request->getPathInfo(),
                $response->getStatusCode(),
                $duration,
                [
                    'route' => $request->route()?->getName(),
                    'user_id' => auth()->id(),
                ]
            );
        }

        return $response;
    }
}
