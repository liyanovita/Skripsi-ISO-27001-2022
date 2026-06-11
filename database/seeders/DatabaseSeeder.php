<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AssessmentSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run ISO Master Data & Knowledge Base
        $this->call([
            IsoStandardSeeder::class,
            KnowledgeBaseSeeder::class,
        ]);

        // 2. Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@audit.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // 3. Create Common User
        $user = User::updateOrCreate(
            ['email' => 'liya@gmail.com'],
            [
                'name' => 'Liya',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ]
        );

        // 3. Create 1 Example Assessment Session (To populate the dashboard)
        $session = AssessmentSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Initial Audit ISO 27001:2022'
            ],
            [
                'status' => 'completed',
                'overall_maturity_score' => 2.4, // Example initial score
            ]
        );

        // 4. Generate Assessment Results for Example Session
        $standards = \App\Models\IsoStandard::all();
        foreach ($standards as $index => $standard) {
            \App\Models\AssessmentResult::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'iso_standard_id' => $standard->id,
                ],
                [
                    'maturity_rating' => ($index % 5) + 1, // Score variation 1-5
                    'status' => 'completed',
                    'answers' => json_encode(['verified' => true]),
                    'notes' => 'Initial review of standard compliance.',
                    'ai_recommendation' => 'Improve documentation and provide periodic implementation evidence.',
                    'risk_priority' => (($index % 5) + 1) < 3 ? 'High' : 'Low',
                ]
            );
        }

        // 5. Build rich Community Templates with actual content_data
        $commonUser = User::first();
        $allResults = \App\Models\AssessmentResult::with('standard')
            ->where('session_id', $session->id)
            ->get();

        // Payload builder
        $buildPayload = fn($results) => [
            'session' => ['overall_maturity_score' => $session->overall_maturity_score],
            'results' => $results->map(fn($r) => [
                'iso_standard_id' => $r->iso_standard_id,
                'maturity_rating' => $r->maturity_rating,
                'status'          => $r->status,
                'notes'           => $r->notes,
                'answers'         => $r->answers,
            ])->values()->toArray(),
        ];

        $templates = [
            [
                'title'       => 'Fintech Compliance Framework',
                'description' => 'Control optimization for payment & e-wallet startups complying with local banking standards and ISO 27001:2022. Includes Annex A controls covering encryption, access control, and incident response.',
                'author_name' => 'SecOps Mastery',
                'tags'        => ['Fintech', 'High-Risk', 'Annex A'],
                'base_score'  => 3.2,
                'downloads_count' => 1240,
                'upvotes'     => 87,
                'rating_sum'  => 219,
                'rating_count'=> 48,
            ],
            [
                'title'       => 'Cloud Native Readiness Audit',
                'description' => 'ISO 27001 control mapping tailored for AWS/Azure infrastructure focusing on container security, SIEM integration, and cloud identity management.',
                'author_name' => 'Cloud Architect ID',
                'tags'        => ['Cloud', 'DevSecOps', 'AWS'],
                'base_score'  => 2.8,
                'downloads_count' => 850,
                'upvotes'     => 54,
                'rating_sum'  => 162,
                'rating_count'=> 36,
            ],
            [
                'title'       => 'SME / UMKM Starter Pack',
                'description' => 'Lightweight ISO 27001 compliance template designed for small businesses and startups. Focuses on minimal viable controls for rapid certification readiness.',
                'author_name' => 'Open GRC Community',
                'tags'        => ['SME', 'Starter', 'Lightweight'],
                'base_score'  => 2.1,
                'downloads_count' => 2100,
                'upvotes'     => 143,
                'rating_sum'  => 344,
                'rating_count'=> 71,
            ],
            [
                'title'       => 'Healthcare ISMS Blueprint',
                'description' => 'ISO 27001 framework specialized for hospitals and health-tech platforms. Integrates medical data privacy requirements alongside standard information security controls.',
                'author_name' => 'HealthSec Indonesia',
                'tags'        => ['Healthcare', 'Privacy', 'Medical'],
                'base_score'  => 3.5,
                'downloads_count' => 620,
                'upvotes'     => 39,
                'rating_sum'  => 117,
                'rating_count'=> 26,
            ],
        ];

        foreach ($templates as $tpl) {
            \App\Models\CommunityTemplate::updateOrCreate(
                ['title' => $tpl['title']],
                array_merge($tpl, [
                    'user_id'      => $commonUser->id,
                    'content_data' => $buildPayload($allResults->random(min(20, $allResults->count()))),
                ])
            );
        }
    }
}