<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Log Statistics Command
 * 
 * Displays statistics about log files
 */
class LogStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:stats {--days=7}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Display log file statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $logsPath = storage_path('logs');

        if (!File::isDirectory($logsPath)) {
            $this->error("Logs directory not found: {$logsPath}");
            return 1;
        }

        $this->info("Log Statistics (Last {$days} days)\n");

        $files = File::files($logsPath);
        $stats = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $size = $file->getSize();
            $lines = count(file($file->getRealPath()));
            $modified = $file->getMTime();

            // Only show files modified in the last N days
            if (time() - $modified > ($days * 86400)) {
                continue;
            }

            $stats[] = [
                'File' => $filename,
                'Size (KB)' => round($size / 1024, 2),
                'Lines' => $lines,
                'Modified' => date('Y-m-d H:i:s', $modified),
            ];
        }

        if (empty($stats)) {
            $this->info("No log files found in the last {$days} days.");
            return 0;
        }

        $this->table(
            ['File', 'Size (KB)', 'Lines', 'Modified'],
            $stats
        );

        // Display total statistics
        $totalSize = array_sum(array_column($stats, 'Size (KB)'));
        $totalLines = array_sum(array_column($stats, 'Lines'));

        $this->info("\nTotal Size: " . round($totalSize, 2) . " KB");
        $this->info("Total Lines: " . $totalLines);

        return 0;
    }
}
