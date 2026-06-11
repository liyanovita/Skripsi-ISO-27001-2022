<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Cleanup Logs Command
 * 
 * Removes old log files based on retention policy
 */
class CleanupLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup {--days=30} {--force}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Clean up old log files';

    /**
     * Retention policy for different log types (in days)
     */
    private const RETENTION_POLICY = [
        'audit.log' => 90,
        'security.log' => 90,
        'errors.log' => 60,
        'api.log' => 30,
        'performance.log' => 30,
        'webhooks.log' => 30,
        'database.log' => 14,
        'cache.log' => 14,
        'laravel.log' => 7,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $logsPath = storage_path('logs');
        $force = $this->option('force');

        if (!File::isDirectory($logsPath)) {
            $this->error("Logs directory not found: {$logsPath}");
            return 1;
        }

        $this->info("Cleaning up old log files...\n");

        $files = File::files($logsPath);
        $deleted = 0;
        $totalSize = 0;

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $retentionDays = $this->getRetentionDays($filename);
            $fileAge = (time() - $file->getMTime()) / 86400;

            if ($fileAge > $retentionDays) {
                $size = $file->getSize();
                $totalSize += $size;

                if ($force) {
                    File::delete($file->getRealPath());
                    $this->line("✓ Deleted: {$filename} (" . round($size / 1024, 2) . " KB)");
                    $deleted++;
                } else {
                    $this->line("Would delete: {$filename} (" . round($size / 1024, 2) . " KB)");
                }
            }
        }

        if (!$force && $deleted === 0) {
            $this->info("\nRun with --force flag to actually delete files.");
        }

        $this->info("\n" . ($force ? "Deleted" : "Would delete") . " {$deleted} files");
        $this->info("Total size freed: " . round($totalSize / 1024 / 1024, 2) . " MB");

        return 0;
    }

    /**
     * Get retention days for a log file
     */
    private function getRetentionDays(string $filename): int
    {
        foreach (self::RETENTION_POLICY as $pattern => $days) {
            if (str_contains($filename, $pattern)) {
                return $days;
            }
        }

        return 30; // Default retention
    }
}
