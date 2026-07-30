<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Corporate ISMS Audit Result Report - {{ $session->name }}</title>
    <style>
        @page {
            margin: 25px 30px 35px 30px;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.45;
            background-color: #ffffff;
        }

        .page-break {
            page-break-before: always;
        }

        /* -----------------------------------------------------------------
           HEADER COVER BANNER
        ----------------------------------------------------------------- */
        .cover-header {
            background-color: #0b132b;
            color: #ffffff;
            padding: 18px 22px;
            border-radius: 8px;
            border-top: 4px solid #2563eb;
            margin-bottom: 16px;
        }
        .cover-badge {
            display: inline-block;
            background-color: rgba(37, 99, 235, 0.25);
            color: #60a5fa;
            border: 1px solid rgba(96, 165, 250, 0.35);
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
        }
        .cover-title {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            color: #ffffff;
            line-height: 1.25;
        }
        .cover-subtitle {
            font-size: 8.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 4px;
            font-weight: 600;
        }

        /* -----------------------------------------------------------------
           METADATA & KPI CARDS
        ----------------------------------------------------------------- */
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .meta-grid td {
            vertical-align: top;
        }
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
        }
        .meta-label {
            font-size: 7.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .meta-value {
            font-size: 10px;
            font-weight: 800;
            color: #0f172a;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }
        .kpi-score {
            font-size: 20px;
            font-weight: 900;
            color: #2563eb;
            line-height: 1;
        }
        .kpi-sub {
            font-size: 7.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .kpi-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* -----------------------------------------------------------------
           SECTION TITLES
        ----------------------------------------------------------------- */
        .section-header {
            font-size: 10.5px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding-bottom: 3px;
            border-bottom: 2px solid #2563eb;
            margin-top: 14px;
            margin-bottom: 8px;
        }

        /* -----------------------------------------------------------------
           AI EXECUTIVE SYNTHESIS
        ----------------------------------------------------------------- */
        .ai-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #2563eb;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 14px;
        }
        .ai-header-bar {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 5px 10px;
        }
        .ai-body {
            padding: 10px 12px;
        }
        .ai-section-title {
            font-size: 8px;
            font-weight: 900;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
            margin-bottom: 2px;
        }
        .ai-section-content {
            font-size: 9px;
            color: #334155;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        /* -----------------------------------------------------------------
           TABLES
        ----------------------------------------------------------------- */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 14px;
        }
        .report-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 6px 7px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        .report-table td {
            padding: 5px 7px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
            vertical-align: middle;
        }
        .report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* -----------------------------------------------------------------
           BADGES & PILLS
        ----------------------------------------------------------------- */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }
        .badge-compliant { background-color: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-partial { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-noncompliant { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-excluded { background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        .score-pill {
            display: inline-block;
            font-weight: 900;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 8px;
        }
        .score-high { background-color: #d1fae5; color: #047857; }
        .score-mid { background-color: #fef3c7; color: #b45309; }
        .score-low { background-color: #fee2e2; color: #b91c1c; }
        .score-none { background-color: #f1f5f9; color: #94a3b8; }

        .code-badge {
            background-color: #eff6ff;
            color: #1d4ed8;
            font-weight: 900;
            font-size: 8px;
            padding: 1.5px 5px;
            border-radius: 3px;
            border: 1px solid #bfdbfe;
        }

        .footer {
            margin-top: 18px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
            font-weight: 600;
        }
    </style>
</head>
<body>

    {{-- Page 1 Cover Header Banner --}}
    <div class="cover-header">
        <span class="cover-badge">ISO/IEC 27001:2022 Official Corporate Audit Document</span>
        <h1 class="cover-title">Executive Audit Result & Strategic Assessment Report</h1>
        <div class="cover-subtitle">Information Security Management System (ISMS) Maturity & Governance Review</div>
    </div>

    {{-- Metadata & KPI Section --}}
    <table class="meta-grid">
        <tr>
            <td width="67%" style="padding-right: 10px;">
                <div class="meta-card">
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td width="50%" style="padding-bottom: 6px;">
                                <div class="meta-label">Audit Session Name</div>
                                <div class="meta-value">{{ $session->name }}</div>
                            </td>
                            <td width="50%" style="padding-bottom: 6px;">
                                <div class="meta-label">Client Organization</div>
                                <div class="meta-value">{{ $session->organization->name ?? 'Standard Organization' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <div class="meta-label">Lead Auditor / Owner</div>
                                <div class="meta-value">{{ $session->user->name ?? 'System Lead Auditor' }}</div>
                            </td>
                            <td width="50%">
                                <div class="meta-label">Report Date & Audit Status</div>
                                <div class="meta-value">{{ $date }} &bull; <span style="color: #059669;">{{ strtoupper($session->status) }}</span></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td width="33%">
                <div class="kpi-card">
                    <div class="kpi-score">{{ number_format($overallMaturity, 2) }}<span style="font-size: 10px; color: #94a3b8;">/5.00</span></div>
                    <div class="kpi-sub">Overall Maturity Score</div>
                    <div class="kpi-badge {{ $complianceScore >= 80 ? 'badge-compliant' : ($complianceScore >= 50 ? 'badge-partial' : 'badge-noncompliant') }}">
                        {{ $complianceScore }}% Compliant &bull; {{ $maturityLevelLabel }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- AI Executive Synthesis Section --}}
    @if($parsedAiSummary)
    <div class="section-header">Executive AI Compliance Synthesis</div>
    <div class="ai-card">
        <div class="ai-header-bar">Unified Strategic Executive Summary</div>
        <div class="ai-body">
            @if(!empty($parsedAiSummary['overall_assessment_summary'] ?? $parsedAiSummary['overall_assessment_conclusion'] ?? null))
            <div class="ai-section-title">1. Overall Assessment Summary</div>
            <div class="ai-section-content">{!! e($parsedAiSummary['overall_assessment_summary'] ?? $parsedAiSummary['overall_assessment_conclusion']) !!}</div>
            @endif

            @if(!empty($parsedAiSummary['control_insight'] ?? $parsedAiSummary['overall_risk_areas'] ?? null))
            <div class="ai-section-title">2. Key Control Insights & Observations</div>
            <div class="ai-section-content">{!! e(is_array($parsedAiSummary['control_insight']) ? implode(', ', $parsedAiSummary['control_insight']) : $parsedAiSummary['control_insight']) !!}</div>
            @endif

            @if(!empty($parsedAiSummary['impact_interpretation'] ?? null))
            <div class="ai-section-title">3. Impact Interpretation & Risk Exposure</div>
            <div class="ai-section-content">{!! e($parsedAiSummary['impact_interpretation']) !!}</div>
            @endif
        </div>
    </div>
    @endif

    {{-- Domain Maturity Breakdown Table --}}
    <div class="section-header">Domain Maturity & Compliance Breakdown</div>
    <table class="report-table">
        <thead>
            <tr>
                <th width="32%">Domain / Category</th>
                <th width="11%" style="text-align: center;">Total Controls</th>
                <th width="11%" style="text-align: center;">Applicable</th>
                <th width="11%" style="text-align: center;">Excluded</th>
                <th width="15%" style="text-align: center;">Avg Score</th>
                <th width="10%" style="text-align: center;">Compliant</th>
                <th width="10%" style="text-align: center;">Gaps</th>
            </tr>
        </thead>
        <tbody>
            @foreach($domainStats as $dName => $stat)
            @php
                $avg = $stat['avg_maturity'];
                $scoreClass = $avg >= 4.0 ? 'score-high' : ($avg >= 2.5 ? 'score-mid' : 'score-low');
            @endphp
            <tr>
                <td><strong>{{ $dName }}</strong></td>
                <td style="text-align: center;">{{ $stat['total'] }}</td>
                <td style="text-align: center;">{{ $stat['applicable'] }}</td>
                <td style="text-align: center;">{{ $stat['excluded'] }}</td>
                <td style="text-align: center;">
                    <span class="score-pill {{ $scoreClass }}">{{ number_format($avg, 2) }} / 5.00</span>
                </td>
                <td style="text-align: center; color: #047857; font-weight: bold;">{{ $stat['compliant_count'] }}</td>
                <td style="text-align: center; color: #b91c1c; font-weight: bold;">{{ $stat['gap_count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Page Break Before Detailed Audit Control Evaluation Table --}}
    <div class="page-break"></div>

    {{-- Comprehensive Audit Control Evaluation Detail Table --}}
    <div class="section-header">Comprehensive Audit Control Evaluation Detail</div>
    <table class="report-table">
        <thead>
            <tr>
                <th width="10%">Code</th>
                <th width="26%">Control Title</th>
                <th width="10%" style="text-align: center;">Applicable</th>
                <th width="12%" style="text-align: center;">Maturity Score</th>
                <th width="14%" style="text-align: center;">Status</th>
                <th width="28%">Audit Findings & Justification Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $res)
            @php
                $hasQuestions = is_array($res->standard->questions) && count($res->standard->questions) > 0;
                $rating = $res->maturity_rating;
                $isApp = $res->is_applicable;
                $badgeClass = 'badge-excluded';
                $statusText = 'Excluded';
                $scoreClass = 'score-none';

                if ($isApp) {
                    if ($rating >= 4) {
                        $badgeClass = 'badge-compliant';
                        $statusText = 'Compliant';
                        $scoreClass = 'score-high';
                    } elseif ($rating >= 2) {
                        $badgeClass = 'badge-partial';
                        $statusText = 'Partially Compliant';
                        $scoreClass = 'score-mid';
                    } else {
                        $badgeClass = 'badge-noncompliant';
                        $statusText = 'Non-Compliant';
                        $scoreClass = 'score-low';
                    }
                }
            @endphp

            @if(!$hasQuestions)
            <tr style="background-color: #0f172a; color: #ffffff;">
                <td colspan="6" style="padding: 7px 10px; font-weight: 900; text-transform: uppercase; font-size: 8.5px; letter-spacing: 0.6px; color: #ffffff; background-color: #0f172a;">
                    <span style="background-color: #2563eb; color: #ffffff; padding: 2px 6px; border-radius: 3px; margin-right: 6px; font-size: 8px;">{{ $res->standard->code }}</span>
                    {{ $res->standard->title }}
                </td>
            </tr>
            @else
            <tr>
                <td><span class="code-badge">{{ $res->standard->code }}</span></td>
                <td><strong>{{ $res->standard->title }}</strong></td>
                <td style="text-align: center;">
                    <span class="badge {{ $isApp ? 'badge-compliant' : 'badge-excluded' }}">
                        {{ $isApp ? 'YES' : 'NO (N/A)' }}
                    </span>
                </td>
                <td style="text-align: center;">
                    @if($isApp && $rating !== null)
                        <span class="score-pill {{ $scoreClass }}">{{ number_format($rating, 1) }} / 5.0</span>
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                </td>
                <td style="font-size: 8px; color: #475569;">
                    @if(!$isApp)
                        <em>Justification: {{ $res->soa_justification ?: 'Excluded per Statement of Applicability.' }}</em>
                    @else
                        {{ $res->notes ?: 'Control evaluated during assessment session.' }}
                    @endif
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated by ISO 27001:2022 ISMS Assessment Intelligence Platform &bull; Confidential Executive Document &bull; {{ $date }}
    </div>

</body>
</html>
