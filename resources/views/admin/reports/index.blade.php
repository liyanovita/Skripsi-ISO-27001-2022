@extends('layouts.admin')

@section('title', 'Compliance Reports & Analytics')
@section('header_title', 'Compliance Reports')

@section('content')
{{-- Print Custom Styles --}}
<style>
    @media print {
        body {
            background: white !important;
            color: black !important;
        }
        .no-print {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        .print-container {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .page-break {
            page-break-before: always;
        }
        .print-chart-box {
            break-inside: avoid;
            page-break-inside: avoid;
            border: 1px solid #e2e8f0 !important;
            margin-bottom: 20px !important;
        }
    }
</style>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6 pb-12">

    {{-- Page Header & Actions Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-black text-xs rounded-lg border border-blue-100 uppercase tracking-wider">
                    ISO 27001:2022 Reports
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Compliance Reports & Visual Analytics') }}</h1>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">{{ __('Executive visual charts, compliance status, risk distribution, clause compliance levels, and summary insights') }}</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.reports.export_pdf') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                <i class="fa-solid fa-file-pdf text-xs"></i> {{ __('PDF Report') }}
            </a>

            <a href="{{ route('admin.reports.export_csv') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                <i class="fa-solid fa-file-excel text-xs"></i> {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    {{-- Print Only Header --}}
    <div class="hidden print-only mb-8 border-b-2 border-slate-800 pb-4 text-center">
        <h1 class="text-2xl font-black text-slate-900 uppercase">ISO 27001:2022 Compliance Audit Report</h1>
        <p class="text-sm text-slate-500 mt-1">Generated on {{ date('d F Y H:i') }} | Executive Analytics</p>
    </div>

    <div class="print-container space-y-6">

        {{-- Executive Audit Summary & Key Insights Banner --}}
        <div class="p-6 bg-gradient-to-r from-blue-50/90 via-indigo-50/70 to-slate-50 border border-blue-100 rounded-3xl shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-blue-100 pb-3 flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 uppercase tracking-wide">{{ __('Executive Audit Summary & Key Insights') }}</h2>
                        <p class="text-xs text-slate-500 font-medium">{{ __('Concise summary of overall ISO 27001:2022 compliance rating, gap distribution, and weakest control areas') }}</p>
                    </div>
                </div>

                @if($selectedSession)
                <span class="px-3 py-1 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 shadow-xs">
                    <i class="fa-solid fa-user-tie text-blue-600 mr-1"></i> {{ $selectedSession->name }} ({{ $selectedSession->user->name }})
                </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                {{-- Overall Score Gauge & Metric --}}
                <div class="md:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block">{{ __('Global Compliance Index') }}</span>
                    <div class="text-3xl font-black text-blue-600">
                        {{ $executiveSummary['overall_compliance_percentage'] }}%
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $executiveSummary['level_name'] }}
                    </span>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden shadow-inner mt-2">
                        <div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500 h-full rounded-full transition-all duration-700" style="width: {{ $executiveSummary['overall_compliance_percentage'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-semibold pt-1">Average Rating: <strong class="text-slate-800">{{ $executiveSummary['average_score'] }} / 5.00</strong></p>
                </div>

                {{-- Single High-Level Overall Executive Conclusion in English --}}
                <div class="md:col-span-8 bg-white/90 p-6 rounded-2xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-lg shrink-0 font-bold mt-0.5">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <div class="space-y-1.5 flex-1">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">{{ __('Executive Audit Conclusion') }}</h3>
                        <p class="text-xs text-slate-700 font-medium leading-relaxed">
                            Overall ISO 27001:2022 compliance stands at <strong class="text-blue-700 font-bold">{{ $executiveSummary['overall_compliance_percentage'] }}%</strong> (overall average score <strong class="text-blue-700 font-bold">{{ $executiveSummary['average_score'] }} / 5.00</strong>) with <strong class="text-amber-700 font-bold">{{ $executiveSummary['total_gaps'] }} identified control gaps</strong>. Primary remediation focus is required for <strong class="text-indigo-900 font-bold">{{ $executiveSummary['weakest_domain'] }}</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top 3 KPI Executive Stat Cards (Grid 3 Kolom Proposional) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100/80 flex items-center justify-center text-base shrink-0 font-bold">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('Completed Sessions') }}</div>
                    <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ number_format($completedSessions) }}</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100/80 flex items-center justify-center text-base shrink-0 font-bold">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('Average Rating') }}</div>
                    <div class="text-2xl font-black text-indigo-600 mt-0.5">{{ number_format($averageScore, 2) }} <span class="text-xs text-slate-400 font-normal">/ 5.00</span></div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 border border-rose-100/80 flex items-center justify-center text-base shrink-0 font-bold">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('High Risk Controls') }}</div>
                    <div class="text-2xl font-black text-rose-600 mt-0.5">{{ number_format($highRiskCount) }}</div>
                </div>
            </div>
        </div>

        {{-- SECTION 1: Executive Compliance & Risk Overview (Grid 2 Kolom Proposional) --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-400">
                <i class="fa-solid fa-shield-halved text-blue-600"></i> Section 1: Executive Compliance & Risk Status
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Chart 1: Overall Compliance Status Chart (Doughnut Chart) --}}
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between min-h-[380px]">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i> {{ __('Overall ISO 27001:2022 Compliance Status') }}
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Control Status</span>
                        </div>
                        <div class="h-56 relative flex items-center justify-center">
                            <canvas id="complianceStatusChart"></canvas>
                        </div>
                    </div>
                    {{-- Chart 1 Summary Takeaway --}}
                    <div class="mt-4 p-3.5 bg-emerald-50/70 border border-emerald-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                        <div>
                            <strong class="font-bold text-emerald-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Compliance Status Summary') }}:</strong>
                            <span>Contains <strong class="text-emerald-700 font-bold">{{ $compliantCount }} Compliant controls</strong> (Level 4-5), <strong class="text-amber-700 font-bold">{{ $needsImprovementCount }} Partially Compliant controls</strong> (Level 2-3), and <strong class="text-rose-700 font-bold">{{ $nonCompliantCount }} Non-Compliant controls</strong> (Level 0-1).</span>
                        </div>
                    </div>
                </div>

                {{-- Chart 2: Risk Priority Distribution (Doughnut Chart) --}}
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between min-h-[380px]">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fa-solid fa-pie-chart text-amber-500"></i> {{ __('Risk Priority Distribution Across Gaps') }}
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $totalGaps }} Total Gaps (Below Level 5)</span>
                        </div>
                        <div class="h-56 relative flex items-center justify-center">
                            <canvas id="riskDistributionChart"></canvas>
                        </div>
                    </div>
                    {{-- Chart 2 Summary Takeaway --}}
                    <div class="mt-4 p-3.5 bg-rose-50/70 border border-rose-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 mt-0.5 shrink-0 text-sm"></i>
                        <div>
                            <strong class="font-bold text-rose-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Risk Priority Summary') }}:</strong>
                            <span>From <strong class="text-slate-800 font-black">{{ $totalGaps }} total gaps</strong> (controls below target Level 5): <strong class="text-rose-700 font-black">{{ $highRiskCount }} High Risk</strong> (Level 0-2), <strong class="text-amber-700 font-black">{{ $mediumRiskCount }} Medium Risk</strong> (Level 3), <strong class="text-emerald-700 font-black">{{ $lowRiskCount }} Low Risk</strong> (Level 4). Remediation must prioritize High Risk items first.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: ISO Standard & Security Domain Performance (Unified Bar Chart) --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-400">
                <i class="fa-solid fa-list-check text-indigo-600"></i> Section 2: ISO 27001:2022 Domains Performance (Clauses 4-10 & Annex A.5-A.8)
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between min-h-[380px]">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-chart-bar text-indigo-600"></i> {{ __('ISO 27001:2022 Security & Management Domains Breakdown') }}
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Maturity Rating (0 - 5.00)</span>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="domainsChart"></canvas>
                    </div>
                </div>
                {{-- Domain Summary Takeaway --}}
                <div class="mt-4 p-3.5 bg-indigo-50/70 border border-indigo-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                    <i class="fa-solid fa-lightbulb text-indigo-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong class="font-bold text-indigo-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Domain Performance Insight') }}:</strong>
                        <span>Performance evaluation across all 11 ISO 27001:2022 domains maintains an overall average score of <strong class="text-blue-700 font-bold">{{ $executiveSummary['average_score'] }} / 5.00</strong>, with priority remediation focused on <strong class="text-indigo-900 font-bold">{{ $executiveSummary['weakest_domain'] }}</strong>.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: Sector Benchmarking & Top Failing Controls (Grid 2 Kolom Proposional) --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-400">
                <i class="fa-solid fa-industry text-emerald-600"></i> Section 3: Sector Benchmarking & Critical Controls Breakdown
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                {{-- Left: Sector Performance Comparison (Horizontal Bar Chart) --}}
                <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between min-h-[380px]">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fa-solid fa-industry text-emerald-600"></i> {{ __('Performance by Business Sector') }}
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg Score</span>
                        </div>
                        <div class="h-56 relative">
                            <canvas id="sectorsChart"></canvas>
                        </div>
                    </div>
                    {{-- Sector Summary Takeaway --}}
                    <div class="mt-4 p-3.5 bg-emerald-50/70 border border-emerald-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                        <i class="fa-solid fa-chart-line text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                        <div>
                            <strong class="font-bold text-emerald-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Industry Sector Insight') }}:</strong>
                            <span>Comparative compliance benchmarking across organization business sectors for strategic industry analysis.</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Top 5 High Risk & High Gap Controls Table --}}
                <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden print-chart-box flex flex-col justify-between min-h-[380px]">
                    <div>
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm">{{ __('Top 5 High Risk & High Gap Controls') }}</h3>
                                    <p class="text-[11px] text-slate-400 font-medium">{{ __('Controls requiring priority remediation action plans') }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider">Priority List</span>
                        </div>

                        <div class="p-4">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                                        <tr>
                                            <th class="px-3 py-2.5">{{ __('Code') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Control Title') }}</th>
                                            <th class="px-3 py-2.5 text-center">{{ __('Gap') }}</th>
                                            <th class="px-3 py-2.5 text-center">{{ __('Risk') }}</th>
                                            <th class="px-3 py-2.5 text-right">{{ __('Score') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($failingControls as $ctrl)
                                        @php
                                            $rPriority = $ctrl->calculated_risk ?: 'High';
                                        @endphp
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="px-3 py-2.5 font-black text-rose-600">
                                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 font-bold text-[11px]">{{ $ctrl->code }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 font-bold text-slate-800 leading-snug max-w-[200px] truncate" title="{{ $ctrl->title }}">{{ $ctrl->title }}</td>
                                            <td class="px-3 py-2.5 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    {{ number_format($ctrl->avg_gap) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border
                                                    {{ $rPriority == 'High' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                                    {{ $rPriority == 'Medium' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                                    {{ $rPriority == 'Low' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                                ">
                                                    {{ $rPriority }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2.5 text-right">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-black bg-slate-100 text-slate-800 border border-slate-200">
                                                    {{ number_format($ctrl->avg_rating) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-slate-400 italic">
                                                {{ __('No failing controls found.') }}
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Table Summary Takeaway --}}
                    <div class="p-3.5 m-4 mt-0 bg-rose-50/70 border border-rose-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                        <i class="fa-solid fa-lightbulb text-rose-600 mt-0.5 shrink-0 text-sm"></i>
                        <div>
                            <strong class="font-bold text-rose-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('High Risk Controls Insight') }}:</strong>
                            <span>The top 5 controls listed above represent the largest compliance gaps and should be prioritized in CAPA remediation plans.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

    </div>
</div>

{{-- Render All Interactive Charts Script --}}
<script>
    function safeInitChart(id, config) {
        const canvas = typeof id === 'string' ? document.getElementById(id) : id;
        if (!canvas) return;
        if (typeof Chart !== 'undefined') {
            const existing = Chart.getChart(canvas);
            if (existing) {
                existing.destroy();
            }
            return new Chart(canvas, config);
        }
    }

    function initCharts() {
        // Chart 1: Compliance Status Chart (Doughnut Chart)
        safeInitChart('complianceStatusChart', {
            type: 'doughnut',
            data: {
                labels: ['Compliant', 'Partially Compliant', 'Non-Compliant'],
                datasets: [{
                    data: [{{ $compliantCount }}, {{ $needsImprovementCount }}, {{ $nonCompliantCount }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart 2: Risk Priority Distribution Doughnut Chart
        safeInitChart('riskDistributionChart', {
            type: 'doughnut',
            data: {
                labels: ['High (Level 0-2)', 'Medium (Level 3)', 'Low (Level 4)'],
                datasets: [{
                    data: [{{ $highRiskCount }}, {{ $mediumRiskCount }}, {{ $lowRiskCount }}],
                    backgroundColor: ['#f43f5e', '#f59e0b', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Unified Domains Bar Chart (Clauses 4-10 & Annex A.5-A.8)
        const domainData = @json($domainStats);
        safeInitChart('domainsChart', {
            type: 'bar',
            data: {
                labels: domainData.map(d => d.code),
                datasets: [{
                    label: 'Maturity Rating (0-5)',
                    data: domainData.map(d => d.avg_rating),
                    backgroundColor: domainData.map(d => d.code.startsWith('Clause') ? '#3b82f6' : '#6366f1'),
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 5 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const d = domainData[context.dataIndex];
                                return d.title + ': ' + context.raw + ' / 5.00';
                            }
                        }
                    }
                }
            }
        });

        // Chart 5: Sector Performance Horizontal Bar Chart
        const sectorData = @json($sectorPerformance);
        safeInitChart('sectorsChart', {
            type: 'bar',
            data: {
                labels: sectorData.map(s => s.business_sector),
                datasets: [{
                    label: 'Avg Maturity Rating',
                    data: sectorData.map(s => parseFloat(s.avg_score).toFixed(2)),
                    backgroundColor: '#10b981',
                    borderRadius: 8,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { beginAtZero: true, max: 5 }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", initCharts);
    } else {
        initCharts();
    }
    document.addEventListener("turbo:load", initCharts);
</script>
@endsection
