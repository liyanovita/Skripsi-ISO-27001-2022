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

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-black text-xs rounded-lg border border-blue-100 uppercase tracking-wider">
                    ISO 27001:2022 Analytics
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Compliance Reports & Visual Analytics') }}</h1>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">{{ __('Executive visual charts, compliance status, risk distribution, clause compliance levels, and summary insights') }}</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            {{-- Session Filter Form --}}
            <form method="GET" action="{{ route('admin.reports.index') }}" x-data class="inline-block">
                <select name="session_id" x-on:change="$el.closest('form').submit()" 
                        class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 max-w-xs cursor-pointer shadow-xs">
                    <option value="">All Completed Audit Sessions</option>
                    @foreach($sessions as $sess)
                        <option value="{{ $sess->id }}" {{ request('session_id') == $sess->id ? 'selected' : '' }}>
                            {{ $sess->name }} ({{ $sess->user->name }})
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.reports.export_pdf', ['session_id' => request('session_id')]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                <i class="fa-solid fa-file-pdf text-xs"></i> {{ __('PDF Report') }}
            </a>

            <a href="{{ route('admin.reports.export_csv', ['session_id' => request('session_id')]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                <i class="fa-solid fa-file-excel text-xs"></i> {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    <div class="print-container space-y-6">

        {{-- Executive Summary & Concise Audit Findings Card --}}
        <div class="p-6 bg-gradient-to-r from-blue-50/90 via-indigo-50/70 to-slate-50 border border-blue-100 rounded-3xl shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-blue-100 pb-3">
                <div class="w-9 h-9 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                    <i class="fa-solid fa-award"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900 uppercase tracking-wide">{{ __('Executive Audit Summary & Key Insights') }}</h2>
                    <p class="text-xs text-slate-500 font-medium">{{ __('Concise summary of overall ISO 27001 compliance rating, gap distribution, and weakest control areas') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                {{-- Overall Score Gauge / Metric --}}
                <div class="md:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2 text-center">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block">{{ __('Compliance Level') }}</span>
                    <div class="text-3xl font-black text-blue-600">
                        {{ $executiveSummary['overall_compliance_percentage'] }}%
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Level {{ $executiveSummary['level_number'] }}: {{ $executiveSummary['level_name'] }}
                    </span>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden shadow-inner mt-2">
                        <div class="bg-gradient-to-r from-blue-500 to-emerald-500 h-full rounded-full" style="width: {{ $executiveSummary['overall_compliance_percentage'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-semibold pt-1">Average Score: <strong class="text-slate-800">{{ $executiveSummary['average_score'] }} / 5.00</strong></p>
                </div>

                {{-- Key Summary Bullet Points --}}
                <div class="md:col-span-8 space-y-2 text-xs text-slate-700 font-medium leading-relaxed bg-white/70 p-5 rounded-2xl border border-slate-200/60">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 mt-1 shrink-0"></i>
                        <div>
                            <strong class="text-slate-900 font-bold">Overall System Maturity:</strong> 
                            The organization achieved an overall ISO 27001 maturity rating of <strong class="text-blue-700">{{ $executiveSummary['average_score'] }} / 5.00</strong> ({{ $executiveSummary['overall_compliance_percentage'] }}% compliance rate).
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-1 shrink-0"></i>
                        <div>
                            <strong class="text-slate-900 font-bold">Identified Control Gaps:</strong> 
                            A total of <strong class="text-amber-700">{{ $executiveSummary['total_gaps'] }} control gaps</strong> require remediation, including <strong class="text-rose-600">{{ $executiveSummary['high_risk_count'] }} High Risk controls</strong> requiring priority action plans.
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-bullseye text-indigo-600 mt-1 shrink-0"></i>
                        <div>
                            <strong class="text-slate-900 font-bold">Primary Improvement Priority:</strong> 
                            The weakest management clause is <strong class="text-indigo-900">{{ $executiveSummary['weakest_clause'] }}</strong> (Avg Rating: {{ $executiveSummary['weakest_clause_rating'] }}/5), and the weakest Annex A domain is <strong class="text-indigo-900">{{ $executiveSummary['weakest_annex'] }}</strong> (Avg Rating: {{ $executiveSummary['weakest_annex_rating'] }}/5).
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 Executive KPI Stats Cards Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 flex items-center justify-center text-base shrink-0 font-bold">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('Total Sessions') }}</div>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalSessions) }}</div>
                </div>
            </div>

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
                    <div class="text-2xl font-black text-indigo-600 mt-0.5">{{ number_format($averageScore, 2) }} <span class="text-xs text-slate-400 font-normal">/ 5</span></div>
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

        {{-- Interactive Visual Charts Suite --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Chart 1: Overall Compliance Status Chart (Doughnut Chart) --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-[#10b981] fa-circle-check text-emerald-600"></i> {{ __('Overall ISO 27001 Compliance Status') }}
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Control Status</span>
                    </div>
                    <div class="h-60 relative flex items-center justify-center">
                        <canvas id="complianceStatusChart"></canvas>
                    </div>
                </div>
                {{-- Chart 1 Summary Takeaway --}}
                <div class="mt-4 p-3.5 bg-emerald-50/70 border border-emerald-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong class="font-bold text-emerald-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Compliance Status Summary') }}:</strong>
                        <span>Contains <strong class="text-emerald-700 font-bold">{{ $compliantCount }} Compliant controls</strong> (Level 4-5), <strong class="text-amber-700 font-bold">{{ $needsImprovementCount }} Needs Improvement controls</strong> (Level 2-3), and <strong class="text-rose-700 font-bold">{{ $nonCompliantCount }} Non-Compliant controls</strong> (Level 0-1).</span>
                    </div>
                </div>
            </div>

            {{-- Chart 2: Risk Priority Distribution (Doughnut Chart) --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-pie-chart text-amber-500"></i> {{ __('Risk Priority Distribution Across Gaps') }}
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">High / Med / Low</span>
                    </div>
                    <div class="h-60 relative flex items-center justify-center">
                        <canvas id="riskDistributionChart"></canvas>
                    </div>
                </div>
                {{-- Chart 2 Summary Takeaway --}}
                <div class="mt-4 p-3.5 bg-rose-50/70 border border-rose-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong class="font-bold text-rose-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Risk Priority Summary') }}:</strong>
                        <span>Identified <strong class="text-rose-700 font-black">{{ $highRiskCount }} High Risk controls</strong> out of {{ $totalGaps }} total gaps. Immediate remediation action plans should prioritize high-risk items.</span>
                    </div>
                </div>
            </div>

            {{-- Chart 3: ISO Main Clauses (Bar Chart) --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-chart-bar text-blue-600"></i> {{ __('ISO Main Clauses (Clauses 4 - 10)') }}
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Maturity Rating (0-5)</span>
                    </div>
                    <div class="h-60 relative">
                        <canvas id="clausesChart"></canvas>
                    </div>
                </div>
                {{-- Chart 3 Summary Takeaway --}}
                <div class="mt-4 p-3.5 bg-blue-50/70 border border-blue-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                    <i class="fa-solid fa-lightbulb text-blue-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong class="font-bold text-blue-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Clause Insight / Summary') }}:</strong>
                        <span>Lowest scoring management clause requiring attention: <strong class="text-slate-900">{{ $executiveSummary['weakest_clause'] }}</strong> (Average score: <strong class="text-blue-700">{{ $executiveSummary['weakest_clause_rating'] }} / 5.00</strong>).</span>
                    </div>
                </div>
            </div>

            {{-- Chart 4: Annex A Control Domains (Bar Chart) --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-lock text-indigo-600"></i> {{ __('Annex A Security Control Domains') }}
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Domains A.5 - A.8</span>
                    </div>
                    <div class="h-60 relative">
                        <canvas id="annexChart"></canvas>
                    </div>
                </div>
                {{-- Chart 4 Summary Takeaway --}}
                <div class="mt-4 p-3.5 bg-indigo-50/70 border border-indigo-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                    <i class="fa-solid fa-shield-halved text-indigo-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong class="font-bold text-indigo-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Annex A Domain Insight / Summary') }}:</strong>
                        <span>Lowest performing security domain: <strong class="text-slate-900">{{ $executiveSummary['weakest_annex'] }}</strong> (Average score: <strong class="text-indigo-700">{{ $executiveSummary['weakest_annex_rating'] }} / 5.00</strong>).</span>
                    </div>
                </div>
            </div>

            {{-- Chart 5: Sector Performance Comparison (Horizontal Bar Chart) --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs print-chart-box lg:col-span-2 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-industry text-emerald-600"></i> {{ __('Performance by Business Sector') }}
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Average Rating</span>
                    </div>
                    <div class="h-60 relative">
                        <canvas id="sectorChart"></canvas>
                    </div>
                </div>
                {{-- Chart 5 Summary Takeaway --}}
                <div class="mt-4 p-3.5 bg-emerald-50/70 border border-emerald-100/80 rounded-2xl text-xs text-slate-700 flex items-start gap-2.5">
                    <i class="fa-solid fa-chart-line text-emerald-600 mt-0.5 shrink-0 text-sm"></i>
                    <div>
                        <strong class="font-bold text-emerald-950 block text-[10px] uppercase tracking-wider mb-0.5">{{ __('Industry Sector Insight') }}:</strong>
                        <span>Displays comparative compliance benchmarking across organization business sectors for strategic industry analysis.</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Top 5 High Risk / Failing Controls Table --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden page-break">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">{{ __('Top 5 High Risk & High Gap Controls') }}</h3>
                        <p class="text-xs text-slate-400 font-medium">{{ __('Controls with the largest compliance gaps requiring priority corrective action') }}</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">{{ __('Control Code') }}</th>
                                <th class="px-4 py-3">{{ __('Control Title') }}</th>
                                <th class="px-4 py-3">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Average Gap') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Risk Level') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Average Rating') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($failingControls as $ctrl)
                            @php
                                $rPriority = $ctrl->calculated_risk ?: 'High';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 font-black text-rose-600">
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 font-black text-xs">{{ $ctrl->code }}</span>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900 max-w-xs leading-snug">{{ $ctrl->title }}</td>
                                <td class="px-4 py-3 text-[10px] uppercase font-bold text-slate-500">{{ $ctrl->type }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Gap: {{ $ctrl->avg_gap }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border
                                        {{ $rPriority == 'High' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                        {{ $rPriority == 'Medium' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                        {{ $rPriority == 'Low' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                    ">
                                        {{ $rPriority }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ number_format($ctrl->avg_rating, 2) }} / 5.00
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400 italic">
                                    {{ __('No failing controls found (all evaluated controls met target maturity level!).') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Render All Interactive Charts Script --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        initCharts();
    });

    function initCharts() {
        // Chart 1: Compliance Status Chart (Doughnut Chart)
        const complianceCtx = document.getElementById('complianceStatusChart');
        if (complianceCtx) {
            new Chart(complianceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Compliant (Level 4-5)', 'Needs Improvement (Level 2-3)', 'Non-Compliant (Level 0-1)'],
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
        }

        // Chart 2: Risk Priority Distribution Doughnut Chart
        const riskCtx = document.getElementById('riskDistributionChart');
        if (riskCtx) {
            new Chart(riskCtx, {
                type: 'doughnut',
                data: {
                    labels: ['High Risk', 'Medium Risk', 'Low Risk'],
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
        }

        // Chart 3: Clauses Bar Chart
        const clausesCtx = document.getElementById('clausesChart');
        if (clausesCtx) {
            const clauseData = @json($clauseStats);
            new Chart(clausesCtx, {
                type: 'bar',
                data: {
                    labels: clauseData.map(c => 'Clause ' + c.code),
                    datasets: [{
                        label: 'Maturity Rating (0-5)',
                        data: clauseData.map(c => c.avg_rating),
                        backgroundColor: '#3b82f6',
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
                        legend: { display: false }
                    }
                }
            });
        }

        // Chart 4: Annex A Bar Chart
        const annexCtx = document.getElementById('annexChart');
        if (annexCtx) {
            const annexData = @json($annexStats);
            new Chart(annexCtx, {
                type: 'bar',
                data: {
                    labels: annexData.map(a => a.code + ' ' + a.title),
                    datasets: [{
                        label: 'Annex Rating (0-5)',
                        data: annexData.map(a => a.avg_rating),
                        backgroundColor: '#6366f1',
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
                        legend: { display: false }
                    }
                }
            });
        }

        // Chart 5: Sector Performance Horizontal Bar Chart
        const sectorCtx = document.getElementById('sectorChart');
        if (sectorCtx) {
            const sectorData = @json($sectorPerformance);
            new Chart(sectorCtx, {
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
    }
</script>
@endsection
