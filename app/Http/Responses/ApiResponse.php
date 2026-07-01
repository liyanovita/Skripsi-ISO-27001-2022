<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Standardized API Response Helper
 * 
 * Provides consistent response formatting across all API endpoints
 */
class ApiResponse
{
    /**
     * Return a success response
     *
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default: 200)
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        string $message = null,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('Success'),
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a created response (201)
     *
     * @param mixed $data Created resource data
     * @param string $message Success message
     * @return JsonResponse
     */
    public static function created(
        mixed $data = null,
        string $message = null
    ): JsonResponse {
        return self::success($data, $message ?? __('Resource created successfully'), 201);
    }

    /**
     * Return an error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code (default: 400)
     * @param array $errors Additional error details
     * @return JsonResponse
     */
    public static function error(
        string $message = null,
        int $statusCode = 400,
        array $errors = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message ?? __('An error occurred'),
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Return a validation error response (422)
     *
     * @param array $errors Validation errors
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function validationError(
        array $errors,
        string $message = null
    ): JsonResponse {
        return self::error($message ?? __('Validation failed'), 422, $errors);
    }

    /**
     * Return a not found response (404)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function notFound(
        string $message = null
    ): JsonResponse {
        return self::error($message ?? __('Resource not found'), 404);
    }

    /**
     * Return an unauthorized response (401)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function unauthorized(
        string $message = null
    ): JsonResponse {
        return self::error($message ?? __('Unauthorized'), 401);
    }

    /**
     * Return a forbidden response (403)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function forbidden(
        string $message = null
    ): JsonResponse {
        return self::error($message ?? __('Forbidden'), 403);
    }

    /**
     * Return a conflict response (409)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function conflict(
        string $message = null
    ): JsonResponse {
        return self::error($message ?? __('Resource conflict'), 409);
    }

    /**
     * Return a server error response (500)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function serverError(
        string $message = null
    ): JsonResponse {
        return self::error($message ?? __('Internal server error'), 500);
    }

    /**
     * Return a paginated response
     *
     * @param mixed $data Paginated data
     * @param string $message Success message
     * @return JsonResponse
     */
    public static function paginated(
        mixed $data,
        string $message = null
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('Success'),
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ], 200);
    }
}