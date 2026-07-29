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
        
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #0B2545; border-bottom: 2px solid #008B9B; padding-bottom: 4px; margin: 20px 0 10px; letter-spacing: 0.5px; }
        
        /* Boxes & Cards */
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 15px; border-radius: 8px; margin-bottom: 16px; }
        .ai-card { border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 14px; overflow: hidden; page-break-inside: avoid; background: #ffffff; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
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
    <div style="margin-bottom: 16px; border-bottom: 2px solid #008B9B; padding-bottom: 10px;">
        <table style="width: 100%; border: none; margin-bottom: 0;">
            <tr>
                <td style="width: 50px; border: none; padding: 0; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 40px; width: 40px; border-radius: 6px;">
                    @endif
                </td>
                <td style="border: none; padding: 0 0 0 10px; vertical-align: middle; text-align: left;">
                    <div style="font-size: 18px; font-weight: bold; line-height: 1.1;">
                        <span style="color: #0B2545;">Audit</span><span style="color: #0284c7;">Guard</span>
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

    <!-- ORGANIZATION & AUDIT INFORMATION (PLACED ABOVE EXECUTIVE SUMMARY) -->
    <div class="summary-box" style="border-left: 4px solid #008B9B; margin-bottom: 16px;">
        <table style="width: 100%; border: none; margin-bottom: 0; background: transparent;">
            <tr style="background: transparent;">
                <td style="border: none; padding: 0 15px 0 0; width: 50%; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold; color: #0B2545; margin-bottom: 5px; text-transform: uppercase;">{{ __('Organization Information') }}</div>
                    <div style="line-height: 1.6; font-size: 8.5px;">
                        <strong>{{ __('Organization Name') }}:</strong> {{ $session->organization->name ?? '-' }}<br>
                        <strong>{{ __('Sector') }}:</strong> {{ $session->organization->business_sector ?? '-' }}<br>
                        <strong>{{ __('ISMS Scope') }}:</strong> {{ $session->organization->isms_scope ?? '-' }}
                    </div>
                </td>
                <td style="border: none; padding: 0; width: 50%; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold; color: #0B2545; margin-bottom: 5px; text-transform: uppercase;">{{ __('Audit Information') }}</div>
                    <div style="line-height: 1.6; font-size: 8.5px;">
                        <strong>{{ __('Audit Session') }}:</strong> {{ $session->name }}<br>
                        <strong>{{ __('Target Deadline') }}:</strong> {{ $session->deadline ? \Carbon\Carbon::parse($session->deadline)->format('d F Y') : '-' }}<br>
                        <strong>{{ __('Assessor / Auditor') }}:</strong> {{ $session->user->name ?? '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. EXECUTIVE SUMMARY -->
    <div class="section-title">1. Executive Summary</div>
    <div class="summary-box" style="border-left: 4px solid #0B2545; line-height: 1.6;">
        @php
            $parsedSummary = \App\Services\Intelligence\AiSummaryService::parseSummary($summary);
            $overallSummary = $parsedSummary['overall_assessment_summary'] ?? $parsedSummary['overall_assessment_conclusion'] ?? '';
        @endphp
        <div style="color: #334155; font-size: 9px; text-align: justify;">
            {{ $overallSummary ?: (is_string($summary) ? $summary : __('No overall summary generated.')) }}
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

    <!-- 3. GAP ANALYSIS & RISK PRIORITY MAPPING (MERGED SECTION) -->
    <div class="section-title">3. Gap Analysis & Risk Priority Mapping</div>
    <p style="margin-top: 0; margin-bottom: 10px; color: #64748b; font-size: 8.5px;">
        {{ __('The following table consolidates all identified control gaps (Maturity Rating < 5) ranked by calculated risk priority, compliance status, gap severity, and recommended remediation timeline.') }}
    </p>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">{{ __('No') }}</th>
                <th style="width: 10%">{{ __('Code') }}</th>
                <th style="width: 35%">{{ __('Control Title') }}</th>
                <th style="width: 13%; text-align: center;">{{ __('Risk Priority') }}</th>
                <th style="width: 12%; text-align: center;">{{ __('Maturity / Gap') }}</th>
                <th style="width: 13%; text-align: center;">{{ __('Compliance') }}</th>
                <th style="width: 12%; text-align: center;">{{ __('Remediation Target') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sortedGaps = $gapResults->sortByDesc(function($r) {
                    $p = $r->calculated_risk_priority ?? $r->risk_priority;
                    return match($p) {
                        'Critical' => 4,
                        'High' => 3,
                        'Medium' => 2,
                        default => 1
                    };
                })->values();
            @endphp
            @forelse($sortedGaps as $index => $result)
            @php 
                $mRating = is_null($result->maturity_rating) ? 0 : $result->maturity_rating; 
                $riskP = $result->calculated_risk_priority ?? $result->risk_priority ?? 'Low';
                $riskBadgeClass = match($riskP) {
                    'Critical' => 'badge-danger',
                    'High'     => 'badge-danger',
                    'Medium'   => 'badge-warning',
                    default    => 'badge-info'
                };
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; color: #0B2545;">{{ $result->standard->code }}</td>
                <td style="font-weight: bold; color: #334155;">{{ $result->standard->title }}</td>
                <td style="text-align: center;">
                    <span class="badge {{ $riskBadgeClass }}">
                        {{ $riskP }}
                    </span>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ $mRating }} / 5 <br>
                    <span style="font-size: 7.5px; color: #dc2626;">(Gap: {{ 5 - $mRating }})</span>
                </td>
                <td style="text-align: center;">
                    <span class="badge {{ $mRating >= 4 ? 'badge-success' : ($mRating >= 2 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $result->compliance_status }}
                    </span>
                </td>
                <td style="font-weight: bold; color: {{ $mRating <= 1 ? '#991b1b' : '#92400e' }}; text-align: center;">
                    {{ $mRating <= 1 ? '30 Days' : ($mRating == 2 ? '60 Days' : '90 Days') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px;">
                    {{ __('No gaps found. All applicable controls are compliant!') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- 4. ARTIFICIAL INTELLIGENCE (AI) AUDIT RECOMMENDATIONS FOR ALL IDENTIFIED GAPS -->
    <div class="section-title">4. Artificial Intelligence (AI) Audit Recommendations for All Identified Gaps</div>
    <p style="margin-top: 0; margin-bottom: 12px; color: #64748b; font-size: 8.5px;">
        {{ __('Comprehensive AI audit recommendations, corrective action plans, audit insights, and impact interpretations generated for every control identified with a gap.') }}
    </p>

    @forelse($gapResults as $index => $result)
    @php
        $mRating = is_null($result->maturity_rating) ? 0 : $result->maturity_rating;
        $gapVal = 5 - $mRating;
        $riskP = $result->calculated_risk_priority ?? $result->risk_priority ?? 'Low';
        
        $rawRec = $result->ai_recommendation ?: '';
        $recText = trim(preg_replace('/^(Rekomendasi\s*AI:\s*|AI\s*Recommendation:\s*|Rekomendasi:\s*)/iu', '', $rawRec));
        
        $planData = $result->corrective_action_plan;
        $planText = is_array($planData) ? implode("\n", array_filter(array_map(fn($i) => is_array($i) ? implode(' ', $i) : trim((string)$i), $planData))) : ($planData ?: '');
        if (is_array($planData) && $planText) { 
            $planLines = array_filter(explode("\n", $planText));
            $planText = implode("\n", array_map(fn($l) => '&bull; ' . ltrim($l, '•- '), $planLines));
        }
        
        $insightData = $result->control_insight;
        $insightText = is_array($insightData) ? implode("\n", array_filter(array_map(fn($i) => is_array($i) ? implode(' ', $i) : trim((string)$i), $insightData))) : ($insightData ?: '');
        
        $impactText = $result->impact_interpretation ?: '';
    @endphp

    <div class="ai-card" style="border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 14px; overflow: hidden; page-break-inside: avoid; background: #ffffff;">
        {{-- Card Header --}}
        <table style="width: 100%; background: #0B2545; color: white; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="padding: 8px 12px; border: none; font-size: 9.5px; font-weight: bold; color: white;">
                    <span style="background: #008B9B; padding: 2px 6px; border-radius: 4px; font-size: 8.5px; margin-right: 6px;">{{ $result->standard->code }}</span>
                    {{ $result->standard->title }}
                </td>
                <td style="padding: 8px 12px; border: none; font-size: 8px; text-align: right; color: #e2e8f0; white-space: nowrap;">
                    <strong>Maturity:</strong> {{ $mRating }}/5 (Gap: {{ $gapVal }}) &nbsp;|&nbsp;
                    <strong>Risk:</strong> 
                    <span class="badge {{ $riskP === 'High' || $riskP === 'Critical' ? 'badge-danger' : ($riskP === 'Medium' ? 'badge-warning' : 'badge-info') }}">
                        {{ strtoupper($riskP) }}
                    </span>
                </td>
            </tr>
        </table>

        {{-- Card Body --}}
        <div style="padding: 10px 12px; font-size: 8.5px; line-height: 1.5; color: #334155;">
            {{-- 1. Strategic Recommendation --}}
            @if($recText)
            <div style="margin-bottom: 8px; padding: 6px 10px; background: #f0f9ff; border-left: 3px solid #0284c7; border-radius: 4px;">
                <div style="font-weight: bold; color: #0369a1; font-size: 8px; text-transform: uppercase; margin-bottom: 2px;">
                    {{ __('STRATEGIC RECOMMENDATION') }}
                </div>
                <div style="color: #0c4a6e; font-size: 8.5px;">{{ $recText }}</div>
            </div>
            @endif

            {{-- 2. Corrective Action Plan --}}
            @if($planText)
            <div style="margin-bottom: 8px; padding: 6px 10px; background: #f8fafc; border-left: 3px solid #0b2545; border-radius: 4px;">
                <div style="font-weight: bold; color: #0b2545; font-size: 8px; text-transform: uppercase; margin-bottom: 2px;">
                    {{ __('CORRECTIVE ACTION PLAN') }}
                </div>
                <div style="color: #1e293b; font-size: 8.5px; whitespace-pre-line;">{!! $planText !!}</div>
            </div>
            @endif

            {{-- 3. AI Audit Insight (Gap) --}}
            @if($insightText)
            <div style="margin-bottom: 8px; padding: 6px 10px; background: #fefce8; border-left: 3px solid #d97706; border-radius: 4px;">
                <div style="font-weight: bold; color: #b45309; font-size: 8px; text-transform: uppercase; margin-bottom: 2px;">
                    {{ __('AI AUDIT INSIGHT (GAP ANALYSIS)') }}
                </div>
                <div style="color: #78350f; font-size: 8.5px; whitespace-pre-line;">{{ $insightText }}</div>
            </div>
            @endif

            {{-- 4. Impact Interpretation --}}
            @if($impactText)
            <div style="padding: 6px 10px; background: #fff1f2; border-left: 3px solid #e11d48; border-radius: 4px;">
                <div style="font-weight: bold; color: #be123c; font-size: 8px; text-transform: uppercase; margin-bottom: 2px;">
                    {{ __('IMPACT INTERPRETATION') }}
                </div>
                <div style="color: #881337; font-size: 8.5px;">{{ $impactText }}</div>
            </div>
            @endif

            @if(!$recText && !$planText && !$insightText && !$impactText)
            <div style="color: #94a3b8; font-style: italic; text-align: center; padding: 6px;">
                {{ __('Tingkatkan dokumentasi proses, lakukan sosialisasi, dan verifikasi penerapan kontrol secara berkala.') }}
            </div>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align: center; color: #94a3b8; font-style: italic; padding: 20px; border: 1px dashed #cbd5e1; border-radius: 6px;">
        {{ __('No gap controls requiring AI recommendations.') }}
    </div>
    @endforelse

    <!-- FOOTER -->
    <div class="footer">
        AuditGuard &copy; {{ date('Y') }} | ISO 27001:2022 Assessment Report
    </div>
</body>
</html>
