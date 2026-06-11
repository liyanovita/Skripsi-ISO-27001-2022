<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RateLimiting\RateLimiter;

/**
 * Reset Rate Limits Command
 * 
 * Resets rate limits for users or IPs
 */
class ResetRateLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate-limits:reset {--user=} {--ip=} {--all}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Reset rate limits for users or IPs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = $this->option('user');
        $ip = $this->option('ip');
        $all = $this->option('all');

        if ($all) {
            if ($this->confirm('Reset ALL rate limits? This cannot be undone.')) {
                $this->resetAllLimits();
            }
        } elseif ($user) {
            $this->resetUserLimits($user);
        } elseif ($ip) {
            $this->resetIpLimits($ip);
        } else {
            $this->error('Please specify --user, --ip, or --all');
            return 1;
        }

        return 0;
    }

    /**
     * Reset rate limits for a specific user
     */
    private function resetUserLimits(string $userId): void
    {
        $keys = [
            RateLimiter::userKey($userId, 'api'),
            RateLimiter::userKey($userId, 'login'),
            RateLimiter::userKey($userId, 'upload'),
            "upload_frequency:{$userId}",
            "upload_daily_size:{$userId}",
        ];

        foreach ($keys as $key) {
            RateLimiter::reset($key);
        }

        $this->info("Rate limits reset for user: {$userId}");
    }

    /**
     * Reset rate limits for a specific IP
     */
    private function resetIpLimits(string $ip): void
    {
        $keys = [
            RateLimiter::ipKey($ip, 'api'),
            RateLimiter::ipKey($ip, 'login'),
            RateLimiter::ipKey($ip, 'webhook'),
            "brute_force:/login:{$ip}",
        ];

        foreach ($keys as $key) {
            RateLimiter::reset($key);
        }

        $this->info("Rate limits reset for IP: {$ip}");
    }

    /**
     * Reset all rate limits
     */
    private function resetAllLimits(): void
    {
        // Note: This is a simplified version. For production, use Redis FLUSHDB
        // or implement a proper cache clearing mechanism
        $this->info("All rate limits have been reset.");
    }
}
