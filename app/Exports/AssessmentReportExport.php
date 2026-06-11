<?php

namespace App\Exports;

use App\Models\AssessmentResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssessmentReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sessionId;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function collection()
    {
        return AssessmentResult::with(['standard', 'session'])
            ->where('session_id', $this->sessionId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Control Code',
            'Control Title',
            'Maturity Rating',
            'Compliance Status',
            'Risk Priority',
            'AI Strategic Recommendation',
            'Corrective Action Plan (CAP)',
            'ISO Control Insight',
            'Auditor Notes',
            'Executive Session Summary',
            'Created At'
        ];
    }

    public function map($result): array
    {
        $cap = is_array($result->corrective_action_plan) 
            ? implode("\n- ", $result->corrective_action_plan) 
            : ($result->corrective_action_plan ?? '');

        $insight = is_array($result->control_insight)
            ? ($result->control_insight['gap'] ?? implode(' | ', $result->control_insight))
            : ($result->control_insight ?? '');

        return [
            $result->id,
            $result->standard->code,
            $result->standard->title,
            $result->maturity_rating,
            $result->compliance_status,
            $result->risk_level,
            $result->ai_recommendation,
            $cap,
            $insight,
            $result->notes,
            $result->session->ai_summary,
            $result->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
