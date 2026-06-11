@extends('layouts.admin')

@section('title', 'System Logs')
@section('header_title', 'System Logs & Audit Trail')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-black text-slate-800">System Logs / Audit Trail</h2>
    <p class="text-sm text-slate-500">Track data changes, updates, and activities across the system.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('admin.logs.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search field, value, or user..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            
            <select name="user_id" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>

            <select name="action" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Actions</option>
                <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>

            <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">

            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'user_id', 'action', 'date']))
                <a href="{{ route('admin.logs.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center justify-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Timestamp</th>
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Action</th>
                    <th class="px-6 py-4">Target Model</th>
                    <th class="px-6 py-4">Field</th>
                    <th class="px-6 py-4">Old Value</th>
                    <th class="px-6 py-4">New Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                        {{ $log->created_at->format('d M Y H:i:s') }}
                    </td>
                    <td class="px-6 py-4 font-bold text-slate-800">
                        {{ $log->user ? $log->user->name : 'System' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                            {{ $log->action == 'created' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $log->action == 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $log->action == 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs font-mono text-slate-500">
                        {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                    </td>
                    <td class="px-6 py-4 font-semibold text-slate-700">
                        {{ $log->field_changed }}
                    </td>
                    <td class="px-6 py-4 text-xs max-w-[150px] truncate" title="{{ $log->old_value }}">
                        {{ $log->old_value ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-xs max-w-[150px] truncate text-indigo-600 font-medium" title="{{ $log->new_value }}">
                        {{ $log->new_value ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-clock text-4xl mb-4 text-slate-300"></i>
                        <p>No audit trail logs found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
