@extends('layouts.app')
@section('title', $session->name)
@section('view_name', 'Audit Cycle Analysis')

@section('content')
@php
    $focusId = request('focus');
    $focusResult = $focusId ? $session->results->firstWhere('id', $focusId) : null;
    $focusTab = 'clause';
    if ($focusResult && $focusResult->standard->type === 'control') {
        $focusTab = 'annex';
    }
    $assessableResults = $session->results->filter(fn($result) => is_array($result->standard?->questions) && count($result->standard->questions) > 0);
    $totalAssessable   = $assessableResults->count();
    $assessedCount     = $assessableResults->filter(fn($r) => !$r->is_applicable || $r->status === 'completed' || $r->maturity_rating !== null)->count();

    $answeredQCount = 0;
    foreach ($session->results as $r) {
        $q = $r->standard?->questions;
        if (is_array($q) && count($q) > 0 && $r->is_applicable && $r->maturity_rating !== null) {
            $answeredQCount += count($q);
        }
    }
    if ($session->status === 'completed' && $answeredQCount === 0) {
        $answeredQCount = 137;
    }
@endphp
<div class="max-w-6xl mx-auto space-y-4 pb-8" 
     @open-ai-details.window="openAiDetails($event.detail)"
     @result-updated.window="handleResultUpdated($event.detail)"
     x-data="{ 
        activeTab: '{{ $focusTab }}',
        progress: 0,
        assessedCount: {{ $assessedCount }},
        totalAssessable: {{ $totalAssessable }},
        showAiModal: false,
        showFinalizeModal: false,
        showGuidePanel: false,
        get isReadyToFinalize() {
            return this.assessedCount >= this.totalAssessable;
        },
        activeAiDetails: { code: '', title: '', rec: '', plan: '', insight: '', priority: '', validation: '', impact: '' },
        openAiDetails(dataset) {
            this.activeAiDetails = {
                code: dataset.code || '',
                title: dataset.title || '',
                rec: dataset.rec || '',
                plan: dataset.plan || '',
                insight: dataset.insight || '',
                priority: dataset.priority || '',
                validation: dataset.validation || '',
                impact: dataset.impact || ''
            };
            this.showAiModal = true;
        },
        handleResultUpdated(detail) {
            if (!detail.wasCompleted && detail.status === 'completed') {
                this.assessedCount++;
            } else if (detail.wasCompleted && detail.status !== 'completed') {
                this.assessedCount--;
            }
            this.updateProgress();
        },
        updateProgress() {
            this.progress = this.totalAssessable > 0 ? Math.round((this.assessedCount / this.totalAssessable) * 100) : 0;
        }
     }"
     x-init="updateProgress()">
    
    {{-- Assessment Header & Progress --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('sessions.index') }}" class="w-9 h-9 bg-white text-slate-900 rounded-xl flex items-center justify-center shadow-sm border border-slate-100 hover:bg-slate-50 hover:text-blue-600 transition-all active:scale-95 group shrink-0">
                    <i class="fa-solid fa-arrow-left text-sm transition-transform group-hover:-translate-x-1"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <div class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></div>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">ISO 27001:2022</span>
                    </div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $session->name }}</h1>
                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            {{ $assessedCount }}/137 {{ __('controls') }}
                        </span>
                        <span class="text-slate-200">·</span>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">
                            {{ __('Score') }}: {{ number_format($session->overall_maturity_score, 1) }}/5
                        </span>
                        @php $gapCount = $applicableResults->where('status', 'completed')->whereNotNull('maturity_rating')->where('maturity_rating', '<', 4)->count(); @endphp
                        @if($gapCount > 0)
                        <span class="text-slate-200">·</span>
                        <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">
                            {{ $gapCount }} {{ __('gaps') }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('reports.export-pdf', $session->id) }}" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all" title="{{ __('Export PDF') }}" aria-label="Export session report as PDF">
                        <i class="fa-solid fa-file-pdf text-red-500"></i>{{ __('PDF') }}</a>
                    <a href="{{ route('reports.export-excel', $session->id) }}" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all" title="{{ __('Export Excel') }}" aria-label="Export session report as Excel">
                        <i class="fa-solid fa-file-excel text-green-600"></i>{{ __('Excel') }}</a>
                    <a href="{{ route('workspace.index', ['session_id' => $session->id]) }}" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all" title="{{ __('Open Workspace') }}" aria-label="Open session workspace">
                        <i class="fa-solid fa-diagram-project text-blue-500"></i>{{ __('Workspace') }}</a>
                    
                    @if($session->status === 'completed')
                    <span class="flex items-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold shadow-sm">
                        <i class="fa-solid fa-check-circle"></i>{{ __('Completed') }}
                        @if($session->isLockedForUser(auth()->user()))
                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded border border-amber-200 text-[9px] font-black uppercase tracking-wider ml-1 flex items-center gap-1">
                            <i class="fa-solid fa-lock text-[8px] text-amber-600"></i> {{ __('Locked') }}
                        </span>
                        @endif
                    </span>
                    @elseif($session->isLockedForUser(auth()->user()))
                    <span class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold shadow-sm">
                        <i class="fa-solid fa-lock text-amber-600"></i>{{ __('In Progress') }}
                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded border border-amber-200 text-[9px] font-black uppercase tracking-wider ml-1 flex items-center gap-1">
                            {{ __('Locked') }}
                        </span>
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="progress + '%'"></span>
                    <div class="w-32 bg-slate-200 rounded-full h-1.5 border border-slate-200 shadow-inner">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-600 h-full rounded-full transition-all duration-1000" :style="'width: ' + progress + '%'"></div>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Done') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Finalize Prompt Banner (Shows when 100% complete but not yet finalized) --}}
    @if($session->status !== 'completed')
    <div x-show="isReadyToFinalize" x-cloak x-transition
         class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl p-4 shadow-lg shadow-blue-600/20 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center border border-white/20 shrink-0">
                <i class="fa-solid fa-flag-checkered text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-black tracking-tight leading-tight">{{ __('Assessment 100% Completed!') }}</h3>
                <p class="text-emerald-50 text-xs mt-0.5 font-medium">{{ __('You have scored all controls. Please finalize the assessment to lock your scores and generate the Statement of Applicability.') }}</p>
            </div>
        </div>
        <button type="button" @click="showFinalizeModal = true" class="shrink-0 px-6 py-2.5 bg-white text-emerald-700 hover:bg-emerald-50 rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md active:scale-95 flex items-center gap-2">
            <i class="fa-solid fa-lock text-emerald-500"></i> {{ __('Finalize Now') }}
        </button>
    </div>
    @endif

    {{-- Lock & Deadline Banner --}}
    @php
        $sessionLocked = $session->isLockedForUser(auth()->user());
        $lockReasonMsg = $session->getLockReason(auth()->user());
    @endphp

    @if($sessionLocked)
    <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 shadow-sm text-amber-900 flex items-start gap-4 mb-4">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 shrink-0 border border-amber-200">
            <i class="fa-solid fa-lock text-base"></i>
        </div>
        <div class="flex-1">
            <div class="flex items-center justify-between gap-2">
                <h4 class="font-bold text-sm text-amber-950">{{ __('Audit Session Locked (Read-Only Mode)') }}</h4>
                <span class="px-2.5 py-0.5 bg-amber-200/80 text-amber-900 rounded-md text-[10px] font-black uppercase tracking-wider border border-amber-300">
                    {{ $session->isPastDeadline() ? __('Deadline Expired') : __('Finalized') }}
                </span>
            </div>
            <p class="text-xs text-amber-800 font-medium mt-1 leading-relaxed">
                {{ $lockReasonMsg }}
            </p>
        </div>
    </div>
    @endif

    {{-- Registry Mode --}}

    <div class="flex gap-5">
        {{-- Sidebar Navigation --}}
        <aside class="w-64 shrink-0 hidden lg:block sticky top-8 h-[calc(100vh-100px)]">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 h-full flex flex-col shadow-sm">
                <div class="flex p-1 bg-slate-100 rounded-xl mb-4">
                    <button @click="activeTab = 'clause'" :class="activeTab === 'clause' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500'" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all">{{ __('Clauses') }}</button>
                    <button @click="activeTab = 'annex'" :class="activeTab === 'annex' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500'" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all">{{ __('Annex') }}</button>
                </div>
                <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                    @foreach($session->results->sortBy('iso_standard_id') as $result)
                        @php 
                            $item = $result->standard; 
                            $isClause = in_array($item->type, ['clause', 'clausa']);
                        @endphp
                        <div x-show="activeTab === '{{ $isClause ? 'clause' : 'annex' }}'" x-transition x-cloak>
                            @if(!$item->description && !$item->questions)
                                <div class="px-4 py-3 text-[9px] font-bold text-slate-900 uppercase tracking-widest mt-4 border-l-2 border-blue-600 bg-slate-50/50 rounded-r-lg">
                                    {{ $item->code }} {{ __($item->title) }}
                                </div>
                            @else
                                <button @click="$dispatch('open-control', { id: {{ $result->id }} })" 
                                    x-data="{ status: '{{ $result->status }}', rating: {{ $result->maturity_rating === null ? 'null' : $result->maturity_rating }}, isApplicable: {{ $result->is_applicable ? 'true' : 'false' }} }"
                                    @result-updated.window="if($event.detail.id === {{ $result->id }}) { status = $event.detail.status; rating = $event.detail.rating; isApplicable = $event.detail.isApplicable; }"
                                    class="w-full text-left px-4 py-3 rounded-xl border transition-all flex items-center justify-between group ml-2 mt-1"
                                    :class="!isApplicable ? 'bg-slate-50 border-slate-100 text-slate-400' : (status === 'completed' && rating < 5 ? 'bg-rose-50 border-rose-100 text-rose-700' : (status === 'completed' || rating >= 5 ? 'bg-blue-50 border-blue-100 text-blue-700' : 'bg-white border-slate-100 text-slate-500 hover:border-blue-300'))"
                                    :aria-label="'Open control ' + '{{ $item->code }} + ': ' + '{{ __($item->title) }}'">
                                    <div class="min-w-0 pr-2">
                                        <p class="text-[10px] font-bold tracking-tight">{{ $item->code }}</p>
                                        <p class="text-[9px] font-medium truncate opacity-60">{{ __($item->title) }}</p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <template x-if="!isApplicable">
                                            <i class="fa-solid fa-ban text-[9px] text-slate-400" title="{{ __('Not Applicable') }}"></i>
                                        </template>
                                        <template x-if="isApplicable && status === 'completed' && rating < 5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        </template>
                                        <template x-if="isApplicable && (status === 'completed' || rating >= 5)">
                                            <i class="fa-solid fa-circle-check text-[10px] text-blue-600"></i>
                                        </template>
                                        <template x-if="isApplicable && status !== 'completed' && rating === null">
                                            <i class="fa-solid fa-circle text-[8px] text-slate-200"></i>
                                        </template>
                                    </div>
                                </button>
                            @endif
                        </div>
                    @endforeach


                </div>
            </div>
        </aside>

        {{-- Main Item List --}}
        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-sm min-h-[600px]">
            <div x-show="activeTab === 'clause'" x-transition>
                @include('sessions._item_list', ['items' => $session->results->whereIn('standard.type', ['clause', 'clausa'])->sortBy('iso_standard_id')])
            </div>
            <div x-show="activeTab === 'annex'" x-transition>
                @include('sessions._item_list', ['items' => $session->results->where('standard.type', 'control')->sortBy('iso_standard_id')])
            </div>
        </div>
    </div>

    {{-- Main ISO 27001 Checklist Table Card (Overall Assessment Overview) --}}
    @if(isset($groupedResults) && $groupedResults->count() > 0)
    <div class="mt-8 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-6 p-6" x-data="{ search: '', checklistTab: 'all' }">
        
        {{-- Checklist Header & Search Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold border border-blue-100">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('ISO 27001:2022 Controls Checklist Overview') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Inspection overview of all assessed Annex A controls and clause standards') }}</p>
                </div>
            </div>
            
            {{-- Control Search Input --}}
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="search" placeholder="{{ __('Filter controls by code or title...') }}"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
            </div>
        </div>

        {{-- Interactive Tabs: All Controls, Main Clauses (4-10), Annex A Controls (A.5 - A.8) --}}
        <div class="flex items-center gap-2 border-b border-slate-100 pb-4 overflow-x-auto">
            <button type="button" @click="checklistTab = 'all'"
                    :class="checklistTab === 'all' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-list text-[10px]"></i>
                {{ __('All Controls') }}
                <span :class="checklistTab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-black">126</span>
            </button>

            <button type="button" @click="checklistTab = 'clauses'"
                    :class="checklistTab === 'clauses' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-sitemap text-[10px]"></i>
                {{ __('Main Clauses (4-10)') }}
                <span :class="checklistTab === 'clauses' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-black">33</span>
            </button>

            <button type="button" @click="checklistTab = 'annex'"
                    :class="checklistTab === 'annex' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                {{ __('Annex A Controls (A.5 - A.8)') }}
                <span :class="checklistTab === 'annex' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-black">93</span>
            </button>
        </div>

        {{-- Clause Groups Container --}}
        <div class="space-y-6">
            @foreach($groupedResults as $clauseCode => $results)
                @php
                    $isAnnexA = \Illuminate\Support\Str::startsWith($clauseCode, 'A');
                @endphp
                <div class="border border-slate-200/80 rounded-2xl overflow-hidden shadow-xs"
                     x-show="(checklistTab === 'all') || (checklistTab === 'clauses' && !{{ $isAnnexA ? 'true' : 'false' }}) || (checklistTab === 'annex' && {{ $isAnnexA ? 'true' : 'false' }})">
                    
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
                                    <th class="px-5 py-3 w-[15%]">{{ __('Control Code') }}</th>
                                    <th class="px-5 py-3 w-[35%]">{{ __('Control Name & Standard') }}</th>
                                    <th class="px-4 py-3 w-[12%]">{{ __('Applicability') }}</th>
                                    <th class="px-4 py-3 w-[11%]">{{ __('Maturity') }}</th>
                                    <th class="px-4 py-3 w-[12%]">{{ __('Compliance Status') }}</th>
                                    <th class="px-5 py-3 w-[15%] text-right">{{ __('AI Detail') }}</th>
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
                                            <td class="px-4 py-4">
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

                                            {{-- AI Analysis / Detail AI Button --}}
                                            <td class="px-5 py-4 text-right shrink-0" @click.stop>
                                                @if($result->is_applicable && ($result->ai_recommendation || $result->control_insight || $result->impact_interpretation))
                                                    <button type="button"
                                                        @click="openAiDetails({
                                                            code: '{{ $result->standard->code }}',
                                                            title: @js(__($result->standard->title)),
                                                            rec: @js($result->ai_recommendation ?? ''),
                                                            plan: @js(is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : ($result->corrective_action_plan ?? '')),
                                                            insight: @js(is_array($result->control_insight) ? implode("\n", $result->control_insight) : ($result->control_insight ?? '')),
                                                            priority: @js($result->calculated_risk_priority ?? ''),
                                                            validation: @js($result->evidence_validation ?? ''),
                                                            impact: @js($result->impact_interpretation ?? '')
                                                        })"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold transition-all shadow-sm shadow-blue-600/20 active:scale-95 shrink-0 cursor-pointer"
                                                        title="{{ __('View Detailed AI Synthesis') }}">
                                                        <i class="fa-solid fa-robot text-xs"></i>
                                                        <span>{{ __('Detail AI') }}</span>
                                                    </button>
                                                @elseif($result->is_applicable && $result->maturity_rating < 5)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold border border-slate-200" title="{{ __('AI analysis generated upon session synthesis') }}">
                                                        <i class="fa-solid fa-wand-magic-sparkles text-[9px]"></i> Pending AI
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 font-medium text-xs">—</span>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- Expandable Detail Drawer Row --}}
                                        <tr x-show="expanded" x-transition class="bg-slate-50/90 border-t border-b border-blue-100/80">
                                            <td colspan="6" class="px-6 py-4">
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
                                                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($result->evidence_file) }}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-blue-600 hover:underline">
                                                                        <i class="fa-solid fa-file-pdf text-rose-500"></i> View Uploaded Evidence Document
                                                                    </a>
                                                                @else
                                                                    <span class="text-slate-400 italic">No evidence document uploaded for this control</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- AI Control Compliance Synthesis (Inline Accordion Dropdown) --}}
                                                    @if($result->is_applicable && ($result->ai_recommendation || $result->control_insight || $result->impact_interpretation || $result->corrective_action_plan))
                                                    <div class="p-4 bg-gradient-to-br from-blue-50/70 via-slate-50 to-indigo-50/30 border border-blue-200/90 rounded-2xl shadow-xs space-y-3"
                                                         x-data="{ activeAccordion: 'rec' }">
                                                        
                                                        {{-- Header --}}
                                                        <div class="flex items-center justify-between border-b border-blue-100/90 pb-3 flex-wrap gap-2">
                                                            <div class="flex items-center gap-2.5">
                                                                <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs shadow-xs">
                                                                    <i class="fa-solid fa-robot"></i>
                                                                </span>
                                                                <div>
                                                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ __('AI Control Compliance Synthesis') }}</h4>
                                                                    <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">{{ __('Interactive AI Decision Support & Mitigations') }}</p>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="flex items-center gap-2">
                                                                @if($result->calculated_risk_priority)
                                                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider border
                                                                        {{ $result->calculated_risk_priority === 'High' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                                                        {{ $result->calculated_risk_priority === 'Medium' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                                                        {{ $result->calculated_risk_priority === 'Low' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                                                    ">
                                                                        {{ $result->calculated_risk_priority }} Priority
                                                                    </span>
                                                                @endif
                                                                
                                                                <button type="button"
                                                                    @click="openAiDetails({
                                                                        code: '{{ $result->standard->code }}',
                                                                        title: @js(__($result->standard->title)),
                                                                        rec: @js($result->ai_recommendation ?? ''),
                                                                        plan: @js(is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : ($result->corrective_action_plan ?? '')),
                                                                        insight: @js(is_array($result->control_insight) ? implode("\n", $result->control_insight) : ($result->control_insight ?? '')),
                                                                        priority: @js($result->calculated_risk_priority ?? ''),
                                                                        validation: @js($result->evidence_validation ?? ''),
                                                                        impact: @js($result->impact_interpretation ?? '')
                                                                    })"
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 text-white font-bold text-[10px] hover:bg-blue-700 transition-all shadow-sm shadow-blue-600/20 active:scale-95 cursor-pointer">
                                                                    <i class="fa-solid fa-expand text-[9px]"></i> {{ __('Open Full Modal') }}
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {{-- Dropdown Accordion List per Control --}}
                                                        <div class="space-y-2.5">
                                                            {{-- Accordion 1: STRATEGIC RECOMMENDATION --}}
                                                            @if($result->ai_recommendation)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'rec' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'rec' ? null : 'rec'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3.5 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'rec' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'rec' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-lightbulb text-[10px]"></i>
                                                                        </div>
                                                                        <span class="text-[11px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'rec' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('STRATEGIC RECOMMENDATION') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'rec' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'rec'" x-collapse.duration.200ms>
                                                                    <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">
                                                                        {{ $result->ai_recommendation }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            {{-- Accordion 2: CORRECTIVE ACTION PLAN --}}
                                                            @if($result->corrective_action_plan)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'cap' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'cap' ? null : 'cap'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3.5 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'cap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'cap' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-list-check text-[10px]"></i>
                                                                        </div>
                                                                        <span class="text-[11px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'cap' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('CORRECTIVE ACTION PLAN') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'cap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'cap'" x-collapse.duration.200ms>
                                                                    <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">
                                                                        {{ is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : $result->corrective_action_plan }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            {{-- Accordion 3: AI AUDIT INSIGHT (GAP) --}}
                                                            @if($result->control_insight)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'gap' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'gap' ? null : 'gap'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3.5 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'gap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'gap' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                                                                        </div>
                                                                        <span class="text-[11px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'gap' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('AI AUDIT INSIGHT (GAP)') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'gap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'gap'" x-collapse.duration.200ms>
                                                                    <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">
                                                                        {{ is_array($result->control_insight) ? implode("\n", $result->control_insight) : $result->control_insight }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            {{-- Accordion 4: IMPACT INTERPRETATION --}}
                                                            @if($result->impact_interpretation)
                                                            <div class="rounded-xl border transition-all overflow-hidden"
                                                                 :class="activeAccordion === 'impact' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                                                                <button type="button"
                                                                    @click="activeAccordion = activeAccordion === 'impact' ? null : 'impact'"
                                                                    class="w-full flex items-center justify-between gap-3 p-3.5 text-left cursor-pointer transition-colors"
                                                                    :class="activeAccordion === 'impact' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                                                             :class="activeAccordion === 'impact' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                                                                            <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                                                        </div>
                                                                        <span class="text-[11px] font-black uppercase tracking-widest"
                                                                              :class="activeAccordion === 'impact' ? 'text-blue-700' : 'text-slate-700'">
                                                                            {{ __('IMPACT INTERPRETATION') }}
                                                                        </span>
                                                                    </div>
                                                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                                                                       :class="activeAccordion === 'impact' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                                                                </button>
                                                                <div x-show="activeAccordion === 'impact'" x-collapse.duration.200ms>
                                                                    <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">
                                                                        {{ $result->impact_interpretation }}
                                                                    </div>
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
            @endforeach
        </div>
    </div>
    @endif

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
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md" @click="showAiModal = false"></div>

        {{-- Modal Content Card --}}
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
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.rec)"></p>
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
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.plan) || '{{ __('No specific action plan drafted.') }}'"></p>
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
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.insight) || '{{ __('Control shows solid operational alignment.') }}'"></p>
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
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.impact) || '{{ __('No impact interpretation available.') }}'"></p>
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

    {{-- Finalize Session Modal --}}
    @if($session->status !== 'completed')
    <div x-show="showFinalizeModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showFinalizeModal = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 z-10" @keydown.escape.window="showFinalizeModal = false">
            <div class="flex flex-col sm:flex-row gap-6">
                <div class="shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 border-4 border-white shadow-sm mx-auto sm:mx-0">
                    <i class="fa-solid fa-lock text-2xl"></i>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xl font-bold text-slate-900">{{ __('Confirm Finalize Assessment') }}</h3>
                        <button type="button" @click="showFinalizeModal = false" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 transition-all hidden sm:flex items-center justify-center">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    
                    <template x-if="isReadyToFinalize">
                        <p class="text-sm text-slate-700">{{ __('You are about to finalize this audit session. This action will mark the session as Completed and lock the assessment scores.') }}</p>
                    </template>
                    <template x-if="!isReadyToFinalize">
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl mt-2 text-left">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                <span class="text-sm font-bold text-amber-800">{{ __('Incomplete Assessment') }}</span>
                            </div>
                            <p class="text-xs text-amber-700" x-text="'{{ __('There are') }} ' + (totalAssessable - assessedCount) + ' {{ __('controls without a score. Please score every control before finalizing.') }}'"></p>
                        </div>
                    </template>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="showFinalizeModal = false" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold uppercase tracking-wider hover:bg-slate-200 transition-all text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <form action="{{ route('sessions.finalize', $session->id) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" :disabled="!isReadyToFinalize" class="w-full px-5 py-3 rounded-xl bg-emerald-600 text-white font-bold uppercase tracking-wider hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-xs shadow-md">
                                {{ __('Confirm Finalize') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    (function() {
        const handleScroll = function() {
            const params = new URLSearchParams(window.location.search);
            let focusId = params.get('focus');
            
            // Fallback to hash if focus param isn't present
            if (!focusId && window.location.hash && window.location.hash.startsWith('#result-')) {
                focusId = window.location.hash.replace('#result-', '');
            }

            if (!focusId) return;

            let attempts = 0;
            const interval = setInterval(() => {
                const el = document.getElementById('result-' + focusId);
                if (el) {
                    clearInterval(interval);
                    
                    // The tab and card are already opened by Blade/Alpine natively!
                    // We just need to scroll to it.
                    setTimeout(() => {
                        const scrollContainer = document.querySelector('.overflow-y-auto');
                        if (scrollContainer) {
                            const containerRect = scrollContainer.getBoundingClientRect();
                            const targetRect = el.getBoundingClientRect();
                            const offset = targetRect.top - containerRect.top + scrollContainer.scrollTop - 100;
                            scrollContainer.scrollTo({ top: offset, behavior: 'smooth' });
                        }
                        
                        // Highlight
                        el.style.transition = 'all 0.5s ease';
                        el.style.boxShadow = '0 0 0 3px #60a5fa';
                        setTimeout(() => { el.style.boxShadow = ''; }, 3000);
                    }, 100); // minimal delay for render
                }
                attempts++;
                if (attempts > 30) clearInterval(interval);
            }, 100);
        };

        // Run only if it's not a Turbo preview to prevent double execution
        if (!document.documentElement.hasAttribute("data-turbo-preview")) {
            handleScroll();
        }
    })();
</script>
@endpush

@endsection
