@extends('layouts.admin')

@section('title', 'Audit Sessions')
@section('header_title', 'All Audit Sessions')

@section('content')
<div class="space-y-6 pb-8">

    {{-- Page Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Audit Sessions') }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('Monitor, inspect, and manage all ISO 27001:2022 assessment sessions across the platform') }}</p>
        </div>
        <a href="{{ route('admin.sessions.create') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold tracking-wide shadow-md shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-95 shrink-0 self-start sm:self-auto">
            <i class="fa-solid fa-plus text-xs"></i> {{ __('Launch Audit Session') }}
        </a>
    </div>

    {{-- Balanced Executive KPI Stats Cards Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Total Sessions --}}
        <a href="{{ route('admin.sessions.index') }}" 
           class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-200 transition-all group flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Sessions') }}</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs group-hover:bg-blue-600 group-hover:text-white transition-colors shrink-0 border border-blue-100/60">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalSessions) }}</div>
        </a>

        {{-- Draft --}}
        <a href="{{ route('admin.sessions.index', ['status' => 'draft']) }}" 
           class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all group flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Draft') }}</span>
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-xs group-hover:bg-slate-700 group-hover:text-white transition-colors shrink-0 border border-slate-200/60">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($draftSessions) }}</div>
        </a>

        {{-- Active --}}
        <a href="{{ route('admin.sessions.index', ['status' => 'in_progress']) }}" 
           class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-200 transition-all group flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Active') }}</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs group-hover:bg-blue-600 group-hover:text-white transition-colors shrink-0 border border-blue-100/60">
                    <i class="fa-solid fa-spinner"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($activeSessions) }}</div>
        </a>

        {{-- Completed --}}
        <a href="{{ route('admin.sessions.index', ['status' => 'completed']) }}" 
           class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all group flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Completed') }}</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0 border border-emerald-100/60">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($completedSessions) }}</div>
        </a>

        {{-- Archive --}}
        <a href="{{ route('admin.sessions.index', ['status' => 'archive']) }}" 
           class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-amber-200 transition-all group flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Archived') }}</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs group-hover:bg-amber-600 group-hover:text-white transition-colors shrink-0 border border-amber-100/60">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($archivedSessions) }}</div>
        </a>
    </div>

    {{-- Main Audit Sessions Table Card --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">

        {{-- Filter Toolbar --}}
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.sessions.index') }}" id="sessions-filter-form" 
                  x-data="{ monthVal: '{{ $month ?? '' }}' }"
                  class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 flex-wrap">
                
                {{-- Search Bar --}}
                <div class="flex items-center gap-3 flex-1 min-w-[240px] w-full lg:w-auto">
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}"
                            x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                            placeholder="Search session title, user, or organization..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                    </div>
                </div>

                {{-- Select Filters --}}
                <div class="flex items-center gap-2.5 shrink-0 w-full lg:w-auto justify-end flex-wrap">
                    {{-- Month Picker --}}
                    <div class="relative flex items-center">
                        <i x-on:click="try { $refs.monthInput.showPicker(); } catch(e) {}"
                           class="fa-regular fa-calendar absolute left-3.5 text-slate-400 text-xs cursor-pointer hover:text-blue-600 transition-colors z-10"></i>
                        
                        <span class="absolute left-9 text-slate-400 text-xs font-medium pointer-events-none z-10"
                              x-show="!monthVal">
                            All Months
                        </span>
                        
                        <input type="month" name="month" x-model="monthVal" x-ref="monthInput"
                            x-on:change="$el.closest('form').requestSubmit()"
                            class="pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-xs bg-white font-medium w-36 relative z-0 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer"
                            :class="{ 'text-transparent': !monthVal, 'text-slate-800': monthVal }">
                    </div>

                    {{-- Status Dropdown --}}
                    <select name="status" x-on:change="$el.closest('form').requestSubmit()" 
                            class="px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 cursor-pointer">
                        <option value="">All Status</option>
                        <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="archive" {{ $statusFilter === 'archive' ? 'selected' : '' }}>Archived</option>
                    </select>

                    @if($search || $statusFilter || $month)
                    <a href="{{ route('admin.sessions.index') }}"
                        class="px-3.5 py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                        <i class="fa-solid fa-xmark text-xs"></i> Clear Filters
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Proportional Sessions Data Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 w-5/12">{{ __('Session Name & Scope') }}</th>
                        <th class="px-5 py-4 w-2/12">{{ __('PIC') }}</th>
                        <th class="px-4 py-4 w-2/12">{{ __('Progress') }}</th>
                        <th class="px-4 py-4 w-2/12">{{ __('Maturity Score') }}</th>
                        <th class="px-4 py-4 w-1/12">{{ __('Status') }}</th>
                        <th class="px-4 py-4 w-1/12 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        {{-- Session Name & Metadata --}}
                        <td class="px-6 py-4">
                            @if($session->status === 'completed')
                                <a href="{{ route('admin.sessions.show', $session) }}"
                                   class="font-black text-slate-900 text-sm hover:text-blue-600 transition-colors block leading-tight">
                                    {{ $session->name }}
                                </a>
                            @else
                                <a href="{{ route('admin.sessions.workspace', [$session, 'from' => 'index']) }}"
                                   class="font-black text-slate-900 text-sm hover:text-blue-600 transition-colors block leading-tight">
                                    {{ $session->name }}
                                </a>
                            @endif
                            <div class="flex items-center gap-2.5 mt-1.5 flex-wrap">
                                <span class="text-[10px] font-medium text-slate-400">
                                    <i class="fa-solid fa-calendar text-[9px] text-slate-300 mr-0.5"></i>
                                    Created {{ $session->created_at->format('d M Y') }}
                                </span>
                                @if($session->organization)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80">
                                        <i class="fa-solid fa-building text-[8px] text-slate-400"></i> {{ $session->organization->name }}
                                    </span>
                                @endif
                                @if($session->deadline)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold {{ $session->deadline->isPast() ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                                        <i class="fa-solid fa-hourglass-half text-[8px]"></i> {{ $session->deadline->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Owner / User --}}
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.users.show', $session->user_id) }}" 
                               class="inline-flex items-center gap-2 hover:text-blue-600 transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold flex items-center justify-center text-[10px] shrink-0 border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    {{ strtoupper(substr($session->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="font-bold text-slate-800 text-xs truncate max-w-[130px]">{{ $session->user->name ?? 'Unknown' }}</span>
                            </a>
                        </td>

                        {{-- Progress Bar --}}
                        <td class="px-4 py-4">
                            @php
                                $totalControls = 122;
                                $completedControls = $session->status === 'completed'
                                    ? 122
                                    : ($session->results ? $session->results->filter(fn($r) => !$r->is_applicable || $r->status === 'completed')->count() : 0);
                                $prog = min(100, round(($completedControls / $totalControls) * 100));
                            @endphp
                            <div class="flex flex-col gap-1.5 w-32">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">{{ $completedControls }} / {{ $totalControls }}</span>
                                    <span class="text-[10px] font-black text-slate-700">{{ $prog }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden flex items-center">
                                    <div class="h-full {{ $prog == 100 ? 'bg-emerald-500' : 'bg-blue-500' }} rounded-full" style="width: {{ $prog }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Maturity Score --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden shrink-0">
                                    <div class="h-full rounded-full transition-all
                                        {{ $session->overall_maturity_score >= 4 ? 'bg-emerald-500' : ($session->overall_maturity_score >= 2.5 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                         style="width: {{ ($session->overall_maturity_score / 5) * 100 }}%">
                                    </div>
                                </div>
                                <span class="font-black text-xs
                                    {{ $session->overall_maturity_score >= 4 ? 'text-emerald-600' : ($session->overall_maturity_score >= 2.5 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ number_format($session->overall_maturity_score, 2) }}
                                </span>
                            </div>
                        </td>

                        {{-- Status Pill --}}
                        <td class="px-4 py-4">
                            @if($session->trashed())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 border-amber-200 text-[9px] font-bold rounded-lg uppercase tracking-widest border">
                                    <i class="fa-solid fa-box-archive text-[8px]"></i>
                                    {{ __('Archived') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 
                                    {{ $session->status == 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                                       ($session->status == 'draft' ? 'bg-slate-50 text-slate-600 border-slate-200' : 'bg-blue-50 text-blue-600 border-blue-100') }} 
                                    text-[9px] font-bold rounded-lg uppercase tracking-widest border">
                                    @if($session->status === 'completed') <i class="fa-solid fa-circle-check text-[8px]"></i>
                                    @elseif($session->status === 'in_progress') <i class="fa-solid fa-circle-notch animate-spin text-[8px]"></i>
                                    @else <i class="fa-solid fa-pen-to-square text-[8px]"></i>
                                    @endif
                                    <span>{{ $session->status == 'completed' ? __('Completed') : ($session->status == 'draft' ? __('Draft') : __('In Progress')) }}</span>

                                    @if($session->status === 'completed' || $session->status === 'closed' || $session->isPastDeadline() || $session->isLockedForUser(auth()->user()))
                                        <span class="px-1.5 py-0.2 bg-amber-100 text-amber-800 rounded border border-amber-200 text-[8px] font-black uppercase tracking-wider flex items-center gap-1 ml-0.5" title="{{ __('Session is completed/locked in read-only mode') }}">
                                            <i class="fa-solid fa-lock text-[7px] text-amber-600"></i> {{ __('Locked') }}
                                        </span>
                                    @endif
                                </span>
                            @endif
                        </td>

                        {{-- Action Toolbar --}}
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.sessions.workspace', [$session, 'from' => 'index']) }}"
                                   class="w-8 h-8 rounded-xl flex items-center justify-center text-blue-600 hover:bg-blue-50 border border-blue-100 bg-white transition-all hover:scale-105"
                                   title="Open Assessment Workspace">
                                    <i class="fa-solid fa-clipboard-check text-xs"></i>
                                </a>
                                @if($session->status === 'completed')
                                    <a href="{{ route('admin.sessions.show', $session) }}"
                                       class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-50 border border-slate-200 bg-white transition-all hover:scale-105"
                                       title="{{ __('View Session Analytics') }}">
                                        <i class="fa-solid fa-chart-line text-xs"></i>
                                    </a>
                                @else
                                    <button type="button" disabled
                                       class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-300 bg-slate-50 border border-slate-200/60 cursor-not-allowed opacity-50"
                                       title="{{ __('Analytics available only for completed audit sessions') }}">
                                        <i class="fa-solid fa-chart-line text-xs"></i>
                                    </button>
                                @endif
                                <a href="{{ route('admin.sessions.edit', $session) }}"
                                   class="w-8 h-8 rounded-xl flex items-center justify-center text-emerald-600 hover:bg-emerald-50 border border-emerald-200 bg-white transition-all hover:scale-105"
                                   title="Edit Session">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}"
                                    x-data
                                    @submit.prevent="
                                        Swal.fire({
                                            title: '{{ addslashes(__('Delete Audit Session?')) }}',
                                            text: '{{ addslashes(__('Are you sure you want to delete audit session ":name"? This action cannot be undone.', ['name' => $session->name])) }}',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: '{{ addslashes(__('Yes, Delete!')) }}',
                                            cancelButtonText: '{{ addslashes(__('Cancel')) }}',
                                            width: '22rem',
                                            customClass: {
                                                title: 'text-base font-bold text-slate-800',
                                                htmlContainer: 'text-xs text-slate-500',
                                                confirmButton: 'text-xs px-3 py-2 rounded-lg font-semibold',
                                                cancelButton: 'text-xs px-3 py-2 rounded-lg font-semibold'
                                            }
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $el.submit();
                                            }
                                        });
                                    ">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-xl flex items-center justify-center text-rose-500 hover:bg-rose-50 border border-rose-200 bg-white transition-all hover:scale-105"
                                        title="Delete Session">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
                                <i class="fa-solid fa-clipboard-list"></i>
                            </div>
                            <p class="text-slate-700 font-bold text-sm">{{ __('No audit sessions found') }}</p>
                            <p class="text-slate-400 text-xs mt-1">{{ __('Try adjusting your search criteria or status filter.') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($sessions->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
