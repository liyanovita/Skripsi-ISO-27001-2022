<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Optimize Database Command
 * 
 * Performs database optimization tasks
 */
class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize {--analyze} {--repair} {--all}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Optimize database tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $analyze = $this->option('analyze');
        $repair = $this->option('repair');
        $all = $this->option('all');

        if ($all) {
            $analyze = $repair = true;
        }

        $this->info("Database Optimization\n");

        $tables = $this->getTables();

        if ($analyze) {
            $this->analyzeTable($tables);
        }

        if ($repair) {
            $this->repairTables($tables);
        }

        if (!$analyze && !$repair) {
            $this->optimizeTables($tables);
        }

        $this->info("\nDatabase optimization completed.");

        return 0;
    }

    /**
     * Get all tables in the database
     */
    private function getTables(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $tables = DB::select('SHOW TABLES');
            $dbName = DB::getDatabaseName();
            $key = "Tables_in_{$dbName}";

            return array_map(fn($table) => $table->$key, $tables);
        }

        return [];
    }

    /**
     * Optimize tables
     */
    private function optimizeTables(array $tables): void
    {
        $this->info("Optimizing tables...\n");

        foreach ($tables as $table) {
            try {
                DB::statement("OPTIMIZE TABLE {$table}");
                $this->line("✓ Optimized: {$table}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to optimize {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Analyze tables
     */
    private function analyzeTable(array $tables): void
    {
        $this->info("Analyzing tables...\n");

        foreach ($tables as $table) {
            try {
                DB::statement("ANALYZE TABLE {$table}");
                $this->line("✓ Analyzed: {$table}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to analyze {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Repair tables
     */
    private function repairTables(array $tables): void
    {
        $this->info("Repairing tables...\n");

        foreach ($tables as $table) {
            try {
                DB::statement("REPAIR TABLE {$table}");
                $this->line("✓ Repaired: {$table}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to repair {$table}: " . $e->getMessage());
            }
        }
    }
}
