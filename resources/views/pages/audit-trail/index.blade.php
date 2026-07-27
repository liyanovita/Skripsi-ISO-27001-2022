@extends('layouts.app')
@section('title', 'Audit Trail')
@section('view_name', 'Audit Trail')

@section('content')
<div class="space-y-5 pb-8">

    {{-- Header Card --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-slate-900/20">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight">{{ __('Audit Trail') }}</h1>
                <p class="text-slate-400 font-semibold text-xs mt-0.5">{{ __('History of changes, assessment activities, and user events across the platform') }}</p>
            </div>
        </div>

        {{-- Filters & Export --}}
        <div class="flex flex-col sm:flex-row items-center gap-2">
            <form action="{{ route('audit-trail.index') }}" method="GET" id="audit-trail-filter" x-data class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search logs...') }}"
                        class="pl-9 {{ request('search') ? 'pr-8' : 'pr-3' }} py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-slate-800 transition-all w-full sm:w-48"
                        @input.debounce.500ms="$el.form.requestSubmit()">
                    @if(request('search'))
                        <a href="{{ route('audit-trail.index', array_merge(request()->except(['search', 'page']))) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fa-solid fa-circle-xmark text-xs"></i>
                        </a>
                    @endif
                </div>

                <select name="session_id" @change="$el.form.requestSubmit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-slate-800 transition-all cursor-pointer w-full sm:w-auto">
                    <option value="" {{ empty($selectedId) ? 'selected' : '' }}>— {{ __('All Assessment Sessions') }} —</option>
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ $selectedId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->created_at->format('M Y') }})
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('audit-trail.export', ['session_id' => $selectedId, 'search' => request('search')]) }}"
               id="btn-export-excel"
               class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-md shadow-emerald-600/20 active:scale-95 flex items-center gap-1.5 shrink-0">
                <i class="fa-solid fa-file-excel text-xs"></i> {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Date & Time') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('User / Actor') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Control Code') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Field Changed') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Old Value') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('New Value') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($trails as $trail)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- Date & Time --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-800 text-xs">{{ $trail->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $trail->created_at->format('H:i:s') }}</div>
                            </td>

                            {{-- User --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 text-[10px] font-black shrink-0">
                                        {{ strtoupper(substr($trail->user->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-slate-800 text-xs truncate max-w-[140px]">{{ $trail->user->name ?? 'System' }}</span>
                                </div>
                            </td>

                            {{-- Control --}}
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 bg-slate-900 text-white rounded-md text-xs font-mono font-bold">
                                    {{ $trail->model?->standard?->code ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Field Changed --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold border border-indigo-100">
                                    {{ friendly_field_label($trail->field_changed) }}
                                </span>
                            </td>

                            @php
                                $booleanFields = ['is_applicable'];
                                $isBool = in_array($trail->field_changed, $booleanFields);
                                $oldRaw = $trail->old_value;
                                $newRaw = $trail->new_value;
                                $oldDisplay = (!is_null($oldRaw) && $oldRaw !== '')
                                    ? ($isBool ? ($oldRaw == '1' ? 'Yes' : 'No') : $oldRaw)
                                    : null;
                                $newDisplay = (!is_null($newRaw) && $newRaw !== '')
                                    ? ($isBool ? ($newRaw == '1' ? 'Yes' : 'No') : $newRaw)
                                    : null;
                            @endphp

                            {{-- Old Value --}}
                            <td class="px-5 py-3.5 text-xs">
                                @if(is_null($oldDisplay))
                                    <span class="text-slate-300 italic">—</span>
                                @elseif($isBool)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $oldDisplay === 'Yes' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $oldDisplay }}
                                    </span>
                                @else
                                    <div class="max-w-[140px] truncate bg-rose-50/80 text-rose-700 border border-rose-100/80 px-2 py-0.5 rounded-lg text-xs font-medium" title="{{ $oldDisplay }}">
                                        {{ $oldDisplay }}
                                    </div>
                                @endif
                            </td>

                            {{-- New Value --}}
                            <td class="px-5 py-3.5 text-xs">
                                @if(is_null($newDisplay))
                                    <span class="text-slate-300 italic">—</span>
                                @elseif($isBool)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $newDisplay === 'Yes' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $newDisplay }}
                                    </span>
                                @else
                                    <div class="max-w-[140px] truncate bg-emerald-50/80 text-emerald-700 border border-emerald-100/80 px-2 py-0.5 rounded-lg text-xs font-bold" title="{{ $newDisplay }}">
                                        {{ $newDisplay }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-200"></i>
                                </div>
                                <p class="text-slate-800 font-bold text-sm">{{ __('No changes found in the audit trail') }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ __('Try selecting another audit session or clearing search filters.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($trails->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $trails->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
