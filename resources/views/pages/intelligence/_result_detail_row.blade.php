{{-- Lazy-loaded expandable detail for a single control row on the Assessment Result page.
     Fetched on-demand via ResultController@rowDetail and injected client-side, instead of
     being rendered inline for every control up front (was causing multi-MB page payloads). --}}
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
                    @php
                        $evidenceFilesList = is_array($result->evidence_file) ? $result->evidence_file : [$result->evidence_file];
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($evidenceFilesList as $file)
                            @if(is_string($file) && !empty($file))
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($file) }}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-blue-600 hover:underline">
                                    <i class="fa-solid fa-file-pdf text-rose-500"></i> {{ basename($file) }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    <span class="text-slate-400 italic">No evidence document uploaded for this control</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Complete AI Compliance Synthesis Dropdown Accordion --}}
    @if($result->ai_recommendation || $result->control_insight || $result->impact_interpretation || $result->corrective_action_plan)
    @php
        $recText = $result->ai_recommendation ?: '';
        $planData = $result->corrective_action_plan;
        $planText = is_array($planData) ? implode("\n", array_filter(array_map(fn($i) => is_array($i) ? implode(' ', $i) : trim((string)$i), $planData))) : ($planData ?: '');
        $insightData = $result->control_insight;
        $insightText = is_array($insightData) ? implode("\n", array_filter(array_map(fn($i) => is_array($i) ? implode(' ', $i) : trim((string)$i), $insightData))) : ($insightData ?: '');
        $impactText = $result->impact_interpretation ?: '';
    @endphp
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
        </div>

        {{-- Dropdown Accordion List per Control --}}
        <div class="space-y-2.5">
            {{-- Accordion 1: STRATEGIC RECOMMENDATION --}}
            @if($recText)
            <div class="rounded-xl border transition-all overflow-hidden"
                 :class="activeAccordion === 'rec' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                <button type="button"
                    @click="activeAccordion = activeAccordion === 'rec' ? null : 'rec'"
                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                    :class="activeAccordion === 'rec' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                             :class="activeAccordion === 'rec' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                            <i class="fa-solid fa-lightbulb text-[9px]"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest"
                              :class="activeAccordion === 'rec' ? 'text-blue-700' : 'text-slate-700'">
                            {{ __('STRATEGIC RECOMMENDATION') }}
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="activeAccordion === 'rec' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                </button>
                <div x-show="activeAccordion === 'rec'" x-collapse.duration.200ms>
                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $recText }}</div>
                </div>
            </div>
            @endif

            {{-- Accordion 2: CORRECTIVE ACTION PLAN --}}
            @if($planText)
            <div class="rounded-xl border transition-all overflow-hidden"
                 :class="activeAccordion === 'cap' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                <button type="button"
                    @click="activeAccordion = activeAccordion === 'cap' ? null : 'cap'"
                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                    :class="activeAccordion === 'cap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                             :class="activeAccordion === 'cap' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                            <i class="fa-solid fa-list-check text-[9px]"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest"
                              :class="activeAccordion === 'cap' ? 'text-blue-700' : 'text-slate-700'">
                            {{ __('CORRECTIVE ACTION PLAN') }}
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="activeAccordion === 'cap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                </button>
                <div x-show="activeAccordion === 'cap'" x-collapse.duration.200ms>
                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $planText }}</div>
                </div>
            </div>
            @endif

            {{-- Accordion 3: AI AUDIT INSIGHT (GAP) --}}
            @if($insightText)
            <div class="rounded-xl border transition-all overflow-hidden"
                 :class="activeAccordion === 'gap' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                <button type="button"
                    @click="activeAccordion = activeAccordion === 'gap' ? null : 'gap'"
                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                    :class="activeAccordion === 'gap' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                             :class="activeAccordion === 'gap' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                            <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest"
                              :class="activeAccordion === 'gap' ? 'text-blue-700' : 'text-slate-700'">
                            {{ __('AI AUDIT INSIGHT (GAP)') }}
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="activeAccordion === 'gap' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                </button>
                <div x-show="activeAccordion === 'gap'" x-collapse.duration.200ms>
                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $insightText }}</div>
                </div>
            </div>
            @endif

            {{-- Accordion 4: IMPACT INTERPRETATION --}}
            @if($impactText)
            <div class="rounded-xl border transition-all overflow-hidden"
                 :class="activeAccordion === 'impact' ? 'border-blue-200 bg-blue-50/50 shadow-xs' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-2xs'">
                <button type="button"
                    @click="activeAccordion = activeAccordion === 'impact' ? null : 'impact'"
                    class="w-full flex items-center justify-between gap-3 p-3 text-left cursor-pointer transition-colors"
                    :class="activeAccordion === 'impact' ? 'bg-blue-50/80' : 'bg-white hover:bg-slate-50/60'">
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-colors"
                             :class="activeAccordion === 'impact' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'">
                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest"
                              :class="activeAccordion === 'impact' ? 'text-blue-700' : 'text-slate-700'">
                            {{ __('IMPACT INTERPRETATION') }}
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="activeAccordion === 'impact' ? 'rotate-180 text-blue-500' : 'text-slate-400'"></i>
                </button>
                <div x-show="activeAccordion === 'impact'" x-collapse.duration.200ms>
                    <div class="p-3.5 text-xs font-medium text-slate-700 leading-relaxed bg-blue-50/30 border-t border-blue-100/60 rounded-b-xl whitespace-pre-wrap">{{ $impactText }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>
