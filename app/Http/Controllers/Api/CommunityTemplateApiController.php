<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Community\CreateTemplateRequest;
use App\Http\Requests\Community\RateTemplateRequest;
use App\Services\Community\TemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="CommunityTemplate",
 *     type="object",
 *     title="Community Template",
 *     description="Community shared assessment template",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Financial Services ISO 27001:2022 Template"),
 *     @OA\Property(property="description", type="string", example="Comprehensive template for financial services compliance"),
 *     @OA\Property(property="author_name", type="string", example="John Doe"),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"finance", "banking", "compliance"}),
 *     @OA\Property(property="base_score", type="number", format="float", example=3.75),
 *     @OA\Property(property="usage_count", type="integer", example=25),
 *     @OA\Property(property="upvotes", type="integer", example=15),
 *     @OA\Property(property="average_rating", type="number", format="float", example=4.2),
 *     @OA\Property(property="rating_count", type="integer", example=8),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="CreateTemplateRequest",
 *     type="object",
 *     required={"title", "description", "json_file"},
 *     @OA\Property(property="title", type="string", minLength=5, maxLength=255, example="Financial Services Template"),
 *     @OA\Property(property="description", type="string", minLength=10, maxLength=1000, example="Template for financial services compliance assessment"),
 *     @OA\Property(property="tags", type="string", example="finance,banking,compliance", description="Comma-separated tags"),
 *     @OA\Property(property="json_file", type="string", format="binary", description="Assessment session JSON file")
 * )
 * 
 * @OA\Schema(
 *     schema="RateTemplateRequest",
 *     type="object",
 *     required={"stars"},
 *     @OA\Property(property="stars", type="integer", minimum=1, maximum=5, example=4)
 * )
 */
