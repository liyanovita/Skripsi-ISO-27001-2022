@extends('layouts.admin')

@section('title', 'Improvement Tracking (CAPA)')
@section('header_title', 'Improvement Tracking')

@php
    $allCapas = $allCapas ?? collect($capas->items());
@endphp

@section('content')
<div class="space-y-6 pb-16" 
     x-data="{ 
         viewMode: localStorage.getItem('admin_capa_view_mode') || 'kanban',
         showAiModal: false,
         activeAiDetails: { code: '', title: '', rec: '', plan: '', insight: '', priority: '', validation: '', impact: '' },
         openAiDetails(details) {
             this.activeAiDetails = details;
             this.showAiModal = true;
         }
     }" 
     x-init="$watch('viewMode', val => localStorage.setItem('admin_capa_view_mode', val))">

    {{-- Top Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-black text-xs rounded-xl border border-blue-100 uppercase tracking-widest">
                    ISO 27001:2022 CAPA HUB
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Improvement Tracking') }}</h1>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">
                {{ __('Monitor, assign, and verify corrective action plans (CAPA) for identified compliance gaps') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.capa.export', array_filter(['status' => request('status'), 'risk' => request('risk'), 'session_id' => request('session_id')])) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-bold shadow-md shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                <i class="fa-solid fa-file-excel"></i> {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    {{-- Executive Summary & Overall Remediation Health Hero Banner --}}
    @php
        $overallProgress = $totalCapa > 0 ? round(($completedCount / $totalCapa) * 100) : 0;
    @endphp
    <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden space-y-6">
        {{-- Background Glow Accents --}}
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-1/3 -bottom-20 w-80 h-80 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-10 bottom-0 w-40 h-40 bg-emerald-500/15 rounded-full blur-2xl pointer-events-none"></div>

        {{-- Top Section: Title & Dial --}}
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                {{-- Circular Progress Dial --}}
                <div class="relative w-20 h-20 shrink-0 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-400 transition-all duration-1000 ease-out" stroke-dasharray="{{ $overallProgress }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-lg font-black text-white leading-none">{{ $overallProgress }}%</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Closed</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px] font-black uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Remediation Health Index
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-white leading-tight">
                        Overall CAPA Completion Status
                    </h2>
                    <p class="text-xs text-slate-300 font-medium leading-relaxed max-w-lg">
                        Managing <strong class="text-white font-bold">{{ $totalCapa }}</strong> identified control gap remediations across all active audit sessions.
                    </p>
                </div>
            </div>

            <div class="hidden lg:flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md shrink-0">
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Active Remediation Rate</span>
                    <span class="text-sm font-black text-emerald-400">{{ number_format($completedCount) }} / {{ number_format($totalCapa) }} Remediated</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>

        {{-- Bottom Section: Full Width 5-Column Stat Grid --}}
        <div class="relative z-10 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 pt-5 border-t border-white/10">
            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'open'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-rose-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-rose-400 text-[10px] font-black uppercase tracking-wider mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                    {{ __('Open') }}
                </div>
                <span class="text-2xl font-black text-white group-hover:scale-110 transition-transform block">{{ number_format($openCount) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Awaiting Plan</span>
            </a>

            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'in_progress'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-blue-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-blue-400 text-[10px] font-black uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-spinner text-[9px] animate-spin"></i>
                    {{ __('In Progress') }}
                </div>
                <span class="text-2xl font-black text-white group-hover:scale-110 transition-transform block">{{ number_format($inProgressCount) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Under Implementation</span>
            </a>

            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'completed'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-emerald-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-emerald-400 text-[10px] font-black uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-check-double text-[9px]"></i>
                    {{ __('Completed') }}
                </div>
                <span class="text-2xl font-black text-white group-hover:scale-110 transition-transform block">{{ number_format($completedCount) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Verified Remediated</span>
            </a>

            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'overdue'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-amber-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-amber-400 text-[10px] font-black uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    {{ __('Overdue') }}
                </div>
                <span class="text-2xl font-black text-white group-hover:scale-110 transition-transform block">{{ number_format($overdueCount) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Past Target Date</span>
            </a>

            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'excluded'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-slate-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-slate-300 text-[10px] font-black uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-ban text-[9px]"></i>
                    {{ __('Excluded') }}
                </div>
                <span class="text-2xl font-black text-white group-hover:scale-110 transition-transform block">{{ number_format($excludedCount ?? 0) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Not Applicable (SoA)</span>
            </a>
        </div>
    </div>

    {{-- Filter Toolbar Bar & View Toggle --}}
    <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <form method="GET" action="{{ route('admin.capa.index') }}" x-data class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 w-full flex-wrap">
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="{{ __('Search control code, title, or PIC...') }}"
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
            </div>

            {{-- Audit Session Filter --}}
            <select name="session_id" @change="$el.form.submit()"
                    class="px-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs font-medium text-slate-700 focus:bg-white focus:outline-none focus:border-blue-500 transition-all">
                <option value="">{{ __('All Audit Sessions') }}</option>
                @foreach($sessions as $sess)
                    <option value="{{ $sess->id }}" {{ request('session_id') == $sess->id ? 'selected' : '' }}>
                        {{ $sess->name }} ({{ $sess->organization->name ?? 'Org' }})
                    </option>
                @endforeach
            </select>

            {{-- Risk Level Filter --}}
            <select name="risk" @change="$el.form.submit()"
                    class="px-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs font-medium text-slate-700 focus:bg-white focus:outline-none focus:border-blue-500 transition-all">
                <option value="">{{ __('All Risk Levels') }}</option>
                <option value="High" {{ request('risk') == 'High' ? 'selected' : '' }}>High Risk</option>
                <option value="Medium" {{ request('risk') == 'Medium' ? 'selected' : '' }}>Medium Risk</option>
                <option value="Low" {{ request('risk') == 'Low' ? 'selected' : '' }}>Low Risk</option>
            </select>

            {{-- Status Filter --}}
            <select name="status" @change="$el.form.submit()"
                    class="px-3.5 py-2.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs font-medium text-slate-700 focus:bg-white focus:outline-none focus:border-blue-500 transition-all">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                <option value="excluded" {{ request('status') == 'excluded' ? 'selected' : '' }}>Excluded (SoA)</option>
            </select>

            @if(request()->anyFilled(['search', 'session_id', 'risk', 'status']))
                <a href="{{ route('admin.capa.index') }}" 
                   class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl text-xs font-bold transition-all text-center">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif

            {{-- View Switcher Buttons --}}
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl border border-slate-200/80 shrink-0 ml-auto">
                <button type="button" @click="viewMode = 'kanban'" 
                        :class="viewMode === 'kanban' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 font-medium hover:text-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-all">
                    <i class="fa-solid fa-table-columns text-[11px]"></i>
                    <span class="hidden sm:inline">{{ __('Kanban') }}</span>
                </button>
                <button type="button" @click="viewMode = 'list'" 
                        :class="viewMode === 'list' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 font-medium hover:text-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-all">
                    <i class="fa-solid fa-list text-[11px]"></i>
                    <span class="hidden sm:inline">{{ __('Detailed List') }}</span>
                </button>
            </div>
        </form>
    </div>

    {{-- KANBAN BOARD VIEW (4 COLUMNS: OPEN, IN PROGRESS, COMPLETED, EXCLUDED) --}}
    @php
        $kanbanCols = [
            'open' => [
                'title' => __('Open / Awaiting Plan'),
                'subtitle' => __('Controls requiring assignment & CAPA target dates'),
                'badge_bg' => 'bg-rose-500',
                'count_badge' => 'bg-rose-100 text-rose-800',
                'items' => $allCapas->filter(fn($c) => $c->is_applicable && (in_array($c->treatment_status, ['open', null]) || $c->treatment_status == 'pending')),
            ],
            'in_progress' => [
                'title' => __('In Progress'),
                'subtitle' => __('Remediations actively being implemented'),
                'badge_bg' => 'bg-blue-500',
                'count_badge' => 'bg-blue-100 text-blue-800',
                'items' => $allCapas->filter(fn($c) => $c->is_applicable && $c->treatment_status == 'in_progress'),
            ],
            'completed' => [
                'title' => __('Completed & Verified'),
                'subtitle' => __('Controls fully remediated with target level achieved'),
                'badge_bg' => 'bg-emerald-500',
                'count_badge' => 'bg-emerald-100 text-emerald-800',
                'items' => $allCapas->filter(fn($c) => $c->is_applicable && in_array($c->treatment_status, ['completed', 'closed'])),
            ],
            'excluded' => [
                'title' => __('Excluded Controls (SoA)'),
                'subtitle' => __('Controls excluded with formal justification'),
                'badge_bg' => 'bg-slate-500',
                'count_badge' => 'bg-slate-200 text-slate-800',
                'items' => $allCapas->filter(fn($c) => !$c->is_applicable),
            ],
        ];
    @endphp

    <div x-show="viewMode === 'kanban'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-98" x-transition:enter-end="opacity-100 scale-100">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 items-start">
            @foreach($kanbanCols as $colKey => $col)
                <div class="bg-slate-100/70 border border-slate-200/80 rounded-3xl p-4 space-y-4">
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full {{ $col['badge_bg'] }} shadow-xs"></span>
                            <div>
                                <h3 class="font-black text-slate-900 text-xs uppercase tracking-wider">{{ $col['title'] }}</h3>
                                <p class="text-[9px] text-slate-400 font-medium leading-none mt-0.5">{{ $col['subtitle'] }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $col['count_badge'] }}">{{ $col['items']->count() }}</span>
                    </div>

                    {{-- Cards --}}
                    <div class="space-y-3">
                        @forelse($col['items'] as $capa)
                            @php
                                $matInfo = \App\Models\AssessmentSession::getMaturityLevelClassification((float)($capa->maturity_rating ?? 0));
                                $risk = $capa->calculated_risk_priority;
                                $planData = $capa->corrective_action_plan ?: [];
                                $existingAction = is_array($planData) ? ($planData['action'] ?? '') : (is_string($planData) ? $planData : '');
                                $actionText = !empty($existingAction) ? $existingAction : ($capa->ai_recommendation ?: 'No specific action plan recorded yet.');
                                $assignedUser = $capa->treatment_pic ?: 'Unassigned';
                                $status = $capa->treatment_status ?: 'open';
                                $progress = $capa->treatment_progress ?? 0;
                                $isOverdue = $capa->treatment_due_date && $capa->treatment_due_date->isPast() && !in_array($status, ['completed', 'closed']);
                                $hasPostEvidence = !empty($capa->evidence_after_improvement);

                                $riskBadge = match($risk) {
                                    'High' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'Medium' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                };
                            @endphp

                            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 space-y-3 group">
                                
                                {{-- Card Header: Code & Badges --}}
                                <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-600 text-white font-black text-[11px] tracking-wider shadow-2xs">
                                        {{ $capa->standard->code }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        @if($capa->is_applicable)
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border {{ $riskBadge }}">
                                                {{ $risk }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-bold border {{ $matInfo['badge_color'] }}">
                                                L{{ $matInfo['level'] }}
                                            </span>
                                            @if($capa->ai_recommendation || $capa->control_insight || $capa->impact_interpretation)
                                                <button type="button"
                                                    @click="openAiDetails({
                                                        code: '{{ $capa->standard->code }}',
                                                        title: @js(__($capa->standard->title)),
                                                        rec: @js($capa->ai_recommendation ?? ''),
                                                        plan: @js(is_array($capa->corrective_action_plan) ? implode("\n", $capa->corrective_action_plan) : ($capa->corrective_action_plan ?? '')),
                                                        insight: @js(is_array($capa->control_insight) ? implode("\n", $capa->control_insight) : ($capa->control_insight ?? '')),
                                                        priority: @js($capa->calculated_risk_priority ?? ''),
                                                        validation: @js($capa->evidence_validation ?? ''),
                                                        impact: @js($capa->impact_interpretation ?? '')
                                                    })"
                                                    class="px-2 py-0.5 rounded-md bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 font-bold text-[9px] border border-blue-200 transition-all flex items-center gap-1 cursor-pointer">
                                                    <i class="fa-solid fa-robot text-[8px]"></i> Detail AI
                                                </button>
                                            @endif
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border bg-slate-100 text-slate-600 border-slate-200">
                                                Excluded
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Control Title --}}
                                <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    {{ $capa->standard->title }}
                                </h4>

                                {{-- Corrective Action Plan or SoA Justification Box --}}
                                <div class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/70 text-[11px] space-y-0.5">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">
                                        {{ $colKey === 'excluded' || !$capa->is_applicable ? __('Justification (SoA)') : __('Action Plan') }}
                                    </span>
                                    <p class="text-slate-700 font-medium line-clamp-2 leading-relaxed text-[11px]">
                                        {{ $colKey === 'excluded' || !$capa->is_applicable ? ($capa->soa_justification ?: ($capa->notes ?: __('No justification recorded'))) : $actionText }}
                                    </p>
                                </div>

                                {{-- PIC & Due Date Meta Pills --}}
                                <div class="flex items-center justify-between gap-2 text-[10px] pt-1">
                                    <div class="flex items-center gap-1.5 truncate text-slate-600">
                                        <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[9px] font-bold shrink-0 border border-blue-200">
                                            <i class="fa-solid fa-user text-[8px]"></i>
                                        </div>
                                        <span class="truncate font-semibold text-slate-800" title="{{ $assignedUser }}">{{ $assignedUser }}</span>
                                    </div>

                                    <div class="shrink-0">
                                        @if($isOverdue)
                                            <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[9px] flex items-center gap-1">
                                                <i class="fa-solid fa-clock text-[8px]"></i> {{ $capa->treatment_due_date->format('d M') }}
                                            </span>
                                        @elseif($capa->treatment_due_date)
                                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 font-medium text-[9px] flex items-center gap-1">
                                                <i class="fa-solid fa-calendar text-[8px]"></i> {{ $capa->treatment_due_date->format('d M') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[9px] italic">No due date</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Progress & Action Footer --}}
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-3">
                                    <div class="flex-1 space-y-0.5">
                                        <div class="flex items-center justify-between text-[9px] font-bold text-slate-400">
                                            <span>Progress</span>
                                            <span class="text-slate-800">{{ $progress }}%</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 {{ in_array($status, ['completed', 'closed']) ? 'bg-emerald-500' : ($status === 'in_progress' ? 'bg-blue-500' : 'bg-rose-500') }}" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>

                                    <a href="{{ route('admin.capa.edit', $capa) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-[10px] transition-all shadow-2xs hover:scale-105 active:scale-95 shrink-0">
                                        <i class="fa-solid fa-pen-to-square text-[9px]"></i> {{ __('Manage') }}
                                    </a>
                                </div>

                            </div>
                        @empty
                            <div class="p-8 text-center bg-white/80 rounded-2xl border border-slate-200/60 shadow-2xs">
                                <i class="fa-solid fa-circle-check text-2xl text-slate-300 mb-1 block"></i>
                                <p class="text-xs font-bold text-slate-600">{{ __('No items') }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('No controls in this stage') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- DETAILED LIST VIEW --}}
    <div x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-98" x-transition:enter-end="opacity-100 scale-100" class="space-y-3">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[10px] uppercase font-black tracking-wider text-slate-400">
                            <th class="py-3.5 px-4 sm:px-6">{{ __('Control & Title') }}</th>
                            <th class="py-3.5 px-4 hidden md:table-cell">{{ __('Action Plan / SoA Justification') }}</th>
                            <th class="py-3.5 px-4 text-center hidden sm:table-cell">{{ __('Risk / Level') }}</th>
                            <th class="py-3.5 px-4">{{ __('PIC & Due Date') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Progress & Status') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($capas as $capa)
                            @php
                                $matInfo = \App\Models\AssessmentSession::getMaturityLevelClassification((float)($capa->maturity_rating ?? 0));
                                $risk = $capa->calculated_risk_priority;
                                $planData = $capa->corrective_action_plan ?: [];
                                $existingAction = is_array($planData) ? ($planData['action'] ?? '') : (is_string($planData) ? $planData : '');
                                $actionText = !empty($existingAction) ? $existingAction : ($capa->ai_recommendation ?: 'No specific action plan recorded yet.');
                                $assignedUser = $capa->treatment_pic ?: 'Unassigned';
                                $sessionLead = $capa->session->user->name ?? '';
                                $status = $capa->treatment_status ?: 'open';
                                $progress = $capa->treatment_progress ?? 0;
                                $isOverdue = $capa->treatment_due_date && $capa->treatment_due_date->isPast() && !in_array($status, ['completed', 'closed']);

                                $riskBadge = match($risk) {
                                    'High' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'Medium' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors group">
                                {{-- Control & Title --}}
                                <td class="py-3.5 px-4 sm:px-6 align-middle">
                                    <div class="flex items-start gap-2.5">
                                        <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-black text-[11px] tracking-wider shrink-0 shadow-2xs">
                                            {{ $capa->standard->code }}
                                        </span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-slate-900 leading-snug truncate max-w-xs group-hover:text-blue-600 transition-colors" title="{{ $capa->standard->title }}">
                                                {{ $capa->standard->title }}
                                            </h4>
                                            <span class="text-[10px] text-slate-400 font-medium block truncate max-w-[220px]">
                                                {{ $capa->session->name }}@if($sessionLead) <span class="text-slate-300">•</span> Lead: {{ $sessionLead }}@endif
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Action Plan --}}
                                <td class="py-3.5 px-4 hidden md:table-cell align-middle max-w-xs">
                                    <p class="text-slate-600 font-medium line-clamp-2 text-[11px] leading-relaxed bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-100">
                                        {{ !$capa->is_applicable ? ($capa->soa_justification ?: ($capa->notes ?: __('No justification recorded'))) : $actionText }}
                                    </p>
                                </td>

                                {{-- Risk / Level --}}
                                <td class="py-3.5 px-4 text-center hidden sm:table-cell align-middle shrink-0">
                                    @if($capa->is_applicable)
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border {{ $riskBadge }}">
                                                {{ $risk }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-bold border {{ $matInfo['badge_color'] }}">
                                                L{{ $matInfo['level'] }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border bg-slate-100 text-slate-600 border-slate-200">
                                            Excluded
                                        </span>
                                    @endif
                                </td>

                                {{-- PIC & Due Date --}}
                                <td class="py-3.5 px-4 align-middle">
                                    <div class="space-y-1 text-[10px]">
                                        <div class="flex items-center gap-1.5 text-slate-700 font-semibold truncate">
                                            <i class="fa-solid fa-user-gear text-blue-500 text-[9px]"></i>
                                            <span class="truncate">{{ $assignedUser }}</span>
                                        </div>
                                        <div>
                                            @if($isOverdue)
                                                <span class="text-rose-600 font-bold flex items-center gap-1 text-[9px]">
                                                    <i class="fa-solid fa-clock"></i> {{ $capa->treatment_due_date->format('d M Y') }} (Overdue)
                                                </span>
                                            @elseif($capa->treatment_due_date)
                                                <span class="text-slate-500 font-medium flex items-center gap-1 text-[9px]">
                                                    <i class="fa-solid fa-calendar"></i> {{ $capa->treatment_due_date->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[9px]">No due date</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Progress & Status --}}
                                <td class="py-3.5 px-4 text-center align-middle">
                                    <div class="inline-flex flex-col items-center gap-1 min-w-[90px]">
                                        @if(!$capa->is_applicable)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase border bg-slate-100 text-slate-600 border-slate-200">
                                                Excluded
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase border
                                                {{ in_array($status, ['completed', 'closed']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                                {{ $status == 'in_progress' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                                {{ in_array($status, ['open', 'pending']) ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                            ">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                        @endif
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 {{ !$capa->is_applicable ? 'bg-slate-400' : (in_array($status, ['completed', 'closed']) ? 'bg-emerald-500' : ($status == 'in_progress' ? 'bg-blue-500' : 'bg-rose-500')) }}" style="width: {{ !$capa->is_applicable ? '100' : $progress }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Action Column --}}
                                <td class="py-3.5 px-4 text-right align-middle shrink-0 space-x-1">
                                    @if($capa->ai_recommendation || $capa->control_insight || $capa->impact_interpretation)
                                        <button type="button"
                                            @click="openAiDetails({
                                                code: '{{ $capa->standard->code }}',
                                                title: @js(__($capa->standard->title)),
                                                rec: @js($capa->ai_recommendation ?? ''),
                                                plan: @js(is_array($capa->corrective_action_plan) ? implode("\n", $capa->corrective_action_plan) : ($capa->corrective_action_plan ?? '')),
                                                insight: @js(is_array($capa->control_insight) ? implode("\n", $capa->control_insight) : ($capa->control_insight ?? '')),
                                                priority: @js($capa->calculated_risk_priority ?? ''),
                                                validation: @js($capa->evidence_validation ?? ''),
                                                impact: @js($capa->impact_interpretation ?? '')
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 rounded-xl font-bold text-[10px] border border-blue-200 transition-all shadow-2xs cursor-pointer">
                                            <i class="fa-solid fa-robot text-[9px]"></i> {{ __('Detail AI') }}
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.capa.edit', $capa) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-[10px] transition-all shadow-2xs hover:scale-105 active:scale-95">
                                        <i class="fa-solid fa-pen-to-square text-[9px]"></i> {{ __('Manage') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-400 italic">
                                    <i class="fa-solid fa-circle-check text-2xl text-slate-300 mb-1 block"></i>
                                    <p class="font-bold text-slate-700 text-sm italic">{{ __('No remediation action items found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($capas->hasPages())
        <div class="p-4 bg-white rounded-3xl border border-slate-200/80 shadow-xs">
            {{ $capas->links() }}
        </div>
        @endif
    </div>

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

</div>
@endsection
