@if(!($wizard ?? false) && ($showGuide ?? true))
{{-- Audit Execution Protocol (Collapsible Guide) --}}
<div x-data="{ showGuide: true }" class="mb-6">
    <div class="flex items-center justify-between mb-3 px-2">
        <div class="flex items-center gap-2">
            <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
            <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">{{ __('Audit Execution Protocol') }}</h5>
        </div>
        <button @click="showGuide = !showGuide" class="text-[9px] font-bold text-slate-400 hover:text-blue-600 transition-colors uppercase tracking-widest outline-none">
            <span x-text="showGuide ? 'Hide Protocol' : 'Show Protocol Guide'"></span>
        </button>
    </div>

    <div x-show="showGuide" x-collapse x-cloak>
        <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 shadow-xl relative overflow-hidden mb-4">
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 backdrop-blur-md">
                        <span class="text-blue-400 font-black text-sm">01</span>
                    </div>
                    <div>
                        <h6 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1">Analyze & Scope</h6>
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{{ __('Review the') }} <strong>{{ __('Requirements') }}</strong> {{ __('and') }} <strong>{{ __('Roadmap') }}</strong> {{ __('on the left side of each card.') }}</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 backdrop-blur-md">
                        <span class="text-blue-400 font-black text-sm">02</span>
                    </div>
                    <div>
                        <h6 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1">Verify & Evidence</h6>
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{{ __('Select a required score of') }} <strong>0-5</strong>; {{ __('evidence and observations are optional audit context.') }}</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 backdrop-blur-md">
                        <span class="text-blue-400 font-black text-sm">03</span>
                    </div>
                    <div>
                        <h6 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1">Synthesize & Lock</h6>
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{{ __('Use') }} <strong>{{ __('AI Insights') }}</strong>, {{ __('then click') }} <strong>{{ __('Verify & Finalize') }}</strong> {{ __('to lock the assessment.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mini Legend with Descriptions --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 mb-6">
            @php
                $guides = [
                    0 => ['title' => 'Non-existent', 'desc' => 'No implementation.', 'color' => 'bg-slate-100 text-slate-400 border-slate-200'],
                    1 => ['title' => 'Initial', 'desc' => 'Ad-hoc implementation.', 'color' => 'bg-blue-50 text-blue-400 border-blue-100'],
                    2 => ['title' => 'Limited/Repeatable', 'desc' => 'Inconsistent application.', 'color' => 'bg-blue-100 text-blue-600 border-blue-200'],
                    3 => ['title' => 'Defined', 'desc' => 'Fully standardized.', 'color' => 'bg-indigo-500 text-white border-indigo-400'],
                    4 => ['title' => 'Managed', 'desc' => 'Systematic management.', 'color' => 'bg-indigo-700 text-white border-indigo-600'],
                    5 => ['title' => 'Optimized', 'desc' => 'Adaptive improvement.', 'color' => 'bg-slate-900 text-white border-slate-900'],
                ];
            @endphp
            @foreach($guides as $v => $g)
            <div class="p-3 rounded-xl border {{ $g['color'] }} flex flex-col items-center justify-center text-center group hover:scale-[1.02] transition-all">
                <span class="text-[10px] font-black leading-none mb-1">{{ $v }}</span>
                <span class="text-[7px] font-bold uppercase tracking-widest leading-none mb-1.5">{{ $g['title'] }}</span>
                <p class="text-[6px] font-medium leading-tight opacity-70 px-1">{{ $g['desc'] }}</p>
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
                rating: {{ $result->maturity_rating }},
                status: '{{ $result->compliance_status }}',
                risk: '{{ $result->risk_level }}',
                loading: false,
                aiLoading: false,
                aiRec: @js($result->ai_recommendation ?? ''),
                aiPlan: @js(is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : ($result->corrective_action_plan ?? '')),
                aiInsight: @js(is_array($result->control_insight) ? ($result->control_insight['gap'] ?? '') : ($result->control_insight ?? '')),
                aiPriority: @js($result->risk_priority ?? ''),
                aiValidation: @js($result->evidence_validation ?? ''),
                nextId: {{ $nextId ?? 'null' }},
                
                get ratingInfo() {
                    const info = {
                        0: { title: 'Non-existent', color: 'bg-slate-100 text-slate-400 border-slate-200' },
                        1: { title: 'Initial', color: 'bg-blue-50 text-blue-400 border-blue-100' },
                        2: { title: 'Limited/Repeatable', color: 'bg-blue-100 text-blue-600 border-blue-200' },
                        3: { title: 'Defined', color: 'bg-indigo-500 text-white border-indigo-400 shadow-md' },
                        4: { title: 'Managed', color: 'bg-indigo-700 text-white border-indigo-600 shadow-md' },
                        5: { title: 'Optimized', color: 'bg-slate-900 text-white border-slate-900 shadow-md' }
                    };
                    return info[this.rating] || info[0];
                },

                get riskInfo() {
                    const info = {
                        'critical': 'bg-rose-100 text-rose-700',
                        'high': 'bg-orange-100 text-orange-700',
                        'medium': 'bg-amber-100 text-amber-700',
                        'low': 'bg-slate-100 text-slate-600',
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
                            this.status = result.compliance_status;
                            this.risk = result.risk_level;
                            this.isCompleted = result.status === 'completed';
                            
                            window.dispatchEvent(new CustomEvent('result-updated', { 
                                detail: { id: {{ $result->id }}, status: result.status, rating: result.maturity_rating, wasCompleted } 
                            }));
                            
                            if(typeof updateProgress === 'function') updateProgress();

                            if(finalize) {
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Control Verified!', type: 'success' } }));
                            }
                        }
                    } finally { this.loading = false; }
                },

                async generateAi() {
                    this.aiLoading = true;
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
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Menghubungi n8n... Menunggu analisis AI.', type: 'success' } }));
                            
                            // Mulai polling untuk menunggu webhook membalas
                            let pollCount = 0;
                            let pollInterval = setInterval(async () => {
                                pollCount++;
                                try {
                                    let statusRes = await fetch('{{ route('results.ai-status', $result->id) }}');
                                    let statusData = await statusRes.json();
                                    let aiResult = statusData.data || statusData.result || statusData;
                                    
                                    if (aiResult.has_ai) {
                                        clearInterval(pollInterval);
                                        this.aiRec = aiResult.ai_recommendation;
                                        this.aiPlan = Array.isArray(aiResult.corrective_action_plan) ? aiResult.corrective_action_plan.join('\n') : (aiResult.corrective_action_plan || '');
                                        this.aiInsight = (typeof aiResult.control_insight === 'object' && aiResult.control_insight !== null) ? (aiResult.control_insight.gap || '') : (aiResult.control_insight || '');
                                        this.aiPriority = aiResult.risk_priority || '';
                                        this.aiValidation = aiResult.evidence_validation || '';
                                        this.aiLoading = false;
                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Analisis AI n8n berhasil diterima!', type: 'success' } }));
                                    } else if (pollCount > 24) { // Timeout after ~60 seconds (24 * 2.5s)
                                        clearInterval(pollInterval);
                                        this.aiLoading = false;
                                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Timeout menunggu respon n8n.', type: 'error' } }));
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
                            <template x-if="isAssessed">
                                <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                                    <span class="text-[9px] font-bold text-blue-600 uppercase tracking-widest" x-text="'Lvl ' + rating"></span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[8px] font-bold uppercase tracking-widest" x-text="ratingInfo.title"></span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-widest ml-1" :class="riskInfo" x-text="risk || 'UNKNOWN'"></span>
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
                    
                    {{-- Context Section --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div class="space-y-4">
                            <div>
                                <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Structural Requirements') }}</h5>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-[10px] text-slate-700 leading-relaxed font-medium">
                                    {{ __($result->standard->description ?? 'Section boundary and scope definition.') }}
                                </div>
                            </div>
                            @if($result->standard->implementation_guidance)
                            <div class="relative pl-5 border-l-2 border-blue-600/20">
                                <h5 class="text-[8px] font-bold text-blue-500 uppercase tracking-widest mb-2">{{ __('Implementation Roadmap') }}</h5>
                                <div class="p-3 bg-blue-50/30 rounded-xl border border-blue-100/60 text-[9px] text-blue-900 leading-relaxed font-bold italic">
                                    {{ __($result->standard->implementation_guidance) }}
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Scoring Section --}}
                        <div class="space-y-3">
                            <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Maturity Verification') }}</h5>
                            <div class="space-y-3">
                                @foreach($result->standard->questions as $qIndex => $q)
                                <div class="space-y-2">
                                    <p class="text-slate-800 font-bold text-[11px] leading-relaxed">{{ __($q) }}</p>
                                    
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $options = [
                                                0 => ['title' => 'Non-existent', 'color' => 'bg-slate-100 text-slate-400 border-slate-200'],
                                                1 => ['title' => 'Initial', 'color' => 'bg-blue-50 text-blue-400 border-blue-100'],
                                                2 => ['title' => 'Limited/Repeatable', 'color' => 'bg-blue-100 text-blue-600 border-blue-200'],
                                                3 => ['title' => 'Defined', 'color' => 'bg-indigo-500 text-white border-indigo-400'],
                                                4 => ['title' => 'Managed', 'color' => 'bg-indigo-700 text-white border-indigo-600'],
                                                5 => ['title' => 'Optimized', 'color' => 'bg-slate-900 text-white border-slate-900'],
                                            ];
                                        @endphp
                                        @foreach($options as $val => $opt)
                                        <label class="flex-1 min-w-[65px] cursor-pointer group/btn">
                                            <input type="radio" name="answers[q{{ $qIndex }}]" value="{{ $val }}" 
                                                   {{ isset($result->answers["q$qIndex"]) && $result->answers["q$qIndex"] == $val ? 'checked' : '' }}
                                                   @change="rating = {{ $val }}; submitForm()"
                                                   class="peer hidden">
                                            <div class="py-1.5 px-0.5 text-center rounded-lg border-2 transition-all duration-300 {{ $opt['color'] }} 
                                                        opacity-40 saturate-50 hover:opacity-100 hover:saturate-100 peer-checked:opacity-100 peer-checked:saturate-100 peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-blue-500 peer-checked:border-blue-500 peer-checked:scale-105 peer-checked:shadow-md">
                                                <div class="text-[10px] font-black mb-0">{{ $val }}</div>
                                                <div class="text-[6px] font-bold uppercase tracking-widest opacity-90 leading-none">{{ $opt['title'] }}</div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Evidence & Notes --}}
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <div class="lg:col-span-12 space-y-3">
                            <div>
                                <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('User Findings') }}</h5>
                                <textarea name="notes" rows="2" @input.debounce.2000ms="submitForm()"
                                          placeholder="{{ __('Enter findings...') }}" 
                                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-medium outline-none focus:bg-white focus:border-blue-600 transition-all text-slate-800 leading-relaxed shadow-inner">{{ $result->notes }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Person In Charge (PIC)') }}</h5>
                                    <input type="text" name="treatment_pic" value="{{ $result->treatment_pic }}" @input.debounce.2000ms="submitForm()" placeholder="{{ __('e.g., IT Dept, Budi...') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-medium outline-none focus:bg-white focus:border-blue-600 transition-all text-slate-800 shadow-inner">
                                </div>
                                <div>
                                    <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Target Deadline') }}</h5>
                                    <input type="date" name="treatment_due_date" value="{{ $result->treatment_due_date ? $result->treatment_due_date->format('Y-m-d') : '' }}" @change="submitForm()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-medium outline-none focus:bg-white focus:border-blue-600 transition-all text-slate-800 shadow-inner">
                                </div>
                            </div>
                            <div>
                                <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Evidence Repository') }}</h5>
                                <div class="relative group/up">
                                    <input type="file" name="evidence_file" @change="submitForm(); window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Artifact uploaded!', type: 'success' } }));" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="w-full py-2.5 bg-white border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center gap-3 group-hover/up:border-blue-400 group-hover:bg-blue-50/50 transition-all">
                                        <i class="fa-solid fa-paperclip text-slate-300 group-hover/up:text-blue-600 text-xs"></i>
                                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">
                                            @if($result->evidence_file)
                                                <span class="text-blue-600">{{ __('Change Artifact') }}</span>
                                            @else
                                                Attach Artifact
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @if($result->evidence_file)
                                <div class="mt-2 px-3 py-1.5 bg-blue-50 rounded-lg flex items-center justify-between">
                                    <span class="text-[8px] font-bold text-blue-700 truncate max-w-[120px]">{{ basename($result->evidence_file) }}</span>
                                    <a href="{{ Storage::url($result->evidence_file) }}" target="_blank" class="text-[8px] font-black text-blue-800 uppercase hover:underline">{{ __('View') }}</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Compact AI Status Indicator --}}
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
                                        validation: aiValidation
                                    }}))"
                                    class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-600/20">
                                    <i class="fa-solid fa-eye mr-1"></i>{{ __('View Result') }}</button>
                                <a href="{{ route('workspace.index', ['session_id' => $session->id, 'focus' => $result->id]) }}" class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white rounded-lg text-[8px] font-black uppercase tracking-widest transition-all border border-indigo-100">{{ __('Workspace') }}<i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </template>

                        <template x-if="isCompleted && rating < 4 && !aiRec">
                            <button type="button" @click="generateAi()" :disabled="aiLoading"
                                    class="px-4 py-2 bg-white border border-slate-200 hover:border-indigo-400 hover:text-indigo-600 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all flex items-center gap-2 text-slate-600">
                                <i class="fa-solid fa-wand-magic-sparkles" :class="aiLoading && 'animate-spin text-indigo-500'"></i>
                                <span x-text="aiLoading ? 'Synthesizing...' : 'Trigger AI'"></span>
                            </button>
                        </template>
                    </div>

                    <div class="flex justify-end pt-3 mt-2">
                        <button type="button" @click="submitForm(true)" :disabled="loading"
                                class="px-6 py-2.5 bg-slate-900 text-white rounded-lg text-[8px] font-bold uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center gap-2 shadow-lg">
                            <i class="fa-solid fa-circle-check text-xs"></i>
                            Verify & Finalize
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
