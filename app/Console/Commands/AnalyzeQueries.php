<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Analyze Queries Command
 * 
 * Analyzes slow queries and provides optimization suggestions
 */
class AnalyzeQueries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:analyze-queries {--threshold=1000}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Analyze slow queries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = $this->option('threshold');

        $this->info("Query Analysis (Threshold: {$threshold}ms)\n");

        // Enable query logging
        DB::enableQueryLog();

        // Run some queries to analyze
        $this->analyzeCommonQueries();

        $queries = DB::getQueryLog();

        $slowQueries = array_filter($queries, function ($query) use ($threshold) {
            return $query['time'] > $threshold;
        });

        if (empty($slowQueries)) {
            $this->info("No slow queries found above {$threshold}ms threshold.");
            return 0;
        }

        $this->displaySlowQueries($slowQueries);
        $this->displayOptimizationSuggestions($slowQueries);

        return 0;
    }

    /**
     * Analyze common queries
     */
    private function analyzeCommonQueries(): void
    {
        // These are example queries - in production, you'd analyze actual queries
        try {
            DB::table('assessment_sessions')->where('user_id', 1)->get();
            DB::table('assessment_results')->where('session_id', 1)->get();
            DB::table('community_templates')->latest()->take(10)->get();
        } catch (\Exception $e) {
            // Ignore errors during analysis
        }
    }

    /**
     * Display slow queries
     */
    private function displaySlowQueries(array $queries): void
    {
        $this->info("Slow Queries Found: " . count($queries) . "\n");

        $data = [];
        foreach ($queries as $query) {
            $data[] = [
                'Time (ms)' => round($query['time'], 2),
                'Query' => substr($query['query'], 0, 80) . '...',
            ];
        }

        $this->table(['Time (ms)', 'Query'], $data);
    }

    /**
     * Display optimization suggestions
     */
    private function displayOptimizationSuggestions(array $queries): void
    {
        $this->info("\nOptimization Suggestions:\n");

        $suggestions = [
            'Add indexes on frequently queried columns',
            'Use eager loading to prevent N+1 queries',
            'Consider query caching for frequently accessed data',
            'Use database query optimization tools',
            'Review query execution plans with EXPLAIN',
            'Consider denormalization for complex queries',
        ];

        foreach ($suggestions as $i => $suggestion) {
            $this->line(($i + 1) . ". {$suggestion}");
        }
    }
}
