{{-- Lazy-loaded interactive body (rating form, notes, evidence, AI status) for a single
     control card on the session assessment page. Fetched on-demand via
     ResultController@cardBody and injected client-side, instead of being rendered inline
     for every one of the ~137 controls up front (was causing multi-MB page payloads). --}}
@php
    $isClause = in_array($result->standard->type, ['clause', 'clausa']);
    $session = $result->session;
@endphp
<form x-ref="form" action="{{ route('results.update', $result->id) }}" method="POST" class="p-5 space-y-5 border-t border-slate-100">
    @csrf
    <fieldset @if($session->isLockedForUser(auth()->user()) || $session->status === 'completed') disabled class="opacity-80" @endif>

    @if(!$isClause)
    {{-- Statement of Applicability (SoA) - Annex A only --}}
    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/60 space-y-3">
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
            <div>
                <h4 class="text-[10px] font-bold text-slate-800 uppercase tracking-wider">{{ __('Statement of Applicability (SoA)') }}</h4>
                <p class="text-[9px] text-slate-500 font-medium leading-snug mt-0.5">{{ __('Is this control applicable to your organization?') }}</p>
            </div>
            <div class="flex items-center gap-2 @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) pointer-events-none select-none opacity-60 @endif">
                <label class="relative inline-flex items-center @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) cursor-not-allowed @else cursor-pointer @endif">
                    <input type="radio" name="is_applicable" value="1" :checked="isApplicable"
                        @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) disabled @endif
                        x-on:change="
                             isApplicable = true;
                             $nextTick(() => submitForm());
                         "
                        class="peer hidden">
                    <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 transition-all">
                        {{ __('Yes') }}
                    </div>
                </label>
                <label class="relative inline-flex items-center @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) cursor-not-allowed @else cursor-pointer @endif">
                    <input type="radio" name="is_applicable" value="0" :checked="!isApplicable"
                        @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) disabled @endif
                        x-on:change="
                             isApplicable = false;
                             rating = null;
                             $nextTick(() => submitForm());
                         "
                        class="peer hidden">
                    <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition-all">
                        {{ __('No') }}
                    </div>
                </label>
            </div>
        </div>

        {{-- SoA Justification (Shown only if NOT applicable) --}}
        <div x-show="!isApplicable" x-transition class="pt-3 border-t border-slate-200 @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) pointer-events-none select-none opacity-70 @endif">
            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-2">{{ __('Exclusion Justification') }} <span class="text-rose-500">*</span></label>
            <textarea name="soa_justification" rows="2" x-model="soaJustification"
                @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) disabled readonly @endif
                x-on:blur="submitForm()"
                placeholder="{{ __('Enter explanation for excluding this control...') }}"
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-medium outline-none focus:border-blue-600 transition-all text-slate-800 leading-relaxed shadow-inner">{{ $result->soa_justification }}</textarea>
        </div>
    </div>
    @endif

    <div x-show="isApplicable" class="space-y-4">

    {{-- Collapsible: Control Details (Structural Requirements + Implementation Roadmap) --}}
    @if($result->standard->description || $result->standard->implementation_guidance)
    <div x-data="{ showDetails: false }" class="rounded-xl border border-slate-200/70 overflow-hidden pointer-events-auto">
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
        <div class="space-y-4 @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) pointer-events-none select-none opacity-80 @endif">
            @foreach($result->standard->questions as $qIndex => $q)
            <div class="space-y-2">
                <p class="text-slate-800 font-bold text-[11px] leading-relaxed">{{ __($q) }}</p>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-1.5">
                    @php
                        $options = [
                            0 => ['title' => 'Non-existent', 'desc' => 'Control is not implemented', 'color' => 'bg-slate-100 text-slate-400 border-slate-200'],
                            1 => ['title' => 'Initial', 'desc' => 'Control is planned but not consistently implemented', 'color' => 'bg-blue-50 text-blue-400 border-blue-100'],
                            2 => ['title' => 'Limited/Repeatable', 'desc' => 'Control is partially implemented', 'color' => 'bg-blue-100 text-blue-600 border-blue-200'],
                            3 => ['title' => 'Defined', 'desc' => 'Control is implemented according to defined procedures', 'color' => 'bg-blue-500 text-white border-blue-400'],
                            4 => ['title' => 'Managed', 'desc' => 'Control is consistently implemented and its effectiveness is monitored', 'color' => 'bg-blue-700 text-white border-blue-600'],
                            5 => ['title' => 'Optimized', 'desc' => 'Control is optimally implemented and supported by continuous improvement', 'color' => 'bg-slate-900 text-white border-slate-900'],
                        ];
                    @endphp
                    @foreach($options as $val => $opt)
                    <label class="@if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) cursor-not-allowed @else cursor-pointer @endif group/btn" title="{{ __($opt['desc']) }}">
                        <input type="radio" name="answers[q{{ $qIndex }}]" value="{{ $val }}"
                               @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) disabled @endif
                               {{ isset($result->answers["q$qIndex"]) && $result->answers["q$qIndex"] == $val ? 'checked' : '' }}
                               @change="rating = {{ $val }}; submitForm()"
                               class="peer hidden">
                        <div class="py-1.5 px-0.5 text-center rounded-lg border-2 transition-all duration-300 {{ $opt['color'] }}
                                    opacity-40 saturate-50 peer-checked:opacity-100 peer-checked:saturate-100 peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-blue-500 peer-checked:border-blue-500 peer-checked:scale-105 peer-checked:shadow-md">
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
        <div class="lg:col-span-6 @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) pointer-events-none select-none opacity-70 @endif">
            <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('User Findings') }}</h5>
            <textarea name="notes" rows="3" @input.debounce.2000ms="submitForm()"
                      @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) disabled readonly @endif
                      placeholder="{{ __('Enter findings...') }}"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-[10px] font-medium outline-none focus:bg-white focus:border-blue-600 transition-all text-slate-800 leading-relaxed shadow-inner h-[86px] resize-none">{{ $result->notes }}</textarea>
        </div>

        {{-- Evidence Repository (Right Side) --}}
        <div class="lg:col-span-6 flex flex-col justify-between">
            <div>
                <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ __('Evidence Repository') }}</h5>
                <div class="relative group/up @if($session->status === 'completed' || $session->isLockedForUser(auth()->user())) pointer-events-none select-none opacity-60 @endif">
                    @if($session->status !== 'completed' && !$session->isLockedForUser(auth()->user()))
                    <input type="file" name="evidence_file" @change="submitForm().then((result) => { $el.value = ''; if (result.success) { window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('Artifact uploaded!') }}', type: 'success' } })); autoExtractMissing(); } });" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    @endif
                    <div class="w-full py-2 bg-white border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center gap-1 @if($session->status !== 'completed' && !$session->isLockedForUser(auth()->user())) group-hover/up:border-blue-400 group-hover:bg-blue-50/50 @else opacity-50 cursor-not-allowed bg-slate-50 @endif transition-all">
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
                            PDF, JPG, JPEG, PNG, DOC, DOCX, XLSX (Max 10MB)
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
                                <button type="button" @click="viewSummaryEvidence(file)" :disabled="!evidenceExtractions[file]"
                                        class="text-[8px] font-black text-slate-500 uppercase hover:underline disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:no-underline">
                                    <i class="fa-solid fa-file-lines text-[9px]"></i> {{ __('View Summary') }}
                                </button>
                                <template x-if="extractingFiles.includes(file)">
                                    <span class="text-[8px] font-black text-blue-500 uppercase flex items-center gap-1">
                                        <i class="fa-solid fa-spinner fa-spin text-[9px]"></i> {{ __('Extracting...') }}
                                    </span>
                                </template>
                                @if($session->status !== 'completed' && !$session->isLockedForUser(auth()->user()))
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
                                @endif
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

    </div>

    </fieldset>

    {{-- Compact AI Status Indicator --}}
    <template x-if="rating < 5">
        <div class="pt-4 border-t border-slate-100 mt-2 flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                     :class="aiLoading ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20 animate-pulse' : (aiRec ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-200 text-slate-400')">
                    <i class="fa-solid" :class="aiLoading ? 'fa-spinner animate-spin text-xs' : 'fa-robot text-xs'"></i>
                </div>
                <div>
                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none">{{ __('AI Synthesis Status') }}</h4>
                    <template x-if="aiLoading">
                        <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest mt-1 animate-pulse"><i class="fa-solid fa-spinner animate-spin mr-1"></i>{{ __('Synthesizing...') }}</p>
                    </template>
                    <template x-if="!aiLoading && aiRec">
                        <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-1"><i class="fa-solid fa-check-circle mr-1"></i>{{ __('Analysis Ready') }}</p>
                    </template>
                    <template x-if="!aiLoading && !aiRec">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('Pending Generation') }}</p>
                    </template>
                </div>
            </div>

            <template x-if="aiRec">
                <div class="flex items-center gap-2">
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('open-ai-details', { detail: {
                            code: code,
                            title: title,
                            rec: aiRec,
                            plan: aiPlan,
                            insight: aiInsight,
                            priority: aiPriority,
                            validation: '',
                            impact: aiImpact,
                            resultId: {{ $result->id }}
                        }}))"
                        class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all shadow-md shadow-blue-600/20 cursor-pointer">
                        <i class="fa-solid fa-eye mr-1"></i>{{ __('View Result') }}</button>
                    @if($session->status === 'completed')
                    <a href="{{ route('workspace.index', ['session_id' => $session->id, 'focus' => $result->id]) }}" class="px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white rounded-lg text-[8px] font-black uppercase tracking-widest transition-all border border-blue-100 cursor-pointer">{{ __('Improvement') }}<i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                    @endif
                    @if($session->status !== 'completed' && !$session->isLockedForUser(auth()->user()))
                    <template x-if="rating < 5">
                        <button type="button" @click="generateAi()" :disabled="aiLoading"
                                class="px-4 py-2 bg-white border border-slate-200 hover:border-blue-400 hover:text-blue-600 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all flex items-center gap-2 text-slate-600">
                            <i class="fa-solid fa-arrows-rotate" :class="aiLoading && 'animate-spin text-blue-500'"></i>
                            <span x-text="aiLoading ? '{{ __('Regenerating...') }}' : '{{ __('Regenerate') }}'"></span>
                        </button>
                    </template>
                    @endif
                </div>
            </template>

            @if($session->status !== 'completed' && !$session->isLockedForUser(auth()->user()))
            <template x-if="isCompleted && rating < 5 && !aiRec">
                <button type="button" @click="generateAi()" :disabled="aiLoading"
                        class="px-4 py-2 bg-white border border-slate-200 hover:border-blue-400 hover:text-blue-600 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all flex items-center gap-2 text-slate-600">
                    <i class="fa-solid fa-wand-magic-sparkles" :class="aiLoading && 'animate-spin text-blue-500'"></i>
                    <span x-text="aiLoading ? '{{ __('Synthesizing...') }}' : '{{ __('Generate AI') }}'"></span>
                </button>
            </template>
            @endif
        </div>
    </template>
</form>
