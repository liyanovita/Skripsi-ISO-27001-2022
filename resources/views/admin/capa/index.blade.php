@extends('layouts.admin')

@section('title', 'CAPA Plan Monitoring')
@section('header_title', 'Centralized CAPA Plan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">Corrective &amp; Preventive Actions (CAPA)</h2>
        <p class="text-sm text-slate-500">Monitor and manage remediation actions across all user audits.</p>
    </div>
    <a href="{{ route('admin.capa.export', array_filter(['status' => request('status'), 'risk' => request('risk')])) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-all shadow-sm hover:shadow-md shrink-0">
        <i class="fa-solid fa-file-csv"></i> Export CSV
    </a>
</div>

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
        <form method="GET" action="{{ route('admin.capa.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Clause/Control, User Name..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            
            <select name="status" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending/Open/In Progress</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <select name="risk" class="px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                <option value="">All Risks</option>
                <option value="Critical" {{ request('risk') == 'Critical' ? 'selected' : '' }}>Critical</option>
                <option value="High" {{ request('risk') == 'High' ? 'selected' : '' }}>High</option>
                <option value="Medium" {{ request('risk') == 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ request('risk') == 'Low' ? 'selected' : '' }}>Low</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'risk']))
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
                    <th class="px-6 py-4">Risk Level</th>
                    <th class="px-6 py-4">Due Date</th>
                    <th class="px-6 py-4">PIC</th>
                    <th class="px-6 py-4">CAPA Status</th>
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
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase shrink-0">
                                {{ $capa->standard->code }}
                            </span>
                            <span class="font-medium text-slate-700 line-clamp-1" title="{{ $capa->standard->title }}">{{ $capa->standard->title }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                            {{ $capa->risk_priority == 'Critical' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $capa->risk_priority == 'High' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $capa->risk_priority == 'Medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $capa->risk_priority == 'Low' ? 'bg-green-100 text-green-800' : '' }}
                            {{ !$capa->risk_priority ? 'bg-slate-100 text-slate-600' : '' }}
                        ">
                            {{ $capa->risk_priority ?: 'Low' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($capa->treatment_due_date)
                            @if($capa->treatment_due_date->isPast() && $capa->treatment_status != 'completed')
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-red-50 text-red-700 rounded-lg text-xs font-bold border border-red-200">
                                    <i class="fa-solid fa-circle-exclamation text-red-500"></i> Overdue ({{ $capa->treatment_due_date->format('d M Y') }})
                                </span>
                            @else
                                <span class="text-slate-700 font-medium">{{ $capa->treatment_due_date->format('d M Y') }}</span>
                            @endif
                        @else
                            <span class="text-slate-400 italic text-xs">Not set</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-slate-700 font-medium">{{ $capa->treatment_pic ?: '-' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $status = $capa->treatment_status ?: 'open';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                            {{ $status == 'completed' ? 'bg-green-50 text-green-700 border border-green-200' : '' }}
                            {{ $status == 'in_progress' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                            {{ $status == 'open' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                        ">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                {{ $status == 'completed' ? 'bg-green-600' : '' }}
                                {{ $status == 'in_progress' ? 'bg-blue-600' : '' }}
                                {{ $status == 'open' ? 'bg-red-600' : '' }}
                            "></span>
                            {{ ucfirst($status) }}
                        </span>
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
                        <p class="text-slate-500 font-medium">No CAPA Plan actions found matching the filters.</p>
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
