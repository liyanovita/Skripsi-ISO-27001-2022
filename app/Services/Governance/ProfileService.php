<?php

namespace App\Services\Governance;

use App\Models\User;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;

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

        // Calculate compliance score using trait method
        $complianceScore = $totalSessions > 0
            ? $this->calculateCompliancePercentage($avgMaturity)
            : 0;

        $auditStats = [
            'total_sessions' => $totalSessions,
            'avg_maturity' => $avgMaturity,
            'total_controls' => $totalControls,
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

        // 1. Separate user fields and organization fields
        $userFields = ['name', 'email'];
        $userData = array_intersect_key($data, array_flip($userFields));

        if (array_key_exists('email', $userData) && $userData['email'] !== $user->email) {
            $userData['email_verified_at'] = null;
        }

        // Update user name/email if any
        if (!empty($userData)) {
            $user->update($userData);
        }

        // 2. Extract organization fields
        $orgFieldsMap = [
            'organization_name' => 'name',
            'organization_scale' => 'organization_scale',
            'business_sector' => 'business_sector',
            'isms_scope' => 'isms_scope',
            'it_governance_structure' => 'it_governance_structure',
            'organization_description' => 'description',
        ];

        $orgData = [];
        foreach ($orgFieldsMap as $inputKey => $dbKey) {
            if (array_key_exists($inputKey, $data)) {
                $orgData[$dbKey] = $data[$inputKey];
            }
        }

        // Only process organization update if there's any organization data passed
        if (!empty($orgData)) {
            if ($user->organization_id && $user->organization) {
                // Update existing organization
                $user->organization->update($orgData);
            } else {
                // Create a new organization
                $orgName = $orgData['name'] ?? ($user->name . ' Organization');
                $orgData['name'] = $orgName;
                
                // Generate clean unique code
                $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $orgName), 0, 5));
                if (empty($baseCode)) {
                    $baseCode = 'ORG';
                }
                
                $code = $baseCode;
                $counter = 1;
                while (\App\Models\Organization::where('code', $code)->exists()) {
                    $code = $baseCode . $counter;
                    $counter++;
                }
                $orgData['code'] = $code;

                $organization = \App\Models\Organization::create($orgData);
                $user->update(['organization_id' => $organization->id]);
            }
        }

        return $user->fresh();
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
