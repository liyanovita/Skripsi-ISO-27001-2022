@extends('layouts.app')

@section('title', 'Strategic Analytics')
@section('view_name', 'Audit Intelligence Hub - Strategic')

@push('head_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
@php
    $trendSessions = $maturityTrends ?? collect();
@endphp
<div class="max-w-6xl mx-auto space-y-6 pb-16" x-data="strategicAnalytics({{ $selectedId ?: 'null' }})">
    
    {{-- Header with Session Filter --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-microchip text-lg"></i>
                </div>
                <div class="leading-none">
                    <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">{{ __('Strategic Analytics') }}</h1>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Unified Strategic Reporting & Technical Analysis') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <form action="{{ route('reports.strategic') }}" method="GET" id="hubFilter" class="flex items-center gap-3">
                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none hidden md:block">{{ __('Session:') }}</label>
                    <select name="session_id" onchange="document.getElementById('hubFilter').submit()" 
                        class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-4 focus:ring-blue-600/5 transition-all min-w-[260px] cursor-pointer shadow-sm">
                        @if($sessions && $sessions->count() > 0)
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ $selectedId == $session->id ? 'selected' : '' }}>
                                    {{ $session->name }} ({{ $session->created_at->format('M Y') }})
                                </option>
                            @endforeach
                        @else
                            <option value="">{{ __('No sessions available') }}</option>
                        @endif
                    </select>
                </form>
            </div>
        </div>
    </div>

    @if(!$latestSession)
    <div class="bg-white rounded-2xl border border-slate-100 p-16 text-center shadow-sm">
        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-chart-line text-3xl text-slate-300"></i>
        </div>
        <h3 class="text-base font-bold text-slate-900">{{ __('No Strategic Data Yet') }}</h3>
        <p class="text-sm text-slate-400 font-medium mt-1">{{ __('Create an audit session first to unlock strategic analytics.') }}</p>
        <a href="{{ route('sessions.index') }}" class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/20">
            <i class="fa-solid fa-plus"></i> {{ __('Create Session') }}
        </a>
    </div>
    @else

    @php
        $latestScore = $comparison['latest_score'] ?? 0;
        $roundedScore = round($latestScore);
        $maturityLabel = 'Non-existent';
        if ($roundedScore == 1) $maturityLabel = 'Initial';
        elseif ($roundedScore == 2) $maturityLabel = 'Limited/Repeatable';
        elseif ($roundedScore == 3) $maturityLabel = 'Defined';
        elseif ($roundedScore == 4) $maturityLabel = 'Managed';
        elseif ($roundedScore >= 5) $maturityLabel = 'Optimized';

        $totalScored = ($stats['compliant'] ?? 0) + ($stats['partial'] ?? 0) + ($stats['non_compliant'] ?? 0);
        $complianceRate = $totalScored > 0 ? round((($stats['compliant'] ?? 0) / $totalScored) * 100) : 0;
    @endphp
    
    {{-- KPI Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Overall Maturity --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Maturity Level') }}</p>
                    <h3 class="text-2xl font-black text-indigo-600 tracking-tight">{{ number_format($latestScore, 2) }}/5</h3>
                </div>
                <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-[8px] font-black uppercase tracking-widest mb-1">
                    <span class="text-slate-500 font-bold">{{ $maturityLabel }}</span>
                    @if(isset($comparison['delta']) && $comparison['delta'] != 0)
                        <span class="font-bold {{ $comparison['delta'] > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-1.5 py-0.5 rounded">
                            <i class="fa-solid {{ $comparison['delta'] > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-0.5"></i>{{ number_format(abs($comparison['delta']), 2) }}
                        </span>
                    @endif
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full" style="width: {{ min(($latestScore / 5) * 100, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Compliance Rate --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Compliance Rate') }}</p>
                    <h3 class="text-2xl font-black text-emerald-600 tracking-tight">{{ $complianceRate }}%</h3>
                </div>
                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-[8px] font-black uppercase tracking-widest mb-1">
                    <span class="text-slate-400">{{ __('Compliant Controls') }}</span>
                    <span class="text-emerald-600 font-bold">{{ $stats['compliant'] ?? 0 }} / {{ $totalScored }}</span>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $complianceRate }}%"></div>
                </div>
            </div>
        </div>

        {{-- Gaps & Action Items --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Active Gaps') }}</p>
                    <h3 class="text-2xl font-black text-orange-500 tracking-tight">{{ $stats['total_gaps'] ?? 0 }}</h3>
                </div>
                <div class="w-9 h-9 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center border border-orange-100 group-hover:bg-orange-500 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="px-1.5 py-0.5 bg-rose-50 text-rose-600 rounded border border-rose-100 text-[8px] font-black uppercase tracking-widest">
                    {{ $stats['critical'] ?? 0 }} Critical
                </span>
                <span class="px-1.5 py-0.5 bg-orange-50 text-orange-600 rounded border border-orange-100 text-[8px] font-black uppercase tracking-widest">
                    {{ ($stats['total_gaps'] ?? 0) - ($stats['critical'] ?? 0) }} Warn
                </span>
            </div>
        </div>

        {{-- Scoping / SoA --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Scope Scenarios') }}</p>
                    <h3 class="text-2xl font-black text-blue-600 tracking-tight">{{ $stats['total_controls'] ?? 0 }}</h3>
                </div>
                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-expand"></i>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-[8px] font-black uppercase tracking-widest">
                    <span class="text-slate-400">{{ __('Applicable / Excluded') }}</span>
                    <span class="text-blue-600 font-bold">{{ $stats['total_controls'] ?? 0 }} A / {{ $stats['excluded'] ?? 0 }} E</span>
                </div>
            </div>
        </div>
    </div>

    {{-- AI Summary (full width) --}}
    <div class="bg-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 via-purple-600/20 to-transparent"></div>
        <div class="relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 leading-none">
                <h2 class="text-xs font-black text-white tracking-tight uppercase flex items-center gap-2">
                    <i class="fa-solid fa-sparkles text-blue-400 text-xs"></i>{{ __('AI Executive Summary') }}</h2>
                @if($latestSession)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('reports.export-pdf', $latestSession->id) }}" class="px-3 py-1.5 bg-rose-500/20 hover:bg-rose-500/40 text-rose-100 rounded-xl text-[8px] font-black uppercase tracking-widest border border-rose-500/30 transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-rose-400"></i>{{ __('PDF') }}</a>
                    <a href="{{ route('reports.export-excel', $latestSession->id) }}" class="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/40 text-emerald-100 rounded-xl text-[8px] font-black uppercase tracking-widest border border-emerald-500/30 transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-emerald-400"></i>{{ __('Excel') }}</a>
                    <button @click="triggerAISummary()" :disabled="isGenerating" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[8px] font-black uppercase tracking-widest border border-white/10 disabled:opacity-50 transition-all sm:ml-1">
                        <i class="fa-solid fa-arrows-rotate mr-1" :class="isGenerating && 'animate-spin'"></i>
                        <span x-text="isGenerating ? 'Synthesizing...' : 'Regenerate'"></span>
                    </button>
                </div>
                @endif
            </div>
            <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-5 max-h-[260px] overflow-y-auto custom-scrollbar">
                <div class="text-blue-50 text-[11px] leading-relaxed font-medium space-y-3">
                    
                    {{-- Alpine dynamically injected summary --}}
                    <div x-show="summaryHtml !== null" x-html="summaryHtml"></div>

                    {{-- Initial Blade-rendered summary --}}
                    <div x-show="summaryHtml === null">
                        @if($latestSession && $latestSession->ai_summary)
                            <div class="ai-prose space-y-2">
                                {!! Str::markdown(e($latestSession->ai_summary)) !!}
                            </div>
                        @else
                            <div class="text-center py-4 opacity-70">
                               <i class="fa-solid fa-wand-magic-sparkles text-2xl mb-2"></i>
                                <p>{{ __('Trigger AI synthesis to analyze the current audit session.') }}</p>
                            </div>
                        @endif
                    </div>

                    <style>
                        .ai-prose p { margin-bottom: 0.5rem; }
                        .ai-prose ol { list-style-type: decimal; padding-left: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
                        .ai-prose ul { list-style-type: disc; padding-left: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
                        .ai-prose li { margin-bottom: 0.25rem; }
                        .ai-prose strong { color: #f8fafc; font-weight: 800; }
                    </style>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="mb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-gauge-high text-indigo-600"></i>{{ __('Maturity Scope Radar') }}</h3>
                <p class="text-[9px] font-medium text-slate-400 mt-1 leading-snug">{{ __('Distribution of average maturity scores (0-5) across the 5 main pillars of ISO 27001:2022.') }}</p>
            </div>
            <div class="h-64 w-full relative">
                <canvas id="maturityChart"></canvas>
                <div data-chart-fallback class="hidden absolute inset-0 items-center justify-center text-center px-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Chart unavailable') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="mb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>{{ __('Compliance Breakdown') }}</h3>
                <p class="text-[9px] font-medium text-slate-400 mt-1 leading-snug">{{ __('Comparison ratio of controls meeting the minimum standard (Level 4-5) versus those that do not.') }}</p>
            </div>
            <div class="h-64 w-full relative">
                <canvas id="complianceChart"></canvas>
                <div data-chart-fallback class="hidden absolute inset-0 items-center justify-center text-center px-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Chart unavailable') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 lg:col-span-2">
            <div class="mb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-chart-column text-blue-600"></i>{{ __('Domain Progress Analysis') }}</h3>
                <p class="text-[9px] font-medium text-slate-400 mt-1 leading-snug">{{ __('Performance comparison across domains between the current and previous audit cycles.') }}</p>
            </div>
            <div class="h-64 w-full relative">
                <canvas id="domainCompChart"></canvas>
                <div data-chart-fallback class="hidden absolute inset-0 items-center justify-center text-center px-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Chart unavailable') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
@php
    $maturityViewsJson = $maturityViews ?? ['global' => []];
    $trendSessionsJson = $trendSessions ?? [];
    $complianceBreakdownJson = $complianceBreakdown ?? ['compliant' => 0, 'partial' => 0, 'non_compliant' => 0, 'unassessed' => 0];
    $comparisonDomainsJson = $comparison['domains'] ?? [];
@endphp
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('strategicAnalytics', (initialSessionId) => ({
        riskFilter: 'all',
        selectedSession: initialSessionId,
        isGenerating: false,
        expandedId: null,
        summaryHtml: null,
        async triggerAISummary() {
            if (!this.selectedSession) return;
            this.isGenerating = true;
            this.summaryHtml = `<div class="text-center py-4 opacity-70"><i class="fa-solid fa-spinner animate-spin text-2xl mb-2 text-indigo-500"></i><p>{{ __('Analyzing and synthesizing session data...') }}</p></div>`;
            try {
                const response = await fetch(`/reports/ai-summary/${this.selectedSession}`, {
                    method: 'GET',
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Synthesis Failed');
                }
                
                // Poll for status since generation is async (give Ollama more time, up to 120s)
                let attempts = 0;
                const maxAttempts = 80;
                const pollInterval = setInterval(async () => {
                    attempts++;
                    try {
                        const statusRes = await fetch(`/reports/ai-summary/${this.selectedSession}/status`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const statusData = await statusRes.json().catch(() => ({}));
                        if (statusRes.ok && statusData.success) {
                            if (statusData.data.status === 'completed') {
                                clearInterval(pollInterval);
                                this.summaryHtml = `<div class='ai-prose space-y-2'>${statusData.data.summary_html}</div>`;
                                this.isGenerating = false;
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Intelligence Core Synchronized!', type: 'success' } }));
                            }
                        }
                    } catch (e) {
                        console.error("Polling error:", e);
                    }
                    
                    if (attempts >= maxAttempts) {
                        clearInterval(pollInterval);
                        this.isGenerating = false;
                        this.summaryHtml = null;
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Synthesis timed out. Please try again.', type: 'error' } }));
                    }
                }, 1500);
            } catch (e) {
                this.summaryHtml = null;
                this.isGenerating = false;
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: e.message || 'Synthesis Failed', type: 'error' } }));
            }
        }
    }));
});

window.chartInstances = window.chartInstances || {};
window.maturityViews = @json($maturityViewsJson);
window.strategicTrendSessions = @json($trendSessionsJson);

window.showStrategicChartFallbacks = function() {
    document.querySelectorAll('[data-chart-fallback]').forEach(el => {
        el.classList.remove('hidden');
        el.classList.add('flex');
    });
};

window.hideStrategicChartFallbacks = function() {
    document.querySelectorAll('[data-chart-fallback]').forEach(el => {
        el.classList.add('hidden');
        el.classList.remove('flex');
    });
};

window.getMaturityConfig = function() {
    const rows = window.maturityViews.global || [];
    return {
        type: 'radar',
        data: {
            labels: rows.map(row => row.label),
            datasets: [{
                label: 'Maturity',
                data: rows.map(row => row.value),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title(items) {
                            const item = items[0];
                            return rows[item.dataIndex]?.fullLabel || item.label;
                        },
                        label(item) {
                            return `Maturity: ${item.formattedValue}/5`;
                        }
                    }
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 5,
                    ticks: { stepSize: 1, display: false },
                    grid: { color: 'rgba(15,23,42,0.08)' },
                    angleLines: { color: 'rgba(15,23,42,0.08)' },
                    pointLabels: {
                        font: { size: 10, weight: 'bold' },
                        color: '#475569'
                    }
                }
            }
        }
    };
};

window.updateMaturityChart = function() {
    if (!window.Chart) return;
    const maturityEl = document.getElementById('maturityChart');
    if (!maturityEl) return;

    if (window.chartInstances['maturityChart']) {
        window.chartInstances['maturityChart'].destroy();
    }

    window.chartInstances['maturityChart'] = new Chart(
        maturityEl.getContext('2d'),
        window.getMaturityConfig()
    );
};

window.initCharts = function() {
    if (document.documentElement.hasAttribute("data-turbo-preview")) return;
    if (!window.Chart) {
        console.warn('Chart.js is not available; Strategic Analytics charts were not initialized.');
        window.showStrategicChartFallbacks();
        return;
    }
    window.hideStrategicChartFallbacks();

    // Destroy existing instances to prevent duplicate errors
    ['maturityChart', 'complianceChart', 'domainCompChart'].forEach(id => {
        if (window.chartInstances[id]) {
            window.chartInstances[id].destroy();
            delete window.chartInstances[id];
        }
    });

    window.updateMaturityChart();

    const complianceEl = document.getElementById('complianceChart');
    if (complianceEl) {
        const compliance = @json($complianceBreakdownJson);
        window.chartInstances['complianceChart'] = new Chart(complianceEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Compliant', 'Partially Compliant', 'Non-Compliant', 'Unassessed'],
                datasets: [{
                    data: [compliance.compliant, compliance.partial, compliance.non_compliant, compliance.unassessed],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#cbd5e1'],
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 10, weight: 'bold' }, usePointStyle: true, boxWidth: 8 }
                    }
                }
            }
        });
    }

    const domainEl = document.getElementById('domainCompChart');
    if (domainEl) {
        const domains = @json($comparisonDomainsJson);
        window.chartInstances['domainCompChart'] = new Chart(domainEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: domains.map(d => d.label),
                datasets: [
                    {
                        label: 'Prev',
                        data: domains.map(d => d.previous),
                        backgroundColor: '#e2e8f0',
                        borderRadius: 4,
                        barThickness: 12
                    },
                    {
                        label: 'Now',
                        data: domains.map(d => d.latest),
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                        barThickness: 12
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: { font: { size: 10, weight: 'bold' }, usePointStyle: true, boxWidth: 8 }
                    } 
                },
                scales: {
                    y: { beginAtZero: true, max: 5, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 10, weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
                }
            }
        });
    }
}
document.addEventListener('DOMContentLoaded', window.initCharts);
document.addEventListener('turbo:load', window.initCharts);
</script>
@endpush
@endsection
