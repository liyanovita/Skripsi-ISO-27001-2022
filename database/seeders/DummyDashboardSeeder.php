<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;
use Carbon\Carbon;

class DummyDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->error('No user found. Please run DatabaseSeeder first.');
            return;
        }

        // Only use standards that have questions (assessable controls = 122)
        $standards = IsoStandard::whereNotNull('questions')
            ->where('questions', '!=', '[]')
            ->where('questions', '!=', 'null')
            ->get();
        if ($standards->isEmpty()) {
            $this->command->error('No standards found.');
            return;
        }

        // Create 3 historical sessions
        $sessionsData = [
            [
                'name' => 'Q1 2026 Internal Audit',
                'status' => 'completed',
                'overall_maturity_score' => 2.1,
                'created_at' => Carbon::now()->subMonths(5),
                'updated_at' => Carbon::now()->addMinutes(1),
                'rating_offset' => 0
            ],
            [
                'name' => 'Q2 2026 External Assessment',
                'status' => 'completed',
                'overall_maturity_score' => 3.4,
                'created_at' => Carbon::now()->subMonths(2),
                'updated_at' => Carbon::now()->addMinutes(2),
                'rating_offset' => 1
            ],
            [
                'name' => 'Q3 2026 Compliance Review',
                'status' => 'in_progress',
                'overall_maturity_score' => 4.2,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->addMinutes(3),
                'rating_offset' => 2
            ]
        ];

        foreach ($sessionsData as $index => $data) {
            $session = AssessmentSession::updateOrCreate(
                ['name' => $data['name']],
                [
                    'user_id' => $user->id,
                    'status' => $data['status'],
                    'overall_maturity_score' => $data['overall_maturity_score'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at']
                ]
            );

            // Clear old results to prevent duplicates
            AssessmentResult::where('session_id', $session->id)->delete();

            // All sessions have all standards
            $controlsToEvaluate = $standards;

            foreach ($controlsToEvaluate as $controlIndex => $standard) {
                // For Q3 (in_progress), only score the first 60, the rest are unassessed
                // 60 out of 137 = ~44% progress, simulating an audit in progress
                $isUnassessed = ($index === 2 && $controlIndex >= 60);

                if ($isUnassessed) {
                    AssessmentResult::create([
                        'session_id' => $session->id,
                        'iso_standard_id' => $standard->id,
                        'maturity_rating' => 0,
                        'status' => 'not_started',
                        'answers' => null,
                        'notes' => null,
                        'ai_recommendation' => null,
                        'risk_priority' => null,
                        'treatment_due_date' => null,
                        'treatment_pic' => null,
                        'treatment_status' => 'open',
                        'created_at' => clone $data['created_at'],
                        'updated_at' => clone $data['updated_at'],
                    ]);
                } else {
                    // Maturity rating increases over time (using rating_offset)
                    $baseRating = ($controlIndex % 3) + 1; // 1 to 3
                    $rating = min(5, $baseRating + $data['rating_offset']);

                    // Make some tasks overdue even in Q3 to show the UI
                    $isOverdue = $rating < 4 && ($index === 0 || $controlIndex % 2 === 0);

                    AssessmentResult::create([
                        'session_id' => $session->id,
                        'iso_standard_id' => $standard->id,
                        'maturity_rating' => $rating,
                        'status' => 'completed',
                        'answers' => json_encode(['verified' => true]),
                        'notes' => 'Audited in ' . $data['name'],
                        'ai_recommendation' => 'This is a simulated AI recommendation for ' . $standard->code,
                        'risk_priority' => $rating < 3 ? 'High' : 'Low',
                        'treatment_due_date' => $rating < 4 ? ($isOverdue ? Carbon::now()->subDays(5) : Carbon::now()->addDays(15)) : Carbon::now(),
                        'treatment_pic' => $rating < 4 ? 'Jane Doe' : 'N/A',
                        'treatment_status' => $rating < 4 ? 'open' : 'closed',
                        'created_at' => clone $data['created_at'],
                        'updated_at' => clone $data['updated_at'],
                    ]);
                }
            }
            
            $total = $standards->count();
            $this->command->info("Created session: {$data['name']} with {$total} results.");
        }
    }
}
