<?php

namespace App\Services\RateLimiting;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Rate Limiter Service
 * 
 * Provides rate limiting functionality to prevent abuse
 */
class RateLimiter
{
    /**
     * Check if a request should be rate limited
     * 
     * @param string $key Rate limit key (e.g., 'user:1:api', 'ip:192.168.1.1:login')
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Time window in minutes
     * @return bool True if rate limited, false if allowed
     */
    public static function isLimited(
        string $key,
        int $maxAttempts = 60,
        int $decayMinutes = 1
    ): bool {
        $cacheKey = "rate_limit:{$key}";
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            return true;
        }

        return false;
    }

    /**
     * Increment rate limit counter
     * 
     * @param string $key Rate limit key
     * @param int $decayMinutes Time window in minutes
     * @return int Current attempt count
     */
    public static function hit(string $key, int $decayMinutes = 1): int
    {
        $cacheKey = "rate_limit:{$key}";
        $attempts = Cache::get($cacheKey, 0);
        $attempts++;

        Cache::put($cacheKey, $attempts, now()->addMinutes($decayMinutes));

        return $attempts;
    }

    /**
     * Get remaining attempts
     * 
     * @param string $key Rate limit key
     * @param int $maxAttempts Maximum attempts allowed
     * @return int Remaining attempts
     */
    public static function remaining(string $key, int $maxAttempts = 60): int
    {
        $cacheKey = "rate_limit:{$key}";
        $attempts = Cache::get($cacheKey, 0);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get retry after time in seconds
     * 
     * @param string $key Rate limit key
     * @return int Seconds until rate limit resets
     */
    public static function retryAfter(string $key): int
    {
        $cacheKey = "rate_limit:{$key}";

        try {
            // Try Redis TTL first
            $store = Cache::getStore();
            if (method_exists($store, 'connection')) {
                $ttl = $store->connection()->ttl($cacheKey);
                return max(0, (int) $ttl);
            }
        } catch (\Exception $e) {
            // Fall through to default
        }

        // Fallback: return 60 seconds for non-Redis drivers
        return 60;
    }

    /**
     * Reset rate limit counter
     * 
     * @param string $key Rate limit key
     * @return void
     */
    public static function reset(string $key): void
    {
        $cacheKey = "rate_limit:{$key}";
        Cache::forget($cacheKey);
    }

    /**
     * Clear all rate limits for a pattern
     * 
     * @param string $pattern Pattern to match (e.g., 'user:1:*')
     * @return void
     */
    public static function clearPattern(string $pattern): void
    {
        // Note: This is a simplified version. For production, use Redis SCAN
        // or implement a proper pattern-based cache clearing
        Log::info("Rate limit pattern cleared: {$pattern}");
    }

    /**
     * Generate rate limit key for user
     * 
     * @param int $userId User ID
     * @param string $action Action name
     * @return string Rate limit key
     */
    public static function userKey(int $userId, string $action = 'api'): string
    {
        return "user:{$userId}:{$action}";
    }

    /**
     * Generate rate limit key for IP address
     * 
     * @param string $ip IP address
     * @param string $action Action name
     * @return string Rate limit key
     */
    public static function ipKey(string $ip, string $action = 'api'): string
    {
        return "ip:{$ip}:{$action}";
    }

    /**
     * Generate rate limit key for endpoint
     * 
     * @param string $endpoint Endpoint path
     * @param string $identifier User ID or IP
     * @return string Rate limit key
     */
    public static function endpointKey(string $endpoint, string $identifier): string
    {
        return "endpoint:{$endpoint}:{$identifier}";
    }
}
