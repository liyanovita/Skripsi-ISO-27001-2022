<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AssessmentSession;
use App\Models\AssessmentResult;
use App\Models\Organization;
use App\Models\IsoStandard;
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

        // 2. Seed Organizations
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

        // 3. Seed Users
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
                'job_title' => 'Lead Lead ISO Auditor',
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

        // 4. Seed Audit Session 1 (KPK - Completed Audit)
        $session1 = AssessmentSession::updateOrCreate(
            [
                'user_id' => $userLiya->id,
                'name' => 'ISO 27001:2022 Comprehensive Audit - KPK 2026',
            ],
            [
                'organization_id' => $orgKpk->id,
                'status' => 'completed',
                'overall_maturity_score' => 3.33,
                'deadline' => now()->addMonth()->toDateString(),
                'ai_summary' => 'Initial audit completed across all 137 standards. High compliance observed in Annex A.5 (Organizational Controls) and Annex A.7 (Physical Controls). Immediate CAPA attention required for Annex A.8 (Technological Controls) specifically access control logging, vulnerability management, and incident response automation.',
            ]
        );

        $session1->invitedUsers()->sync([
            $userLiya->id => ['role' => 'lead'],
            $userBudi->id => ['role' => 'auditor'],
        ]);

        $standards = IsoStandard::all();
        $picList = ['Liya Novitasari', 'Budi Santoso', 'Auditor Admin', 'Ahmad Dani (SecOps)'];

        $excludedCodes = [
            'A.5.7' => 'Organisasi tidak mengelola ancaman intelijen secara mandiri melainkan mengandalkan penyedia layanan terkelola (Managed SOC/MSP).',
            'A.8.11' => 'Tidak dilakukan masking data pada lingkungan produksi karena seluruh data sensitif dienkripsi di tingkat kolom (Column-Level Encryption).',
            'A.8.14' => 'Seluruh infrastruktur 100% cloud-native (AWS Multi-AZ), sehingga tidak memelihara fasilitas pemrosesan fisik cadangan sendiri.',
        ];

        $actionPlans = [
            'A.5.1' => 'Menyusun dan mengesahkan ulang Kebijakan Keamanan Informasi sesuai ISO 27001:2022 serta mendistribusikan ke seluruh pegawai.',
            'A.5.15' => 'Menerapkan sistem kontrol akses berbasis peran (RBAC) dan meninjau hak akses pengguna secara berkala setiap 3 bulan.',
            'A.5.24' => 'Membentuk Tim Tanggap Insiden Siber (CSIRT) dan mengesahkan Standard Operating Procedure (SOP) Penanganan Insiden Keamanan.',
            'A.7.2' => 'Memasang perangkat pemantauan CCTV tambahan di area fisik sensitif dan memperbarui log akses fisik pengunjung.',
            'A.8.7' => 'Menginstal solusi EDR/Antivirus terpusat pada seluruh workstation dan server produksi dengan pembaruan definisi otomatis.',
            'A.8.8' => 'Melakukan pemindaian kerentanan (Vulnerability Assessment) secara rutin bulanan dan patching sistem kritis maksimal 7 hari.',
            'A.8.12' => 'Mengaktifkan fitur Data Loss Prevention (DLP) pada email korporat dan endpoint untuk mencegah kebocoran data terklasifikasi.',
            'A.8.16' => 'Mengonfigurasikan SIEM untuk mengumpulkan dan memantau log aktivitas jaringan serta audit log sistem secara 24/7.',
        ];

        $resultCount = 0;
        $totalRatingSum = 0;

        foreach ($standards as $index => $standard) {
            $hasQuestions = is_array($standard->questions) && count($standard->questions) > 0;
            $isExcluded = isset($excludedCodes[$standard->code]);
            $isApplicable = !$isExcluded;

            if (!$hasQuestions || $isExcluded) {
                $rating = null;
                $status = 'completed';
            } else {
                $hash = crc32($standard->code);
                $mod = $hash % 10;
                if ($mod < 2) {
                    $rating = 1;
                } elseif ($mod < 5) {
                    $rating = rand(2, 3);
                } elseif ($mod < 8) {
                    $rating = 4;
                } else {
                    $rating = 5;
                }
                $status = 'completed';
                $resultCount++;
                $totalRatingSum += $rating;
            }

            $treatmentStatus = null;
            $treatmentProgress = 0;
            $treatmentPic = null;
            $treatmentDueDate = null;
            $actionPlanText = null;
            $evidenceAfter = null;

            if ($isApplicable && $rating !== null && $rating < 5) {
                $gapMod = crc32($standard->code . 'gap') % 10;
                if ($gapMod < 3) {
                    $treatmentStatus = 'completed';
                    $treatmentProgress = 100;
                    $treatmentPic = $picList[$gapMod % count($picList)];
                    $treatmentDueDate = now()->subDays(rand(5, 30))->toDateString();
                    $actionPlanText = $actionPlans[$standard->code] ?? "Memperbarui dokumentasi, prosedur kerja, dan bukti pelaksanaan untuk kontrol {$standard->code}.";
                    $evidenceAfter = "Dokumen SOP resmi, Log Sistem Terverifikasi, dan Berita Acara Pelaksanaan.";
                } elseif ($gapMod < 6) {
                    $treatmentStatus = 'in_progress';
                    $treatmentProgress = rand(25, 75);
                    $treatmentPic = $picList[$gapMod % count($picList)];
                    $treatmentDueDate = now()->addDays(rand(10, 45))->toDateString();
                    $actionPlanText = $actionPlans[$standard->code] ?? "Melaksanakan sosialisasi, konfigurasi teknis, dan verifikasi penerapan kontrol {$standard->code}.";
                } elseif ($gapMod < 8) {
                    $treatmentStatus = 'open';
                    $treatmentProgress = 0;
                    $treatmentPic = $picList[$gapMod % count($picList)];
                    $treatmentDueDate = now()->addDays(rand(15, 60))->toDateString();
                    $actionPlanText = $actionPlans[$standard->code] ?? "Menyusun rencana tindakan perbaikan dan mengalokasikan sumber daya untuk kontrol {$standard->code}.";
                } else {
                    $treatmentStatus = 'in_progress';
                    $treatmentProgress = rand(10, 40);
                    $treatmentPic = $picList[$gapMod % count($picList)];
                    $treatmentDueDate = now()->subDays(rand(3, 20))->toDateString();
                    $actionPlanText = $actionPlans[$standard->code] ?? "Mempercepat penyelesaian perbaikan kontrol {$standard->code} yang mengalami keterlambatan SLA.";
                }
            }

            AssessmentResult::updateOrCreate(
                [
                    'session_id' => $session1->id,
                    'iso_standard_id' => $standard->id,
                ],
                [
                    'is_applicable' => $isApplicable,
                    'soa_justification' => $isExcluded ? $excludedCodes[$standard->code] : null,
                    'maturity_rating' => $rating,
                    'status' => $status,
                    'answers' => $hasQuestions ? json_encode(['compliance' => $rating >= 4 ? 'full' : ($rating >= 2 ? 'partial' : 'none'), 'verified' => true]) : null,
                    'notes' => $isExcluded ? 'Kontrol dikecualikan dalam SoA.' : "Penilaian kontrol {$standard->code} berdasarkan bukti verifikasi dan wawancara audit.",
                    'ai_recommendation' => $rating !== null && $rating < 5 
                        ? "Rekomendasi AI: Tingkatkan dokumentasi proses, lakukan pelatihan berkala, dan pastikan log verifikasi teknis kontrol {$standard->code} tersimpan terpusat."
                        : null,
                    'treatment_status' => $treatmentStatus ?: 'open',
                    'treatment_progress' => $treatmentProgress,
                    'treatment_pic' => $treatmentPic,
                    'treatment_due_date' => $treatmentDueDate,
                    'corrective_action_plan' => $actionPlanText ? ['action' => $actionPlanText] : null,
                    'evidence_after_improvement' => $evidenceAfter,
                ]
            );
        }

        $avgScore1 = $resultCount > 0 ? round($totalRatingSum / $resultCount, 2) : 0;
        $session1->update(['overall_maturity_score' => $avgScore1]);

        // 5. Seed Audit Session 2 (Bank Indonesia - Active Audit)
        $session2 = AssessmentSession::updateOrCreate(
            [
                'user_id' => $userSiti->id,
                'name' => 'ISO 27001:2022 Financial Sector Audit - Bank Indonesia',
            ],
            [
                'organization_id' => $orgBi->id,
                'status' => 'in_progress',
                'overall_maturity_score' => 3.79,
                'deadline' => now()->addMonths(2)->toDateString(),
                'ai_summary' => 'Audit in progress. Initial results show high maturity (Level 4+) in network security and cryptography, with ongoing evaluations for cloud governance and third-party supplier risk.',
            ]
        );

        $session2->invitedUsers()->sync([
            $userSiti->id => ['role' => 'lead']
        ]);

        $resultCount2 = 0;
        $totalRatingSum2 = 0;

        foreach ($standards as $index => $standard) {
            $hasQuestions = is_array($standard->questions) && count($standard->questions) > 0;
            $isCompleted = $index < 80;
            $rating = null;

            if ($hasQuestions && $isCompleted) {
                $hash = crc32($standard->code . 'bi');
                $mod = $hash % 10;
                $rating = $mod < 1 ? 2 : ($mod < 4 ? 3 : ($mod < 8 ? 4 : 5));
                $resultCount2++;
                $totalRatingSum2 += $rating;
            }

            $treatmentStatus = null;
            $treatmentProgress = 0;
            $treatmentPic = null;
            $treatmentDueDate = null;
            $actionPlanText = null;

            if ($rating !== null && $rating < 5) {
                $treatmentStatus = ($index % 2 == 0) ? 'in_progress' : 'open';
                $treatmentProgress = ($index % 2 == 0) ? 40 : 0;
                $treatmentPic = 'Siti Aminah';
                $treatmentDueDate = now()->addDays(rand(20, 60))->toDateString();
                $actionPlanText = "Tindakan perbaikan kontrol {$standard->code} sesuai standar perbankan BI.";
            }

            AssessmentResult::updateOrCreate(
                [
                    'session_id' => $session2->id,
                    'iso_standard_id' => $standard->id,
                ],
                [
                    'is_applicable' => true,
                    'maturity_rating' => $rating,
                    'status' => $isCompleted ? 'completed' : 'in_progress',
                    'answers' => $isCompleted ? json_encode(['verified' => true]) : null,
                    'notes' => $isCompleted ? "Penilaian kontrol {$standard->code} sektor keuangan." : null,
                    'treatment_status' => $treatmentStatus ?: 'open',
                    'treatment_progress' => $treatmentProgress,
                    'treatment_pic' => $treatmentPic,
                    'treatment_due_date' => $treatmentDueDate,
                    'corrective_action_plan' => $actionPlanText ? ['action' => $actionPlanText] : null,
                ]
            );
        }

        $avgScore2 = $resultCount2 > 0 ? round($totalRatingSum2 / $resultCount2, 2) : 0;
        $session2->update(['overall_maturity_score' => $avgScore2]);
    }
}