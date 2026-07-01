@extends('layouts.app')

@section('title', 'Dashboard')
@section('view_name', 'Dashboard')

@push('head_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-16" x-data="{ showNewModal: false }">

    @if(!$hasData)
    {{-- Empty State --}}
    <div class="p-16 text-center bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-900">{{ __('No Assessment Data Yet') }}</h3>
        <p class="text-slate-500 mt-1 max-w-sm mx-auto font-medium text-sm">{{ __('Start your first ISO 27001:2022 audit session to see compliance insights here.') }}</p>
        <a href="{{ route('sessions.index') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg active:scale-95">
            <i class="fa-solid fa-plus"></i>{{ __('Start First Session') }}</a>
    </div>
    @else

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></div>
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">ISO 27001:2022 DSS</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3 flex-wrap">
                <span>{{ __('Welcome Back') }}, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 font-black">{{ auth()->user()->name }}</span>!</span>
                @if(isset($assessorBadge))
                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 {{ $assessorBadge['color'] }} shadow-sm">
                    <i class="fa-solid {{ $assessorBadge['icon'] }}"></i> {{ $assessorBadge['title'] }}
                </span>
                @endif
            </h1>
            <p class="text-sm text-slate-500 font-medium mt-0.5">{{ __('Here is the aggregate overview of your ISO 27001:2022 compliance posture.') }}</p>
        </div>
    </div>


    {{-- Stats Grid --}}
    <div id="dashboard-kpi-grid" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Overall Compliance --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Overall Compliance') }}</p>
                    <h3 class="text-3xl font-bold text-blue-600 tracking-tight">{{ $complianceScore }}%</h3>
                </div>
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            @if($complianceScore >= 80)
                <div class="mt-3 flex items-center text-[10px] font-bold text-emerald-600 bg-emerald-50 w-fit px-2 py-0.5 rounded-lg border border-emerald-100">
                    <i class="fa-solid fa-shield-check mr-1.5"></i>{{ __('Optimal Posture') }}
                </div>
            @elseif($complianceScore >= 50)
                <div class="mt-3 flex items-center text-[10px] font-bold text-amber-600 bg-amber-50 w-fit px-2 py-0.5 rounded-lg border border-amber-100">
                    <i class="fa-solid fa-shield-halved mr-1.5"></i>{{ __('Needs Improvement') }}
                </div>
            @else
                <div class="mt-3 flex items-center text-[10px] font-bold text-rose-600 bg-rose-50 w-fit px-2 py-0.5 rounded-lg border border-rose-100">
                    <i class="fa-solid fa-shield-virus mr-1.5"></i>{{ __('Critical Risk') }}
                </div>
            @endif
        </div>



        {{-- High Priority Gaps --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Priority Gaps') }}</p>
                    <h3 class="text-3xl font-bold text-orange-600 tracking-tight">{{ $totalGaps }}</h3>
                </div>
                <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center border border-orange-100 group-hover:bg-orange-600 group-hover:text-white transition-all">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-lg border border-rose-100 text-[9px] font-black uppercase tracking-widest">
                    {{ $criticalGapCount }} Critical
                </span>
                <span class="px-2 py-0.5 bg-orange-50 text-orange-600 rounded-lg border border-orange-100 text-[9px] font-black uppercase tracking-widest">
                    {{ $highGapCount }} High
                </span>
            </div>
        </div>

        {{-- Maturity Level --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all group flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Maturity Level') }}</p>
                    <h3 class="text-xl font-bold text-purple-600 tracking-tight leading-tight">{{ $statusKematangan }}</h3>
                </div>
                <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center border border-purple-100 group-hover:bg-purple-600 group-hover:text-white transition-all">
                    <i class="fa-solid fa-gauge"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest mb-1.5">
                    <span class="text-slate-400">{{ __('Score') }}</span>
                    <span class="text-purple-600">{{ number_format($averageMaturity, 2) }}/5</span>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-purple-500 rounded-full" style="width: {{ min(($averageMaturity / 5) * 100, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resume Assessment Hero Banner --}}
    <div id="dashboard-resume-banner" class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-4 shadow-lg shadow-blue-900/20 text-white flex flex-col sm:flex-row items-center justify-between gap-3 relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-40 h-40 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex items-center gap-4 flex-1 min-w-0">
            <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center border border-white/20 shrink-0">
                <i class="fa-solid fa-rocket text-sm"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-sm font-black tracking-tight leading-tight">{{ $latestSession->name }}</h2>
                <div class="flex items-center gap-3 mt-1">
                    <div class="flex items-center gap-1.5">
                        <div class="w-24 bg-white/20 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-white h-full rounded-full" style="width: {{ $activeSessionProgress }}%"></div>
                        </div>
                        <span class="text-[10px] font-bold text-blue-100">{{ $activeSessionProgress }}%</span>
                    </div>
                    <span class="text-blue-200 text-[10px] font-medium hidden sm:block">{{ $activeSessionAnswered }}/{{ $totalIsoControls }} {{ __('controls assessed') }}</span>
                </div>
            </div>
        </div>
        <div class="relative z-10 shrink-0 flex items-center gap-2">
            <span class="text-[10px] font-bold text-blue-200 hidden md:block">{{ $completedCycles }} {{ __('completed cycles') }}</span>
            <a href="{{ route('sessions.show', $latestSession->id) }}" class="flex items-center gap-2 px-5 py-2.5 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                {{ __('Continue') }}
                <i class="fa-solid fa-arrow-right-long"></i>
            </a>
        </div>
    </div>

    {{-- Audit Sessions Portfolio --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mt-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight">{{ __('Audit Sessions Portfolio') }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $allSessions->count() }} {{ __('Sessions Total') }}</p>
                </div>
            </div>
            <a href="{{ route('sessions.index') }}" class="text-[10px] font-bold text-blue-600 hover:underline uppercase tracking-widest">
                {{ __('Manage All') }} →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($allSessions->take(3) as $session)
            <a href="{{ route('sessions.show', $session->id) }}" class="block p-4 bg-slate-50 border border-slate-200 rounded-xl hover:border-blue-300 hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-2">
                    <h4 class="font-bold text-slate-900 text-sm group-hover:text-blue-600 transition-colors">{{ $session->name }}</h4>
                    @php
                        $statusLabel = match($session->status) {
                            'completed'   => __('Completed'),
                            'in_progress' => __('In Progress'),
                            default       => __('In Progress'),
                        };
                        $statusColor = match($session->status) {
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            default     => 'bg-blue-100 text-blue-700',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <div class="flex items-center justify-between mt-4">
                    @if($session->status === 'completed')
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-black text-slate-800">{{ number_format(($session->overall_maturity_score / 5) * 100) }}%</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Score</span>
                        </div>
                    @else
                        <div class="flex-1 mr-3 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-400 h-full rounded-full w-1/2"></div>
                        </div>
                    @endif
                    <span class="text-[9px] font-bold text-slate-400"><i class="fa-regular fa-clock mr-1"></i>{{ $session->updated_at->diffForHumans() }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Compliance Trend - Full Width --}}
    <div class="mt-5 mb-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-4">{{ __('Compliance Trend over Time') }}</h3>
            <div class="relative w-full" style="height: 280px;">
                @if(count($trendData['data']) >= 1)
                    <canvas id="complianceTrendChart" style="display:block; width:100%; height:280px;"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-center">
                        <i class="fa-solid fa-chart-line text-3xl text-slate-200 mb-2"></i>
                        <p class="text-xs font-bold text-slate-400">{{ __('No completed sessions yet') }}</p>
                        <p class="text-[10px] text-slate-400 mt-1">{{ __('Complete an audit session to see the trend.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Analytics & Insights Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

        {{-- Compliance Radar Chart --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col h-full min-h-[300px]">
            <h3 class="text-sm font-bold text-slate-900 mb-4 shrink-0">{{ __('Global Maturity by Domain') }}</h3>
            <div class="flex-1 relative w-full flex items-center justify-center">
                @if(isset($radarData) && count($radarData['data']) >= 1 && array_sum($radarData['data']) > 0)
                    <canvas id="complianceRadarChart" style="display:block; width:100%; height:220px;"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-center">
                        <i class="fa-solid fa-spider text-3xl text-slate-200 mb-2"></i>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('No Data Yet') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Executive Summary --}}
        <div class="relative bg-gradient-to-br from-indigo-600 to-blue-800 rounded-2xl p-5 shadow-lg shadow-blue-900/20 text-white overflow-hidden flex flex-col" style="min-height: 300px; max-height: 340px;">
            <div class="absolute -right-8 -top-8 w-28 h-28 bg-white/10 rounded-full blur-3xl"></div>

            <div class="flex items-center gap-3 mb-4 relative z-10 shrink-0">
                <h3 class="text-sm font-bold tracking-tight">{{ __('Compliance & Maturity Overview') }}</h3>
                <span class="ml-auto px-2 py-0.5 bg-blue-500/50 text-blue-100 rounded-lg text-[9px] font-black uppercase tracking-widest border border-blue-400/30">Auto Generated</span>
            </div>

            <div class="relative z-10 flex-1 flex flex-col gap-3 min-h-0 overflow-hidden">
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/10 overflow-y-auto flex-1
                            [&::-webkit-scrollbar]:w-1.5
                            [&::-webkit-scrollbar-track]:bg-white/10
                            [&::-webkit-scrollbar-track]:rounded-full
                            [&::-webkit-scrollbar-thumb]:bg-white/30
                            [&::-webkit-scrollbar-thumb]:rounded-full
                            [&::-webkit-scrollbar-thumb:hover]:bg-white/50">
                    <p class="text-sm font-medium leading-relaxed">{!! $executiveSummary !!}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Community Spotlight & Audit Trail Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        {{-- Community Spotlight Widget --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col h-full" style="min-height: 280px; max-height: 340px;">
            <div class="flex items-center justify-between mb-4 shrink-0">
                <h3 class="text-sm font-bold text-slate-900">{{ __('Community Spotlight') }}</h3>
                <a href="{{ route('community.index') }}" class="text-[9px] font-bold text-blue-600 hover:underline uppercase tracking-widest">{{ __('Explore') }}</a>
            </div>
            <div class="flex-1 flex flex-col gap-3 overflow-y-auto pr-1
                        [&::-webkit-scrollbar]:w-1
                        [&::-webkit-scrollbar-track]:bg-slate-50
                        [&::-webkit-scrollbar-track]:rounded-full
                        [&::-webkit-scrollbar-thumb]:bg-slate-200
                        [&::-webkit-scrollbar-thumb]:rounded-full">
                @forelse($topTemplates ?? [] as $template)
                    <a href="{{ route('community.preview', $template->id) }}" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-blue-50 rounded-xl border border-slate-100 transition-colors group">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-file-shield text-[10px]"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold text-slate-900 truncate group-hover:text-blue-700 transition-colors">{{ $template->title }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest"><i class="fa-solid fa-arrow-up text-emerald-500 mr-0.5"></i>{{ $template->upvotes }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest"><i class="fa-solid fa-star text-amber-400 mr-0.5"></i>{{ $template->avg_rating }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-center opacity-50">
                        <i class="fa-solid fa-users text-2xl text-slate-300 mb-2"></i>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('No Templates Yet') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Live Audit Trail Widget --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col h-full" style="min-height: 280px; max-height: 340px;">
            <div class="flex items-center justify-between mb-4 shrink-0">
                <h3 class="text-sm font-bold text-slate-900">{{ __('Live Audit Trail') }}</h3>
                <a href="{{ route('audit-trail.index') }}" class="text-[9px] font-bold text-blue-600 hover:underline uppercase tracking-widest">{{ __('View All') }}</a>
            </div>
            <div class="flex-1 flex flex-col gap-3 overflow-y-auto pr-1
                        [&::-webkit-scrollbar]:w-1
                        [&::-webkit-scrollbar-track]:bg-slate-50
                        [&::-webkit-scrollbar-track]:rounded-full
                        [&::-webkit-scrollbar-thumb]:bg-slate-200
                        [&::-webkit-scrollbar-thumb]:rounded-full">
                @forelse($recentAuditTrails ?? [] as $trail)
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center border border-slate-200 text-slate-400 shrink-0 shadow-sm">
                            <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold text-slate-900 truncate">{{ $trail->model?->standard?->code ?? 'N/A' }} <span class="text-slate-400 font-medium ml-1">updated</span></p>
                            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">{{ str_replace('_', ' ', Str::title($trail->field_changed)) }}</p>
                            <p class="text-[10px] text-slate-500 mt-1 truncate flex items-center gap-1.5">
                                <span class="line-through text-rose-400">{{ $trail->old_value }}</span> 
                                <i class="fa-solid fa-arrow-right text-[8px] text-slate-300"></i> 
                                <span class="text-emerald-600 font-bold bg-emerald-50 px-1 rounded">{{ $trail->new_value }}</span>
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-center opacity-50">
                        <i class="fa-solid fa-history text-2xl text-slate-300 mb-2"></i>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('No Recent Activity') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>



    {{-- Remediation Tracker --}}
    @if(isset($activeTasks) && $activeTasks->count() > 0)
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center border border-indigo-100">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight">{{ __('Active Remediation Tasks') }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('CAPA Tracking') }}</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 text-[9px] font-bold rounded-lg uppercase tracking-widest">
                {{ $activeTasks->count() }} Active
            </span>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[350px] border border-slate-100 rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-white shadow-sm z-10">
                    <tr class="border-b border-slate-100">
                        <th class="py-2.5 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Control') }}</th>
                        <th class="py-2.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Gap Level') }}</th>
                        <th class="py-2.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('PIC') }}</th>
                        <th class="py-2.5 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">{{ __('Deadline') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($activeTasks as $task)
                    @php
                        $isOverdue = $task->treatment_due_date->isPast();
                        $daysLeft = $task->treatment_due_date->startOfDay()->diffInDays(now()->startOfDay());
                    @endphp
                    <tr class="hover:bg-blue-50/50 transition-colors cursor-pointer group" onclick="window.location='{{ route('workspace.index', ['session_id' => $task->session_id, 'focus' => $task->id]) }}'">
                        <td class="py-3 px-3">
                            <span class="font-bold text-slate-900 text-xs group-hover:text-blue-600 transition-colors">{{ $task->standard->code }}</span>
                            <p class="text-[10px] text-slate-500 font-medium truncate max-w-[180px]">{{ $task->standard->title }}</p>
                            <p class="text-[9px] font-bold text-indigo-500 bg-indigo-50 w-fit px-1.5 py-0.5 rounded mt-1 truncate max-w-[180px]" title="{{ $task->session->name ?? 'Unknown Session' }}">{{ $task->session->name ?? 'Unknown Session' }}</p>
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest {{ 
                                $task->risk_level === 'Critical' ? 'bg-rose-100 text-rose-700' : (
                                $task->risk_level === 'High' ? 'bg-orange-100 text-orange-700' : (
                                $task->risk_level === 'Medium' ? 'bg-amber-100 text-amber-700' : (
                                $task->risk_level === 'Low' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'
                            ))) }}">
                                {{ $task->risk_level }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[8px] font-bold uppercase shrink-0">
                                    {{ substr($task->treatment_pic, 0, 2) }}
                                </div>
                                <span class="text-xs font-bold text-slate-700 truncate max-w-[100px]">{{ $task->treatment_pic }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-right">
                            <span class="text-xs font-bold {{ $isOverdue && $daysLeft > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                                {{ $task->treatment_due_date->format('d M Y') }}
                            </span>
                            <p class="text-[9px] font-bold uppercase tracking-widest {{ $isOverdue && $daysLeft > 0 ? 'text-rose-400' : 'text-slate-400' }}">
                                @if($isOverdue && $daysLeft > 0) Overdue {{ $daysLeft }}d
                                @elseif($daysLeft == 0) Due today
                                @else {{ $daysLeft }}d left
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @else
    {{-- Empty Remediation State --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center border border-indigo-100">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">{{ __('Remediation Tasks') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('CAPA Tracking') }}</p>
            </div>
        </div>
        <div class="py-6 text-center">
            <div class="w-10 h-10 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-circle-check text-lg text-emerald-400"></i>
            </div>
            <p class="text-sm font-bold text-slate-500">{{ __('No active remediation tasks') }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ __('Great job! All assessed controls are compliant.') }}</p>
        </div>
    </div>
    @endif

    @endif {{-- end if latestSession --}}

    {{-- New Session Modal (Alpine.js) --}}
    <div x-show="showNewModal" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showNewModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 relative z-10 border border-slate-100"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-11 h-11 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20">
                    <i class="fa-solid fa-plus text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('New Audit Session') }}</h3>
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mt-0.5">{{ __('Initialize Assessment') }}</p>
                </div>
            </div>
            <form action="{{ route('sessions.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5 ml-1">{{ __('Session Name') }}</label>
                    <input type="text" name="name" required placeholder="{{ __('e.g., ISO Audit Q3 2026') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all">
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showNewModal = false"
                        class="flex-1 px-5 py-2.5 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">{{ __('Cancel') }}</button>
                    <button type="submit"
                        class="flex-1 px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20">{{ __('Initialize') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>

@if($latestSession ?? false)
@push('scripts')
<script>
window.chartInstances = window.chartInstances || {};

// Waits until Chart.js is available, then runs the callback
window.waitForChartJs = function(callback, retries) {
    retries = retries === undefined ? 20 : retries;
    if (window.Chart) {
        callback();
    } else if (retries > 0) {
        setTimeout(function() {
            window.waitForChartJs(callback, retries - 1);
        }, 150);
    }
};

window.initDashboardCharts = function() {
    window.waitForChartJs(function() {

    // Destroy existing instances to prevent duplicates or rendering glitches
    ['complianceTrendChart', 'complianceRadarChart'].forEach(id => {
        if (window.chartInstances[id]) {
            window.chartInstances[id].destroy();
            delete window.chartInstances[id];
        }
    });

    // Trend Chart
    const trendCtx = document.getElementById('complianceTrendChart');
    if (trendCtx) {
        const trendData = @json($trendData);
        if (trendData.labels && trendData.labels.length > 0) {
            window.chartInstances['complianceTrendChart'] = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [{
                        label: 'Compliance Score (%)',
                        data: trendData.data,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ` Score: ${ctx.parsed.y}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 0,
                            max: 100,
                            ticks: {
                                font: { size: 10 },
                                callback: function(value) { return value + '%'; }
                            },
                            grid: { color: 'rgba(0,0,0,0.04)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 9, weight: 'bold' },
                                maxRotation: 35,
                                minRotation: 0,
                                autoSkip: false,
                                callback: function(val, idx) {
                                    const label = this.getLabelForValue(val);
                                    return label && label.length > 14 ? label.slice(0, 13) + '…' : label;
                                }
                            }
                        }
                    },
                    animation: { duration: 800 }
                }
            });
        }
    }

    // Radar Chart
    const radarCtx = document.getElementById('complianceRadarChart');
    if (radarCtx) {
        const radarData = @json($radarData ?? ['labels' => [], 'data' => []]);
        if (radarData.labels && radarData.labels.length > 0) {
            window.chartInstances['complianceRadarChart'] = new Chart(radarCtx, {
                type: 'radar',
                data: {
                    labels: radarData.labels,
                    datasets: [{
                        label: 'Avg Maturity',
                        data: radarData.data,
                        backgroundColor: 'rgba(99, 102, 241, 0.2)', // Indigo
                        borderColor: '#6366f1',
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#6366f1',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { color: 'rgba(0,0,0,0.1)' },
                            grid: { color: 'rgba(0,0,0,0.1)' },
                            pointLabels: {
                                font: { size: 9, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#64748b'
                            },
                            ticks: {
                                min: 0,
                                max: 5,
                                stepSize: 1,
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ` Maturity: ${ctx.parsed.r}/5`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    }); // end waitForChartJs
};

document.addEventListener('DOMContentLoaded', window.initDashboardCharts);
document.addEventListener('turbo:load', window.initDashboardCharts);
document.addEventListener('turbo:render', window.initDashboardCharts);
</script>
@endpush
@endif
@endsection
