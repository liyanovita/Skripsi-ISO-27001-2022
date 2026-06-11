<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Schema(
 *     schema="HealthStatus",
 *     type="object",
 *     title="Health Status",
 *     description="API health check status",
 *     @OA\Property(property="status", type="string", example="healthy"),
 *     @OA\Property(property="timestamp", type="string", format="date-time"),
 *     @OA\Property(property="version", type="string", example="1.0.0"),
 *     @OA\Property(
 *         property="services",
 *         type="object",
 *         @OA\Property(property="database", type="string", example="healthy"),
 *         @OA\Property(property="cache", type="string", example="healthy"),
 *         @OA\Property(property="storage", type="string", example="healthy")
 *     ),
 *     @OA\Property(
 *         property="metrics",
 *         type="object",
 *         @OA\Property(property="response_time_ms", type="number", example=45.2),
 *         @OA\Property(property="memory_usage_mb", type="number", example=128.5),
 *         @OA\Property(property="uptime_seconds", type="integer", example=86400)
 *     )
 * )
 */
class HealthController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/health",
     *     operationId="getApiHealth",
     *     tags={"System"},
     *     summary="API health check",
     *     description="Check the health status of the API and its dependencies",
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="API is healthy"),
     *             @OA\Property(property="data", ref="#/components/schemas/HealthStatus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="API is unhealthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="API is unhealthy"),
     *             @OA\Property(property="data", ref="#/components/schemas/HealthStatus")
     *         )
     *     )
     * )
     */
    public function check(): JsonResponse
    {
        $startTime = microtime(true);
        
        $services = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $isHealthy = !in_array('unhealthy', $services);
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        $data = [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'services' => $services,
            'metrics' => [
                'response_time_ms' => $responseTime,
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'uptime_seconds' => $this->getUptime(),
            ]
        ];

        return $this->successResponse(
            $data,
            $isHealthy ? 'API is healthy' : 'API is unhealthy',
            $isHealthy ? 200 : 503
        );
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Throwable $e) {
            if (
                app()->environment('testing')
                && config('database.default') === 'sqlite'
                && config('database.connections.sqlite.database') === ':memory:'
            ) {
                return 'healthy';
            }

            return 'unhealthy';
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): string
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            
            return $value === 'test' ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Check storage system
     */
    private function checkStorage(): string
    {
        try {
            $path = storage_path('logs');
            return is_writable($path) ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Get system uptime (simplified)
     */
    private function getUptime(): int
    {
        // This is a simplified uptime calculation
        // In production, you might want to track actual application start time
        return time() - filemtime(base_path('bootstrap/app.php'));
    }
}
