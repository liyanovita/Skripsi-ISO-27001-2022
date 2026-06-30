<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Governance\CreateKnowledgeBaseRequest;
use App\Http\Requests\Governance\UpdateKnowledgeBaseRequest;
use App\Services\Governance\KnowledgeBaseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *     schema="KnowledgeBase",
 *     type="object",
 *     title="Knowledge Base",
 *     description="Knowledge base resource for ISO 27001:2022 documentation",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Information Security Policy Template"),
 *     @OA\Property(property="description", type="string", example="Comprehensive template for creating information security policies"),
 *     @OA\Property(property="content", type="string", example="This policy establishes the framework..."),
 *     @OA\Property(property="category", type="string", enum={"guides", "templates", "sop", "evidence"}, example="templates"),
 *     @OA\Property(property="format", type="string", nullable=true, example="PDF"),
 *     @OA\Property(property="size", type="string", nullable=true, example="45KB"),
 *     @OA\Property(property="icon", type="string", nullable=true, example="fa-file-lines"),
 *     @OA\Property(property="downloads_count", type="integer", example=3),
 *     @OA\Property(property="is_system", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="CreateKnowledgeBaseRequest",
 *     type="object",
 *     required={"title", "content", "category"},
 *     @OA\Property(property="title", type="string", minLength=5, maxLength=255, example="Information Security Policy Template"),
 *     @OA\Property(property="description", type="string", nullable=true, maxLength=1000, example="Comprehensive template for creating information security policies"),
 *     @OA\Property(property="content", type="string", minLength=10, example="This policy establishes the framework for information security..."),
 *     @OA\Property(property="category", type="string", enum={"guides", "templates", "sop", "evidence"}, example="templates"),
 *     @OA\Property(property="format", type="string", nullable=true, example="PDF"),
 *     @OA\Property(property="size", type="string", nullable=true, example="45KB"),
 *     @OA\Property(property="icon", type="string", nullable=true, example="fa-file-lines")
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateKnowledgeBaseRequest",
 *     type="object",
 *     required={"title", "content", "category"},
 *     @OA\Property(property="title", type="string", minLength=5, maxLength=255, example="Updated Information Security Policy Template"),
 *     @OA\Property(property="description", type="string", nullable=true, maxLength=1000, example="Updated comprehensive template for creating information security policies"),
 *     @OA\Property(property="content", type="string", minLength=10, example="This updated policy establishes the framework..."),
 *     @OA\Property(property="category", type="string", enum={"guides", "templates", "sop", "evidence"}, example="templates"),
 *     @OA\Property(property="format", type="string", nullable=true, example="PDF"),
 *     @OA\Property(property="size", type="string", nullable=true, example="45KB"),
 *     @OA\Property(property="icon", type="string", nullable=true, example="fa-file-lines")
 * )
 */
