@extends('layouts.admin')

@section('title', 'Audit Sessions')
@section('header_title', 'All Audit Sessions')

@section('content')

{{-- Page Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">Audit Sessions</h2>
        <p class="text-sm text-slate-500">Monitor, inspect, and manage all user audit sessions across the platform.</p>
    </div>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-lg shrink-0">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Sessions</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalSessions) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-lg shrink-0">
            <i class="fa-solid fa-spinner"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">In Progress</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($activeSessions) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-lg shrink-0">
            <i class="fa-solid fa-check-double"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Completed</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($completedSessions) }}</span>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

    {{-- Toolbar --}}
    <div class="p-5 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('admin.sessions.index') }}"
              class="flex flex-col gap-3">
            {{-- Row 1: Search + Status + Filter Button --}}
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-xs font-bold text-slate-400 bg-white border border-slate-200 px-2.5 py-1 rounded-full shrink-0">
                    {{ $sessions->total() }} sessions
                </span>
                <div class="relative flex-1 min-w-[180px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search session or user..."
                        class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                </div>
                <select name="status" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white font-semibold text-slate-700">
                    <option value="">All Status</option>
                    <option value="draft"       {{ $statusFilter === 'draft'       ? 'selected' : '' }}>Draft</option>
                    <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed"   {{ $statusFilter === 'completed'   ? 'selected' : '' }}>Completed</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                @if($search || $statusFilter || $dateFrom || $dateTo)
                <a href="{{ route('admin.sessions.index') }}"
                    class="px-3 py-2 bg-rose-50 text-rose-600 rounded-lg text-sm font-bold hover:bg-rose-100 border border-rose-200 transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-xs"></i> Clear All
                </a>
                @endif
            </div>
            {{-- Row 2: Date Range --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-slate-500 shrink-0">Date Range:</span>
                <div class="relative">
                    <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white text-slate-700"
                        placeholder="From">
                </div>
                <span class="text-slate-400 text-sm font-bold shrink-0">—</span>
                <div class="relative">
                    <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white text-slate-700"
                        placeholder="To">
                </div>
                @if($dateFrom || $dateTo)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-[10px] font-bold border border-blue-200">
                    <i class="fa-solid fa-calendar-check text-[9px]"></i>
                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : '...' }}
                    &mdash;
                    {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : '...' }}
                </span>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-4">Session Name</th>
                    <th class="px-5 py-4">Owner</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Controls</th>
                    <th class="px-5 py-4">Maturity Score</th>
                    <th class="px-5 py-4">Last Updated</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sessions as $session)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.sessions.show', $session) }}"
                            class="font-bold text-slate-900 hover:text-blue-600 transition-colors">
                            {{ $session->name }}
                        </a>
                        <div class="text-xs text-slate-400 mt-0.5">Created {{ $session->created_at->format('d M Y') }}</div>
                    </td>
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.users.show', $session->user_id) }}" class="flex items-center gap-2 hover:opacity-80">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 text-blue-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                                {{ strtoupper(substr($session->user->name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800 text-xs">{{ $session->user->name ?? 'Unknown' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $session->user->organization_name ?? '' }}</div>
                            </div>
                        </a>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest
                            {{ $session->status === 'completed'   ? 'bg-emerald-100 text-emerald-700' :
                               ($session->status === 'in_progress' ? 'bg-amber-100 text-amber-700'   : 'bg-slate-100 text-slate-600') }}">
                            @if($session->status === 'completed') <i class="fa-solid fa-check mr-1 text-[8px]"></i>
                            @elseif($session->status === 'in_progress') <i class="fa-solid fa-spinner mr-1 text-[8px]"></i>
                            @else <i class="fa-solid fa-pen mr-1 text-[8px]"></i>
                            @endif
                            {{ str_replace('_', ' ', $session->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-bold text-slate-700">{{ $session->results_count }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all
                                    {{ $session->overall_maturity_score >= 4 ? 'bg-emerald-500' : ($session->overall_maturity_score >= 2.5 ? 'bg-amber-500' : 'bg-red-500') }}"
                                    style="width: {{ ($session->overall_maturity_score / 5) * 100 }}%">
                                </div>
                            </div>
                            <span class="font-bold text-sm
                                {{ $session->overall_maturity_score >= 4 ? 'text-emerald-600' : ($session->overall_maturity_score >= 2.5 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ number_format($session->overall_maturity_score, 1) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                        {{ $session->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2" x-data="{ showDelete: false }">
                            <a href="{{ route('admin.sessions.show', $session) }}"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 border border-blue-200 bg-white transition-colors"
                                title="View Detail">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <button @click="showDelete = true" x-show="!showDelete"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 border border-red-200 bg-white transition-colors"
                                title="Delete Session">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}"
                                x-show="showDelete" class="flex gap-1" x-cloak>
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-colors">
                                    Confirm
                                </button>
                                <button type="button" @click="showDelete = false"
                                    class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <i class="fa-solid fa-clipboard text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-500 font-semibold">No audit sessions found.</p>
                        <p class="text-slate-400 text-xs mt-1">Try adjusting your search or status filter.</p>
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
