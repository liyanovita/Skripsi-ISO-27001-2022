<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // GUIDES
            [
                'category' => 'guides',
                'title' => 'ISO 27001:2022 Implementation Guide',
                'description' => 'A strategic 6-phase roadmap from gap analysis to certification readiness.',
                'icon' => 'fa-route',
                'is_system' => true,
                'content' => "PHASE 1: PREPARATION & GAP ANALYSIS\n- Obtain Management Commitment: Secure budget and resource allocation.\n- Determine Context: Identify internal and external issues (Clause 4.1).\n- Initial Gap Assessment: Evaluate current conditions against Annex A controls.\n\nPHASE 2: ISMS GOVERNANCE\n- Define Scope: Document physical and logical boundaries (Clause 4.3).\n- Appoint CISO/ISMS Head: Establish clear roles and responsibilities.\n- Information Security Policy: Draft and approve a high-level ISP.\n\nPHASE 3: RISK MANAGEMENT\n- Asset Inventory: Identify and assess information assets.\n- Risk Assessment: Identify threats and vulnerabilities using ISO 27005 methodology.\n- Risk Treatment Plan (RTP): Determine how to address identified risks.\n\nPHASE 4: CONTROL IMPLEMENTATION\n- Select Annex A Controls: Map controls to risk treatment decisions.\n- Draft SOA: Finalize the Statement of Applicability document.\n- Technical & Administrative Controls: Implement encryption, access controls, etc.\n\nPHASE 5: TRAINING & OPERATIONS\n- Awareness Training: Conduct sessions for all employees.\n- Operational Procedures: Implement SOPs for incident response, backups, etc.\n\nPHASE 6: AUDIT & CERTIFICATION\n- Internal Audit: Verify the effectiveness of ISMS controls.\n- Management Review: Review audit results with leadership (Clause 9.3).\n- External Audit: Stage 1 and Stage 2 certification audits."
            ],
            [
                'category' => 'guides',
                'title' => 'Risk Assessment Methodology',
                'description' => 'Formal approach to identifying, analyzing, and evaluating information security risks.',
                'icon' => 'fa-shield-halved',
                'is_system' => true,
                'content' => "1. ASSET IDENTIFICATION\nIdentify all information assets supporting business processes within the ISMS scope. Categorize by Hardware, Software, Information, People, and Services.\n\n2. THREAT & VULNERABILITY MAPPING\nMap potential threats (e.g., unauthorized access, natural disasters) to existing vulnerabilities (e.g., weak passwords, unpatched systems).\n\n3. IMPACT & LIKELIHOOD ASSESSMENT\nUse a 1-5 or 3x3 matrix to assess:\n- Impact: Financial, Legal, and Operational consequences.\n- Likelihood: Historical data and expert judgment on probability.\n\n4. RISK CALCULATION\nRisk Score = Impact x Likelihood. Determine thresholds (e.g., Score > 15 = Critical Risk).\n\n5. RISK TREATMENT DECISIONS\n- Mitigate: Apply controls to reduce risk.\n- Accept: Acknowledge the risk (with management approval).\n- Transfer: Use insurance or outsourcing.\n- Avoid: Discontinue activities causing the risk."
            ],
            // TEMPLATES
            [
                'category' => 'templates',
                'title' => 'Information Security Policy (ISP)',
                'description' => 'High-level information security governance framework.',
                'icon' => 'fa-file-lines',
                'format' => 'DOCX',
                'size' => '45KB',
                'is_system' => true,
                'content' => "TITLE: INFORMATION SECURITY POLICY\nVERSION: 1.0\n\n1. PURPOSE\nThe purpose of this policy is to establish a framework to protect the confidentiality, integrity, and availability (CIA) of [Organization Name]'s information assets.\n\n2. SCOPE\nThis policy applies to all employees, contractors, and third parties accessing the organization's information systems.\n\n3. OBJECTIVES\n- Ensure compliance with legal and regulatory requirements (ISO 27001, GDPR).\n- Reduce information security incidents and minimize their impact.\n- Maintain business continuity in the event of a disaster.\n\n4. PRINCIPLES\n- Security is a business responsibility, not just an IT issue.\n- Access is granted based on the 'Need-to-Know' principle.\n- All security incidents must be reported and investigated.\n\n5. ENFORCEMENT\nNon-compliance with this policy may result in disciplinary action, up to and including termination of employment."
            ],
            // EVIDENCE
            [
                'category' => 'evidence',
                'title' => 'Sample Risk Register',
                'description' => 'Example document for information asset risk management.',
                'icon' => 'fa-file-excel',
                'is_system' => true,
                'content' => "A professional Risk Register must contain the following columns:\n1. Asset Name\n2. Risk Description\n3. Risk Owner\n4. Impact Score\n5. Likelihood Score\n6. Initial Risk Level\n7. Mitigation\n8. Residual Risk Score"
            ],
        ];

        foreach ($data as $item) {
            \App\Models\KnowledgeBase::create($item);
        }
    }
}
