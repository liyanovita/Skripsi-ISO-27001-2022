<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoStandard;

class IsoAnnexA7Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- ANNEX A.7: PHYSICAL CONTROLS ---
        $annex7 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'control',
            'level'     => 'clause',
            'code'      => 'A.7',
            'title'     => 'Physical controls',
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.1',
            'title'       => 'Physical security perimeters',
            'description' => 'Security perimeters must be defined and used to protect areas that contain information and other associated assets.',
            'questions'   => [
                'Have security perimeters (such as fences, walls, or gates) been effectively defined to protect the organization\'s sensitive areas?'
            ],
            'implementation_guidance' => 'Evidence: Site plans of sensitive areas, photos of perimeter fences/walls, or evidence of guard posts at outer boundaries.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.2',
            'title'       => 'Physical entry',
            'description' => 'Secure areas must be protected by appropriate entry controls and access points.',
            'questions'   => [
                'Are secure areas protected by appropriate entry controls (such as access cards or biometrics) and managed access points?'
            ],
            'implementation_guidance' => 'Evidence: Access card usage logs, biometric system records, or authorization lists showing who may enter server/archive rooms.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.3',
            'title'       => 'Securing offices, rooms and facilities',
            'description' => 'Physical security for offices, rooms and facilities must be designed and implemented.',
            'questions'   => [
                'Has physical security for offices, rooms, and other supporting facilities been designed to prevent unintended access?'
            ],
            'implementation_guidance' => 'Evidence: Evidence of physical/electronic locks on doors, use of locked storage cabinets, and ID badges required for everyone in the office area.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.4',
            'title'       => 'Physical security monitoring',
            'description' => 'Premises must be continuously monitored for unauthorized physical access.',
            'questions'   => [
                'Are all premises continuously monitored (24/7) to detect unauthorized physical access?'
            ],
            'implementation_guidance' => 'Evidence: CCTV recordings (retention log), security guard patrol reports, or intrusion detection alarm system logs.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.5',
            'title'       => 'Protecting against physical and environmental threats',
            'description' => 'Protection against physical and environmental threats, such as natural disasters and other intentional or unintentional physical threats to infrastructure must be designed and implemented.',
            'questions'   => [
                'Has the organization\'s infrastructure been protected against physical and environmental threats (natural disasters, fire, or sabotage)?'
            ],
            'implementation_guidance' => 'Evidence: Availability of fire extinguishers (APAR), smoke/heat sensors, drainage systems to prevent flooding, or placing IT equipment in safe positions (e.g., elevated floors).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.6',
            'title'       => 'Working in secure areas',
            'description' => 'Security measures for working in secure areas must be designed and implemented.',
            'questions'   => [
                'Have specific security measures (such as prohibitions on cameras/phones) been implemented for personnel working in sensitive areas?'
            ],
            'implementation_guidance' => 'Evidence: Work instructions (WI) for working in the server room, warning signs in sensitive areas, and logs monitoring activities in restricted areas.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.7',
            'title'       => 'Clear desk and clear screen',
            'description' => 'Clear desk rules for physical media and clear screen rules for information processing facilities must be defined and appropriately enforced.',
            'questions'   => [
                'Have clear desk rules (for physical documents) and clear screen rules (for computers) been defined and consistently enforced?'
            ],
            'implementation_guidance' => 'Evidence: Clear Desk & Clear Screen policy, PC auto-lock features (via GPO), and results of random spot checks regarding desk cleanliness from sensitive documents.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.8',
            'title'       => 'Equipment siting and protection',
            'description' => 'Equipment must be sited securely and protected from environmental threats and unauthorized access.',
            'questions'   => [
                'Is IT equipment sited securely and protected from environmental threats and unauthorized access?'
            ],
            'implementation_guidance' => 'Evidence: Asset inventory with specific locations, use of locked server racks, and placing equipment away from water/heat sources.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.9',
            'title'       => 'Security of assets off-premises',
            'description' => 'Off-site assets must be protected.',
            'questions'   => [
                'Are organizational assets located off-site (such as laptops or IoT devices) protected with adequate security measures?'
            ],
            'implementation_guidance' => 'Evidence: Off-site asset usage policy, implementation of storage encryption (Full Disk Encryption), and reporting procedures if assets are lost.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.10',
            'title'       => 'Storage media',
            'description' => 'Storage media must be managed through their life cycle of acquisition, use, transportation and disposal in accordance with the organization\'s classification scheme and handling requirements.',
            'questions'   => [
                'Is the entire lifecycle of storage media managed according to the classification scheme, from acquisition to disposal?'
            ],
            'implementation_guidance' => 'Evidence: Logs of permanent data wiping, physical media destruction procedures (shredding), and media labeling based on data classification.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.11',
            'title'       => 'Supporting utilities',
            'description' => 'Information processing facilities must be protected from power failures and other disruptions caused by failures in supporting utilities.',
            'questions'   => [
                'Are information processing facilities protected from power failures and disruptions to other supporting utilities (internet, water)?'
            ],
            'implementation_guidance' => 'Evidence: Use of Uninterruptible Power Supply (UPS), availability of Generators, redundant internet connection paths (Dual ISP), and routine backup utility test schedules.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.12',
            'title'       => 'Cabling security',
            'description' => 'Cables carrying power, data or supporting information services must be protected from interception, interference or damage.',
            'questions'   => [
                'Are power and data cables protected from interception attempts (eavesdropping), electromagnetic interference, or physical damage?'
            ],
            'implementation_guidance' => 'Evidence: Use of enclosed cable pathways (conduit/trunking), separation between power and data cables to avoid interference, and documented cabling layout plans.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.13',
            'title'       => 'Equipment maintenance',
            'description' => 'Equipment must be correctly maintained to ensure availability, integrity and confidentiality of information.',
            'questions'   => [
                'Is equipment correctly maintained to ensure availability and optimal performance according to manufacturer recommendations?'
            ],
            'implementation_guidance' => 'Evidence: Routine maintenance logs, service contracts with official vendors, and periodic calibration or hardware cleaning schedules.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex7->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.7.14',
            'title'       => 'Secure disposal or re-use of equipment',
            'description' => 'Items of equipment containing storage media must be verified to ensure that any sensitive data and licensed software has been removed or securely overwritten prior to disposal or re-use.',
            'questions'   => [
                'Is verification performed to ensure sensitive data and software licenses have been permanently deleted before equipment is disposed of or reused?'
            ],
            'implementation_guidance' => 'Evidence: Certificate of Destruction, logs of data wiping processes using standard methods (e.g., DoD 5220.22-M), and asset disposal handover certificates.'
        ]);
    }
}
