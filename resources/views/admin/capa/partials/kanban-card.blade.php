@php
    $matInfo = \App\Models\AssessmentSession::getMaturityLevelClassification((float)($capa->maturity_rating ?? 0));
    $risk = $capa->calculated_risk_priority;
    $planData = $capa->corrective_action_plan ?: [];
    $actionText = is_array($planData) ? ($planData['action'] ?? '-') : ($planData ?: '-');
    $assignedUser = $capa->treatment_pic ?: 'Unassigned';
    $sessionLead = $capa->session->user->name ?? 'Unknown';
    $progress = $capa->treatment_progress ?? 0;
    $isOverdue = $capa->treatment_due_date && $capa->treatment_due_date->isPast() && $capa->treatment_status != 'completed';
@endphp

<div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all space-y-3.5 relative group">
    {{-- Card Header: Badges --}}
    <div class="flex items-center justify-between gap-2 flex-wrap border-b border-slate-100 pb-3">
        <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-black text-xs shadow-xs">
            {{ $capa->standard->code }}
        </span>
        <div class="flex items-center gap-1.5 flex-wrap">
            @php
                $compStatus = $capa->compliance_status;
                $compBadgeClass = match($compStatus) {
                    'Compliant' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'Partially Compliant' => 'bg-amber-50 text-amber-700 border-amber-200',
                    default => 'bg-rose-50 text-rose-700 border-rose-200'
                };
            @endphp
            {{-- Compliance Status --}}
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase border {{ $compBadgeClass }}">
                {{ $compStatus }}
            </span>

            {{-- Maturity Level Badge --}}
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold border {{ $matInfo['badge_color'] }}">
                <i class="fa-solid fa-chart-line text-[8px]"></i> Level {{ $matInfo['level'] }}
            </span>

            {{-- Gap Value --}}
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold border bg-indigo-50 text-indigo-700 border-indigo-200">
                Gap: {{ $capa->gap }}
            </span>

            {{-- Risk Priority --}}
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border
                {{ $risk == 'High' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                {{ $risk == 'Medium' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                {{ $risk == 'Low' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
            ">
                {{ $risk }}
            </span>
        </div>
    </div>

    {{-- Control Title --}}
    <div>
        <h3 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2" title="{{ $capa->standard->title }}">
            {{ $capa->standard->title }}
        </h3>
        <p class="text-[10px] font-medium text-slate-400 mt-1 flex items-center gap-1 truncate">
            <i class="fa-solid fa-folder text-slate-300"></i> {{ $capa->session->name }}
        </p>
    </div>

    {{-- People Roles: Session Lead & Assigned User --}}
    <div class="p-3 bg-slate-50/70 rounded-2xl border border-slate-100 space-y-1.5 text-[10px]">
        <div class="flex items-center justify-between text-slate-500 font-semibold">
            <span class="flex items-center gap-1"><i class="fa-solid fa-user-tie text-slate-400"></i> Assessed By:</span>
            <strong class="text-slate-800 truncate max-w-[120px]">{{ $sessionLead }}</strong>
        </div>
        <div class="flex items-center justify-between text-blue-700 font-bold border-t border-slate-200/60 pt-1.5">
            <span class="flex items-center gap-1"><i class="fa-solid fa-user-gear text-blue-500"></i> Assigned User:</span>
            <span class="bg-blue-100/80 px-2 py-0.5 rounded-md text-blue-900 font-black truncate max-w-[120px]">{{ $assignedUser }}</span>
        </div>
    </div>

    {{-- Action Plan Description Box --}}
    <div class="space-y-1">
        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">{{ __('Corrective Action Plan') }}</span>
        <p class="text-xs text-slate-700 font-medium leading-relaxed bg-blue-50/40 p-3 rounded-2xl border border-blue-100/60 line-clamp-3">
            {{ $actionText }}
        </p>
    </div>

    {{-- Progress & Target Due Date --}}
    <div class="space-y-1.5 pt-1">
        <div class="flex items-center justify-between text-xs font-black">
            <span class="text-slate-500 text-[10px] uppercase font-bold tracking-wider">{{ __('Remediation Progress') }}</span>
            <span class="text-slate-900">{{ $progress }}%</span>
        </div>
        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden shadow-inner">
            <div class="h-full rounded-full transition-all duration-500 {{ $columnStatus == 'completed' ? 'bg-emerald-500' : ($columnStatus == 'in_progress' ? 'bg-blue-500 animate-pulse' : 'bg-rose-500') }}"
                 style="width: {{ $progress }}%"></div>
        </div>

        <div class="flex items-center justify-between gap-2 pt-1 text-[10px]">
            @if($isOverdue)
                <span class="inline-flex items-center gap-1 font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                    <i class="fa-solid fa-clock-rotate-left"></i> Overdue ({{ $capa->treatment_due_date->format('d M Y') }})
                </span>
            @elseif($capa->treatment_due_date)
                <span class="text-slate-400 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-calendar text-slate-300"></i> Due: {{ $capa->treatment_due_date->format('d M Y') }}
                </span>
            @else
                <span class="text-slate-400 italic">No due date set</span>
            @endif

            @if(!empty($capa->evidence_after_improvement))
                <span class="text-emerald-600 font-bold flex items-center gap-1"><i class="fa-solid fa-file-circle-check"></i> Evidence Verified</span>
            @endif
        </div>
    </div>

    {{-- Card Footer Action Button --}}
    <div class="pt-2 border-t border-slate-100">
        <a href="{{ route('admin.capa.edit', $capa) }}" 
           class="w-full inline-flex items-center justify-center gap-2 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm group-hover:shadow-md active:scale-95">
            <i class="fa-solid fa-pen-to-square text-[10px]"></i> {{ __('Manage CAPA') }}
        </a>
    </div>
</div>
