<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ISO 27001 Compliance Dashboard Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; font-size: 11px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .header h1 { color: #1e293b; margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { color: #64748b; margin: 5px 0; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #1e293b; border-left: 4px solid #2563eb; padding-left: 8px; margin: 25px 0 12px; letter-spacing: 1px; }
        
        /* KPI Cards Table */
        .kpi-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: center; width: 33%; }
        .kpi-title { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 5px; }
        .kpi-val { font-size: 18px; font-weight: 900; color: #1e293b; }
        
        /* Standard Data Tables */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th { background: #f1f5f9; color: #475569; font-size: 9px; text-transform: uppercase; text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; }
        table.data-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
        
        /* Bar Progress Indicator in PDF */
        .progress-container { width: 100px; background-color: #e2e8f0; border-radius: 4px; height: 8px; overflow: hidden; display: inline-block; vertical-align: middle; margin-left: 8px; }
        .progress-bar { height: 100%; border-radius: 4px; }
        .progress-blue { background-color: #3b82f6; }
        .progress-green { background-color: #10b981; }
        .progress-yellow { background-color: #f59e0b; }
        .progress-red { background-color: #ef4444; }

        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
        
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/jpeg;base64,' . $logoData;
        }
    @endphp
    <div style="margin-bottom: 25px; border-bottom: 2px solid #008B9B; padding-bottom: 12px;">
        <table style="width: 100%; border: none; margin-bottom: 0;">
            <tr>
                <td style="width: 50px; border: none; padding: 0; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 45px; width: 45px; border-radius: 8px;">
                    @endif
                </td>
                <td style="border: none; padding: 0 0 0 10px; vertical-align: middle; text-align: left;">
                    <div style="font-size: 20px; font-weight: bold; line-height: 1.1;">
                        <span style="color: #0B2545;">Audit</span><span style="color: #008B9B;">Guard</span>
                    </div>
                    <div style="font-size: 7px; font-weight: 900; color: #64748b; letter-spacing: 2px; margin-top: 2px; text-transform: uppercase;">
                        ASSESS &bull; ANALYZE &bull; ASSURE
                    </div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle; color: #475569;">
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">ISO 27001 Compliance Audit Report</div>
                    <div style="font-size: 8px; margin-top: 2px; color: #64748b;">
                        Global System Aggregates & Analytics | Generated: {{ $date }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Core Performance Metrics</div>
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="padding-right: 10px;">
                <div class="kpi-title">Total Audit Sessions</div>
                <div class="kpi-val">{{ $totalSessions }}</div>
            </td>
            <td class="kpi-card" style="padding-left: 10px; padding-right: 10px;">
                <div class="kpi-title">Completed Sessions</div>
                <div class="kpi-val">{{ $completedSessions }}</div>
            </td>
            <td class="kpi-card" style="padding-left: 10px;">
                <div class="kpi-title">Average Maturity Score</div>
                <div class="kpi-val">{{ number_format($averageScore, 2) }} <span style="font-size: 10px; font-weight: normal; color: #94a3b8;">/ 5.00</span></div>
            </td>
        </tr>
    </table>

    <div class="section-title">Performance by Business Sector</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50%;">Sector</th>
                <th style="width: 25%; text-align: center;">Sessions Count</th>
                <th style="width: 25%; text-align: right;">Average Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sectorPerformance as $item)
            <tr>
                <td style="font-weight: bold;">{{ $item->business_sector }}</td>
                <td style="text-align: center;">{{ $item->sessions_count }}</td>
                <td style="text-align: right;">
                    <span style="font-weight: bold; margin-right: 10px;">{{ number_format($item->avg_score, 2) }}</span>
                    <div class="progress-container">
                        <div class="progress-bar progress-blue" style="width: {{ ($item->avg_score / 5) * 100 }}%"></div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic;">No industry sector data available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">Compliance Rates by ISO Clause</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">ISO 27001 Clause</th>
                <th style="width: 30%; text-align: right;">Average Maturity Rating</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clauseStats as $stat)
            @php
                $barColor = match(true) {
                    $stat['avg_rating'] >= 4 => 'progress-green',
                    $stat['avg_rating'] >= 2 => 'progress-yellow',
                    default => 'progress-red',
                };
            @endphp
            <tr>
                <td style="font-weight: bold;">Clause {{ $stat['code'] }}: {{ $stat['title'] }}</td>
                <td style="text-align: right;">
                    <span style="font-weight: bold; margin-right: 10px;">{{ number_format($stat['avg_rating'], 2) }} / 5.00</span>
                    <div class="progress-container">
                        <div class="progress-bar {{ $barColor }}" style="width: {{ ($stat['avg_rating'] / 5) * 100 }}%"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Top 5 Critical / Failing Controls</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Code</th>
                <th style="width: 45%;">Control Name</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 12%; text-align: center;">Occurrences</th>
                <th style="width: 13%; text-align: right;">Avg Rating</th>
            </tr>
        </thead>
        <tbody>
            @forelse($failingControls as $ctrl)
            <tr>
                <td style="font-weight: bold; color: #ef4444;">{{ $ctrl->code }}</td>
                <td style="font-weight: bold;">{{ $ctrl->title }}</td>
                <td style="text-transform: uppercase; font-size: 8px; color: #94a3b8;">{{ $ctrl->type }}</td>
                <td style="text-align: center;">{{ $ctrl->occurrences }} times</td>
                <td style="text-align: right;">
                    <span class="badge badge-danger">
                        {{ number_format($ctrl->avg_rating, 2) }} / 5.00
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8; font-style: italic;">No failing controls found (all controls have met compliance!).</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        AuditGuard &copy; {{ date('Y') }} | ISO 27001 Compliance Dashboard Report
    </div>
</body>
</html>
