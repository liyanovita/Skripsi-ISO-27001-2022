@extends('layouts.app')
@section('title', 'Improvement Tracking')
@section('view_name', 'Improvement Tracking')

@section('content')
@php
    $workspaceControls = $results->map(fn($result) => [
        'id' => $result->id,
        'code' => strtolower($result->standard->code ?? ''),
        'title' => strtolower($result->standard->title ?? ''),
        'risk' => strtolower($result->risk_level ?? 'low'),
        'maturityGap' => $result->is_applicable && $result->status === 'completed' && $result->maturity_rating !== null && $result->maturity_rating < 5,
        'isGap' => $result->is_applicable && $result->status === 'completed' && $result->maturity_rating !== null && $result->maturity_rating < 5,
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
    filterOption: 'gaps',
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
    showManageModal: false,
    activeManageDetails: { id: null, code: '', title: '', pic: '', dueDate: '', status: 'open', notes: '', files: [] },
    submittingManage: false,
    openEvidenceDetails(details) {
        this.activeEvidenceDetails = {
            code: details.code || '',
            title: details.title || '',
            notes: details.notes || '',
            files: details.files || []
        };
        this.showEvidenceModal = true;
    },
    openManageModal(details) {
        this.activeManageDetails = {
            id: details.id,
            code: details.code || '',
            title: details.title || '',
            complianceStatus: details.complianceStatus || 'Non-Compliant',
            riskLevel: details.riskLevel || 'Low',
            maturity: details.maturity ?? 0,
            gap: details.gap ?? 5,
            justification: details.justification || '',
            auditEvidence: details.auditEvidence || '',
            auditNotes: details.auditNotes || '',
            aiPlan: details.aiPlan || '',
            aiInsight: details.aiInsight || '',
            pic: details.pic || '',
            dueDate: details.dueDate || '',
            status: details.status || 'open',
            notes: '',
            files: details.files || []
        };
        this.showManageModal = true;
    },
    async submitManageForm() {
        if (!this.activeManageDetails.id) return;
        this.submittingManage = true;
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('treatment_pic', this.activeManageDetails.pic || '');
        formData.append('treatment_due_date', this.activeManageDetails.dueDate || '');
        formData.append('treatment_status', this.activeManageDetails.status || 'open');
        formData.append('notes', this.activeManageDetails.notes || '');
        
        const fileInput = this.$refs.manageFileInput;
        if (fileInput && fileInput.files.length > 0) {
            formData.append('evidence_file', fileInput.files[0]);
        }

        try {
            const response = await fetch(`/workspace/entry/${this.activeManageDetails.id}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await response.json();
            if (response.ok && data.success) {
                if (data.data.evidence_files) {
                    this.activeManageDetails.files = data.data.evidence_files;
                }
                const control = this.controls.find(c => c.id === this.activeManageDetails.id);
                if (control) {
                    control.treatmentStatus = data.data.treatment_status;
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ __("Remediation updated successfully!") }}',
                    showConfirmButton: false,
                    timer: 2000
                });
                this.showManageModal = false;
                window.location.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Save Failed") }}',
                    text: data.message || '{{ __("An error occurred while saving data.") }}'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: '{{ __("Error") }}',
                text: '{{ __("Network error occurred.") }}'
            });
        } finally {
            this.submittingManage = false;
        }
    },
    get filteredControls() {
        return this.controls.filter((control) => {
            let matchesOption = true;
            if (this.filterOption === 'gaps') {
                matchesOption = control.isGap && control.isApplicable;
            } else if (this.filterOption === 'applicable') {
                matchesOption = control.isApplicable;
            } else if (this.filterOption === 'excluded') {
                matchesOption = !control.isApplicable;
            }

            let matchesRisk = (this.riskFilter === 'all' || this.riskFilter === control.risk);

            let matchesSearch = (this.searchQuery === '' ||
                control.code.includes(this.searchQuery.toLowerCase()) ||
                control.title.includes(this.searchQuery.toLowerCase()));

            return matchesOption && matchesRisk && matchesSearch;
        });
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
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                <i class="fa-solid fa-table-cells-large text-lg"></i>
            </div>
            <div class="leading-none">
                <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">{{ __('Improvement Tracking') }}</h1>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Gap Report, SoA & Treatment Management') }}</p>
            </div>
        </div>

        {{-- Session selector --}}
        <div class="flex items-center gap-3">
            <form action="{{ route('workspace.index') }}" method="GET" id="workspaceFilter" class="flex items-center gap-3">
                <select name="session_id" onchange="document.getElementById('workspaceFilter').requestSubmit()"
                    class="bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 outline-none focus:ring-4 focus:ring-blue-600/5 transition-all min-w-[260px] cursor-pointer shadow-sm">
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
            <a href="{{ route('sessions.index') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/20">
                <i class="fa-solid fa-plus"></i> {{ __('Create Session') }}
            </a>
        </div>
    @else

    {{-- Dashboard Overview (Unified Stats & Session Comparison) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Stats Grid (Left 2 cols on LG) --}}
        <div class="lg:col-span-2">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-start">
                {{-- Total Items --}}
                <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm hover:shadow transition-all group flex flex-col justify-between min-h-[90px]">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ __('Total Items') }}</p>
                            <p class="text-xl font-black text-slate-900 mt-1">137</p>
                        </div>
                        <div class="w-6.5 h-6.5 bg-slate-50 text-slate-400 rounded-lg flex items-center justify-center border border-slate-100 shrink-0">
                            <i class="fa-solid fa-table-list text-[9px]"></i>
                        </div>
                    </div>
                    <p class="text-[8px] font-bold text-slate-400 mt-2">{{ __('All ISO 27001:2022 items') }}</p>
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
            {{-- View / Applicability Filter --}}
            <div class="flex items-center gap-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mr-1.5">{{ __('View:') }}</span>
                <button @click="filterOption = 'gaps'"
                    :class="filterOption === 'gaps' ? 'bg-rose-600 text-white shadow shadow-rose-600/20' : 'bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-100'"
                    class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-filter text-[7px] mr-1"></i>{{ __('Gaps Only') }}
                </button>
                <button @click="filterOption = 'applicable'"
                    :class="filterOption === 'applicable' ? 'bg-emerald-600 text-white shadow shadow-blue-600/20' : 'bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-100'"
                    class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                    {{ __('Applicable') }}
                </button>
                <button @click="filterOption = 'excluded'"
                    :class="filterOption === 'excluded' ? 'bg-slate-800 text-white shadow' : 'bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-100'"
                    class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                    {{ __('Excluded (N/A)') }}
                </button>
            </div>
            <div class="w-px h-6 bg-slate-200 hidden md:block"></div>
            {{-- Risk Filter --}}
            <div class="flex items-center gap-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mr-1.5">{{ __('Risk:') }}</span>
                @foreach(['all' => __('All'), 'high' => __('High'), 'medium' => __('Medium'), 'low' => __('Low')] as $val => $label)
                <button @click="riskFilter = '{{ $val }}'"
                    :class="riskFilter === '{{ $val }}' 
                        ? ('{{ $val }}' === 'high' ? 'bg-rose-600 text-white shadow shadow-rose-600/20' 
                          : '{{ $val }}' === 'medium' ? 'bg-amber-500 text-white shadow shadow-amber-500/20' 
                          : '{{ $val }}' === 'low' ? 'bg-emerald-600 text-white shadow shadow-blue-600/20' 
                          : 'bg-slate-900 text-white shadow') 
                        : 'bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-100'"
                    class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <div class="w-px h-6 bg-slate-200 hidden xl:block"></div>
            <div class="relative w-full xl:w-auto">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                <input type="text" x-model="searchQuery" placeholder="{{ __('Search control or title...') }}"
                    class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/30 transition-all w-full xl:w-64 placeholder:text-slate-400">
            </div>
        </div>
        <div class="xl:ml-auto self-start xl:self-center">
            <span class="text-[8px] font-bold uppercase tracking-widest flex items-center gap-1"
                :class="{
                    'text-blue-600': saveState === 'saving',
                    'text-emerald-600': saveState === 'saved' || saveState === 'ready',
                    'text-rose-600': saveState === 'error'
                }">
                <span class="w-2 h-2 rounded-full inline-block"
                    :class="{
                        'bg-blue-500 animate-pulse': saveState === 'saving',
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
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Control & Title') }}</th>
                        <th class="px-4 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('Evaluation & Risk') }}</th>
                        <th class="px-4 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('Maturity & Gap') }}</th>
                        <th class="px-4 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('Action Status') }}</th>
                        <th class="px-4 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('PIC Assigned') }}</th>
                        <th class="px-4 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($results as $result)
                    @php
                        $isScored     = $result->status === 'completed';
                        $isClause     = in_array($result->standard->type ?? '', ['clause', 'clausa']);
                        $isGap        = $isScored && $result->maturity_rating !== null && $result->maturity_rating < 5;
                        $isApplicable = $isClause ? true : (bool) $result->is_applicable;
                        $dueDate      = $result->treatment_due_date ? $result->treatment_due_date->format('Y-m-d') : '';
                        $status       = $result->treatment_status ?? 'open';
                        $riskLevelLabel = $result->risk_level ?? 'Low';
                        $complianceStatus = $result->compliance_status ?? 'Non-Compliant';

                        $riskClass = match(strtolower($riskLevelLabel)) {
                            'critical' => 'text-rose-700 bg-rose-50 border-rose-100',
                            'high'     => 'text-orange-700 bg-orange-50 border-orange-100',
                            'medium'   => 'text-amber-700 bg-amber-50 border-amber-100',
                            default    => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                        };

                        $complianceClass = match(strtolower($complianceStatus)) {
                            'compliant'           => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                            'partially compliant' => 'text-amber-700 bg-amber-50 border-amber-100',
                            default               => 'text-rose-700 bg-rose-50 border-rose-100',
                        };

                        $statusBadgeClass = match($status) {
                            'closed'      => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                            'in_progress' => 'text-amber-700 bg-amber-50 border-amber-200',
                            default       => 'text-rose-700 bg-rose-50 border-rose-200',
                        };

                        $statusLabel = match($status) {
                            'closed'      => __('Closed / Solved'),
                            'in_progress' => __('In Progress'),
                            default       => __('Open'),
                        };

                        $evidenceFiles = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);
                        $mappedFiles = [];
                        foreach ($evidenceFiles as $file) {
                            $mappedFiles[] = [
                                'name' => basename($file),
                                'url'  => route('results.evidence', [$result->id, 'file' => $file])
                            ];
                        }
                        $aiPlanText = is_array($result->corrective_action_plan) 
                            ? implode("\n", array_map(fn($item) => is_array($item) ? implode(' ', $item) : (string)$item, $result->corrective_action_plan)) 
                            : ($result->corrective_action_plan ?? '');
                        $aiInsightText = $result->control_insight ?? $result->ai_recommendation ?? '';
                    @endphp
                    <tr id="row-{{ $result->id }}"
                        class="hover:bg-slate-50/50 transition-all group"
                        x-show="isControlVisible({{ $result->id }})">
                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-black rounded-lg uppercase tracking-wider shrink-0">
                                        {{ $result->standard->code }}
                                    </span>
                                    <span class="text-xs font-black text-slate-900 tracking-tight leading-snug">
                                        {{ __($result->standard->title) }}
                                    </span>
                                </div>
                                @if(!empty($result->soa_justification))
                                    <p class="text-[10px] text-slate-500 font-medium italic mt-1 line-clamp-1">
                                        {{ $result->soa_justification }}
                                    </p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="px-2.5 py-1 rounded-lg border text-[9px] font-black uppercase tracking-wider inline-block text-center {{ $complianceClass }}">
                                    {{ $complianceStatus }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md border text-[8px] font-black uppercase tracking-wider inline-block text-center {{ $riskClass }}">
                                    {{ __('Risk') }}: {{ $riskLevelLabel }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <div class="inline-flex items-baseline">
                                    <span class="text-sm font-black text-slate-900 leading-none">{{ $result->maturity_rating }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold ml-0.5">/5</span>
                                </div>
                                <span class="text-[9px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">
                                    Gap: {{ 5 - $result->maturity_rating }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $statusBadgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                                @if($dueDate)
                                    <span class="text-[8px] font-bold text-slate-400 flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-[8px]"></i> {{ $dueDate }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700">
                                <i class="fa-solid fa-user-gear text-[9px] text-slate-400"></i>
                                <span>{{ $result->treatment_pic ?: __('Unassigned') }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="inline-flex items-center justify-center gap-1.5 flex-wrap">
                                @if($result->ai_recommendation || $result->control_insight || $result->impact_interpretation || $result->corrective_action_plan)
                                    <button type="button"
                                        @click="openAiDetails({
                                            code: @js($result->standard->code),
                                            title: @js(__($result->standard->title)),
                                            rec: @js($result->ai_recommendation ?? ''),
                                            plan: @js($aiPlanText),
                                            insight: @js($result->control_insight ?? ''),
                                            priority: @js($result->calculated_risk_priority ?? ''),
                                            validation: @js($result->evidence_validation ?? ''),
                                            impact: @js($result->impact_interpretation ?? '')
                                        })"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 rounded-xl text-[10px] font-black uppercase tracking-wider border border-blue-200 transition-all shadow-2xs active:scale-95 cursor-pointer">
                                        <i class="fa-solid fa-robot text-[10px]"></i>
                                        <span>{{ __('Detail AI') }}</span>
                                    </button>
                                @endif
                                <button @click="openManageModal({
                                        id: {{ $result->id }},
                                        code: @js($result->standard->code),
                                        title: @js(__($result->standard->title)),
                                        complianceStatus: @js($complianceStatus),
                                        riskLevel: @js($riskLevelLabel),
                                        maturity: @js($result->maturity_rating),
                                        gap: @js(5 - ($result->maturity_rating ?? 0)),
                                        justification: @js($result->soa_justification ?? ''),
                                        auditEvidence: @js($result->evidence ?? ''),
                                        auditNotes: @js($result->notes ?? ''),
                                        aiPlan: @js($aiPlanText),
                                        aiInsight: @js($aiInsightText),
                                        rec: @js($result->ai_recommendation ?? ''),
                                        impact: @js($result->impact_interpretation ?? ''),
                                        pic: @js($result->treatment_pic ?? ''),
                                        dueDate: @js($dueDate),
                                        status: @js($status),
                                        notes: @js($result->notes ?? ''),
                                        files: @js($mappedFiles)
                                    })"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm transition-all hover:scale-105 active:scale-95 cursor-pointer">
                                    <i class="fa-solid fa-file-pen text-[10px]"></i>
                                    <span>{{ __('Remediate') }}</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-clipboard text-2xl text-slate-300"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('No compliance gaps found') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif


    {{-- AI Detail Modal (shared, lives in root Alpine scope) --}}
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
        <div class="relative bg-white rounded-3xl border border-slate-100 w-full max-w-3xl p-6 md:p-8 shadow-2xl space-y-6 z-10 max-h-[90vh] overflow-y-auto"
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
                    <div class="w-10 h-10 bg-blue-50 text-blue-700 rounded-lg flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-file-shield text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-700 text-[9px] font-black rounded-lg uppercase tracking-wider leading-none" x-text="activeEvidenceDetails.code"></span>
                            <span class="text-[8px] text-blue-500 font-bold uppercase tracking-widest leading-none">{{ __('Assessor Notes & Evidence') }}</span>
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
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-file-lines text-sm"></i>
                                    </div>
                                    <a :href="file.url" target="_blank" class="text-xs font-bold text-slate-700 truncate max-w-md hover:text-blue-600 hover:underline" x-text="file.name"></a>
                                </div>
                                <a :href="file.url" target="_blank" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-600 text-white rounded-lg text-[9px] font-black uppercase tracking-wider shadow-sm flex items-center gap-1.5 hover:scale-102 transition-all">
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

    {{-- Manage Perbaikan Modal --}}
    <div x-show="showManageModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak>
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md" @click="showManageModal = false"></div>
        <div class="relative bg-white rounded-2xl border border-slate-100 w-full max-w-2xl p-6 md:p-8 shadow-2xl space-y-6 z-10 max-h-[90vh] overflow-y-auto"
            @click.away="showManageModal = false">
            
            {{-- Modal Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shadow-sm border border-blue-100">
                        <i class="fa-solid fa-file-arrow-up text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-black rounded-lg uppercase tracking-wider leading-none" x-text="activeManageDetails.code"></span>
                            <span class="text-[9px] text-blue-500 font-bold uppercase tracking-widest leading-none">{{ __('Remediation Hub') }}</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight mt-1 leading-snug" x-text="activeManageDetails.title"></h3>
                    </div>
                </div>
                <button @click="showManageModal = false" class="w-8 h-8 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Modal Body Form --}}
            <form @submit.prevent="submitManageForm()" class="space-y-6">
                {{-- Top Action Settings (Placed directly below header) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-blue-50/50 p-3.5 rounded-2xl border border-blue-100">
                    <div>
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block mb-1"><i class="fa-solid fa-user-gear text-blue-600 mr-1"></i>{{ __('PIC Assigned') }}</label>
                        <select x-model="activeManageDetails.pic" class="w-full text-xs font-medium text-slate-800 border border-slate-200 rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-blue-500/30 bg-white">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach($users as $userOption)
                                <option value="{{ $userOption->name }}">{{ $userOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block mb-1"><i class="fa-regular fa-calendar-check text-blue-600 mr-1"></i>{{ __('Target Deadline') }}</label>
                        <input type="date" x-model="activeManageDetails.dueDate" class="w-full text-xs font-bold text-slate-800 border border-slate-200 rounded-xl p-2 outline-none focus:ring-2 focus:ring-blue-500/30 bg-white">
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block mb-1"><i class="fa-solid fa-list-check text-blue-600 mr-1"></i>{{ __('Action Status') }}</label>
                        <select x-model="activeManageDetails.status" class="w-full text-xs font-black uppercase tracking-wider border border-slate-200 rounded-xl p-2.5 outline-none focus:ring-2 focus:ring-blue-500/30 bg-white">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="closed">Closed / Solved</option>
                        </select>
                    </div>
                </div>

                {{-- Read-Only Initial Audit Context Card --}}
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-1.5">
                            <i class="fa-solid fa-clipboard-check text-blue-500"></i>{{ __('Initial Audit Context (Read-Only)') }}
                        </span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Original Evaluation Data') }}</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('Compliance') }}</span>
                            <span class="text-xs font-black text-slate-800" x-text="activeManageDetails.complianceStatus"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('Risk Level') }}</span>
                            <span class="text-xs font-black text-rose-600" x-text="activeManageDetails.riskLevel"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('Maturity Score') }}</span>
                            <span class="text-xs font-black text-slate-900"><span x-text="activeManageDetails.maturity"></span>/5</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('Maturity Gap') }}</span>
                            <span class="text-xs font-black text-rose-600"><span x-text="activeManageDetails.gap"></span> Points</span>
                        </div>
                    </div>

                    {{-- SoA Justification --}}
                    <template x-if="activeManageDetails.justification">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block"><i class="fa-solid fa-align-left text-slate-400 mr-1"></i>{{ __('SoA Justification') }}</span>
                            <p class="text-xs font-medium text-slate-700 leading-relaxed italic" x-text="activeManageDetails.justification"></p>
                        </div>
                    </template>

                    {{-- Initial Audit Evidence / Findings --}}
                    <template x-if="activeManageDetails.auditEvidence">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block"><i class="fa-solid fa-file-lines text-slate-400 mr-1"></i>{{ __('Initial Audit Findings & Evidence') }}</span>
                            <p class="text-xs font-medium text-slate-700 leading-relaxed" x-text="activeManageDetails.auditEvidence"></p>
                        </div>
                    </template>

                    {{-- Initial Assessment Notes --}}
                    <template x-if="activeManageDetails.auditNotes">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block"><i class="fa-solid fa-note-sticky text-slate-400 mr-1"></i>{{ __('Initial Assessment Notes') }}</span>
                            <p class="text-xs font-medium text-slate-700 leading-relaxed" x-text="activeManageDetails.auditNotes"></p>
                        </div>
                    </template>

                    {{-- AI Control Compliance Synthesis (Dropdown Accordion) --}}
                    <template x-if="activeManageDetails.rec || activeManageDetails.aiInsight || activeManageDetails.aiPlan || activeManageDetails.impact">
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
                                <button type="button"
                                    @click="openAiDetails({
                                        code: activeManageDetails.code,
                                        title: activeManageDetails.title,
                                        rec: activeManageDetails.rec || activeManageDetails.aiInsight,
                                        plan: activeManageDetails.aiPlan,
                                        insight: activeManageDetails.aiInsight,
                                        priority: activeManageDetails.riskLevel,
                                        validation: '',
                                        impact: activeManageDetails.impact
                                    })"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 text-white font-bold text-[10px] hover:bg-blue-700 transition-all shadow-sm shadow-blue-600/20 active:scale-95 cursor-pointer">
                                    <i class="fa-solid fa-expand text-[9px]"></i> {{ __('Open Full Modal') }}
                                </button>
                            </div>

                            {{-- Dropdown Accordion List per Control --}}
                            <div class="space-y-2.5">
                                {{-- Accordion 1: STRATEGIC RECOMMENDATION --}}
                                <template x-if="activeManageDetails.rec || activeManageDetails.aiInsight">
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
                                            <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap" x-text="activeManageDetails.rec || activeManageDetails.aiInsight"></div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Accordion 2: CORRECTIVE ACTION PLAN --}}
                                <template x-if="activeManageDetails.aiPlan">
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
                                            <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap" x-text="activeManageDetails.aiPlan"></div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Accordion 3: AI AUDIT INSIGHT (GAP) --}}
                                <template x-if="activeManageDetails.aiInsight">
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
                                            <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap" x-text="activeManageDetails.aiInsight"></div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Accordion 4: IMPACT INTERPRETATION --}}
                                <template x-if="activeManageDetails.impact">
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
                                            <div class="p-4 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap" x-text="activeManageDetails.impact"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Upload Evidence File Section --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-700 uppercase tracking-widest block">
                        <i class="fa-solid fa-upload text-blue-600 mr-1.5"></i>{{ __('Upload Remediation Evidence File') }}
                    </label>
                    <div class="p-4 border-2 border-dashed border-slate-200 hover:border-blue-400 rounded-xl bg-slate-50/50 transition-all text-center group">
                        <input type="file" x-ref="manageFileInput" class="hidden" id="manage_file_input">
                        <label for="manage_file_input" class="cursor-pointer flex flex-col items-center justify-center space-y-2">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-700 group-hover:text-blue-600 transition-colors">{{ __('Choose remediation evidence file') }}</span>
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('PDF, PNG, JPG, DOCX, XLSX, ZIP (Max: 10MB)') }}</span>
                        </label>
                    </div>
                </div>

                {{-- Remediation Notes / Action Remarks (Default Blank) --}}
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-700 uppercase tracking-widest block">
                        <i class="fa-solid fa-file-pen text-slate-500 mr-1.5"></i>{{ __('Remediation Notes & Action Remarks') }}
                    </label>
                    <textarea x-model="activeManageDetails.notes" rows="3" placeholder="{{ __('Write remediation notes or progress details of corrective actions...') }}"
                        class="w-full text-xs font-medium text-slate-800 border border-slate-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500/30 transition-all placeholder:text-slate-400"></textarea>
                </div>

                {{-- Uploaded Files List --}}
                <div x-show="activeManageDetails.files && activeManageDetails.files.length > 0" class="space-y-2 pt-2 border-t border-slate-100">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block"><i class="fa-solid fa-paperclip mr-1"></i>{{ __('Previously Uploaded Evidence Files') }}</span>
                    <div class="space-y-1.5">
                        <template x-for="(file, index) in activeManageDetails.files" :key="index">
                            <div class="flex items-center justify-between p-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <i class="fa-solid fa-file-pdf text-blue-600"></i>
                                    <span class="font-bold text-slate-700 truncate" x-text="file.name"></span>
                                </div>
                                <a :href="file.url" target="_blank" class="text-[10px] font-black text-blue-600 hover:underline uppercase tracking-wider shrink-0">
                                    {{ __('View File') }} <i class="fa-solid fa-up-right-from-square ml-0.5"></i>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showManageModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" :disabled="submittingManage" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md transition-all flex items-center gap-2">
                        <i class="fa-solid fa-spinner animate-spin" x-show="submittingManage"></i>
                        <i class="fa-solid fa-floppy-disk" x-show="!submittingManage"></i>
                        <span>{{ __('Save Remediation') }}</span>
                    </button>
                </div>
            </form>
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
