@extends('layouts.admin')

@section('title', 'Audit Sessions')
@section('header_title', 'All Audit Sessions')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Header --}}
    <div class="p-5 border-b border-slate-200">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-slate-800">All Audit Sessions</h3>
                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-full">{{ $sessions->total() }} total</span>
            </div>
            <form method="GET" action="{{ route('admin.sessions.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                <div class="relative flex-1 sm:w-56">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search session or user..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                </div>
                <select name="status" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Session Name</th>
                    <th class="px-6 py-4">Owner</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Controls</th>
                    <th class="px-6 py-4">Maturity Score</th>
                    <th class="px-6 py-4">Last Updated</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sessions as $session)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.sessions.show', $session) }}" class="font-bold text-slate-900 hover:text-blue-600 transition-colors">
                            {{ $session->name }}
                        </a>
                        <div class="text-xs text-slate-400 mt-0.5">Created {{ $session->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.users.show', $session->user_id) }}" class="flex items-center gap-2 hover:opacity-80">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 text-blue-700 flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                {{ strtoupper(substr($session->user->name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-medium text-slate-800 text-xs">{{ $session->user->name ?? 'Unknown' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $session->user->organization_name ?? '' }}</div>
                            </div>
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-widest
                            {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($session->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ str_replace('_', ' ', $session->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-700">{{ $session->results_count }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $session->overall_maturity_score >= 4 ? 'bg-emerald-500' : ($session->overall_maturity_score >= 2.5 ? 'bg-amber-500' : 'bg-red-500') }}"
                                     style="width: {{ ($session->overall_maturity_score / 5) * 100 }}%"></div>
                            </div>
                            <span class="font-bold text-sm {{ $session->overall_maturity_score >= 4 ? 'text-emerald-600' : ($session->overall_maturity_score >= 2.5 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ number_format($session->overall_maturity_score, 1) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500">
                        {{ $session->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2" x-data="{ showDelete: false }">
                            <a href="{{ route('admin.sessions.show', $session) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 bg-white border border-blue-200 transition-colors" title="View Detail">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <button @click="showDelete = true" x-show="!showDelete" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50 bg-white border border-red-200 transition-colors" title="Delete">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}" x-show="showDelete" class="flex gap-1" x-cloak>
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">Confirm</button>
                                <button type="button" @click="showDelete = false" class="px-3 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded hover:bg-slate-300">Cancel</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-clipboard text-3xl mb-3 text-slate-300"></i>
                        <p>No audit sessions found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $sessions->links() }}
    </div>
    @endif
</div>
@endsection