class CommunityTemplateApiController extends BaseApiController
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/community/templates",
     *     operationId="getCommunityTemplates",
     *     tags={"Community Templates"},
     *     summary="Get all community templates",
     *     description="Retrieve all community templates with optional search functionality",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term for filtering templates",
     *         @OA\Schema(type="string", example="finance")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Templates retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="templates",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/CommunityTemplate")
     *                 ),
     *                 @OA\Property(property="total_templates", type="integer", example=25),
     *                 @OA\Property(property="total_usage", type="integer", example=150),
     *                 @OA\Property(property="search", type="string", nullable=true, example="finance")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->templateService->getTemplatesData($request->get('search'));
        $data['search'] = $request->get('search');

        return $this->successResponse($data, 'Templates retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/community/templates",
     *     operationId="createCommunityTemplate",
     *     tags={"Community Templates"},
     *     summary="Create a new community template",
     *     description="Share an assessment session as a community template",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/CreateTemplateRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Your best practice has been successfully shared with the community!"),
     *             @OA\Property(property="data", ref="#/components/schemas/CommunityTemplate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="json_file",
     *                     type="array",
     *                     @OA\Items(type="string", example="The json file field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(CreateTemplateRequest $request): JsonResponse
    {
        try {
            $fileContent = json_decode(
                file_get_contents($request->file('json_file')->getRealPath()),
                true
            );

            if (!$fileContent) {
                return $this->errorResponse('Invalid JSON file format.', 422);
            }

            $template = $this->templateService->createTemplate([
                'title'        => $request->title,
                'description'  => $request->description,
                'author_name'  => auth()->user()->name ?? 'Anonymous Assessor',
                'tags'         => $request->tags ? explode(',', $request->tags) : [],
                'base_score'   => $fileContent['session']['overall_maturity_score'] ?? 0,
                'content_data' => $fileContent,
            ], auth()->id());

            return $this->successResponse($template, 'Your best practice has been successfully shared with the community!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create template: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/community/templates/{id}",
     *     operationId="getCommunityTemplate",
     *     tags={"Community Templates"},
     *     summary="Get a specific community template",
     *     description="Retrieve a specific community template with detailed information and results preview",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Template ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Template retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="template", ref="#/components/schemas/CommunityTemplate"),
     *                 @OA\Property(
     *                     property="results_preview",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="standard_code", type="string", example="A.5.1.1"),
     *                         @OA\Property(property="maturity_rating", type="integer", example=3),
     *                         @OA\Property(property="compliance_status", type="string", example="compliant")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Template not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->templateService->getTemplateWithResults($id);
            return $this->successResponse($data, 'Template retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Template not found', 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/community/templates/{id}/use",
     *     operationId="useCommunityTemplate",
     *     tags={"Community Templates"},
     *     summary="Use a community template",
     *     description="Create a new assessment session based on a community template",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Template ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template used successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Community template successfully activated!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="session_id", type="integer", example=5),
     *                 @OA\Property(property="session_name", type="string", example="Financial Services Template - Copy")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Template not found")
     *         )
     *     )
     * )
     */
    public function useTemplate(int $id): JsonResponse
    {
        try {
            $session = $this->templateService->useTemplate($id, auth()->id());

            return $this->successResponse([
                'session_id' => $session->id,
                'session_name' => $session->name,
            ], 'Community template successfully activated!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to use template: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/community/templates/{id}/clone",
     *     operationId="cloneCommunityTemplate",
     *     tags={"Community Templates"},
     *     summary="Clone a community template",
     *     description="Create a copy of a community template for customization",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Template ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template cloned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Template successfully cloned! You can now adapt it."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="session_id", type="integer", example=6),
     *                 @OA\Property(property="session_name", type="string", example="Financial Services Template - Cloned")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Template not found")
     *         )
     *     )
     * )
     */
    public function clone(int $id): JsonResponse
    {
        try {
            $session = $this->templateService->cloneTemplate($id, auth()->id());

            return $this->successResponse([
                'session_id' => $session->id,
                'session_name' => $session->name,
            ], 'Template successfully cloned! You can now adapt it.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to clone template: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/community/templates/{id}/upvote",
     *     operationId="upvoteCommunityTemplate",
     *     tags={"Community Templates"},
     *     summary="Upvote a community template",
     *     description="Give an upvote to a community template to show appreciation",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Template ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template upvoted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Template upvoted successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="upvotes", type="integer", example=16)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Template not found")
     *         )
     *     )
     * )
     */
    public function upvote(int $id): JsonResponse
    {
        try {
            if (session()->has("upvoted_template_{$id}")) {
                return $this->errorResponse('You have already upvoted this template.', 400);
            }

            $this->templateService->upvote($id);
            session()->put("upvoted_template_{$id}", true);
            
            $template = \App\Models\CommunityTemplate::findOrFail($id);
            return $this->successResponse(['upvotes' => $template->upvotes], 'Template upvoted successfully!');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upvote template: ' . $e->getMessage(), 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/community/templates/{id}/rate",
     *     operationId="rateCommunityTemplate",
     *     tags={"Community Templates"},
     *     summary="Rate a community template",
     *     description="Give a star rating to a community template",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Template ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RateTemplateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating submitted! Thank you."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.3),
     *                 @OA\Property(property="rating_count", type="integer", example=9)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="stars",
     *                     type="array",
     *                     @OA\Items(type="string", example="The stars must be between 1 and 5.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function rate(RateTemplateRequest $request, int $id): JsonResponse
    {
        try {
            if (session()->has("rated_template_{$id}")) {
                return $this->errorResponse('You have already rated this template.', 400);
            }

            $this->templateService->rate($id, $request->stars);
            session()->put("rated_template_{$id}", true);
            
            $template = \App\Models\CommunityTemplate::findOrFail($id);
            return $this->successResponse([
                'average_rating' => $template->avg_rating,
                'rating_count'   => $template->rating_count,
            ], 'Rating submitted! Thank you.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit rating: ' . $e->getMessage(), 404);
        }
    }
}