@extends('layouts.app')
@section('title', 'Audit Assessment')
@section('view_name', 'Audit Assessment')

@section('content')
@php
    $assessableResults = $session->results->filter(fn($result) => is_array($result->standard?->questions) && count($result->standard->questions) > 0);
@endphp
@php
    $firstOpenId = $assessableResults->first(fn($r) => $r->maturity_rating === null)?->id
        ?? $assessableResults->first()?->id;
@endphp
<div x-data="{ 
    showFinalizeModal: false, 
    openId: {{ $firstOpenId ?? 'null' }}, 
    missingScores: @json($missingCodes ?? []), 
    results: [
        @foreach($assessableResults as $r)
        { id: {{ $r->id }}, code: '{{ $r->standard->code }}', is_applicable: {{ $r->is_applicable ? 'true' : 'false' }}, is_completed: {{ $r->status === 'completed' ? 'true' : 'false' }} },
        @endforeach
    ],
    get totalApplicable() {
        return this.results.filter(r => r.is_applicable).length;
    },
    get completedApplicable() {
        return this.results.filter(r => r.is_applicable && r.is_completed).length;
    },
    get allCompleted() {
        return this.totalApplicable > 0 && this.totalApplicable === this.completedApplicable;
    },
    get progressPercentage() {
        return this.totalApplicable > 0 ? Math.round((this.completedApplicable / this.totalApplicable) * 100) : 0;
    },
    updateResultState(id, isApplicable, isCompleted) {
        let r = this.results.find(res => res.id === id);
        if (r) {
            r.is_applicable = isApplicable;
            r.is_completed = isCompleted;
        }
    },
    finalizeMessage() { 
        return this.missingScores.length > 0 ? 'There are ' + this.missingScores.length + ' applicable controls without a score. Please score every applicable control before finalizing.' : 'You are about to finalize this audit session. Evidence and notes are optional.'; 
    }, 
    submitFinalize() { 
        this.$refs.finalizeForm.submit(); 
    } 
}" class="max-w-7xl mx-auto space-y-8">
    {{-- Sticky Header --}}
    <div class="sticky top-[4rem] z-30 bg-white/80 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('sessions.show', $session->id) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:text-blue-600 transition-all border border-slate-100">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ __('Audit Assessment') }}</h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('Session:') }} <span class="text-blue-600 font-bold">{{ $session->name }}</span></p>
            </div>
        </div>
        <div class="text-right flex items-center gap-6">
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export-pdf', $session->id) }}" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-bold hover:bg-slate-50 transition-all" title="{{ __('Export PDF') }}" aria-label="Export session PDF">
                    <i class="fa-solid fa-file-pdf text-red-500 mr-2"></i>{{ __('PDF') }}</a>
                <a href="{{ route('reports.export-excel', $session->id) }}" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-bold hover:bg-slate-50 transition-all" title="{{ __('Export Excel') }}" aria-label="Export session Excel">
                    <i class="fa-solid fa-file-excel text-green-600 mr-2"></i>{{ __('Excel') }}</a>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Overall Maturity') }}</p>
                <div class="flex items-center gap-3">
                    <span class="text-xl font-bold text-slate-900">{{ number_format($session->overall_maturity_score ?? 0, 2) }}</span>
                    <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600" style="width: {{ ($session->overall_maturity_score ?? 0) / 5 * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assessment Forms --}}
    <div class="space-y-6">
        @foreach($assessableResults as $result)
        <div id="result-{{ $result->id }}" 
             x-data="{ 
                 rating: {{ $result->maturity_rating === null ? 'null' : $result->maturity_rating }}, 
                 isApplicable: {{ $result->is_applicable ? 'true' : 'false' }}, 
                 soaJustification: @js($result->soa_justification ?? ''), 
                 labels: {0:'{{ __('Non-existent') }}', 1:'{{ __('Initial') }}', 2:'{{ __('Limited/Repeatable') }}', 3:'{{ __('Defined') }}', 4:'{{ __('Managed') }}', 5:'{{ __('Optimized') }}'} 
             }"
             class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden scroll-mt-32 transition-all duration-200"
             :class="openId === {{ $result->id }} ? 'border-blue-200 shadow-md' : 'hover:border-slate-200'">

            {{-- Accordion Header --}}
            <button type="button"
                x-on:click="
                    openId = (openId === {{ $result->id }}) ? null : {{ $result->id }};
                    if (openId === {{ $result->id }}) {
                        $nextTick(() => {
                            setTimeout(() => {
                                document.getElementById('result-{{ $result->id }}').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            }, 300);
                        });
                    }
                "
                class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left transition-colors"
                :class="openId === {{ $result->id }} ? 'bg-slate-50 border-b border-slate-100' : 'hover:bg-slate-50/50'">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="shrink-0 px-2 py-0.5 {{ in_array($result->standard->type, ['clause','clausa']) ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }} text-[9px] font-black rounded border uppercase tracking-widest">
                        {{ $result->standard->code }}
                    </span>
                    <span class="text-sm font-bold text-slate-800 truncate">{{ __($result->standard->title) }}</span>
                    
                    {{-- Dynamic badges --}}
                    <span x-show="!isApplicable" x-cloak class="shrink-0 px-2 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 rounded text-[9px] font-black uppercase tracking-widest">
                        {{ __('Not Applicable') }}
                    </span>
                    <span x-show="isApplicable && rating !== null" x-cloak class="shrink-0 px-2 py-0.5 bg-slate-900 text-white rounded text-[9px] font-black uppercase tracking-widest">
                        L<span x-text="rating"></span>
                    </span>
                    <span x-show="isApplicable && rating === null" x-cloak class="shrink-0 px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded text-[9px] font-black uppercase tracking-widest">
                        {{ __('Unscored') }}
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down text-slate-400 text-xs shrink-0 transition-transform duration-300"
                   :class="openId === {{ $result->id }} ? 'rotate-180' : ''"></i>
            </button>

            {{-- Accordion Body --}}
            <div class="transition-all duration-500 ease-in-out overflow-hidden"
                 x-ref="accordionBody_{{ $result->id }}"
                 :style="openId === {{ $result->id }} ? 'max-height: ' + ($refs.accordionBody_{{ $result->id }}?.scrollHeight || 1500) + 'px; opacity: 1; visibility: visible;' : 'max-height: 0px; opacity: 0; visibility: hidden; pointer-events: none;'">
            <form action="{{ route('results.update', $result->id) }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                @method('POST')

                <div class="flex flex-col lg:flex-row gap-12">
                    {{-- Control Metadata --}}
                    <div class="lg:w-1/3 space-y-6">
                        <div>
                            <span class="px-2.5 py-1 {{ in_array($result->standard->type, ['clause', 'clausa']) ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }} text-[10px] font-bold rounded-lg uppercase border tracking-widest">
                                {{ $result->standard->type }} {{ $result->standard->code }}
                            </span>
                            <div class="flex items-center gap-2 mt-4 flex-wrap">
                                <h3 class="text-xl font-bold text-slate-900 leading-tight">{{ __($result->standard->title) }}</h3>
                                <span x-show="!isApplicable" x-cloak class="px-2 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 rounded text-[10px] font-bold uppercase tracking-widest ml-2 flex items-center gap-1">
                                    <i class="fa-solid fa-ban text-[8px]"></i>{{ __('Not Applicable') }}
                                </span>
                                <template x-if="isApplicable && rating !== null">
                                    <div class="flex gap-2 items-center">
                                        <span class="px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] font-bold uppercase tracking-widest ml-2">{{ __('Score L') }}<span x-text="rating"></span></span>
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold uppercase tracking-widest" x-text="labels[rating]"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <h4 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Level') }}</h4>
                                <p class="text-[10px] font-bold text-slate-700 uppercase">{{ $result->standard->level }}</p>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <h4 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Category') }}</h4>
                                <p class="text-[10px] font-bold text-slate-700 truncate" title="{{ __($result->standard->parent->title ?? 'Top Level') }}">
                                    {{ __($result->standard->parent->title ?? 'Top Level') }}
                                </p>
                            </div>
                        </div>

                        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Description') }}</h4>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ __($result->standard->description) }}</p>
                        </div>

                        @if($result->standard->implementation_guidance)
                        <div class="p-5 bg-blue-50/30 rounded-2xl border border-blue-100">
                            <h4 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-lightbulb"></i> {{ __('Guidance') }}
                            </h4>
                            <p class="text-[11px] text-slate-600 leading-relaxed font-medium">
                                {{ __($result->standard->implementation_guidance) }}
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- Assessment Interaction --}}
                    <div class="lg:w-2/3 space-y-8 lg:border-l lg:border-slate-100 lg:pl-12">
                        {{-- Statement of Applicability --}}
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 space-y-4">
                            <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ __('Statement of Applicability (SoA)') }}</h4>
                                    <p class="text-[10px] text-slate-500 font-medium leading-snug mt-0.5">{{ __('Is this control applicable to your organization?') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="radio" name="is_applicable" value="1" :checked="isApplicable"
                                            x-on:change="
                                                isApplicable = true;
                                                let form = $el.closest('form');
                                                fetch(form.action, {
                                                    method: 'POST',
                                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                                    body: new FormData(form)
                                                }).then(res => res.json()).then(data => {
                                                    if(data.success) {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Control set as Applicable!') }}', type: 'success' } }));
                                                        updateResultState({{ $result->id }}, true, rating !== null);
                                                    }
                                                });
                                            "
                                            class="peer hidden">
                                        <div class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-black uppercase tracking-widest text-slate-500 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 transition-all hover:bg-slate-100">
                                            {{ __('Yes') }}
                                        </div>
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="radio" name="is_applicable" value="0" :checked="!isApplicable"
                                            x-on:change="
                                                isApplicable = false;
                                                rating = null;
                                                let form = $el.closest('form');
                                                fetch(form.action, {
                                                    method: 'POST',
                                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                                    body: new FormData(form)
                                                }).then(res => res.json()).then(data => {
                                                    if(data.success) {
                                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Control set as Not Applicable!') }}', type: 'info' } }));
                                                        missingScores = missingScores.filter(code => code !== '{{ $result->standard->code }}');
                                                        updateResultState({{ $result->id }}, false, true);
                                                    }
                                                });
                                            "
                                            class="peer hidden">
                                        <div class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-black uppercase tracking-widest text-slate-500 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition-all hover:bg-slate-100">
                                            {{ __('No') }}
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- SoA Justification (Shown only if NOT applicable) --}}
                            <div x-show="!isApplicable" x-transition class="pt-3 border-t border-slate-100">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">{{ __('Exclusion Justification') }} <span class="text-rose-500">*</span></label>
                                <textarea name="soa_justification" rows="2" x-model="soaJustification"
                                    x-on:blur="
                                        let form = $el.closest('form');
                                        fetch(form.action, {
                                            method: 'POST',
                                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                            body: new FormData(form)
                                        }).then(res => res.json()).then(data => {
                                            if(data.success) {
                                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Justification auto-saved!') }}', type: 'success' } }));
                                            }
                                        });
                                    "
                                    placeholder="{{ __('Explain why this control is not applicable (e.g., outsourced processes, no physical premises)...') }}"
                                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 outline-none focus:border-rose-500 focus:ring-4 focus:ring-rose-500/5 transition-all"></textarea>
                            </div>
                        </div>

                        {{-- Applicable Content Section --}}
                        <div x-show="isApplicable" x-transition class="space-y-8">
                            {{-- Verification Questions --}}
                            @if($result->standard->questions)
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-4 ml-1">{{ __('Verification Checklist') }}</label>
                            <div class="space-y-3">
                                @foreach($result->standard->questions as $index => $question)
                                <div class="flex items-start gap-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                    <div class="mt-0.5 w-5 h-5 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-list-check text-[10px]"></i>
                                    </div>
                                    <p class="text-xs text-slate-700 font-medium leading-relaxed">{{ __($question) }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Maturity Scale --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Maturity Level (Likert 0-5)') }}</label>
                                <span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded uppercase tracking-wider">{{ __('Affects Overall Score') }}</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-2">
                                @php
                                    $labels = [
                                        0 => ['title' => 'Non-existent', 'desc' => 'Lack of policies, procedures, controls, etc.'],
                                        1 => ['title' => 'Initial', 'desc' => 'Development has just started and will require significant effort to meet the requirements.'],
                                        2 => ['title' => 'Limited/Repeatable', 'desc' => 'Progress is reasonably good but not yet complete.'],
                                        3 => ['title' => 'Defined', 'desc' => 'Development is more or less complete, although details are still lacking and/or it has not been fully implemented, enforced, and actively supported by management.'],
                                        4 => ['title' => 'Managed', 'desc' => 'Development is complete, processes/controls have been implemented and are newly operational.'],
                                        5 => ['title' => 'Optimized', 'desc' => 'Requirements are fully met, operating completely as expected, actively monitored and improved, and there is substantial evidence that can be provided to auditors.']
                                    ];
                                @endphp
                                @for($i = 0; $i <= 5; $i++)
                                <label class="relative group cursor-pointer" title="{{ __($labels[$i]['desc']) }}">
                                    <input type="radio" name="maturity_rating" value="{{ $i }}" x-model.number="rating" 
                                        x-on:change="
                                            let form = $el.closest('form');
                                            fetch(form.action, {
                                                method: 'POST',
                                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                                body: new FormData(form)
                                            }).then(res => res.json()).then(data => {
                                                if(data.success) {
                                                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Maturity Level') }} ' + $el.value + ' - ' + labels[$el.value] + ' {{ __('auto-saved!') }}', type: 'success' } }));
                                                    // Remove from missingScores if it exists
                                                    missingScores = missingScores.filter(code => code !== '{{ $result->standard->code }}');
                                                    updateResultState({{ $result->id }}, true, true);
                                                }
                                            });
                                        "
                                        class="peer hidden">
                                    <div class="flex flex-col items-center gap-1 p-3 rounded-xl border border-slate-100 text-slate-400 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 peer-checked:scale-110 peer-checked:ring-4 peer-checked:ring-slate-900/30 peer-checked:z-10 hover:border-blue-500 hover:-translate-y-1 hover:shadow-md transition-all duration-300 transform">
                                        <span class="text-lg font-bold">{{ $i }}</span>
                                        <span class="text-[8px] font-bold uppercase tracking-tight text-center leading-none">{{ __($labels[$i]['title']) }}</span>
                                    </div>
                                </label>
                                @endfor
                            </div>
                        </div>

                        {{-- Evidence Notes --}}
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 ml-1">Evidence & Observations</label>
                            <textarea name="notes" rows="4" placeholder="{{ __('Describe implementation status, evidence observed, or gaps identified...') }}" 
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-sm font-medium outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">{{ $result->notes }}</textarea>
                        </div>

                        {{-- AI Compliance Synthesis Card (Accordion Panels) --}}
                        <div id="ai-synthesis-wrapper-{{ $result->id }}">
                            @if(!empty($result->ai_recommendation))
                            @php
                                $aiSections = [];
                                $aiSections[] = [
                                    'key'   => 'strategic',
                                    'icon'  => 'fa-lightbulb',
                                    'color' => 'indigo',
                                    'label' => __('Strategic Recommendation'),
                                    'desc'  => __('AI-generated compliance improvement strategy'),
                                    'body'  => $result->ai_recommendation,
                                    'type'  => 'text',
                                ];
                                if (!empty($result->corrective_action_plan)) {
                                    $aiSections[] = [
                                        'key'   => 'cap',
                                        'icon'  => 'fa-list-check',
                                        'color' => 'emerald',
                                        'label' => __('Corrective Action Plan'),
                                        'desc'  => __('Step-by-step remediation roadmap'),
                                        'body'  => $result->corrective_action_plan,
                                        'type'  => 'pre',
                                    ];
                                }
                                $insightText = is_array($result->control_insight) ? ($result->control_insight['gap'] ?? null) : $result->control_insight;
                                if (!empty($insightText)) {
                                    $aiSections[] = [
                                        'key'   => 'gap',
                                        'icon'  => 'fa-magnifying-glass-chart',
                                        'color' => 'violet',
                                        'label' => __('AI Audit Insight (Gap Analysis)'),
                                        'desc'  => __('Identified gaps against ISO 27001:2022 requirements'),
                                        'body'  => $insightText,
                                        'type'  => 'text',
                                    ];
                                }
                                if (!empty($result->evidence_validation)) {
                                    $aiSections[] = [
                                        'key'   => 'evidence',
                                        'icon'  => 'fa-circle-check',
                                        'color' => 'sky',
                                        'label' => __('Evidence Validation'),
                                        'desc'  => __('AI review of submitted supporting evidence'),
                                        'body'  => $result->evidence_validation,
                                        'type'  => 'text',
                                    ];
                                }
                                if (!empty($result->impact_interpretation)) {
                                    $aiSections[] = [
                                        'key'   => 'impact',
                                        'icon'  => 'fa-triangle-exclamation',
                                        'color' => 'rose',
                                        'label' => __('Impact Interpretation'),
                                        'desc'  => __('Consequences if this condition is not remediated'),
                                        'body'  => $result->impact_interpretation,
                                        'type'  => 'text',
                                    ];
                                }
                                $colorMap = [
                                    'indigo'  => ['bg' => 'bg-indigo-600',  'light' => 'bg-indigo-50',  'border' => 'border-indigo-100', 'text' => 'text-indigo-600',  'ring' => 'ring-indigo-600/10'],
                                    'emerald' => ['bg' => 'bg-emerald-600', 'light' => 'bg-emerald-50', 'border' => 'border-emerald-100','text' => 'text-emerald-600', 'ring' => 'ring-emerald-600/10'],
                                    'violet'  => ['bg' => 'bg-violet-600',  'light' => 'bg-violet-50',  'border' => 'border-violet-100', 'text' => 'text-violet-600',  'ring' => 'ring-violet-600/10'],
                                    'sky'     => ['bg' => 'bg-sky-600',     'light' => 'bg-sky-50',     'border' => 'border-sky-100',    'text' => 'text-sky-600',     'ring' => 'ring-sky-600/10'],
                                    'rose'    => ['bg' => 'bg-rose-600',    'light' => 'bg-rose-50',    'border' => 'border-rose-100',   'text' => 'text-rose-600',    'ring' => 'ring-rose-600/10'],
                                ];
                            @endphp

                            <div class="mt-6 rounded-3xl border border-indigo-100 shadow-sm overflow-hidden"
                                 x-data="{ activeAi_{{ $result->id }}: 'strategic' }">

                                {{-- Card Header --}}
                                <div class="flex items-center justify-between gap-3 px-5 py-4 bg-gradient-to-r from-indigo-600 to-violet-600">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center text-white backdrop-blur-sm">
                                            <i class="fa-solid fa-robot text-xs"></i>
                                        </div>
                                        <div class="leading-none">
                                            <h4 class="text-[11px] font-black text-white tracking-tight uppercase">{{ __('AI Compliance Synthesis') }}</h4>
                                            <p class="text-[8px] text-indigo-200 font-bold uppercase tracking-widest mt-0.5">{{ __('AI Expert Recommendation') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[8px] font-black text-indigo-200 uppercase tracking-widest">{{ count($aiSections) }} {{ __('sections') }}</span>
                                        @if(!empty($result->risk_priority))
                                        <span class="px-2.5 py-1 bg-white/15 text-white border border-white/20 text-[8px] font-black rounded-lg uppercase tracking-wider leading-none backdrop-blur-sm">
                                            {{ $result->risk_priority }}
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Accordion Panels --}}
                                <div class="divide-y divide-indigo-50 bg-gradient-to-br from-indigo-50/60 to-purple-50/60">
                                    @foreach($aiSections as $section)
                                    @php $c = $colorMap[$section['color']]; @endphp
                                    <div x-data="{ get isOpen() { return activeAi_{{ $result->id }} === '{{ $section['key'] }}' } }">

                                        {{-- Panel Header / Toggle Button --}}
                                        <button type="button"
                                            @click="activeAi_{{ $result->id }} = isOpen ? null : '{{ $section['key'] }}'"
                                            class="w-full flex items-center justify-between gap-3 px-5 py-3.5 text-left transition-all duration-200 hover:bg-white/50 group"
                                            :class="isOpen ? 'bg-white/70 shadow-sm' : ''">

                                            <div class="flex items-center gap-3 min-w-0">
                                                {{-- Colored Icon Badge --}}
                                                <div class="shrink-0 w-7 h-7 {{ $c['bg'] }} rounded-lg flex items-center justify-center text-white shadow-sm transition-transform duration-200"
                                                     :class="isOpen ? 'scale-110 ring-4 {{ $c['ring'] }}' : ''">
                                                    <i class="fa-solid {{ $section['icon'] }} text-[10px]"></i>
                                                </div>

                                                <div class="min-w-0">
                                                    <p class="text-[11px] font-black text-slate-800 uppercase tracking-wide leading-none">{{ $section['label'] }}</p>
                                                    <p class="text-[9px] {{ $c['text'] }} font-semibold mt-0.5 leading-none">{{ $section['desc'] }}</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                {{-- Word count badge (shown when collapsed) --}}
                                                <span x-show="!isOpen"
                                                      class="hidden sm:inline-block text-[8px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                                    {{ str_word_count(strip_tags($section['body'])) }} {{ __('words') }}
                                                </span>

                                                {{-- Chevron --}}
                                                <div class="w-6 h-6 rounded-lg {{ $c['light'] }} {{ $c['border'] }} border flex items-center justify-center transition-all duration-300"
                                                     :class="isOpen ? '{{ $c['bg'] }} border-transparent' : ''">
                                                    <i class="fa-solid fa-chevron-down text-[9px] transition-all duration-300"
                                                       :class="isOpen ? 'rotate-180 text-white' : '{{ $c['text'] }}'"></i>
                                                </div>
                                            </div>
                                        </button>

                                        {{-- Panel Body --}}
                                        <div class="transition-all duration-300 ease-in-out overflow-hidden"
                                             x-ref="aiPanelBody_{{ $result->id }}_{{ $section['key'] }}"
                                             :style="isOpen ? 'max-height: ' + ($refs.aiPanelBody_{{ $result->id }}_{{ $section['key'] }}?.scrollHeight || 400) + 'px; opacity: 1; visibility: visible;' : 'max-height: 0px; opacity: 0; visibility: hidden; pointer-events: none;'">
                                            <div class="px-5 pb-5">
                                                <div class="bg-white rounded-2xl border {{ $c['border'] }} shadow-inner p-4 mt-1">
                                                    @if($section['type'] === 'pre')
                                                    <div class="text-xs text-slate-700 font-medium leading-relaxed whitespace-pre-line">{{ $section['body'] }}</div>
                                                    @else
                                                    <p class="text-xs text-slate-700 font-medium leading-relaxed">{{ $section['body'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    @endforeach
                                </div>

                            </div>{{-- /AI accordion wrapper --}}

                            @elseif($result->status === 'completed' && $result->maturity_rating < 3 && empty($result->ai_recommendation))
                            <div class="mt-6 bg-gradient-to-br from-indigo-50/30 to-purple-50/30 rounded-3xl border border-indigo-100/50 p-6 shadow-sm flex flex-col items-center justify-center text-center space-y-4 py-8 relative overflow-hidden" 
                                 x-data="{
                                    poll() {
                                        let interval = setInterval(async () => {
                                            try {
                                                let res = await fetch('/results/{{ $result->id }}/ai-status');
                                                let data = await res.json();
                                                let aiResult = data.data || data.result || data;
                                                if (aiResult.has_ai) {
                                                    clearInterval(interval);
                                                    
                                                    // Dynamic fetch to avoid full page reload flash
                                                    let pageRes = await fetch(window.location.href);
                                                    let html = await pageRes.text();
                                                    let parser = new DOMParser();
                                                    let doc = parser.parseFromString(html, 'text/html');
                                                    let newContent = doc.getElementById('ai-synthesis-wrapper-{{ $result->id }}');
                                                    
                                                    let oldContainer = document.getElementById('ai-synthesis-wrapper-{{ $result->id }}');
                                                    if (oldContainer && newContent) {
                                                        oldContainer.style.transition = 'opacity 0.3s ease';
                                                        oldContainer.style.opacity = '0';
                                                        setTimeout(() => {
                                                            oldContainer.innerHTML = newContent.innerHTML;
                                                            oldContainer.style.opacity = '1';
                                                            
                                                            // Re-initialize Alpine in this dynamically updated subtree if needed
                                                            if (window.Alpine) {
                                                                window.Alpine.initTree(oldContainer);
                                                            }
                                                        }, 300);
                                                    } else {
                                                        window.location.reload();
                                                    }
                                                }
                                            } catch(e) {
                                                console.error(e);
                                            }
                                        }, 2500);
                                    }
                                 }" 
                                 x-init="poll()">
                                 <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/5 to-purple-500/5 animate-pulse"></div>

                                 <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/20 animate-bounce relative z-10">
                                     <i class="fa-solid fa-wand-magic-sparkles text-xl"></i>
                                 </div>

                                 <div class="space-y-1 relative z-10">
                                     <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">{{ __('AI Compliance Synthesis Active') }}</h4>
                                     <p class="text-[10px] text-slate-500 font-medium">{{ __('AI is currently analyzing your evidence and drafting strategic recommendations...') }}</p>
                                 </div>

                                 <div class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-full text-[8px] font-black uppercase tracking-widest text-indigo-700 animate-pulse relative z-10">
                                     <i class="fa-solid fa-spinner animate-spin"></i>{{ __('Processing Real-Time Synthesis') }}</div>
                            </div>
                            @endif
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" name="trigger_ai" value="1" class="px-8 py-3.5 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/20 transition-all flex items-center gap-2 active:scale-95">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                {{ !empty($result->ai_recommendation) ? __('Save & Regenerate with AI') : __('Save & Analyze with AI') }}
                            </button>
                        </div>
                        </div> {{-- /applicable content section --}}
                    </div>
                </div>
            </form>
            </div>{{-- /accordion body --}}
        </div>
        @endforeach
    </div>

    {{-- Finalize Audit Section --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 mt-6">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            {{-- Progress Info --}}
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg transition-all" :class="allCompleted ? 'bg-emerald-600 shadow-emerald-600/20' : 'bg-slate-400 shadow-slate-400/10'">
                    <i class="fa-solid text-xl transition-all" :class="allCompleted ? 'fa-flag-checkered' : 'fa-clipboard-list'"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">{{ __('Audit Completion') }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5" :class="allCompleted ? 'text-emerald-600' : 'text-slate-400'">
                        <span x-text="completedApplicable"></span>/<span x-text="totalApplicable"></span> {{ __('controls scored') }}
                    </p>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="flex-1 max-w-md w-full">
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500" :class="allCompleted ? 'bg-emerald-500' : 'bg-blue-500'" :style="'width: ' + progressPercentage + '%'"></div>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 text-right">
                    <span x-text="progressPercentage"></span>% {{ __('Complete') }}
                </p>
            </div>

            {{-- Finalize Button / Locked Status --}}
            @if($session->status === 'completed')
                <div class="px-6 py-3 bg-emerald-50 text-emerald-700 text-xs font-black uppercase tracking-widest rounded-xl border border-emerald-200 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ __('Audit Completed') }}
                </div>
            @else
                {{-- Shown only when all applicable controls are completed --}}
                <form x-show="allCompleted" x-cloak x-transition x-ref="finalizeForm" id="finalize-form" action="{{ route('sessions.finalize', $session->id) }}" method="POST">
                    @csrf
                    <button type="button" x-on:click="showFinalizeModal = true" aria-label="Finalize Audit" title="{{ __('Finalize Audit') }}" 
                        class="px-8 py-3.5 bg-emerald-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-600/20 transition-all flex items-center gap-2 active:scale-95 shadow-md">
                        <i class="fa-solid fa-circle-check"></i>
                        {{ __('Finalize Audit') }}
                    </button>
                </form>

                {{-- Shown when some controls are still missing --}}
                <div x-show="!allCompleted" class="px-6 py-3 bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-widest rounded-xl border border-slate-200 flex items-center gap-2 cursor-not-allowed select-none">
                    <i class="fa-solid fa-lock"></i>
                    {{ __('Score All Applicable Controls First') }}
                </div>

                {{-- Finalize Confirmation Modal --}}
                <div x-show="showFinalizeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="showFinalizeModal = false"></div>
                    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 z-10" x-on:keydown.escape.window="showFinalizeModal = false">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">{{ __('Confirm Finalize Audit') }}</h3>
                                <p class="text-sm text-slate-500 mt-2">{{ __('Review the status below before finalizing.') }}</p>
                            </div>
                            <button type="button" x-on:click="showFinalizeModal = false" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 transition-all">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="mt-6 p-4 rounded-3xl bg-slate-50 border border-slate-200">
                            <p class="text-sm text-slate-700" x-text="finalizeMessage()"></p>
                            <template x-if="missingScores.length > 0">
                                <div class="mt-4 text-sm text-slate-600">
                                    <p class="font-bold text-slate-900 mb-2">{{ __('Missing score for:') }}</p>
                                    <div class="grid grid-cols-1 gap-2 max-h-40 overflow-y-auto pr-2">
                                        <template x-for="code in missingScores.slice(0, 6)" :key="code">
                                            <div class="px-4 py-3 bg-white rounded-2xl border border-slate-200 text-slate-700">{{ '"' }}<span x-text="code"></span>{{ '"' }}</div>
                                        </template>
                                    </div>
                                    <p class="mt-3 text-xs text-slate-500">{{ __('Select a score for these controls before finalizing.') }}</p>
                                </div>
                            </template>
                        </div>
                        <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-3">
                            <button type="button" x-on:click="showFinalizeModal = false" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold uppercase tracking-wider hover:bg-slate-200 transition-all">
                                {{ __('Cancel') }}
                            </button>
                            <button type="button" x-on:click="submitFinalize()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-emerald-600 text-white font-bold uppercase tracking-wider hover:bg-emerald-700 transition-all">
                                {{ __('Confirm Finalize') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
