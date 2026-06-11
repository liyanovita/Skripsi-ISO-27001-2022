<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IsoStandard;

class IsoClauseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- CLAUSE 4: CONTEXT OF THE ORGANIZATION ---
        $clause4 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'clause',
            'level'     => 'clause',
            'code'      => '4',
            'title'     => 'Context of the organization',
        ]);

        IsoStandard::create([
            'parent_id'   => $clause4->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '4.1',
            'title'       => 'Understanding the organization and its context',
            'description' => 'The organization must determine external and internal issues that are relevant to its purpose and that affect its ability to achieve the intended outcome(s) of its information security management system.',
            'questions'   => [
                'To what extent has the organization identified and reviewed internal issues (such as culture, values, performance) and external issues (such as legal, technological, market) that affect the objectives of the ISMS?'
            ],
            'implementation_guidance' => 'There should be a SWOT or PESTLE Analysis document covering legal, regulatory, technological, and organizational culture aspects, reviewed at least annually.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause4->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '4.2',
            'title'       => 'Understanding the needs and expectations of interested parties',
            'description' => 'The organization must determine interested parties, their requirements, and which of these requirements will be addressed through the ISMS.',
            'questions'   => [
                'To what extent has the organization identified relevant interested parties and documented their requirements (legal, regulatory, contractual)?'
            ],
            'implementation_guidance' => 'The Stakeholder Register must include Shareholders, Employees, Customers, and Government Agencies, along with their legal/contractual obligations.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause4->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '4.3',
            'title'       => 'Determining the scope of the information security management system',
            'description' => 'The organization must determine the boundaries and applicability of the ISMS.',
            'questions'   => [
                'Does the organization have a documented ISMS scope that covers physical, organizational, and technological boundaries?'
            ],
            'implementation_guidance' => 'The Scope Statement must be available in writing, explaining which business units are included in the audit and what IT assets are protected.'
        ]);

        // --- CLAUSE 5: LEADERSHIP ---
        $clause5 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'clause',
            'level'     => 'clause',
            'code'      => '5',
            'title'     => 'Leadership',
        ]);

        IsoStandard::create([
            'parent_id'   => $clause5->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '5.1',
            'title'       => 'Leadership and commitment',
            'description' => 'Top management must demonstrate leadership and commitment with respect to the ISMS by ensuring the policy and objectives are established and compatible with the strategic direction of the organization.',
            'questions'   => [
                'To what extent does top management ensure that ISMS policies and objectives are aligned with the strategic direction, integrated into business processes, and supported by adequate resources?',
                'To what extent does top management communicate the importance of the ISMS, direct personnel to contribute to its effectiveness, and support other relevant management roles in promoting continual improvement?'
            ],
            'implementation_guidance' => 'Evidence of commitment can be Management Review Meeting (MRM) minutes, dedicated IT security budgets, and official decrees supporting the security program.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause5->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '5.2',
            'title'       => 'Policy',
            'description' => 'Top management must establish an information security policy that is appropriate to the purpose of the organization, provides a framework for setting objectives, and includes a commitment to satisfy applicable requirements.',
            'questions'   => [
                'To what extent has the organization established, documented, and communicated an information security policy aligned with organizational goals, providing a framework for objectives, and including commitments to compliance and continual improvement?'
            ],
            'implementation_guidance' => 'A High-Level Information Security Policy signed by the Board of Directors must be available, socialized to employees (evidence via email/attendance), and periodically reviewed.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause5->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '5.3',
            'title'       => 'Organizational roles, responsibilities and authorities',
            'description' => 'Top management must ensure that the responsibilities and authorities for roles relevant to information security are assigned and communicated.',
            'questions'   => [
                'To what extent has top management established, communicated, and assigned responsibilities and authorities to relevant roles to ensure ISMS compliance with standards and routine performance reporting to top management?'
            ],
            'implementation_guidance' => 'Evidence includes an ISMS Organizational Structure (e.g., ISMS Team), Job Descriptions detailing information security responsibilities, and an official appointment letter for the CISO or IT Head.'
        ]);

        // --- CLAUSE 6: PLANNING ---
        $clause6 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'clause',
            'level'     => 'clause',
            'code'      => '6',
            'title'     => 'Planning',
        ]);

        $sub61 = IsoStandard::create([
            'parent_id' => $clause6->id,
            'type'      => 'clause',
            'level'     => 'sub_clause',
            'code'      => '6.1',
            'title'     => 'Actions to address risks and opportunities',
        ]);

        IsoStandard::create([
            'parent_id'   => $sub61->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '6.1.1',
            'title'       => 'General',
            'description' => 'The organization must consider issues referred to in 4.1 and requirements referred to in 4.2 to determine risks and opportunities that need to be addressed to ensure the ISMS can achieve its intended outcome(s).',
            'questions'   => [
                'To what extent has the organization established a planning process that integrates internal/external issues and stakeholder needs to determine risks and opportunities, plan mitigation actions, and evaluate the effectiveness of these actions?'
            ],
            'implementation_guidance' => 'A documented risk management methodology is required. The assessment must consider issues identified in 4.1 and stakeholders in 4.2.'
        ]);

        IsoStandard::create([
            'parent_id'   => $sub61->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '6.1.2',
            'title'       => 'Information security risk assessment',
            'description' => 'The organization must define and apply an information security risk assessment process that establishes risk criteria, ensures consistent risk identification, analysis, and evaluation.',
            'questions'   => [
                'To what extent has the organization established standard risk criteria, including risk acceptance criteria and assessment scales (impact/likelihood), to ensure consistent assessment results?',
                'To what extent has the organization identified risks related to CIA, determined Risk Owners, and documented risk level analysis?'
            ],
            'implementation_guidance' => 'A Risk Assessment Document (Risk Register) is mandatory. It must validate Impact & Likelihood scores, and the designation of Risk Owners for each information asset.'
        ]);

        IsoStandard::create([
            'parent_id'   => $sub61->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '6.1.3',
            'title'       => 'Information security risk treatment',
            'description' => 'The organization must plan risk treatment by selecting appropriate options, determining necessary controls from Annex A, and producing a Statement of Applicability (SoA) and a Risk Treatment Plan (RTP).',
            'questions'   => [
                'To what extent has the organization selected risk treatment options, compared chosen controls against Annex A, and produced a Statement of Applicability (SoA)?',
                'To what extent has the organization drafted a detailed Risk Treatment Plan (RTP) and obtained approval from risk owners regarding residual risk?'
            ],
            'implementation_guidance' => 'Two mandatory documents: 1) Statement of Applicability (SoA) covering all 93 Annex A 2022 controls. 2) Risk Treatment Plan (RTP) detailing the implementation schedule and signed approval from Risk Owners.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause6->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '6.2',
            'title'       => 'Information security objectives and planning to achieve them',
            'description' => 'Establish objectives that are consistent with the policy, measurable, take into account applicable requirements, monitored, and communicated.',
            'questions'   => [
                'To what extent has the organization established documented, measurable information security objectives that align with the policy and risk assessment results?',
                'To what extent has the organization planned actions to achieve these objectives, including resources, person in charge (PIC), deadlines, and evaluation methods?'
            ],
            'implementation_guidance' => 'Information Security Objectives must use SMART criteria (Specific, Measurable, Achievable, Relevant, Time-bound). Example: "Maintain 99.9% server availability throughout the year".'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause6->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '6.3',
            'title'       => 'Planning of changes',
            'description' => 'When the organization determines the need for changes to the ISMS, the changes must be carried out in a planned manner.',
            'questions'   => [
                'To what extent has the organization established a formal procedure governing how changes to the ISMS (policy, process, or technology) are planned and validated?',
                'Has every implemented change gone through a risk impact analysis process, documented planning, and obtained formal approval before deployment?'
            ],
            'implementation_guidance' => 'Change Management Procedure is mandatory. Audit evidence can include Request for Change (RFC) forms covering change impact risk analysis.'
        ]);

        // --- CLAUSE 7: SUPPORT ---
        $clause7 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'clause',
            'level'     => 'clause',
            'code'      => '7',
            'title'     => 'Support',
        ]);

        IsoStandard::create([
            'parent_id'   => $clause7->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '7.1',
            'title'       => 'Resources',
            'description' => 'The organization must determine and provide the resources needed for the establishment, implementation, maintenance and continual improvement of the ISMS.',
            'questions'   => [
                'To what extent has the organization identified and provided adequate resources (HR, infrastructure, technology, and budget) to support the entire ISMS lifecycle?',
                'Does the organization have a mechanism to ensure these resources remain available and maintained to support continual improvement?'
            ],
            'implementation_guidance' => 'Audit evidence can be the Annual Work Plan and Budget (RKAP) for IT security, infrastructure asset lists, and organizational structure showing dedicated personnel allocation.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause7->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '7.2',
            'title'       => 'Competence',
            'description' => 'The organization must determine necessary competence of personnel, ensure they are competent on the basis of education, training, or experience, and take actions to acquire necessary competence.',
            'questions'   => [
                'To what extent has the organization determined the competence requirements (education, training, experience) needed for every role impacting information security?',
                'Has the organization taken actions (such as training, mentoring, or recruitment) to address competence gaps, and evaluated the effectiveness of those actions?'
            ],
            'implementation_guidance' => 'Requires a Skill Matrix document, employee training records, professional certifications (e.g., CISA, CISSP, Lead Auditor), and post-training evaluations.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause7->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '7.3',
            'title'       => 'Awareness',
            'description' => 'Personnel working under the organization\'s control must be aware of the information security policy, their contribution to ISMS effectiveness, and implications of nonconformity.',
            'questions'   => [
                'To what extent are personnel working under the organization\'s control aware of the information security policy and understand how their roles contribute to ISMS effectiveness?',
                'To what extent do personnel understand the negative implications and impacts of nonconformity with ISMS requirements?'
            ],
            'implementation_guidance' => 'Evidence includes information security socialization materials, Security Awareness Training attendance logs, or periodic security awareness quiz/survey results.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause7->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '7.4',
            'title'       => 'Communication',
            'description' => 'Determine internal and external communication needs: what, when, with whom, and how to communicate.',
            'questions'   => [
                'To what extent has the organization established internal and external communication needs detailing what, when, with whom, and how security information is communicated?',
                'Does the organization have evidence of executing communication according to the established plan?'
            ],
            'implementation_guidance' => 'Requires a Communication Plan matrix defining incident reporting, third-party (vendor) communication, and internal security announcements.'
        ]);

        $sub75 = IsoStandard::create([
            'parent_id' => $clause7->id,
            'type'      => 'clause',
            'level'     => 'sub_clause',
            'code'      => '7.5',
            'title'     => 'Documented information',
        ]);

        IsoStandard::create([
            'parent_id'   => $sub75->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '7.5.1',
            'title'       => 'General',
            'description' => 'The ISMS must include documented information required by the standard and determined by the organization as necessary for effectiveness.',
            'questions'   => [
                'To what extent has the organization documented all information required by the ISO 27001 standard and additional documents deemed necessary for ISMS effectiveness?',
                'To what extent is documented information well-managed, covering creation, review, approval, version updating, and protection from loss or unauthorized alteration?'
            ],
            'implementation_guidance' => 'Check for Mandatory Documents such as ISMS Policy, SoA, Risk Assessment, and control implementation evidence. Ensure no mandatory ISO 27001 documents are missing.'
        ]);

        IsoStandard::create([
            'parent_id'   => $sub75->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '7.5.2',
            'title'       => 'Creating and updating',
            'description' => 'Ensure appropriate identification, format, media, and review/approval of documents.',
            'questions'   => [
                'Does every documented information have a unique identity (title, date, reference number) and use a format/media suited for organizational access needs?',
                'To what extent does the organization ensure each document goes through a review and approval process by authorized personnel to guarantee suitability and adequacy before publication?'
            ],
            'implementation_guidance' => 'Every document must have a Header/Footer containing Document ID, Revision Number, Effective Date, and Signatory/Approver Name.'
        ]);

        IsoStandard::create([
            'parent_id'   => $sub75->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '7.5.3',
            'title'       => 'Control of documented information',
            'description' => 'Ensure documents are available when needed, adequately protected, and manage their distribution, access, storage, change control, retention, and disposition.',
            'questions'   => [
                'To what extent is documented information available when needed and adequately protected against misuse, loss of confidentiality, or compromised integrity?',
                'Has the organization regulated distribution, storage (preservation of legibility), version control, retention periods, and disposition of documents?'
            ],
            'implementation_guidance' => 'Requires a Master List of Documents and retention records. For digital files, clear Access Control Lists (ACL) dictating read/write privileges are necessary.'
        ]);

        // --- CLAUSE 8: OPERATION ---
        $clause8 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'clause',
            'level'     => 'clause',
            'code'      => '8',
            'title'     => 'Operation',
        ]);

        IsoStandard::create([
            'parent_id'   => $clause8->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '8.1',
            'title'       => 'Operational planning and control',
            'description' => 'The organization must plan, implement, and control processes required to meet information security requirements and implement actions determined in Planning.',
            'questions'   => [
                'To what extent has the organization established criteria for ISMS processes and implemented operational controls to ensure Clause 6 plans are fulfilled?',
                'To what extent does the organization monitor planned changes and mitigate adverse effects of unintended changes?',
                'To what extent does the organization ensure that externally provided processes or services (vendors) are controlled?'
            ],
            'implementation_guidance' => 'Evidence includes IT Standard Operating Procedures (SOP), Change Requests for system changes, and vendor contracts (SLA/NDA) containing information security clauses.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause8->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '8.2',
            'title'       => 'Information security risk assessment',
            'description' => 'Perform risk assessments at planned intervals or when significant changes occur.',
            'questions'   => [
                'To what extent does the organization perform risk assessments routinely according to planned intervals (e.g., annually)?',
                'Does the organization perform additional risk assessments when significant changes occur (such as new systems or structural changes)?'
            ],
            'implementation_guidance' => 'Periodic risk assessment reports (e.g., 2024 vs 2025) and ad-hoc risk assessment reports for major infrastructure/application changes.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause8->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '8.3',
            'title'       => 'Information security risk treatment',
            'description' => 'Implement the Risk Treatment Plan.',
            'questions'   => [
                'To what extent has the organization implemented actions in the Risk Treatment Plan (RTP) to reduce risk levels?',
                'Does the organization retain documented evidence or records of each risk treatment outcome?'
            ],
            'implementation_guidance' => 'Evidence of RTP execution, such as firewall configuration screenshots, staff training certificates, or system logs indicating activated security controls.'
        ]);

        // --- CLAUSE 9: PERFORMANCE EVALUATION ---
        $clause9 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'clause',
            'level'     => 'clause',
            'code'      => '9',
            'title'     => 'Performance evaluation',
        ]);

        IsoStandard::create([
            'parent_id'   => $clause9->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '9.1',
            'title'       => 'Monitoring, measurement, analysis and evaluation',
            'description' => 'The organization must evaluate information security performance and ISMS effectiveness by determining what needs to be monitored, measured, and the analysis methods used.',
            'questions'   => [
                'To what extent has the organization determined objects to be monitored (processes & controls) and valid, comparable, and reproducible measurement methods?',
                'Has the organization established a schedule (when) and assigned personnel (who) to conduct monitoring, analysis, and evaluation of measurement results?',
                'To what extent does the organization analyze monitoring results to evaluate overall information security performance and ISMS effectiveness?'
            ],
            'implementation_guidance' => 'Requires information security KPI documents, such as server downtime percentage, monthly security incident count, or periodic log monitoring reviews.'
        ]);

        $sub92 = IsoStandard::create([
            'parent_id' => $clause9->id,
            'type'      => 'clause',
            'level'     => 'sub_clause',
            'code'      => '9.2',
            'title'     => 'Internal audit',
        ]);

        IsoStandard::create([
            'parent_id'   => $sub92->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '9.2.1',
            'title'       => 'General',
            'description' => 'The organization must conduct internal audits at planned intervals to provide information on whether the ISMS conforms to organizational and ISO 27001 requirements.',
            'questions'   => [
                'To what extent does the organization conduct routine internal audits to ensure ISMS conformance to ISO 27001 requirements and internal policies?',
                'Has the internal audit objectively evaluated that all security controls are effectively implemented?'
            ],
            'implementation_guidance' => 'An Internal Audit Report covering the entire ISMS scope must be available.'
        ]);

        IsoStandard::create([
            'parent_id'   => $sub92->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '9.2.2',
            'title'       => 'Internal audit programme',
            'description' => 'The organization must plan, establish, implement, and maintain an audit programme covering frequency, methods, responsibilities, planning requirements, and reporting.',
            'questions'   => [
                'To what extent has the organization established an audit programme covering frequency, methods, and responsibilities?',
                'How does the organization ensure auditor objectivity (not auditing their own work)?',
                'Have audit results been officially reported to management and retained as documented evidence?'
            ],
            'implementation_guidance' => 'Requires an Annual Audit Plan and Schedule. Crucially, auditors must demonstrate competence via internal auditor certifications.'
        ]);

        $sub93 = IsoStandard::create([
            'parent_id' => $clause9->id,
            'type'      => 'clause',
            'level'     => 'sub_clause',
            'code'      => '9.3',
            'title'     => 'Management review',
        ]);

        IsoStandard::create([
            'parent_id'   => $sub93->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '9.3.1',
            'title'       => 'General',
            'description' => 'Top management must review the organization\'s ISMS at planned intervals to ensure its continuing suitability, adequacy, and effectiveness.',
            'questions'   => [
                'To what extent does top management review the ISMS at planned intervals to ensure its continuing suitability, adequacy, and effectiveness?'
            ],
            'implementation_guidance' => 'Evidence includes an annual MRM (Management Review Meeting) schedule and confirmation of top management (Directors/Heads) attendance.'
        ]);

        IsoStandard::create([
            'parent_id'   => $sub93->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '9.3.2',
            'title'       => 'Management review inputs',
            'description' => 'Management review must include considerations of status of actions from previous reviews, changes in internal/external issues, security performance feedback, audit results, and risk status.',
            'questions'   => [
                'To what extent does the management review evaluate security performance trends, including audit results and objective achievements?',
                'Does the review cover changes in internal/external issues and the latest risk assessment status?'
            ],
            'implementation_guidance' => 'Requires MRM Agenda or Presentation Slides containing complete data according to 9.3.2 (incident trends, internal audit results, risk status).'
        ]);

        IsoStandard::create([
            'parent_id'   => $sub93->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '9.3.3',
            'title'       => 'Management review results',
            'description' => 'The results of the management review must include decisions related to continual improvement opportunities and any needs for changes to the ISMS.',
            'questions'   => [
                'To what extent do management review outcomes produce decisions regarding continual improvement opportunities?',
                'Do review outputs include specific assignments, resource provisioning, or policy revisions?',
                'Does the organization retain documented evidence of management review results?'
            ],
            'implementation_guidance' => 'Mandatory signed Minutes of Meeting (MoM) and an Action Plan representing top management decisions.'
        ]);

        // --- CLAUSE 10: IMPROVEMENT ---
        $clause10 = IsoStandard::create([
            'parent_id' => null,
            'type'      => 'clause',
            'level'     => 'clause',
            'code'      => '10',
            'title'     => 'Improvement',
        ]);

        IsoStandard::create([
            'parent_id'   => $clause10->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '10.1',
            'title'       => 'Continual improvement',
            'description' => 'The organization must continually improve the suitability, adequacy and effectiveness of the ISMS.',
            'questions'   => [
                'To what extent does the organization demonstrate a commitment to continually improve ISMS effectiveness by utilizing data analysis, audits, and management review outcomes?',
                'Does the organization actively adapt the ISMS to remain relevant to the latest threat landscape?'
            ],
            'implementation_guidance' => 'Evidence includes security technology upgrade initiatives, policy revisions based on recent cyber threats, or security infrastructure capacity enhancements.'
        ]);

        IsoStandard::create([
            'parent_id'   => $clause10->id,
            'type'        => 'clausa',
            'level'       => 'requirement',
            'code'        => '10.2',
            'title'       => 'Nonconformity and corrective action',
            'description' => 'React to nonconformities, eliminate root causes, and review the effectiveness of corrective actions.',
            'questions'   => [
                'To what extent does the organization react to nonconformities by taking immediate control and correction actions?',
                'Does the organization evaluate the need for action to eliminate root causes?',
                'Does the organization retain complete records detailing the nature of nonconformities and outcomes of corrective actions?'
            ],
            'implementation_guidance' => 'Mandatory Non-Conformity Report (NCR) and Corrective Action (CAPA) Log. Ensure Root Cause Analysis (e.g., 5 Whys or Fishbone) is documented.'
        ]);
    }
}
