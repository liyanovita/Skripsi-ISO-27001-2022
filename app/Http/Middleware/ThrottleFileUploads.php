<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Services\RateLimiting\RateLimiter;
use App\Services\Logging\AuditLogger;

/**
 * Throttle File Uploads Middleware
 * 
 * Limits file upload frequency and size
 */
class ThrottleFileUploads
{
    /**
     * Maximum file size in MB
     */
    private const MAX_FILE_SIZE_MB = 50;

    /**
     * Maximum uploads per hour
     */
    private const MAX_UPLOADS_PER_HOUR = 20;

    /**
     * Maximum total upload size per day in MB
     */
    private const MAX_DAILY_UPLOAD_MB = 500;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check file upload endpoints
        if (!$request->hasFile('evidence_file') && !$request->hasFile('json_file')) {
            return $next($request);
        }

        $file = $request->file('evidence_file') ?? $request->file('json_file');
        $userId = auth()->id() ?? $request->ip();

        // Check file size
        if (!$this->checkFileSize($file)) {
            return $this->buildErrorResponse(
                'File size exceeds maximum allowed size of ' . self::MAX_FILE_SIZE_MB . 'MB'
            );
        }

        // Check upload frequency
        $frequencyKey = "upload_frequency:{$userId}";
        if (RateLimiter::isLimited($frequencyKey, self::MAX_UPLOADS_PER_HOUR, 60)) {
            AuditLogger::logSecurityEvent(
                'upload_frequency_limit_exceeded',
                'warning',
                ['user_id' => $userId]
            );

            return $this->buildErrorResponse(
                'Upload frequency limit exceeded. Maximum ' . self::MAX_UPLOADS_PER_HOUR . ' uploads per hour.'
            );
        }

        // Check daily upload size
        $dailyKey = "upload_daily_size:{$userId}";
        $dailySize = Cache::get($dailyKey, 0);
        $fileSize = $file->getSize() / 1024 / 1024; // Convert to MB

        if ($dailySize + $fileSize > self::MAX_DAILY_UPLOAD_MB) {
            AuditLogger::logSecurityEvent(
                'daily_upload_limit_exceeded',
                'warning',
                [
                    'user_id' => $userId,
                    'current_size_mb' => round($dailySize, 2),
                    'requested_size_mb' => round($fileSize, 2),
                ]
            );

            return $this->buildErrorResponse(
                'Daily upload limit exceeded. Maximum ' . self::MAX_DAILY_UPLOAD_MB . 'MB per day.'
            );
        }

        $response = $next($request);

        // Update counters on successful upload
        if ($response->getStatusCode() < 400) {
            RateLimiter::hit($frequencyKey, 60);
            Cache::put($dailyKey, $dailySize + $fileSize, now()->addDay());
        }

        return $response;
    }

    /**
     * Check if file size is within limits
     */
    private function checkFileSize($file): bool
    {
        $sizeMB = $file->getSize() / 1024 / 1024;

        return $sizeMB <= self::MAX_FILE_SIZE_MB;
    }

    /**
     * Build error response
     */
    private function buildErrorResponse(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 413);
    }
}
