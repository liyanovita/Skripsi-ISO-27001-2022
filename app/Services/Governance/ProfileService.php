<?php

namespace App\Services\Governance;

use App\Models\User;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\CommunityTemplate;
use App\Services\Traits\MaturityHelper;
use App\Services\Traits\SessionLoader;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    use MaturityHelper, SessionLoader;

    /**
     * Get profile data
     */
    public function getProfileData(User $user): array
    {
        return $this->buildProfileData($user);
    }

    /**
     * Build profile data
     */
    private function buildProfileData(User $user): array
    {
        $userId = $user->id;

        // Load sessions with results in one query using trait method
        $sessions = $this->loadUserSessions($userId);

        // Calculate stats using database queries
        $totalSessions = $sessions->count();
        $avgMaturity = round($sessions->avg('overall_maturity_score') ?? 0, 2);

        // Count completed results using database query
        $totalControls = AssessmentResult::whereIn('session_id', $sessions->pluck('id'))
            ->where('status', 'completed')
            ->count();

        // Count community templates using database query
        $communityShared = CommunityTemplate::where('user_id', $userId)->count();

        // Calculate compliance score using trait method
        $complianceScore = $totalSessions > 0
            ? $this->calculateCompliancePercentage($avgMaturity)
            : 0;

        $auditStats = [
            'total_sessions' => $totalSessions,
            'avg_maturity' => $avgMaturity,
            'total_controls' => $totalControls,
            'community_shared' => $communityShared,
            'compliance_score' => $complianceScore,
        ];

        // Get recent sessions with result counts
        $recentSessions = AssessmentSession::where('user_id', $userId)
            ->withCount('results')
            ->latest()
            ->take(5)
            ->get();

        return compact('user', 'auditStats', 'recentSessions');
    }

    /**
     * Update user profile
     * 
     * Authorization: Only the user can update their own profile
     */
    public function updateProfile(User $user, array $data): User
    {
        // Verify ownership
        if ($user->id !== auth()->id()) {
            throw new \Exception('Unauthorized: You can only update your own profile.');
        }

        // Validate data
        $allowedFields = [
            'name', 'email',
            'organization_name', 'organization_scale',
            'business_sector', 'isms_scope',
            'it_governance_structure', 'organization_description',
        ];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            throw new \Exception('No valid fields to update.');
        }

        if (array_key_exists('email', $updateData) && $updateData['email'] !== $user->email) {
            $updateData['email_verified_at'] = null;
        }

        $user->update($updateData);

        return $user;
    }

    /**
     * Update user password
     * 
     * Authorization: Only the user can update their own password
     */
    public function updatePassword(User $user, string $newPassword): bool
    {
        // Verify ownership
        if ($user->id !== auth()->id()) {
            throw new \Exception('Unauthorized: You can only update your own password.');
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            throw new \Exception('Password must be at least 8 characters long.');
        }

        return $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
