@extends('layouts.admin')

@section('title', 'Manage CAPA Action Plan')
@section('header_title', 'Manage CAPA Action Plan')

@section('content')
<div class="space-y-6 pb-16 max-w-7xl">

    {{-- Back Navigation --}}
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('admin.capa.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors bg-white px-4 py-2.5 rounded-2xl border border-slate-200 shadow-xs hover:border-slate-300">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Back to Improvement Tracking') }}
        </a>
    </div>

    {{-- Executive Header Banner Card --}}
    <div class="p-6 sm:p-7 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl shadow-xl space-y-5 relative overflow-hidden">
        {{-- Background Glow --}}
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            
            {{-- Header Row: Code, Title, Meta & Badges --}}
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                
                {{-- Left: Icon + Code + Title + Meta --}}
                <div class="flex items-start gap-4 flex-1">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg shrink-0 shadow-md shadow-indigo-600/30 mt-0.5">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <div class="space-y-3 flex-1">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="px-3 py-1 rounded-xl bg-indigo-500/30 text-indigo-300 font-black text-xs border border-indigo-400/30 shadow-xs">
                                {{ $capa->standard->code }}
                            </span>
                            <h1 class="text-lg sm:text-xl font-black text-white tracking-tight leading-snug">{{ $capa->standard->title }}</h1>
                        </div>
                        
                        {{-- Meta Info (Session & Assessed By) --}}
                        <div class="flex items-center gap-2.5 text-xs text-slate-300 font-medium flex-wrap">
                            <span class="inline-flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/10 shadow-xs">
                                <i class="fa-solid fa-folder text-indigo-400 text-xs"></i> 
                                <span>Session: <strong class="text-white font-bold">{{ $capa->session->name }}</strong></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/10 shadow-xs">
                                <i class="fa-solid fa-user-tie text-indigo-400 text-xs"></i> 
                                <span>Assessed By: <strong class="text-white font-bold">{{ $capa->session->user->name }}</strong></span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right: Metric Badges Row --}}
                <div class="shrink-0 flex items-center gap-2 flex-wrap lg:justify-end">
                    @php
                        $matInfo = \App\Models\AssessmentSession::getMaturityLevelClassification((float)($capa->maturity_rating ?? 0));
                        $cleanLevelName = str_replace('Maturity: ', '', $matInfo['name']);
                        $risk = $capa->calculated_risk_priority;
                        $compStatus = $capa->compliance_status;
                        $compBadgeClass = match($compStatus) {
                            'Compliant' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                            'Partially Compliant' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                            default => 'bg-rose-500/20 text-rose-300 border-rose-500/30'
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border {{ $compBadgeClass }} shadow-xs">
                        <i class="fa-solid fa-shield-halved text-[10px]"></i>
                        Compliance: {{ $compStatus }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border {{ $matInfo['badge_color'] }} shadow-xs">
                        <i class="fa-solid fa-chart-line text-[10px]"></i>
                        {{ $cleanLevelName }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-500/20 text-indigo-200 border border-indigo-500/30 shadow-xs">
                        <i class="fa-solid fa-arrows-left-right text-[10px]"></i>
                        Gap: {{ $capa->gap }}
                    </span>
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider border
                        {{ $risk == 'High' ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : '' }}
                        {{ $risk == 'Medium' ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : '' }}
                        {{ $risk == 'Low' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : '' }}
                    ">
                        {{ $risk }}
                    </span>
                </div>

            </div>

        </div>
    </div>

    {{-- Main 2-Column Grid: LEFT (READ-ONLY INITIAL INPUT) vs RIGHT (EDITABLE IMPROVEMENT FORM) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: INITIAL USER INPUT DATA (READ-ONLY) --}}
        <div class="lg:col-span-5 space-y-6">
            
            <div class="bg-slate-100/80 rounded-3xl border border-slate-200 p-6 shadow-xs space-y-5">
                
                {{-- Section Header --}}
                <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-slate-800 text-slate-200 flex items-center justify-center text-xs font-bold shadow-sm">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ __('Initial User Input Data') }}</h2>
                            <p class="text-[11px] text-slate-500 font-medium">{{ __('Initial assessment baseline data (Read-Only)') }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-200 text-slate-700 border border-slate-300">
                        Read-Only
                    </span>
                </div>

                {{-- Initial Assessment Likert Score Card --}}
                <div class="p-4 bg-white rounded-2xl border border-slate-200 space-y-2 shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">{{ __('Initial Assessment Likert Score') }}</span>
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">Initial Score:</span>
                            <span class="text-sm font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-xl border border-slate-200">Score {{ $capa->maturity_rating ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">Target Score:</span>
                            <span class="text-sm font-black text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl border border-indigo-200">Score 5</span>
                        </div>
                    </div>
                </div>

                {{-- Initial Audit Finding Notes --}}
                <div class="p-4 bg-white rounded-2xl border border-slate-200 space-y-1.5 shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">{{ __('Initial Inspection / Finding Notes') }}</span>
                    <p class="text-xs text-slate-700 font-medium leading-relaxed italic bg-slate-50 p-3 rounded-xl border border-slate-100">
                        "{{ $capa->notes ?: __('No specific notes provided during initial assessment.') }}"
                    </p>
                </div>

                {{-- Initial Evidence File --}}
                <div class="p-4 bg-white rounded-2xl border border-slate-200 space-y-1.5 shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">{{ __('Initial Evidence File (Uploaded Evidence)') }}</span>
                    @if($capa->evidence_file)
                        <div class="flex items-center justify-between gap-2 p-2.5 bg-indigo-50/60 rounded-xl border border-indigo-100">
                            <div class="flex items-center gap-2 truncate">
                                <i class="fa-solid fa-file-arrow-down text-indigo-600 text-sm"></i>
                                <span class="text-xs font-bold text-indigo-900 truncate">{{ basename($capa->evidence_file) }}</span>
                            </div>
                            <a href="{{ Storage::url($capa->evidence_file) }}" target="_blank" 
                               class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-bold shrink-0 transition-colors shadow-xs">
                                <i class="fa-solid fa-eye"></i> {{ __('View Evidence') }}
                            </a>
                        </div>
                    @else
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-400 italic">
                            <i class="fa-solid fa-circle-info mr-1"></i> {{ __('No supporting evidence uploaded during initial assessment.') }}
                        </div>
                    @endif
                </div>

                {{-- AI Recommendation Box (Read-Only Context) --}}
                @if($capa->ai_recommendation)
                <div class="p-4 bg-emerald-50/90 border border-emerald-200 rounded-2xl space-y-2 shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-emerald-600 text-xs"></i>
                        <span class="text-[10px] font-black text-emerald-950 uppercase tracking-wider">{{ __('AI Recommendation') }}</span>
                    </div>
                    <div class="text-xs font-medium text-emerald-900 leading-relaxed bg-white/80 p-3.5 rounded-xl border border-emerald-100 whitespace-pre-line text-left">{{ trim($capa->ai_recommendation) }}</div>
                </div>
                @endif

            </div>

            {{-- Audit Trail History --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ __('Audit Trail (Improvement Modification Timeline)') }}</h3>
                        <p class="text-[11px] text-slate-400 font-medium">{{ __('History log of remediation updates') }}</p>
                    </div>
                </div>

                <div class="space-y-3.5 max-h-72 overflow-y-auto pr-1 custom-scrollbar">
                    @forelse($history as $log)
                        <div class="relative pl-5 pb-3.5 last:pb-0 border-l border-slate-200/80 last:border-transparent">
                            <span class="absolute left-0 top-1 -translate-x-1/2 w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <div class="text-[11px]">
                                <span class="font-bold text-slate-900">{{ $log->user->name ?? 'System' }}</span>
                                <span class="text-slate-400 font-medium ml-1.5 text-[10px]">{{ $log->created_at->format('d M H:i') }}</span>
                            </div>
                            <p class="text-[10px] font-semibold text-slate-500 mt-0.5 uppercase tracking-wider">
                                Field: <strong class="text-slate-800">{{ friendly_field_label($log->field_changed) }} ({{ str_replace('_', ' ', $log->field_changed) }})</strong>
                            </p>
                            <div class="grid grid-cols-2 gap-2 mt-1 p-2 bg-slate-50 rounded-xl border border-slate-100 text-[10px]">
                                <div>
                                    <span class="text-slate-400 block font-semibold text-[8px] uppercase">Old Value</span>
                                    <span class="text-slate-600 font-medium italic truncate block">{{ $log->old_value ?: 'None' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block font-semibold text-[8px] uppercase">New Value</span>
                                    <span class="text-slate-900 font-bold truncate block">{{ $log->new_value ?: 'None' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <i class="fa-solid fa-history text-2xl mb-1.5 text-slate-300 block"></i>
                            <p class="text-[11px] font-medium">{{ __('No change history recorded yet.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: REMEDIATION & ACTION PLAN INPUT (EDITABLE FORM) --}}
        <div class="lg:col-span-7">
            <form method="POST" action="{{ route('admin.capa.update', $capa) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Header Card for Remediation Input --}}
                <div class="bg-gradient-to-r from-indigo-500 to-blue-600 text-white p-5 rounded-3xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-white/20 text-white flex items-center justify-center text-sm font-bold border border-white/20">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black tracking-tight">{{ __('CAPA Remediation & Progress Update') }}</h2>
                            <p class="text-[11px] text-indigo-100 font-medium">{{ __('Active form to submit assignment, status, likert score, and post-improvement evidence') }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30">
                        Editable
                    </span>
                </div>

                {{-- Card 1: Remediation Assignment & Schedule --}}
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ __('1. Remediation Assignment & Target Schedule') }}</h3>
                            <p class="text-xs text-slate-400 font-medium">{{ __('Assign responsible person (PIC) and target completion date') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Assigned Remediation User (PIC Dropdown) --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('Remediation Person in Charge (PIC)') }}
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-user-gear absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <select name="treatment_pic" 
                                        class="w-full pl-10 pr-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer">
                                    <option value="">-- Select Invited PIC --</option>
                                    @php
                                        $selectedPic = old('treatment_pic', $capa->treatment_pic);
                                        $foundSelected = false;
                                    @endphp
                                    @foreach($sessionUsers as $sUser)
                                        @php
                                            $isSelected = ($selectedPic === $sUser->name || $selectedPic === $sUser->email);
                                            if ($isSelected) $foundSelected = true;
                                        @endphp
                                        <option value="{{ $sUser->name }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $sUser->name }} ({{ $sUser->email }})
                                        </option>
                                    @endforeach
                                    @if($selectedPic && !$foundSelected)
                                        <option value="{{ $selectedPic }}" selected>{{ $selectedPic }}</option>
                                    @endif
                                </select>
                            </div>
                            @error('treatment_pic') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Target Due Date --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('Target Completion Date (Due Date)') }}
                            </label>
                            <input type="date" name="treatment_due_date" 
                                   value="{{ old('treatment_due_date', $capa->treatment_due_date ? $capa->treatment_due_date->format('Y-m-d') : '') }}" 
                                   class="w-full px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            @error('treatment_due_date') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Progress Input & Likert Scale Re-Evaluation --}}
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ __('2. Progress Input & Likert Scale Re-Evaluation') }}</h3>
                            <p class="text-xs text-slate-400 font-medium">{{ __('Update status, progress percentage, post-improvement evidence, and re-evaluate likert score') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Treatment Status --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('Remediation Status') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="treatment_status" required
                                    class="w-full px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer">
                                <option value="open" {{ old('treatment_status', $capa->treatment_status ?: 'open') == 'open' ? 'selected' : '' }}>{{ __('Open (Not Started)') }}</option>
                                <option value="in_progress" {{ old('treatment_status', $capa->treatment_status) == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                <option value="completed" {{ old('treatment_status', $capa->treatment_status) == 'completed' ? 'selected' : '' }}>{{ __('Completed (Verified)') }}</option>
                            </select>
                            @error('treatment_status') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Progress Percentage --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('Progress (%)') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="treatment_progress" required
                                    class="w-full px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer">
                                @for ($i = 0; $i <= 100; $i += 10)
                                    <option value="{{ $i }}" {{ old('treatment_progress', $capa->treatment_progress ?? 0) == $i ? 'selected' : '' }}>{{ $i }}%</option>
                                @endfor
                            </select>
                            @error('treatment_progress') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Re-evaluated Likert Scale Rating --}}
                        <div class="space-y-1.5" x-data="{ 
                            open: false, 
                            selected: {{ old('maturity_rating', $capa->maturity_rating ?? 0) }},
                            options: {
                                0: 'Score 0: Control not implemented',
                                1: 'Score 1: Control planning underway but not consistently implemented',
                                2: 'Score 2: Control implemented in partial processes',
                                3: 'Score 3: Control implemented according to established procedures',
                                4: 'Score 4: Control consistently implemented and effectiveness monitored',
                                5: 'Score 5: Control optimally implemented and supported by continual improvement'
                            }
                        }">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('Re-evaluated Likert Scale') }}
                            </label>
                            
                            <input type="hidden" name="maturity_rating" :value="selected">

                            <div class="relative">
                                <!-- Trigger Button: Displays ONLY 'Score X' when closed -->
                                <button type="button" 
                                        @click="open = !open" 
                                        @click.outside="open = false"
                                        class="w-full px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-bold text-indigo-900 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all flex items-center justify-between cursor-pointer">
                                    <span x-text="'Score ' + selected"></span>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </button>

                                <!-- Dropdown Menu: Floating Popover Overlay (z-50) so page width is unaffected -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute z-50 right-0 sm:-left-32 sm:-right-32 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden py-1 max-w-[95vw] sm:max-w-md">
                                    <template x-for="(desc, lvl) in options" :key="lvl">
                                        <button type="button" 
                                                @click="selected = parseInt(lvl); open = false"
                                                :class="selected == parseInt(lvl) ? 'bg-indigo-50 text-indigo-900 font-black' : 'text-slate-700 font-medium hover:bg-slate-50 hover:text-slate-900'"
                                                class="w-full px-4 py-3 text-left text-xs transition-colors flex items-start justify-between gap-3 border-b border-slate-100 last:border-none">
                                            <span class="leading-relaxed" x-text="desc"></span>
                                            <i x-show="selected == parseInt(lvl)" class="fa-solid fa-check text-indigo-600 text-xs shrink-0 mt-0.5"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <p class="text-[9px] text-slate-400">{{ __('Increasing to Likert Score 4/5 will automatically close this control gap.') }}</p>
                            @error('maturity_rating') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Post-Improvement Evidence Output & Document Upload --}}
                        <div class="md:col-span-3 space-y-3 pt-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('Post-Improvement Evidence & Document Upload') }}
                            </label>

                            @if($capa->evidence_after_improvement)
                                @php
                                    $hasDocTag = str_contains($capa->evidence_after_improvement, '[Document]');
                                    $docPath = '';
                                    if ($hasDocTag) {
                                        preg_match('/\[Document\]\s*([^\s\n]+)/', $capa->evidence_after_improvement, $matches);
                                        $docPath = trim($matches[1] ?? '');
                                    }
                                @endphp
                                @if($docPath)
                                    <div class="p-3 bg-emerald-50/80 rounded-2xl border border-emerald-200 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2 truncate">
                                            <i class="fa-solid fa-file-circle-check text-emerald-600 text-base"></i>
                                            <span class="text-xs font-bold text-emerald-900 truncate">{{ basename($docPath) }}</span>
                                        </div>
                                        <a href="{{ Storage::url($docPath) }}" target="_blank" 
                                           class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shrink-0 transition-colors shadow-xs flex items-center gap-1.5">
                                            <i class="fa-solid fa-download text-[10px]"></i> {{ __('View Document') }}
                                        </a>
                                    </div>
                                @endif
                            @endif

                            {{-- File Attachment Input (Add Document) --}}
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block"><i class="fa-solid fa-paperclip text-indigo-500 mr-1"></i> {{ __('Add Document / Evidence File') }}</span>
                                <input type="file" name="evidence_after_file" 
                                       accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip,.rar,.xlsx,.xls" 
                                       class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 bg-slate-50/70 border border-slate-200 rounded-2xl p-1.5 cursor-pointer transition-all">
                                <p class="text-[9px] text-slate-400">{{ __('Upload verification file (PDF, DOCX, Images, ZIP, Excel - Max 10MB)') }}</p>
                                @error('evidence_after_file') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Text Notes Input --}}
                            <div class="space-y-1 pt-1">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">{{ __('Post-Improvement Notes / Verification Details') }}</span>
                                <textarea name="evidence_after_improvement" rows="3" 
                                          placeholder="{{ __('Enter verification notes, link to updated SOP, firewall rule logs, etc...') }}" 
                                          class="w-full px-4 py-3 bg-slate-50/70 border border-slate-200 rounded-2xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all leading-relaxed">{{ old('evidence_after_improvement', trim(preg_replace('/\[Document\]\s*[^\s\n]+/', '', $capa->evidence_after_improvement))) }}</textarea>
                                @error('evidence_after_improvement') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.capa.index') }}" 
                       class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-all text-xs">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all hover:scale-[1.02] active:scale-95 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('Save CAPA Remediation Data') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
