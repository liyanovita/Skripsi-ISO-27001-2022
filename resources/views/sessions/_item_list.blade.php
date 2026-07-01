@if(!($wizard ?? false) && ($showGuide ?? true))
{{-- Sticky Combined Audit Execution Protocol & Maturity Scale --}}
<div x-data="{ showGuide: true }" 
     class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-100 p-6 shadow-sm transition-all duration-300 rounded-t-2xl">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
            <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">{{ __('Audit Execution Protocol & Maturity Scale') }}</h5>
        </div>
        <button @click="showGuide = !showGuide" class="text-[9px] font-bold text-slate-400 hover:text-blue-600 transition-colors uppercase tracking-widest outline-none">
            <span x-text="showGuide ? '{{ __('Hide Protocol & Scale') }}' : '{{ __('Show Protocol & Scale') }}'"></span>
        </button>
    </div>

    <div x-show="showGuide" x-collapse x-cloak class="mt-5 space-y-5">
        {{-- Protocol steps --}}
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 backdrop-blur-md">
                        <span class="text-blue-400 font-black text-sm">01</span>
                    </div>
                    <div>
                        <h6 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1">{{ __('Analyze & Scope') }}</h6>
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{!! __('Review the <strong>Requirements</strong> and <strong>Roadmap</strong> on the left side of each card.') !!}</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 backdrop-blur-md">
                        <span class="text-blue-400 font-black text-sm">02</span>
                    </div>
                    <div>
                        <h6 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1">{{ __('Verify & Evidence') }}</h6>
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{!! __('Select a maturity score from <strong>0-5</strong>. Scores 0-3 are classified as gaps and will require evidence and corrective actions (CAPA).') !!}</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 backdrop-blur-md">
                        <span class="text-blue-400 font-black text-sm">03</span>
                    </div>
                    <div>
                        <h6 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1">{{ __('Synthesize & Lock') }}</h6>
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{!! __('Use <strong>AI Insights</strong> to generate recommendations, then click <strong>Verify & Finalize</strong> to lock the assessment.') !!}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legend cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
            @php
                $guides = [
                    0 => ['title' => 'Non-existent', 'desc' => 'Lack of policies, procedures, controls, etc.', 'color' => 'bg-slate-100 text-slate-400 border-slate-200'],
                    1 => ['title' => 'Initial', 'desc' => 'Development has just started and will require significant effort to meet the requirements.', 'color' => 'bg-blue-50 text-blue-400 border-blue-100'],
                    2 => ['title' => 'Limited/Repeatable', 'desc' => 'Progress is reasonably good but not yet complete.', 'color' => 'bg-blue-100 text-blue-600 border-blue-200'],
                    3 => ['title' => 'Defined', 'desc' => 'Development is more or less complete, although details are still lacking and/or it has not been fully implemented, enforced, and actively supported by management.', 'color' => 'bg-indigo-500 text-white border-indigo-400'],
                    4 => ['title' => 'Managed', 'desc' => 'Development is complete, processes/controls have been implemented and are newly operational.', 'color' => 'bg-indigo-700 text-white border-indigo-600'],
                    5 => ['title' => 'Optimized', 'desc' => 'Requirements are fully met, operating completely as expected, actively monitored and improved, and there is substantial evidence that can be provided to auditors.', 'color' => 'bg-slate-900 text-white border-slate-900'],
                ];
            @endphp
            @foreach($guides as $v => $g)
            <div class="p-3 rounded-xl border {{ $g['color'] }} flex flex-col items-center justify-center text-center group hover:scale-[1.02] transition-all bg-white shadow-sm">
                <span class="text-xl font-black leading-none mb-1">{{ $v }}</span>
                <span class="text-[7px] font-bold uppercase tracking-widest leading-none mb-1.5">{{ __($g['title']) }}</span>
                <p class="text-[8px] font-medium leading-tight opacity-70 px-1">{{ __($g['desc']) }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="space-y-4 p-6">
    @forelse($items->values() as $index => $result)
    @php
        $isClause = in_array($result->standard->type, ['clause', 'clausa']);
        $hasQuestions = is_array($result->standard->questions) && count($result->standard->questions) > 0;
        
        $nextResult = $items->values()->get($index + 1);
        $nextId = $nextResult ? $nextResult->id : null;
    @endphp

    @if(!$hasQuestions)
        {{-- Plain Header for Parent Items --}}
        @if(!($wizard ?? false))
        <div class="py-3 border-b border-slate-100 px-2 mt-4 first:mt-0">
            <div class="flex items-center gap-3">
                <span class="text-xl font-bold text-slate-200 uppercase tracking-tighter">{{ $result->standard->code }}</span>
                <h4 class="text-base font-bold text-slate-900 uppercase tracking-tight">{{ __($result->standard->title) }}</h4>
            </div>
        </div>
        @endif
    @else
        {{-- Interactive Card --}}
        <div id="result-{{ $result->id }}" 
             x-data="{ 
                open: {{ ($wizard ?? false) ? 'true' : ((session('last_updated_id') == $result->id || request('focus') == $result->id) ? 'true' : 'false') }},
                isAssessed: {{ $result->status == 'completed' ? 'true' : 'false' }},
                isCompleted: {{ $result->status == 'completed' ? 'true' : 'false' }},
                rating: {{ $result->maturity_rating === null ? 'null' : $result->maturity_rating }},
                status: '{{ $result->status }}',
                 complianceStatus: '{{ $result->compliance_status }}',
                risk: '{{ $result->risk_level }}',
                loading: false,
                aiLoading: false,
                isApplicable: {{ $isClause ? 'true' : ($result->is_applicable ? 'true' : 'false') }},
                soaJustification: @js($result->soa_justification ?? ''),
                aiRec: @js($result->ai_recommendation ?? ''),
                aiPlan: @js(is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : ($result->corrective_action_plan ?? '')),
                aiInsight: @js(is_array($result->control_insight) ? ($result->control_insight['gap'] ?? '') : ($result->control_insight ?? '')),
                aiPriority: @js($result->risk_priority ?? ''),
                aiValidation: @js($result->evidence_validation ?? ''),
                aiImpact: @js($result->impact_interpretation ?? ''),
                nextId: {{ $nextId ?? 'null' }},
                evidenceFiles: @js(is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file])),
                
                get ratingInfo() {
                    if (!this.isApplicable) {
                        return { title: '{{ __('Not Applicable') }}', color: 'bg-slate-100 text-slate-400 border-slate-200' };
                    }
                    if (this.rating === null) {
                        return { title: '{{ __('Unscored') }}', color: 'bg-amber-50 text-amber-500 border-amber-100' };
                    }
                    const info = {
                        0: { title: '{{ __('Non-existent') }}', color: 'bg-slate-100 text-slate-400 border-slate-200' },
                        1: { title: '{{ __('Initial') }}', color: 'bg-blue-50 text-blue-400 border-blue-100' },
                        2: { title: '{{ __('Limited/Repeatable') }}', color: 'bg-blue-100 text-blue-600 border-blue-200' },
                        3: { title: '{{ __('Defined') }}', color: 'bg-indigo-500 text-white border-indigo-400 shadow-md' },
                        4: { title: '{{ __('Managed') }}', color: 'bg-indigo-700 text-white border-indigo-600 shadow-md' },
                        5: { title: '{{ __('Optimized') }}', color: 'bg-slate-900 text-white border-slate-900 shadow-md' }
                    };
                    return info[this.rating] || info[0];
                },

                get complianceColorInfo() {
                    const info = {
                        'compliant': 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'partially compliant': 'bg-amber-100 text-amber-800 border-amber-200',
                        'non-compliant': 'bg-rose-100 text-rose-800 border-rose-200',
                    };
                    return info[this.complianceStatus?.toLowerCase()] || 'bg-slate-100 text-slate-700 border-slate-200';
                },

                get riskInfo() {
                    const info = {
                        'critical': 'bg-rose-100 text-rose-700',
                        'high': 'bg-orange-100 text-orange-700',
                        'medium': 'bg-amber-100 text-amber-700',
                        'compliant': 'bg-emerald-100 text-emerald-700',
                        'low': 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                    };
                    return info[this.risk?.toLowerCase()] || 'bg-slate-100 text-slate-500';
                },

                async submitForm(finalize = false) {
                    this.loading = true;
                    try {
                        const form = this.$refs.form;
                        const formData = new FormData(form);
                        if(finalize) formData.append('status', 'completed');

                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        
                        if (data.success) {
                            const result = data.result || data.data || {};
                            const wasCompleted = this.isCompleted;
                            this.isAssessed = true;
                            this.rating = result.maturity_rating;
                            this.status = result.compliance_status || '';
                            this.risk = result.risk_level || '';
                            this.isCompleted = result.status === 'completed';
                            this.isApplicable = Boolean(result.is_applicable);
                            this.evidenceFiles = result.evidence_file || [];
                            this.complianceStatus = result.compliance_status || '';
                            
                            window.dispatchEvent(new CustomEvent('result-updated', { 
                                detail: { 
                                    id: {{ $result->id }}, 
                                    status: result.status, 
                                    rating: result.maturity_rating, 
                                    isApplicable: Boolean(result.is_applicable), 
                                    wasCompleted 
                                } 
                            }));
                            
                            if(typeof updateProgress === 'function') updateProgress();

                            if(finalize) {
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Control Verified!') }}', type: 'success' } }));
                            }
                        }
                    } finally { this.loading = false; }
                },

                async generateAi() {
                    this.aiLoading = true;
                    this.aiRec = '';
                    this.aiPlan = '';
                    this.aiInsight = '';
                    this.aiPriority = '';
                    this.aiValidation = '';
                    this.aiImpact = '';
                    try {
                        const res = await fetch('{{ route('results.generate-ai', $result->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });
                        const data = await res.json();

                        // Guard: no data change since last AI generation
                        if (res.status === 409 && data.no_change) {
                            this.aiLoading = false;
                            // Restore existing AI data since we aborted
                            const statusRes = await fetch('{{ route('results.ai-status', $result->id) }}');
                            const statusData = await statusRes.json();
                            const aiResult = statusData.data || statusData.result || statusData;
                            if (aiResult.has_ai) {
                                this.aiRec        = aiResult.ai_recommendation || '';
                                this.aiPlan       = (typeof aiResult.corrective_action_plan === 'object' && aiResult.corrective_action_plan !== null)
                                                     ? (aiResult.corrective_action_plan.action || (Array.isArray(aiResult.corrective_action_plan) ? aiResult.corrective_action_plan.join('\n') : JSON.stringify(aiResult.corrective_action_plan)))
                                                     : (aiResult.corrective_action_plan || '');
                                this.aiInsight    = (typeof aiResult.control_insight === 'object' && aiResult.control_insight !== null) ? (aiResult.control_insight.gap || '') : (aiResult.control_insight || '');
                                this.aiPriority   = aiResult.risk_priority || '';
                                this.aiValidation = aiResult.evidence_validation || '';
                                this.aiImpact     = aiResult.impact_interpretation || '';
                            }
                            Swal.fire({
                                icon: 'info',
                                title: '{{ __('No Changes Detected') }}',
                                html: '<p class=\'text-sm text-slate-600 leading-relaxed\'>{{ addslashes(__('The assessment data for this control has not changed since the last AI analysis was generated.')) }}</p>' +
                                      '<p class=\'text-xs text-slate-400 mt-2\'>{{ addslashes(__('Regeneration is only required after modifying the maturity score, answers, or audit notes.')) }}</p>',
                                confirmButtonText: '{{ __('Understood') }}',
                                confirmButtonColor: '#4f46e5',
                                width: '26rem',
                                customClass: {
                                    title: 'text-base font-bold text-slate-800',
                                    htmlContainer: 'text-left px-2',
                                    confirmButton: 'text-xs font-bold uppercase tracking-widest px-5 py-2.5 rounded-lg'
                                }
                            });
                            return;
                        }

                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Connecting to n8n... Waiting for AI analysis.') }}', type: 'success' } }));
                            
                            // Start polling to wait for webhook response
                            let pollCount = 0;
                            let pollInterval = setInterval(async () => {
                                pollCount++;
                                try {
                                    let statusRes = await fetch('{{ route('results.ai-status', $result->id) }}');
                                    let statusData = await statusRes.json();
                                    let aiResult = statusData.data || statusData.result || statusData;
                                    
                                    if (aiResult.has_ai) {
                                        clearInterval(pollInterval);
                                        this.aiRec        = aiResult.ai_recommendation;
                                        this.aiPlan       = (typeof aiResult.corrective_action_plan === 'object' && aiResult.corrective_action_plan !== null)
                                                             ? (aiResult.corrective_action_plan.action || (Array.isArray(aiResult.corrective_action_plan) ? aiResult.corrective_action_plan.join('\n') : JSON.stringify(aiResult.corrective_action_plan)))
                                                             : (aiResult.corrective_action_plan || '');
                                        this.aiInsight    = (typeof aiResult.control_insight === 'object' && aiResult.control_insight !== null) ? (aiResult.control_insight.gap || '') : (aiResult.control_insight || '');
                                        this.aiPriority   = aiResult.risk_priority || '';
                                        this.aiValidation = aiResult.evidence_validation || '';
                                        this.aiImpact     = aiResult.impact_interpretation || '';
                                        this.aiLoading    = false;
                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('AI analysis received successfully!') }}', type: 'success' } }));
                                    } else if (pollCount > 24) { // Timeout after ~60 seconds (24 * 2.5s)
                                        clearInterval(pollInterval);
                                        this.aiLoading = false;
                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Timeout waiting for AI response.') }}', type: 'error' } }));
                                    }
                                } catch(e) {
                                    console.error('Polling error', e);
                                }
                            }, 2500);
                        } else {
                            this.aiLoading = false;
                        }
                    } catch(e) { 
                        console.error(e); 
                        this.aiLoading = false;
                    }
                }
             }"
             @open-control.window="if($event.detail.id === {{ $result->id }}) { 
                open = true; 
                $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'center' }));
             }"
             class="rounded-2xl border transition-all duration-500 scroll-mt-24 overflow-hidden shadow-sm bg-white mb-2"
             :class="[
                open ? 'ring-2 ring-blue-600/5 scale-[1.002] z-20 shadow-lg border-blue-600/20' : 'border-slate-100 z-10'
             ]">
            
            {{-- Card Header --}}
            <div @click="open = !open" class="p-5 cursor-pointer group flex items-center justify-between bg-slate-50/30">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center transition-all duration-700 border shadow-sm"
                         :class="ratingInfo.color">
                        <span class="text-[7px] font-bold uppercase mb-0.5 tracking-widest opacity-60">{{ $isClause ? 'Clause' : 'Annex' }}</span>
                        <span class="text-lg font-black tracking-tighter">{{ $result->standard->code }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight">{{ __($result->standard->title) }}</h3>
                            <template x-if="isCompleted">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            </template>
                        </div>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $result->standard->level }}</span>
                            <template x-if="!isApplicable">
                                <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 rounded text-[8px] font-bold uppercase tracking-widest">
                                        {{ __('Not Applicable') }}
                                    </span>
                                </div>
                            </template>
                            <template x-if="isApplicable && isAssessed && rating !== null">
                                <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                                    <span class="text-[9px] font-bold text-blue-600 uppercase tracking-widest" x-text="'Lvl ' + rating"></span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[8px] font-bold uppercase tracking-widest" x-text="ratingInfo.title"></span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-widest ml-1" :class="riskInfo" x-text="risk"></span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-widest ml-1 border" :class="complianceColorInfo" x-text="complianceStatus"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div x-show="loading" class="fa-solid fa-circle-notch fa-spin text-blue-500 text-xs opacity-60"></div>
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-700" :class="open ? 'rotate-180' : ''"></i>
                    </div>
                </div>
            </div>

            {{-- Card Body --}}
            <div x-show="open" x-collapse x-cloak>
                <form x-ref="form" action="{{ route('results.update', $result->id) }}" method="POST" class="p-5 space-y-5 border-t border-slate-100">
                    @csrf

                    @if(!$isClause)
                    {{-- Statement of Applicability (SoA) - Annex A only --}}
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/60 space-y-3">
                        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                            <div>
                                <h4 class="text-[10px] font-bold text-slate-800 uppercase tracking-wider">{{ __('Statement of Applicability (SoA)') }}</h4>
                                <p class="text-[9px] text-slate-500 font-medium leading-snug mt-0.5">{{ __('Is this control applicable to your organization?') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="radio" name="is_applicable" value="1" :checked="isApplicable"
                                        x-on:change="
                                            isApplicable = true;
                                            $nextTick(() => submitForm());
                                        "
                                        class="peer hidden">
                                    <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 transition-all hover:bg-slate-100">
                                        {{ __('Yes') }}
                                    </div>
                                </label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="radio" name="is_applicable" value="0" :checked="!isApplicable"
                                        x-on:change="
                                            isApplicable = false;
                                            rating = null;
                                            $nextTick(() => submitForm());
                                        "
                                        class="peer hidden">
                                    <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition-all hover:bg-slate-100">
                                        {{ __('No') }}
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- SoA Justification (Shown only if NOT applicable) --}}
                        <div x-show="!isApplicable" x-transition class="pt-3 border-t border-slate-200">
                            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-2">{{ __('Exclusion Justification') }} <span class="text-rose-500">*</span></label>
                            <textarea name="soa_justification" rows="2" x-model="soaJustification"
                                x-on:blur="submitForm()"
                                placeholder="{{ __('Enter explanation for excluding this control...') }}"
                                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-medium outline-none focus:border-blue-600 transition-all text-slate-800 leading-relaxed shadow-inner">{{ $result->soa_justification }}</textarea>
                        </div>
                    </div>
                    @endif

                    <div x-show="isApplicable" class="space-y-4">

                    {{-- Collapsible: Control Details (Structural Requirements + Implementation Roadmap) --}}
                    @if($result->standard->description || $result->standard->implementation_guidance)
                    <div x-data="{ showDetails: false }" class="rounded-xl border border-slate-200/70 overflow-hidden">
                        <button type="button" @click="showDetails = !showDetails"
                            class="w-full flex items-center justify-between px-4 py-2.5 bg-slate-50 hover:bg-slate-100 transition-colors text-left group">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-slate-400 text-[10px] group-hover:text-blue-500 transition-colors"></i>
                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest group-hover:text-blue-600 transition-colors">{{ __('Control Details') }}</span>
                                <span class="text-[8px] font-medium text-slate-400">&mdash; {{ __('Requirements & Implementation Guide') }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-300" :class="showDetails && 'rotate-180'"></i>
                        </button>
                        <div x-show="showDetails" x-collapse x-cloak class="border-t border-slate-200/60">
                            <div class="p-4 space-y-3 bg-white">
                                @if($result->standard->description)
                                <div>
                                    <h6 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">{{ __('Structural Requirements') }}</h6>
                                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-[10px] text-slate-700 leading-relaxed font-medium">
                                        {{ __($result->standard->description) }}
                                    </div>
                                </div>
                                @endif
                                @if($result->standard->implementation_guidance)
                                <div class="relative pl-4 border-l-2 border-blue-400/30">
                                    <h6 class="text-[8px] font-bold text-blue-500 uppercase tracking-widest mb-1.5">{{ __('Implementation Roadmap') }}</h6>
                                    <div class="p-3 bg-blue-50/40 rounded-xl border border-blue-100/60 text-[9px] text-blue-900 leading-relaxed font-medium italic">
                                        {{ __($result->standard->implementation_guidance) }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Scoring Section — always visible, full width --}}
                    <div class="space-y-3">
                        <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Score This Control') }}</h5>
                        <div class="space-y-4">
                            @foreach($result->standard->questions as $qIndex => $q)
                            <div class="space-y-2">
                                <p class="text-slate-800 font-bold text-[11px] leading-relaxed">{{ __($q) }}</p>
                                <div class="grid grid-cols-3 md:grid-cols-6 gap-1.5">
                                    @php
                                        $options = [
                                            0 => ['title' => 'Non-existent', 'desc' => 'Lack of policies, procedures, controls, etc.', 'color' => 'bg-slate-100 text-slate-400 border-slate-200'],
                                            1 => ['title' => 'Initial', 'desc' => 'Development has just started and will require significant effort to meet the requirements.', 'color' => 'bg-blue-50 text-blue-400 border-blue-100'],
                                            2 => ['title' => 'Limited/Repeatable', 'desc' => 'Progress is reasonably good but not yet complete.', 'color' => 'bg-blue-100 text-blue-600 border-blue-200'],
                                            3 => ['title' => 'Defined', 'desc' => 'Development is more or less complete, although details are still lacking and/or it has not been fully implemented, enforced, and actively supported by management.', 'color' => 'bg-indigo-500 text-white border-indigo-400'],
                                            4 => ['title' => 'Managed', 'desc' => 'Development is complete, processes/controls have been implemented and are newly operational.', 'color' => 'bg-indigo-700 text-white border-indigo-600'],
                                            5 => ['title' => 'Optimized', 'desc' => 'Requirements are fully met, operating completely as expected, actively monitored and improved, and there is substantial evidence that can be provided to auditors.', 'color' => 'bg-slate-900 text-white border-slate-900'],
                                        ];
                                    @endphp
                                    @foreach($options as $val => $opt)
                                    <label class="cursor-pointer group/btn" title="{{ __($opt['desc']) }}">
                                        <input type="radio" name="answers[q{{ $qIndex }}]" value="{{ $val }}"
                                               {{ isset($result->answers["q$qIndex"]) && $result->answers["q$qIndex"] == $val ? 'checked' : '' }}
                                               @change="rating = {{ $val }}; submitForm()"
                                               class="peer hidden">
                                        <div class="py-1.5 px-0.5 text-center rounded-lg border-2 transition-all duration-300 {{ $opt['color'] }}
                                                    opacity-40 saturate-50 hover:opacity-100 hover:saturate-100 peer-checked:opacity-100 peer-checked:saturate-100 peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-blue-500 peer-checked:border-blue-500 peer-checked:scale-105 peer-checked:shadow-md">
                                            <div class="text-sm font-black mb-0.5">{{ $val }}</div>
                                            <div class="text-[6px] font-bold uppercase tracking-widest opacity-90 leading-none">{{ __($opt['title']) }}</div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Evidence & Notes --}}
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-1 lg:grid-cols-12 gap-6">
                        {{-- User Findings (Left Side) --}}
                        <div class="lg:col-span-6">
                            <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('User Findings') }}</h5>
                            <textarea name="notes" rows="3" @input.debounce.2000ms="submitForm()"
                                      placeholder="{{ __('Enter findings...') }}" 
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-medium outline-none focus:bg-white focus:border-blue-600 transition-all text-slate-800 leading-relaxed shadow-inner h-[86px] resize-none">{{ $result->notes }}</textarea>
                        </div>

                        {{-- Evidence Repository (Right Side) --}}
                        <div class="lg:col-span-6 flex flex-col justify-between">
                            <div>
                                <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Evidence Repository') }}</h5>
                                <div class="relative group/up">
                                    <input type="file" name="evidence_file" @change="submitForm().then(() => { $el.value = ''; window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Artifact uploaded!') }}', type: 'success' } })); });" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="w-full py-2 bg-white border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center gap-1 group-hover/up:border-blue-400 group-hover:bg-blue-50/50 transition-all">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-paperclip text-slate-300 group-hover/up:text-blue-600 text-xs"></i>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">
                                                <template x-if="evidenceFiles.length > 0">
                                                    <span class="text-blue-600">{{ __('Upload More Artifact') }}</span>
                                                </template>
                                                <template x-if="evidenceFiles.length === 0">
                                                    <span>{{ __('Attach Artifact') }}</span>
                                                </template>
                                            </span>
                                        </div>
                                        <span class="text-[7px] font-semibold text-slate-400/80 tracking-wider">
                                            PDF, JPG, JPEG, PNG, DOCX, XLSX (Max 10MB)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <template x-if="evidenceFiles.length > 0">
                                <div class="mt-2 space-y-1">
                                    <template x-for="file in evidenceFiles" :key="file">
                                        <div class="px-3 py-1.5 bg-blue-50/70 border border-blue-100/50 rounded-lg flex items-center justify-between gap-2 hover:bg-blue-50 transition-colors"
                                             x-data="{ deleting: false }">
                                            <a :href="'/results/{{ $result->id }}/evidence?file=' + encodeURIComponent(file)" target="_blank" 
                                               class="text-[8px] font-bold text-blue-700 hover:text-blue-800 hover:underline truncate flex-1 block"
                                               :title="file.split('/').pop()" 
                                               x-text="file.split('/').pop()"></a>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button" 
                                                        @click="
                                                             Swal.fire({
                                                                title: '{{ addslashes(__('Delete Attachment File?')) }}',
                                                                text: '{{ addslashes(__('Are you sure you want to delete file "')) }}' + file.split('/').pop() + '{{ addslashes(__('"? This action cannot be undone.')) }}',
                                                                icon: 'warning',
                                                                showCancelButton: true,
                                                                confirmButtonColor: '#ef4444',
                                                                cancelButtonColor: '#64748b',
                                                                confirmButtonText: '{{ addslashes(__('Yes, Delete!')) }}',
                                                                cancelButtonText: '{{ addslashes(__('Cancel')) }}',
                                                                width: '22rem',
                                                                customClass: {
                                                                    title: 'text-base font-bold text-slate-800',
                                                                    htmlContainer: 'text-xs text-slate-500',
                                                                    confirmButton: 'text-xs px-3 py-2 rounded-lg font-semibold',
                                                                    cancelButton: 'text-xs px-3 py-2 rounded-lg font-semibold'
                                                                }
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    deleting = true;
                                                                    fetch('{{ route('results.evidence.delete', $result->id) }}', {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                            'Accept': 'application/json',
                                                                            'Content-Type': 'application/json'
                                                                        },
                                                                        body: JSON.stringify({ _method: 'DELETE', file_path: file })
                                                                    })
                                                                    .then(res => res.json())
                                                                    .then(data => {
                                                                        if(data.success) {
                                                                            evidenceFiles = evidenceFiles.filter(f => f !== file);
                                                                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('File deleted!') }}', type: 'success' } }));
                                                                        }
                                                                    })
                                                                    .finally(() => deleting = false);
                                                                }
                                                            });
                                                        "
                                                        :disabled="deleting"
                                                        class="text-[8px] font-black text-rose-600 uppercase hover:underline">
                                                    <i class="fa-solid fa-trash-can text-[9px]" x-show="!deleting"></i>
                                                    <i class="fa-solid fa-spinner fa-spin text-[9px]" x-show="deleting"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="evidenceFiles.length === 0">
                                <div class="mt-2 h-[26px]"></div>
                            </template>
                        </div>
                    </div>

                    {{-- Compact AI Status Indicator --}}
                    <template x-if="rating < 4">
                        <div class="pt-4 border-t border-slate-100 mt-2 flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all" 
                                     :class="aiRec ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-200 text-slate-400'">
                                    <i class="fa-solid fa-robot text-xs"></i>
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none">{{ __('AI Synthesis Status') }}</h4>
                                    <template x-if="aiRec">
                                        <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-1"><i class="fa-solid fa-check-circle mr-1"></i>{{ __('Analysis Ready') }}</p>
                                    </template>
                                    <template x-if="!aiRec">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('Pending Generation') }}</p>
                                    </template>
                                </div>
                            </div>

                            <template x-if="aiRec">
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                        data-code="{{ $result->standard->code }}"
                                        data-title="{{ __($result->standard->title) }}"
                                        @click="window.dispatchEvent(new CustomEvent('open-ai-details', { detail: {
                                            code: $el.dataset.code,
                                            title: $el.dataset.title,
                                            rec: aiRec,
                                            plan: aiPlan,
                                            insight: aiInsight,
                                            priority: aiPriority,
                                            validation: aiValidation,
                                            impact: aiImpact
                                        }}))"
                                        class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-600/20">
                                        <i class="fa-solid fa-eye mr-1"></i>{{ __('View Result') }}</button>
                                    <a href="{{ route('workspace.index', ['session_id' => $session->id, 'focus' => $result->id]) }}" class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white rounded-lg text-[8px] font-black uppercase tracking-widest transition-all border border-indigo-100">{{ __('Workspace') }}<i class="fa-solid fa-arrow-right ml-1"></i>
                                    </a>
                                    <template x-if="rating < 4">
                                        <button type="button" @click="generateAi()" :disabled="aiLoading"
                                                class="px-4 py-2 bg-white border border-slate-200 hover:border-indigo-400 hover:text-indigo-600 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all flex items-center gap-2 text-slate-600">
                                            <i class="fa-solid fa-arrows-rotate" :class="aiLoading && 'animate-spin text-indigo-500'"></i>
                                            <span x-text="aiLoading ? '{{ __('Regenerating...') }}' : '{{ __('Regenerate') }}'"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>

                            <template x-if="isCompleted && rating < 4 && !aiRec">
                                <button type="button" @click="generateAi()" :disabled="aiLoading"
                                        class="px-4 py-2 bg-white border border-slate-200 hover:border-indigo-400 hover:text-indigo-600 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all flex items-center gap-2 text-slate-600">
                                    <i class="fa-solid fa-wand-magic-sparkles" :class="aiLoading && 'animate-spin text-indigo-500'"></i>
                                    <span x-text="aiLoading ? '{{ __('Synthesizing...') }}' : '{{ __('Trigger AI') }}'"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                    </div>

                    <div class="flex justify-end pt-3 mt-2">
                        <button type="button" @click="submitForm(true)" :disabled="loading"
                                class="px-6 py-2.5 bg-slate-900 text-white rounded-lg text-[8px] font-bold uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center gap-2 shadow-lg">
                            <i class="fa-solid fa-circle-check text-xs"></i>
                            {{ __('Verify & Finalize') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @empty
    <div class="p-16 text-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
            <i class="fa-solid fa-box-open text-2xl text-slate-200"></i>
        </div>
        <h3 class="text-slate-900 font-bold text-base">{{ __('No Assets Detected') }}</h3>
    </div>
    @endforelse
</div>

