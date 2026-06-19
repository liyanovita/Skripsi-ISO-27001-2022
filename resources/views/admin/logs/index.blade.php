@extends('layouts.admin')

@section('title', 'System Logs')
@section('header_title', 'System Logs & Audit Trail')

@section('content')

{{-- Page Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">System Logs / Audit Trail</h2>
        <p class="text-sm text-slate-500">Track every data change, update, and activity across the system.</p>
    </div>
    <a href="{{ route('admin.logs.export') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2 self-start">
        <i class="fa-solid fa-file-csv"></i> Export CSV
    </a>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-lg shrink-0">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Events</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalLogs) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-lg shrink-0">
            <i class="fa-solid fa-calendar-day"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Changes Today</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($logsToday) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-lg shrink-0">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Unique Actors</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($activeUsers) }}</span>
        </div>
    </div>
</div>

{{-- Filter Bar + Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Filters --}}
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('admin.logs.index') }}" class="flex flex-col sm:flex-row gap-3 flex-wrap">
            <div class="flex-1 min-w-[200px] relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search field, value, or user..."
                    class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <select name="user_id" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>

            <select name="action" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Actions</option>
                <option value="created"  {{ request('action') == 'created'  ? 'selected' : '' }}>Created</option>
                <option value="updated"  {{ request('action') == 'updated'  ? 'selected' : '' }}>Updated</option>
                <option value="deleted"  {{ request('action') == 'deleted'  ? 'selected' : '' }}>Deleted</option>
            </select>

            <input type="date" name="date" value="{{ request('date') }}"
                class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">

            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>

            @if(request()->hasAny(['search', 'user_id', 'action', 'date']))
                <a href="{{ route('admin.logs.index') }}"
                    class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center justify-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-4">Timestamp</th>
                    <th class="px-5 py-4">Actor</th>
                    <th class="px-5 py-4">Action</th>
                    <th class="px-5 py-4">Target</th>
                    <th class="px-5 py-4">Field Changed</th>
                    <th class="px-5 py-4">Old Value</th>
                    <th class="px-5 py-4">New Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                @php
                    $modelLabel = class_basename($log->model_type);
                    // Map raw class names to human-readable labels
                    $modelMap = [
                        'AssessmentResult'  => 'CAPA / Audit Result',
                        'AssessmentSession' => 'Audit Session',
                        'KnowledgeBase'     => 'Knowledge Base',
                        'IsoStandard'       => 'ISO Standard',
                        'User'              => 'User',
                    ];
                    $modelLabel = $modelMap[$modelLabel] ?? $modelLabel;
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap text-xs">
                        <div class="font-semibold">{{ $log->created_at->format('d M Y') }}</div>
                        <div class="text-slate-400">{{ $log->created_at->format('H:i:s') }}</div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-[10px] font-black shrink-0">
                                {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="font-semibold text-slate-800 text-xs">
                                {{ $log->user?->name ?? 'System' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                            {{ $log->action == 'created' ? 'bg-green-100 text-green-700'  : '' }}
                            {{ $log->action == 'updated' ? 'bg-blue-100 text-blue-700'   : '' }}
                            {{ $log->action == 'deleted' ? 'bg-red-100 text-red-700'     : '' }}
                        ">
                            @if($log->action == 'created') <i class="fa-solid fa-plus mr-1 text-[9px]"></i>
                            @elseif($log->action == 'updated') <i class="fa-solid fa-pen mr-1 text-[9px]"></i>
                            @elseif($log->action == 'deleted') <i class="fa-solid fa-trash mr-1 text-[9px]"></i>
                            @endif
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="text-xs font-semibold text-slate-700">{{ $modelLabel }}</div>
                        <div class="text-[10px] font-mono text-slate-400">#{{ $log->model_id }}</div>
                    </td>
                    <td class="px-5 py-3.5 font-semibold text-slate-700 text-xs">
                        {{ $log->field_changed ?? '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-xs max-w-[140px] truncate text-slate-500" title="{{ $log->old_value }}">
                        {{ $log->old_value ?? '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-xs max-w-[140px] truncate font-semibold text-indigo-600" title="{{ $log->new_value }}">
                        {{ $log->new_value ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <i class="fa-solid fa-clock-rotate-left text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-500 font-semibold">No audit trail logs found.</p>
                        <p class="text-slate-400 text-xs mt-1">Try adjusting your filters or check back later.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
