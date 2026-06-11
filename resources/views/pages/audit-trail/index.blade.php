@extends('layouts.app')
@section('title', 'Audit Trail')
@section('view_name', 'Audit Trail')

@section('content')
<div class="max-w-6xl mx-auto space-y-5 pb-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center text-white shadow-lg shadow-slate-800/20">
                <i class="fa-solid fa-clock-rotate-left text-base"></i>
            </div>
            <div class="leading-none">
                <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">{{ __('Audit Trail') }}</h1>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('History of Changes & Activities') }}</p>
            </div>
        </div>

        {{-- Session filter & Export --}}
        <div class="flex flex-col sm:flex-row items-center gap-2">
            <form action="{{ route('audit-trail.index') }}" method="GET" id="auditTrailFilter" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search logs...') }}"
                        class="pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-slate-800/30 transition-all w-full sm:w-44 placeholder:text-slate-400"
                        onkeypress="if(event.keyCode==13) { document.getElementById('auditTrailFilter').submit(); }">
                </div>
                <select name="session_id" onchange="document.getElementById('auditTrailFilter').submit()"
                    class="bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-slate-800/30 transition-all cursor-pointer shadow-sm">
                    <option value="" {{ empty($selectedId) ? 'selected' : '' }}>-- {{ __('All Sessions') }} --</option>
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ $selectedId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->created_at->format('M Y') }})
                        </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('audit-trail.export', ['session_id' => $selectedId, 'search' => request('search')]) }}"
               class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-emerald-500 shadow-md transition-all flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-download"></i>{{ __('CSV') }}</a>
        </div>
    </div>

    {{-- Content --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Date & Time') }}</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('User') }}</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Control') }}</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Field Changed') }}</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Old Value') }}</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('New Value') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($trails as $trail)
                        <tr class="hover:bg-slate-50/50 transition-all">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-800">{{ $trail->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] font-medium text-slate-400">{{ $trail->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[9px] font-bold text-slate-600">
                                        {{ substr($trail->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $trail->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[9px] font-black uppercase tracking-widest">
                                    {{ $trail->model?->standard?->code ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-bold text-indigo-600">
                                    {{ str_replace('_', ' ', Str::title($trail->field_changed)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-rose-500 font-medium bg-rose-50 px-2 py-0.5 rounded inline-block max-w-[120px] truncate">
                                    {{ $trail->old_value ?? '—' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded inline-block max-w-[120px] truncate">
                                    {{ $trail->new_value ?? '—' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-clock-rotate-left text-xl text-slate-300"></i>
                                </div>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">{{ __('No changes found in the audit trail.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-1">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            {{ __('Showing') }} <span class="text-slate-700">{{ $trails->firstItem() ?? 0 }}</span>
            {{ __('to') }} <span class="text-slate-700">{{ $trails->lastItem() ?? 0 }}</span>
        </div>
        <div class="flex items-center gap-2">
            @if ($trails->onFirstPage())
                <span class="px-4 py-2 rounded-xl border border-slate-100 bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest cursor-not-allowed opacity-60 flex items-center gap-2">
                    <i class="fa-solid fa-chevron-left"></i> {{ __('Prev') }}
                </span>
            @else
                <a href="{{ $trails->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-[10px] font-black uppercase tracking-widest hover:border-blue-200 hover:text-blue-600 transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-chevron-left"></i> {{ __('Prev') }}
                </a>
            @endif
            @if ($trails->hasMorePages())
                <a href="{{ $trails->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-[10px] font-black uppercase tracking-widest hover:border-blue-200 hover:text-blue-600 transition-all shadow-sm flex items-center gap-2">
                    {{ __('Next') }} <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="px-4 py-2 rounded-xl border border-slate-100 bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest cursor-not-allowed opacity-60 flex items-center gap-2">
                    {{ __('Next') }} <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
</div>
@endsection
