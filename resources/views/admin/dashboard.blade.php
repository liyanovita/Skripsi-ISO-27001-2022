@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header_title', 'System Overview')

@section('content')
<div class="space-y-8 pb-8">
    {{-- Header Banner / Command Center Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-blue-950 p-6 sm:p-8 rounded-3xl text-white shadow-2xl relative overflow-hidden border border-blue-800/40">
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
        <div class="absolute right-32 bottom-0 w-64 h-64 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2 mb-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-400"></span>
                    </span>
                    <span class="text-[10px] font-black text-blue-300 uppercase tracking-widest">{{ __('ISO 27001:2022 Command Center · System Active') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-2">
                    {{ __('Welcome back') }}, <span class="text-blue-300">Admin</span>!
                </h1>
                <p class="text-blue-200/80 text-xs sm:text-sm mt-1.5 leading-relaxed">
                    {{ __("Monitor your organization's global ISO 27001:2022 security governance, manage user accounts, oversee audit sessions, and track compliance improvements from a unified dashboard.") }}
                </p>
            </div>

            {{-- Quick Action Hub in Banner --}}
            <div class="flex flex-wrap items-center gap-2.5 shrink-0 pt-2 lg:pt-0">
                <a href="{{ route('admin.sessions.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-500 hover:to-blue-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-blue-600/30 active:scale-95 border border-blue-400/20">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>{{ __('New Session') }}</span>
                </a>
                <a href="{{ route('admin.users.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all backdrop-blur-md border border-white/15 hover:border-white/30 active:scale-95">
                    <i class="fa-solid fa-user-plus text-xs text-blue-300"></i>
                    <span>{{ __('Add User') }}</span>
                </a>
                <a href="{{ route('admin.organizations.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all backdrop-blur-md border border-white/15 hover:border-white/30 active:scale-95">
                    <i class="fa-solid fa-building-circle-check text-xs text-emerald-300"></i>
                    <span>{{ __('Add Org') }}</span>
                </a>
            </div>
        </div>
    </div>



    {{-- Top Stats KPI Grid (5 Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        {{-- Card 1: Users --}}
        <a href="{{ route('admin.users.index') }}" class="block bg-white rounded-2xl border border-slate-200/80 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Total Users') }}</h3>
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shadow-xs border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-users text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">{{ number_format($totalUsers) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] relative z-10">
                <span class="font-medium text-slate-500">{{ __('Platform Accounts') }}</span>
                @if($suspendedUsers > 0)
                <span class="px-1.5 py-0.5 bg-rose-50 text-rose-600 rounded font-bold border border-rose-100">{{ $suspendedUsers }} {{ __('blocked') }}</span>
                @else
                <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-600 rounded font-bold border border-emerald-100">{{ __('All Active') }}</span>
                @endif
            </div>
        </a>

        {{-- Card 2: Audit Sessions --}}
        <a href="{{ route('admin.sessions.index') }}" class="block bg-white rounded-2xl border border-slate-200/80 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Audit Sessions') }}</h3>
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shadow-xs border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-clipboard-list text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">{{ number_format($totalSessions) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] relative z-10">
                <span class="font-medium text-amber-600 font-bold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $activeSessions }} {{ __('In-Progress') }}
                </span>
                <span class="font-medium text-emerald-600 font-bold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $completedSessions }} {{ __('Done') }}
                </span>
            </div>
        </a>

        {{-- Card 3: Organizations --}}
        <a href="{{ route('admin.organizations.index') }}" class="block bg-white rounded-2xl border border-slate-200/80 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-teal-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Organizations') }}</h3>
                <div class="w-10 h-10 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center shadow-xs border border-teal-100 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-building text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">{{ number_format($totalOrganizations) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] relative z-10">
                <span class="font-medium text-slate-500">{{ __('Registered Tenants') }}</span>
                <span class="text-teal-600 font-bold"><i class="fa-solid fa-arrow-right text-[8px]"></i></span>
            </div>
        </a>

        {{-- Card 4: Avg Maturity (Links to Compliance Reports) --}}
        <a href="{{ route('admin.reports.index') }}" class="block bg-white rounded-2xl border border-slate-200/80 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-sky-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Avg Maturity') }}</h3>
                <div class="w-10 h-10 bg-sky-50 text-sky-600 rounded-xl flex items-center justify-center shadow-xs border border-sky-100 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-chart-pie text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10 flex items-baseline gap-1">
                {{ number_format($averageScore, 2) }} <span class="text-xs text-slate-400 font-bold">/ 5.00</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] relative z-10">
                <span class="font-medium text-slate-500">{{ __('Compliance Reports') }}</span>
                <span class="px-2 py-0.5 bg-sky-50 text-sky-700 group-hover:bg-sky-100 rounded font-black uppercase tracking-widest border border-sky-100 transition-colors">
                    @if($averageScore >= 4.5) Level 5 @elseif($averageScore >= 3.5) Level 4 @elseif($averageScore >= 2.5) Level 3 @elseif($averageScore >= 1.5) Level 2 @elseif($averageScore >= 0.5) Level 1 @else Level 0 @endif
                </span>
            </div>
        </a>

        {{-- Card 5: ISO Standards --}}
        <a href="{{ route('admin.standards.index') }}" class="block bg-white rounded-2xl border border-slate-200/80 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-2 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('ISO Standards') }}</h3>
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shadow-xs border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">
                {{ number_format($totalStandards) }} <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Items</span>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-1.5 relative z-10">
                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded text-[9px] font-bold uppercase tracking-wider">
                    {{ $mainClausesCount }} Klausul ISMS
                </span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded text-[9px] font-bold uppercase tracking-wider">
                    {{ $annexControlsCount }} Annex A
                </span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] relative z-10">
                <span class="font-medium text-slate-500">{{ __('Controls Catalog') }}</span>
                <span class="text-blue-600 font-bold"><i class="fa-solid fa-gear text-[9px]"></i></span>
            </div>
        </a>
    </div>

    {{-- Urgent Action Alerts Hub --}}
    @if($pendingCapa > 0 || $overdueCapa > 0 || $overdueSessions > 0 || $upcomingSessions > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Improvement / CAPA Alert --}}
        @if($pendingCapa > 0 || $overdueCapa > 0)
        <div class="bg-white rounded-2xl border border-rose-200 p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-rose-500"></div>
            <div class="flex items-start gap-3.5 pl-2">
                <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center shrink-0 border border-rose-100">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Improvement Action Required') }}</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ __('There are :overdue overdue tasks and :pending pending improvement tracking items across active audit sessions.', ['overdue' => $overdueCapa, 'pending' => $pendingCapa]) }}
                    </p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between pl-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded text-[9px] font-black uppercase">{{ $overdueCapa }} {{ __('Overdue') }}</span>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] font-black uppercase">{{ $pendingCapa }} {{ __('Pending') }}</span>
                </div>
                <a href="{{ route('admin.capa.index') }}" class="px-3.5 py-1.5 bg-rose-600 text-white hover:bg-rose-700 rounded-xl text-xs font-bold transition-all shadow-sm">
                    {{ __('Review Items') }} <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                </a>
            </div>
        </div>
        @endif

        {{-- Audit Deadline Alert --}}
        @if($overdueSessions > 0 || $upcomingSessions > 0)
        <div class="bg-white rounded-2xl border border-amber-200 p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-500"></div>
            <div class="flex items-start gap-3.5 pl-2">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0 border border-amber-100">
                    <i class="fa-solid fa-clock-rotate-left text-base animate-pulse"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Audit Session Deadlines Alert') }}</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ __('There are :overdue overdue audit sessions and :upcoming sessions due within the next 7 days.', ['overdue' => $overdueSessions, 'upcoming' => $upcomingSessions]) }}
                    </p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between pl-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded text-[9px] font-black uppercase">{{ $overdueSessions }} {{ __('Expired') }}</span>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] font-black uppercase">{{ $upcomingSessions }} {{ __('Due Soon') }}</span>
                </div>
                <a href="{{ route('admin.sessions.index') }}" class="px-3.5 py-1.5 bg-amber-600 text-white hover:bg-amber-700 rounded-xl text-xs font-bold transition-all shadow-sm">
                    {{ __('Manage Deadlines') }} <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                </a>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Analytics Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- User Growth Chart --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-blue-600"></i> {{ __('Platform User Registration Growth') }}
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ __('Monthly new user account registrations') }}</p>
                </div>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[9px] font-black uppercase tracking-widest border border-blue-100">{{ __('Last 6 Months') }}</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        {{-- Session Activity Chart --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-chart-bar text-emerald-600"></i> {{ __('Audit Session Creation Velocity') }}
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ __('Total ISO audit sessions created per month') }}</p>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[9px] font-black uppercase tracking-widest border border-emerald-100">{{ __('Sessions Created') }}</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="sessionActivityChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Demographics & Governance Indicators --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Maturity Level Distribution --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col">
            <h3 class="text-sm font-black text-slate-900 tracking-tight mb-1 flex items-center gap-2">
                <i class="fa-solid fa-signal text-sky-600"></i> {{ __('ISO 27001:2022 Maturity Breakdown') }}
            </h3>
            <p class="text-[11px] text-slate-400 font-medium mb-4">{{ __('Distribution across maturity ratings (Level 1-5)') }}</p>
            <div class="relative h-56 w-full flex-1">
                <canvas id="maturityChart"></canvas>
            </div>
        </div>

        {{-- Industry Sector Distribution --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col">
            <h3 class="text-sm font-black text-slate-900 tracking-tight mb-1 flex items-center gap-2">
                <i class="fa-solid fa-building-user text-teal-600"></i> {{ __('Industry Sector Demographics') }}
            </h3>
            <p class="text-[11px] text-slate-400 font-medium mb-4">{{ __('Organizations grouped by business sector') }}</p>
            @if(count($sectorDistribution) > 0)
            <div class="relative h-56 w-full flex-1">
                <canvas id="sectorChart"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-56 text-slate-400 flex-1 border border-dashed border-slate-200 rounded-xl">
                <i class="fa-solid fa-chart-pie text-3xl mb-2 text-slate-300"></i>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('No sector data registered yet') }}</p>
            </div>
            @endif
        </div>

        {{-- Governance Indicators Summary --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
            <h3 class="text-sm font-black text-slate-900 tracking-tight mb-1 flex items-center gap-2">
                <i class="fa-solid fa-cubes text-blue-600"></i> {{ __('Governance Indicators') }}
            </h3>
            <p class="text-[11px] text-slate-400 font-medium mb-4">{{ __('Key system metrics summary') }}</p>
            
            <div class="divide-y divide-slate-100">
                @php
                    $indicators = [
                        ['label' => __('Total User Accounts'), 'value' => $totalUsers, 'icon' => 'fa-users', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                        ['label' => __('Audit Sessions'), 'value' => $totalSessions, 'icon' => 'fa-clipboard-list', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                        ['label' => __('Registered Organizations'), 'value' => $totalOrganizations, 'icon' => 'fa-building', 'color' => 'text-teal-600', 'bg' => 'bg-teal-50'],
                        ['label' => __('Knowledge Base Articles'), 'value' => $totalArticles, 'icon' => 'fa-book-open', 'color' => 'text-sky-600', 'bg' => 'bg-sky-50'],
                        ['label' => __('Pending Improvement Tasks'), 'value' => $pendingCapa, 'icon' => 'fa-clock', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                        ['label' => __('Overdue Improvement Tasks'), 'value' => $overdueCapa, 'icon' => 'fa-triangle-exclamation', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50'],
                    ];
                @endphp
                @foreach($indicators as $ind)
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-slate-600 flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg {{ $ind['bg'] }} {{ $ind['color'] }} flex items-center justify-center shrink-0 border border-slate-100">
                            <i class="fa-solid {{ $ind['icon'] }} text-[10px]"></i>
                        </div>
                        {{ $ind['label'] }}
                    </span>
                    <span class="text-xs font-black text-slate-900">{{ number_format($ind['value']) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Activity Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Users --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-blue-600"></i> {{ __('Recent User Accounts') }}
                    </h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ __('Newly registered platform users') }}</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all border border-blue-100">
                    {{ __('View All Users') }}
                </a>
            </div>
            <div class="divide-y divide-slate-100 flex-1">
                @forelse($recentUsers as $user)
                <a href="{{ route('admin.users.show', $user) }}" class="p-4 hover:bg-slate-50/80 flex items-center justify-between transition-colors block group">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-slate-900 group-hover:text-blue-600 transition-colors truncate">{{ $user->name }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="text-xs font-medium text-slate-400 text-right shrink-0">
                        <div class="text-[10px] text-slate-400">{{ $user->created_at->diffForHumans() }}</div>
                        @if($user->organization)
                        <div class="text-[8px] font-black uppercase tracking-widest text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md mt-1 inline-block">{{ $user->organization->name }}</div>
                        @endif
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs py-16 flex flex-col items-center justify-center">
                    <i class="fa-regular fa-user-circle text-3xl mb-2 text-slate-300"></i>
                    <p class="font-bold uppercase tracking-widest">{{ __('No users registered yet.') }}</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Audit Activity --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> {{ __('Recent Audit Activity') }}
                    </h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ __('Latest updated assessment sessions') }}</p>
                </div>
                <a href="{{ route('admin.sessions.index') }}" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all border border-emerald-100">
                    {{ __('View All Sessions') }}
                </a>
            </div>
            <div class="divide-y divide-slate-100 flex-1">
                @forelse($recentSessions as $session)
                <a href="{{ route('admin.sessions.show', $session) }}" class="p-4 hover:bg-slate-50/80 block transition-colors group">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-bold text-sm text-slate-900 group-hover:text-emerald-600 transition-colors truncate max-w-[220px]">{{ $session->name }}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $session->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : ($session->status === 'in_progress' ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                            {{ str_replace('_', ' ', $session->status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-5 h-5 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 text-[8px] font-bold">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span class="truncate text-[10px] font-bold text-slate-600">{{ $session->user->name ?? __('Unknown User') }}</span>
                        </div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $session->updated_at->diffForHumans() }}</div>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs py-16 flex flex-col items-center justify-center">
                    <i class="fa-regular fa-clipboard text-3xl mb-2 text-slate-300"></i>
                    <p class="font-bold uppercase tracking-widest">{{ __('No recent audit sessions.') }}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function initAdminDashboardCharts() {
    if (typeof Chart === 'undefined') return;
    // Style chart tooltips and fonts globally
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 10;
    Chart.defaults.color = '#94a3b8';

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 12,
                titleFont: { size: 11, weight: 'bold' },
                bodyFont: { size: 10 },
                cornerRadius: 10,
                displayColors: false
            }
        }
    };

    // Helper to safely initialize and destroy existing charts (Turbo.js fix)
    const initChart = (id, config) => {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }
        
        new Chart(canvas, config);
    };

    // User Growth Line Chart
    initChart('userGrowthChart', {
        type: 'line',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                label: 'New Users',
                data: @json($userGrowthData),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 2.5,
                pointBackgroundColor: '#2563eb',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#2563eb',
                pointHoverBorderWidth: 3,
                pointHoverRadius: 6,
                pointRadius: 3,
            }]
        },
        options: { 
            ...chartDefaults, 
            scales: { 
                y: { 
                    grid: { color: '#f1f5f9' },
                    border: { dash: [5, 5] },
                    beginAtZero: true, 
                    ticks: { stepSize: 1 } 
                },
                x: {
                    grid: { display: false }
                }
            } 
        }
    });

    // Session Activity Bar Chart
    initChart('sessionActivityChart', {
        type: 'bar',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                label: 'Sessions Created',
                data: @json($sessionActivityData),
                backgroundColor: '#10b981',
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 28,
            }]
        },
        options: { 
            ...chartDefaults, 
            scales: { 
                y: { 
                    grid: { color: '#f1f5f9' },
                    border: { dash: [5, 5] },
                    beginAtZero: true, 
                    ticks: { stepSize: 1 } 
                },
                x: {
                    grid: { display: false }
                }
            } 
        }
    });

    // Maturity Distribution Doughnut Chart
    initChart('maturityChart', {
        type: 'doughnut',
        data: {
            labels: ['Lvl 1 (Initial)', 'Lvl 2 (Limited/Repeatable)', 'Lvl 3 (Defined)', 'Lvl 4 (Managed)', 'Lvl 5 (Optimized)'],
            datasets: [{
                data: @json($maturityDistribution),
                backgroundColor: ['#ef4444', '#f97316', '#eab308', '#10b981', '#2563eb'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { 
                    display: true, 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 8, 
                        boxHeight: 8, 
                        padding: 10,
                        usePointStyle: true, 
                        pointStyle: 'circle',
                        font: { size: 9, weight: 'bold' } 
                    } 
                }
            }
        }
    });

    // Sector Distribution Pie Chart
    @if(count($sectorDistribution) > 0)
    initChart('sectorChart', {
        type: 'pie',
        data: {
            labels: @json(array_keys($sectorDistribution)),
            datasets: [{
                data: @json(array_values($sectorDistribution)),
                backgroundColor: ['#2563eb', '#3b82f6', '#0d9488', '#e11d48', '#d97706', '#0284c7'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true, 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 8, 
                        boxHeight: 8, 
                        padding: 10,
                        usePointStyle: true, 
                        pointStyle: 'circle',
                        font: { size: 9, weight: 'bold' } 
                    } 
                }
            }
        }
    });
    @endif
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboardCharts);
} else {
    initAdminDashboardCharts();
}
document.addEventListener('turbo:load', initAdminDashboardCharts);
</script>
@endsection
