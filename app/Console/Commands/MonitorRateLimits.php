<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Monitor Rate Limits Command
 * 
 * Displays current rate limit status
 */
class MonitorRateLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate-limits:monitor {--user=} {--ip=}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Monitor current rate limit status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = $this->option('user');
        $ip = $this->option('ip');

        $this->info("Rate Limit Monitor\n");

        if ($user) {
            $this->displayUserLimits($user);
        } elseif ($ip) {
            $this->displayIpLimits($ip);
        } else {
            $this->displayAllLimits();
        }

        return 0;
    }

    /**
     * Display rate limits for a specific user
     */
    private function displayUserLimits(string $userId): void
    {
        $this->info("Rate Limits for User: {$userId}\n");

        $limits = [
            "rate_limit:user:{$userId}:api" => 'API Requests',
            "rate_limit:user:{$userId}:login" => 'Login Attempts',
            "rate_limit:user:{$userId}:upload" => 'File Uploads',
            "upload_frequency:{$userId}" => 'Upload Frequency',
            "upload_daily_size:{$userId}" => 'Daily Upload Size',
        ];

        $data = [];
        foreach ($limits as $key => $label) {
            $value = Cache::get($key, 0);
            if ($value > 0) {
                $data[] = [
                    'Limit' => $label,
                    'Current' => $value,
                    'Key' => $key,
                ];
            }
        }

        if (empty($data)) {
            $this->info("No active rate limits for this user.");
        } else {
            $this->table(['Limit', 'Current', 'Key'], $data);
        }
    }

    /**
     * Display rate limits for a specific IP
     */
    private function displayIpLimits(string $ip): void
    {
        $this->info("Rate Limits for IP: {$ip}\n");

        $limits = [
            "rate_limit:ip:{$ip}:api" => 'API Requests',
            "rate_limit:ip:{$ip}:login" => 'Login Attempts',
            "rate_limit:ip:{$ip}:webhook" => 'Webhook Requests',
            "brute_force:/login:{$ip}" => 'Brute Force Attempts',
        ];

        $data = [];
        foreach ($limits as $key => $label) {
            $value = Cache::get($key, 0);
            if ($value > 0) {
                $data[] = [
                    'Limit' => $label,
                    'Current' => $value,
                    'Key' => $key,
                ];
            }
        }

        if (empty($data)) {
            $this->info("No active rate limits for this IP.");
        } else {
            $this->table(['Limit', 'Current', 'Key'], $data);
        }
    }

    /**
     * Display all active rate limits
     */
    private function displayAllLimits(): void
    {
        $this->info("All Active Rate Limits\n");
        $this->info("Note: This shows a summary. Use --user or --ip for detailed information.\n");

        $this->line("To view limits for a specific user:");
        $this->line("  php artisan rate-limits:monitor --user=1\n");

        $this->line("To view limits for a specific IP:");
        $this->line("  php artisan rate-limits:monitor --ip=192.168.1.1\n");
    }
}
