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
        /* Ensure charts print properly and do not break pages */
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

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
    <div>
        <h2 class="text-xl font-black text-slate-800">Compliance Reports & Aggregate Analytics</h2>
        <p class="text-sm text-slate-500">View maturity statistics, sector comparisons, and export audit results.</p>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('admin.reports.export_pdf') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
            <i class="fa-solid fa-file-pdf"></i> Download PDF Report
        </a>
        <a href="{{ route('admin.reports.export_csv') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
            <i class="fa-solid fa-file-csv"></i> Export Raw Data (CSV)
        </a>
    </div>
</div>

{{-- Header shown only when printing --}}
<div class="hidden print-only mb-8 border-b-2 border-slate-800 pb-4">
    <div class="text-center">
        <h1 class="text-2xl font-black text-slate-900 uppercase">ISO 27001 Compliance Audit Report</h1>
        <p class="text-sm text-slate-500 mt-1">Generated on {{ date('d F Y H:i') }} | Global System Aggregates</p>
    </div>
</div>

<div class="print-container">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-xl shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Audit Sessions</span>
                <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ $totalSessions }}</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center text-green-600 text-xl shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Completed Sessions</span>
                <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ $completedSessions }}</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Average Maturity Score</span>
                <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($averageScore, 2) }} <span class="text-xs text-slate-400 font-normal">/ 5.00</span></span>
            </div>
        </div>
    </div>

    {{-- Interactive Charts Panel --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm print-chart-box">
            <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-bar text-blue-600 no-print"></i> ISO Clauses Compliance Levels
            </h3>
            <div class="h-64 relative">
                <canvas id="clausesChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm print-chart-box">
            <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-industry text-indigo-600 no-print"></i> Sector Performance Comparison
            </h3>
            <div class="h-64 relative">
                <canvas id="sectorsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Main Statistics Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Section: Sector Performance --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-200 bg-slate-50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-industry text-blue-600"></i> Performance by Business Sector
                </h3>
            </div>
            <div class="p-5 flex-1">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="text-xs uppercase font-bold text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="pb-3">Sector</th>
                                <th class="pb-3 text-center">Sessions</th>
                                <th class="pb-3 text-right">Avg Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($sectorPerformance as $item)
                            <tr>
                                <td class="py-3 font-semibold text-slate-700">{{ $item->business_sector }}</td>
                                <td class="py-3 text-center text-slate-500">{{ $item->sessions_count }}</td>
                                <td class="py-3 text-right">
                                    <span class="font-black text-slate-800">{{ number_format($item->avg_score, 2) }}</span>
                                    <div class="w-24 bg-slate-100 h-1.5 rounded-full mt-1 ml-auto overflow-hidden">
                                        <div class="bg-blue-600 h-full rounded-full" style="width: {{ ($item->avg_score / 5) * 100 }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400 italic">No industry sector data available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Section: Compliance by ISO Clauses --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-200 bg-slate-50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-indigo-600"></i> Compliance Rates by ISO Clause
                </h3>
            </div>
            <div class="p-5 flex-1">
                <div class="space-y-4">
                    @foreach($clauseStats as $stat)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-bold text-slate-700 truncate max-w-[80%]" title="Clause {{ $stat['code'] }}: {{ $stat['title'] }}">
                                Clause {{ $stat['code'] }}: {{ $stat['title'] }}
                            </span>
                            <span class="font-bold text-slate-800">{{ number_format($stat['avg_rating'], 2) }} / 5.00</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-full rounded-full 
                                {{ $stat['avg_rating'] >= 4 ? 'bg-green-500' : '' }}
                                {{ $stat['avg_rating'] >= 2 && $stat['avg_rating'] < 4 ? 'bg-yellow-500' : '' }}
                                {{ $stat['avg_rating'] < 2 ? 'bg-red-500' : '' }}
                            " style="width: {{ ($stat['avg_rating'] / 5) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Top 5 Failing Controls --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden page-break">
        <div class="p-5 border-b border-slate-200 bg-slate-50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-600"></i> Top 5 Critical / Failing Controls
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Controls with the lowest average maturity scores across all completed audits.</p>
        </div>
        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="text-xs uppercase font-bold text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="pb-3">Code</th>
                            <th class="pb-3">Control Name</th>
                            <th class="pb-3">Type</th>
                            <th class="pb-3 text-center">Failing Audits</th>
                            <th class="pb-3 text-right">Average Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($failingControls as $ctrl)
                        <tr>
                            <td class="py-3 font-bold text-red-600">{{ $ctrl->code }}</td>
                            <td class="py-3 font-semibold text-slate-700">{{ $ctrl->title }}</td>
                            <td class="py-3 text-xs uppercase font-semibold text-slate-400">{{ $ctrl->type }}</td>
                            <td class="py-3 text-center text-slate-500">{{ $ctrl->occurrences }} times</td>
                            <td class="py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                    {{ number_format($ctrl->avg_rating, 2) }} / 5.00
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 italic">No failing controls found (all controls have met target compliance!).</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Render Charts script --}}
<script>
    document.addEventListener("turbo:load", function() {
        initCharts();
    });

    // Handle initial load before Turbo transitions
    if (document.readyState === "complete" || document.readyState === "interactive") {
        initCharts();
    }

    function initCharts() {
        const clausesCtx = document.getElementById('clausesChart');
        const sectorsCtx = document.getElementById('sectorsChart');

        if (!clausesCtx || !sectorsCtx) return;

        // Destroy previous instances if they exist to prevent redraw bugs
        if (window.myClausesChart) window.myClausesChart.destroy();
        if (window.mySectorsChart) window.mySectorsChart.destroy();

        // 1. Clauses Chart Data
        const clauseLabels = @js(collect($clauseStats)->map(fn($c) => 'Clause ' . $c['code']));
        const clauseData = @js(collect($clauseStats)->map(fn($c) => round($c['avg_rating'], 2)));

        window.myClausesChart = new Chart(clausesCtx, {
            type: 'bar',
            data: {
                labels: clauseLabels,
                datasets: [{
                    label: 'Avg Maturity Rating',
                    data: clauseData,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 5,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 10 } }
                    }
                }
            }
        });

        // 2. Sectors Chart Data
        const sectorLabels = @js(collect($sectorPerformance)->map(fn($s) => $s['business_sector']));
        const sectorData = @js(collect($sectorPerformance)->map(fn($s) => round($s['avg_score'], 2)));

        window.mySectorsChart = new Chart(sectorsCtx, {
            type: 'bar',
            data: {
                labels: sectorLabels,
                datasets: [{
                    label: 'Avg Maturity',
                    data: sectorData,
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        min: 0,
                        max: 5,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, color: '#64748b' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 10 } }
                    }
                }
            }
        });
    }
</script>
@endsection
