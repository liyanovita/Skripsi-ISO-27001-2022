<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Governance\UpdateProfileRequest;
use App\Http\Requests\Governance\UpdatePasswordRequest;
use App\Services\Governance\ProfileService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="UserProfile",
 *     type="object",
 *     title="User Profile",
 *     description="User profile and organization information",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@company.com"),
 *     @OA\Property(property="organization_name", type="string", nullable=true, example="Acme Corporation"),
 *     @OA\Property(property="organization_type", type="string", nullable=true, example="Private Company"),
 *     @OA\Property(property="industry", type="string", nullable=true, example="Financial Services"),
 *     @OA\Property(property="employee_count", type="string", nullable=true, example="100-500"),
 *     @OA\Property(property="country", type="string", nullable=true, example="Indonesia"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="ProfileData",
 *     type="object",
 *     title="Profile Data",
 *     description="Complete profile data with statistics",
 *     @OA\Property(property="user", ref="#/components/schemas/UserProfile"),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         @OA\Property(property="total_sessions", type="integer", example=5),
 *         @OA\Property(property="completed_sessions", type="integer", example=3),
 *         @OA\Property(property="avg_maturity_score", type="number", format="float", example=3.25),
 *         @OA\Property(property="last_activity", type="string", format="date-time")
 *     ),
 *     @OA\Property(
 *         property="recent_activity",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="session_name", type="string", example="Q2 2026 Assessment"),
 *             @OA\Property(property="action", type="string", example="Session completed"),
 *             @OA\Property(property="date", type="string", format="date-time")
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateProfileRequest",
 *     type="object",
 *     required={"name", "email"},
 *     @OA\Property(property="name", type="string", minLength=2, maxLength=255, example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.doe@company.com"),
 *     @OA\Property(property="organization_name", type="string", nullable=true, maxLength=255, example="Acme Corporation"),
 *     @OA\Property(property="organization_type", type="string", nullable=true, maxLength=100, example="Private Company"),
 *     @OA\Property(property="industry", type="string", nullable=true, maxLength=100, example="Financial Services"),
 *     @OA\Property(property="employee_count", type="string", nullable=true, maxLength=50, example="100-500"),
 *     @OA\Property(property="country", type="string", nullable=true, maxLength=100, example="Indonesia")
 * )
 * 
 * @OA\Schema(
 *     schema="UpdatePasswordRequest",
 *     type="object",
 *     required={"current_password", "password", "password_confirmation"},
 *     @OA\Property(property="current_password", type="string", format="password", example="current_password123"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8, example="new_password123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="new_password123")
 * )
 */
class ProfileApiController extends BaseApiController
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     operationId="getUserProfile",
     *     tags={"User Profile"},
     *     summary="Get user profile data",
     *     description="Retrieve complete user profile information including statistics and recent activity",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile data retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProfileData")
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        $data = $this->profileService->getProfileData(auth()->user());
        return $this->successResponse($data, 'Profile data retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/profile",
     *     operationId="updateUserProfile",
     *     tags={"User Profile"},
     *     summary="Update user profile",
     *     description="Update user profile and organization information",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile and organization information successfully updated."),
     *             @OA\Property(property="data", ref="#/components/schemas/UserProfile")
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
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email has already been taken.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->profileService->updateProfile($request->user(), $request->validated());

            return $this->successResponse($user, 'Profile and organization information successfully updated.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/profile/password",
     *     operationId="updateUserPassword",
     *     tags={"User Profile"},
     *     summary="Update user password",
     *     description="Update user password with current password verification",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password successfully updated.")
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
     *                     property="current_password",
     *                     type="array",
     *                     @OA\Items(type="string", example="The current password is incorrect.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $this->profileService->updatePassword($request->user(), $request->password);

            return $this->successResponse(null, 'Password successfully updated.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update password: ' . $e->getMessage(), 500);
        }
    }
}