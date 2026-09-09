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

    <div x-show="showGuide" x-collapse class="mt-5 space-y-5">
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
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{!! __('Select a maturity score from <strong>0-5</strong>. Scores 0-3 are classified as gaps and will require evidence and improvement tracking.') !!}</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/10 backdrop-blur-md">
                        <span class="text-blue-400 font-black text-sm">03</span>
                    </div>
                    <div>
                        <h6 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1">{{ __('Synthesize & Complete') }}</h6>
                        <p class="text-[9px] text-slate-400 leading-relaxed font-medium">{!! __('Use <strong>AI Insights</strong> to generate recommendations. Scores and findings are saved automatically as you complete questions.') !!}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legend cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
            @php
                $guides = [
                    0 => ['title' => 'Non-existent', 'desc' => 'Control is not implemented', 'color' => 'bg-slate-100 text-slate-400 border-slate-200'],
                    1 => ['title' => 'Initial', 'desc' => 'Control is planned but not consistently implemented', 'color' => 'bg-blue-50 text-blue-400 border-blue-100'],
                    2 => ['title' => 'Limited/Repeatable', 'desc' => 'Control is partially implemented', 'color' => 'bg-blue-100 text-blue-600 border-blue-200'],
                    3 => ['title' => 'Defined', 'desc' => 'Control is implemented according to defined procedures', 'color' => 'bg-blue-500 text-white border-blue-400'],
                    4 => ['title' => 'Managed', 'desc' => 'Control is consistently implemented and its effectiveness is monitored', 'color' => 'bg-blue-700 text-white border-blue-600'],
                    5 => ['title' => 'Optimized', 'desc' => 'Control is optimally implemented and supported by continuous improvement', 'color' => 'bg-slate-900 text-white border-slate-900'],
                ];
            @endphp
            @foreach($guides as $v => $g)
            <div class="p-3 rounded-xl border {{ $g['color'] }} flex flex-col items-center justify-center text-center group hover:scale-[1.02] transition-all shadow-2xs">
                <span class="text-xl font-black leading-none mb-1">{{ $v }}</span>
                <span class="text-[7px] font-bold uppercase tracking-widest leading-none mb-1.5">{{ __($g['title']) }}</span>
                <p class="text-[8px] font-medium leading-tight opacity-85 px-1">{{ __($g['desc']) }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="space-y-4 p-6">
    @php
        $sessionLocked = $session->status === 'completed' || $session->isLockedForUser(auth()->user());
        $extractionService = app(\App\Services\Assessment\ResultService::class);
        $locale = app()->getLocale();
    @endphp
    @forelse($items->values() as $index => $result)
    @php
        $isClause = in_array($result->standard->type, ['clause', 'clausa']);
        $hasQuestions = is_array($result->standard->questions) && count($result->standard->questions) > 0;

        $nextResult = $items->values()->get($index + 1);
        $nextId = $nextResult ? $nextResult->id : null;

        $aiLocalized = $result->getAiContentForLocale($locale);

        $resolvedExtractions = [];
        if (is_array($result->evidence_extractions)) {
            foreach (array_keys($result->evidence_extractions) as $filePath) {
                $resolvedExtractions[$filePath] = $extractionService->getExtractionForLocale($result, $filePath, $locale);
            }
        }

        $initialCardData = [
            'open'                => ($wizard ?? false) || session('last_updated_id') == $result->id || request('focus') == $result->id,
            'isAssessed'          => $result->status == 'completed',
            'isCompleted'         => $result->status == 'completed',
            'rating'              => $result->maturity_rating,
            'status'              => $result->status,
            'complianceStatus'    => $result->compliance_status,
            'risk'                => $result->risk_level,
            'code'                => $result->standard->code,
            'title'               => __($result->standard->title),
            'isApplicable'        => $isClause ? true : (bool) $result->is_applicable,
            'soaJustification'    => $result->soa_justification ?? '',
            'aiRec'               => $aiLocalized['ai_recommendation'] ?? '',
            'aiPlan'              => is_array($aiLocalized['corrective_action_plan'])
                                        ? ($aiLocalized['corrective_action_plan']['action'] ?? implode("\n", $aiLocalized['corrective_action_plan']))
                                        : ($aiLocalized['corrective_action_plan'] ?? ''),
            'aiInsight'           => is_array($aiLocalized['control_insight'])
                                        ? ($aiLocalized['control_insight']['gap'] ?? implode("\n", $aiLocalized['control_insight']))
                                        : ($aiLocalized['control_insight'] ?? ''),
            'aiPriority'          => $result->risk_priority ?? '',
            'aiImpact'            => $aiLocalized['impact_interpretation'] ?? '',
            'nextId'              => $nextId,
            'evidenceFiles'       => is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]),
            'evidenceExtractions' => $resolvedExtractions,
        ];
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
             x-data="resultCard({{ $result->id }}, @js($initialCardData), {{ $sessionLocked ? 'true' : 'false' }})"
             @open-control.window="if($event.detail.id === {{ $result->id }}) {
                open = true;
                loadBody();
                $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'center' }));
             }"
             class="rounded-2xl border transition-all duration-500 scroll-mt-24 overflow-hidden shadow-sm bg-white mb-2"
             :class="[
                open ? 'ring-2 ring-blue-600/5 scale-[1.002] z-20 shadow-lg border-blue-600/20' : 'border-slate-100 z-10'
             ]">
            
            {{-- Card Header --}}
            <div @click="open = !open; if (open) loadBody();" class="p-5 cursor-pointer group flex items-center justify-between bg-slate-50/30">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center transition-all duration-700 border shadow-sm"
                         :class="ratingInfo.color">
                        <span class="text-[7px] font-bold uppercase mb-0.5 tracking-widest opacity-60">{{ $isClause ? __('Clause') : __('Annex') }}</span>
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
                                    <span class="text-[9px] font-bold text-blue-600 uppercase tracking-widest" x-text="'{{ __('Lvl') }} ' + rating"></span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[8px] font-bold uppercase tracking-widest" x-text="ratingInfo.title"></span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-widest ml-1" :class="riskInfo" x-text="risk"></span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-widest ml-1 border cursor-help" :class="complianceColorInfo" :title="complianceTooltip" x-text="complianceStatus"></span>
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

            {{-- Card Body — content is fetched lazily on first expand (see loadBody() in
                 the resultCard component below) instead of being rendered inline for
                 every one of the ~137 controls up front. --}}
            <div x-show="open" x-collapse>
                <div x-show="bodyLoading" class="p-8 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                    <span class="text-xs font-semibold">{{ __('Loading...') }}</span>
                </div>
                <div x-ref="bodyContainer"></div>
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

