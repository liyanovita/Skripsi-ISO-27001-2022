<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('ISO 27001:2022 Assessment Report') }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1e293b; line-height: 1.5; font-size: 9px; margin: 0; padding: 0; }
        .page { padding: 5px; }
        .page-break { page-break-after: always; }
        
        /* Typography */
        h1, h2, h3 { color: #0B2545; margin: 0; }
        
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #0B2545; border-bottom: 2px solid #008B9B; padding-bottom: 4px; margin: 25px 0 10px; letter-spacing: 0.5px; }
        
        /* Boxes & Cards */
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .ai-card { border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px; overflow: hidden; page-break-inside: avoid; }
        .ai-card-header { background: #0B2545; color: white; padding: 8px 12px; font-size: 9px; font-weight: bold; }
        .ai-card-body { padding: 12px; background: #fff; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; color: #475569; font-size: 8px; text-transform: uppercase; font-weight: bold; padding: 8px; border: 1px solid #e2e8f0; text-align: left; }
        td { padding: 8px; border: 1px solid #e2e8f0; font-size: 8.5px; color: #334155; vertical-align: top; }
        
        /* Badges */
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-info { background: #e0f2fe; color: #035388; }
        .badge-slate { background: #f1f5f9; color: #475569; }

        /* Footer */
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 7.5px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 8px; }
        
        /* Colors */
        .text-navy { color: #0B2545; }
        .text-teal { color: #008B9B; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <!-- HEADER / COVER TOP -->
    <div style="margin-bottom: 20px; border-bottom: 2px solid #008B9B; padding-bottom: 10px;">
        <table style="width: 100%; border: none; margin-bottom: 0;">
            <tr>
                <td style="width: 50px; border: none; padding: 0; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 40px; width: 40px; border-radius: 6px;">
                    @endif
                </td>
                <td style="border: none; padding: 0 0 0 10px; vertical-align: middle; text-align: left;">
                    <div style="font-size: 18px; font-weight: bold; line-height: 1.1;">
                        <span style="color: #0B2545;">Audit</span><span style="color: #008B9B;">Guard</span>
                    </div>
                    <div style="font-size: 6.5px; font-weight: 900; color: #64748b; letter-spacing: 2px; margin-top: 2px; text-transform: uppercase;">
                        ASSESS &bull; ANALYZE &bull; ASSURE
                    </div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle; color: #475569;">
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('ISO 27001:2022 Assessment Report') }}</div>
                    <div style="font-size: 7.5px; margin-top: 2px; color: #64748b;">
                        Session: {{ $session->name }}<br>
                        Generated: {{ $date }} &nbsp;|&nbsp; {{ auth()->user()->name }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. EXECUTIVE SUMMARY -->
    <div class="section-title">1. Executive Summary</div>
    <div class="summary-box" style="border-left: 4px solid #0B2545;">
        <table style="width: 100%; border: none; margin-bottom: 10px; background: transparent;">
            <tr style="background: transparent;">
                <td style="border: none; padding: 0 15px 0 0; width: 50%;">
                    <div style="font-size: 10px; font-weight: bold; color: #0B2545; margin-bottom: 5px;">{{ __('Organization Information') }}</div>
                    <div style="line-height: 1.6;">
                        <strong>{{ __('Organization Name') }}:</strong> {{ $session->organization->name ?? '-' }}<br>
                        <strong>{{ __('Sector') }}:</strong> {{ $session->organization->business_sector ?? '-' }}<br>
                        <strong>{{ __('ISMS Scope') }}:</strong> {{ $session->organization->isms_scope ?? '-' }}
                    </div>
                </td>
                <td style="border: none; padding: 0; width: 50%;">
                    <div style="font-size: 10px; font-weight: bold; color: #0B2545; margin-bottom: 5px;">{{ __('Audit Information') }}</div>
                    <div style="line-height: 1.6;">
                        <strong>{{ __('Audit Session') }}:</strong> {{ $session->name }}<br>
                        <strong>{{ __('Target Deadline') }}:</strong> {{ $session->deadline ? \Carbon\Carbon::parse($session->deadline)->format('d F Y') : '-' }}<br>
                        <strong>{{ __('Assessor / Auditor') }}:</strong> {{ $session->user->name ?? '-' }}
                    </div>
                </td>
            </tr>
        </table>
        
        @php
            $parsedSummary = \App\Services\Intelligence\AiSummaryService::parseSummary($summary);
            $overallSummary = $parsedSummary['overall_assessment_summary'] ?? $parsedSummary['overall_assessment_conclusion'] ?? '';
            $controlInsight = $parsedSummary['control_insight'] ?? $parsedSummary['overall_risk_areas'] ?? '';
            $impactInterpretation = $parsedSummary['impact_interpretation'] ?? $parsedSummary['assessment_confidence'] ?? '';
            $strategicRec = $parsedSummary['strategic_recommendation'] ?? $parsedSummary['executive_strategic_recommendations'] ?? [];
            $actionPlan = $parsedSummary['action_plan'] ?? '';
        @endphp

        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #e2e8f0; line-height: 1.6;">
            <div style="font-weight: bold; color: #0B2545; text-transform: uppercase; font-size: 8.5px; margin-bottom: 4px;">{{ __('Overall Assessment Summary') }}</div>
            <div style="color: #334155; font-size: 9px;">
                {{ $overallSummary ?: (is_string($summary) ? $summary : __('No overall summary generated.')) }}
            </div>
        </div>
    </div>

    <!-- 2. COMPLIANCE & OVERALL MATURITY -->
    <div class="section-title">2. Compliance & Overall Maturity</div>
    <table style="width: 100%; border: none; margin-bottom: 20px; border-spacing: 8px; border-collapse: separate;">
        <tr>
            <td style="width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Compliance Score</div>
                <div style="font-size: 18px; font-weight: 900; color: #008B9B;">{{ $complianceScore }}%</div>
            </td>
            <td style="width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Compliance Status</div>
                @php
                    $statusColor = match($complianceStatusText) {
                        'Compliant' => '#059669',
                        'Partially Compliant' => '#d97706',
                        default => '#dc2626',
                    };
                @endphp
                <div style="font-size: 11px; font-weight: bold; color: {{ $statusColor }};">{{ __($complianceStatusText) }}</div>
            </td>
            <td style="width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Overall Maturity Score</div>
                <div style="font-size: 18px; font-weight: 900; color: #0b2545;">{{ number_format($overallMaturity, 2) }} / 5.0</div>
            </td>
            <td style="width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Maturity Level</div>
                <div style="font-size: 10px; font-weight: bold; color: #475569;">{{ __($maturityLevelLabel) }}</div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- 3. GAP ANALYSIS -->
    <div class="section-title">3. Gap Analysis (Critical & Partially Compliant Controls)</div>
    <p style="margin-top: 0; margin-bottom: 10px; color: #64748b; font-size: 8.5px;">
        {{ __('The following table displays all applicable controls that did not reach the target compliance level (Maturity Level 4 - Managed).') }}
    </p>
    <table>
        <thead>
            <tr>
                <th style="width: 8%">{{ __('No') }}</th>
                <th style="width: 12%">{{ __('Code') }}</th>
                <th style="width: 42%">{{ __('Control Name') }}</th>
                <th style="width: 13%">{{ __('Maturity') }}</th>
                <th style="width: 10%">{{ __('Gap') }}</th>
                <th style="width: 15%">{{ __('Compliance') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gapResults as $index => $result)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; color: #0B2545;">{{ $result->standard->code }}</td>
                <td>{{ $result->standard->title }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $result->maturity_rating }} / 5</td>
                <td style="text-align: center; font-weight: bold; color: #dc2626;">{{ 5 - $result->maturity_rating }}</td>
                <td style="text-align: center;">
                    <span class="badge {{ $result->maturity_rating >= 4 ? 'badge-success' : ($result->maturity_rating >= 2 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $result->compliance_status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">
                    {{ __('No gaps found. All applicable controls are compliant!') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. RISK PRIORITY -->
    <div class="section-title">4. Risk Priority Mapping</div>
    <p style="margin-top: 0; margin-bottom: 10px; color: #64748b; font-size: 8.5px;">
        {{ __('This section ranks improvement activities by audit risk priority. Actions should target Critical and High priorities first.') }}
    </p>
    <table>
        <thead>
            <tr>
                <th style="width: 15%">{{ __('Risk Priority') }}</th>
                <th style="width: 15%">{{ __('Control Code') }}</th>
                <th style="width: 45%">{{ __('Control Name') }}</th>
                <th style="width: 10%">{{ __('Maturity') }}</th>
                <th style="width: 15%">{{ __('Remediation Target') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sortedByRisk = $gapResults->sortByDesc(function($r) {
                    return match($r->risk_priority) {
                        'Critical' => 4,
                        'High' => 3,
                        'Medium' => 2,
                        default => 1
                    };
                });
            @endphp
            @forelse($sortedByRisk as $result)
            <tr>
                <td style="text-align: center;">
                    <span class="badge
                        {{ $result->risk_priority == 'Critical' ? 'badge-danger' : '' }}
                        {{ $result->risk_priority == 'High' ? 'badge-warning' : '' }}
                        {{ $result->risk_priority == 'Medium' ? 'badge-info' : '' }}
                        {{ $result->risk_priority == 'Low' || !$result->risk_priority ? 'badge-success' : '' }}
                    ">
                        {{ $result->risk_priority ?: 'Low' }}
                    </span>
                </td>
                <td style="font-weight: bold; color: #0b2545;">{{ $result->standard->code }}</td>
                <td>{{ $result->standard->title }}</td>
                <td style="text-align: center;">{{ $result->maturity_rating }} / 5</td>
                <td style="font-weight: bold; color: {{ $result->maturity_rating <= 1 ? '#991b1b' : '#92400e' }}; text-align: center;">
                    {{ $result->maturity_rating <= 1 ? '30 Days' : ($result->maturity_rating == 2 ? '60 Days' : '90 Days') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">
                    {{ __('No high-priority risk remediations pending.') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- 5. AI ANALYSIS -->
    <div class="section-title">5. Artificial Intelligence (AI) Audit Insights</div>
    
    @if(!empty($overallSummary))
        <div class="ai-card" style="border-left: 4px solid #0B2545;">
            <div class="ai-card-header" style="background: #0B2545;">A. OVERALL ASSESSMENT SUMMARY</div>
            <div class="ai-card-body" style="font-size: 8.5px; color: #334155; line-height: 1.6;">
                {{ $overallSummary }}
            </div>
        </div>
    @endif

    @if(!empty($controlInsight))
        <div class="ai-card" style="border-left: 4px solid #dc2626;">
            <div class="ai-card-header" style="background: #dc2626;">B. CONTROL INSIGHT & WEAKNESS AREAS</div>
            <div class="ai-card-body" style="font-size: 8.5px; color: #334155; line-height: 1.6;">
                {{ $controlInsight }}
            </div>
        </div>
    @endif

    @if(!empty($impactInterpretation))
        <div class="ai-card" style="border-left: 4px solid #0284c7;">
            <div class="ai-card-header" style="background: #0284c7;">C. IMPACT INTERPRETATION & RISK ASSESSMENT</div>
            <div class="ai-card-body" style="font-size: 8.5px; color: #334155; line-height: 1.6;">
                {{ $impactInterpretation }}
            </div>
        </div>
    @endif

    @if(!empty($strategicRec))
        <div class="ai-card" style="border-left: 4px solid #d97706;">
            <div class="ai-card-header" style="background: #d97706;">D. STRATEGIC RECOMMENDATIONS</div>
            <div class="ai-card-body" style="font-size: 8.5px; color: #334155; line-height: 1.6;">
                @if(is_array($strategicRec))
                    <ol style="margin: 0; padding-left: 15px;">
                        @foreach($strategicRec as $rec)
                            <li style="margin-bottom: 4px;">{{ $rec }}</li>
                        @endforeach
                    </ol>
                @else
                    {{ $strategicRec }}
                @endif
            </div>
        </div>
    @endif

    @if(!empty($actionPlan))
        <div class="ai-card" style="border-left: 4px solid #059669;">
            <div class="ai-card-header" style="background: #059669;">E. REMEDIATION ACTION PLAN</div>
            <div class="ai-card-body" style="font-size: 8.5px; color: #334155; line-height: 1.6;">
                {{ $actionPlan }}
            </div>
        </div>
    @endif

    <div class="page-break"></div>

    <!-- 6. IMPROVEMENT TRACKING (CAPA) -->
    <div class="section-title">6. Remediation & Improvement Tracking (CAPA)</div>
    <p style="margin-top: 0; margin-bottom: 10px; color: #64748b; font-size: 8.5px;">
        {{ __('The following roadmap logs real-time corrective actions, assignees, deadlines, and verified physical evidence of compliance improvements.') }}
    </p>
    
    <table>
        <thead>
            <tr>
                <th style="width: 10%">{{ __('Control') }}</th>
                <th style="width: 25%">{{ __('Corrective Action Plan') }}</th>
                <th style="width: 12%">{{ __('PIC') }}</th>
                <th style="width: 13%">{{ __('Due Date') }}</th>
                <th style="width: 15%">{{ __('Status & Progress') }}</th>
                <th style="width: 25%">{{ __('Evidence After Improvement') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trackingResults as $result)
            @php
                $plan = $result->corrective_action_plan ?: [];
                $planActionText = is_array($plan) ? ($plan['action'] ?? '-') : ($plan ?: '-');
                
                $status = $result->treatment_status ?: 'open';
                $progress = $result->treatment_progress ?? 0;
            @endphp
            <tr>
                <td style="font-weight: bold; color: #0b2545;">{{ $result->standard->code }}</td>
                <td style="font-size: 8px; line-height: 1.4;">{{ $planActionText }}</td>
                <td style="font-weight: bold; color: #475569;">{{ $result->treatment_pic ?: '-' }}</td>
                <td style="font-size: 8px; font-weight: bold; text-align: center;">
                    @if($result->treatment_due_date)
                        @if($result->treatment_due_date->isPast() && $status !== 'completed')
                            <span style="color: #dc2626;">{{ $result->treatment_due_date->format('d M Y') }} (Overdue)</span>
                        @else
                            <span>{{ $result->treatment_due_date->format('d M Y') }}</span>
                        @endif
                    @else
                        <span style="color: #94a3b8; font-style: italic;">Not Set</span>
                    @endif
                </td>
                <td>
                    <span class="badge
                        {{ $status == 'completed' ? 'badge-success' : '' }}
                        {{ $status == 'in_progress' ? 'badge-info' : '' }}
                        {{ $status == 'open' ? 'badge-danger' : '' }}
                    " style="margin-bottom: 4px;">
                        {{ ucfirst($status) }}
                    </span>
                    <br>
                    <span style="font-weight: bold; font-size: 8px; color: #475569;">Progress: {{ $progress }}%</span>
                </td>
                <td style="font-size: 8px; font-style: italic; color: #64748b; line-height: 1.4;">
                    {{ $result->evidence_after_improvement ?: '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">
                    {{ __('No improvement activities currently logged.') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        AuditGuard &copy; {{ date('Y') }} | ISO 27001:2022 Assessment Report
    </div>
</body>
</html>
