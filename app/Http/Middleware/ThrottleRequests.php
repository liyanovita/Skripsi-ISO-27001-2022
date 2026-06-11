<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\RateLimiting\RateLimiter;
use App\Services\Logging\AuditLogger;

/**
 * Throttle Requests Middleware
 * 
 * Implements rate limiting for API endpoints
 */
class ThrottleRequests
{
    /**
     * Rate limit configurations for different endpoints
     */
    private const RATE_LIMITS = [
        // API endpoints
        'api' => ['limit' => 60, 'decay' => 1],
        'webhook' => ['limit' => 100, 'decay' => 1],
        
        // Authentication endpoints
        'login' => ['limit' => 5, 'decay' => 15],
        'register' => ['limit' => 3, 'decay' => 60],
        'password-reset' => ['limit' => 3, 'decay' => 60],
        
        // File upload endpoints
        'upload' => ['limit' => 10, 'decay' => 60],
        
        // Export endpoints
        'export' => ['limit' => 5, 'decay' => 5],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRateLimitKey($request);
        $limit = $this->resolveRateLimit($request);

        if (RateLimiter::isLimited($key, $limit['limit'], $limit['decay'])) {
            AuditLogger::logSecurityEvent(
                'rate_limit_exceeded',
                'warning',
                [
                    'key' => $key,
                    'limit' => $limit['limit'],
                    'path' => $request->getPathInfo(),
                ]
            );

            return $this->buildResponse(
                429,
                'Too many requests. Please try again later.',
                RateLimiter::retryAfter($key)
            );
        }

        RateLimiter::hit($key, $limit['decay']);

        $response = $next($request);

        return $response
            ->header('X-RateLimit-Limit', $limit['limit'])
            ->header('X-RateLimit-Remaining', RateLimiter::remaining($key, $limit['limit']))
            ->header('X-RateLimit-Reset', now()->addMinutes($limit['decay'])->timestamp);
    }

    /**
     * Resolve the rate limit key for the request
     */
    private function resolveRateLimitKey(Request $request): string
    {
        if (auth()->check()) {
            return RateLimiter::userKey(auth()->id(), $this->getAction($request));
        }

        return RateLimiter::ipKey($request->ip(), $this->getAction($request));
    }

    /**
     * Resolve the rate limit configuration for the request
     */
    private function resolveRateLimit(Request $request): array
    {
        $action = $this->getAction($request);

        return self::RATE_LIMITS[$action] ?? self::RATE_LIMITS['api'];
    }

    /**
     * Get the action name from the request
     */
    private function getAction(Request $request): string
    {
        $path = $request->getPathInfo();

        if (str_contains($path, '/login')) {
            return 'login';
        }
        if (str_contains($path, '/register')) {
            return 'register';
        }
        if (str_contains($path, '/password')) {
            return 'password-reset';
        }
        if (str_contains($path, '/webhook')) {
            return 'webhook';
        }
        if (str_contains($path, '/upload')) {
            return 'upload';
        }
        if (str_contains($path, '/export')) {
            return 'export';
        }

        return 'api';
    }

    /**
     * Build a rate limit response
     */
    private function buildResponse(int $status, string $message, int $retryAfter): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'retry_after' => $retryAfter,
        ], $status)
            ->header('Retry-After', $retryAfter);
    }
}
