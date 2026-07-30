<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\IsoStandard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyAuditSessionSeeder extends Seeder
{
    /**
     * Seed exactly 5 audit sessions enforcing:
     * - Draft sessions owned ONLY by Admin (hidden from regular users)
     * - User Liya has both In Progress (editable) and Completed (finalized & locked) sessions
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding Exactly 5 Audit Sessions according to exact user role visibility...');

        // Ensure IsoStandard master data exists
        if (IsoStandard::count() === 0) {
            $this->command->info('📦 Seeding ISO Standard master data first...');
            $this->call(IsoStandardSeeder::class);
        }

        $standards = IsoStandard::all();
        if ($standards->isEmpty()) {
            $this->command->error('❌ ISO Standards table is empty!');
            return;
        }

        // 1. Seed Organizations
        $orgKpk = Organization::updateOrCreate(
            ['code' => 'KPK'],
            [
                'name' => 'Komisi Pemberantasan Korupsi',
                'description' => 'Lembaga negara yang memiliki tugas memberantas korupsi secara profesional, intensif, dan berkesinambungan.',
                'address' => 'Jl. Kuningan Persada No.4, Guntur, Setiabudi, Jakarta Selatan',
                'contact_email' => 'info@kpk.go.id',
                'contact_phone' => '(021) 25578300',
                'business_sector' => 'Government & Public Sector',
                'organization_scale' => 'Large',
                'it_governance_structure' => 'Direktorat Deteksi dan Analisis Korupsi / IT Department',
                'isms_scope' => 'Seluruh layanan sistem informasi antikorupsi KPK.',
            ]
        );

        $orgBi = Organization::updateOrCreate(
            ['code' => 'BI'],
            [
                'name' => 'Bank Indonesia',
                'description' => 'Bank sentral Republik Indonesia yang memelihara kestabilan nilai rupiah.',
                'address' => 'Jl. M.H. Thamrin No.2, Jakarta Pusat',
                'contact_email' => 'contact@bi.go.id',
                'contact_phone' => '131',
                'business_sector' => 'Banking & Financial Services',
                'organization_scale' => 'Large',
                'it_governance_structure' => 'Departemen Teknologi Informasi Bank Indonesia',
                'isms_scope' => 'Sistem pembayaran nasional dan infrastruktur pasar keuangan BI.',
            ]
        );

        $orgTelkom = Organization::updateOrCreate(
            ['code' => 'TLKM'],
            [
                'name' => 'PT Telkom Indonesia (Persero) Tbk',
                'description' => 'BUMN penyedia layanan telekomunikasi dan jaringan terbesar di Indonesia.',
                'address' => 'Telkom Landmark Tower, Jl. Jend. Gatot Subroto No.Kav 52, Jakarta Selatan',
                'contact_email' => 'customercare@telkom.co.id',
                'contact_phone' => '147',
                'business_sector' => 'Telecommunication & Cloud Infrastructure',
                'organization_scale' => 'Enterprise',
                'it_governance_structure' => 'Directorate of Digital Business & Cybersecurity Governance',
                'isms_scope' => 'Layanan cloud data center, backbone jaringan nasional, dan aplikasi internal corporate.',
            ]
        );

        $orgKalbe = Organization::updateOrCreate(
            ['code' => 'KLBF'],
            [
                'name' => 'PT Kalbe Farma Tbk',
                'description' => 'Perusahaan farmasi dan kesehatan terbesar di Asia Tenggara.',
                'address' => 'Gedung KALBE, Jl. Let. Jend. Suprapto Kav 4, Cempaka Putih, Jakarta Pusat',
                'contact_email' => 'corp.sec@kalbe.co.id',
                'contact_phone' => '(021) 42873888',
                'business_sector' => 'Healthcare & Pharmaceuticals',
                'organization_scale' => 'Large',
                'it_governance_structure' => 'Corporate IT & Health Data Privacy Office',
                'isms_scope' => 'Sistem informasi riset R&D obat dan rekam medis pasien.',
            ]
        );

        // 2. Seed Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@audit.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'job_title' => 'Chief Information Security Officer (CISO)',
                'role_description' => 'Oversees governance, risk, and compliance management.',
            ]
        );

        $auditorAdmin = User::updateOrCreate(
            ['email' => 'auditor@audit.com'],
            [
                'name' => 'Auditor Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'job_title' => 'Lead ISO 27001 Auditor',
                'role_description' => 'Conducts independent ISMS audits and maturity reviews.',
            ]
        );

        $userLiya = User::updateOrCreate(
            ['email' => 'liya@gmail.com'],
            [
                'name' => 'Liya Novitasari',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
                'organization_id' => $orgKpk->id,
                'job_title' => 'IT Compliance & Risk Specialist',
                'role_description' => 'Manages IT control evidence collection, risk assessments, and corrective action plans.',
            ]
        );

        $userBudi = User::updateOrCreate(
            ['email' => 'budi@kpk.go.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
                'organization_id' => $orgKpk->id,
                'job_title' => 'Lead Infrastructure Engineer',
                'role_description' => 'Responsible for cloud security, network access, and backup controls.',
            ]
        );

        $userSiti = User::updateOrCreate(
            ['email' => 'siti@bi.go.id'],
            [
                'name' => 'Siti Aminah',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
                'organization_id' => $orgBi->id,
                'job_title' => 'Information Security Officer',
                'role_description' => 'Leads ISMS implementation and financial cybersecurity compliance.',
            ]
        );

        $userFarhan = User::updateOrCreate(
            ['email' => 'farhan@telkom.co.id'],
            [
                'name' => 'Farhan Hidayat',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
                'organization_id' => $orgTelkom->id,
                'job_title' => 'Cloud & Network Security Manager',
                'role_description' => 'Oversees data center physical & logical security, network perimeter, and incident handling.',
            ]
        );

        // SoA Exclusions & Action Plans References
        $excludedCodes = [
            'A.5.7'  => 'Organisasi tidak mengelola threat intelligence secara mandiri, melainkan berlangganan layanan Managed SOC pihak ketiga.',
            'A.8.11' => 'Penyembunyian data (masking) tidak dilakukan di produksi karena seluruh data sensitif menggunakan enkripsi AES-256 tingkat kolom.',
            'A.8.14' => 'Seluruh infrastruktur menggunakan arsitektur cloud Multi-AZ (AWS/GCP), tanpa fasilitas pemrosesan fisik cadangan mandiri.',
        ];

        $sampleActionPlans = [
            'A.5.1'  => 'Melakukan kaji ulang tahunan Kebijakan Keamanan Informasi dan menyelaraskan dengan ISO 27001:2022.',
            'A.5.15' => 'Menerapkan kebijakan Least Privilege dan kontrol RBAC terpusat serta tinjauan hak akses berkala 3 bulanan.',
            'A.5.24' => 'Membentuk Tim CSIRT terdaftar dan mengesahkan SOP Penanganan Insiden Keamanan Informasi.',
            'A.7.2'  => 'Menambah kamera CCTV pada akses masuk ruang server dan mengimplementasikan log pemindaian biometrik.',
            'A.8.7'  => 'Mengimplementasikan EDR terpusat pada seluruh laptop pegawai dan server produksi dengan pembaruan otomatis.',
            'A.8.8'  => 'Melakukan pemindaian kerentanan (Vulnerability Scan) bulanan dan uji penetrasi (Pentest) tahunan.',
            'A.8.12' => 'Mengaktifkan aturan Data Loss Prevention (DLP) pada email gateway untuk memblokir pengiriman dokumen terklasifikasi Rahasia.',
            'A.8.16' => 'Mengonfigurasikan SIEM terpusat untuk agregasi log sistem 24/7 dan alert otomatis anomali login admin.',
        ];

        $picNames = ['Liya Novitasari', 'Budi Santoso', 'Siti Aminah', 'Farhan Hidayat', 'Auditor Admin'];

        // Helper Lambda to seed results for a session
        $seedSessionResults = function (AssessmentSession $session, string $seedKey, array $ratingDistribution, string $defaultStatus = 'completed', ?int $completionLimit = null, ?array $customPics = null) use ($standards, $excludedCodes, $sampleActionPlans, $picNames) {
            $activePics = $customPics ?? $picNames;
            foreach ($standards as $index => $standard) {
                $hasQuestions = is_array($standard->questions) && count($standard->questions) > 0;
                $isAssessed = ($completionLimit === null) || ($index < $completionLimit);
                $isExcluded = isset($excludedCodes[$standard->code]) && rand(0, 1) === 1;
                $isApplicable = !$isExcluded;

                $rating = null;
                if ($hasQuestions && $isAssessed && $isApplicable) {
                    $hash = crc32($standard->code . $seedKey);
                    $mod = $hash % 100;
                    
                    $accum = 0;
                    foreach ($ratingDistribution as $scoreVal => $percentage) {
                        $accum += $percentage;
                        if ($mod < $accum) {
                            $rating = $scoreVal;
                            break;
                        }
                    }
                    if ($rating === null) $rating = 3;
                }

                $treatmentStatus = 'open';
                $treatmentProgress = 0;
                $treatmentPic = null;
                $treatmentDueDate = null;
                $actionPlanText = null;
                $evidenceAfter = null;
                $notes = null;

                if ($isApplicable) {
                    $treatmentStatus = 'open';
                    $treatmentProgress = 0;
                    $notes = $isAssessed ? "Penilaian kontrol {$standard->code} berdasarkan wawancara audit dan pengujian bukti teknis." : null;

                    if ($rating !== null && $rating < 5) {
                        $gapMod = crc32($standard->code . $seedKey . 'gap') % 10;
                        $treatmentPic = $activePics[$gapMod % count($activePics)];
                        $actionPlanText = $sampleActionPlans[$standard->code] ?? "Pelaksanaan perbaikan dan pemenuhan bukti audit untuk kontrol {$standard->code}.";

                        if ($gapMod < 4) {
                            $treatmentStatus = 'closed';
                            $treatmentProgress = 100;
                            $treatmentDueDate = now()->subDays(rand(5, 30))->toDateString();
                            $evidenceAfter = "evidence/SOP_{$standard->code}_Approved.pdf";
                            if ($isAssessed) $notes .= " Perbaikan kontrol telah selesai dan terverifikasi.";
                        } elseif ($gapMod < 7) {
                            $treatmentStatus = 'in_progress';
                            $treatmentProgress = rand(25, 80);
                            $treatmentDueDate = now()->addDays(rand(10, 45))->toDateString();
                            if ($isAssessed) $notes .= " Implementasi perbaikan sedang berlangsung sesuai target SLA.";
                        } else {
                            $treatmentStatus = 'open';
                            $treatmentProgress = 0;
                            $treatmentDueDate = now()->addDays(rand(15, 60))->toDateString();
                            if ($isAssessed) $notes .= " Menunggu jadwal alokasi sumber daya.";
                        }
                    }
                }

                $aiRecommendation = null;
                $aiActionPlan = null;
                $aiInsight = null;
                $aiImpact = null;

                if ($isApplicable && $rating !== null) {
                    // 1. Strategic Recommendation (Without "Rekomendasi AI: " prefix as requested)
                    $aiRecommendation = "Lakukan penguatan tata kelola keamanan, pembaruan prosedur kerja standar, dan integrasi pemantauan otomatis untuk kontrol {$standard->code} secara berkala.";
                    
                    // 2. Corrective Action Plan
                    $planText = $actionPlanText ?: "1. Lakukan evaluasi dan pembaruan dokumen kebijakan kontrol {$standard->code}.\n2. Sosialisasikan prosedur kerja standar kepada tim operasional terkait.\n3. Lakukan pengujian berkala untuk memastikan efektivitas kontrol.";
                    $aiActionPlan = ['action' => $planText];

                    // 3. AI Audit Insight (Gap)
                    $aiInsight = ['gap' => "Terdapat celah pada efektivitas penerapan kontrol {$standard->code}, di mana dokumentasi prosedur belum sepenuhnya diperbarui dan pengawasan berkala belum berjalan otomatis."];

                    // 4. Impact Interpretation
                    $aiImpact = "Kelemahan pada kontrol {$standard->code} berpotensi meningkatkan risiko insiden keamanan informasi, kebocoran data operasional, dan ketidakpatuhan terhadap standar sertifikasi ISO/IEC 27001:2022.";
                }

                AssessmentResult::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'iso_standard_id' => $standard->id,
                    ],
                    [
                        'is_applicable' => $isApplicable,
                        'soa_justification' => $isExcluded ? $excludedCodes[$standard->code] : null,
                        'maturity_rating' => $isApplicable ? $rating : null,
                        'status' => $isAssessed ? $defaultStatus : 'in_progress',
                        'answers' => ($isApplicable && $hasQuestions && $isAssessed && $rating !== null) ? json_encode(['compliance' => ($rating >= 4 ? 'full' : ($rating >= 2 ? 'partial' : 'none')), 'verified' => true]) : null,
                        'notes' => $notes,
                        'evidence_file' => ($isApplicable && $isAssessed) ? ["evidence/SOP_{$standard->code}_Policy.pdf"] : null,
                        'ai_recommendation' => $aiRecommendation,
                        'control_insight' => $aiInsight,
                        'impact_interpretation' => $aiImpact,
                        'treatment_status' => $treatmentStatus,
                        'treatment_progress' => $treatmentProgress,
                        'treatment_pic' => $treatmentPic,
                        'treatment_due_date' => $treatmentDueDate,
                        'corrective_action_plan' => $aiActionPlan,
                        'evidence_after_improvement' => $evidenceAfter ? [$evidenceAfter] : null,
                    ]
                );
            }

            $session->calculateMaturityScore();
            if ($session->ai_summary) {
                $aiService = new \App\Services\Intelligence\AiSummaryService();
                $session->update(['ai_summary_hash' => $aiService->computeSessionHash($session)]);
            }
        };

        // Reset existing audit sessions table cleanly
        AssessmentSession::query()->delete();

        // ---------------------------------------------------------------------
        // SESSION 1: User Liya - KPK Audit 2026 (IN_PROGRESS - Partial 110/137)
        // ---------------------------------------------------------------------
        $this->command->info('📌 Seeding Session 1/6: KPK Audit 2026 (User Liya - Status: IN_PROGRESS)...');
        $session1 = AssessmentSession::create([
            'user_id' => $userLiya->id,
            'organization_id' => $orgKpk->id,
            'name' => 'ISO 27001:2022 Surveillance Audit 2026 - KPK',
            'status' => 'in_progress',
            'overall_maturity_score' => 3.42,
            'deadline' => now()->addDays(30)->toDateString(),
            'ai_summary' => 'Audit evaluasi tingkat kematangan SMKI KPK sedang berjalan. User Liya Novitasari dapat melakukan remedi dan pembaruan bukti kontrol.',
        ]);
        $session1->invitedUsers()->sync([$userLiya->id => ['role' => 'lead'], $userBudi->id => ['role' => 'auditor']]);
        $seedSessionResults($session1, 'kpk2026_active', [1 => 5, 2 => 15, 3 => 45, 4 => 25, 5 => 10], 'completed', 110);

        // ---------------------------------------------------------------------
        // SESSION 1B: User Liya - KPK Internal Audit (IN_PROGRESS - 100% Filled 137/137 - Pending Finalization)
        // ---------------------------------------------------------------------
        $this->command->info('📌 Seeding Session 1B/6: KPK Internal Audit (User Liya - Status: IN_PROGRESS, 100% Filled)...');
        $session1b = AssessmentSession::create([
            'user_id' => $userLiya->id,
            'organization_id' => $orgKpk->id,
            'name' => 'ISO 27001:2022 Internal Security Audit 2026 - KPK (Pending Finalization)',
            'status' => 'in_progress',
            'overall_maturity_score' => 3.85,
            'deadline' => now()->addDays(45)->toDateString(),
            'ai_summary' => 'Pengisian 137/137 kontrol telah 100% selesai dinilai, namun status masih IN_PROGRESS karena belum di-finalize oleh Lead.',
        ]);
        $session1b->invitedUsers()->sync([$userLiya->id => ['role' => 'lead'], $userBudi->id => ['role' => 'auditor']]);
        $seedSessionResults($session1b, 'kpk2026_full_in_progress', [1 => 0, 2 => 10, 3 => 30, 4 => 40, 5 => 20], 'completed', 137);

        // ---------------------------------------------------------------------
        // SESSION 2: User Liya - KPK Audit 2025 (COMPLETED - Finalized & Expired Deadline -> LOCKED)
        // ---------------------------------------------------------------------
        $this->command->info('📌 Seeding Session 2/6: KPK Audit 2025 (User Liya - Status: COMPLETED, Expired -> Locked)...');
        $session2 = AssessmentSession::create([
            'user_id' => $userLiya->id,
            'organization_id' => $orgKpk->id,
            'name' => 'ISO 27001:2022 Baseline Audit 2025 - KPK (Finalized & Locked)',
            'status' => 'completed',
            'overall_maturity_score' => 2.22,
            'deadline' => now()->subYear()->toDateString(),
            'ai_summary' => 'Audit siklus 2025 KPK telah selesai dan deadline telah lalu. Sesi ini terkunci (read-only) sebagai perbandingan historis.',
            'created_at' => now()->subYear(),
        ]);
        $session2->invitedUsers()->sync([$userLiya->id => ['role' => 'lead'], $userBudi->id => ['role' => 'auditor']]);
        $seedSessionResults($session2, 'kpk2025_final', [1 => 20, 2 => 50, 3 => 20, 4 => 10, 5 => 0]);

        // ---------------------------------------------------------------------
        // SESSION 3: User Liya - KPK Recertification 2026 (COMPLETED - Active Future Deadline -> NOT LOCKED)
        // ---------------------------------------------------------------------
        $this->command->info('📌 Seeding Session 3/6: KPK Recertification 2026 (User Liya - Status: COMPLETED, Future Deadline -> NOT LOCKED)...');
        $session3 = AssessmentSession::create([
            'user_id' => $userLiya->id,
            'organization_id' => $orgKpk->id,
            'name' => 'ISO 27001:2022 ISMS Recertification 2026 - KPK (Active SLA)',
            'status' => 'completed',
            'overall_maturity_score' => 4.15,
            'deadline' => now()->addDays(90)->toDateString(),
            'ai_summary' => 'Audit resertifikasi SMKI 2026 KPK telah selesai dinilai (Completed) tetapi deadline belum berakhir (Active SLA). Sesi ini TIDAK TERKUNCI dan dapat diperbarui.',
        ]);
        $session3->invitedUsers()->sync([$userLiya->id => ['role' => 'lead'], $userBudi->id => ['role' => 'auditor']]);
        $seedSessionResults($session3, 'kpk2026_unlocked_completed', [1 => 0, 2 => 5, 3 => 20, 4 => 45, 5 => 30]);

        // ---------------------------------------------------------------------
        // SESSION 3: User Siti - Bank Indonesia (COMPLETED - Finalized & Locked Read-Only)
        // ---------------------------------------------------------------------
        $this->command->info('📌 Seeding Session 3/5: Bank Indonesia Audit (User Siti - Status: COMPLETED Finalized)...');
        $session3 = AssessmentSession::create([
            'user_id' => $userSiti->id,
            'organization_id' => $orgBi->id,
            'name' => 'ISO 27001:2022 Financial Sector Security Audit - Bank Indonesia',
            'status' => 'completed',
            'overall_maturity_score' => 4.08,
            'deadline' => now()->addDays(60)->toDateString(),
            'ai_summary' => 'Audit resertifikasi sektor keuangan Bank Indonesia telah selesai (finalized). Sesi terkunci read-only.',
        ]);
        $session3->invitedUsers()->sync([$userSiti->id => ['role' => 'lead'], $userLiya->id => ['role' => 'auditor']]);
        $seedSessionResults($session3, 'bi2026_final', [1 => 0, 2 => 5, 3 => 15, 4 => 50, 5 => 30], 'completed', null, ['Siti Aminah', 'Liya Novitasari']);

        // ---------------------------------------------------------------------
        // SESSION 4: User Farhan - Telkom Indonesia (IN_PROGRESS - Active & Editable for Users)
        // ---------------------------------------------------------------------
        $this->command->info('📌 Seeding Session 4/5: Telkom Infrastructure Audit (User Farhan - Status: IN_PROGRESS)...');
        $session4 = AssessmentSession::create([
            'user_id' => $userFarhan->id,
            'organization_id' => $orgTelkom->id,
            'name' => 'ISO 27001:2022 Cloud Data Center Infrastructure Audit - Telkom',
            'status' => 'in_progress',
            'overall_maturity_score' => 2.90,
            'deadline' => now()->addDays(45)->toDateString(),
            'ai_summary' => 'Audit infrastruktur data center Telkom sedang berlangsung. Pengguna dapat mengupdate rencana remedi.',
        ]);
        $session4->invitedUsers()->sync([$userFarhan->id => ['role' => 'lead'], $auditorAdmin->id => ['role' => 'auditor']]);
        $seedSessionResults($session4, 'tlkm2026_active', [1 => 10, 2 => 30, 3 => 40, 4 => 15, 5 => 5], 'completed', 95, ['Farhan Hidayat', 'Auditor Admin']);

        // ---------------------------------------------------------------------
        // SESSION 5: Kalbe Farma (Status: IN_PROGRESS)
        // ---------------------------------------------------------------------
        $this->command->info('📌 Seeding Session 5/5: Kalbe Farma R&D Assessment (Status: IN_PROGRESS)...');
        $session5 = AssessmentSession::create([
            'user_id' => $admin->id,
            'organization_id' => $orgKalbe->id,
            'name' => 'ISO 27001:2022 Health Data & R&D ISMS Initial Assessment - Kalbe Farma',
            'status' => 'in_progress',
            'overall_maturity_score' => 1.85,
            'deadline' => now()->addDays(150)->toDateString(),
            'ai_summary' => 'Penilaian awal SMKI untuk sistem R&D dan rekam medis Kalbe Farma sedang berjalan.',
        ]);
        $session5->invitedUsers()->sync([$admin->id => ['role' => 'lead']]);
        $seedSessionResults($session5, 'klbf2026_active', [1 => 45, 2 => 35, 3 => 15, 4 => 5, 5 => 0], 'completed', 40, ['Administrator', 'Auditor Admin']);

        $this->command->info('🎉 SUCCESS! Exactly 5 Audit Sessions successfully seeded and scores accurately calculated!');
        $this->command->table(
            ['ID', 'Session Name', 'Owner / Org', 'Status & User Access Visibility', 'Maturity Score', 'Compliance %', 'Classification'],
            AssessmentSession::with(['organization', 'user'])->get()->map(function ($s) {
                $accessRule = match($s->status) {
                    'draft' => 'DRAFT (Khusus Admin)',
                    'completed' => 'COMPLETED (Finalized & Locked Read-Only)',
                    'in_progress' => 'IN_PROGRESS (Aktif & Editable)',
                    default => strtoupper($s->status),
                };
                return [
                    $s->id,
                    $s->name,
                    ($s->user->name ?? 'Admin') . ' (' . ($s->organization->code ?? 'N/A') . ')',
                    $accessRule,
                    number_format($s->overall_maturity_score, 2) . ' / 5.00',
                    $s->compliance_score . '%',
                    $s->compliance_status,
                ];
            })
        );
    }
}
