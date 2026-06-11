<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoStandard;

class IsoAnnexA8Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- ANNEX A.8: TECHNOLOGICAL CONTROLS ---
        $annex8 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'control',
            'level'     => 'clause',
            'code'      => 'A.8',
            'title'     => 'Technological controls',
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.1',
            'title'       => 'User endpoint devices',
            'description' => 'Information stored on, processed by or accessible via user endpoint devices must be protected.',
            'questions'   => [
                'Is information accessed via user endpoint devices (laptops, mobile phones, tablets) protected against unauthorized access and cyber attacks?'
            ],
            'implementation_guidance' => 'Evidence: Bring Your Own Device (BYOD) policy, installation of Antivirus/EDR, storage media encryption, and remote wipe procedures for lost devices.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.2',
            'title'       => 'Privileged access rights',
            'description' => 'The allocation and use of privileged access rights must be restricted and managed.',
            'questions'   => [
                'Are privileged access rights (Admin/Superuser) restricted only to personnel who require them and strictly managed?'
            ],
            'implementation_guidance' => 'Evidence: List of high-privilege accounts, records of admin rights approval, and periodic reviews of privileged account usage.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.3',
            'title'       => 'Information access restriction',
            'description' => 'Access to information and other associated assets must be restricted in accordance with the established topic-specific policy on access control.',
            'questions'   => [
                'Is access to information and associated assets strictly restricted in accordance with the specific access control policy?'
            ],
            'implementation_guidance' => 'Evidence: Implementation of Role-Based Access Control (RBAC) in the system, user authorization matrix, and access logs to sensitive data.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.4',
            'title'       => 'Access to source code',
            'description' => 'Read and write access to source code, development tools and software libraries must be appropriately managed.',
            'questions'   => [
                'Is read and write access to source code and development tools securely managed?'
            ],
            'implementation_guidance' => 'Evidence: Access permission settings on repositories (GitHub/GitLab), commit activity logs, and the use of SSH keys or two-factor authentication for code access.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.5',
            'title'       => 'Secure authentication',
            'description' => 'Secure authentication technologies and procedures must be implemented based on information access restrictions and the topic-specific policy on access control.',
            'questions'   => [
                'Have secure authentication technologies and procedures (such as MFA) been implemented according to the sensitivity level of the information?'
            ],
            'implementation_guidance' => 'Evidence: Password complexity policy, implementation of Multi-Factor Authentication (MFA), and protection against brute force attacks (rate limiting).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.6',
            'title'       => 'Capacity management',
            'description' => 'The use of resources must be monitored and adjusted in line with current and expected capacity requirements.',
            'questions'   => [
                'Is resource utilization (CPU, RAM, Disk) monitored and adjusted to meet current and projected capacity requirements?'
            ],
            'implementation_guidance' => 'Evidence: Server monitoring dashboards (e.g., Grafana/Netdata), monthly resource utilization reports, and infrastructure capacity upgrade plans.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.7',
            'title'       => 'Protection against malware',
            'description' => 'Protection against malware must be implemented and supported by appropriate user awareness.',
            'questions'   => [
                'Has protection against malware been implemented across all systems and supported by user awareness?'
            ],
            'implementation_guidance' => 'Evidence: Installation of Endpoint Protection/Antivirus, threat detection logs, and policies prohibiting the installation of unauthorized software.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.8',
            'title'       => 'Management of technical vulnerabilities',
            'description' => 'Information about technical vulnerabilities of information systems being used must be obtained, the organization\'s exposure to such vulnerabilities evaluated and appropriate measures taken.',
            'questions'   => [
                'Does the organization routinely obtain information regarding technical vulnerabilities and take appropriate mitigation (patching) actions?'
            ],
            'implementation_guidance' => 'Evidence: Vulnerability assessment scanning results, routine security patching schedules, and a list of Common Vulnerabilities and Exposures (CVEs) relevant to the technology stack.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.9',
            'title'       => 'Configuration management',
            'description' => 'Configurations, including security configurations, of hardware, software, services and networks must be established, documented, implemented, monitored and reviewed.',
            'questions'   => [
                'Have security configurations for hardware, software, and networks been established, documented, and periodically reviewed?'
            ],
            'implementation_guidance' => 'Evidence: Documentation of standard configurations (hardening), change logs for configuration modifications, and secure configuration templates for servers/network devices.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.10',
            'title'       => 'Information deletion',
            'description' => 'Information stored in information systems, devices or in any other storage media must be deleted when no longer required.',
            'questions'   => [
                'Is information stored in the system securely deleted when it is no longer needed according to the retention policy?'
            ],
            'implementation_guidance' => 'Evidence: Data retention policy, logs of periodic database/file deletion, and the use of secure data wiping methods.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.11',
            'title'       => 'Data masking',
            'description' => 'Data masking must be used in accordance with the organization\'s topic-specific policy on access control and other related topic-specific policies, and business requirements, taking into consideration applicable legislation.',
            'questions'   => [
                'Are data masking techniques (masking, anonymization, or pseudonymization) used to protect sensitive data?'
            ],
            'implementation_guidance' => 'Evidence: Implementation of masking on the UI for sensitive data (e.g., censored phone numbers/emails), database encryption techniques, or use of anonymized data for testing environments.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.12',
            'title'       => 'Data leakage prevention',
            'description' => 'Data leakage prevention measures must be applied to systems, networks and any other devices that process, store or transmit sensitive information.',
            'questions'   => [
                'Have data leakage prevention (DLP) measures been applied to systems and networks that manage sensitive information?'
            ],
            'implementation_guidance' => 'Evidence: Configuration of Data Loss Prevention (DLP) systems, restricted access to data export/download, and monitoring of outbound data transfer activities.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.13',
            'title'       => 'Information backup',
            'description' => 'Backup copies of information, software and systems must be maintained and regularly tested in accordance with the agreed topic-specific policy on backup.',
            'questions'   => [
                'Are backup copies of information, software, and systems maintained and regularly tested?'
            ],
            'implementation_guidance' => 'Evidence: Automated backup schedules, successful backup reports, periodic data restoration test results, and storage of backups in a separate location (off-site/cloud).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.14',
            'title'       => 'Redundancy of information processing facilities',
            'description' => 'Information processing facilities must be implemented with sufficient redundancy to meet availability requirements.',
            'questions'   => [
                'Do information processing facilities (servers, networks, databases) have sufficient redundancy to guarantee service availability?'
            ],
            'implementation_guidance' => 'Evidence: High Availability (HA) architecture, use of load balancers, database replication (master-slave), or availability of failover servers.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.15',
            'title'       => 'Logging',
            'description' => 'Logs that record activities, exceptions, faults and other relevant events must be produced, stored, protected and analysed.',
            'questions'   => [
                'Are logs that record user activities, system faults, and security events produced, securely stored, and routinely analyzed?'
            ],
            'implementation_guidance' => 'Evidence: Application audit logs (activity logs), system logs (syslog), protection of log files from modification, and periodic reviews for anomalies within the logs.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.16',
            'title'       => 'Monitoring activities',
            'description' => 'Networks, systems and applications must be monitored for anomalous behaviour and appropriate actions taken to evaluate potential information security incidents.',
            'questions'   => [
                'Are networks, systems, and applications monitored for anomalous behavior, and are appropriate actions taken to evaluate potential incidents?'
            ],
            'implementation_guidance' => 'Evidence: Implementation of SIEM (Security Information and Event Management) or SOC (Security Operations Center), intrusion detection alerts (IDS/IPS), and investigation logs of suspicious activities.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.17',
            'title'       => 'Clock synchronization',
            'description' => 'The clocks of information processing systems used by the organization must be synchronized to approved time sources.',
            'questions'   => [
                'Are the clocks of all information processing systems synchronized to approved time sources (NTP)?'
            ],
            'implementation_guidance' => 'Evidence: Server Network Time Protocol (NTP) configuration pointing to official time servers (e.g., pool.ntp.org), and evidence that system log times are synchronized.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.18',
            'title'       => 'Use of privileged utility programs',
            'description' => 'The use of utility programs that can be capable of overriding system and application controls must be restricted and tightly controlled.',
            'questions'   => [
                'Is the use of utility programs capable of overriding system controls restricted and tightly managed?'
            ],
            'implementation_guidance' => 'Evidence: Restrictions on tools like Wireshark or database administration tools on production networks, and logs recording the usage of privileged commands (e.g., sudo logs).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.19',
            'title'       => 'Installation of software on operational systems',
            'description' => 'Procedures and measures must be implemented to securely manage software installation on operational systems.',
            'questions'   => [
                'Have procedures been implemented to securely manage software installation on operational systems?'
            ],
            'implementation_guidance' => 'Evidence: Prohibition of unauthorized software installation on production servers, software update validation procedures, and use of standard repositories.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.20',
            'title'       => 'Networks security',
            'description' => 'Networks and network devices must be secured, managed and controlled to protect information in systems and applications.',
            'questions'   => [
                'Are networks and network devices secured, managed, and controlled to protect information in systems and applications?'
            ],
            'implementation_guidance' => 'Evidence: Network topology diagrams, firewall configuration (ACLs), disabling of unused network ports, and use of secure protocols (HTTPS, SSH).'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.21',
            'title'       => 'Security of network services',
            'description' => 'Security mechanisms, service levels and management requirements of network services must be identified, implemented and monitored.',
            'questions'   => [
                'Have security mechanisms and management requirements for network services been identified, implemented, and monitored?'
            ],
            'implementation_guidance' => 'Evidence: SLA with ISPs or managed security service providers, monitoring of network bandwidth usage to prevent DDoS, and utilization of VPN/IPsec services.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.22',
            'title'       => 'Segregation of networks',
            'description' => 'Groups of information services, users and information systems must be segregated in the organization\'s networks.',
            'questions'   => [
                'Are information services, users, and information systems segregated into different network segments within the organization?'
            ],
            'implementation_guidance' => 'Evidence: Implementation of VLANs or subnetting, separation between guest Wi-Fi and internal networks, and strict routing rules between DMZ and internal networks.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.23',
            'title'       => 'Web filtering',
            'description' => 'Access to external websites must be managed to reduce exposure to malicious content.',
            'questions'   => [
                'Is access to external websites managed and filtered to reduce exposure to malicious content?'
            ],
            'implementation_guidance' => 'Evidence: Use of web proxies or DNS filtering, lists of blocked websites (e.g., gambling, pornography, known malicious IPs), and logs of blocked access.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.24',
            'title'       => 'Use of cryptography',
            'description' => 'Rules for the effective use of cryptography, including cryptographic key management, must be defined and implemented.',
            'questions'   => [
                'Have rules for the effective use of cryptography and key management been defined and implemented?'
            ],
            'implementation_guidance' => 'Evidence: Cryptography policy (e.g., mandating AES-256 for data at rest and TLS 1.2+ for data in transit), and secure procedures for storing cryptographic keys/certificates.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.25',
            'title'       => 'Secure development life cycle',
            'description' => 'Rules for the secure development of software and systems must be established and applied.',
            'questions'   => [
                'Have rules for the secure development lifecycle (SDLC) of software and systems been established and applied?'
            ],
            'implementation_guidance' => 'Evidence: Secure Software Development Life Cycle (SSDLC) documents, implementation of DevSecOps, and integration of security testing (SAST/DAST) in the CI/CD pipeline.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.26',
            'title'       => 'Application security requirements',
            'description' => 'Information security requirements must be identified, specified and approved when developing or acquiring applications.',
            'questions'   => [
                'Are information security requirements identified, specified, and approved during the development or acquisition of applications?'
            ],
            'implementation_guidance' => 'Evidence: Application requirement documents containing security features (e.g., session timeout, password hashing), and sign-off from the security team prior to development/purchase.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.27',
            'title'       => 'Secure system architecture and engineering principles',
            'description' => 'Principles for engineering secure systems must be established, documented, maintained and applied to any information system development activities.',
            'questions'   => [
                'Have secure system architecture and engineering principles been documented and applied to all system development activities?'
            ],
            'implementation_guidance' => 'Evidence: Secure Architecture Guidelines (e.g., Zero Trust Architecture, Defense in Depth), and design reviews evaluating system security resilience.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.28',
            'title'       => 'Secure coding',
            'description' => 'Secure coding principles must be applied to software development.',
            'questions'   => [
                'Are secure coding principles (such as OWASP Top 10 guidelines) applied during software development?'
            ],
            'implementation_guidance' => 'Evidence: Secure coding guidelines for programmers, results of code reviews focusing on security, and training materials on secure programming practices.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.29',
            'title'       => 'Security testing in development and acceptance',
            'description' => 'Security testing processes must be defined and implemented in the development life cycle.',
            'questions'   => [
                'Are security testing processes defined and implemented throughout the development lifecycle and during system acceptance?'
            ],
            'implementation_guidance' => 'Evidence: Penetration test reports, vulnerability scanning before go-live, and User Acceptance Test (UAT) scenarios that include security test cases.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.30',
            'title'       => 'Outsourced development',
            'description' => 'The organization must direct, monitor and review the activities related to outsourced system development.',
            'questions'   => [
                'Does the organization direct, monitor, and review security activities related to system development outsourced to third parties?'
            ],
            'implementation_guidance' => 'Evidence: Contracts with vendor developers that include security standards, independent code reviews on vendor deliverables, and security audits of vendor development environments.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.31',
            'title'       => 'Separation of development, test and production environments',
            'description' => 'Development, testing and production environments must be separated and secured.',
            'questions'   => [
                'Are development, testing, and production environments separated and secured individually?'
            ],
            'implementation_guidance' => 'Evidence: Network architecture demonstrating segregation between environments, different access credentials for each environment, and the prohibition of real production data in testing without masking.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.32',
            'title'       => 'Change management',
            'description' => 'Changes to information processing facilities and information systems must be subject to change management procedures.',
            'questions'   => [
                'Are changes to information processing facilities and systems governed by formal change management procedures?'
            ],
            'implementation_guidance' => 'Evidence: Change Request forms with impact/risk analysis, Change Advisory Board (CAB) meeting minutes, and evidence of rollback testing.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.33',
            'title'       => 'Test information',
            'description' => 'Test information must be appropriately selected, protected and managed.',
            'questions'   => [
                'Is test information appropriately selected, protected, and managed (e.g., not using real sensitive data)?'
            ],
            'implementation_guidance' => 'Evidence: Test data management procedures, logs indicating the use of dummy data or masked production data during testing phases.'
        ]);

        IsoStandard::create([
            'parent_id'   => $annex8->id,
            'type'        => 'control',
            'level'       => 'requirement',
            'code'        => 'A.8.34',
            'title'       => 'Protection of information systems during audit testing',
            'description' => 'Audit tests and other assurance activities involving assessment of operational systems must be planned and agreed between the tester and appropriate management.',
            'questions'   => [
                'Are audit tests on operational systems properly planned and agreed upon to prevent service disruption?'
            ],
            'implementation_guidance' => 'Evidence: Audit test plans (e.g., scheduling pentests during off-peak hours), formal approval from the IT Manager prior to scanning, and monitoring during the audit process.'
        ]);
    }
}
