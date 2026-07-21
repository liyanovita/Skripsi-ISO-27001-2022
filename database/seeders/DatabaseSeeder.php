<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AssessmentSession;
use App\Models\Organization;
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

        // Seed Organizations
        $org1 = Organization::updateOrCreate(
            ['code' => 'KPK'],
            [
                'name' => 'Komisi Pemberantasan Korupsi',
                'description' => 'Lembaga negara yang memiliki tugas memberantas korupsi secara profesional, intensif, dan berkesinambungan.',
                'address' => 'Jl. Kuningan Persada No.4, RT.1/RW.6, Guntur, Kec. Setiabudi, Jakarta Selatan',
                'contact_email' => 'info@kpk.go.id',
                'contact_phone' => '(021) 25578300',
                'business_sector' => 'Government & Public Sector',
                'organization_scale' => 'Besar',
                'it_governance_structure' => 'Direktorat Deteksi dan Analisis Korupsi / IT Department',
                'isms_scope' => 'Seluruh layanan sistem informasi antikorupsi KPK.',
            ]
        );

        $org2 = Organization::updateOrCreate(
            ['code' => 'BI'],
            [
                'name' => 'Bank Indonesia',
                'description' => 'Bank sentral Republik Indonesia yang memelihara kestabilan nilai rupiah.',
                'address' => 'Jl. M.H. Thamrin No.2, Jakarta Pusat',
                'contact_email' => 'contact@bi.go.id',
                'contact_phone' => '131',
                'business_sector' => 'Banking & Financial Services',
                'organization_scale' => 'Besar',
                'it_governance_structure' => 'Departemen Teknologi Informasi Bank Indonesia',
                'isms_scope' => 'Sistem pembayaran nasional dan infrastruktur pasar keuangan BI.',
            ]
        );

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

        // Create Admin User (Assessment Manager)
        User::updateOrCreate(
            ['email' => 'auditor@audit.com'],
            [
                'name' => 'Auditor Admin',
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
                'organization_id' => $org1->id,
            ]
        );

        // 3. Create 1 Example Assessment Session (To populate the dashboard)
        $session = AssessmentSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Initial Audit ISO 27001:2022'
            ],
            [
                'organization_id' => Organization::where('code', 'KPK')->first()?->id,
                'status' => 'completed',
                'overall_maturity_score' => 2.4, // Example initial score
                'deadline' => now()->addMonth()->toDateString(),
            ]
        );

        $session->invitedUsers()->sync([
            $user->id => ['role' => 'lead']
        ]);

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


    }
}