<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoStandard;

class IsoAnnexA6Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- ANNEX A.6: PEOPLE CONTROLS ---
        $annex6 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'control',
            'level'     => 'clause',
            'code'      => 'A.6',
            'title'     => 'People controls',
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.1',
            'title'       => 'Screening',
            'description' => 'Background verification checks on all candidates to become personnel must be carried out prior to joining the organization and on an ongoing basis taking into consideration applicable laws, regulations and ethics.',
            'questions'   => [
                'Are background verification checks conducted on personnel candidates prior to joining and periodically according to the risk level of their roles?'
            ],
            'implementation_guidance' => 'Evidence: Diploma verification documents, past employment references, police records (SKCK), or professional track records of candidates.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.2',
            'title'       => 'Terms and conditions of employment',
            'description' => 'The employment contractual agreements must state the personnel\'s and the organization\'s responsibilities for information security.',
            'questions'   => [
                'Do employment agreements explicitly state the information security responsibilities that must be adhered to by personnel and the organization?'
            ],
            'implementation_guidance' => 'Evidence: Employment contracts containing information security clauses, integrity pacts, or employee Non-Disclosure Agreement (NDA) documents.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.3',
            'title'       => 'Information security awareness, education and training',
            'description' => 'Personnel of the organization and relevant interested parties must receive appropriate information security awareness, education and training and regular updates of the organization\'s information security policy.',
            'questions'   => [
                'Do personnel receive regular information security awareness training, education, and updates?'
            ],
            'implementation_guidance' => 'Evidence: Security training certificates, cybersecurity socialization attendance lists, or completion logs for security e-learning modules.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.4',
            'title'       => 'Disciplinary process',
            'description' => 'A disciplinary process must be formalized and communicated to take action against personnel and other relevant interested parties who have committed an information security policy violation.',
            'questions'   => [
                'Does the organization have a clear disciplinary process for personnel who commit information security policy violations?'
            ],
            'implementation_guidance' => 'Evidence: Company Regulations (PP) containing data breach sanctions, or records of warning letters issued for security violations.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.5',
            'title'       => 'Responsibilities after termination or change of employment',
            'description' => 'Information security responsibilities and duties that remain valid after termination or change of employment must be defined, enforced and communicated to relevant personnel and other interested parties.',
            'questions'   => [
                'Are information security responsibilities that remain valid after an employee leaves or changes roles still enforced and communicated?'
            ],
            'implementation_guidance' => 'Evidence: Post-employment confidentiality clauses in contracts, or off-boarding checklists reminding of the obligation to maintain company secrets.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.6',
            'title'       => 'Confidentiality or non-disclosure agreements',
            'description' => 'Confidentiality or non-disclosure agreements reflecting the organization\'s needs for the protection of information must be identified, documented, regularly reviewed and signed by personnel and other relevant interested parties.',
            'questions'   => [
                'Have confidentiality agreements (NDAs) been identified, signed, and regularly reviewed by personnel and external parties?'
            ],
            'implementation_guidance' => 'Evidence: Signed Non-Disclosure Agreement (NDA) documents, records of periodic NDA reviews, and lists of external parties bound by agreements.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.7',
            'title'       => 'Remote working',
            'description' => 'Security measures must be implemented when personnel are working remotely to protect information accessed, processed or stored outside the organization\'s premises.',
            'questions'   => [
                'Have security measures been implemented to protect information accessed, processed, or stored by personnel while working remotely outside the office?'
            ],
            'implementation_guidance' => 'Evidence: Remote working policy, use of VPN (Virtual Private Network), drive encryption on employee laptops, and home/public network security procedures.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex6->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.6.8',
            'title'       => 'Information security event reporting',
            'description' => 'The organization must provide a mechanism for personnel to report observed or suspected information security events through appropriate channels in a timely manner.',
            'questions'   => [
                'Is there an easily accessible mechanism for personnel to report suspicious security events through appropriate channels?'
            ],
            'implementation_guidance' => 'Evidence: Online incident reporting forms, dedicated reporting email addresses (e.g., security@company.com), or a security helpdesk portal.'
        ]);
    }
}
