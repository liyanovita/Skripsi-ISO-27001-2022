<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

/**
 * Monitor Logs Command
 * 
 * Displays recent log entries and statistics
 */
class MonitorLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:monitor {--channel=audit} {--lines=20} {--follow}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Monitor application logs in real-time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $channel = $this->option('channel');
        $lines = $this->option('lines');
        $follow = $this->option('follow');

        $logPath = storage_path("logs/{$channel}.log");

        if (!File::exists($logPath)) {
            $this->error("Log file not found: {$logPath}");
            return 1;
        }

        $this->info("Monitoring {$channel} logs...\n");

        if ($follow) {
            $this->followLogs($logPath, $lines);
        } else {
            $this->displayLogs($logPath, $lines);
        }

        return 0;
    }

    /**
     * Display recent log entries
     */
    private function displayLogs(string $logPath, int $lines): void
    {
        $content = File::get($logPath);
        $logLines = explode("\n", trim($content));
        $recentLines = array_slice($logLines, -$lines);

        foreach ($recentLines as $line) {
            if (!empty($line)) {
                $this->line($line);
            }
        }
    }

    /**
     * Follow logs in real-time
     */
    private function followLogs(string $logPath, int $lines): void
    {
        $lastSize = 0;

        while (true) {
            $currentSize = filesize($logPath);

            if ($currentSize > $lastSize) {
                $handle = fopen($logPath, 'r');
                fseek($handle, $lastSize);

                while (!feof($handle)) {
                    $line = fgets($handle);
                    if ($line !== false && !empty(trim($line))) {
                        $this->line($line);
                    }
                }

                $lastSize = $currentSize;
                fclose($handle);
            }

            sleep(1);
        }
    }
}
