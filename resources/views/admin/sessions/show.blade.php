@extends('layouts.admin')

@section('title', $session->name . ' — Session Detail')
@section('header_title', 'Session Detail')

@section('content')
<div class="space-y-6 pb-8">

    {{-- Top Back Navigation --}}
    <div class="flex items-center justify-between">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.sessions.index') }}" 
           onclick="if (document.referrer && document.referrer !== location.href) { window.history.back(); return false; }"
           class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-slate-600 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all group">
            <i class="fa-solid fa-arrow-left text-slate-400 group-hover:-translate-x-0.5 group-hover:text-blue-600 transition-transform"></i>
            {{ __('Back to Sessions') }}
        </a>
    </div>

    {{-- Hero Session Banner (Polished Executive Light Theme) --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 relative overflow-hidden">
        {{-- Subtle Gradient Top Accent Line --}}
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-blue-600 to-sky-600"></div>

        <div class="relative z-10 flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            {{-- Left Info Block --}}
            <div class="space-y-4 flex-1 min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                        {{ $session->status === 'completed' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 
                           ($session->status === 'in_progress' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-slate-50 text-slate-600 border-slate-200') }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $session->status === 'completed' ? 'bg-emerald-500 animate-pulse' : ($session->status === 'in_progress' ? 'bg-blue-500 animate-pulse' : 'bg-slate-400') }}"></span>
                        {{ str_replace('_', ' ', $session->status) }}
                    </span>

                    @if($session->deadline)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $session->deadline->isPast() ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                            <i class="fa-solid fa-hourglass-half text-[9px]"></i> Deadline: {{ $session->deadline->format('M d, Y') }}
                        </span>
                    @endif
                </div>

                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                        {{ $session->name }}
                    </h1>
                    <div class="flex items-center gap-4 mt-2 text-xs text-slate-500 flex-wrap font-medium">
                        <a href="{{ route('admin.users.show', $session->user_id) }}" class="flex items-center gap-1.5 hover:text-blue-600 transition-colors">
                            <div class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[9px] font-bold shrink-0 border border-blue-100">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span>{{ $session->user->name ?? 'Unknown' }}</span>
                        </a>
                        @if($session->organization)
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-building text-slate-400"></i>
                                {{ $session->organization->name }}
                            </span>
                        @endif
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar text-slate-400"></i> Created {{ $session->created_at->format('M d, Y') }}</span>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-clock text-slate-400"></i> Updated {{ $session->updated_at->diffForHumans() }}</span>
                    </div>
                </div>

                {{-- Action Buttons Toolbar --}}
                <div class="pt-2 flex items-center gap-2.5 flex-wrap">
                    <a href="{{ route('admin.sessions.workspace', [$session, 'from' => 'show']) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                        <i class="fa-solid fa-clipboard-check text-sm"></i> {{ __('Open Workspace') }}
                    </a>

                    <a href="{{ route('admin.capa.index', ['session_id' => $session->id]) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 rounded-xl text-xs font-bold uppercase tracking-wider transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500"></i> {{ __('Improvement Tracking') }}
                    </a>

                    {{-- Export Buttons Pair (Always Side-by-Side) --}}
                    <div class="inline-flex items-center gap-2 shrink-0">
                        <a href="{{ route('reports.export-pdf', $session) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition-all hover:scale-[1.02] active:scale-95">
                            <i class="fa-solid fa-file-pdf text-rose-500"></i> {{ __('Export PDF') }}
                        </a>
                        <a href="{{ route('reports.export-excel', $session) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm transition-all hover:scale-[1.02] active:scale-95">
                            <i class="fa-solid fa-file-excel text-emerald-600"></i> {{ __('Export Excel') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Executive Metric Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 shrink-0 self-stretch xl:self-auto">
                @php
                    $complianceScore = round(($session->overall_maturity_score / 5) * 100, 1);
                    $gapCount = isset($criticalFindings) ? $criticalFindings->count() : (($stats['partial'] ?? 0) + ($stats['non_compliant'] ?? 0));
                    $score = $session->overall_maturity_score;
                    $riskText = $score >= 4.0 ? 'Low' : ($score >= 2.0 ? 'Medium' : 'High');
                    $riskColor = $score >= 4.0 ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : ($score >= 2.0 ? 'text-amber-600 bg-amber-50 border-amber-100' : 'text-rose-600 bg-rose-50 border-rose-100');

                    $maturityLabel = 'L0';
                    if ($score >= 4.5) $maturityLabel = 'L5';
                    elseif ($score >= 3.5) $maturityLabel = 'L4';
                    elseif ($score >= 2.5) $maturityLabel = 'L3';
                    elseif ($score >= 1.5) $maturityLabel = 'L2';
                    elseif ($score >= 0.5) $maturityLabel = 'L1';
                @endphp

                {{-- 1. Compliance Badge --}}
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 text-center flex flex-col justify-between min-w-[110px]">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[9px] font-bold uppercase tracking-widest">{{ __('Compliance') }}</span>
                        <i class="fa-solid fa-chart-pie text-emerald-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black tracking-tight {{ $complianceScore >= 80 ? 'text-emerald-600' : ($complianceScore >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ number_format($complianceScore, 1) }}%
                    </div>
                </div>

                {{-- 2. Maturity Score Badge --}}
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 text-center flex flex-col justify-between min-w-[115px]">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[9px] font-bold uppercase tracking-widest">{{ __('Maturity') }}</span>
                        <i class="fa-solid fa-award text-sky-500 text-xs"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-black tracking-tight text-sky-600">{{ number_format($session->overall_maturity_score, 2) }}</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mt-0.5">{{ $maturityLabel }}</span>
                    </div>
                </div>

                {{-- 3. Gap Count Badge --}}
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 text-center flex flex-col justify-between min-w-[110px]">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[9px] font-bold uppercase tracking-widest">{{ __('Gaps') }}</span>
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black tracking-tight {{ $gapCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $gapCount }}
                    </div>
                </div>

                {{-- 4. Risk Level Badge --}}
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 text-center flex flex-col justify-between min-w-[125px]">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[9px] font-bold uppercase tracking-widest">{{ __('Risk Level') }}</span>
                        <i class="fa-solid fa-shield-halved text-amber-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black tracking-tight {{ $score >= 4.0 ? 'text-emerald-600' : ($score >= 2.0 ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ $riskText }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Executive Interactive Charts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Chart 1: Compliance Breakdown --}}
        <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-black text-slate-900 tracking-tight text-base flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        {{ __('Compliance Breakdown') }}
                    </h3>
                </div>
                <p class="text-xs text-slate-400 font-medium mb-4 ml-10">{{ __('Distribution of control compliance ratings') }}</p>
            </div>
            
            <div class="relative h-56 w-full my-2">
                <canvas id="complianceStatusChart"></canvas>
            </div>

            <div class="grid grid-cols-4 gap-2 pt-4 mt-2 border-t border-slate-100 text-center">
                <div class="bg-slate-50/80 rounded-xl p-2">
                    <span class="block font-black text-emerald-600 text-sm">{{ $stats['compliant'] }}</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Compliant') }}</span>
                </div>
                <div class="bg-slate-50/80 rounded-xl p-2">
                    <span class="block font-black text-amber-600 text-sm">{{ $stats['partial'] }}</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Partial') }}</span>
                </div>
                <div class="bg-slate-50/80 rounded-xl p-2">
                    <span class="block font-black text-rose-600 text-sm">{{ $stats['non_compliant'] }}</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Non-Comp') }}</span>
                </div>
                <div class="bg-slate-50/80 rounded-xl p-2">
                    <span class="block font-black text-slate-400 text-sm">{{ $stats['excluded'] }}</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Excluded') }}</span>
                </div>
            </div>

            {{-- Chart 1 Summary Takeaway --}}
            <div class="mt-3 p-3 bg-emerald-50/70 border border-emerald-100/80 rounded-2xl text-[11px] text-slate-700 flex items-start gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 shrink-0 text-xs"></i>
                <div>
                    <strong class="font-bold text-emerald-950 block text-[9px] uppercase tracking-wider mb-0.5">{{ __('Compliance Summary') }}:</strong>
                    <span>Achieved <strong class="text-emerald-700 font-bold">{{ number_format($complianceScore, 1) }}% overall compliance</strong> with <strong class="text-emerald-700 font-bold">{{ $stats['compliant'] }} compliant controls</strong> and <strong class="text-amber-700 font-bold">{{ $gapCount }} total control gaps</strong>.</span>
                </div>
            </div>
        </div>

        {{-- Chart 2: Maturity Distribution --}}
        <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-black text-slate-900 tracking-tight text-base flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid fa-chart-column"></i>
                        </div>
                        {{ __('Maturity Distribution') }}
                    </h3>
                </div>
                <p class="text-xs text-slate-400 font-medium mb-4 ml-10">{{ __('Controls count per maturity level (L1 - L5)') }}</p>
            </div>

            <div class="relative h-64 w-full">
                <canvas id="maturityChart"></canvas>
            </div>

            {{-- Chart 2 Summary Takeaway --}}
            <div class="mt-3 p-3 bg-sky-50/70 border border-sky-100/80 rounded-2xl text-[11px] text-slate-700 flex items-start gap-2">
                <i class="fa-solid fa-chart-column text-sky-600 mt-0.5 shrink-0 text-xs"></i>
                <div>
                    <strong class="font-bold text-sky-950 block text-[9px] uppercase tracking-wider mb-0.5">{{ __('Maturity Summary') }}:</strong>
                    <span>Session overall maturity score is <strong class="text-sky-700 font-bold">{{ number_format($session->overall_maturity_score, 2) }} / 5.00</strong> (Classification: <strong class="text-sky-900 font-bold">{{ $maturityLabel }}</strong>).</span>
                </div>
            </div>
        </div>

        {{-- Chart 3: Assessment Progress & AI Insights --}}
        <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-black text-slate-900 tracking-tight text-base flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid fa-tasks"></i>
                        </div>
                        {{ __('Assessment Progress') }}
                    </h3>
                </div>

                <div class="flex items-center justify-center gap-6 py-3">
                    <div class="relative w-32 h-32 shrink-0">
                        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                            <circle cx="60" cy="60" r="50" fill="none" stroke="{{ $stats['completion_pct'] >= 80 ? '#10b981' : ($stats['completion_pct'] >= 50 ? '#f59e0b' : '#ef4444') }}" stroke-width="10" stroke-linecap="round"
                                    stroke-dasharray="{{ 314 * $stats['completion_pct'] / 100 }} 314"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-slate-900">{{ number_format($stats['completion_pct']) }}%</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Assessed') }}</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                            <span class="text-slate-400 font-medium block text-[10px] uppercase tracking-wider">{{ __('Total Questions') }}</span>
                            <span class="font-black text-slate-800 text-sm">{{ $stats['answered_questions'] }} / {{ $stats['total_questions'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 3 Summary Takeaway --}}
            <div class="mt-3 p-3 bg-blue-50/70 border border-blue-100/80 rounded-2xl text-[11px] text-slate-700 flex items-start gap-2">
                <i class="fa-solid fa-tasks text-blue-600 mt-0.5 shrink-0 text-xs"></i>
                <div>
                    <strong class="font-bold text-blue-950 block text-[9px] uppercase tracking-wider mb-0.5">{{ __('Progress Summary') }}:</strong>
                    <span>Assessment <strong class="text-blue-700 font-bold">{{ number_format($stats['completion_pct']) }}% complete</strong> (<strong class="text-blue-700 font-bold">{{ $stats['answered_questions'] }} / {{ $stats['total_questions'] }} questions</strong> answered).</span>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-800 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-robot text-blue-500"></i> {{ __('AI Summary Insights') }}
                </h4>
                @if($session->ai_summary)
                    <div class="text-xs text-slate-600 leading-relaxed max-h-24 overflow-y-auto prose prose-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
                        {!! \Illuminate\Support\Str::markdown(e($session->ai_summary)) !!}
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">{{ __('No AI summary generated yet.') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Audit Results Tables Section with Tabs --}}
    @php
        $excludedList = $excludedControls ?? collect();
    @endphp

    <div x-data="{ 
            activeTab: 'gaps',
            showAiModal: false,
            activeAiDetails: { code: '', title: '', rec: '', plan: '', insight: '', priority: '', validation: '', impact: '' },
            openAiDetails(details) {
                this.activeAiDetails = details;
                this.showAiModal = true;
            }
        }" class="space-y-4">

        {{-- Global AI Detail Modal --}}
        <div x-show="showAiModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-6"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-cloak>
            
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md" @click="showAiModal = false"></div>

            <div class="relative bg-white rounded-3xl border border-slate-100 w-full max-w-3xl p-6 md:p-8 shadow-2xl space-y-6 z-10 overflow-hidden max-h-[90vh] overflow-y-auto"
                @click.away="showAiModal = false">
                
                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20 shrink-0">
                            <i class="fa-solid fa-robot text-xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="px-2.5 py-0.5 bg-blue-50 border border-blue-100/90 text-blue-700 text-[11px] font-extrabold rounded-lg uppercase tracking-wider leading-none" x-text="activeAiDetails.code"></span>
                                <span class="text-[10px] text-blue-500 font-extrabold uppercase tracking-widest leading-none">{{ __('AI COMPLIANCE SYNTHESIS') }}</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight leading-snug mt-1" x-text="activeAiDetails.title"></h3>
                        </div>
                    </div>
                    <button @click="showAiModal = false" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all flex items-center justify-center shrink-0 cursor-pointer">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                {{-- AI Analysis Accordion List --}}
                <div class="space-y-3.5" x-data="{ openSection: 'rec' }">

                    {{-- Section 1: Strategic Recommendation --}}
                    <div class="rounded-2xl border transition-all overflow-hidden"
                         :class="openSection === 'rec' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                        <button type="button"
                            @click="openSection = openSection === 'rec' ? null : 'rec'"
                            class="w-full flex items-center justify-between gap-3 p-4 text-left cursor-pointer transition-colors"
                            :class="openSection === 'rec' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="openSection === 'rec' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                    <i class="fa-solid fa-lightbulb text-xs"></i>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest"
                                      :class="openSection === 'rec' ? 'text-blue-700' : 'text-slate-600'">
                                    {{ __('STRATEGIC RECOMMENDATION') }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                               :class="openSection === 'rec' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                        </button>
                        <div x-show="openSection === 'rec'" x-collapse.duration.250ms>
                            <div class="p-5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-2xl">
                                <p class="whitespace-pre-line" x-text="activeAiDetails.rec || '{{ __('No recommendation recorded.') }}'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Corrective Action Plan --}}
                    <div class="rounded-2xl border transition-all overflow-hidden"
                         :class="openSection === 'cap' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                        <button type="button"
                            @click="openSection = openSection === 'cap' ? null : 'cap'"
                            class="w-full flex items-center justify-between gap-3 p-4 text-left cursor-pointer transition-colors"
                            :class="openSection === 'cap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="openSection === 'cap' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                    <i class="fa-solid fa-list-check text-xs"></i>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest"
                                      :class="openSection === 'cap' ? 'text-blue-700' : 'text-slate-600'">
                                    {{ __('CORRECTIVE ACTION PLAN') }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                               :class="openSection === 'cap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                        </button>
                        <div x-show="openSection === 'cap'" x-collapse.duration.250ms>
                            <div class="p-5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-2xl">
                                <p class="whitespace-pre-line" x-text="activeAiDetails.plan || '{{ __('No specific action plan drafted.') }}'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: AI Audit Insight (Gap) --}}
                    <div class="rounded-2xl border transition-all overflow-hidden"
                         :class="openSection === 'gap' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                        <button type="button"
                            @click="openSection = openSection === 'gap' ? null : 'gap'"
                            class="w-full flex items-center justify-between gap-3 p-4 text-left cursor-pointer transition-colors"
                            :class="openSection === 'gap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="openSection === 'gap' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest"
                                      :class="openSection === 'gap' ? 'text-blue-700' : 'text-slate-600'">
                                    {{ __('AI AUDIT INSIGHT (GAP)') }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                               :class="openSection === 'gap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                        </button>
                        <div x-show="openSection === 'gap'" x-collapse.duration.250ms>
                            <div class="p-5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-2xl">
                                <p class="whitespace-pre-line" x-text="activeAiDetails.insight || '{{ __('Control shows solid operational alignment.') }}'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 4: Impact Interpretation --}}
                    <div class="rounded-2xl border transition-all overflow-hidden"
                         :class="openSection === 'impact' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                        <button type="button"
                            @click="openSection = openSection === 'impact' ? null : 'impact'"
                            class="w-full flex items-center justify-between gap-3 p-4 text-left cursor-pointer transition-colors"
                            :class="openSection === 'impact' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                     :class="openSection === 'impact' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest"
                                      :class="openSection === 'impact' ? 'text-blue-700' : 'text-slate-600'">
                                    {{ __('IMPACT INTERPRETATION') }}
                                </span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200"
                               :class="openSection === 'impact' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                        </button>
                        <div x-show="openSection === 'impact'" x-collapse.duration.250ms>
                            <div class="p-5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-2xl">
                                <p class="whitespace-pre-line" x-text="activeAiDetails.impact || '{{ __('No impact interpretation available.') }}'"></p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Footer Badges --}}
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3" x-show="activeAiDetails.priority">
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Risk Tier:') }}</span>
                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-100 text-[9px] font-black rounded-lg uppercase tracking-wider leading-none" x-text="activeAiDetails.priority"></span>
                    </div>
                    <button type="button" @click="showAiModal = false" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all cursor-pointer">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Tab Switcher Bar --}}
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 flex-wrap gap-3">
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80">
                <button type="button" 
                        @click="activeTab = 'gaps'" 
                        :class="activeTab === 'gaps' ? 'bg-white text-slate-900 shadow-sm font-black border border-slate-200/60' : 'text-slate-500 font-bold hover:text-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-magnifying-glass-chart text-amber-500"></i>
                    <span>{{ __('Control Gap Findings') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                          :class="activeTab === 'gaps' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600'">
                        {{ $criticalFindings->count() }}
                    </span>
                </button>

                <button type="button" 
                        @click="activeTab = 'excluded'" 
                        :class="activeTab === 'excluded' ? 'bg-white text-slate-900 shadow-sm font-black border border-slate-200/60' : 'text-slate-500 font-bold hover:text-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-ban text-slate-500"></i>
                    <span>{{ __('Excluded Controls (SoA)') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                          :class="activeTab === 'excluded' ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-600'">
                        {{ $excludedList->count() }}
                    </span>
                </button>
            </div>

            <div class="text-xs text-slate-400 font-medium hidden sm:block">
                <i class="fa-solid fa-circle-info text-slate-400 mr-1"></i> Switch tabs to view gap findings vs non-applicable controls
            </div>
        </div>

        {{-- TAB 1: Control Gap Findings Table --}}
        <div x-show="activeTab === 'gaps'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @if($criticalFindings->count() > 0)
            @php
                $highCount   = $criticalFindings->filter(fn($f) => $f->calculated_risk_priority === 'High')->count();
                $mediumCount = $criticalFindings->filter(fn($f) => $f->calculated_risk_priority === 'Medium')->count();
                $lowCount    = $criticalFindings->filter(fn($f) => $f->calculated_risk_priority === 'Low')->count();
                $maxGap      = $criticalFindings->max(fn($f) => $f->gap);
            @endphp
            <div class="bg-white rounded-3xl border border-amber-100 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-amber-100 bg-amber-500/5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-magnifying-glass-chart text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900 text-base tracking-tight">{{ __('Control Gap Findings') }}</h3>
                                <p class="text-xs text-slate-500 font-medium">{{ __('All controls with identified compliance gaps, sorted by severity') }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-black text-amber-700 bg-amber-100 border border-amber-200 px-3 py-1 rounded-full">
                            {{ $criticalFindings->count() }} {{ __('Gaps Found') }}
                        </span>
                    </div>
                    {{-- Gap Analysis Summary (inline below subtitle) --}}
                    <div class="flex items-start gap-2.5 text-xs text-slate-700 bg-white/60 border border-amber-100 rounded-2xl px-3 py-2.5">
                        <i class="fa-solid fa-circle-info text-amber-500 mt-0.5 shrink-0"></i>
                        <span>
                            <strong class="font-bold text-amber-900">{{ __('Gap Analysis Summary') }}:</strong>
                            A total of <strong class="text-slate-800 font-bold">{{ $criticalFindings->count() }} controls</strong> have identified compliance gaps (max gap: <strong class="text-rose-700 font-bold">{{ $maxGap }}</strong>) —
                            <strong class="text-rose-700 font-bold">{{ $highCount }} High Risk</strong>,
                            <strong class="text-amber-700 font-bold">{{ $mediumCount }} Medium Risk</strong>, and
                            <strong class="text-slate-600 font-bold">{{ $lowCount }} Low Risk</strong>.
                            Prioritize CAPA for all High and Medium risk controls to improve overall compliance posture.
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3.5">{{ __('Control') }}</th>
                                <th class="px-6 py-3.5">{{ __('Title') }}</th>

                                <th class="px-6 py-3.5 text-center">{{ __('Score') }}</th>
                                <th class="px-6 py-3.5 text-center">{{ __('Gap') }}</th>
                                <th class="px-6 py-3.5 text-center">{{ __('Risk Level') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('AI Analysis') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($criticalFindings as $finding)
                            @php
                                $risk = $finding->calculated_risk_priority;
                                $riskBadge = match($risk) {
                                    'High'   => 'bg-rose-600 text-white',
                                    'Medium' => 'bg-amber-100 text-amber-700',
                                    default  => 'bg-slate-100 text-slate-600',
                                };
                                $scoreBadge = $finding->maturity_rating <= 1
                                    ? 'bg-rose-100 text-rose-700'
                                    : ($finding->maturity_rating <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600');
                                $gapBadge = $finding->gap >= 4
                                    ? 'bg-rose-600 text-white'
                                    : ($finding->gap >= 3 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700');
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3.5 font-black text-slate-900">{{ $finding->standard->code ?? 'N/A' }}</td>
                                <td class="px-6 py-3.5 text-slate-700 font-medium max-w-xs truncate" title="{{ $finding->standard->title ?? '' }}">
                                    {{ \Illuminate\Support\Str::limit($finding->standard->title ?? '', 60) }}
                                </td>

                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black {{ $scoreBadge }}">
                                        {{ $finding->maturity_rating }}/5
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black {{ $gapBadge }}">
                                        -{{ $finding->gap }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $riskBadge }}">
                                        {{ $risk }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right shrink-0">
                                    @if($finding->ai_recommendation || $finding->control_insight || $finding->impact_interpretation)
                                        <button type="button"
                                            @click="openAiDetails({
                                                code: '{{ $finding->standard->code ?? '' }}',
                                                title: @js(__($finding->standard->title ?? '')),
                                                rec: @js($finding->ai_recommendation ?? ''),
                                                plan: @js(is_array($finding->corrective_action_plan) ? implode("\n", $finding->corrective_action_plan) : ($finding->corrective_action_plan ?? '')),
                                                insight: @js(is_array($finding->control_insight) ? implode("\n", $finding->control_insight) : ($finding->control_insight ?? '')),
                                                priority: @js($finding->calculated_risk_priority ?? ''),
                                                validation: @js($finding->evidence_validation ?? ''),
                                                impact: @js($finding->impact_interpretation ?? '')
                                            })"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold transition-all shadow-sm shadow-blue-600/20 active:scale-95 cursor-pointer">
                                            <i class="fa-solid fa-robot text-xs"></i>
                                            <span>{{ __('Detail AI') }}</span>
                                        </button>
                                    @else
                                        <span class="text-slate-400 font-medium text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mx-auto mb-3">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <p class="font-bold text-slate-800 text-base">{{ __('No Control Gap Findings') }}</p>
                <p class="text-slate-400 text-xs mt-1">{{ __('All applicable controls have reached maximum maturity score (Level 5).') }}</p>
            </div>
            @endif
        </div>

        {{-- TAB 2: Excluded Controls (SoA) Table --}}
        <div x-show="activeTab === 'excluded'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-ban text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-base tracking-tight">{{ __('Excluded Controls (Statement of Applicability)') }}</h3>
                            <p class="text-xs text-slate-500 font-medium">{{ __('Controls marked as Not Applicable (Excluded) with justification') }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-black {{ $excludedList->count() > 0 ? 'text-slate-700 bg-slate-200/70 border border-slate-300' : 'text-slate-400 bg-slate-100 border border-slate-200' }} px-3 py-1 rounded-full">
                        {{ $excludedList->count() }} {{ __('Excluded') }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    @if($excludedList->count() > 0)
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3.5">{{ __('Control') }}</th>
                                <th class="px-6 py-3.5">{{ __('Title') }}</th>

                                <th class="px-6 py-3.5 text-center">{{ __('Status') }}</th>
                                <th class="px-6 py-3.5">{{ __('Justification (SoA)') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($excludedList as $excluded)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3.5 font-black text-slate-900">{{ $excluded->standard->code ?? 'N/A' }}</td>
                                <td class="px-6 py-3.5 text-slate-700 font-medium max-w-xs truncate" title="{{ $excluded->standard->title ?? '' }}">
                                    {{ \Illuminate\Support\Str::limit($excluded->standard->title ?? '', 60) }}
                                </td>

                                <td class="px-6 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-wider border border-slate-200">
                                        Excluded
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-600 font-medium italic">
                                    {{ $excluded->soa_justification ?: ($excluded->notes ?: __('No justification provided')) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="p-12 text-center bg-white">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-700">{{ __('No Excluded Controls') }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ __('All controls in this audit session are marked as applicable.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Interactive Chart.js Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function initSessionDetailCharts() {
    const maturityCanvas = document.getElementById('maturityChart');
    const complianceCanvas = document.getElementById('complianceStatusChart');

    if (maturityCanvas) {
        const existingChart = Chart.getChart(maturityCanvas);
        if (existingChart) existingChart.destroy();

        new Chart(maturityCanvas, {
            type: 'bar',
            data: {
                labels: ['L1 (Initial)', 'L2 (Limited)', 'L3 (Defined)', 'L4 (Managed)', 'L5 (Optimized)'],
                datasets: [{
                    data: @json($maturityDistribution),
                    backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6'],
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, color: '#94a3b8', font: { family: "'Inter', sans-serif", size: 10 } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { color: '#64748b', font: { family: "'Inter', sans-serif", size: 9, weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    if (complianceCanvas) {
        const existingChart = Chart.getChart(complianceCanvas);
        if (existingChart) existingChart.destroy();

        new Chart(complianceCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Compliant', 'Partially Compliant', 'Non-Compliant', 'Excluded'],
                datasets: [{
                    data: [
                        {{ $stats['compliant'] }},
                        {{ $stats['partial'] }},
                        {{ $stats['non_compliant'] }},
                        {{ $stats['excluded'] }}
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { family: "'Inter', sans-serif", size: 10, weight: 'bold' }, padding: 12 }
                    }
                },
                cutout: '70%'
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSessionDetailCharts);
} else {
    initSessionDetailCharts();
}
document.addEventListener('turbo:load', initSessionDetailCharts);
</script>
@endsection
