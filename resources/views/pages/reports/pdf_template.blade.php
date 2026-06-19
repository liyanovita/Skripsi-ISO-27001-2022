<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('ISO 27001:2022 Improvement Roadmap') }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #2563eb; padding-bottom: 20px; }
        .header h1 { color: #1e293b; margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { color: #64748b; margin: 5px 0; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        
        .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #1e293b; border-left: 4px solid #2563eb; padding-left: 10px; margin: 30px 0 15px; letter-spacing: 1px; }
        
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 12px; margin-bottom: 30px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f1f5f9; color: #475569; font-size: 9px; text-transform: uppercase; text-align: left; padding: 12px; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        
        .badge { padding: 3px 10px; border-radius: 6px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #d1fae5; color: #065f46; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        
        .ai-card { margin-bottom: 25px; page-break-inside: avoid; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .ai-card-header { background: #0f172a; color: white; padding: 10px 20px; font-size: 10px; font-weight: bold; }
        .ai-card-body { padding: 20px; }
        
        .cap-box { margin-top: 15px; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 10px; color: #1e293b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('ISO 27001:2022 IMPROVEMENT ROADMAP') }}</h1>
        <p>Audit Session: {{ $session->name }}</p>
        <p>Generated on: {{ $date }} | User: {{ auth()->user()->name }}</p>
    </div>

    <div class="section-title">Executive Summary & AI Intelligence</div>
    <div class="summary-box" style="border-left: 5px solid #2563eb;">
        <div style="font-size: 18px; font-weight: bold; color: #1e293b; margin-bottom: 15px;">
            Overall Maturity Score: {{ number_format($session->overall_maturity_score, 1) }} / 5.0
        </div>
        <div style="font-size: 11px; color: #334155; line-height: 1.8; white-space: pre-line;">
            {!! nl2br(e($summary)) !!}
        </div>
    </div>

    <div class="section-title">Priority Roadmap & Critical Gaps</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%">{{ __('Priority') }}</th>
                <th style="width: 12%">{{ __('Code') }}</th>
                <th style="width: 30%">{{ __('Control Name') }}</th>
                <th style="width: 22%">{{ __('Status') }}</th>
                <th style="width: 12%">{{ __('Risk') }}</th>
                <th style="width: 14%">{{ __('Target') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $result)
            @php
                $targetDays = match(true) {
                    $result->maturity_rating <= 1 => '30 Days',
                    $result->maturity_rating == 2 => '60 Days',
                    $result->maturity_rating == 3 => '90 Days',
                    default => '180 Days',
                };
                $targetColor = match(true) {
                    $result->maturity_rating <= 1 => '#991b1b',
                    $result->maturity_rating == 2 => '#92400e',
                    default => '#374151',
                };
            @endphp
            <tr>
                <td>
                    <span class="badge {{ $result->maturity_rating == 1 ? 'badge-danger' : 'badge-warning' }}">
                        #{{ $index + 1 }}
                    </span>
                </td>
                <td style="font-weight: bold;">{{ $result->standard->code }}</td>
                <td>{{ $result->standard->title }}</td>
                <td>
                    <div style="margin-bottom: 3px; font-size: 9px; color: #64748b;">Maturity Level {{ $result->maturity_rating }}</div>
                    <span class="badge {{ $result->maturity_rating >= 4 ? 'badge-success' : ($result->maturity_rating >= 2 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $result->compliance_status }}
                    </span>
                </td>
                <td style="font-weight: bold; color: {{ $result->maturity_rating == 1 ? '#991b1b' : '#92400e' }};">{{ $result->risk_level }}</td>
                <td style="font-weight: bold; color: {{ $targetColor }};">{{ $targetDays }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">AI Intelligence Analysis & Improvement Plans</div>
    @php
        $aiResults = $results->filter(fn($r) => !empty($r->ai_recommendation));
    @endphp

    @if($aiResults->count() > 0)
        @foreach($aiResults as $result)
        <div class="ai-card">
            <div class="ai-card-header">
                {{ $result->standard->code }}: {{ $result->standard->title }}
            </div>
            <div class="ai-card-body">
                <table style="margin-bottom: 0; border: none;">
                    <tr>
                        <td style="width: 50%; border: none; padding: 0 15px 0 0; vertical-align: top;">
                            <div style="font-size: 8px; font-weight: bold; color: #2563eb; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">{{ __('Strategic Recommendation') }}</div>
                            <div style="font-size: 10px; color: #334155; line-height: 1.5;">{{ $result->ai_recommendation }}</div>
                        </td>
                        <td style="width: 50%; border: none; padding: 0 0 0 15px; vertical-align: top;">
                            <div style="font-size: 8px; font-weight: bold; color: #d97706; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">{{ __('AI Evidence Validation') }}</div>
                            <div style="font-size: 10px; color: #475569; font-style: italic; line-height: 1.5;">{{ $result->evidence_validation ?? 'No evidence provided.' }}</div>
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                    <div style="font-size: 8px; font-weight: bold; color: #1e293b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">{{ __('Corrective Action Plan (CAP)') }}</div>
                    <div class="cap-box">
                        @if(is_array($result->corrective_action_plan))
                            {!! nl2br(e($result->corrective_action_plan['action'] ?? implode("\n", $result->corrective_action_plan))) !!}
                        @else
                            {!! nl2br(e($result->corrective_action_plan)) !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="summary-box">
            <p style="text-align: center; color: #94a3b8; font-size: 11px;">{{ __('No AI analysis has been generated for this session yet.') }}</p>
        </div>
    @endif

    <div class="footer">
        Audit Intelligence Hub | Confidential Internal Audit Document | Digital Signature Verified
    </div>
</body>
</html>
