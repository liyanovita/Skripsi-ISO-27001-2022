@extends('layouts.admin')

@section('title', 'Improvement Tracking')
@section('header_title', 'Improvement Tracking')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">Improvement Tracking</h2>
        <p class="text-sm text-slate-500">Monitor and manage remediation actions across all user audits.</p>
    </div>
    <a href="{{ route('admin.capa.export', array_filter(['status' => request('status'), 'risk' => request('risk'), 'session_id' => request('session_id')])) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-all shadow-sm hover:shadow-md shrink-0">
        <i class="fa-solid fa-file-excel"></i> Export Excel
    </a>
</div>

{{-- Filtered Session Context Banner --}}
@if(isset($filteredSession))
<div class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-between gap-4 flex-wrap">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-clipboard-check text-lg"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Filtering by Session</div>
            <div class="text-sm font-black text-slate-800 mt-1">{{ $filteredSession->name }} (User: {{ $filteredSession->user->name }})</div>
        </div>
    </div>
    <a href="{{ route('admin.sessions.show', $filteredSession) }}" 
       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-50 transition-colors shadow-sm">
        <i class="fa-solid fa-arrow-left"></i> Back to Session Detail
    </a>
</div>
@endif

{{-- Stats Row --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-list-check"></i>
        </div>
        <div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($totalCapa) }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Actions</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-circle-dot"></i>
        </div>
        <div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($openCount) }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Open</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-spinner"></i>
        </div>
        <div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($inProgressCount) }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">In Progress</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($completedCount) }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Completed</div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm col-span-2 lg:col-span-1">
        <div class="w-10 h-10 rounded-lg {{ $overdueCount > 0 ? 'bg-red-100 text-red-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <div class="text-2xl font-black {{ $overdueCount > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ number_format($overdueCount) }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Overdue</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('admin.capa.index') }}" x-data class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                    placeholder="Search Clause/Control, User Name..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <select name="session_id" x-on:change="$el.closest('form').requestSubmit()" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white max-w-xs">
                <option value="">All Sessions</option>
                @foreach($sessions as $sess)
                    <option value="{{ $sess->id }}" {{ request('session_id') == $sess->id ? 'selected' : '' }}>
                        {{ $sess->name }} ({{ $sess->user->name }})
                    </option>
                @endforeach
            </select>
            
            <select name="status" x-on:change="$el.closest('form').requestSubmit()" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Statuses</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <select name="risk" x-on:change="$el.closest('form').requestSubmit()" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Risks</option>
                <option value="High" {{ request('risk') == 'High' ? 'selected' : '' }}>High</option>
                <option value="Medium" {{ request('risk') == 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ request('risk') == 'Low' ? 'selected' : '' }}>Low</option>
            </select>

            @if(request()->hasAny(['search', 'status', 'risk', 'session_id']))
                <a href="{{ route('admin.capa.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center justify-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Session & User</th>
                    <th class="px-6 py-4">Standard / Control</th>
                    <th class="px-6 py-4">Action Plan</th>
                    <th class="px-6 py-4">PIC & Due Date</th>
                    <th class="px-6 py-4">Status & Progress</th>
                    <th class="px-6 py-4">Evidence After Improvement</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($capas as $capa)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800">{{ $capa->session->user->name }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">{{ $capa->session->name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase shrink-0">
                                {{ $capa->standard->code }}
                            </span>
                            <span class="font-medium text-slate-700 line-clamp-1" title="{{ $capa->standard->title }}">{{ $capa->standard->title }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest
                                {{ $capa->risk_priority == 'Critical' ? 'bg-rose-100 text-rose-700' : '' }}
                                {{ $capa->risk_priority == 'High' ? 'bg-orange-100 text-orange-700' : '' }}
                                {{ $capa->risk_priority == 'Medium' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $capa->risk_priority == 'Low' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ !$capa->risk_priority ? 'bg-slate-100 text-slate-700' : '' }}
                            ">
                                {{ $capa->risk_priority ?: 'Low' }}
                            </span>
                            @if($capa->ai_recommendation)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 cursor-help" title="{{ $capa->ai_recommendation }}">
                                    <i class="fa-solid fa-wand-magic-sparkles text-[8px]"></i> Rec
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $planData = $capa->corrective_action_plan ?: [];
                            $actionText = is_array($planData) ? ($planData['action'] ?? '-') : ($planData ?: '-');
                        @endphp
                        <p class="text-xs text-slate-600 line-clamp-2 max-w-[200px]" title="{{ $actionText }}">{{ $actionText }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800 text-xs">{{ $capa->treatment_pic ?: '-' }}</div>
                        <div class="mt-0.5">
                            @if($capa->treatment_due_date)
                                @if($capa->treatment_due_date->isPast() && $capa->treatment_status != 'completed')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-600">
                                        <i class="fa-solid fa-circle-exclamation"></i> Overdue ({{ $capa->treatment_due_date->format('d M Y') }})
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[10px] font-medium">{{ $capa->treatment_due_date->format('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-slate-400 italic text-[10px]">No due date</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $status = $capa->treatment_status ?: 'open';
                            $progress = $capa->treatment_progress ?? 0;
                        @endphp
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                {{ $status == 'completed' ? 'bg-green-50 text-green-700 border border-green-200' : '' }}
                                {{ $status == 'in_progress' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                {{ $status == 'open' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                            ">
                                {{ ucfirst($status) }}
                            </span>
                            <span class="text-xs font-black text-slate-700">{{ $progress }}%</span>
                        </div>
                        <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-xs text-slate-500 line-clamp-2 max-w-[180px] italic" title="{{ $capa->evidence_after_improvement ?: 'No evidence logged yet.' }}">
                            {{ $capa->evidence_after_improvement ?: '-' }}
                        </p>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.capa.edit', $capa) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all hover:shadow-sm">
                            <i class="fa-solid fa-pen-to-square"></i> Manage
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <i class="fa-solid fa-circle-check text-4xl mb-4 text-slate-300 block"></i>
                        <p class="text-slate-500 font-medium">No Improvement actions found matching the filters.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($capas->hasPages())
    <div class="p-4 border-t border-slate-200 bg-slate-50">
        {{ $capas->links() }}
    </div>
    @endif
</div>
@endsection