<script>
{{-- Registered once per page (not per control card) — previously this entire block was
     duplicated inline inside every card's x-data, which for a ~137-control session meant
     the same ~400 lines of JS were repeated 137 times in the HTML response. --}}
(function() {
    const EXTRACTION_STATUS_URL_TMPL = @js(route('results.extraction-status', ':id'));
    const EXTRACT_EVIDENCE_URL_TMPL = @js(route('results.extract-evidence', ':id'));
    const AI_STATUS_URL_TMPL = @js(route('results.ai-status', ':id'));
    const CARD_BODY_URL_TMPL = @js(route('results.card-body', ':id'));

    const registerResultCard = () => {
        if (window.Alpine.data('resultCard')) return;

        window.Alpine.data('resultCard', (resultId, initial, sessionLocked) => ({
            open: initial.open,
            isAssessed: initial.isAssessed,
            isCompleted: initial.isCompleted,
            rating: initial.rating,
            status: initial.status,
            complianceStatus: initial.complianceStatus,
            risk: initial.risk,
            loading: false,
            aiLoading: false,
            code: initial.code,
            title: initial.title,
            isApplicable: initial.isApplicable,
            soaJustification: initial.soaJustification,
            aiRec: initial.aiRec,
            aiPlan: initial.aiPlan,
            aiInsight: initial.aiInsight,
            aiPriority: initial.aiPriority,
            aiValidation: '',
            aiImpact: initial.aiImpact,
            nextId: initial.nextId,
            evidenceFiles: initial.evidenceFiles,
            evidenceExtractions: initial.evidenceExtractions,
            extractingFiles: [],
            bodyLoaded: false,
            bodyLoading: false,

            init() {
                if (this.open) this.loadBody();
            },

            async loadBody() {
                if (this.bodyLoaded || this.bodyLoading) return;
                this.bodyLoading = true;
                try {
                    const res = await fetch(CARD_BODY_URL_TMPL.replace(':id', resultId));
                    const html = await res.text();
                    this.$refs.bodyContainer.innerHTML = html;
                    window.Alpine.initTree(this.$refs.bodyContainer);
                    this.bodyLoaded = true;
                    this.autoExtractMissing();
                } catch (e) {
                    console.error('Failed to load card body', e);
                    this.$refs.bodyContainer.innerHTML = '<p class=\'text-xs text-rose-600 font-semibold p-5\'>{{ addslashes(__('Failed to load control detail.')) }}</p>';
                } finally {
                    this.bodyLoading = false;
                }
            },

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
                    3: { title: '{{ __('Defined') }}', color: 'bg-blue-500 text-white border-blue-400 shadow-md' },
                    4: { title: '{{ __('Managed') }}', color: 'bg-blue-700 text-white border-blue-600 shadow-md' },
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

            get complianceTooltip() {
                const desc = {
                    'compliant': '{{ __('Control is implemented according to ISO/IEC 27001:2022 standard requirements and demonstrates adequate implementation.') }}',
                    'partially compliant': '{{ __('Control is partially implemented, but there are still gaps or aspects that need improvement to meet standard requirements.') }}',
                    'non-compliant': '{{ __('Control is not implemented or its implementation does not meet the requirements specified in the ISO/IEC 27001:2022 standard.') }}',
                };
                return desc[this.complianceStatus?.toLowerCase()] || '';
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
                if (sessionLocked) {
                    return { success: false };
                }
                this.loading = true;
                try {
                    const form = this.$refs.form;
                    const formData = new FormData(form);
                    if (finalize) formData.append('status', 'completed');

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
                                id: resultId,
                                status: result.status,
                                rating: result.maturity_rating,
                                isApplicable: Boolean(result.is_applicable),
                                wasCompleted
                            }
                        }));

                        if (typeof updateProgress === 'function') updateProgress();

                        if (finalize) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Control Verified!') }}', type: 'success' } }));
                        }

                        return { success: true, data };
                    }

                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || '{{ __('Failed to save changes.') }}', type: 'error' } }));
                    return { success: false, data };
                } catch (e) {
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Failed to save changes. Please check your connection and try again.') }}', type: 'error' } }));
                    return { success: false, error: e };
                } finally { this.loading = false; }
            },

            // Called whenever the card body loads or a new file is uploaded — extracts
            // every evidence file that doesn't have a result yet, all in parallel
            // (the backend lock is now scoped per file, not per result, so this is safe).
            autoExtractMissing() {
                if (sessionLocked) return;
                for (const file of this.evidenceFiles) {
                    if (!this.evidenceExtractions[file] && !this.extractingFiles.includes(file)) {
                        this.extractEvidence(file);
                    }
                }
            },

            async extractEvidence(filePath) {
                if (sessionLocked) return;
                if (this.extractingFiles.includes(filePath)) return;
                this.extractingFiles.push(filePath);
                const self = this;

                const stopTracking = () => {
                    self.extractingFiles = self.extractingFiles.filter(f => f !== filePath);
                };

                const startPolling = () => {
                    let pollCount = 0;
                    let pollInterval = setInterval(async () => {
                        pollCount++;
                        try {
                            let statusRes = await fetch(EXTRACTION_STATUS_URL_TMPL.replace(':id', resultId));
                            let statusData = await statusRes.json();
                            let payload = statusData.data || statusData;
                            let extractions = payload.evidence_extractions || {};

                            if (extractions[filePath]) {
                                clearInterval(pollInterval);
                                self.evidenceExtractions[filePath] = extractions[filePath];
                                stopTracking();
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Evidence extraction completed!') }}', type: 'success' } }));
                            } else if (pollCount > 200) { // Timeout after ~6-7 minutes
                                clearInterval(pollInterval);
                                stopTracking();
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Timeout waiting for evidence extraction.') }}', type: 'error' } }));
                            }
                        } catch (e) {
                            console.error('Extraction polling error', e);
                            clearInterval(pollInterval);
                            stopTracking();
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Error retrieving extraction status.') }}', type: 'error' } }));
                        }
                    }, 2000);
                };

                try {
                    const res = await fetch(EXTRACT_EVIDENCE_URL_TMPL.replace(':id', resultId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ file_path: filePath })
                    });
                    const data = await res.json();

                    if (res.status === 429 || (data && data.is_processing)) {
                        startPolling();
                        return;
                    }

                    if (data.success) {
                        startPolling();
                    } else {
                        stopTracking();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || '{{ __('Failed to trigger evidence extraction.') }}', type: 'error' } }));
                    }
                } catch (e) {
                    console.error(e);
                    stopTracking();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Failed to trigger evidence extraction.') }}', type: 'error' } }));
                }
            },

            escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str ?? '';
                return div.innerHTML;
            },

            formatSummaryPoints(rawText) {
                if (!rawText) return '';
                const text = String(rawText).trim();
                let lines = [];

                // Check if text already has newline breaks or bullet characters
                if (text.includes('\n') || text.includes('•') || /(?:^|\n)\s*[-*•]\s+/m.test(text)) {
                    lines = text.split(/\r?\n/)
                        .map(l => l.replace(/^[\s•]*([-*]\s+|\d+[\.\)]\s+|•\s*)/, '').trim())
                        .filter(l => l.length > 0);
                } else {
                    // Continuous paragraph: split by sentence ending (. ! ?) followed by whitespace and uppercase/number
                    // while preserving decimal/version numbers like 2.0 or v1.1
                    const sentenceRegex = /((?:[^\s.!?]|\.(?!\s+[A-Z0-9])|!(?!\s+[A-Z0-9])|\?(?!\s+[A-Z0-9])|\s)+[.!?]+)(?:\s+(?=[A-Z0-9])|$)|([^.!?]+$)/g;
                    let match;
                    while ((match = sentenceRegex.exec(text)) !== null) {
                        const part = (match[1] || match[2] || '').trim();
                        if (part.length > 0) {
                            lines.push(part);
                        }
                    }
                    if (lines.length === 0) {
                        lines = [text];
                    }
                }

                return lines.map(line => {
                    let escaped = this.escapeHtml(line);
                    escaped = escaped.replace(/\*\*(.+?)\*\*/g, '<strong class="font-bold text-slate-800">$1</strong>');
                    return `
                        <li class="flex items-start gap-2.5 text-left group/item py-1 px-2.5 rounded-lg hover:bg-slate-100/70 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 ring-4 ring-blue-100 mt-1.5 shrink-0 group-hover/item:bg-blue-600 transition-all"></span>
                            <div class="text-[13px] text-slate-700 font-normal leading-relaxed flex-1">${escaped}</div>
                        </li>
                    `;
                }).join('');
            },

            getFileIcon(fileName) {
                const ext = (fileName.split('.').pop() || '').toLowerCase();
                const map = {
                    pdf: 'fa-file-pdf',
                    doc: 'fa-file-word', docx: 'fa-file-word',
                    xls: 'fa-file-excel', xlsx: 'fa-file-excel', xlsm: 'fa-file-excel', csv: 'fa-file-csv', ods: 'fa-file-excel',
                    jpg: 'fa-file-image', jpeg: 'fa-file-image', png: 'fa-file-image', webp: 'fa-file-image', bmp: 'fa-file-image', tif: 'fa-file-image', tiff: 'fa-file-image',
                };
                return map[ext] || 'fa-file-lines';
            },

            viewSummaryEvidence(filePath) {
                const extraction = this.evidenceExtractions[filePath];
                if (!extraction) return;

                const fileName = this.escapeHtml(filePath.split('/').pop());
                const fileIcon = this.getFileIcon(fileName);

                if (extraction.status === 'ok') {
                    const rawSummary = extraction.summary || '{{ addslashes(__('No summary available.')) }}';
                    const summaryHtml = this.formatSummaryPoints(rawSummary);
                    let relevanceText = extraction.relevance ? this.escapeHtml(extraction.relevance) : '';
                    if (relevanceText) {
                        relevanceText = relevanceText.replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold text-indigo-950">$1</strong>');
                    }
                    const extractedAt = extraction.extracted_at ? this.escapeHtml(extraction.extracted_at) : '';

                    Swal.fire({
                        title: '{{ addslashes(__('Evidence Summary')) }}',
                        html: `
                            <div class='flex items-center justify-between gap-3 mb-3 pb-3 border-b border-slate-100 text-left'>
                                <div class='flex items-center gap-3 min-w-0'>
                                    <div class='w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0 shadow-2xs'>
                                        <i class='fa-solid ${fileIcon} text-blue-500 text-sm'></i>
                                    </div>
                                    <div class='min-w-0'>
                                        <p class='text-[9px] font-black text-blue-600 uppercase tracking-widest leading-none mb-1'>{{ addslashes(__('AI Document Analysis')) }}</p>
                                        <p class='text-xs font-bold text-slate-800 truncate' title='${fileName}'>${fileName}</p>
                                    </div>
                                </div>
                                ${extractedAt ? `
                                <div class='shrink-0 text-right'>
                                    <span class='text-[10px] text-slate-400 font-medium flex items-center gap-1.5 bg-slate-50 border border-slate-100 px-2.5 py-1 rounded-lg'>
                                        <i class='fa-regular fa-clock text-slate-400 text-[10px]'></i>${extractedAt}
                                    </span>
                                </div>` : ''}
                            </div>
                            <div class='flex items-center justify-between mb-1.5 px-0.5 text-left'>
                                <span class='text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5'>
                                    <i class='fa-solid fa-list-check text-blue-500'></i>{{ addslashes(__('Key Extraction Points')) }}
                                </span>
                                <span class='text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full'>
                                    {{ addslashes(__('Structured Summary')) }}
                                </span>
                            </div>
                            <div class='bg-slate-50/75 border border-slate-200/80 rounded-xl p-2 sm:p-2.5 text-left max-h-[58vh] overflow-y-auto custom-scrollbar shadow-inner'>
                                <ul class='space-y-0.5'>
                                    ${summaryHtml}
                                </ul>
                            </div>
                            ${relevanceText ? `
                            <div class='mt-2.5 bg-indigo-50/60 border border-indigo-100/90 rounded-xl px-3.5 py-2.5 text-left'>
                                <p class='text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none mb-1 flex items-center gap-1.5'><i class='fa-solid fa-link text-indigo-400'></i>{{ addslashes(__('Relevance to Control')) }}</p>
                                <p class='text-[12.5px] text-indigo-950/85 font-medium leading-relaxed whitespace-pre-line'>${relevanceText}</p>
                            </div>
                            ` : ''}
                        `,
                        confirmButtonText: '{{ addslashes(__('Close')) }}',
                        confirmButtonColor: '#2563eb',
                        width: '60rem',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl border border-slate-100 max-w-[95vw]',
                            title: 'text-base font-black text-slate-900 pt-4 pb-0',
                            htmlContainer: 'px-4 sm:px-6 pb-2 text-left',
                            confirmButton: 'text-[11px] font-bold uppercase tracking-wider px-6 py-2 rounded-xl shadow-xs'
                        }
                    });
                } else {
                    const errorText = this.escapeHtml(extraction.error_reason || '{{ addslashes(__('Unknown error.')) }}');

                    Swal.fire({
                        title: '{{ addslashes(__('Extraction Failed')) }}',
                        html: `
                            <div class='flex items-start gap-3 mb-4 pb-4 border-b border-slate-100 text-left'>
                                <div class='w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0'>
                                    <i class='fa-solid ${fileIcon} text-rose-400 text-base'></i>
                                </div>
                                <div class='min-w-0 pt-0.5'>
                                    <p class='text-[9px] font-black text-rose-500 uppercase tracking-widest leading-none mb-1.5'>{{ addslashes(__('Document Could Not Be Read')) }}</p>
                                    <p class='text-xs font-bold text-slate-800 break-words leading-snug'>${fileName}</p>
                                </div>
                            </div>
                            <div class='bg-rose-50/60 border border-rose-100 rounded-xl p-4 text-left'>
                                <p class='text-[13px] text-rose-700 font-medium leading-relaxed'>${errorText}</p>
                            </div>
                        `,
                        confirmButtonText: '{{ addslashes(__('Close')) }}',
                        confirmButtonColor: '#ef4444',
                        width: '30rem',
                        customClass: {
                            popup: 'rounded-2xl',
                            title: 'text-base font-black text-slate-900',
                            htmlContainer: 'px-1',
                            confirmButton: 'text-[10px] font-black uppercase tracking-widest px-5 py-2.5 rounded-lg'
                        }
                    });
                }
            },

            async generateAi() {
                if (sessionLocked) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('This assessment session is completed and read-only.') }}', type: 'info' } }));
                    return;
                }
                this.aiLoading = true;
                this.aiRec = '';
                this.aiPlan = '';
                this.aiInsight = '';
                this.aiPriority = '';
                this.aiValidation = '';
                this.aiImpact = '';
                const self = this;

                // Helper to start polling
                const startPolling = () => {
                    let pollCount = 0;
                    let pollInterval = setInterval(async () => {
                        pollCount++;
                        try {
                            let statusRes = await fetch(AI_STATUS_URL_TMPL.replace(':id', resultId));
                            let statusData = await statusRes.json();
                            let aiResult = statusData.data || statusData.result || statusData;

                            if (aiResult.has_ai) {
                                clearInterval(pollInterval);
                                self.aiRec        = aiResult.ai_recommendation;
                                self.aiPlan       = (typeof aiResult.corrective_action_plan === 'object' && aiResult.corrective_action_plan !== null)
                                                     ? (aiResult.corrective_action_plan.action || (Array.isArray(aiResult.corrective_action_plan) ? aiResult.corrective_action_plan.join('\n') : JSON.stringify(aiResult.corrective_action_plan)))
                                                     : (aiResult.corrective_action_plan || '');
                                self.aiInsight    = (typeof aiResult.control_insight === 'object' && aiResult.control_insight !== null)
                                                     ? (aiResult.control_insight.gap || (Array.isArray(aiResult.control_insight) ? aiResult.control_insight.join('\n') : JSON.stringify(aiResult.control_insight)))
                                                     : (aiResult.control_insight || '');
                                self.aiPriority   = aiResult.risk_priority || '';
                                self.aiValidation = '';
                                self.aiImpact     = aiResult.impact_interpretation || '';
                                self.aiLoading    = false;
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('AI analysis received successfully!') }}', type: 'success' } }));
                            } else if (pollCount > 24) { // Timeout after ~60 seconds (24 * 2.5s)
                                clearInterval(pollInterval);
                                self.aiLoading = false;
                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Timeout waiting for AI response.') }}', type: 'error' } }));
                            }
                        } catch(e) {
                            console.error('Polling error', e);
                            clearInterval(pollInterval);
                            self.aiLoading = false;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Error retrieving AI status.') }}', type: 'error' } }));
                        }
                    }, 2500);
                };

                try {
                    const form = this.$refs.form;
                    const formData = new FormData(form);
                    formData.append('trigger_ai', '1');

                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    const data = await res.json();

                    // Guard: already processing
                    if (res.status === 429 || (data && data.is_processing)) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('AI is currently analyzing this control.') }}', type: 'info' } }));
                        startPolling();
                        return;
                    }

                    // Guard: no data change since last AI generation
                    if (res.status === 409 && data.no_change) {
                        self.aiLoading = false;
                        // Restore existing AI data since we aborted
                        const statusRes = await fetch(AI_STATUS_URL_TMPL.replace(':id', resultId));
                        const statusData = await statusRes.json();
                        const aiResult = statusData.data || statusData.result || statusData;
                        if (aiResult.has_ai) {
                            self.aiRec        = aiResult.ai_recommendation || '';
                            self.aiPlan       = (typeof aiResult.corrective_action_plan === 'object' && aiResult.corrective_action_plan !== null)
                                                 ? (aiResult.corrective_action_plan.action || (Array.isArray(aiResult.corrective_action_plan) ? aiResult.corrective_action_plan.join('\n') : JSON.stringify(aiResult.corrective_action_plan)))
                                                 : (aiResult.corrective_action_plan || '');
                            self.aiInsight    = (typeof aiResult.control_insight === 'object' && aiResult.control_insight !== null)
                                                 ? (aiResult.control_insight.gap || (Array.isArray(aiResult.control_insight) ? aiResult.control_insight.join('\n') : JSON.stringify(aiResult.control_insight)))
                                                 : (aiResult.control_insight || '');
                            self.aiPriority   = aiResult.risk_priority || '';
                            self.aiValidation = '';
                            self.aiImpact     = aiResult.impact_interpretation || '';
                        }
                        Swal.fire({
                            icon: 'warning',
                            title: '{{ __('Warning: No Data Changes Detected') }}',
                            html: '<p class=\'text-sm text-slate-600 font-medium leading-relaxed\'>{{ addslashes(__('Re-generation of AI recommendation is disabled because no maturity scores, notes, or applicability data have changed since the last AI generation.')) }}</p>' +
                                  '<p class=\'text-xs text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200 mt-3 font-semibold flex items-center gap-2\'><i class=\'fa-solid fa-triangle-exclamation text-amber-600\'></i> {{ addslashes(__('Please update the maturity score or remarks before attempting to re-generate AI recommendations.')) }}</p>',
                            confirmButtonText: '{{ __('Understood') }}',
                            confirmButtonColor: '#f59e0b',
                            width: '27rem',
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
                        startPolling();
                    } else {
                        self.aiLoading = false;
                        window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || '{{ __('Failed to trigger AI generation.') }}', type: 'error' } }));
                    }
                } catch(e) {
                    console.error(e);
                    self.aiLoading = false;
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Failed to trigger AI generation.') }}', type: 'error' } }));
                }
            }
        }));
    };

    if (window.Alpine) {
        registerResultCard();
    } else {
        document.addEventListener('alpine:init', registerResultCard);
    }
})();
</script>

