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
    $assessedCount     = $assessableResults->filter(fn($r) => !$r->is_applicable || $r->status === 'completed')->count();

    $answeredQCount = 0;
    foreach ($session->results as $r) {
        $q = $r->standard?->questions;
        if (is_array($q) && count($q) > 0 && $r->is_applicable && $r->maturity_rating !== null) {
            $answeredQCount += count($q);
        }
    }
    if ($session->status === 'completed' && $answeredQCount === 0) {
        $answeredQCount = $totalAssessable ?: 122;
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

            if (this.isReadyToFinalize && !detail.wasCompleted) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ addslashes(__('Assessment 100% Completed!')) }}',
                    html: '<p class="text-sm text-slate-600 font-medium leading-relaxed">{{ addslashes(__('Congratulations! You have completed all 122 controls. Please finalize the assessment session to publish your results in Assessment Results & Workspace.')) }}</p>',
                    confirmButtonText: '{{ addslashes(__('Finalize Now')) }}',
                    showCancelButton: true,
                    cancelButtonText: '{{ addslashes(__('Later')) }}',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    width: '26rem',
                    customClass: {
                        title: 'text-base font-bold text-slate-800 mt-1',
                        htmlContainer: 'text-left px-2',
                        confirmButton: 'text-xs font-bold uppercase tracking-widest px-5 py-2.5 rounded-lg',
                        cancelButton: 'text-xs font-bold uppercase tracking-widest px-5 py-2.5 rounded-lg'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.showFinalizeModal = true;
                    }
                });
            }
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
                            <span x-text="assessedCount"></span>/{{ $totalAssessable }} {{ __('controls') }}
                        </span>
                        <span class="text-slate-200">·</span>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">
                            {{ __('Score') }}: {{ number_format($session->overall_maturity_score, 1) }}/5
                        </span>
                        @php $gapCount = $session->results->where('is_applicable', true)->where('status', 'completed')->whereNotNull('maturity_rating')->where('maturity_rating', '<', 5)->count(); @endphp
                        @if($gapCount > 0)
                        <span class="text-slate-200">·</span>
                        <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">
                            {{ $gapCount }} {{ __('gaps') }}
                        </span>
                        @endif
                    </div>

                    {{-- Assigned Team / Users --}}
                    <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                            <i class="fa-solid fa-users text-blue-500 text-[9px]"></i> {{ __('Assigned Team') }}:
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs" title="{{ __('Session Lead') }}">
                            <i class="fa-solid fa-user-shield text-[9px] text-blue-600"></i>
                            <span>{{ $session->user->name ?? __('Lead') }}</span>
                            <span class="text-[7px] uppercase font-black bg-blue-600 text-white px-1 py-0.5 rounded">Lead</span>
                        </span>
                        @foreach($session->invitedUsers as $invited)
                            @if(!empty($session->user) && $invited->id === $session->user->id) @continue @endif
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs" title="{{ __('Invited Member') }}">
                                <i class="fa-solid fa-user text-[9px] text-slate-400"></i>
                                <span>{{ $invited->name }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('reports.export-pdf', $session->id) }}" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all" title="{{ __('Export PDF') }}" aria-label="Export session report as PDF">
                        <i class="fa-solid fa-file-pdf text-red-500"></i>{{ __('PDF') }}</a>
                    <a href="{{ route('reports.export-excel', $session->id) }}" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all" title="{{ __('Export Excel') }}" aria-label="Export session report as Excel">
                        <i class="fa-solid fa-file-excel text-green-600"></i>{{ __('Excel') }}</a>
                    @if($session->status === 'completed')
                    <a href="{{ route('workspace.index', ['session_id' => $session->id]) }}" class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all" title="{{ __('Open Workspace') }}" aria-label="Open session workspace">
                        <i class="fa-solid fa-diagram-project text-blue-500"></i>{{ __('Workspace') }}</a>
                    @endif
                    
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
                <p class="text-emerald-50 text-xs mt-0.5 font-medium">{{ __('You have completed all 122 controls. Please finalize the assessment session to lock your scores and publish your results in Assessment Results & Workspace.') }}</p>
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
                    @php
                        $sortedResults = $session->results->sortBy('iso_standard_id');
                        $clauseSidebarResults = $sortedResults->filter(fn($r) => in_array($r->standard->type, ['clause', 'clausa']));
                        $annexSidebarResults  = $sortedResults->filter(fn($r) => !in_array($r->standard->type, ['clause', 'clausa']));
                    @endphp

                    {{-- Clauses Tab Items --}}
                    <div x-show="activeTab === 'clause'" class="space-y-1">
                        @foreach($clauseSidebarResults as $result)
                            @php $item = $result->standard; @endphp
                            @if(!$item->description && !$item->questions)
                                <div class="px-4 py-3 text-[9px] font-bold text-slate-900 uppercase tracking-widest mt-4 border-l-2 border-blue-600 bg-slate-50/50 rounded-r-lg">
                                    {{ $item->code }} {{ __($item->title) }}
                                </div>
                            @else
                                <button @click="$dispatch('open-control', { id: {{ $result->id }} })" 
                                    x-data="{ status: '{{ $result->status }}', rating: {{ $result->maturity_rating === null ? 'null' : $result->maturity_rating }}, isApplicable: {{ $result->is_applicable ? 'true' : 'false' }} }"
                                    @result-updated.window="if($event.detail.id === {{ $result->id }}) { status = $event.detail.status; rating = $event.detail.rating; isApplicable = $event.detail.isApplicable; }"
                                    class="w-full text-left px-4 py-3 rounded-xl border transition-all flex items-center justify-between group ml-2 mt-1"
                                    :class="!isApplicable ? 'bg-slate-50 border-slate-100 text-slate-400' : (status === 'completed' && rating < 5 ? 'bg-rose-50 border-rose-100 text-rose-700' : (status === 'completed' ? 'bg-blue-50 border-blue-100 text-blue-700' : 'bg-white border-slate-100 text-slate-500 hover:border-blue-300'))"
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
                                        <template x-if="isApplicable && status === 'completed' && rating >= 5">
                                            <i class="fa-solid fa-circle-check text-[10px] text-blue-600"></i>
                                        </template>
                                        <template x-if="isApplicable && status !== 'completed'">
                                            <i class="fa-solid fa-circle text-[8px] text-slate-200"></i>
                                        </template>
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>

                    {{-- Annex Tab Items --}}
                    <div x-show="activeTab === 'annex'" class="space-y-1">
                        @foreach($annexSidebarResults as $result)
                            @php $item = $result->standard; @endphp
                            @if(!$item->description && !$item->questions)
                                <div class="px-4 py-3 text-[9px] font-bold text-slate-900 uppercase tracking-widest mt-4 border-l-2 border-blue-600 bg-slate-50/50 rounded-r-lg">
                                    {{ $item->code }} {{ __($item->title) }}
                                </div>
                            @else
                                <button @click="$dispatch('open-control', { id: {{ $result->id }} })" 
                                    x-data="{ status: '{{ $result->status }}', rating: {{ $result->maturity_rating === null ? 'null' : $result->maturity_rating }}, isApplicable: {{ $result->is_applicable ? 'true' : 'false' }} }"
                                    @result-updated.window="if($event.detail.id === {{ $result->id }}) { status = $event.detail.status; rating = $event.detail.rating; isApplicable = $event.detail.isApplicable; }"
                                    class="w-full text-left px-4 py-3 rounded-xl border transition-all flex items-center justify-between group ml-2 mt-1"
                                    :class="!isApplicable ? 'bg-slate-50 border-slate-100 text-slate-400' : (status === 'completed' && rating < 5 ? 'bg-rose-50 border-rose-100 text-rose-700' : (status === 'completed' ? 'bg-blue-50 border-blue-100 text-blue-700' : 'bg-white border-slate-100 text-slate-500 hover:border-blue-300'))"
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
                                        <template x-if="isApplicable && status === 'completed' && rating >= 5">
                                            <i class="fa-solid fa-circle-check text-[10px] text-blue-600"></i>
                                        </template>
                                        <template x-if="isApplicable && status !== 'completed'">
                                            <i class="fa-solid fa-circle text-[8px] text-slate-200"></i>
                                        </template>
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>


                </div>
            </div>
        </aside>

        {{-- Main Item List --}}
        <div class="flex-1 bg-white rounded-2xl border border-slate-100 shadow-sm min-h-[600px]">
            <div x-show="activeTab === 'clause'" x-transition>
                @include('sessions._item_list', ['items' => $session->results->whereIn('standard.type', ['clause', 'clausa'])->sortBy('iso_standard_id')])
            </div>
            <div x-show="activeTab === 'annex'" x-transition>
                @include('sessions._item_list', ['items' => $session->results->reject(fn($r) => in_array($r->standard->type, ['clause', 'clausa']))->sortBy('iso_standard_id')])
            </div>
        </div>
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
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md" @click="showAiModal = false"></div>

        {{-- Modal Content Card --}}
        <div class="relative bg-white rounded-2xl border border-slate-100 w-full max-w-2xl p-5 md:p-6 shadow-2xl space-y-4 z-10 overflow-hidden max-h-[85vh] overflow-y-auto"
            @click.away="showAiModal = false">
            
            {{-- Header --}}
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-blue-600/20 shrink-0">
                        <i class="fa-solid fa-robot text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-blue-50 border border-blue-100/90 text-blue-700 text-[10px] font-extrabold rounded-md uppercase tracking-wider leading-none" x-text="activeAiDetails.code"></span>
                            <span class="text-[9px] text-blue-500 font-extrabold uppercase tracking-widest leading-none">{{ __('AI COMPLIANCE SYNTHESIS') }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight leading-snug mt-0.5" x-text="activeAiDetails.title"></h3>
                    </div>
                </div>
                <button @click="showAiModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all flex items-center justify-center shrink-0 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- AI Analysis Accordion List --}}
            <div class="space-y-2.5" x-data="{ openSection: 'rec' }">

                {{-- Section 1: Strategic Recommendation --}}
                <div class="rounded-xl border transition-all overflow-hidden"
                     :class="openSection === 'rec' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                    <button type="button"
                        @click="openSection = openSection === 'rec' ? null : 'rec'"
                        class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                        :class="openSection === 'rec' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'rec' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                <i class="fa-solid fa-lightbulb text-[10px]"></i>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-widest"
                                  :class="openSection === 'rec' ? 'text-blue-700' : 'text-slate-600'">
                                {{ __('STRATEGIC RECOMMENDATION') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                           :class="openSection === 'rec' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                    </button>
                    <div x-show="openSection === 'rec'" x-collapse.duration.250ms>
                        <div class="p-3.5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-xl">
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.rec)"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Corrective Action Plan --}}
                <div class="rounded-xl border transition-all overflow-hidden"
                     :class="openSection === 'cap' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                    <button type="button"
                        @click="openSection = openSection === 'cap' ? null : 'cap'"
                        class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                        :class="openSection === 'cap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'cap' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                <i class="fa-solid fa-list-check text-[10px]"></i>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-widest"
                                  :class="openSection === 'cap' ? 'text-blue-700' : 'text-slate-600'">
                                {{ __('CORRECTIVE ACTION PLAN') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                           :class="openSection === 'cap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                    </button>
                    <div x-show="openSection === 'cap'" x-collapse.duration.250ms>
                        <div class="p-3.5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-xl">
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.plan) || '{{ __('No specific action plan drafted.') }}'"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 3: AI Audit Insight (Gap) --}}
                <div class="rounded-xl border transition-all overflow-hidden"
                     :class="openSection === 'gap' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                    <button type="button"
                        @click="openSection = openSection === 'gap' ? null : 'gap'"
                        class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                        :class="openSection === 'gap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'gap' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-widest"
                                  :class="openSection === 'gap' ? 'text-blue-700' : 'text-slate-600'">
                                {{ __('AI AUDIT INSIGHT (GAP)') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                           :class="openSection === 'gap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                    </button>
                    <div x-show="openSection === 'gap'" x-collapse.duration.250ms>
                        <div class="p-3.5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-xl">
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.insight) || '{{ __('Control shows solid operational alignment.') }}'"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Impact Interpretation --}}
                <div class="rounded-xl border transition-all overflow-hidden"
                     :class="openSection === 'impact' ? 'border-blue-200/90 bg-blue-50/40 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                    <button type="button"
                        @click="openSection = openSection === 'impact' ? null : 'impact'"
                        class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                        :class="openSection === 'impact' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'impact' ? 'bg-blue-600 text-white shadow-xs shadow-blue-600/20' : 'bg-slate-100 text-slate-400'">
                                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-widest"
                                  :class="openSection === 'impact' ? 'text-blue-700' : 'text-slate-600'">
                                {{ __('IMPACT INTERPRETATION') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"
                           :class="openSection === 'impact' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                    </button>
                    <div x-show="openSection === 'impact'" x-collapse.duration.250ms>
                        <div class="p-3.5 text-xs font-medium text-slate-600 leading-relaxed bg-blue-50/40 border-t border-blue-100/60 rounded-b-xl">
                            <p class="whitespace-pre-line" x-html="formatMarkdown(activeAiDetails.impact) || '{{ __('No impact interpretation available.') }}'"></p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Badges --}}
            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5" x-show="activeAiDetails.priority">
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Risk Tier:') }}</span>
                    <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-100 text-[9px] font-black rounded-md uppercase tracking-wider leading-none" x-text="activeAiDetails.priority"></span>
                </div>
                <button type="button" @click="showAiModal = false" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all cursor-pointer">
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
