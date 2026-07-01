@extends('layouts.app')
@section('title', 'Compliance Center')
@section('view_name', 'Compliance Center')

@section('content')
@php
    $workspaceControls = $results->map(fn($result) => [
        'id' => $result->id,
        'code' => strtolower($result->standard->code ?? ''),
        'title' => strtolower($result->standard->title ?? ''),
        'risk' => strtolower($result->risk_level ?? 'low'),
        'maturityGap' => $result->is_applicable && $result->status === 'completed' && $result->maturity_rating !== null && $result->maturity_rating < 4,
        'isGap' => $result->is_applicable && $result->status === 'completed' && $result->maturity_rating !== null && $result->maturity_rating < 4,
        'isApplicable' => (bool) $result->is_applicable,
        'treatmentStatus' => $result->treatment_status ?? 'open',
    ])->values();
    $gapFindings = $findings->map(fn($finding) => [
        'id' => $finding->id,
        'risk' => $finding->risk_level ?? 'Low',
        'isCritical' => $finding->risk_level === 'Critical' || $finding->maturity_rating <= 1,
        'isApplicable' => (bool) $finding->is_applicable,
    ])->values();
@endphp
<div class="max-w-[1600px] mx-auto space-y-3 pb-6" x-data="{
    activeTab: '{{ $activeTab }}',
    filterOption: 'all',
    riskFilter: 'all',
    searchQuery: '',
    saving: false,
    saveState: 'ready',
    workspaceStats: {
        total: {{ $stats['total'] }},
        gaps: {{ $stats['gaps'] }},
        applicable: {{ $stats['applicable'] }},
        notApplicable: {{ $stats['not_applicable'] }},
        closed: {{ $stats['closed'] }}
    },
    gapStats: {
        totalGaps: {{ $tacticalStats['total_gaps'] }},
        critical: {{ $tacticalStats['critical'] }},
        compliant: {{ $tacticalStats['compliant'] }},
        totalControls: {{ $tacticalStats['total_controls'] }},
        scored: {{ $tacticalStats['scored'] ?? $tacticalStats['assessed'] ?? 0 }}
    },
    controls: @js($workspaceControls),
    gapFindings: @js($gapFindings),
    showAiModal: false,
    activeAiDetails: { code: '', title: '', rec: '', plan: '', insight: '', priority: '', validation: '', impact: '' },
    showEvidenceModal: false,
    activeEvidenceDetails: { code: '', title: '', notes: '', files: [] },
    openEvidenceDetails(details) {
        this.activeEvidenceDetails = {
            code: details.code || '',
            title: details.title || '',
            notes: details.notes || '',
            files: details.files || []
        };
        this.showEvidenceModal = true;
    },
    get filteredControls() {
        return this.controls.filter((control) => (
            this.filterOption === 'all' ||
            (this.filterOption === 'gaps' && control.isGap) ||
            (this.filterOption === 'applicable' && control.isApplicable) ||
            (this.filterOption === 'not_applicable' && !control.isApplicable)
        ) && (
            this.riskFilter === 'all' || this.riskFilter === control.risk
        ) && (
            this.searchQuery === '' ||
            control.code.includes(this.searchQuery.toLowerCase()) ||
            control.title.includes(this.searchQuery.toLowerCase())
        ));
    },
    isControlVisible(resultId) {
        return this.filteredControls.some((control) => control.id === resultId);
    },
    switchTab(tab) {
        this.activeTab = tab;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    },
    openAiDetails(details) {
        this.activeAiDetails = {
            code: details.code || '',
            title: details.title || '',
            rec: details.rec || '',
            plan: details.plan || '',
            insight: details.insight || '',
            priority: details.priority || '',
            validation: details.validation || '',
            impact: details.impact || ''
        };
        this.showAiModal = true;
    },
    async saveSingle(resultId, payload) {
        this.saving = true;
        this.saveState = 'saving';
        try {
            const response = await fetch(`/workspace/entry/${resultId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Save failed.');
            }
            this.saveState = 'saved';
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Saved successfully.', type: 'success' } }));
            return data.data || {};
        } catch(e) {
            this.saveState = 'error';
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: e.message || 'Save failed.', type: 'error' } }));
            return null;
        } finally {
            this.saving = false;
            setTimeout(() => {
                if (!this.saving && this.saveState !== 'error') this.saveState = 'ready';
            }, 1800);
        }
    },
    applySavedValue(resultId, field, oldValue, newValue, isGap) {
        const control = this.controls.find((item) => item.id === resultId);
        if (!control) return;

        if (field === 'is_applicable' && oldValue !== newValue) {
            control.isApplicable = newValue;
            this.workspaceStats.applicable += newValue ? 1 : -1;
            this.workspaceStats.notApplicable += newValue ? -1 : 1;

            if (control.maturityGap) {
                control.isGap = newValue;
                this.workspaceStats.gaps += newValue ? 1 : -1;

                if (control.treatmentStatus === 'closed') {
                    this.workspaceStats.closed += newValue ? 1 : -1;
                    this.workspaceStats.closed = Math.max(this.workspaceStats.closed, 0);
                }
            }

            const finding = this.gapFindings.find((item) => item.id === resultId);
            if (finding) {
                finding.isApplicable = newValue;
                this.gapStats.totalGaps += newValue ? 1 : -1;
                this.gapStats.totalGaps = Math.max(this.gapStats.totalGaps, 0);

                if (finding.isCritical) {
                    this.gapStats.critical += newValue ? 1 : -1;
                    this.gapStats.critical = Math.max(this.gapStats.critical, 0);
                }
            }
        }

        if (field === 'treatment_status' && isGap && oldValue !== newValue) {
            control.treatmentStatus = newValue;
            if (oldValue === 'closed') this.workspaceStats.closed = Math.max(this.workspaceStats.closed - 1, 0);
            if (newValue === 'closed') this.workspaceStats.closed += 1;
        }
    }
}">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 rounded-lg border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-table-cells-large text-lg"></i>
            </div>
            <div class="leading-none">
                <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">{{ __('Compliance Center') }}</h1>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Gap Report, SoA & Treatment Management') }}</p>
            </div>
        </div>

        {{-- Session selector --}}
        <div class="flex items-center gap-3">
            <form action="{{ route('workspace.index') }}" method="GET" id="workspaceFilter" class="flex items-center gap-3">
                <select name="session_id" onchange="document.getElementById('workspaceFilter').requestSubmit()"
                    class="bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-600/5 transition-all min-w-[260px] cursor-pointer shadow-sm">
                    @if($sessions->isEmpty())
                        <option value="">{{ __('No sessions available') }}</option>
                    @endif
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ $selectedId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->created_at->format('M Y') }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if(!$selectedSession)
        <div class="bg-white rounded-lg border border-slate-100 p-16 text-center shadow-sm">
            <div class="w-16 h-16 bg-slate-50 rounded-lg flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">{{ __('No Assessment Data') }}</h3>
            <p class="text-sm text-slate-400 font-medium mt-1">{{ __('Create an audit session first to manage compliance.') }}</p>
            <a href="{{ route('sessions.index') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-plus"></i> {{ __('Create Session') }}
            </a>
        </div>
    @else

    {{-- Dashboard Overview (Unified Stats & Session Comparison) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Stats Grid (Left 2 cols on LG) --}}
        <div class="lg:col-span-2">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-start">
                {{-- Total Controls --}}
                <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm hover:shadow transition-all group flex flex-col justify-between min-h-[90px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ __('Total Controls') }}</p>
                            <p class="text-xl font-black text-slate-900 mt-1" x-text="workspaceStats.total"></p>
                        </div>
                        <div class="w-6.5 h-6.5 bg-slate-50 text-slate-400 rounded-lg flex items-center justify-center border border-slate-100 shrink-0">
                            <i class="fa-solid fa-table-list text-[9px]"></i>
                        </div>
                    </div>
                    <p class="text-[8px] font-bold text-slate-400 mt-2">{{ __('All ISO 27001:2022 controls') }}</p>
                </div>

                {{-- Identified Gaps --}}
                <div class="bg-rose-50 rounded-xl p-3 border border-rose-100 shadow-sm hover:shadow transition-all group flex flex-col justify-between min-h-[90px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-bold text-rose-600 uppercase tracking-widest leading-tight">{{ __('Identified Gaps') }}</p>
                            <div class="flex items-baseline gap-0.5 mt-1">
                                <p class="text-xl font-black text-rose-700" x-text="workspaceStats.gaps"></p>
                                <p class="text-[9px] font-bold text-rose-400">/ <span x-text="workspaceStats.total"></span></p>
                            </div>
                        </div>
                        <div class="w-6.5 h-6.5 bg-rose-100/50 text-rose-600 rounded-lg flex items-center justify-center border border-rose-200 shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                        </div>
                    </div>
                    <p class="text-[8px] font-bold text-rose-400 mt-2"><span x-text="workspaceStats.total - workspaceStats.gaps"></span> {{ __('Compliant') }}</p>
                </div>

                {{-- Applicable Controls --}}
                <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 shadow-sm hover:shadow transition-all group flex flex-col justify-between min-h-[90px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest leading-tight">{{ __('Applicable') }}</p>
                            <div class="flex items-baseline gap-0.5 mt-1">
                                <p class="text-xl font-black text-emerald-700" x-text="workspaceStats.applicable"></p>
                                <p class="text-[9px] font-bold text-emerald-400">/ <span x-text="workspaceStats.total"></span></p>
                            </div>
                        </div>
                        <div class="w-6.5 h-6.5 bg-emerald-100/50 text-emerald-600 rounded-lg flex items-center justify-center border border-emerald-200 shrink-0">
                            <i class="fa-solid fa-shield-check text-[9px]"></i>
                        </div>
                    </div>
                    <p class="text-[8px] font-bold text-emerald-400 mt-2"><span x-text="workspaceStats.notApplicable"></span> {{ __('excluded') }}</p>
                </div>

                {{-- Treatments Closed --}}
                <div class="bg-blue-50 rounded-xl p-3 border border-blue-100 shadow-sm hover:shadow transition-all group flex flex-col justify-between min-h-[90px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest leading-tight">{{ __('Treatments') }}</p>
                            <div class="flex items-baseline gap-0.5 mt-1">
                                <p class="text-xl font-black text-blue-700" x-text="workspaceStats.closed"></p>
                                <p class="text-[9px] font-bold text-blue-400">/ <span x-text="workspaceStats.gaps"></span></p>
                            </div>
                        </div>
                        <div class="w-6.5 h-6.5 bg-blue-100/50 text-blue-600 rounded-lg flex items-center justify-center border border-blue-200 shrink-0">
                            <i class="fa-solid fa-circle-check text-[9px]"></i>
                        </div>
                    </div>
                    <p class="text-[8px] font-bold text-blue-400 mt-2"><span x-text="Math.max(workspaceStats.gaps - workspaceStats.closed, 0)"></span> {{ __('remaining') }}</p>
                </div>
            </div>

            @if($selectedSession)
            {{-- Export Report Buttons (Placed directly below the cards, buttons only) --}}
            <div class="flex flex-wrap gap-2 mt-4 justify-start">
                {{-- SoA Exports --}}
                <a href="{{ route('workspace.export-soa', $selectedSession->id) }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow transition-all flex items-center gap-1.5 shrink-0" title="{{ __('Export Statement of Applicability Excel') }}">
                    <i class="fa-solid fa-file-excel text-white"></i>{{ __('SoA Excel') }}</a>
                <a href="{{ route('workspace.export-soa-pdf', $selectedSession->id) }}" class="px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow transition-all flex items-center gap-1.5 shrink-0" title="{{ __('Export Statement of Applicability PDF') }}">
                    <i class="fa-solid fa-file-pdf text-white"></i>{{ __('SoA PDF') }}</a>
                
                {{-- Gap Report Exports --}}
                <a href="{{ route('reports.export-excel', $selectedSession->id) }}" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow transition-all flex items-center gap-1.5 shrink-0" title="{{ __('Export Gap Report Excel') }}">
                    <i class="fa-solid fa-file-excel text-white"></i>{{ __('Gap Excel') }}</a>
                <a href="{{ route('reports.export-pdf', $selectedSession->id) }}" class="px-3 py-2 bg-rose-700 hover:bg-rose-800 text-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow transition-all flex items-center gap-1.5 shrink-0" title="{{ __('Export Gap Report PDF') }}">
                    <i class="fa-solid fa-file-pdf text-white"></i>{{ __('Gap PDF') }}</a>
            </div>
            @endif
        </div>

        {{-- Session Comparison (Right 1 col on LG) --}}
        <div class="lg:col-span-1">
            @if($comparison && isset($comparison['delta']))
            <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm h-full flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">{{ __('Session Comparison') }}</h3>
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('Current vs Previous Cycle') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-right">
                            <p class="text-[7px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Score') }}</p>
                            <p class="text-base font-black text-slate-900">{{ number_format($comparison['latest_score'], 1) }}<span class="text-[9px] text-slate-400">/5</span></p>
                        </div>
                        @if($comparison['delta'] != 0)
                        <span class="flex items-center gap-0.5 px-2 py-1 rounded-lg text-[10px] font-black {{ $comparison['delta'] > 0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                            <i class="fa-solid {{ $comparison['delta'] > 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                            {{ $comparison['delta'] > 0 ? '+' : '' }}{{ number_format($comparison['delta'], 1) }}
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-lg text-[10px] font-black bg-slate-50 text-slate-500 border border-slate-200">{{ __('No change') }}</span>
                        @endif
                    </div>
                </div>
                <div class="space-y-2">
                    @foreach($comparison['domains'] as $domain)
                    @php $pct = min(($domain['latest'] / 5) * 100, 100); @endphp
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold text-slate-600 w-16 shrink-0 truncate">{{ $domain['label'] }}</span>
                        <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $domain['latest'] >= 4 ? 'bg-emerald-500' : ($domain['latest'] >= 3 ? 'bg-amber-400' : 'bg-rose-500') }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[9px] font-black text-slate-700 w-5 text-right">{{ number_format($domain['latest'], 1) }}</span>
                            @if($domain['delta'] != 0)
                            <span class="text-[8px] font-bold {{ $domain['delta'] > 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                                {{ $domain['delta'] > 0 ? '+' : '' }}{{ number_format($domain['delta'], 1) }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>


    {{-- Filters --}}
    <div class="flex flex-col xl:flex-row xl:items-center gap-3 bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mr-2">{{ __('View:') }}</span>
                @foreach(['all' => __('All Controls'), 'gaps' => __('Gaps Only'), 'applicable' => __('Applicable'), 'not_applicable' => __('Not Applicable')] as $val => $label)
                <button @click="filterOption = '{{ $val }}'"
                    :class="filterOption === '{{ $val }}' ? 'bg-slate-900 text-white shadow' : 'bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-100'"
                    class="px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <div class="w-px h-6 bg-slate-200 hidden md:block"></div>
            <div class="flex items-center gap-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mr-2">{{ __('Risk:') }}</span>
                @foreach(['all' => __('All'), 'critical' => __('Critical'), 'high' => __('High'), 'medium' => __('Medium'), 'compliant' => __('Low')] as $val => $label)
                <button @click="riskFilter = '{{ $val }}'"
                    :class="riskFilter === '{{ $val }}' 
                        ? ('{{ $val }}' === 'critical' ? 'bg-rose-600 text-white shadow shadow-rose-600/20' 
                          : '{{ $val }}' === 'high' ? 'bg-orange-500 text-white shadow shadow-orange-500/20' 
                          : '{{ $val }}' === 'medium' ? 'bg-amber-500 text-white shadow shadow-amber-500/20' 
                          : '{{ $val }}' === 'compliant' ? 'bg-emerald-600 text-white shadow shadow-emerald-600/20' 
                          : 'bg-slate-900 text-white shadow') 
                        : 'bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-100'"
                    class="px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <div class="w-px h-6 bg-slate-200 hidden xl:block"></div>
            <div class="relative w-full xl:w-auto">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                <input type="text" x-model="searchQuery" placeholder="{{ __('Search control or title...') }}"
                    class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all w-full xl:w-64 placeholder:text-slate-400">
            </div>
        </div>
        <div class="xl:ml-auto self-start xl:self-center">
            <span class="text-[8px] font-bold uppercase tracking-widest flex items-center gap-1"
                :class="{
                    'text-indigo-600': saveState === 'saving',
                    'text-emerald-600': saveState === 'saved' || saveState === 'ready',
                    'text-rose-600': saveState === 'error'
                }">
                <span class="w-2 h-2 rounded-full inline-block"
                    :class="{
                        'bg-indigo-500 animate-pulse': saveState === 'saving',
                        'bg-emerald-400': saveState === 'saved' || saveState === 'ready',
                        'bg-rose-500': saveState === 'error'
                    }"></span>
                <span x-text="saveState === 'saving' ? '{{ __('Saving...') }}' : (saveState === 'saved' ? '{{ __('Saved') }}' : (saveState === 'error' ? '{{ __('Save failed') }}' : '{{ __('Auto-save ready') }}'))"></span>
            </span>
        </div>
    </div>

    @if($selectedSession && request()->has('focus'))
    <div class="sticky top-0 z-40 py-2">
        <a href="{{ route('sessions.show', $selectedSession) }}?focus={{ request('focus') }}"
           class="inline-flex items-center gap-3 px-5 py-2.5 bg-slate-900/95 text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-xl hover:bg-blue-600 transition-all duration-300 group hover:scale-[1.02] active:scale-95 backdrop-blur-md border border-white/10">
            <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
            <span>{{ __('Back to Assessment') }}</span>
        </a>
    </div>
    @endif


    {{-- Controls Table --}}
    @if($selectedSession)
    <div class="bg-white rounded-lg border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1500px]">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-96">{{ __('Control') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">{{ __('Applicable') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-48">{{ __('SoA Justification') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-32 text-center">{{ __('Risk Level') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-36 text-center">{{ __('Compliance Status') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">{{ __('Maturity') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-36 text-center">{{ __('Gap') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-32">{{ __('Treatment Due') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-72">{{ __('PIC') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-48">{{ __('Action Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($results as $result)
                    @php
                        $isScored     = $result->status === 'completed';
                        $isClause     = in_array($result->standard->type ?? '', ['clause', 'clausa']);
                        $isGap        = $isScored && $result->maturity_rating !== null && $result->maturity_rating < 4;
                        $isApplicable = $isClause ? true : (bool) $result->is_applicable;
                        $dueDate      = $result->treatment_due_date ? $result->treatment_due_date->format('Y-m-d') : '';
                        $status       = $result->treatment_status ?? 'open';
                        $riskLevel    = strtolower($result->risk_level ?? 'low');
                        $controlTitle = strtolower($result->standard->title ?? '');
                        $aiPlan       = is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : ($result->corrective_action_plan ?? '');
                    @endphp
                    <tr id="row-{{ $result->id }}"
                        class="hover:bg-slate-50/50 transition-all group"
                        x-data="{
                            isApplicable: {{ $isApplicable ? 'true' : 'false' }},
                            isClause: {{ $isClause ? 'true' : 'false' }},
                            justification: @js($result->soa_justification ?? ''),
                            dueDate: @js($dueDate),
                            pic: @js($result->treatment_pic ?? ''),
                            status: @js($status),
                            previousStatus: @js($status),
                            risk: @js($riskLevel),
                            editingJust: false,
                            isGap: {{ $isGap ? 'true' : 'false' }},
                            isScored: {{ $isScored ? 'true' : 'false' }},
                            async toggleApplicable() {
                                const previous = this.isApplicable;
                                this.isApplicable = !this.isApplicable;
                                if (!(await this.save('is_applicable', this.isApplicable, previous))) {
                                    this.isApplicable = previous;
                                }
                            },
                            async changeStatus() {
                                const previous = this.previousStatus;
                                if (await this.save('treatment_status', this.status, previous)) {
                                    this.previousStatus = this.status;
                                } else {
                                    this.status = previous;
                                }
                            },
                            async save(field, value, oldValue = null) {
                                let payload = {};
                                payload[field] = value;
                                const saved = await saveSingle({{ $result->id }}, payload);
                                if (saved) {
                                    applySavedValue({{ $result->id }}, field, oldValue, value, isGap);
                                    return true;
                                }
                                return false;
                            }
                        }"
                        x-show="isControlVisible({{ $result->id }})">
                        <td class="px-3 py-3">
                            <div class="flex flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-900 tracking-tight leading-snug">{{ $result->standard->code }}</span>
                                        <span class="text-[11px] font-medium text-slate-500 leading-tight mt-0.5">{{ __($result->standard->title) }}</span>
                                        @if(!empty($result->notes) || !empty($result->evidence_file))
                                            @php
                                                $evidenceFiles = is_array($result->evidence_file)
                                                    ? $result->evidence_file
                                                    : (empty($result->evidence_file) ? [] : [$result->evidence_file]);
                                                $mappedFiles = [];
                                                foreach ($evidenceFiles as $file) {
                                                    $mappedFiles[] = [
                                                        'name' => basename($file),
                                                        'url' => route('results.evidence', [$result->id, 'file' => $file])
                                                    ];
                                                }
                                            @endphp
                                            <div class="mt-1.5 flex items-center">
                                                <button @click="openEvidenceDetails({
                                                        code: @js($result->standard->code),
                                                        title: @js(__($result->standard->title)),
                                                        notes: @js($result->notes ?? ''),
                                                        files: @js($mappedFiles)
                                                    })"
                                                    class="inline-flex items-center gap-1.5 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all cursor-pointer">
                                                    <i class="fa-solid fa-eye text-[9px]"></i>
                                                    {{ __('Notes & Evidence') }}
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                    @if($isGap && !empty($result->ai_recommendation))
                                    @php
                                        $aiBtnClass = 'bg-indigo-50 text-indigo-700 border-indigo-100 hover:bg-indigo-600 hover:text-white shadow-indigo-600/5';
                                        $aiIconAnim = '';
                                        if (in_array($riskLevel, ['critical', 'high'])) {
                                            $aiBtnClass = 'bg-rose-50 text-rose-700 border-rose-100 hover:bg-rose-600 hover:text-white shadow-rose-600/5 ring-1 ring-rose-500/20';
                                            $aiIconAnim = 'animate-pulse text-rose-500';
                                        } elseif ($riskLevel === 'medium') {
                                            $aiBtnClass = 'bg-amber-50 text-amber-700 border-amber-100 hover:bg-amber-500 hover:text-white shadow-amber-600/5';
                                        }
                                    @endphp
                                    <button @click="openAiDetails({
                                            code: @js($result->standard->code ?? ''),
                                            title: @js(__($result->standard->title ?? '')),
                                            rec: @js($result->ai_recommendation ?? ''),
                                            plan: @js($aiPlan),
                                            insight: @js(is_array($result->control_insight) ? ($result->control_insight['gap'] ?? '') : ($result->control_insight ?? '')),
                                            priority: @js($result->risk_priority ?? ''),
                                            validation: @js($result->evidence_validation ?? ''),
                                            impact: @js($result->impact_interpretation ?? '')
                                        })"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border {{ $aiBtnClass }} rounded-lg text-[8px] font-black uppercase tracking-widest transition-all leading-none shrink-0 shadow-sm hover:scale-105 active:scale-95 cursor-pointer group/aibtn">
                                        <i class="fa-solid fa-robot text-[9px] {{ $aiIconAnim }} group-hover/aibtn:text-white"></i>{{ __('Detail AI') }}</button>
                                    @endif
                                </div>
                                @if($isGap && !empty(is_array($result->control_insight) ? ($result->control_insight['gap'] ?? null) : $result->control_insight) && empty($result->ai_recommendation))
                                <div class="mt-1 flex items-start gap-1 text-[9px] text-rose-600 font-medium bg-rose-50 p-1.5 rounded-lg border border-rose-100">
                                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                    <span class="leading-tight">{{ is_array($result->control_insight) ? ($result->control_insight['gap'] ?? '') : $result->control_insight }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center">
                        {{-- Applicable: read-only badge. Only show if assessed. Edit via Assessment page only. --}}
                            @if(!$isClause && $isScored)
                            <span :class="isApplicable ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-rose-50 text-rose-500 border border-rose-100'"
                                class="px-2 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-widest inline-block w-16 text-center cursor-default select-none">
                                <span x-text="isApplicable ? 'Yes' : 'No'"></span>
                            </span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <div class="text-[9px] text-slate-600 font-medium min-h-[26px] px-2 py-1 flex items-center">
                                <span x-text="justification || ''" :class="!justification && 'text-slate-300 italic'"></span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            {{-- Risk Level --}}
                            @if($isScored && $isApplicable)
                                @if($isGap)
                                    @php
                                        $riskLevelLabel = $result->risk_level ?? 'Low';
                                        $riskClass = match(strtolower($riskLevelLabel)) {
                                            'critical'  => 'text-rose-700 bg-rose-50 border-rose-100',
                                            'high'      => 'text-orange-700 bg-orange-50 border-orange-100',
                                            'medium'    => 'text-amber-700 bg-amber-50 border-amber-100',
                                            default     => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1.5 rounded-lg border text-[9px] font-black uppercase tracking-wider inline-block text-center w-24 {{ $riskClass }}">
                                        {{ $riskLevelLabel }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1.5 rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-wider inline-block text-center w-24">
                                        {{ __('Low') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-[9px] text-slate-300 font-bold italic">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            {{-- Compliance Status --}}
                            @if($isScored && $isApplicable)
                                @php
                                    $complianceStatus = $result->compliance_status;
                                    $complianceClass = match(strtolower($complianceStatus)) {
                                        'compliant'           => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                                        'partially compliant' => 'text-amber-700 bg-amber-50 border-amber-100',
                                        default               => 'text-rose-700 bg-rose-50 border-rose-100',
                                    };
                                @endphp
                                <span class="px-2.5 py-1.5 rounded-lg border text-[9px] font-black uppercase tracking-wider inline-block text-center w-32 {{ $complianceClass }}">
                                    {{ $complianceStatus }}
                                </span>
                            @else
                                <span class="text-[9px] text-slate-300 font-bold italic">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            {{-- Maturity --}}
                            @if($isScored && $isApplicable && $result->maturity_rating !== null)
                                <div class="inline-flex items-baseline justify-center">
                                    <span class="text-sm font-black text-slate-900 leading-none">{{ $result->maturity_rating }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold ml-0.5">/5</span>
                                </div>
                            @else
                                <span class="text-[9px] text-slate-300 font-bold italic">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            {{-- Gap --}}
                            @if($isScored && $isApplicable)
                                @php
                                    $gapPct = $isGap ? (5 - $result->maturity_rating) * 20 : 0;
                                @endphp
                                <div class="flex items-center gap-2 justify-center">
                                    <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all {{ $gapPct > 0 ? 'bg-rose-500' : 'bg-slate-200' }}" style="width: {{ $gapPct }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 w-8">{{ $gapPct }}%</span>
                                </div>
                            @else
                                <div class="text-center">
                                    <span class="text-[9px] text-slate-300 font-bold italic">-</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <input type="date" x-model="dueDate" @change="save('treatment_due_date', dueDate)" :disabled="!isApplicable || !isGap"
                                class="w-full text-[9px] font-bold text-slate-700 border border-slate-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all bg-white disabled:opacity-30 disabled:cursor-not-allowed">
                        </td>
                        <td class="px-3 py-3">
                            <input type="text" x-model="pic" @blur="save('treatment_pic', pic)" @keydown.enter.prevent="$event.target.blur()" :disabled="!isApplicable || !isGap"
                                placeholder="{{ __('PIC Name/Role') }}"
                                class="w-full text-[9px] font-medium text-slate-700 border border-slate-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all bg-white disabled:opacity-30 disabled:cursor-not-allowed">
                        </td>
                        <td class="px-3 py-3">
                            <select x-model="status" @change="changeStatus()" :disabled="!isApplicable || !isGap"
                                class="w-full text-[8px] font-black uppercase tracking-widest border border-slate-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all bg-white disabled:opacity-30 disabled:cursor-not-allowed"
                                :class="{
                                    'text-rose-600 bg-rose-50 border-rose-200': status === 'open' && isGap,
                                    'text-amber-600 bg-amber-50 border-amber-200': status === 'in_progress' && isGap,
                                    'text-emerald-600 bg-emerald-50 border-emerald-200': status === 'closed' && isGap
                                }">
                                <option value="open">{{ __('Open') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="closed">{{ __('Closed') }}</option>
                            </select>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-clipboard text-2xl text-slate-300"></i>
                            </div>
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">{{ __('No controls found for this session') }}</p>
                        </td>
                    </tr>
                    @endforelse
                    @if($results->isNotEmpty())
                    <tr x-show="filteredControls.length === 0" x-cloak>
                        <td colspan="9" class="px-6 py-14 text-center">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-magnifying-glass text-xl text-slate-300"></i>
                            </div>
                            <p class="text-slate-500 font-black uppercase tracking-widest text-[10px]">{{ __('No matching controls') }}</p>
                            <p class="text-slate-400 font-medium text-xs mt-1">{{ __('Try changing the search keyword or filters.') }}</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif


    {{-- AI Detail Modal (shared, lives in root Alpine scope) --}}
    <div x-show="showAiModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak>
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md" @click="showAiModal = false"></div>
        <div class="relative bg-white rounded-lg border border-slate-100 w-full max-w-4xl p-6 md:p-8 shadow-2xl space-y-6 z-10 max-h-[90vh] overflow-y-auto"
            @click.away="showAiModal = false">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                        <i class="fa-solid fa-robot text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 text-[9px] font-black rounded-lg uppercase tracking-wider leading-none" x-text="activeAiDetails.code"></span>
                            <span class="text-[8px] text-indigo-500 font-bold uppercase tracking-widest leading-none">{{ __('AI Compliance Synthesis') }}</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight mt-1 leading-snug" x-text="activeAiDetails.title"></h3>
                    </div>
                </div>
                <button @click="showAiModal = false" class="w-8 h-8 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            {{-- AI Analysis Accordion --}}
            <div class="space-y-2" x-data="{ openSection: 'rec' }">

                {{-- Section 1: Strategic Recommendation --}}
                <div class="rounded-xl border border-slate-100 overflow-hidden">
                    <button type="button"
                        @click="openSection = openSection === 'rec' ? null : 'rec'"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-50"
                        :class="openSection === 'rec' ? 'bg-indigo-50 border-b border-indigo-100' : 'bg-white'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'rec' ? 'bg-indigo-600' : 'bg-slate-100'">
                                <i class="fa-solid fa-lightbulb text-[9px]"
                                   :class="openSection === 'rec' ? 'text-white' : 'text-slate-400'"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest"
                                  :class="openSection === 'rec' ? 'text-indigo-700' : 'text-slate-600'">
                                {{ __('Strategic Recommendation') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                           :class="openSection === 'rec' ? 'rotate-180 text-indigo-400' : ''"></i>
                    </button>
                    <div x-show="openSection === 'rec'" x-collapse.duration.250ms>
                        <div class="px-4 py-3 bg-white">
                            <p class="text-xs text-slate-700 font-medium leading-relaxed" x-html="formatMarkdown(activeAiDetails.rec)"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Corrective Action Plan --}}
                <div class="rounded-xl border border-slate-100 overflow-hidden">
                    <button type="button"
                        @click="openSection = openSection === 'cap' ? null : 'cap'"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-50"
                        :class="openSection === 'cap' ? 'bg-emerald-50 border-b border-emerald-100' : 'bg-white'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'cap' ? 'bg-emerald-600' : 'bg-slate-100'">
                                <i class="fa-solid fa-list-check text-[9px]"
                                   :class="openSection === 'cap' ? 'text-white' : 'text-slate-400'"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest"
                                  :class="openSection === 'cap' ? 'text-emerald-700' : 'text-slate-600'">
                                {{ __('Corrective Action Plan') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                           :class="openSection === 'cap' ? 'rotate-180 text-emerald-400' : ''"></i>
                    </button>
                    <div x-show="openSection === 'cap'" x-collapse.duration.250ms>
                        <div class="px-4 py-3 bg-white">
                            <p class="text-xs text-slate-700 font-medium leading-relaxed whitespace-pre-line"
                               x-html="formatMarkdown(activeAiDetails.plan) || '{{ __('No specific action plan drafted.') }}'"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 3: AI Audit Insight --}}
                <div class="rounded-xl border border-slate-100 overflow-hidden">
                    <button type="button"
                        @click="openSection = openSection === 'gap' ? null : 'gap'"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-50"
                        :class="openSection === 'gap' ? 'bg-violet-50 border-b border-violet-100' : 'bg-white'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'gap' ? 'bg-violet-600' : 'bg-slate-100'">
                                <i class="fa-solid fa-magnifying-glass-chart text-[9px]"
                                   :class="openSection === 'gap' ? 'text-white' : 'text-slate-400'"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest"
                                  :class="openSection === 'gap' ? 'text-violet-700' : 'text-slate-600'">
                                {{ __('AI Audit Insight (Gap)') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                           :class="openSection === 'gap' ? 'rotate-180 text-violet-400' : ''"></i>
                    </button>
                    <div x-show="openSection === 'gap'" x-collapse.duration.250ms>
                        <div class="px-4 py-3 bg-white">
                            <p class="text-xs text-slate-700 font-medium leading-relaxed"
                               x-html="formatMarkdown(activeAiDetails.insight) || '{{ __('Control shows solid operational alignment.') }}'"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Impact Interpretation --}}
                <div class="rounded-xl border border-slate-100 overflow-hidden" x-cloak>
                    <button type="button"
                        @click="openSection = openSection === 'impact' ? null : 'impact'"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-50"
                        :class="openSection === 'impact' ? 'bg-rose-50 border-b border-rose-100' : 'bg-white'">
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 transition-colors"
                                 :class="openSection === 'impact' ? 'bg-rose-600' : 'bg-slate-100'">
                                <i class="fa-solid fa-triangle-exclamation text-[9px]"
                                   :class="openSection === 'impact' ? 'text-white' : 'text-slate-400'"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest"
                                  :class="openSection === 'impact' ? 'text-rose-700' : 'text-slate-600'">
                                {{ __('Impact Interpretation') }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                           :class="openSection === 'impact' ? 'rotate-180 text-rose-400' : ''"></i>
                    </button>
                    <div x-show="openSection === 'impact'" x-collapse.duration.250ms>
                        <div class="px-4 py-3 bg-rose-50/30">
                            <p class="text-xs text-slate-700 font-medium leading-relaxed" x-html="formatMarkdown(activeAiDetails.impact) || '{{ __('No impact interpretation available. Please click Regenerate AI to update this analysis.') }}'"></p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2" x-show="activeAiDetails.priority">
                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Risk Tier:</span>
                    <span class="px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-100 text-[8px] font-black rounded-lg uppercase tracking-wider leading-none" x-text="activeAiDetails.priority"></span>
                </div>
                <div class="flex items-start gap-2 max-w-md bg-slate-50 border border-slate-200/50 p-2.5 rounded-xl flex-1 sm:ml-auto" x-show="activeAiDetails.validation">
                    <i class="fa-solid fa-circle-check text-indigo-500 text-xs mt-0.5"></i>
                    <div class="text-left">
                        <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ __('Evidence Validation') }}</span>
                        <p class="text-[10px] text-slate-600 font-medium mt-0.5 leading-relaxed" x-html="formatMarkdown(activeAiDetails.validation)"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Evidence & Notes Detail Modal --}}
    <div x-show="showEvidenceModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak>
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md" @click="showEvidenceModal = false"></div>
        <div class="relative bg-white rounded-xl border border-slate-100 w-full max-w-2xl p-6 md:p-8 shadow-2xl space-y-6 z-10 max-h-[90vh] overflow-y-auto"
            @click.away="showEvidenceModal = false">
            
            {{-- Modal Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-700 rounded-lg flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-file-shield text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 text-[9px] font-black rounded-lg uppercase tracking-wider leading-none" x-text="activeEvidenceDetails.code"></span>
                            <span class="text-[8px] text-indigo-500 font-bold uppercase tracking-widest leading-none">{{ __('Assessor Notes & Evidence') }}</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight mt-1 leading-snug" x-text="activeEvidenceDetails.title"></h3>
                    </div>
                </div>
                <button @click="showEvidenceModal = false" class="w-8 h-8 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="space-y-5">
                {{-- Audit Notes Section --}}
                <div class="space-y-1.5">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block"><i class="fa-solid fa-file-pen text-slate-400 mr-1"></i>{{ __('Audit Notes') }}</span>
                    <div x-show="activeEvidenceDetails.notes" class="text-xs text-slate-850 font-medium leading-relaxed bg-amber-50/50 border border-amber-100/50 p-4 rounded-lg shadow-inner whitespace-pre-line" x-text="activeEvidenceDetails.notes"></div>
                    <div x-show="!activeEvidenceDetails.notes" class="text-xs text-slate-400 font-medium italic p-4 bg-slate-50 rounded-lg border border-slate-100 text-center">{{ __('No audit notes have been provided for this control.') }}</div>
                </div>

                {{-- Evidence Files Section --}}
                <div class="space-y-1.5">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block"><i class="fa-solid fa-paperclip text-slate-400 mr-1"></i>{{ __('Attached Evidence Files') }}</span>
                    
                    {{-- Files List --}}
                    <div x-show="activeEvidenceDetails.files && activeEvidenceDetails.files.length > 0" class="grid grid-cols-1 gap-2">
                        <template x-for="(file, index) in activeEvidenceDetails.files" :key="index">
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/60 rounded-xl hover:bg-slate-100/50 transition-all">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-file-lines text-sm"></i>
                                    </div>
                                    <a :href="file.url" target="_blank" class="text-xs font-bold text-slate-700 truncate max-w-md hover:text-indigo-600 hover:underline" x-text="file.name"></a>
                                </div>
                                <a :href="file.url" target="_blank" class="px-3.5 py-1.5 bg-indigo-550 hover:bg-indigo-600 text-white rounded-lg text-[9px] font-black uppercase tracking-wider shadow-sm flex items-center gap-1.5 hover:scale-102 transition-all">
                                    {{ __('View File') }}
                                    <i class="fa-solid fa-up-right-from-square text-[9px]"></i>
                                </a>
                            </div>
                        </template>
                    </div>

                    {{-- Empty State --}}
                    <div x-show="!activeEvidenceDetails.files || activeEvidenceDetails.files.length === 0" class="text-xs text-slate-400 font-medium italic p-4 bg-slate-50 rounded-lg border border-slate-100 text-center">
                        {{ __('No evidence files have been attached to this control.') }}
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button @click="showEvidenceModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[9px] font-black uppercase tracking-widest transition-all">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>

</div> {{-- end root Alpine div --}}

@push('scripts')
<script>
(function() {
    if (document.documentElement.hasAttribute("data-turbo-preview")) return;
    const params = new URLSearchParams(window.location.search);
    const focusId = params.get('focus');
    if (!focusId) return;
    let attempts = 0;
    const interval = setInterval(() => {
        const target = document.getElementById('row-' + focusId);
        if (target) {
            clearInterval(interval);
            setTimeout(() => {
                const scrollContainer = document.querySelector('.overflow-y-auto');
                if (scrollContainer) {
                    const containerRect = scrollContainer.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();
                    const offset = targetRect.top - containerRect.top + scrollContainer.scrollTop - 150;
                    scrollContainer.scrollTo({ top: offset, behavior: 'smooth' });
                }
                target.style.transition = 'all 0.5s ease';
                target.style.backgroundColor = '#eff6ff';
                target.style.boxShadow = '0 0 0 3px #60a5fa';
                setTimeout(() => {
                    target.style.backgroundColor = '';
                    target.style.boxShadow = '';
                }, 3000);
            }, 100);
        }
        attempts++;
        if (attempts > 30) clearInterval(interval);
    }, 100);
})();
</script>
@endpush

@endsection
