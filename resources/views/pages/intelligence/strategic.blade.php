@extends('layouts.app')

@section('title', 'Assessment Result')
@section('view_name', 'Audit Intelligence Hub - Strategic')

@push('head_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
@php
    $trendSessions = $maturityTrends ?? collect();
@endphp
<div class="max-w-6xl mx-auto space-y-6 pb-16" x-data="strategicAnalytics({{ $selectedId ?: 'null' }}, {{ $isAiProcessing ? 'true' : 'false' }}, {{ ($latestSession && $latestSession->ai_summary) ? 'true' : 'false' }})" x-init="initSummary()">
    
    {{-- Header with Session Filter --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-microchip text-lg"></i>
                </div>
                <div class="leading-none">
                    <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">{{ __('Assessment Result') }}</h1>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Unified Strategic Reporting & Technical Analysis') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <form action="{{ route('reports.strategic') }}" method="GET" id="hubFilter" class="flex items-center gap-3">
                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none hidden md:block">{{ __('Session:') }}</label>
                    <select name="session_id" onchange="document.getElementById('hubFilter').requestSubmit()" 
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
        <h3 class="text-base font-bold text-slate-900">{{ __('No Assessment Data Yet') }}</h3>
        <p class="text-sm text-slate-400 font-medium mt-1">{{ __('Create an audit session first to unlock assessment result analytics.') }}</p>
        <a href="{{ route('sessions.index') }}" class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/20">
            <i class="fa-solid fa-plus"></i> {{ __('Create Session') }}
        </a>
    </div>
    @else

    @php
        $latestScore = $comparison['latest_score'] ?? 0;
        $maturityLabel = 'Non-existent';
        if ($latestScore >= 4.5) $maturityLabel = 'Optimized';
        elseif ($latestScore >= 3.5) $maturityLabel = 'Managed';
        elseif ($latestScore >= 2.5) $maturityLabel = 'Defined';
        elseif ($latestScore >= 1.5) $maturityLabel = 'Limited/Repeatable';
        elseif ($latestScore >= 0.5) $maturityLabel = 'Initial';

        $totalScored = ($stats['compliant'] ?? 0) + ($stats['partial'] ?? 0) + ($stats['non_compliant'] ?? 0);
        $complianceRate = $totalScored > 0 ? round((($stats['compliant'] ?? 0) / $totalScored) * 100) : 0;
        
        $sessionRiskPriority = 'Low';
        $sessionRiskBadge = 'bg-emerald-50 text-emerald-600 border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white';
        $sessionRiskText = 'text-emerald-500';
        if (($stats['critical'] ?? 0) > 0) {
            $sessionRiskPriority = 'High';
            $sessionRiskBadge = 'bg-rose-50 text-rose-600 border-rose-100 group-hover:bg-rose-500 group-hover:text-white';
            $sessionRiskText = 'text-rose-500';
        } elseif (($stats['total_gaps'] ?? 0) > 0) {
            $sessionRiskPriority = 'Medium';
            $sessionRiskBadge = 'bg-amber-50 text-amber-600 border-amber-100 group-hover:bg-amber-500 group-hover:text-white';
            $sessionRiskText = 'text-amber-500';
        }
    @endphp
    
    {{-- KPI Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Card 1: Compliance Overview --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between h-[116px]">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Compliance Overview') }}</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-2xl font-black text-emerald-600 tracking-tight">{{ $latestSession ? $latestSession->compliance_score : 0 }}%</h3>
                        @php
                            $statusText = $latestSession ? $latestSession->compliance_status : 'Non-Compliant';
                            $statusColor = match($statusText) {
                                'Compliant' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                'Partially Compliant' => 'text-amber-600 bg-amber-50 border-amber-100',
                                default => 'text-rose-600 bg-rose-50 border-rose-100',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded border text-[8px] font-black uppercase tracking-wider {{ $statusColor }}">
                            {{ __($statusText) }}
                        </span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
            <div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $latestSession ? $latestSession->compliance_score : 0 }}%"></div>
                </div>
            </div>
        </div>

        {{-- Card 2: Overall Maturity --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between h-[116px]">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Overall Maturity') }}</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-2xl font-black text-indigo-600 tracking-tight">{{ number_format($latestScore, 2) }}/5</h3>
                        <span class="px-2 py-0.5 rounded border border-indigo-100 bg-indigo-50 text-indigo-700 text-[8px] font-black uppercase tracking-wider">
                            {{ __($maturityLabel) }}
                        </span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
            </div>
            <div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full" style="width: {{ min(($latestScore / 5) * 100, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Card 3: Risk & Gap Analysis --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between h-[116px]">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Risk & Gap Analysis') }}</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-2xl font-black text-orange-500 tracking-tight">{{ $stats['total_gaps'] ?? 0 }} {{ __('Gaps') }}</h3>
                        <span class="px-2 py-0.5 rounded border text-[8px] font-black uppercase tracking-wider {{ $sessionRiskBadge }}">
                            {{ __($sessionRiskPriority) }}
                        </span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center border border-rose-100 group-hover:bg-rose-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[8px] font-black uppercase tracking-widest text-slate-450">
                <span class="text-rose-500">{{ $stats['critical'] ?? 0 }} {{ __('Critical') }}</span>
                <span>&bull;</span>
                <span class="text-orange-500">{{ ($stats['total_gaps'] ?? 0) - ($stats['critical'] ?? 0) }} {{ __('Warn') }}</span>
            </div>
        </div>

        {{-- Card 4: Control Implementation --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group flex flex-col justify-between h-[116px]">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Control Implementation') }}</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-xl font-black text-teal-600 tracking-tight whitespace-nowrap">
                            {{ $latestSession ? ($stats['compliant'] ?? 0) : 0 }}/{{ $latestSession ? ($stats['total_controls'] ?? 0) : 0 }}
                        </h3>
                        <span class="px-2 py-0.5 rounded border border-teal-100 bg-teal-50 text-teal-700 text-[8px] font-black uppercase tracking-wider">
                            {{ __('Implemented') }}
                        </span>
                    </div>
                </div>
                <div class="w-9 h-9 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center border border-teal-100 group-hover:bg-teal-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
            @php
                $totalControls = $latestSession ? ($stats['total_controls'] ?? 0) : 0;
                $compliantControls = $latestSession ? ($stats['compliant'] ?? 0) : 0;
                $implementedPercent = $totalControls > 0 ? round(($compliantControls / $totalControls) * 100) : 0;
            @endphp
            <div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-teal-500 rounded-full" style="width: {{ $implementedPercent }}%"></div>
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
                    <i class="fa-solid fa-sparkles text-blue-400 text-xs"></i>{{ __('AI Analysis') }}</h2>
                @if($latestSession)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('reports.export-pdf', $latestSession->id) }}" class="px-3 py-1.5 bg-rose-500/20 hover:bg-rose-500/40 text-rose-100 rounded-xl text-[8px] font-black uppercase tracking-widest border border-rose-500/30 transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-rose-400"></i>{{ __('PDF') }}</a>
                    <a href="{{ route('reports.export-excel', $latestSession->id) }}" class="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/40 text-emerald-100 rounded-xl text-[8px] font-black uppercase tracking-widest border border-emerald-500/30 transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-emerald-400"></i>{{ __('Excel') }}</a>
                    @if($latestSession->status === 'completed')
                    <button @click="triggerAISummary()" :disabled="isGenerating" id="btn-generate-summary" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[8px] font-black uppercase tracking-widest border border-white/10 disabled:opacity-50 transition-all sm:ml-1">
                        <i class="fa-solid fa-arrows-rotate mr-1" :class="isGenerating && 'animate-spin'"></i>
                        <span x-text="isGenerating ? '{{ __('Synthesizing...') }}' : '{{ __('Regenerate') }}'"></span>
                    </button>
                    @endif
                </div>
                @endif
            </div>
            <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-5 max-h-[300px] overflow-y-auto custom-scrollbar">
                <div class="text-blue-50 text-[11px] leading-relaxed font-medium space-y-3">
                    
                    @if($latestSession && $latestSession->status !== 'completed')
                        <div class="text-center py-6 flex flex-col items-center justify-center">
                            <div class="w-12 h-12 bg-amber-500/10 text-amber-400 rounded-2xl flex items-center justify-center mb-3 border border-amber-500/20">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </div>
                            <h3 class="text-xs font-bold text-white mb-1 uppercase tracking-wider">{{ __('Assessment Not Finalized') }}</h3>
                            <p class="text-slate-400 max-w-sm mx-auto mb-4 text-[10px] leading-relaxed">{{ __('You must finalize and complete this assessment session before the AI can generate a strategic executive summary.') }}</p>
                            <a href="{{ route('sessions.show', $latestSession->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-black text-[9px] uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-blue-600/20">
                                <i class="fa-solid fa-arrow-right-to-bracket mr-1"></i>{{ __('Go to Assessment') }}
                            </a>
                        </div>
                    @else
                        {{-- Alpine dynamically injected summary --}}
                        <div x-show="summaryHtml !== null" x-html="summaryHtml"></div>

                        <div x-show="summaryHtml === null">
                            @if($latestSession && $latestSession->ai_summary)
                                @php
                                    $parsedSummary = \App\Services\Intelligence\AiSummaryService::parseSummary($latestSession->ai_summary);
                                @endphp
                                <div class="ai-prose space-y-4">
                                    @if($parsedSummary && (isset($parsedSummary['overall_assessment_summary']) || isset($parsedSummary['overall_assessment_conclusion'])))
                                        @php
                                            $overallSummary = $parsedSummary['overall_assessment_summary'] ?? $parsedSummary['overall_assessment_conclusion'] ?? '';
                                            $controlInsight = $parsedSummary['control_insight'] ?? $parsedSummary['overall_risk_areas'] ?? '';
                                            $impactInterpretation = $parsedSummary['impact_interpretation'] ?? $parsedSummary['assessment_confidence'] ?? '';
                                            $strategicRec = $parsedSummary['strategic_recommendation'] ?? $parsedSummary['executive_strategic_recommendations'] ?? [];
                                            $actionPlan = $parsedSummary['action_plan'] ?? '';
                                        @endphp

                                        @if(!empty($overallSummary))
                                            <div class="summary-section">
                                                <div class="summary-section-title"><i class="fa-solid fa-chart-line"></i> {{ __('Overall Assessment Summary') }}</div>
                                                <div class="summary-section-body">{!! Str::markdown(e($overallSummary)) !!}</div>
                                            </div>
                                        @endif
                                        
                                        @if(!empty($controlInsight))
                                            <div class="summary-section">
                                                <div class="summary-section-title"><i class="fa-solid fa-lightbulb"></i> {{ __('Control Insight') }}</div>
                                                <div class="summary-section-body">{!! Str::markdown(e($controlInsight)) !!}</div>
                                            </div>
                                        @endif

                                        @if(!empty($impactInterpretation))
                                            <div class="summary-section">
                                                <div class="summary-section-title"><i class="fa-solid fa-circle-nodes"></i> {{ __('Impact Interpretation') }}</div>
                                                <div class="summary-section-body">{!! Str::markdown(e($impactInterpretation)) !!}</div>
                                            </div>
                                        @endif

                                        @if(!empty($strategicRec))
                                            @php
                                                if (is_string($strategicRec)) $strategicRec = [$strategicRec];
                                            @endphp
                                            <div class="summary-section">
                                                <div class="summary-section-title"><i class="fa-solid fa-bullseye"></i> {{ __('Strategic Recommendation') }}</div>
                                                <ol class="summary-recs-list">
                                                    @foreach($strategicRec as $rec)
                                                        <li>{!! Str::markdown(e($rec)) !!}</li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        @endif

                                        @if(!empty($actionPlan))
                                            <div class="summary-section">
                                                <div class="summary-section-title"><i class="fa-solid fa-circle-check"></i> {{ __('Action Plan') }}</div>
                                                <div class="summary-section-body">{!! Str::markdown(e($actionPlan)) !!}</div>
                                            </div>
                                        @endif
                                    @else
                                        {!! Str::markdown(e($latestSession->ai_summary)) !!}
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-4 opacity-70">
                                   <i class="fa-solid fa-wand-magic-sparkles text-2xl mb-2"></i>
                                    <p>{{ __('Trigger AI synthesis to analyze the current audit session.') }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <style>
                        .ai-prose p { margin-bottom: 0.5rem; }
                        .ai-prose ol { list-style-type: decimal; padding-left: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
                        .ai-prose ul { list-style-type: disc; padding-left: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
                        .ai-prose li { margin-bottom: 0.25rem; }
                        .ai-prose strong { color: #f8fafc; font-weight: 800; }

                        .summary-section { margin-bottom: 1.25rem; }
                        .summary-section:last-child { margin-bottom: 0; }
                        .summary-section-title { font-weight: 800; text-transform: uppercase; font-size: 10px; tracking: 0.05em; color: #818cf8; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem; }
                        .summary-section-body { color: #cbd5e1; font-size: 11px; line-height: 1.6; }
                        .summary-recs-list { list-style-type: decimal; padding-left: 1.25rem; color: #cbd5e1; font-size: 11px; line-height: 1.6; }
                        .summary-recs-list li { margin-bottom: 0.35rem; }
                    </style>
                </div>
            </div>
        </div>
    </div>

    <div id="analytics-radar-section" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="mb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-gauge-high text-indigo-600"></i>{{ __('Session Maturity Radar') }}</h3>
                <p class="text-[9px] font-bold text-indigo-650 mt-1 leading-snug">
                    @php
                        $bestDomain = null;
                        $bestScore = -1;
                        $worstDomain = null;
                        $worstScore = 999;
                        
                        if (isset($comparison['domains']) && count($comparison['domains']) > 0) {
                            foreach ($comparison['domains'] as $dom) {
                                if ($dom['latest'] > $bestScore) {
                                    $bestScore = $dom['latest'];
                                    $bestDomain = $dom['label'];
                                }
                                if ($dom['latest'] < $worstScore) {
                                    $worstScore = $dom['latest'];
                                    $worstDomain = $dom['label'];
                                }
                            }
                        }
                    @endphp
                    @if($bestDomain && $bestScore > 0)
                        @if($bestScore == $worstScore)
                            {!! __('All ISO pillars have balanced maturity with an average score of <strong class="text-indigo-750">:score/5.0</strong>.', ['score' => number_format($bestScore, 1)]) !!}
                        @else
                            {!! __('The best performance is achieved in the <strong class="text-emerald-600">:best</strong> (:best_score/5) pillar, while the <strong class="text-rose-600">:worst</strong> (:worst_score/5) pillar is still the lowest and requires priority attention.', [
                                'best' => __($bestDomain),
                                'best_score' => number_format($bestScore, 1),
                                'worst' => __($worstDomain),
                                'worst_score' => number_format($worstScore, 1)
                            ]) !!}
                        @endif
                    @else
                        {{ __('Not enough assessment data to analyze maturity pillars.') }}
                    @endif
                </p>
            </div>
            <div class="h-64 w-full relative">
                <canvas id="maturityChart"></canvas>
                <div data-chart-fallback class="hidden absolute inset-0 items-center justify-center text-center px-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Chart unavailable') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>{{ __('Compliance Breakdown') }}
                    </h3>
                    @if($latestSession)
                    @php
                        $cbScore  = $latestSession->compliance_score;
                        $cbStatus = $latestSession->compliance_status;
                        $cbBadge  = match(strtolower($cbStatus)) {
                            'compliant'           => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'partially compliant' => 'bg-amber-50 text-amber-700 border-amber-100',
                            default               => 'bg-rose-50 text-rose-700 border-rose-100',
                        };
                    @endphp
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded border text-[8px] font-black uppercase tracking-wider {{ $cbBadge }}">{{ $cbStatus }}</span>
                    </div>
                    @endif
                </div>
                @if($latestSession)
                @php
                    $cbDesc = match(strtolower($cbStatus)) {
                        'compliant'           => __('Control implemented and meets requirements.'),
                        'partially compliant' => __('Control partially implemented with gaps.'),
                        default               => __('Control not implemented or below standard.'),
                    };
                    
                    // Pre-calculate counts for raw legend rendering
                    $csResults    = $latestSession->results->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);
                    $csApplicable = $csResults->where('is_applicable', true);
                    $csScored     = $csApplicable->where('status', 'completed');

                    $totalControls= $csResults->count();
                    $cntCompliant = $csScored->where('maturity_rating', '>=', 4)->count();
                    $cntPartial   = $csScored->whereBetween('maturity_rating', [2, 3])->count();
                    $cntNonCompl  = $csScored->where('maturity_rating', '<=', 1)->count();
                    $cntUnassessed= $csApplicable->where('status', '!=', 'completed')->count();
                    $cntExcluded  = $csResults->where('is_applicable', false)->count();
                @endphp
                <p class="text-[9px] font-bold text-indigo-600 mt-1 leading-snug">
                    @if($cbScore >= 80)
                        {{ __('Based on the assessment, the organization\'s information security controls are well-established and mostly compliant.') }}
                    @elseif($cbScore >= 40)
                        {{ __('The organization\'s controls are developing well, but active efforts are still required to remediate identified gaps.') }}
                    @else
                        {{ __('Significant effort is still required to implement basic security controls and address critical gaps.') }}
                    @endif
                </p>
                @else
                <p class="text-[9px] font-medium text-slate-400 leading-snug">{{ __('Comparison ratio of controls meeting the minimum standard (Level 4-5) versus those that do not.') }}</p>
                @endif
            </div>

            <div class="h-64 relative flex items-center justify-center mt-6">
                <canvas id="complianceChart"></canvas>
                @if($latestSession)
                <div class="absolute flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-black text-slate-800 leading-none">{{ $cbScore }}%</span>
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('Score') }}</span>
                </div>
                @endif
                <div data-chart-fallback class="hidden absolute inset-0 items-center justify-center text-center px-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Chart unavailable') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 lg:col-span-2">
            <div class="mb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-chart-column text-blue-600"></i>{{ __('Domain Progress Analysis') }}</h3>
                <p class="text-[9px] font-bold text-indigo-650 mt-1 leading-snug">
                    @php
                        $improvedDomains = [];
                        $declinedDomains = [];
                        $flatDomains = [];
                        $hasPreviousSession = isset($comparison['previous_score']) && $comparison['previous_score'] > 0;
                        
                        if ($hasPreviousSession && isset($comparison['domains'])) {
                            foreach ($comparison['domains'] as $dom) {
                                $translatedLabel = __($dom['label']);
                                if ($dom['delta'] > 0) {
                                    $improvedDomains[] = $translatedLabel . ' (+' . number_format($dom['delta'], 1) . ')';
                                } elseif ($dom['delta'] < 0) {
                                    $declinedDomains[] = $translatedLabel . ' (' . number_format($dom['delta'], 1) . ')';
                                } else {
                                    $flatDomains[] = $translatedLabel;
                                }
                            }
                        }
                    @endphp
                    @if($hasPreviousSession)
                        @if(($comparison['delta'] ?? 0) > 0)
                            {{ __('The assessment shows an overall score improvement of :delta compared to the previous cycle, led by progress in :improved.', [
                                'delta' => number_format($comparison['delta'], 2),
                                'improved' => implode(', ', $improvedDomains)
                            ]) }}
                        @elseif(($comparison['delta'] ?? 0) < 0)
                            {{ __('The overall maturity score declined by :delta compared to the previous cycle, with decreased performance observed in :declined.', [
                                'delta' => number_format(abs($comparison['delta']), 2),
                                'declined' => implode(', ', $declinedDomains)
                            ]) }}
                        @else
                            {{ __('The overall maturity remains stable at :score compared to the previous assessment cycle.', [
                                'score' => number_format($comparison['latest_score'] ?? 0, 2)
                            ]) }}
                        @endif
                    @else
                        @php
                            $bestDomain = null;
                            $bestScore = -1;
                            $worstDomain = null;
                            $worstScore = 999;
                            
                            if (isset($comparison['domains']) && count($comparison['domains']) > 0) {
                                foreach ($comparison['domains'] as $dom) {
                                    if ($dom['latest'] > $bestScore) {
                                        $bestScore = $dom['latest'];
                                        $bestDomain = $dom['label'];
                                    }
                                    if ($dom['latest'] < $worstScore) {
                                        $worstScore = $dom['latest'];
                                        $worstDomain = $dom['label'];
                                    }
                                }
                            }
                        @endphp
                        @if($bestDomain && $bestScore > 0)
                            {{ __('Since this is the initial audit cycle, the current domain scores are compared against a baseline of 0.0 to establish a performance benchmark for future cycles.') }}
                        @else
                            {{ __('In this initial cycle, no controls have been scored yet. Please complete control assessments to view domain progress.') }}
                        @endif
                    @endif
                </p>
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

    {{-- Incomplete Assessment Modal --}}
    <div x-show="showIncompleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showIncompleteModal = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 z-10 text-center">
            <div class="w-16 h-16 bg-amber-100 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">{{ __('Not Finalized') }}</h3>
            <p class="text-sm text-slate-500 mt-2">{{ __('You must complete and finalize the assessment before the AI can generate a strategic summary.') }}</p>
            <div class="mt-6 flex flex-col gap-2">
                @if($latestSession)
                <a href="{{ route('sessions.show', $latestSession->id) }}" class="w-full px-5 py-3 rounded-xl bg-blue-600 text-white font-bold uppercase tracking-wider hover:bg-blue-500 transition-all text-xs shadow-md">
                    <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>{{ __('Go to Assessment') }}
                </a>
                @endif
                <button @click="showIncompleteModal = false" class="w-full px-5 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold uppercase tracking-wider hover:bg-slate-200 transition-all text-xs">
                    {{ __('Dismiss') }}
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
@php
    $maturityViewsJson = $maturityViews ?? ['global' => []];
    $trendSessionsJson = $trendSessions ?? [];
    $complianceBreakdownJson = $complianceBreakdown ?? ['compliant' => 0, 'partial' => 0, 'non_compliant' => 0, 'unassessed' => 0];
    $comparisonDomainsJson = $comparison['domains'] ?? [];
@endphp
<script>
const registerStrategicAnalytics = () => {
    if (window.Alpine.data('strategicAnalytics')) return;

    window.Alpine.data('strategicAnalytics', (initialSessionId, isProcessing, hasSummary) => ({
        riskFilter: 'all',
        selectedSession: initialSessionId,
        isGenerating: false,
        expandedId: null,
        summaryHtml: null,
        showIncompleteModal: false,
        async initSummary() {
            if (!this.selectedSession) return;

            // If summary already exists, let the Blade template show it (no redundant fetch needed)
            if (hasSummary) {
                return;
            }

            // If server indicates it is processing, start polling immediately
            if (isProcessing) {
                this.isGenerating = true;
                this.summaryHtml = `<div class="text-center py-4 opacity-70"><i class="fa-solid fa-spinner animate-spin text-2xl mb-2 text-indigo-500"></i><p>{{ __('Analyzing and synthesizing session data...') }}</p></div>`;
                this.startPolling();
                return;
            }

            // Otherwise, check status to see if it recently started processing in another window/tab
            try {
                const statusRes = await fetch(`/reports/ai-summary/${this.selectedSession}/status`, {
                    headers: { 'Accept': 'application/json' }
                });
                const statusData = await statusRes.json().catch(() => ({}));
                if (!statusRes.ok || !statusData.success) return;

                if (statusData.data.status === 'processing') {
                    this.isGenerating = true;
                    this.summaryHtml = `<div class="text-center py-4 opacity-70"><i class="fa-solid fa-spinner animate-spin text-2xl mb-2 text-indigo-500"></i><p>{{ __('Analyzing and synthesizing session data...') }}</p></div>`;
                    this.startPolling();
                } else if (statusData.data.status === 'completed') {
                    const html = statusData.data.summary_html || statusData.data.summary;
                    if (html) {
                        this.summaryHtml = `<div class='ai-prose space-y-4'>${html}</div>`;
                    }
                }
            } catch (e) {
                // Fail silently
            }
        },
        startPolling() {
            let attempts = 0;
            const maxAttempts = 80;
            const pollInterval = setInterval(async () => {
                attempts++;
                try {
                    const statusRes = await fetch(`/reports/ai-summary/${this.selectedSession}/status`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const statusData = await statusRes.json().catch(() => ({}));
                    if (statusRes.ok && statusData.success && statusData.data.status === 'completed') {
                        clearInterval(pollInterval);
                        const html = statusData.data.summary_html || statusData.data.summary;
                        this.summaryHtml = `<div class='ai-prose space-y-2'>${html}</div>`;
                        this.isGenerating = false;
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Intelligence Core Synchronized!') }}', type: 'success' } }));
                    }
                } catch (e) { console.error("Polling error:", e); }
                if (attempts >= maxAttempts) {
                    clearInterval(pollInterval);
                    this.isGenerating = false;
                    // Do a final DB check before giving up — webhook may have delivered
                    // the summary AFTER the cache key expired (n8n slow response)
                    try {
                        const finalRes = await fetch(`/reports/ai-summary/${this.selectedSession}/status`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const finalData = await finalRes.json().catch(() => ({}));
                        if (finalRes.ok && finalData.success && finalData.data.status === 'completed') {
                            const html = finalData.data.summary_html || finalData.data.summary;
                            if (html) {
                                this.summaryHtml = `<div class='ai-prose space-y-2'>${html}</div>`;
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Intelligence Core Synchronized!') }}', type: 'success' } }));
                                return;
                            }
                        }
                    } catch (e) { /* ignore final check error */ }
                    // Truly timed out — keep Blade fallback visible (don't set null if Blade has content)
                    // Only clear summaryHtml if it's currently showing the spinner
                    if (this.summaryHtml && this.summaryHtml.includes('fa-spinner')) {
                        this.summaryHtml = null;
                    }
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Synthesis timed out. Please try again.') }}', type: 'error' } }));
                }
            }, 1500);
        },
        async triggerAISummary() {
            if (!this.selectedSession) return;
            
            const isCompleted = {{ ($latestSession && $latestSession->status === 'completed') ? 'true' : 'false' }};
            if (!isCompleted) {
                this.showIncompleteModal = true;
                return;
            }

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

                // Guard: no data change since last AI summary generation
                if (response.status === 409 && data.no_change) {
                    this.isGenerating = false;
                    // Restore the existing summary from the API
                    try {
                        const statusRes = await fetch(`/reports/ai-summary/${this.selectedSession}/status`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const statusData = await statusRes.json().catch(() => ({}));
                        if (statusRes.ok && statusData.success && statusData.data.status === 'completed') {
                            const html = statusData.data.summary_html || statusData.data.summary;
                            if (html) this.summaryHtml = `<div class='ai-prose space-y-4'>${html}</div>`;
                        }
                    } catch (e) { this.summaryHtml = null; }
                    Swal.fire({
                        icon: 'info',
                        title: '{{ __('No Changes Detected') }}',
                        html: '<p class=\'text-sm text-slate-600 leading-relaxed\'>{{ addslashes(__('The assessment scores and notes have not changed since the last executive summary was generated.')) }}</p>' +
                              '<p class=\'text-xs text-slate-400 mt-2\'>{{ addslashes(__('Regeneration is only required after modifying maturity scores or audit notes in any control.')) }}</p>',
                        confirmButtonText: '{{ __('Understood') }}',
                        confirmButtonColor: '#4f46e5',
                        width: '28rem',
                        customClass: {
                            title: 'text-base font-bold text-slate-800',
                            htmlContainer: 'text-left px-2',
                            confirmButton: 'text-xs font-bold uppercase tracking-widest px-5 py-2.5 rounded-lg'
                        }
                    });
                    return;
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.message || '{{ __('Synthesis Failed') }}');
                }
                this.startPolling();
            } catch (e) {
                this.summaryHtml = null;
                this.isGenerating = false;
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: e.message || '{{ __('Synthesis Failed') }}', type: 'error' } }));
            }
        }
    }));
};

