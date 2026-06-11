<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\RateLimiting\RateLimiter;
use App\Services\Logging\AuditLogger;

/**
 * Prevent Brute Force Middleware
 * 
 * Protects against brute force attacks on login and password reset
 */
class PreventBruteForce
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check login and password reset endpoints
        if (!$this->shouldCheck($request)) {
            return $next($request);
        }

        $key = $this->getBruteForceKey($request);
        $maxAttempts = 5;
        $decayMinutes = 15;

        if (RateLimiter::isLimited($key, $maxAttempts, $decayMinutes)) {
            AuditLogger::logSecurityEvent(
                'brute_force_attempt_blocked',
                'critical',
                [
                    'key' => $key,
                    'ip_address' => $request->ip(),
                    'path' => $request->getPathInfo(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please try again in ' . RateLimiter::retryAfter($key) . ' seconds.',
            ], 429)
                ->header('Retry-After', RateLimiter::retryAfter($key));
        }

        $response = $next($request);

        // Only increment counter on failed attempts
        if ($response->getStatusCode() >= 400) {
            RateLimiter::hit($key, $decayMinutes);
        } else {
            // Reset on successful attempt
            RateLimiter::reset($key);
        }

        return $response;
    }

    /**
     * Check if the request should be rate limited
     */
    private function shouldCheck(Request $request): bool
    {
        return $request->is('login', 'register', 'forgot-password', 'reset-password');
    }

    /**
     * Get the brute force key for the request
     */
    private function getBruteForceKey(Request $request): string
    {
        $identifier = $request->input('email') ?? $request->ip();

        return "brute_force:{$request->getPathInfo()}:{$identifier}";
    }
}
