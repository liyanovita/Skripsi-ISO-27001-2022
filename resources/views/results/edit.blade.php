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
<div x-data="{ showFinalizeModal: false, openId: {{ $firstOpenId ?? 'null' }}, missingScores: @json($missingCodes ?? []), finalizeMessage() { return this.missingScores.length > 0 ? 'There are ' + this.missingScores.length + ' controls without a score. Please score every control before finalizing.' : 'You are about to finalize this audit session. Evidence and notes are optional.'; }, submitFinalize() { this.$refs.finalizeForm.submit(); } }" class="max-w-7xl mx-auto space-y-8">
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
        <div id="result-{{ $result->id }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden scroll-mt-32 transition-all duration-200"
             :class="openId === {{ $result->id }} ? 'border-blue-200 shadow-md' : 'hover:border-slate-200'">

            {{-- Accordion Header --}}
            <button type="button"
                x-on:click="openId = (openId === {{ $result->id }}) ? null : {{ $result->id }}"
                class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left transition-colors"
                :class="openId === {{ $result->id }} ? 'bg-slate-50 border-b border-slate-100' : 'hover:bg-slate-50/50'">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="shrink-0 px-2 py-0.5 {{ in_array($result->standard->type, ['clause','clausa']) ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }} text-[9px] font-black rounded border uppercase tracking-widest">
                        {{ $result->standard->code }}
                    </span>
                    <span class="text-sm font-bold text-slate-800 truncate">{{ __($result->standard->title) }}</span>
                    @if($result->maturity_rating !== null)
                    <span class="shrink-0 px-2 py-0.5 bg-slate-900 text-white rounded text-[9px] font-black uppercase tracking-widest">
                        L{{ $result->maturity_rating }}
                    </span>
                    @else
                    <span class="shrink-0 px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded text-[9px] font-black uppercase tracking-widest">
                        {{ __('Unscored') }}
                    </span>
                    @endif
                </div>
                <i class="fa-solid fa-chevron-down text-slate-400 text-xs shrink-0 transition-transform duration-300"
                   :class="openId === {{ $result->id }} ? 'rotate-180' : ''"></i>
            </button>

            {{-- Accordion Body --}}
            <div x-show="openId === {{ $result->id }}"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">
            <form action="{{ route('results.update', $result->id) }}" method="POST" enctype="multipart/form-data" class="p-8" x-data="{ rating: {{ $result->maturity_rating === null ? 'null' : $result->maturity_rating }}, labels: {0:'Non-existent', 1:'Initial', 2:'Limited/Repeatable', 3:'Defined', 4:'Managed', 5:'Optimized'} }">
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
                                <template x-if="rating !== null">
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
                                        0 => 'Non-existent',
                                        1 => 'Initial',
                                        2 => 'Limited/Repeatable',
                                        3 => 'Defined',
                                        4 => 'Managed',
                                        5 => 'Optimized'
                                    ];
                                @endphp
                                @for($i = 0; $i <= 5; $i++)
                                <label class="relative group cursor-pointer">
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
                                                }
                                            });
                                        "
                                        class="peer hidden">
                                    <div class="flex flex-col items-center gap-1 p-3 rounded-xl border border-slate-100 text-slate-400 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-slate-900 peer-checked:scale-110 peer-checked:ring-4 peer-checked:ring-slate-900/30 peer-checked:z-10 hover:border-blue-500 hover:-translate-y-1 hover:shadow-md transition-all duration-300 transform">
                                        <span class="text-lg font-bold">{{ $i }}</span>
                                        <span class="text-[8px] font-bold uppercase tracking-tight text-center leading-none">{{ $labels[$i] }}</span>
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

                        {{-- AI Compliance Synthesis Card --}}
                        @if(!empty($result->ai_recommendation))
                        <div class="mt-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl border border-indigo-100 p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-indigo-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white shadow-md shadow-indigo-600/10">
                                        <i class="fa-solid fa-robot text-sm"></i>
                                    </div>
                                    <div class="leading-none">
                                        <h4 class="text-xs font-black text-slate-900 tracking-tight uppercase">{{ __('AI Compliance Synthesis') }}</h4>
                                        <p class="text-[8px] text-indigo-600 font-bold uppercase tracking-widest mt-0.5">{{ __('Gemini 2.5 Flash Expert Recommendation') }}</p>
                                    </div>
                                </div>
                                @if(!empty($result->risk_priority))
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-100 text-[8px] font-black rounded-lg uppercase tracking-wider leading-none">
                                    {{ $result->risk_priority }}
                                </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-1">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Strategic Recommendation') }}</span>
                                    <p class="text-xs text-slate-700 font-medium leading-relaxed bg-white/60 p-3 rounded-xl border border-slate-100/50 shadow-inner h-full">{{ $result->ai_recommendation }}</p>
                                </div>
                                
                                @if(!empty($result->corrective_action_plan))
                                <div class="space-y-1">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Corrective Action Plan') }}</span>
                                    <div class="text-xs text-slate-700 font-medium leading-relaxed bg-white/60 p-3 rounded-xl border border-slate-100/50 shadow-inner whitespace-pre-line h-full">{{ $result->corrective_action_plan }}</div>
                                </div>
                                @endif

                                @if(!empty($result->control_insight['gap']))
                                <div class="space-y-1">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('AI Audit Insight (Gap Analysis)') }}</span>
                                    <p class="text-xs text-slate-700 font-medium leading-relaxed bg-white/60 p-3 rounded-xl border border-slate-100/50 shadow-inner h-full">{{ $result->control_insight['gap'] }}</p>
                                </div>
                                @endif
                            </div>

                            @if(!empty($result->evidence_validation))
                            <div class="pt-3 border-t border-indigo-100 flex items-start gap-2.5 text-[10px] text-indigo-700 font-semibold bg-indigo-50/50 p-3 rounded-xl border border-indigo-100/30">
                                <i class="fa-solid fa-circle-check mt-0.5 text-indigo-500"></i>
                                <div>
                                    <span class="block text-[8px] font-black text-indigo-400 uppercase tracking-widest mb-0.5">{{ __('Evidence Validation') }}</span>
                                    <p class="leading-relaxed">{{ $result->evidence_validation }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
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
                                                window.location.reload();
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
                                 <p class="text-[10px] text-slate-500 font-medium">{{ __('Gemini is currently analyzing your evidence and drafting strategic recommendations...') }}</p>
                             </div>

                             <div class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-full text-[8px] font-black uppercase tracking-widest text-indigo-700 animate-pulse relative z-10">
                                 <i class="fa-solid fa-spinner animate-spin"></i>{{ __('Processing Real-Time Synthesis') }}</div>
                        </div>
                        @endif

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="px-8 py-3.5 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/20 transition-all flex items-center gap-2 active:scale-95">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                Save & Analyze with AI
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            </div>{{-- /accordion body --}}
        </div>
        @endforeach
    </div>

    {{-- Finalize Audit Section --}}
    @php
        $totalControls = $assessableResults->count();
        $completedControls = $assessableResults->where('status', 'completed')->count();
        $allCompleted = $totalControls > 0 && $totalControls === $completedControls;
    @endphp

    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 mt-6">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            {{-- Progress Info --}}
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg {{ $allCompleted ? 'bg-emerald-600 shadow-emerald-600/20' : 'bg-slate-400 shadow-slate-400/10' }}">
                    <i class="fa-solid {{ $allCompleted ? 'fa-flag-checkered' : 'fa-clipboard-list' }} text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">{{ __('Audit Completion') }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest mt-0.5 {{ $allCompleted ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $completedControls }}/{{ $totalControls }} {{ __('controls scored') }}
                    </p>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="flex-1 max-w-md w-full">
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $allCompleted ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $totalControls > 0 ? round(($completedControls / $totalControls) * 100) : 0 }}%"></div>
                </div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 text-right">
                    {{ $totalControls > 0 ? round(($completedControls / $totalControls) * 100) : 0 }}% {{ __('Complete') }}
                </p>
            </div>

            {{-- Finalize Button --}}
            @if($allCompleted && $session->status !== 'completed')
                <form x-ref="finalizeForm" id="finalize-form" action="{{ route('sessions.finalize', $session->id) }}" method="POST">
                    @csrf
                    <button type="button" x-on:click="showFinalizeModal = true" aria-label="Finalize Audit" title="{{ __('Finalize Audit') }}" 
                        class="px-8 py-3.5 bg-emerald-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-600/20 transition-all flex items-center gap-2 active:scale-95 shadow-md">
                        <i class="fa-solid fa-circle-check"></i>
                        {{ __('Finalize Audit') }}
                    </button>
                </form>

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
            @elseif($session->status === 'completed')
                <div class="px-6 py-3 bg-emerald-50 text-emerald-700 text-xs font-black uppercase tracking-widest rounded-xl border border-emerald-200 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ __('Audit Completed') }}
                </div>
            @else
                <div class="px-6 py-3 bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-widest rounded-xl border border-slate-200 flex items-center gap-2 cursor-not-allowed">
                    <i class="fa-solid fa-lock"></i>
                    {{ __('Score All Controls First') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
