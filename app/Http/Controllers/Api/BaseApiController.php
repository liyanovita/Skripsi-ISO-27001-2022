<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="ISO 27001:2022 Audit System API",
 *     version="1.0.0",
 *     description="Comprehensive API for ISO 27001:2022 compliance audit and assessment system",
 *     @OA\Contact(
 *         email="support@audit-iso27001:2022.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost",
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Enter token in format: Bearer {token}"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization"
 * )
 * 
 * @OA\Tag(
 *     name="Assessment Sessions",
 *     description="Manage assessment sessions"
 * )
 * 
 * @OA\Tag(
 *     name="Assessment Results",
 *     description="Manage assessment results and findings"
 * )
 * 
 * @OA\Tag(
 *     name="Community Templates",
 *     description="Share and use community assessment templates"
 * )
 * 
 * @OA\Tag(
 *     name="Intelligence & Analytics",
 *     description="Analytics, reports, and AI insights"
 * )
 * 
 * @OA\Tag(
 *     name="Compliance Workspace",
 *     description="Statement of Applicability and compliance management"
 * )
 * 
 * @OA\Tag(
 *     name="Knowledge Base",
 *     description="Documentation and knowledge resources"
 * )
 * 
 * @OA\Tag(
 *     name="User Profile",
 *     description="User profile and organization settings"
 * )
 * 
 * @OA\Tag(
 *     name="Webhooks",
 *     description="Webhook endpoints for external integrations"
 * )
 */
class BaseApiController extends Controller
{
    /**
     * Standard success response format
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Standard error response format
     */
    protected function errorResponse(string $message = 'Error', int $statusCode = 400, array $errors = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Paginated response format
     */
    protected function paginatedResponse($data, string $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
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