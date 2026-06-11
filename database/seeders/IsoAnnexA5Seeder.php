<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoStandard;

class IsoAnnexA5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- ANNEX A.5: ORGANIZATIONAL CONTROLS ---
        $annex5 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'control', 
            'level'     => 'clause',
            'code'      => 'A.5',
            'title'     => 'Organizational controls',
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.1',
            'title'       => 'Policies for information security',
            'description' => 'Information security policy and topic-specific policies must be defined, approved by management, published, communicated to and acknowledged by relevant personnel, and reviewed at planned intervals.',
            'questions'   => [
                'To what extent have information security policies been defined, approved by management, communicated, and periodically reviewed?'
            ],
            'implementation_guidance' => 'Evidence: ISMS Policy document, list of topic-specific policies (e.g., access control, encryption), and records of annual reviews.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.2',
            'title'       => 'Information security roles and responsibilities',
            'description' => 'Information security roles and responsibilities must be defined and allocated according to the organization\'s needs.',
            'questions'   => [
                'Have information security roles and responsibilities been formally defined and allocated to the appropriate personnel?'
            ],
            'implementation_guidance' => 'Evidence: Information security organizational structure, Job Descriptions mentioning security duties, and appointment letters for the ISMS team.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.3',
            'title'       => 'Segregation of duties',
            'description' => 'Conflicting duties and conflicting areas of responsibility must be segregated to reduce opportunities for unauthorized or unintentional modification or misuse of the organization\'s assets.',
            'questions'   => [
                'Have conflicting duties and areas of responsibility that could cause conflicts of interest been clearly segregated within the organization?'
            ],
            'implementation_guidance' => 'Evidence: Role-Based Access Control (RBAC) matrix, separation between developers (Dev) and operations (Ops), or separation between transaction creators and approvers.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.4',
            'title'       => 'Management responsibilities',
            'description' => 'Management must require all personnel to apply information security in accordance with the established policies and procedures of the organization.',
            'questions'   => [
                'Has management required and ensured that all personnel apply information security in accordance with the established policies?'
            ],
            'implementation_guidance' => 'Evidence: Employee integrity pacts, evidence of socialization from leadership, and leadership participation in information security forums.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.5',
            'title'       => 'Contact with authorities',
            'description' => 'The organization must establish and maintain contact with relevant authorities.',
            'questions'   => [
                'Has the organization established and maintained effective contact with relevant authorities (regulators, law enforcement)?'
            ],
            'implementation_guidance' => 'Evidence: List of external authority contacts (e.g., BSSN, Kominfo, Police), correspondence records, or routine reporting to relevant agencies.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.6',
            'title'       => 'Contact with special interest groups',
            'description' => 'The organization must establish and maintain contact with special interest groups or other specialist security forums and professional associations.',
            'questions'   => [
                'Has the organization established and maintained contact with special interest groups, specialist security forums, or relevant professional associations?'
            ],
            'implementation_guidance' => 'Evidence: Proof of membership in security communities (e.g., ISACA, Honeynet Project, CERT groups), and records of participation in external cybersecurity forums.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.7',
            'title'       => 'Threat intelligence',
            'description' => 'Information relating to information security threats must be collected and analysed to produce threat intelligence.',
            'questions'   => [
                'Is information regarding security threats systematically collected and analyzed to produce actionable threat intelligence?'
            ],
            'implementation_guidance' => 'Evidence: Threat analysis reports, utilization of threat intelligence feeds (open-source or commercial), and evidence of follow-up actions (e.g., updating firewall/IPS rules).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.8',
            'title'       => 'Information security in project management',
            'description' => 'Information security must be integrated into project management.',
            'questions'   => [
                'Have information security aspects been integrated into every stage of project management, for both IT and other operational projects?'
            ],
            'implementation_guidance' => 'Evidence: Project Charter documents containing security risk analysis, and the presence of a security approval gate prior to project go-live.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.9',
            'title'       => 'Inventory of information and other associated assets',
            'description' => 'An inventory of information and other associated assets, including owners, must be developed and maintained.',
            'questions'   => [
                'Has the organization accurately developed and maintained an inventory of information assets (and other associated assets), complete with designated owners?'
            ],
            'implementation_guidance' => 'Evidence: Master List of Assets (Hardware, Software, Data, Personnel) detailing asset owner names, locations, and their criticality to the organization.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.10',
            'title'       => 'Acceptable use of information and other associated assets',
            'description' => 'Rules for the acceptable use and procedures for handling information and other associated assets must be identified, documented and implemented.',
            'questions'   => [
                'Have rules for the acceptable use (Acceptable Use Policy) and procedures for handling information and assets been defined, documented, and practically implemented by all users?'
            ],
            'implementation_guidance' => 'Evidence: Acceptable Use Policy (AUP) document signed by all employees, or a system login banner providing asset usage warnings.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.11',
            'title'       => 'Return of assets',
            'description' => 'Personnel and other interested parties must return all the organization\'s assets in their possession upon change or termination of their employment, contract or agreement.',
            'questions'   => [
                'Has the organization ensured that all personnel and external parties return all organizational assets upon change or termination of employment/contract?'
            ],
            'implementation_guidance' => 'Evidence: Asset handover certificates (BAST), off-boarding checklists, and ID card/laptop return procedures.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.12',
            'title'       => 'Classification of information',
            'description' => 'Information must be classified according to the information security needs of the organization based on confidentiality, integrity, availability and relevant interested party requirements.',
            'questions'   => [
                'Has information been classified based on security needs (CIA) and the requirements of relevant interested parties?'
            ],
            'implementation_guidance' => 'Evidence: Information Classification Scheme document (e.g., Public, Internal, Confidential, Strictly Confidential) and classification labels on documents/databases.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.13',
            'title'       => 'Labelling of information',
            'description' => 'An appropriate set of procedures for information labelling must be developed and implemented in accordance with the information classification scheme adopted by the organization.',
            'questions'   => [
                'Has the organization implemented information labeling procedures that are consistent with the established classification scheme?'
            ],
            'implementation_guidance' => 'Evidence: Examples of physical or digital documents containing classification watermarks/labels, and guidelines on how to label sensitive information.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.14',
            'title'       => 'Information transfer',
            'description' => 'Information transfer rules, procedures, or agreements must be in place for all types of transfer facilities within the organization and between the organization and other parties.',
            'questions'   => [
                'Have information transfer rules, procedures, or agreements been consistently applied to protect information during transit?'
            ],
            'implementation_guidance' => 'Evidence: Secure email usage procedures, secure file transfer protocols (SFTP), and Non-Disclosure Agreements (NDA) with third parties prior to data transfer.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.15',
            'title'       => 'Access control',
            'description' => 'Rules to control physical and logical access to information and other associated assets must be established and implemented based on business and information security requirements.',
            'questions'   => [
                'Have rules controlling physical and logical access to information and assets been established and implemented based on business requirements?'
            ],
            'implementation_guidance' => 'Evidence: Access Control Policy, user access rights lists, Multi-Factor Authentication (MFA) usage, and access logs for sensitive areas.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.16',
            'title'       => 'Identity management',
            'description' => 'The full life cycle of identities must be managed.',
            'questions'   => [
                'Has the entire identity lifecycle of personnel (creation, maintenance, up to deletion) been systematically managed and documented?'
            ],
            'implementation_guidance' => 'Evidence: User registration procedures, account request forms, and system logs showing when accounts were created or deactivated.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.17',
            'title'       => 'Authentication information',
            'description' => 'Allocation and management of authentication information must be controlled by a management process, including advising personnel on appropriate handling of authentication information.',
            'questions'   => [
                'Is the allocation of authentication information (e.g., passwords/tokens) formally controlled, and are personnel provided guidance on secure handling?'
            ],
            'implementation_guidance' => 'Evidence: Password usage policy (complexity, expiry), evidence of password manager usage, or socialization regarding the prohibition of password sharing.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.18',
            'title'       => 'Access rights',
            'description' => 'Access rights to information and other associated assets must be provisioned, reviewed, modified and removed in accordance with the organization\'s topic-specific policy on access control.',
            'questions'   => [
                'Are personnel access rights thoroughly managed—from initial provisioning, routine reviews, up to revocation—according to organizational policy?'
            ],
            'implementation_guidance' => 'Evidence: Privilege Matrix, evidence of periodic user access reviews, and access revocation forms for resigning employees.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.19',
            'title'       => 'Information security in supplier relationships',
            'description' => 'Processes and procedures must be defined and implemented to manage the information security risks associated with the use of supplier\'s products or services.',
            'questions'   => [
                'Does the organization have procedures to manage information security risks arising from the use of third-party/supplier services?'
            ],
            'implementation_guidance' => 'Evidence: Supplier risk assessment documents and supplier inventory lists detailing criticality levels.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.20',
            'title'       => 'Addressing information security within supplier agreements',
            'description' => 'Relevant information security requirements must be established and agreed with each supplier based on the type of supplier relationship.',
            'questions'   => [
                'Have information security requirements been formally established and agreed upon (contracts/NDAs) with each supplier based on the relationship type?'
            ],
            'implementation_guidance' => 'Evidence: Security clauses in cooperation contracts, Non-Disclosure Agreement (NDA) documents, or security Service Level Agreements (SLA).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.21',
            'title'       => 'Managing information security in the ICT supply chain',
            'description' => 'Processes and procedures must be defined and implemented to manage the information security risks associated with the ICT products and services supply chain.',
            'questions'   => [
                'Has the organization implemented processes to manage information security risks related to the entire supply chain of ICT products and services?'
            ],
            'implementation_guidance' => 'Evidence: ICT vendor selection policy, evidence of integrity checks for purchased hardware/software, and security clauses for sub-contractors.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.22',
            'title'       => 'Monitoring, review and change management of supplier services',
            'description' => 'The organization must regularly monitor, review, evaluate and manage change in supplier information security practices and service delivery.',
            'questions'   => [
                'Does the organization routinely monitor and manage changes in the information security practices and service delivery of suppliers?'
            ],
            'implementation_guidance' => 'Evidence: Annual vendor audit reports, vendor performance review meeting minutes, and third-party service change logs.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.23',
            'title'       => 'Information security for use of cloud services',
            'description' => 'Processes for acquisition, use, management and exit from cloud services must be established in accordance with the organization\'s information security requirements.',
            'questions'   => [
                'Has the organization established processes for the acquisition, use, and termination (exit strategy) of cloud services according to security requirements?'
            ],
            'implementation_guidance' => 'Evidence: Cloud strategy documents, cloud provider security assessment results (e.g., SOC2 or ISO 27001 certificates from AWS/GCP), and data migration procedures upon unsubscription.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.24',
            'title'       => 'Information security incident management planning and preparation',
            'description' => 'The organization must plan and prepare for managing information security incidents by defining, establishing and communicating information security incident management processes, roles and responsibilities.',
            'questions'   => [
                'Has the organization defined and clearly communicated incident management processes, roles, and responsibilities?'
            ],
            'implementation_guidance' => 'Evidence: Incident Response Plan (IRP) document, establishment of a Computer Security Incident Response Team (CSIRT), and incident escalation workflows.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.25',
            'title'       => 'Assessment and decision on information security events',
            'description' => 'The organization must assess information security events and decide if they are to be categorized as information security incidents.',
            'questions'   => [
                'Does the organization have a process to assess every event and consistently decide on its categorization as a security incident?'
            ],
            'implementation_guidance' => 'Evidence: Event vs Incident classification logs, incident impact criteria (Low/Medium/High), and initial assessment records.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.26',
            'title'       => 'Response to information security incidents',
            'description' => 'Information security incidents must be responded to in accordance with the documented procedures.',
            'questions'   => [
                'Is every information security incident responded to swiftly and appropriately according to established documented procedures?'
            ],
            'implementation_guidance' => 'Evidence: Incident handling reports (incident logs), evidence of response procedure execution, and response time records against SLAs.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.27',
            'title'       => 'Learning from information security incidents',
            'description' => 'Knowledge gained from information security incidents must be used to strengthen and improve the information security controls.',
            'questions'   => [
                'Is knowledge derived from incident handling systematically used to strengthen future information security controls?'
            ],
            'implementation_guidance' => 'Evidence: Post-Incident Reports (PIR) or Lessons Learned documents, and evidence of policy/system modifications based on incident evaluation.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.28',
            'title'       => 'Collection of evidence',
            'description' => 'The organization must establish and implement procedures for the identification, collection, acquisition and preservation of evidence related to information security events.',
            'questions'   => [
                'Has the organization implemented procedures for the collection of digital/physical evidence to ensure legal admissibility during information security events?'
            ],
            'implementation_guidance' => 'Evidence: Chain of Custody procedures, immutable access logs, and the use of standardized forensic tools.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.29',
            'title'       => 'Information security during disruption',
            'description' => 'The organization must plan how to maintain information security at an appropriate level during disruption.',
            'questions'   => [
                'Has the organization planned how to maintain information security at an appropriate level during disruptions or emergencies?'
            ],
            'implementation_guidance' => 'Evidence: Information Security Continuity Plan document and risk assessment results during emergencies (e.g., access policies when working from emergency locations).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.30',
            'title'       => 'ICT readiness for business continuity',
            'description' => 'ICT readiness must be planned, implemented, maintained and tested based on business continuity objectives and ICT continuity requirements.',
            'questions'   => [
                'To what extent is ICT readiness planned, tested, and maintained to support the organization\'s business continuity objectives?'
            ],
            'implementation_guidance' => 'Evidence: Disaster Recovery Plan (DRP) document, data restore test reports, and the availability of backup systems (redundancy).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.31',
            'title'       => 'Legal, statutory, regulatory and contractual requirements',
            'description' => 'Legal, statutory, regulatory and contractual requirements relevant to information security and the organization\'s approach to meet these requirements must be identified, documented and kept up to date.',
            'questions'   => [
                'Does the organization periodically identify and document all legal, regulatory, and contractual requirements relevant to information security?'
            ],
            'implementation_guidance' => 'Evidence: List of relevant legislation (e.g., PDP Law, ITE Law), list of third-party contracts, and records of periodic updates to these regulations.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.32',
            'title'       => 'Intellectual property rights',
            'description' => 'The organization must implement appropriate procedures to protect intellectual property rights.',
            'questions'   => [
                'Have appropriate procedures been implemented to protect Intellectual Property Rights (such as software licenses or code copyrights)?'
            ],
            'implementation_guidance' => 'Evidence: Software license inventory list, copyright/trademark certificates, and procedures prohibiting the use of pirated software within the organization.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.33',
            'title'       => 'Protection of records',
            'description' => 'Records must be protected from loss, destruction, falsification, unauthorized access and unauthorized release.',
            'questions'   => [
                'Are records (archives/data) protected from loss, destruction, falsification, and unauthorized access or release?'
            ],
            'implementation_guidance' => 'Evidence: Document retention procedures, use of secure archiving systems, and access logs for sensitive archives.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.34',
            'title'       => 'Privacy and protection of PII',
            'description' => 'The organization must identify and meet the requirements regarding the preservation of privacy and protection of personally identifiable information (PII) according to applicable laws and regulations and contractual requirements.',
            'questions'   => [
                'Has the organization fulfilled privacy and PII (Personal Data) protection requirements in accordance with laws (such as PDP Law) and contracts?'
            ],
            'implementation_guidance' => 'Evidence: Privacy Policy, Data Protection Impact Assessment (DPIA), and evidence of consent from data subjects.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.35',
            'title'       => 'Independent review of information security',
            'description' => 'The organization\'s approach to managing information security and its implementation must be reviewed independently at planned intervals or when significant changes occur.',
            'questions'   => [
                'Is the organization\'s information security management reviewed by independent parties periodically or when major changes occur?'
            ],
            'implementation_guidance' => 'Evidence: Internal audit reports, external audit reports from certification bodies, or assessment results from third-party security consultants.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.36',
            'title'       => 'Compliance with policies, rules and standards for information security',
            'description' => 'Compliance with the organization\'s information security policy, topic-specific policies, rules and standards must be regularly reviewed.',
            'questions'   => [
                'To what extent is compliance with information security policies and technical standards regularly reviewed by management?'
            ],
            'implementation_guidance' => 'Evidence: Management Review reports, internal compliance assessment results, and evidence of corrective actions if nonconformities are found.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex5->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.5.37',
            'title'       => 'Documented operating procedures',
            'description' => 'Operating procedures for information processing facilities must be documented and made available to personnel who need them.',
            'questions'   => [
                'Are all operating procedures for information processing facilities (such as Backup SOPs, IT Ops) documented and available to personnel who require them?'
            ],
            'implementation_guidance' => 'Evidence: Standard Operating Procedure (SOP) documents for IT operations, technical work instructions, and proof that relevant personnel have access to these documents.'
        ]);
    }
}
