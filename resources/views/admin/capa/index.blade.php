@extends('layouts.admin')

@section('title', 'Improvement Tracking (CAPA)')
@section('header_title', 'Improvement Tracking')

@php
    $allCapas = $allCapas ?? collect($capas->items());
@endphp

@section('content')
<div class="space-y-6 pb-16" x-data="{ viewMode: localStorage.getItem('admin_capa_view_mode') || 'kanban' }" x-init="$watch('viewMode', val => localStorage.setItem('admin_capa_view_mode', val))">

    {{-- Top Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-black text-xs rounded-xl border border-indigo-100 uppercase tracking-widest">
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
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0">
                <i class="fa-solid fa-file-excel"></i> {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    {{-- Executive Summary & Overall Remediation Health Hero Banner --}}
    @php
        $overallProgress = $totalCapa > 0 ? round(($completedCount / $totalCapa) * 100) : 0;
    @endphp
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden space-y-6">
        {{-- Background Glow Accents --}}
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
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
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-black uppercase tracking-widest">
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
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Underway</span>
            </a>

            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'completed'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-emerald-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-emerald-400 text-[10px] font-black uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-circle-check text-[9px]"></i>
                    {{ __('Completed') }}
                </div>
                <span class="text-2xl font-black text-white group-hover:scale-110 transition-transform block">{{ number_format($completedCount) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Verified & Closed</span>
            </a>

            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'overdue'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-amber-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-amber-400 text-[10px] font-black uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                    {{ __('Overdue') }}
                </div>
                <span class="text-2xl font-black text-amber-400 group-hover:scale-110 transition-transform block">{{ number_format($overdueCount) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Requires SLA Focus</span>
            </a>

            <a href="{{ route('admin.capa.index', array_merge(request()->all(), ['status' => 'excluded'])) }}"
               class="p-3.5 bg-white/10 hover:bg-white/15 border border-white/10 hover:border-slate-400/50 rounded-2xl transition-all text-center group backdrop-blur-md">
                <div class="flex items-center justify-center gap-1.5 text-slate-300 text-[10px] font-black uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-ban text-[9px]"></i>
                    {{ __('Excluded / N/A') }}
                </div>
                <span class="text-2xl font-black text-white group-hover:scale-110 transition-transform block">{{ number_format($excludedCount ?? 0) }}</span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">SoA Justification</span>
            </a>
        </div>
    </div>

    {{-- Filter Bar & View Switcher --}}
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        
        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.capa.index') }}" x-data class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 w-full flex-wrap">
            
            {{-- Search Bar --}}
            <div class="relative min-w-[240px] flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                    placeholder="Search control code, title, session, or PIC..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
            </div>

            {{-- Session Select --}}
            <select name="session_id" x-on:change="$el.closest('form').requestSubmit()" 
                    class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 max-w-xs cursor-pointer">
                <option value="">All Completed Audit Sessions</option>
                @foreach($sessions as $sess)
                    <option value="{{ $sess->id }}" {{ request('session_id') == $sess->id ? 'selected' : '' }}>
                        {{ $sess->name }}
                    </option>
                @endforeach
            </select>

            {{-- Status Select & Risk Level Select Side-by-Side --}}
            <div class="flex items-center gap-3">
                {{-- Status Select --}}
                <select name="status" x-on:change="$el.closest('form').requestSubmit()" 
                        class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="excluded" {{ request('status') == 'excluded' ? 'selected' : '' }}>Excluded / Not Applicable</option>
                </select>

                {{-- Risk Priority Select --}}
                <select name="risk" x-on:change="$el.closest('form').requestSubmit()" 
                        class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer">
                    <option value="">All Risk Levels</option>
                    <option value="High" {{ request('risk') == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Medium" {{ request('risk') == 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="Low" {{ request('risk') == 'Low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            @if(request()->hasAny(['search', 'status', 'risk', 'session_id']))
                <a href="{{ route('admin.capa.index') }}" 
                   class="px-3.5 py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1">
                    <i class="fa-solid fa-xmark text-xs"></i> Reset
                </a>
            @endif
        </form>

        {{-- View Switcher Pills (Underneath Search & Filters) --}}
        <div class="flex items-center justify-between pt-3 border-t border-slate-100 flex-wrap gap-3">
            <span class="text-xs font-bold text-slate-500">
                <i class="fa-solid fa-sliders text-indigo-500 mr-1"></i> View Mode
            </span>
            <div class="flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200 shrink-0">
                <button type="button" 
                        @click="viewMode = 'kanban'" 
                        :class="viewMode === 'kanban' ? 'bg-white text-indigo-600 shadow-sm font-black' : 'text-slate-500 font-medium hover:text-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-table-columns text-xs"></i>
                    <span>Kanban Matrix</span>
                </button>
                <button type="button" 
                        @click="viewMode = 'list'" 
                        :class="viewMode === 'list' ? 'bg-white text-indigo-600 shadow-sm font-black' : 'text-slate-500 font-medium hover:text-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-list-check text-xs"></i>
                    <span>Detailed List</span>
                </button>
            </div>
        </div>

    </div>

    {{-- KANBAN MATRIX BOARD VIEW --}}
    <div x-show="viewMode === 'kanban'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-98" x-transition:enter-end="opacity-100 scale-100">
        @php
            $kanbanColumns = [
                'open' => [
                    'title' => __('Open / Pending'),
                    'subtitle' => __('Requires action plan definition'),
                    'badge_bg' => 'bg-rose-500',
                    'col_bg' => 'bg-rose-50/30 border-rose-200/60',
                    'header_bg' => 'bg-rose-500 text-white',
                    'count_badge' => 'bg-rose-100 text-rose-800',
                    'items' => $allCapas->filter(fn($c) => $c->is_applicable && in_array($c->treatment_status ?: 'open', ['open', 'pending']))
                ],
                'in_progress' => [
                    'title' => __('In Progress'),
                    'subtitle' => __('Remediation plan actively underway'),
                    'badge_bg' => 'bg-blue-500',
                    'col_bg' => 'bg-blue-50/30 border-blue-200/60',
                    'header_bg' => 'bg-blue-600 text-white',
                    'count_badge' => 'bg-blue-100 text-blue-800',
                    'items' => $allCapas->filter(fn($c) => $c->is_applicable && $c->treatment_status === 'in_progress')
                ],
                'completed' => [
                    'title' => __('Completed & Verified'),
                    'subtitle' => __('Corrective action verified & closed'),
                    'badge_bg' => 'bg-emerald-500',
                    'col_bg' => 'bg-emerald-50/30 border-emerald-200/60',
                    'header_bg' => 'bg-emerald-600 text-white',
                    'count_badge' => 'bg-emerald-100 text-emerald-800',
                    'items' => $allCapas->filter(fn($c) => $c->is_applicable && in_array($c->treatment_status, ['completed', 'closed']))
                ],
                'excluded' => [
                    'title' => __('Excluded / N/A (SoA)'),
                    'subtitle' => __('Controls excluded with justification'),
                    'badge_bg' => 'bg-slate-500',
                    'col_bg' => 'bg-slate-100/50 border-slate-200/60',
                    'header_bg' => 'bg-slate-700 text-white',
                    'count_badge' => 'bg-slate-200 text-slate-800',
                    'items' => $excludedCapas ?? collect()
                ]
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 items-start">
            @foreach($kanbanColumns as $colKey => $col)
                <div class="rounded-3xl p-4 border shadow-sm {{ $col['col_bg'] }} space-y-3.5 backdrop-blur-xs">
                    
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between bg-white rounded-2xl p-3 border border-slate-200/70 shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full {{ $col['badge_bg'] }} shadow-xs"></span>
                            <div>
                                <h3 class="font-black text-slate-900 text-xs uppercase tracking-wider">{{ $col['title'] }}</h3>
                                <p class="text-[9px] text-slate-400 font-medium leading-none mt-0.5">{{ $col['subtitle'] }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $col['count_badge'] }}">
                            {{ $col['items']->count() }}
                        </span>
                    </div>

                    {{-- Column Cards List --}}
                    <div class="space-y-3 max-h-[850px] overflow-y-auto custom-scrollbar pr-1">
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
                                    <span class="px-2.5 py-0.5 rounded-lg bg-indigo-600 text-white font-black text-[11px] tracking-wider shadow-2xs">
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
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border bg-slate-100 text-slate-600 border-slate-200">
                                                Excluded
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Control Title --}}
                                <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2 group-hover:text-indigo-600 transition-colors">
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
                                        <div class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[9px] font-bold shrink-0 border border-indigo-200">
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
                                            <div class="h-full rounded-full transition-all duration-500 {{ in_array($status, ['completed', 'closed']) ? 'bg-emerald-500' : ($status === 'in_progress' ? 'bg-indigo-500' : 'bg-rose-500') }}" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>

                                    <a href="{{ route('admin.capa.edit', $capa) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-[10px] transition-all shadow-2xs hover:scale-105 active:scale-95 shrink-0">
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

    {{-- DETAILED LIST VIEW (SLEEK & COMPACT TABLE) --}}
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
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-black text-[11px] tracking-wider shrink-0 shadow-2xs">
                                            {{ $capa->standard->code }}
                                        </span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-slate-900 leading-snug truncate max-w-xs group-hover:text-indigo-600 transition-colors" title="{{ $capa->standard->title }}">
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
                                            <i class="fa-solid fa-user-gear text-indigo-500 text-[9px]"></i>
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
                                                {{ $status == 'in_progress' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : '' }}
                                                {{ in_array($status, ['open', 'pending']) ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                            ">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                        @endif
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 {{ !$capa->is_applicable ? 'bg-slate-400' : (in_array($status, ['completed', 'closed']) ? 'bg-emerald-500' : ($status == 'in_progress' ? 'bg-indigo-500' : 'bg-rose-500')) }}" style="width: {{ !$capa->is_applicable ? '100' : $progress }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Action --}}
                                <td class="py-3.5 px-4 text-right align-middle">
                                    <a href="{{ route('admin.capa.edit', $capa) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-[10px] transition-all shadow-2xs hover:scale-105 active:scale-95">
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

        {{-- Pagination --}}
        @if($capas->hasPages())
        <div class="p-4 bg-white rounded-3xl border border-slate-200/80 shadow-xs">
            {{ $capas->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