class KnowledgeBaseApiController extends BaseApiController
{
    public function __construct(
        protected KnowledgeBaseService $knowledgeBaseService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/knowledge-base",
     *     operationId="getKnowledgeBaseResources",
     *     tags={"Knowledge Base"},
     *     summary="Get all knowledge base resources",
     *     description="Retrieve all knowledge base resources including policies, procedures, and templates",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=false,
     *         description="Search term applied to title, description, and content",
     *         @OA\Schema(type="string", example="risk")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter by knowledge base category",
     *         @OA\Schema(type="string", enum={"all", "guides", "templates", "sop", "evidence"}, example="templates")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         required=false,
     *         description="Filter official system resources or custom resources",
     *         @OA\Schema(type="string", enum={"all", "official", "custom"}, example="official")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         required=false,
     *         description="Sort resources",
     *         @OA\Schema(type="string", enum={"latest", "title", "most_downloaded"}, example="most_downloaded")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Knowledge base resources retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="resources",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/KnowledgeBase")
     *                 ),
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"guides", "templates", "sop", "evidence"}
     *                 ),
     *                 @OA\Property(
     *                     property="statistics",
     *                     type="object",
     *                     @OA\Property(property="total_resources", type="integer", example=25),
     *                     @OA\Property(property="system_resources", type="integer", example=15),
     *                     @OA\Property(property="user_resources", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->knowledgeBaseService->getAll($request->only(['q', 'category', 'sort', 'source']));
        return $this->successResponse($data, 'Knowledge base resources retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/knowledge-base",
     *     operationId="createKnowledgeBaseResource",
     *     tags={"Knowledge Base"},
     *     summary="Create a new knowledge base resource",
     *     description="Create a new knowledge base resource such as policy, procedure, or template",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateKnowledgeBaseRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resource added successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/KnowledgeBase")
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
     *                     property="title",
     *                     type="array",
     *                     @OA\Items(type="string", example="The title field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(CreateKnowledgeBaseRequest $request): JsonResponse
    {
        try {
            $resource = $this->knowledgeBaseService->create($request->validated());
            return $this->successResponse($resource, 'Resource added successfully.', 201);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to create resource.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/knowledge-base/{id}",
     *     operationId="getKnowledgeBaseResource",
     *     tags={"Knowledge Base"},
     *     summary="Get a specific knowledge base resource",
     *     description="Retrieve a specific knowledge base resource by ID",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resource ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resource retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/KnowledgeBase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $resource = $this->knowledgeBaseService->findOrFail($id);
            return $this->successResponse($resource, 'Resource retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Resource not found', 404);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to retrieve resource.', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/knowledge-base/{id}",
     *     operationId="updateKnowledgeBaseResource",
     *     tags={"Knowledge Base"},
     *     summary="Update a knowledge base resource",
     *     description="Update an existing knowledge base resource (system resources cannot be modified)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resource ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateKnowledgeBaseRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resource updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/KnowledgeBase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot modify system resource",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Official system assets cannot be modified.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function update(UpdateKnowledgeBaseRequest $request, int $id): JsonResponse
    {
        try {
            $resource = $this->knowledgeBaseService->findOrFail($id);
            
            if ($resource->is_system) {
                return $this->errorResponse('Official system assets cannot be modified.', 403);
            }

            $updatedResource = $this->knowledgeBaseService->update($id, $request->validated());
            return $this->successResponse($updatedResource, 'Resource updated successfully.');
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Resource not found', 404);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to update resource.', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/knowledge-base/{id}",
     *     operationId="deleteKnowledgeBaseResource",
     *     tags={"Knowledge Base"},
     *     summary="Delete a knowledge base resource",
     *     description="Delete a knowledge base resource (system resources cannot be deleted)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resource ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resource deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot delete system resource",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Official system assets cannot be deleted.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->knowledgeBaseService->delete($id);
            return $this->successResponse(null, 'Resource deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Resource not found', 404);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to delete resource.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/knowledge-base/{id}/download",
     *     operationId="downloadKnowledgeBaseResource",
     *     tags={"Knowledge Base"},
     *     summary="Download knowledge base resource as PDF",
     *     description="Generate and download a knowledge base resource as PDF document",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resource ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF generated successfully",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function download(int $id): Response
    {
        try {
            $item = $this->knowledgeBaseService->findOrFail($id);

            if (! $item->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF download is only available for official system resources.'
                ], 403);
            }

            $item = $this->knowledgeBaseService->recordDownload($item);
            $html = $this->knowledgeBaseService->generatePdfContent($item);
            $pdf = Pdf::loadHTML($html);
            $safeTitle = $this->knowledgeBaseService->safeDownloadName($item);

            return $pdf->download($safeTitle . '.pdf');
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate download'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/knowledge-base/{id}/attachment",
     *     operationId="downloadKnowledgeBaseAttachment",
     *     tags={"Knowledge Base"},
     *     summary="Download knowledge base resource attachment",
     *     description="Download the original attachment file associated with a knowledge base resource",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resource ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File downloaded successfully",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource or attachment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Attachment file not found.")
     *         )
     *     )
     * )
     */
    public function downloadAttachment(int $id): Response
    {
        try {
            $item = $this->knowledgeBaseService->findOrFail($id);

            if (! $this->knowledgeBaseService->hasAttachment($item)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment file not found.'
                ], 404);
            }

            $this->knowledgeBaseService->recordDownload($item);

            return response()->download(
                Storage::disk('local')->path($item->attachment_path),
                $this->knowledgeBaseService->attachmentDownloadName($item)
            );
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download attachment'
            ], 500);
        }
    }
}
