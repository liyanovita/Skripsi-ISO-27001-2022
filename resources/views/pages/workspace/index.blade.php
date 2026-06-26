@extends('layouts.app')
@section('title', 'Compliance Center')
@section('view_name', 'Compliance Center')

@section('content')
@php
    $workspaceControls = $results->map(fn($result) => [
        'id' => $result->id,
        'code' => strtolower($result->standard->code ?? ''),
        'title' => strtolower($result->standard->title ?? ''),
        'risk' => strtolower($result->risk_level ?? 'compliant'),
        'maturityGap' => $result->status === 'completed' && $result->maturity_rating < 4,
        'isGap' => $result->is_applicable && $result->status === 'completed' && $result->maturity_rating < 4,
        'isApplicable' => (bool) $result->is_applicable,
        'treatmentStatus' => $result->treatment_status ?? 'open',
    ])->values();
    $gapFindings = $findings->map(fn($finding) => [
        'id' => $finding->id,
        'risk' => $finding->risk_level ?? 'Compliant',
        'isCritical' => $finding->risk_level === 'Critical' || $finding->maturity_rating <= 1,
        'isApplicable' => (bool) $finding->is_applicable,
    ])->values();
@endphp
<div class="max-w-[1400px] mx-auto space-y-3 pb-6" x-data="{
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
    activeAiDetails: { code: '', title: '', rec: '', plan: '', insight: '', priority: '', validation: '' },
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
            validation: details.validation || ''
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

        {{-- Session selector & Actions --}}
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('workspace.index') }}" method="GET" id="workspaceFilter" class="flex items-center gap-3">
                <input type="hidden" name="tab" :value="activeTab">
                <select name="session_id" onchange="document.getElementById('workspaceFilter').submit()"
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
            @if($selectedSession)
            <div class="flex flex-wrap gap-2" x-show="activeTab === 'workspace'">
                <a href="{{ route('workspace.export-soa', $selectedSession->id) }}" class="px-3 py-2 bg-slate-900 text-white rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-slate-800 shadow-md transition-all flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-file-excel text-emerald-400"></i>{{ __('SoA Excel') }}</a>
                <a href="{{ route('workspace.export-soa-pdf', $selectedSession->id) }}" class="px-3 py-2 bg-red-700 text-white rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-600 shadow-md transition-all flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-file-pdf text-white"></i>{{ __('SoA PDF') }}</a>
            </div>
            <div class="flex flex-wrap gap-2" x-show="activeTab === 'gap-report'" x-cloak>
                <a href="{{ route('reports.export-pdf', $selectedSession->id) }}" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[9px] font-black uppercase tracking-widest text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-file-pdf text-red-500"></i>{{ __('PDF') }}</a>
                <a href="{{ route('reports.export-excel', $selectedSession->id) }}" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[9px] font-black uppercase tracking-widest text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>{{ __('Excel') }}</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex items-center gap-1 bg-white p-1.5 rounded-xl border border-slate-100 shadow-sm w-fit">
        <button @click="switchTab('gap-report')"
            :class="activeTab === 'gap-report' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-500 hover:bg-slate-50'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">
            <i class="fa-solid fa-chart-column text-xs"></i>
            {{ __('Gap Report') }}
        </button>
        <button @click="switchTab('workspace')"
            :class="activeTab === 'workspace' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-500 hover:bg-slate-50'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">
            <i class="fa-solid fa-table-cells-large text-xs"></i>
            {{ __('Workspace') }}
        </button>
    </div>


    {{-- ================================================================ --}}
    {{-- TAB: GAP REPORT                                                  --}}
    {{-- ================================================================ --}}
    <div x-show="activeTab === 'gap-report'" x-cloak>
    @if(!$selectedSession)
        <div class="bg-white rounded-lg border border-slate-100 p-16 text-center shadow-sm">
            <div class="w-16 h-16 bg-slate-50 rounded-lg flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-folder-open text-2xl text-slate-300"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900">{{ __('No Assessment Data') }}</h3>
            <p class="text-sm text-slate-400 font-medium mt-1">{{ __('Create an audit session first to see gap reports.') }}</p>
            <a href="{{ route('sessions.index') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-plus"></i> {{ __('Create Session') }}
            </a>
        </div>
    @else

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Total Gaps') }}</p>
            <p class="text-3xl font-black text-rose-600" x-text="gapStats.totalGaps"></p>
            <p class="text-[9px] font-bold text-slate-400 mt-1">{{ __('controls below target') }}</p>
        </div>
        <div class="bg-rose-50 p-4 rounded-lg border border-rose-100 shadow-sm">
            <p class="text-[9px] font-bold text-rose-600 uppercase tracking-widest mb-1">{{ __('Critical') }}</p>
            <p class="text-3xl font-black text-rose-700" x-text="gapStats.critical"></p>
            <p class="text-[9px] font-bold text-rose-400 mt-1">{{ __('immediate action required') }}</p>
        </div>
        <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100 shadow-sm">
            <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mb-1">{{ __('Compliant') }}</p>
            <p class="text-3xl font-black text-emerald-700" x-text="gapStats.compliant"></p>
            <p class="text-[9px] font-bold text-emerald-400 mt-1">{{ __('of') }} <span x-text="gapStats.totalControls"></span> {{ __('controls') }}</p>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 shadow-sm">
            <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest mb-1">{{ __('Scored') }}</p>
            <p class="text-3xl font-black text-blue-700" x-text="gapStats.scored"></p>
            <p class="text-[9px] font-bold text-blue-400 mt-1"><span x-text="gapStats.totalControls - gapStats.scored"></span> {{ __('remaining') }}</p>
        </div>
    </div>


    {{-- Session Comparison --}}
    @if($comparison && isset($comparison['delta']))
    <div class="bg-white p-5 rounded-lg border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">{{ __('Session Comparison') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('Current vs Previous Cycle') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Score') }}</p>
                    <p class="text-2xl font-black text-slate-900">{{ number_format($comparison['latest_score'], 1) }}<span class="text-sm text-slate-400">/5</span></p>
                </div>
                @if($comparison['delta'] != 0)
                <span class="flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black {{ $comparison['delta'] > 0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                    <i class="fa-solid {{ $comparison['delta'] > 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                    {{ $comparison['delta'] > 0 ? '+' : '' }}{{ number_format($comparison['delta'], 1) }}
                </span>
                @else
                <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-slate-50 text-slate-500 border border-slate-200">{{ __('No change') }}</span>
                @endif
            </div>
        </div>
        <div class="space-y-2.5">
            @foreach($comparison['domains'] as $domain)
            @php $pct = min(($domain['latest'] / 5) * 100, 100); @endphp
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-bold text-slate-600 w-20 shrink-0">{{ $domain['label'] }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $domain['latest'] >= 4 ? 'bg-emerald-500' : ($domain['latest'] >= 3 ? 'bg-amber-400' : 'bg-rose-500') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-[10px] font-black text-slate-700 w-6 text-right">{{ number_format($domain['latest'], 1) }}</span>
                    @if($domain['delta'] != 0)
                    <span class="text-[9px] font-bold {{ $domain['delta'] > 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                        {{ $domain['delta'] > 0 ? '+' : '' }}{{ number_format($domain['delta'], 1) }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- Gap Findings Table --}}
    <div class="bg-white rounded-lg border border-slate-100 shadow-sm overflow-hidden" x-data="{
        gapRiskFilter: 'all',
        get filteredGapFindings() {
            return this.gapFindings.filter((finding) =>
                finding.isApplicable && (this.gapRiskFilter === 'all' || finding.risk === this.gapRiskFilter)
            );
        },
        isGapFindingVisible(resultId) {
            return this.filteredGapFindings.some((finding) => finding.id === resultId);
        }
    }">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">{{ __('Gap Findings') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5"><span x-text="gapStats.totalGaps"></span> {{ __('controls requiring attention') }}</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mr-1">{{ __('Risk:') }}</span>
                @foreach(['all' => __('All'), 'Critical' => __('Critical'), 'High' => __('High'), 'Medium' => __('Medium')] as $val => $label)
                <button @click="gapRiskFilter = '{{ $val }}'"
                    :class="gapRiskFilter === '{{ $val }}' 
                        ? ('{{ $val }}' === 'Critical' ? 'bg-rose-600 text-white shadow shadow-rose-600/20' 
                          : '{{ $val }}' === 'High' ? 'bg-orange-500 text-white shadow shadow-orange-500/20' 
                          : '{{ $val }}' === 'Medium' ? 'bg-amber-500 text-white shadow shadow-amber-500/20' 
                          : 'bg-slate-900 text-white shadow') 
                        : 'bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-100'"
                    class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
        @if($findings->isEmpty())
        <div class="p-12 text-center">
            <div class="w-14 h-14 bg-emerald-50 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-circle-check text-2xl text-emerald-400"></i>
            </div>
            <p class="text-sm font-bold text-slate-500">{{ __('No gaps found') }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ __('All scored controls meet the target maturity level.') }}</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Control') }}</th>
                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Risk Level') }}</th>
                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Maturity') }}</th>
                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Gap') }}</th>
                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('PIC') }}</th>
                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Deadline') }}</th>
                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($findings as $finding)
                    @php
                        $riskLevel  = $finding->risk_level ?? 'Compliant';
                        $riskClass  = match(strtolower($riskLevel)) {
                            'critical'  => 'bg-rose-100 text-rose-700',
                            'high'      => 'bg-orange-100 text-orange-700',
                            'medium'    => 'bg-amber-100 text-amber-700',
                            'compliant' => 'bg-emerald-100 text-emerald-700',
                            default     => 'bg-slate-100 text-slate-600',
                        };
                        $gapPct    = (5 - $finding->maturity_rating) * 20;
                        $isOverdue = $finding->treatment_due_date && $finding->treatment_due_date->isPast();
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors"
                        x-show="isGapFindingVisible({{ $finding->id }})">
                        <td class="px-4 py-3">
                            <span class="text-xs font-black text-slate-900">{{ $finding->standard->code }}</span>
                            <p class="text-[10px] text-slate-500 font-medium mt-0.5 max-w-[200px] truncate">{{ $finding->standard->title }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $riskClass }}">{{ $riskLevel }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-slate-900">{{ $finding->maturity_rating }}</span>
                                <span class="text-[9px] text-slate-400 font-bold">/5</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-rose-500 h-full rounded-full" style="width: {{ $gapPct }}%"></div>
                                </div>
                                <span class="text-[9px] font-bold text-slate-500">{{ $gapPct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($finding->treatment_pic)
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[7px] font-black shrink-0">
                                    {{ strtoupper(substr($finding->treatment_pic, 0, 2)) }}
                                </div>
                                <span class="text-[10px] font-bold text-slate-700 truncate max-w-[80px]">{{ $finding->treatment_pic }}</span>
                            </div>
                            @else
                            <span class="text-[9px] text-slate-300 italic">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($finding->treatment_due_date)
                            <span class="text-[10px] font-bold {{ $isOverdue ? 'text-rose-600' : 'text-slate-700' }}">
                                {{ $finding->treatment_due_date->format('d M Y') }}
                            </span>
                            @if($isOverdue)<p class="text-[8px] font-bold text-rose-400 uppercase tracking-widest">{{ __('Overdue') }}</p>@endif
                            @else
                            <span class="text-[9px] text-slate-300 italic">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(!empty($finding->ai_recommendation))
                                @php
                                    $aiGapBtnClass = match(strtolower($finding->risk_level ?? '')) {
                                        'critical' => 'bg-rose-50 text-rose-700 border-rose-100 hover:bg-rose-600 hover:text-white',
                                        'high'     => 'bg-orange-50 text-orange-700 border-orange-100 hover:bg-orange-500 hover:text-white',
                                        'medium'   => 'bg-amber-50 text-amber-700 border-amber-100 hover:bg-amber-500 hover:text-white',
                                        default    => 'bg-indigo-50 text-indigo-700 border-indigo-100 hover:bg-indigo-600 hover:text-white',
                                    };
                                    $aiGapPlan = is_array($finding->corrective_action_plan) ? implode("\n", $finding->corrective_action_plan) : ($finding->corrective_action_plan ?? '');
                                    $aiGapInsight = is_array($finding->control_insight) ? ($finding->control_insight['gap'] ?? '') : ($finding->control_insight ?? '');
                                @endphp
                                <button @click="openAiDetails({
                                        code: @js($finding->standard->code ?? ''),
                                        title: @js(__($finding->standard->title ?? '')),
                                        rec: @js($finding->ai_recommendation ?? ''),
                                        plan: @js($aiGapPlan),
                                        insight: @js($aiGapInsight),
                                        priority: @js($finding->risk_priority ?? ''),
                                        validation: @js($finding->evidence_validation ?? '')
                                    })"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border {{ $aiGapBtnClass }} rounded-lg text-[8px] font-black uppercase tracking-widest transition-all shrink-0 shadow-sm hover:scale-105 active:scale-95 cursor-pointer"
                                    title="{{ __('View AI Recommendation') }}">
                                    <i class="fa-solid fa-robot text-[9px]"></i>{{ __('Detail AI') }}
                                </button>
                                @endif
                                <button @click="switchTab('workspace'); $nextTick(() => { const el = document.getElementById('row-{{ $finding->id }}'); if(el) { el.scrollIntoView({behavior:'smooth', block:'center'}); el.style.boxShadow='0 0 0 3px #818cf8'; setTimeout(()=>el.style.boxShadow='',3000); } })"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 transition-all"
                                    title="{{ __('Edit in Workspace') }}">
                                    <i class="fa-solid fa-pen-to-square text-[9px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    <tr x-show="filteredGapFindings.length === 0" x-cloak>
                        <td colspan="7" class="px-6 py-14 text-center">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-filter-circle-xmark text-xl text-slate-300"></i>
                            </div>
                            <p class="text-slate-500 font-black uppercase tracking-widest text-[10px]">{{ __('No matching gaps') }}</p>
                            <p class="text-slate-400 font-medium text-xs mt-1">{{ __('Try selecting a different risk level.') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Quick link to Strategic --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('reports.strategic', ['session_id' => $selectedSession->id]) }}"
           class="flex items-center gap-2 px-4 py-2 bg-white text-slate-700 border border-slate-200 rounded-xl text-xs font-bold hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm">
            <i class="fa-solid fa-chart-pie text-blue-500"></i> {{ __('Strategic Analytics') }}
        </a>
    </div>

    @endif {{-- end if selectedSession --}}
    </div> {{-- end gap-report tab --}}


    {{-- ================================================================ --}}
    {{-- TAB: WORKSPACE                                                   --}}
    {{-- ================================================================ --}}
    <div x-show="activeTab === 'workspace'" x-cloak>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg p-4 border border-slate-100 shadow-sm">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Controls') }}</p>
            <p class="text-2xl font-black text-slate-900 mt-1" x-text="workspaceStats.total"></p>
            <p class="text-[8px] font-bold text-slate-400 mt-1">{{ __('All ISO 27001:2022 controls') }}</p>
        </div>
        <div class="bg-rose-50 rounded-lg p-4 border border-rose-100 shadow-sm">
            <p class="text-[9px] font-bold text-rose-600 uppercase tracking-widest">{{ __('Identified Gaps') }}</p>
            <div class="flex items-baseline gap-1 mt-1">
                <p class="text-2xl font-black text-rose-700" x-text="workspaceStats.gaps"></p>
                <p class="text-sm font-bold text-rose-400">/ <span x-text="workspaceStats.total"></span></p>
            </div>
            <p class="text-[8px] font-bold text-rose-400 mt-1"><span x-text="workspaceStats.total - workspaceStats.gaps"></span> {{ __('Compliant') }}</p>
        </div>
        <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-100 shadow-sm">
            <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">{{ __('Applicable Controls') }}</p>
            <div class="flex items-baseline gap-1 mt-1">
                <p class="text-2xl font-black text-emerald-700" x-text="workspaceStats.applicable"></p>
                <p class="text-sm font-bold text-emerald-400">/ <span x-text="workspaceStats.total"></span></p>
            </div>
            <p class="text-[8px] font-bold text-emerald-400 mt-1"><span x-text="workspaceStats.notApplicable"></span> {{ __('excluded') }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 shadow-sm">
            <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest">{{ __('Treatments Closed') }}</p>
            <div class="flex items-baseline gap-1 mt-1">
                <p class="text-2xl font-black text-blue-700" x-text="workspaceStats.closed"></p>
                <p class="text-sm font-bold text-blue-400">/ <span x-text="workspaceStats.gaps"></span></p>
            </div>
            <p class="text-[8px] font-bold text-blue-400 mt-1"><span x-text="Math.max(workspaceStats.gaps - workspaceStats.closed, 0)"></span> {{ __('remaining') }}</p>
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
                @foreach(['all' => __('All'), 'critical' => __('Critical'), 'high' => __('High'), 'medium' => __('Medium'), 'compliant' => __('Compliant')] as $val => $label)
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
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-20">{{ __('ID') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-64">{{ __('Control Focus') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">{{ __('Applicable') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-48">{{ __('SoA Justification') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-20 text-center">{{ __('Score') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-32">{{ __('Treatment Due') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-40">{{ __('PIC') }}</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-32">{{ __('Action Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($results as $result)
                    @php
                        $isScored     = $result->status === 'completed';
                        $isGap        = $isScored && $result->maturity_rating < 4;
                        $isApplicable = $result->is_applicable;
                        $dueDate      = $result->treatment_due_date ? $result->treatment_due_date->format('Y-m-d') : '';
                        $status       = $result->treatment_status ?? 'open';
                        $riskLevel    = strtolower($result->risk_level ?? 'compliant');
                        $controlTitle = strtolower($result->standard->title ?? '');
                        $aiPlan       = is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : ($result->corrective_action_plan ?? '');
                    @endphp
                    <tr id="row-{{ $result->id }}"
                        class="hover:bg-slate-50/50 transition-all group"
                        x-data="{
                            isApplicable: {{ $isApplicable ? 'true' : 'false' }},
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
                            <span class="inline-flex items-center justify-center w-12 h-7 bg-slate-100 text-slate-800 font-black text-[9px] rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                {{ $result->standard->code }}
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex flex-col">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-[11px] font-bold text-slate-800 leading-tight">{{ __($result->standard->title) }}</span>
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
                                            validation: @js($result->evidence_validation ?? '')
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
                            <button @click="toggleApplicable()"
                                :class="isApplicable ? 'bg-emerald-600 text-white shadow-emerald-600/30 shadow' : 'bg-rose-100 text-rose-600 border border-rose-200'"
                                class="px-2 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all w-16">
                                <span x-text="isApplicable ? 'Yes' : 'No'"></span>
                            </button>
                        </td>
                        <td class="px-3 py-3">
                            <div x-show="!editingJust" @click="editingJust = true"
                                class="text-[9px] text-slate-600 font-medium cursor-text min-h-[26px] px-2 py-1 rounded-lg hover:bg-slate-100 transition-all border border-transparent hover:border-slate-200 flex items-center">
                                <span x-text="justification || '-'" :class="!justification && 'text-slate-300 italic'"></span>
                            </div>
                            <div x-show="editingJust" x-cloak>
                                <textarea x-model="justification" rows="2" @blur="save('soa_justification', justification); editingJust = false" @keydown.enter.prevent="$event.target.blur()"
                                    placeholder="{{ __('Justification...') }}"
                                    class="w-full text-[9px] font-medium border border-indigo-300 rounded-lg px-2 py-1 outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none transition-all"></textarea>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($isScored)
                            <div class="inline-flex items-center justify-center w-6 h-6 rounded-md font-black text-[10px] {{ $result->maturity_rating >= 4 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $result->maturity_rating }}
                            </div>
                            @else
                            <span class="text-[9px] text-slate-300 font-bold italic">-</span>
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
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-clipboard text-2xl text-slate-300"></i>
                            </div>
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">{{ __('No controls found for this session') }}</p>
                        </td>
                    </tr>
                    @endforelse
                    @if($results->isNotEmpty())
                    <tr x-show="filteredControls.length === 0" x-cloak>
                        <td colspan="8" class="px-6 py-14 text-center">
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
    @else
    <div class="bg-white rounded-lg border border-slate-100 p-16 text-center shadow-sm">
        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-folder-open text-3xl text-slate-300"></i>
        </div>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">{{ __('No assessment session found. Create a session first.') }}</p>
        <a href="{{ route('sessions.index') }}" class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-600/20">
            <i class="fa-solid fa-plus"></i> {{ __('Create Session') }}
        </a>
    </div>
    @endif

    </div> {{-- end workspace tab --}}


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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1.5 flex flex-col">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">{{ __('Strategic Recommendation') }}</span>
                    <div class="text-xs text-slate-700 font-medium leading-relaxed bg-slate-50 border border-slate-100 p-4 rounded-lg shadow-inner flex-1" x-text="activeAiDetails.rec"></div>
                </div>
                <div class="space-y-1.5 flex flex-col">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">{{ __('Corrective Action Plan') }}</span>
                    <div class="text-xs text-slate-700 font-medium leading-relaxed bg-slate-50 border border-slate-100 p-4 rounded-lg shadow-inner whitespace-pre-line flex-1" x-text="activeAiDetails.plan || 'No specific action plan drafted.'"></div>
                </div>
                <div class="space-y-1.5 flex flex-col">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">{{ __('AI Audit Insight (Gap)') }}</span>
                    <div class="text-xs text-slate-700 font-medium leading-relaxed bg-slate-50 border border-slate-100 p-4 rounded-lg shadow-inner flex-1" x-text="activeAiDetails.insight || 'Control shows solid operational alignment.'"></div>
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
                        <p class="text-[10px] text-slate-600 font-medium mt-0.5 leading-relaxed" x-text="activeAiDetails.validation"></p>
                    </div>
                </div>
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
