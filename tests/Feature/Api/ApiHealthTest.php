<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    /**
     * Test API health check endpoint
     */
    public function test_api_health_check_returns_healthy_status(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'status',
                         'timestamp',
                         'version',
                         'services' => [
                             'database',
                             'cache',
                             'storage'
                         ],
                         'metrics' => [
                             'response_time_ms',
                             'memory_usage_mb',
                             'uptime_seconds'
                         ]
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'status' => 'healthy',
                         'version' => '1.0.0'
                     ]
                 ]);
    }

    /**
     * Test API version headers are present
     */
    public function test_api_version_headers_are_present(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertHeader('X-API-Version', '1.0.0')
                 ->assertHeader('X-API-Documentation');
    }

    /**
     * Test CORS headers for API requests
     */
    public function test_cors_headers_are_present_for_api_requests(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertHeader('Access-Control-Allow-Origin', '*')
                 ->assertHeader('Access-Control-Allow-Methods')
                 ->assertHeader('Access-Control-Allow-Headers');
    }
}