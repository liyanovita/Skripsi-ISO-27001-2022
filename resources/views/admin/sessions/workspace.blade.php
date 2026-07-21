@extends('layouts.admin')

@section('title', 'Admin Session Workspace')
@section('header_title', 'Assessment Session Workspace')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sessions.show', $session) }}" class="w-9 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg flex items-center justify-center transition-colors">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-xl font-black text-slate-800">{{ $session->name }}</h2>
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">User: {{ $session->user->name }} @if($session->organization) | Organization: {{ $session->organization->name }} @endif</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.capa.index', ['session_id' => $session->id]) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hover:shadow-md flex items-center gap-1.5">
            <i class="fa-solid fa-triangle-exclamation"></i> View Session Improvement Tracking
        </a>
    </div>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-black text-slate-800">{{ $stats['total_controls'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total Controls</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-black text-slate-850">{{ $stats['applicable'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Applicable</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-black text-slate-600">{{ $stats['completed'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Assessed</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-black text-emerald-600">{{ $stats['compliant'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Compliant</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-black text-amber-600">{{ $stats['partial'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Partial</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center col-span-2 lg:col-span-1">
        <div class="text-2xl font-black text-red-600">{{ $stats['non_compliant'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Non-Comp</div>
    </div>
</div>

{{-- Completion Progress Bar --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6 shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Overall Assessment Completion</span>
        <span class="text-xs font-black text-slate-800">{{ $stats['completion_pct'] }}%</span>
    </div>
    <div class="w-full bg-slate-100 rounded-full h-3.5 overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500 {{ $stats['completion_pct'] >= 85 ? 'bg-emerald-500' : ($stats['completion_pct'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
             style="width: {{ $stats['completion_pct'] }}%"></div>
    </div>
</div>

{{-- Main Workspace Panels --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ activeTab: 'controls' }">
    <div class="border-b border-slate-200 bg-slate-50 flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-list-check text-slate-500"></i>
            <h3 class="font-bold text-slate-800 text-sm">ISO 27001 Checklist - Read Only View</h3>
        </div>
    </div>

    <div class="p-6">
        <div class="space-y-6">
            @forelse($groupedResults as $clauseCode => $results)
                <div class="border border-slate-150 rounded-xl overflow-hidden">
                    <div class="bg-slate-50/80 px-5 py-3 border-b border-slate-150 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ $clauseCode }} - {{ $results->first()->standard->parent?->title ?? 'Main Clause' }}</span>
                        <span class="text-[10px] font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full">{{ $results->count() }} Controls</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50/50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-150">
                                <tr>
                                    <th class="px-5 py-2.5">Code</th>
                                    <th class="px-5 py-2.5">Control Name</th>
                                    <th class="px-5 py-2.5">Applicability</th>
                                    <th class="px-5 py-2.5">Maturity Rating</th>
                                    <th class="px-5 py-2.5">Compliance Status</th>
                                    <th class="px-5 py-2.5 text-right">Improvement Status / Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($results as $result)
                                    <tr class="hover:bg-slate-50/30 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-slate-800">{{ $result->standard->code }}</td>
                                        <td class="px-5 py-3.5 font-medium text-slate-700 max-w-xs truncate" title="{{ $result->standard->title }}">{{ $result->standard->title }}</td>
                                        <td class="px-5 py-3.5">
                                            @if($result->is_applicable)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-150">Applicable</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-bold border border-slate-200">Excluded</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 font-bold text-slate-800">
                                            @if(!$result->is_applicable)
                                                <span class="text-slate-400">—</span>
                                            @elseif($result->status !== 'completed')
                                                <span class="text-slate-400 italic font-medium">Unassessed</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-slate-100 text-slate-700 rounded font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full 
                                                        {{ $result->maturity_rating == 5 ? 'bg-blue-500' : '' }}
                                                        {{ $result->maturity_rating == 4 ? 'bg-emerald-500' : '' }}
                                                        {{ $result->maturity_rating == 3 ? 'bg-yellow-500' : '' }}
                                                        {{ $result->maturity_rating == 2 ? 'bg-orange-500' : '' }}
                                                        {{ $result->maturity_rating <= 1 ? 'bg-red-500' : '' }}
                                                    "></span>
                                                    L{{ $result->maturity_rating }} / 5
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 font-semibold">
                                            @if(!$result->is_applicable)
                                                <span class="text-slate-400 font-medium">Not Applicable</span>
                                            @elseif($result->status !== 'completed')
                                                <span class="text-slate-400 font-medium italic">Pending</span>
                                            @else
                                                @php
                                                    $status = $result->compliance_status;
                                                @endphp
                                                <span class="
                                                    {{ $status === 'Compliant' ? 'text-emerald-600' : '' }}
                                                    {{ $status === 'Partially Compliant' ? 'text-amber-600' : '' }}
                                                    {{ $status === 'Non-Compliant' ? 'text-rose-600' : '' }}
                                                ">{{ $status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            @if($result->is_applicable && $result->status === 'completed')
                                                @if($result->maturity_rating < 4)
                                                    <div class="flex items-center justify-end gap-2">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold 
                                                            {{ $result->treatment_status == 'completed' ? 'bg-green-50 text-green-700 border border-green-200' : '' }}
                                                            {{ $result->treatment_status == 'in_progress' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                                            {{ !$result->treatment_status || $result->treatment_status == 'open' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                                                        ">
                                                            {{ ucfirst($result->treatment_status ?: 'Open') }}
                                                        </span>
                                                        <a href="{{ route('admin.capa.edit', $result) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-bold transition-all shadow-xs">
                                                            <i class="fa-solid fa-pen-to-square"></i> Edit Improvement
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-emerald-600 font-bold text-[10px]"><i class="fa-solid fa-circle-check"></i> Fully Compliant</span>
                                                @endif
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-500">
                    <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 text-slate-350 block"></i>
                    <p class="font-medium text-slate-600">No assessable ISO controls found in this session.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
