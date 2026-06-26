<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement of Applicability - {{ $session->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1f2937; margin: 0; padding: 0; }
        .page { padding: 22px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; letter-spacing: 0.12em; text-transform: uppercase; }
        .header p { margin: 3px 0 0; font-size: 11px; color: #475569; }
        .meta { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 12px; font-size: 10px; color: #475569; }
        .meta div { background: #f8fafc; padding: 8px 10px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .section-title { margin: 12px 0 8px; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #0f172a; letter-spacing: 0.08em; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 8px; vertical-align: top; }
        th { background: #f1f5f9; color: #334155; text-align: left; font-weight: 700; }
        td { color: #334155; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 999px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
        .badge-yes { background: #e0f2fe; color: #035388; }
        .badge-no { background: #fee2e2; color: #991b1b; }
        .status { font-weight: 700; }
        .footer { position: fixed; bottom: 18px; left: 18px; right: 18px; text-align: center; font-size: 8px; color: #64748b; }
        .table-wrap { }
        .parent-title { font-size: 11px; font-weight: bold; color: #0f172a; margin: 18px 0 8px; text-transform: uppercase; letter-spacing: 0.08em; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>{{ __('Statement of Applicability') }}</h1>
            <p>{{ __('ISO/IEC 27001:2022 SoA for Audit Session') }}</p>
        </div>

        <div class="meta">
            <div>
                <strong>Session:</strong><br>{{ $session->name }}
            </div>
            <div>
                <strong>Generated:</strong><br>{{ $date }}
            </div>
            <div>
                <strong>Prepared by:</strong><br>{{ auth()->user()->name }}
            </div>
        </div>

        @if($clausaResults->isNotEmpty())
            <div class="section-title">{{ __('Clausa Controls') }}</div>
            @php
                $groupedClausa = $clausaResults->groupBy(fn($result) => $result->standard->parent ? $result->standard->parent->code . ' - ' . $result->standard->parent->title : 'No Parent');
            @endphp
            @foreach($groupedClausa as $parentLabel => $group)
                <div class="parent-title">{{ $parentLabel }}</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 8%;">{{ __('Code') }}</th>
                                <th style="width: 40%;">{{ __('Control') }}</th>
                                <th style="width: 14%;">{{ __('Applicable') }}</th>
                                <th style="width: 22%;">{{ __('Justification') }}</th>
                                <th style="width: 8%;">{{ __('Maturity') }}</th>
                                <th style="width: 8%;">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group as $result)
                                <tr>
                                    <td>{{ $result->standard->code }}</td>
                                    <td>{{ $result->standard->title }}</td>
                                    <td>
                                        @if($result->is_applicable)
                                            <span class="badge badge-yes">{{ __('Yes') }}</span>
                                        @else
                                            <span class="badge badge-no">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $result->soa_justification ?: '-' }}</td>
                                    <td>{{ $result->maturity_rating }}</td>
                                    @php $statusText = $result->is_applicable ? 'Implemented' : 'Excluded'; @endphp
                                    <td class="status">{{ __($statusText) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        @if($annexResults->isNotEmpty())
            <div class="section-title">{{ __('Annex Controls') }}</div>
            @php
                $groupedAnnex = $annexResults->groupBy(fn($result) => $result->standard->parent ? $result->standard->parent->code . ' - ' . $result->standard->parent->title : 'No Parent');
            @endphp
            @foreach($groupedAnnex as $parentLabel => $group)
                <div class="parent-title">{{ $parentLabel }}</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 8%;">{{ __('Code') }}</th>
                                <th style="width: 40%;">{{ __('Control') }}</th>
                                <th style="width: 14%;">{{ __('Applicable') }}</th>
                                <th style="width: 22%;">{{ __('Justification') }}</th>
                                <th style="width: 8%;">{{ __('Maturity') }}</th>
                                <th style="width: 8%;">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group as $result)
                                <tr>
                                    <td>{{ $result->standard->code }}</td>
                                    <td>{{ $result->standard->title }}</td>
                                    <td>
                                        @if($result->is_applicable)
                                            <span class="badge badge-yes">{{ __('Yes') }}</span>
                                        @else
                                            <span class="badge badge-no">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $result->soa_justification ?: '-' }}</td>
                                    <td>{{ $result->maturity_rating }}</td>
                                    @php $statusText = $result->is_applicable ? 'Implemented' : 'Excluded'; @endphp
                                    <td class="status">{{ __($statusText) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        @if($clausaResults->isEmpty() && $annexResults->isEmpty())
            <div class="section-title">Control Applicability & Justification</div>
            @php
                $groupedFallback = $session->results->sortBy(fn($result) => ($result->standard->parent?->code ?? '') . '|' . ($result->standard->code ?? ''), SORT_NATURAL | SORT_FLAG_CASE)
                    ->groupBy(fn($result) => $result->standard->parent ? $result->standard->parent->code . ' - ' . $result->standard->parent->title : 'No Parent');
            @endphp
            @foreach($groupedFallback as $parentLabel => $group)
                <div class="parent-title">{{ $parentLabel }}</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 8%;">{{ __('Code') }}</th>
                                <th style="width: 40%;">{{ __('Control') }}</th>
                                <th style="width: 14%;">{{ __('Applicable') }}</th>
                                <th style="width: 22%;">{{ __('Justification') }}</th>
                                <th style="width: 8%;">{{ __('Maturity') }}</th>
                                <th style="width: 8%;">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group as $result)
                                <tr>
                                    <td>{{ $result->standard->code }}</td>
                                    <td>{{ $result->standard->title }}</td>
                                    <td>
                                        @if($result->is_applicable)
                                            <span class="badge badge-yes">{{ __('Yes') }}</span>
                                        @else
                                            <span class="badge badge-no">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $result->soa_justification ?: '-' }}</td>
                                    <td>{{ $result->maturity_rating }}</td>
                                    @php $statusText = $result->is_applicable ? 'Implemented' : 'Excluded'; @endphp
                                    <td class="status">{{ __($statusText) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        <div class="footer">ISO 27001:2022 Statement of Applicability | Generated by Audit ISO27001:2022</div>
    </div>
</body>
</html>
