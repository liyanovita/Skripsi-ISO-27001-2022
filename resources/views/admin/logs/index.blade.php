@extends('layouts.admin')

@section('title', 'Audit Trail')
@section('header_title', 'Audit Trail')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                System Logs
            </h2>
            <p class="text-sm text-slate-500 mt-0.5 ml-9">History of changes, assessment activities, and user events across the platform.</p>
        </div>
        <a href="{{ route('admin.logs.export', request()->query()) }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 active:scale-95 transition-all shadow-md shadow-emerald-600/20 shrink-0">
            <i class="fa-solid fa-file-excel text-xs"></i> Export Excel
        </a>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Events</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalLogs) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Recorded system log entries</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Changes Today</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-day text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-amber-600 tracking-tight">{{ number_format($logsToday) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Activities in past 24 hours</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Unique Actors</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-users text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-indigo-600 tracking-tight">{{ number_format($activeUsers) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Users contributing updates</div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Filter Bar --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white">
            <form method="GET" action="{{ route('admin.logs.index') }}" x-data class="flex flex-col lg:flex-row gap-3 items-center">
                <div class="flex-1 relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        x-on:input.debounce.400ms="$el.closest('form').requestSubmit()"
                        placeholder="Search field, value, target, or user…"
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-amber-400 transition-all bg-white">
                </div>
                <div class="flex items-center gap-2 flex-wrap w-full lg:w-auto">
                    <select name="user_id" x-on:change="$el.closest('form').requestSubmit()"
                        class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none bg-white font-semibold text-slate-600 cursor-pointer">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="action" x-on:change="$el.closest('form').requestSubmit()"
                        class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none bg-white font-semibold text-slate-600 cursor-pointer">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>

                    <input type="date" name="date" value="{{ request('date') }}"
                        x-on:change="$el.closest('form').requestSubmit()"
                        class="px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none bg-white font-semibold text-slate-600 cursor-pointer">

                    @if(request()->hasAny(['search', 'user_id', 'action', 'date']))
                        <a href="{{ route('admin.logs.index') }}"
                            class="px-3.5 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-sm font-bold hover:bg-slate-200 transition-colors flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-xmark text-xs"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Timestamp</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Actor</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Target</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Field Changed</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Old Value</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">New Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                    @php
                        $modelLabel = class_basename($log->model_type);
                        $modelMap = [
                            'AssessmentResult'  => 'CAPA / Audit Result',
                            'AssessmentSession' => 'Audit Session',
                            'KnowledgeBase'     => 'Knowledge Base',
                            'IsoStandard'       => 'ISO Standard',
                            'User'              => 'User',
                        ];
                        $modelLabel = $modelMap[$modelLabel] ?? $modelLabel;

                        $actionBadge = match($log->action) {
                            'created' => 'bg-emerald-50 text-emerald-700 border-emerald-100 fa-plus',
                            'updated' => 'bg-blue-50 text-blue-700 border-blue-100 fa-pen',
                            'deleted' => 'bg-rose-50 text-rose-700 border-rose-100 fa-trash-can',
                            default => 'bg-slate-100 text-slate-600 border-slate-200 fa-circle-dot',
                        };

                        $booleanFields = ['is_applicable'];
                        $isBoolLog = in_array($log->field_changed, $booleanFields);
                        $oldLogDisplay = (!is_null($log->old_value) && $log->old_value !== '')
                            ? ($isBoolLog ? ($log->old_value == '1' ? 'Yes' : 'No') : $log->old_value)
                            : null;
                        $newLogDisplay = (!is_null($log->new_value) && $log->new_value !== '')
                            ? ($isBoolLog ? ($log->new_value == '1' ? 'Yes' : 'No') : $log->new_value)
                            : null;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        {{-- Timestamp --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-bold text-slate-800 text-xs">{{ $log->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>

                        {{-- Actor --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 text-[10px] font-black shrink-0">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-800 text-xs truncate max-w-[120px]">
                                    {{ $log->user?->name ?? 'System' }}
                                </span>
                            </div>
                        </td>

                        {{-- Action --}}
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ explode(' ', $actionBadge)[0] }} {{ explode(' ', $actionBadge)[1] }} {{ explode(' ', $actionBadge)[2] }}">
                                <i class="fa-solid {{ explode(' ', $actionBadge)[3] }} text-[8px]"></i>
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>

                        {{-- Target --}}
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-800 text-xs">{{ $modelLabel }}</div>
                            <div class="text-[10px] font-mono text-slate-400">#{{ $log->model_id }}</div>
                        </td>

                        {{-- Field Changed --}}
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-xs font-bold border border-indigo-100">
                                {{ friendly_field_label($log->field_changed) }}
                            </span>
                        </td>

                        {{-- Old Value --}}
                        <td class="px-5 py-3.5 text-xs">
                            @if(is_null($oldLogDisplay))
                                <span class="text-slate-300 italic">—</span>
                            @elseif($isBoolLog)
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $oldLogDisplay === 'Yes' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $oldLogDisplay }}
                                </span>
                            @else
                                <div class="max-w-[140px] truncate bg-rose-50/80 text-rose-700 border border-rose-100/80 px-2 py-0.5 rounded-lg text-xs font-medium" title="{{ $oldLogDisplay }}">
                                    {{ $oldLogDisplay }}
                                </div>
                            @endif
                        </td>

                        {{-- New Value --}}
                        <td class="px-5 py-3.5 text-xs">
                            @if(is_null($newLogDisplay))
                                <span class="text-slate-300 italic">—</span>
                            @elseif($isBoolLog)
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $newLogDisplay === 'Yes' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $newLogDisplay }}
                                </span>
                            @else
                                <div class="max-w-[140px] truncate bg-emerald-50/80 text-emerald-700 border border-emerald-100/80 px-2 py-0.5 rounded-lg text-xs font-bold" title="{{ $newLogDisplay }}">
                                    {{ $newLogDisplay }}
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-20 text-center">
                            <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-200"></i>
                            </div>
                            <p class="text-slate-500 font-bold text-sm">No Audit Trail Logs Found</p>
                            <p class="text-slate-400 text-xs mt-1">Try adjusting search keywords or filter dates.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