if (window.Alpine) {
    registerStrategicAnalytics();
} else {
    document.addEventListener('alpine:init', registerStrategicAnalytics);
}

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
                label: '{{ __('Maturity') }}',
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
                            return `{{ __('Maturity') }}: ${item.formattedValue}/5`;
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
        console.warn('Chart.js is not available; Assessment Result charts were not initialized.');
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
        const compliance = @json($complianceBreakdown);
        window.chartInstances['complianceChart'] = new Chart(complianceEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['{{ __('Compliant') }}', '{{ __('Partially Compliant') }}', '{{ __('Non-Compliant') }}', '{{ __('Unassessed') }}', '{{ __('Not Applicable') }}'],
                datasets: [{
                    data: [compliance.compliant, compliance.partial, compliance.non_compliant, compliance.unassessed, compliance.excluded],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8', '#cbd5e1'],
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10, weight: 'bold' },
                        padding: 8,
                        cornerRadius: 6,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const dataArr = context.chart.data.datasets[0].data;
                                const total = dataArr.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                return ` ${label}: ${value} / ${total} (${pct}%)`;
                            }
                        }
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
                        label: '{{ __('Prev') }}',
                        data: domains.map(d => d.previous),
                        backgroundColor: '#e2e8f0',
                        borderRadius: 4,
                        barThickness: 12
                    },
                    {
                        label: '{{ __('Now') }}',
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
