<?php

/**
 * Map database field names to user-friendly display labels.
 * Used in audit trail / activity log displays.
 */
if (!function_exists('friendly_field_label')) {
    function friendly_field_label(?string $field): string
    {
        if (!$field) return '-';

        $map = [
            'maturity_rating'       => 'Likert Scale',
            'is_applicable'         => 'Applicability',
            'soa_justification'     => 'SoA Justification',
            'notes'                 => 'Notes / Remarks',
            'evidence_file'         => 'Evidence File',
            'status'                => 'Assessment Status',
            'treatment_status'      => 'Improvement Status',
            'treatment_plan'        => 'Improvement Plan',
            'treatment_pic'         => 'Person In Charge (PIC)',
            'treatment_due_date'    => 'Due Date',
            'treatment_priority'    => 'Priority',
            'compliance_status'     => 'Compliance Status',
            'gap_detail'            => 'Gap Detail',
            'risk_level'            => 'Risk Level',
            'risk_description'      => 'Risk Description',
            'overall_maturity_score'=> 'Overall Maturity Score',
            'job_title'             => 'Organizational Position',
            'role_description'      => 'Role Description',
        ];

        return $map[$field] ?? str_replace('_', ' ', \Illuminate\Support\Str::title($field));
    }
}
