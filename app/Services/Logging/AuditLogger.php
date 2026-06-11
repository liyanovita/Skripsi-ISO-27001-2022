<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Audit Logger Service
 * 
 * Provides comprehensive logging for user actions and system events
 */
class AuditLogger
{
    /**
     * Log user action
     * 
     * @param string $action Action name (e.g., 'create_session', 'update_result')
     * @param string $entity Entity type (e.g., 'AssessmentSession', 'AssessmentResult')
     * @param int|null $entityId Entity ID
     * @param array $data Additional data to log
     * @param string $status Status (success, failed, pending)
     * @return void
     */
    public static function logAction(
        string $action,
        string $entity,
        ?int $entityId = null,
        array $data = [],
        string $status = 'success'
    ): void {
        $userId = Auth::id();
        $userEmail = Auth::user()?->email ?? 'unknown';

        $logData = [
            'timestamp' => now()->toIso8601String(),
            'user_id' => $userId,
            'user_email' => $userEmail,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
        ];

        Log::channel('audit')->info("User Action: {$action}", $logData);
    }

    /**
     * Log security event
     * 
     * @param string $event Event name (e.g., 'unauthorized_access', 'failed_login')
     * @param string $severity Severity level (info, warning, critical)
     * @param array $details Event details
     * @return void
     */
    public static function logSecurityEvent(
        string $event,
        string $severity = 'warning',
        array $details = []
    ): void {
        $userId = Auth::id();
        $userEmail = Auth::user()?->email ?? 'unknown';

        $logData = [
            'timestamp' => now()->toIso8601String(),
            'event' => $event,
            'severity' => $severity,
            'user_id' => $userId,
            'user_email' => $userEmail,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
        ];

        Log::channel('security')->log(
            $severity === 'critical' ? 'critical' : ($severity === 'warning' ? 'warning' : 'info'),
            "Security Event: {$event}",
            $logData
        );
    }

    /**
     * Log API request
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @param int $statusCode Response status code
     * @param float $duration Request duration in milliseconds
     * @param array $metadata Additional metadata
     * @return void
     */
    public static function logApiRequest(
        string $method,
        string $path,
        int $statusCode,
        float $duration,
        array $metadata = []
    ): void {
        $userId = Auth::id();

        $logData = [
            'timestamp' => now()->toIso8601String(),
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'metadata' => $metadata,
        ];

        // Log slow requests
        if ($duration > 1000) {
            Log::channel('performance')->warning("Slow API Request: {$method} {$path}", $logData);
        } else {
            Log::channel('api')->info("API Request: {$method} {$path}", $logData);
        }
    }


    /**
     * Log data modification
     * 
     * @param string $entity Entity type
     * @param int $entityId Entity ID
     * @param string $action Action (create, update, delete)
     * @param array $oldValues Old values (for updates)
     * @param array $newValues New values
     * @return void
     */
    public static function logDataModification(
        string $entity,
        int $entityId,
        string $action,
        array $oldValues = [],
        array $newValues = []
    ): void {
        $userId = Auth::id();

        $logData = [
            'timestamp' => now()->toIso8601String(),
            'user_id' => $userId,
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changes' => self::calculateChanges($oldValues, $newValues),
        ];

        Log::channel('audit')->info("Data Modification: {$entity} {$action}", $logData);
    }

    /**
     * Calculate changes between old and new values
     * 
     * @param array $oldValues Old values
     * @param array $newValues New values
     * @return array Changes
     */
    private static function calculateChanges(array $oldValues, array $newValues): array
    {
        $changes = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

}
