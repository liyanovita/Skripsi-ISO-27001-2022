@extends('layouts.app')
@section('title', 'Remediation Hub - ' . ($result->standard->code ?? ''))
@section('view_name', 'Remediation Hub')

@section('content')
@php
    $evidenceFiles = is_array($result->evidence_file) ? $result->evidence_file : (empty($result->evidence_file) ? [] : [$result->evidence_file]);
    $complianceStatus = match(true) {
        !$result->is_applicable => __('Not Applicable'),
        $result->status !== 'completed' => __('Pending'),
        $result->maturity_rating >= 4 => __('Compliant'),
        $result->maturity_rating >= 2 => __('Partially Compliant'),
        default => __('Non-Compliant'),
    };
    $riskLevelLabel = $result->calculated_risk_priority ?? $result->risk_level ?? 'Low';
@endphp

<div class="max-w-[1400px] mx-auto space-y-6 pb-16">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-bold shadow-xs">
        <i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-bold shadow-xs">
        <i class="fa-solid fa-circle-exclamation text-rose-500 text-base"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Back Navigation --}}
    <div class="flex items-center justify-between gap-4">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('workspace.index', ['session_id' => $result->session_id]) }}" 
           onclick="if (document.referrer && document.referrer !== location.href) { window.history.back(); return false; }"
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-blue-600 transition-colors bg-white px-4 py-2.5 rounded-2xl border border-slate-200 shadow-xs hover:border-slate-300">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Back') }}
        </a>
        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ISO 27001:2022 &mdash; Remediation Hub</span>
    </div>

    {{-- Header Banner Card --}}
    <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-start gap-4 flex-1">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-xl shrink-0 shadow-md">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-3 py-1 rounded-xl bg-blue-500/30 text-blue-300 font-black text-xs border border-blue-400/30">{{ $result->standard->code }}</span>
                        <h1 class="text-lg sm:text-xl font-black text-white tracking-tight leading-snug">{{ __($result->standard->title) }}</h1>
                    </div>
                    <div class="text-xs text-slate-300 font-medium">
                        <span class="inline-flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/10">
                            <i class="fa-solid fa-folder text-blue-400 text-xs"></i>
                            Session: <strong class="text-white ml-1">{{ $result->session->name }}</strong>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap lg:justify-end shrink-0">
                <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase bg-white/10 border border-white/20 text-white">{{ $complianceStatus }}</span>
                <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase bg-rose-500/20 border border-rose-400/30 text-rose-300">Risk: {{ $riskLevelLabel }}</span>
                <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase bg-blue-500/20 border border-blue-400/30 text-blue-200">Maturity: {{ $result->maturity_rating ?? 0 }}/5</span>
                <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase
                    {{ $result->treatment_status === 'closed' ? 'bg-emerald-500/20 border border-emerald-400/30 text-emerald-300' : ($result->treatment_status === 'in_progress' ? 'bg-amber-500/20 border border-amber-400/30 text-amber-300' : 'bg-slate-500/20 border border-slate-400/30 text-slate-300') }}">
                    {{ ucfirst(str_replace('_', ' ', $result->treatment_status ?? 'Open')) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Audit Context + Previous Notes & Evidence + AI Plan --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Audit Evaluation Metrics --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-clipboard-check text-blue-500"></i>
                    {{ __('Initial Audit Context') }}
                    <span class="ml-auto text-[8px] font-black text-slate-400 normal-case tracking-normal">Read-Only</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ __('Maturity') }}</span>
                        @if(!$result->is_applicable || $result->maturity_rating === null)
                            <span class="text-lg font-black text-slate-400">&mdash;</span>
                        @else
                            <span class="text-lg font-black text-slate-900">{{ $result->maturity_rating }}<span class="text-xs text-slate-400">/5</span></span>
                        @endif
                    </div>
                    <div class="bg-rose-50/50 p-3 rounded-2xl border border-rose-100 text-center">
                        <span class="text-[8px] font-black text-rose-400 uppercase tracking-widest block mb-1">{{ __('Gap') }}</span>
                        @if(!$result->is_applicable || $result->maturity_rating === null)
                            <span class="text-lg font-black text-slate-400">&mdash;</span>
                        @else
                            <span class="text-lg font-black text-rose-600">{{ 5 - $result->maturity_rating }}</span>
                        @endif
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ __('Status') }}</span>
                        <span class="text-xs font-black text-slate-800 leading-tight">{{ !$result->is_applicable ? __('Not Applicable') : $complianceStatus }}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 text-center">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ __('Risk') }}</span>
                        <span class="text-xs font-black text-slate-600">{{ !$result->is_applicable ? 'N/A' : $riskLevelLabel }}</span>
                    </div>
                </div>

                @if(!empty($result->soa_justification))
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest block mb-1.5">{{ __('SoA Justification') }}</span>
                    <p class="text-xs text-slate-700 leading-relaxed italic">{{ $result->soa_justification }}</p>
                </div>
                @endif
            </div>

            {{-- Previous Notes (Read-Only) --}}
            @if(!empty($result->notes))
            <div class="bg-amber-50 p-6 rounded-3xl border border-amber-200 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-amber-200 pb-3">
                    <h3 class="text-xs font-black text-amber-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
                        {{ __('Previous Remediation Notes') }}
                    </h3>
                    <span class="text-[8px] font-black text-amber-500 uppercase tracking-widest bg-amber-100 px-2 py-0.5 rounded-lg border border-amber-200">{{ __('Read-Only') }}</span>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-amber-100">
                    <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ $result->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Previous Evidence Files (Read-Only) --}}
            @if(count($evidenceFiles) > 0)
            <div class="bg-blue-50 p-6 rounded-3xl border border-blue-200 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-blue-200 pb-3">
                    <h3 class="text-xs font-black text-blue-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-folder-open text-blue-500"></i>
                        {{ __('Previous Evidence Files') }}
                    </h3>
                    <span class="text-[8px] font-black text-blue-500 uppercase tracking-widest bg-blue-100 px-2 py-0.5 rounded-lg border border-blue-200">{{ count($evidenceFiles) }} {{ __('file(s)') }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($evidenceFiles as $file)
                    <a href="{{ route('results.evidence', [$result->id, 'file' => $file]) }}" target="_blank"
                       class="flex items-center gap-3 p-3 bg-white/70 border border-blue-100 rounded-2xl text-xs text-blue-700 font-bold hover:bg-white hover:border-blue-300 transition-all group">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-file-arrow-down text-sm"></i>
                        </div>
                        <span class="truncate flex-1">{{ basename($file) }}</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-blue-400 group-hover:text-blue-600 transition-colors"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- AI Recommendations & Corrective Action Plan --}}
            @if(!empty($result->ai_recommendation) || !empty($result->corrective_action_plan))
            <div class="bg-gradient-to-br from-indigo-50/80 to-white p-6 rounded-3xl border border-indigo-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3 border-b border-indigo-100 pb-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm shadow-xs">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">{{ __('AI Audit Recommendations') }}</h3>
                        <p class="text-[8px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5">{{ __('ISO 27001 Implementation Guidance') }}</p>
                    </div>
                </div>
                @if(!empty($result->ai_recommendation))
                <div class="bg-white p-4 rounded-2xl border border-indigo-100 shadow-2xs">
                    <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest block mb-1.5">{{ __('Strategic Recommendation') }}</span>
                    <p class="text-xs text-slate-700 leading-relaxed">{{ $result->ai_recommendation }}</p>
                </div>
                @endif
                @if(!empty($result->corrective_action_plan))
                <div class="bg-white p-4 rounded-2xl border border-indigo-100 shadow-2xs">
                    <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest block mb-1.5">{{ __('Corrective Action Roadmap') }}</span>
                    <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ is_array($result->corrective_action_plan) ? implode("\n", array_map(fn($i) => is_array($i) ? implode(' ', $i) : (string)$i, $result->corrective_action_plan)) : $result->corrective_action_plan }}</p>
                </div>
                @endif
            </div>
            @endif

        </div>

        {{-- RIGHT: Editable Remediation Action Form --}}
        <div>
            <form action="{{ route('workspace.entry.update', $result->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                @php
                    $isRemediationExpired = $result->treatment_due_date && $result->treatment_due_date->isPast() && (!auth()->user() || !auth()->user()->isAdmin());
                    $isLocked = $isRemediationExpired;
                @endphp
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-5 sticky top-6">

                    <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                                {{ __('Update Remediation') }}
                            </h3>
                            <p class="text-[8px] text-slate-400 font-bold uppercase tracking-wider mt-1">{{ __('Fill in to add new progress') }}</p>
                        </div>
                        @if($isLocked)
                        <span class="px-2 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-[9px] font-black uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-lock text-amber-500"></i> {{ __('Deadline Expired') }}
                        </span>
                        @endif
                    </div>

                    @if($isLocked)
                    <div class="p-3.5 bg-amber-50/80 border border-amber-200/80 rounded-2xl text-amber-800 text-xs font-bold flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-lock text-amber-500 text-sm mt-0.5 shrink-0"></i>
                        <span class="leading-snug">
                            {{ __('The remediation deadline for this control has expired') }} ({{ $result->treatment_due_date->format('d/m/Y') }}). {{ __('Remediation updates are now locked (Read-Only).') }}
                        </span>
                    </div>
                    @endif

                    {{-- PIC Assigned --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block">
                            <i class="fa-solid fa-user-gear text-blue-600 mr-1"></i>{{ __('PIC Assigned') }}
                        </label>
                        <select name="treatment_pic" {{ $isLocked ? 'disabled' : '' }} class="w-full text-xs font-bold text-slate-800 border border-slate-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500/30 bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach($users as $userOption)
                                <option value="{{ $userOption->name }}" {{ ($result->treatment_pic === $userOption->name) ? 'selected' : '' }}>
                                    {{ $userOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($result->treatment_pic)
                        <p class="text-[8px] text-slate-400 font-medium"><i class="fa-solid fa-user text-slate-300 mr-1"></i>{{ __('Current PIC') }}: <strong>{{ $result->treatment_pic }}</strong></p>
                        @endif
                    </div>

                    {{-- Target Deadline --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block">
                            <i class="fa-regular fa-calendar-check text-blue-600 mr-1"></i>{{ __('Target Deadline') }}
                        </label>
                        <input type="date" name="treatment_due_date"
                               value="{{ optional($result->treatment_due_date)->toDateString() }}"
                               {{ $isLocked ? 'disabled' : '' }}
                               class="w-full text-xs font-bold text-slate-800 border border-slate-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500/30 bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    {{-- Action Status --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block">
                            <i class="fa-solid fa-bars-progress text-blue-600 mr-1"></i>{{ __('Action Status') }}
                        </label>
                        <select name="treatment_status" {{ $isLocked ? 'disabled' : '' }} class="w-full text-xs font-black uppercase tracking-wider border border-slate-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500/30 bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="open" {{ ($result->treatment_status === 'open' || !$result->treatment_status) ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $result->treatment_status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="closed" {{ $result->treatment_status === 'closed' ? 'selected' : '' }}>Closed / Solved</option>
                        </select>
                    </div>

                    {{-- New Remediation Notes (kosong/empty) --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block">
                            <i class="fa-solid fa-pen-nib text-blue-600 mr-1"></i>{{ __('Add New Progress Notes') }}
                        </label>
                        <textarea name="notes" rows="5"
                                  {{ $isLocked ? 'disabled' : '' }}
                                  placeholder="{{ $isLocked ? __('Session is closed (locked for new notes)') : __('Write new implementation progress, actions taken, findings, or updates here...') }}"
                                  class="w-full text-xs font-medium text-slate-800 border border-slate-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500/30 bg-slate-50 resize-none placeholder:text-slate-300 disabled:opacity-60 disabled:cursor-not-allowed"></textarea>
                        @if(!empty($result->notes) && !$isLocked)
                        <p class="text-[8px] text-amber-500 font-bold flex items-center gap-1"><i class="fa-solid fa-triangle-exclamation"></i>{{ __('Previous notes are shown on the left. New input will replace them.') }}</p>
                        @endif
                    </div>

                    {{-- Upload New Evidence File --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest block">
                            <i class="fa-solid fa-paperclip text-blue-600 mr-1"></i>{{ __('Upload New Evidence File') }}
                        </label>
                        <input type="file" name="evidence_file"
                               {{ $isLocked ? 'disabled' : '' }}
                               class="w-full text-xs text-slate-600 border border-slate-200 rounded-xl p-2 bg-slate-50 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[9px] file:font-black file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                        @if(count($evidenceFiles) > 0 && !$isLocked)
                        <p class="text-[8px] text-blue-500 font-bold flex items-center gap-1"><i class="fa-solid fa-circle-info"></i>{{ __('Existing files shown on the left. Upload to add or replace.') }}</p>
                        @endif
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-1">
                        @if($isLocked)
                        <button type="button" disabled
                                class="w-full py-3.5 bg-slate-200 text-slate-500 font-black text-xs uppercase tracking-widest rounded-xl shadow-xs cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fa-solid fa-lock"></i>
                            <span>{{ __('Remediation Deadline Expired') }}</span>
                        </button>
                        @else
                        <button type="submit"
                                class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>{{ __('Save & Update Remediation') }}</span>
                        </button>
                        @endif
                    </div>

                </div>
            </form>
        </div>

    </div>
</div>
@endsection
