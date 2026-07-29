@extends('layouts.admin')

@section('title', 'Assessment Workspace - ' . $session->name)
@section('header_title', 'Assessment Session Workspace')

@section('content')
<div class="space-y-6 pb-10" x-data="{ search: '', activeTab: 'all' }">

    {{-- Executive Header Banner Card --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm flex flex-col xl:flex-row xl:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            @php
                $backUrl = request('from') === 'show' 
                    ? route('admin.sessions.show', $session) 
                    : (request('from') === 'index' 
                        ? route('admin.sessions.index') 
                        : (url()->previous() && url()->previous() !== url()->current() ? url()->previous() : route('admin.sessions.index')));
            @endphp
            <a href="{{ $backUrl }}" 
               class="w-10 h-10 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 rounded-2xl flex items-center justify-center transition-all shrink-0 mt-0.5 shadow-xs"
               title="{{ __('Go Back') }}">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                        {{ $session->status === 'completed' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 
                           ($session->status === 'in_progress' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-slate-50 text-slate-600 border-slate-200') }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $session->status === 'completed' ? 'bg-emerald-500 animate-pulse' : ($session->status === 'in_progress' ? 'bg-blue-500 animate-pulse' : 'bg-slate-400') }}"></span>
                        {{ str_replace('_', ' ', $session->status) }}
                    </span>
                    @if($session->organization)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="fa-solid fa-building text-slate-400 text-[9px]"></i> {{ $session->organization->name }}
                        </span>
                    @endif
                    @if($session->deadline)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold {{ $session->deadline->isPast() ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                            <i class="fa-solid fa-hourglass-half text-[9px]"></i> {{ __('Deadline') }}: {{ $session->deadline->format('d M Y') }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ $session->name }}
                    </h1>
                    <a href="{{ route('admin.capa.index', ['session_id' => $session->id]) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-600/20 hover:scale-[1.02] active:scale-95 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-xs"></i> {{ __('Improvement Tracking') }}
                    </a>
                </div>

                <div class="flex items-center gap-4 text-xs text-slate-500 font-medium flex-wrap">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-shield text-slate-400"></i> PIC: <strong class="text-slate-800">{{ $session->user->name ?? 'Unknown' }}</strong></span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar text-slate-400"></i> {{ __('Created') }}: {{ $session->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Session Lockout & Deadline Read-Only Warning Banner --}}
    @if($session->isLockedForUser())
    <div class="p-5 bg-amber-50/90 border border-amber-200/90 rounded-3xl flex items-center justify-between gap-4 flex-wrap shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-sm font-bold shrink-0 shadow-sm">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest block">{{ __('Audit Session Locked / Read-Only') }}</span>
                <h4 class="text-sm font-black text-slate-900 mt-0.5">
                    {{ __('This audit session has passed its deadline or has been marked as completed.') }}
                </h4>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    {{ __('Data is stored in read-only mode. Editing is locked unless reopened by an Administrator.') }}
                </p>
            </div>
        </div>
        @if(auth()->user() && auth()->user()->isAdmin())
            <a href="{{ route('admin.sessions.edit', $session) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm shrink-0">
                <i class="fa-solid fa-lock-open text-xs"></i> {{ __('Extend Deadline / Reopen Session') }}
            </a>
        @endif
    </div>
    @endif

    {{-- Horizontal Icon-Accented KPI Stat Cards Grid (4 Cards) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {{-- Total Controls --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 border border-slate-200/80 flex items-center justify-center text-base shrink-0 font-bold">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('Total Controls') }}</div>
                <div class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total_controls'] }}</div>
            </div>
        </div>

        {{-- Total Questions --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-blue-200 hover:shadow-md transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 flex items-center justify-center text-base shrink-0 font-bold">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('Total Questions') }}</div>
                <div class="text-xl font-black text-slate-900 mt-0.5">126</div>
            </div>
        </div>

        {{-- Applicable --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-blue-200 hover:shadow-md transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/80 flex items-center justify-center text-base shrink-0 font-bold">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('Applicable') }}</div>
                <div class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['applicable'] }} / {{ $stats['total_controls'] ?? 137 }}</div>
            </div>
        </div>

        {{-- Assessed --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-sky-200 hover:shadow-md transition-all">
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 border border-sky-100/80 flex items-center justify-center text-base shrink-0 font-bold">
                <i class="fa-solid fa-square-check"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ __('Assessed') }}</div>
                <div class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['completed'] }} / {{ $stats['completed_target'] ?? $stats['applicable'] }}</div>
            </div>
        </div>
    </div>

    {{-- Executive Completion Progress Card --}}
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold border border-blue-100">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ __('Overall Assessment Completion') }}</span>
            </div>
            <span class="text-base font-black text-slate-900">{{ $stats['completion_pct'] }}%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-700 {{ $stats['completion_pct'] >= 85 ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : ($stats['completion_pct'] >= 50 ? 'bg-gradient-to-r from-amber-500 to-yellow-500' : 'bg-gradient-to-r from-rose-500 to-red-500') }}"
                 style="width: {{ $stats['completion_pct'] }}%"></div>
        </div>
    </div>

    {{-- Main ISO 27001 Checklist Table Card --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-6 p-6">
        
        {{-- Checklist Header & Search Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold border border-blue-100">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('ISO 27001:2022 Controls Checklist') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Inspection overview of all assessed Annex A controls and clause standards') }}</p>
                </div>
            </div>
            
            {{-- Control Search Input --}}
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="search" placeholder="Filter controls by code or title..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
            </div>
        </div>

        {{-- Interactive Tabs: All Controls, Main Clauses (4-10), Annex A Controls (A.5 - A.8) --}}
        <div class="flex items-center gap-2 border-b border-slate-100 pb-4 overflow-x-auto">
            <button type="button" @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-list text-[10px]"></i>
                {{ __('All Controls') }}
                <span :class="activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-black">126</span>
            </button>

            <button type="button" @click="activeTab = 'clauses'"
                    :class="activeTab === 'clauses' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-sitemap text-[10px]"></i>
                {{ __('Main Clauses (4-10)') }}
                <span :class="activeTab === 'clauses' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-black">33</span>
            </button>

            <button type="button" @click="activeTab = 'annex'"
                    :class="activeTab === 'annex' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                {{ __('Annex A Controls (A.5 - A.8)') }}
                <span :class="activeTab === 'annex' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-black">93</span>
            </button>
        </div>

        {{-- Clause Groups Container --}}
        <div class="space-y-6">
            @forelse($groupedResults as $clauseCode => $results)
                @php
                    $isAnnexA = \Illuminate\Support\Str::startsWith($clauseCode, 'A');
                @endphp
                <div class="border border-slate-200/80 rounded-2xl overflow-hidden shadow-xs"
                     x-show="(activeTab === 'all') || (activeTab === 'clauses' && !{{ $isAnnexA ? 'true' : 'false' }}) || (activeTab === 'annex' && {{ $isAnnexA ? 'true' : 'false' }})">
                    
                    {{-- Clause Header --}}
                    <div class="bg-slate-50/80 px-5 py-3.5 border-b border-slate-200/80 flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-black text-xs">
                                {{ $clauseCode }}
                            </span>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                {{ $results->first()->standard->parent?->title ?? 'Main Clause Controls' }}
                            </span>
                        </div>
                        <span class="text-[10px] font-bold bg-white text-slate-600 border border-slate-200 px-3 py-1 rounded-full shadow-xs">
                            {{ $results->count() }} {{ __('Controls') }}
                        </span>
                    </div>

                    {{-- Clause Controls Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-white text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3 w-[12%]">{{ __('Control Code') }}</th>
                                    <th class="px-5 py-3 w-[46%]">{{ __('Control Name & Standard') }}</th>
                                    <th class="px-4 py-3 w-[14%]">{{ __('Applicability') }}</th>
                                    <th class="px-4 py-3 w-[14%]">{{ __('Maturity') }}</th>
                                    <th class="px-5 py-3 w-[14%] text-right">{{ __('Compliance Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($results as $result)
                                    <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100 bg-white">
                                        <tr class="hover:bg-slate-50/80 transition-colors cursor-pointer"
                                            @click="expanded = !expanded"
                                            x-show="!search || '{{ strtolower($result->standard->code) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($result->standard->title)) }}'.includes(search.toLowerCase())">
                                            
                                            {{-- Code --}}
                                            <td class="px-5 py-4 font-black text-slate-900 text-xs">
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-90 text-blue-600': expanded }"></i>
                                                    <span class="px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200/80 text-slate-800">
                                                        {{ $result->standard->code }}
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Title --}}
                                            <td class="px-5 py-4 font-bold text-slate-800 text-xs leading-normal" title="{{ $result->standard->title }}">
                                                {{ $result->standard->title }}
                                            </td>

                                            {{-- Applicability --}}
                                            <td class="px-4 py-4">
                                                @if($result->is_applicable)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">
                                                        <i class="fa-solid fa-check text-[8px]"></i> Applicable
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold border border-slate-200">
                                                        <i class="fa-solid fa-ban text-[8px]"></i> Excluded
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Maturity Rating --}}
                                            <td class="px-4 py-4">
                                                @if(!$result->is_applicable)
                                                    <span class="text-slate-400 font-medium text-xs">—</span>
                                                @elseif($result->status !== 'completed')
                                                    <span class="text-slate-400 italic font-medium text-xs">Unassessed</span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border
                                                        {{ $result->maturity_rating == 5 ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                                        {{ $result->maturity_rating == 4 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                                        {{ $result->maturity_rating == 3 ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : '' }}
                                                        {{ $result->maturity_rating == 2 ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                                                        {{ $result->maturity_rating <= 1 ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                                    ">
                                                        <span class="w-1.5 h-1.5 rounded-full 
                                                            {{ $result->maturity_rating == 5 ? 'bg-blue-500' : '' }}
                                                            {{ $result->maturity_rating == 4 ? 'bg-emerald-500' : '' }}
                                                            {{ $result->maturity_rating == 3 ? 'bg-yellow-500' : '' }}
                                                            {{ $result->maturity_rating == 2 ? 'bg-orange-500' : '' }}
                                                            {{ $result->maturity_rating <= 1 ? 'bg-rose-500' : '' }}
                                                        "></span>
                                                        {{ $result->maturity_rating }}/5
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Compliance Status --}}
                                            <td class="px-5 py-4 text-right">
                                                @if(!$result->is_applicable)
                                                    <span class="text-slate-400 font-medium text-xs">Not Applicable</span>
                                                @elseif($result->status !== 'completed')
                                                    <span class="text-slate-400 font-medium italic text-xs">Pending</span>
                                                @else
                                                    @php
                                                        $status = $result->compliance_status;
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold
                                                        {{ $status === 'Compliant' ? 'text-emerald-600' : '' }}
                                                        {{ $status === 'Partially Compliant' ? 'text-amber-600' : '' }}
                                                        {{ $status === 'Non-Compliant' ? 'text-rose-600' : '' }}
                                                    ">
                                                        <span class="w-1.5 h-1.5 rounded-full
                                                            {{ $status === 'Compliant' ? 'bg-emerald-500' : '' }}
                                                            {{ $status === 'Partially Compliant' ? 'bg-amber-500' : '' }}
                                                            {{ $status === 'Non-Compliant' ? 'text-rose-500' : '' }}
                                                        "></span>
                                                        {{ $status }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- Expandable Detail Drawer Row --}}
                                        <tr x-show="expanded" x-transition class="bg-slate-50/90 border-t border-b border-blue-100/80">
                                            <td colspan="5" class="px-6 py-4">
                                                <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                                                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs border border-blue-100 flex items-center gap-1.5">
                                                                <i class="fa-solid fa-circle-info"></i> {{ $result->standard->code }} {{ __('Control Assessment Inspection Detail') }}
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            @if($result->is_applicable)
                                                                @php
                                                                    $matInfo = \App\Models\AssessmentSession::getMaturityLevelClassification((float)($result->maturity_rating ?? 0));
                                                                    $status = $result->compliance_status;
                                                                    $risk = $result->calculated_risk_priority;
                                                                @endphp

                                                                {{-- Maturity Classification --}}
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold border {{ $matInfo['badge_color'] }}">
                                                                    <i class="fa-solid fa-chart-line text-[9px]"></i> {{ $matInfo['name'] }}
                                                                </span>

                                                                {{-- Gap Value --}}
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold border bg-blue-50 text-blue-700 border-blue-200">
                                                                    <i class="fa-solid fa-arrows-left-right text-[9px]"></i> Gap: <strong>{{ $result->gap }}</strong>
                                                                </span>

                                                                {{-- Risk Priority --}}
                                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border
                                                                    {{ $risk === 'High' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                                                    {{ $risk === 'Medium' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                                                    {{ $risk === 'Low' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                                                ">
                                                                    {{ $risk }}
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-bold border bg-slate-100 text-slate-600 border-slate-200">
                                                                    <i class="fa-solid fa-ban text-[9px]"></i> Applicability: <strong>Excluded (Not Applicable)</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if(!$result->is_applicable)
                                                    {{-- Excluded Control Justification (SoA) --}}
                                                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed font-medium">
                                                        <div class="flex items-center gap-2 font-bold text-slate-800 text-[11px] mb-1">
                                                            <i class="fa-solid fa-file-signature text-blue-600"></i> {{ __('Control Exclusion Reason (Statement of Applicability Justification)') }}
                                                        </div>
                                                        <p class="text-slate-600 italic">
                                                            {{ $result->soa_justification ?: __('This control has been excluded from the scope of the organization\'s ISMS implementation.') }}
                                                        </p>
                                                    </div>
                                                    @else
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        {{-- Notes / Remarks --}}
                                                        <div class="space-y-1">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block flex items-center gap-1">
                                                                <i class="fa-solid fa-comment-dots text-slate-400"></i> {{ __('User Findings & Remarks') }}
                                                            </span>
                                                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs text-slate-700 font-medium leading-relaxed">
                                                                {{ $result->notes ?: __('No specific remarks recorded during assessment.') }}
                                                            </div>
                                                        </div>

                                                        {{-- Evidence Document --}}
                                                        <div class="space-y-1">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block flex items-center gap-1">
                                                                <i class="fa-solid fa-paperclip text-slate-400"></i> {{ __('Uploaded Evidence Document') }}
                                                            </span>
                                                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs text-slate-700 font-medium flex items-center justify-between">
                                                                @if($result->evidence_file)
                                                                    <a href="{{ Storage::url($result->evidence_file) }}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-blue-600 hover:underline">
                                                                        <i class="fa-solid fa-file-pdf text-rose-500"></i> View Uploaded Evidence Document
                                                                    </a>
                                                                @else
                                                                    <span class="text-slate-400 italic">No evidence document uploaded for this control</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Complete AI Compliance Synthesis Dropdown Accordion --}}
                                                    @if($result->ai_recommendation || $result->control_insight || $result->impact_interpretation || $result->corrective_action_plan)
                                                    @php
                                                        $recText = $result->ai_recommendation ?: '';
                                                        $planData = $result->corrective_action_plan;
                                                        $planText = is_array($planData) ? implode("\n", array_filter(array_map(fn($i) => is_array($i) ? implode(' ', $i) : trim((string)$i), $planData))) : ($planData ?: '');
                                                        $insightData = $result->control_insight;
                                                        $insightText = is_array($insightData) ? implode("\n", array_filter(array_map(fn($i) => is_array($i) ? implode(' ', $i) : trim((string)$i), $insightData))) : ($insightData ?: '');
                                                        $impactText = $result->impact_interpretation ?: '';
                                                    @endphp
                                                    <div class="p-4 bg-gradient-to-br from-blue-50/70 via-slate-50 to-indigo-50/30 border border-blue-200/90 rounded-2xl shadow-xs space-y-3"
                                                         x-data="{ activeAccordion: 'rec' }">
                                                        
                                                        {{-- Header --}}
                                                        <div class="flex items-center justify-between border-b border-blue-100/90 pb-3 flex-wrap gap-2">
                                                            <div class="flex items-center gap-2.5">
                                                                <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs shadow-xs">
                                                                    <i class="fa-solid fa-robot"></i>
                                                                </span>
                                                                <div>
                                                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ __('AI Compliance Synthesis') }}</h4>
                                                                    <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">{{ __('Expert Decision Support & Mitigations') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Dropdown Accordion List per Control --}}
                                                        <div class="space-y-2.5">
                                                            {{-- Accordion 1: STRATEGIC RECOMMENDATION --}}
                                                            @if($recText)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'rec' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'rec' ? null : 'rec'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'rec' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'rec' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-lightbulb text-[9px]"></i>
                                                                        </div>
                                                                        <span class="text-[10px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'rec' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('STRATEGIC RECOMMENDATION') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'rec' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'rec'" x-collapse.duration.200ms>
                                                                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $recText }}</div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            {{-- Accordion 2: CORRECTIVE ACTION PLAN --}}
                                                            @if($planText)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'cap' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'cap' ? null : 'cap'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'cap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'cap' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-list-check text-[9px]"></i>
                                                                        </div>
                                                                        <span class="text-[10px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'cap' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('CORRECTIVE ACTION PLAN') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'cap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'cap'" x-collapse.duration.200ms>
                                                                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $planText }}</div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            {{-- Accordion 3: AI AUDIT INSIGHT (GAP) --}}
                                                            @if($insightText)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'gap' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'gap' ? null : 'gap'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'gap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'gap' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                                                                        </div>
                                                                        <span class="text-[10px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'gap' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('AI AUDIT INSIGHT (GAP)') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'gap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'gap'" x-collapse.duration.200ms>
                                                                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $insightText }}</div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            {{-- Accordion 4: IMPACT INTERPRETATION --}}
                                                            @if($impactText)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'impact' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'impact' ? null : 'impact'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'impact' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'impact' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                                                                        </div>
                                                                        <span class="text-[10px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'impact' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('IMPACT INTERPRETATION') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'impact' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'impact'" x-collapse.duration.200ms>
                                                                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $impactText }}</div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-slate-500">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <p class="font-bold text-slate-700 text-sm">{{ __('No assessable ISO controls found in this session') }}</p>
                    <p class="text-slate-400 text-xs mt-1">{{ __('The audit checklist is currently empty.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
