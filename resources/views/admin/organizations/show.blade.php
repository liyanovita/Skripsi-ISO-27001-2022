@extends('layouts.admin')

@section('title', $organization->name . ' — Organization Detail')
@section('header_title', 'Organization Detail')

@section('content')
<div class="space-y-6 pb-8">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.organizations.index') }}" class="hover:text-slate-700 transition-colors font-medium">Organizations</a>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
        <span class="font-bold text-slate-800 truncate">{{ $organization->name }}</span>
    </div>

    {{-- Header Card --}}
    <div class="bg-gradient-to-br from-blue-900 to-blue-950 rounded-2xl p-6 text-white relative overflow-hidden shadow-xl">
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-24 bottom-0 w-56 h-56 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-2xl shrink-0">
                    <i class="fa-solid fa-building text-blue-200"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-2xl font-black tracking-tight">{{ $organization->name }}</h1>
                        @if($organization->code)
                            <span class="px-2 py-0.5 bg-white/10 text-blue-200 rounded text-[10px] font-black uppercase tracking-widest border border-white/10">
                                {{ $organization->code }}
                            </span>
                        @endif
                    </div>
                    @if($organization->description)
                        <p class="text-sm text-blue-200/80 font-medium max-w-xl leading-relaxed">{{ $organization->description }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-4 mt-3">
                        @if($organization->contact_email)
                            <span class="flex items-center gap-1.5 text-[11px] text-blue-300 font-medium">
                                <i class="fa-regular fa-envelope"></i> {{ $organization->contact_email }}
                            </span>
                        @endif
                        @if($organization->contact_phone)
                            <span class="flex items-center gap-1.5 text-[11px] text-blue-300 font-medium">
                                <i class="fa-solid fa-phone"></i> {{ $organization->contact_phone }}
                            </span>
                        @endif
                        @if($organization->address)
                            <span class="flex items-center gap-1.5 text-[11px] text-blue-300 font-medium">
                                <i class="fa-solid fa-location-dot"></i> {{ Str::limit($organization->address, 60) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.organizations.edit', $organization) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-xl text-xs font-bold transition-all">
                    <i class="fa-solid fa-pen text-xs"></i> Edit
                </a>
                <a href="{{ route('admin.sessions.create') }}?organization_id={{ $organization->id }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-bold transition-all shadow-md">
                    <i class="fa-solid fa-plus text-xs"></i> Create Audit Session
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Sessions</p>
                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-list text-xs"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-900 tracking-tight">{{ $stats['total_sessions'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Active</p>
                <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-spinner text-xs"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-amber-600 tracking-tight">{{ $stats['active_sessions'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Completed</p>
                <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-check-double text-xs"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-emerald-600 tracking-tight">{{ $stats['completed_sessions'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Avg Maturity</p>
                <div class="w-8 h-8 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-900 tracking-tight">
                {{ number_format($stats['avg_maturity'], 1) }}
                <span class="text-sm text-slate-400 font-medium">/ 5</span>
            </p>
        </div>
    </div>

    {{-- Organization Context Card --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Left Side: Scope & Sector Info --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-50">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-compass text-sm"></i>
                </div>
                <div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">ISO 27001 Business Context</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Organization context & scale parameters</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Business Sector</span>
                    <span class="text-sm font-semibold text-slate-800">{{ $organization->business_sector ?? 'Not Specified' }}</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Organization Scale</span>
                    @if($organization->organization_scale)
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100">
                            {{ $organization->organization_scale }}
                        </span>
                    @else
                        <span class="text-sm font-semibold text-slate-400">Not Specified</span>
                    @endif
                </div>
            </div>

            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">ISMS Scope (Ruang Lingkup SGIP)</span>
                <p class="text-xs text-slate-600 leading-relaxed font-medium bg-slate-50 p-3 rounded-xl border border-slate-100">
                    {{ $organization->isms_scope ?? 'No ISMS scope defined. Edit organization details to set it.' }}
                </p>
            </div>
        </div>

        {{-- Right Side: IT Governance --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-50">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-sitemap text-sm"></i>
                </div>
                <div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">IT Governance Structure</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">Roles, responsibilities, & reporting lines</p>
                </div>
            </div>

            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Structure Details</span>
                <p class="text-xs text-slate-600 leading-relaxed font-medium bg-slate-50 p-3 rounded-xl border border-slate-100">
                    {{ $organization->it_governance_structure ?? 'No governance details defined. Edit organization details to set it.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Audit Sessions Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-500"></i> Audit Sessions
                </h3>
                <p class="text-[10px] text-slate-400 font-medium mt-0.5">All assessment sessions linked to this organization</p>
            </div>
            <a href="{{ route('admin.sessions.create') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors border border-blue-100">
                <i class="fa-solid fa-plus text-[10px]"></i> New Session
            </a>
        </div>

        @if($sessions->isEmpty())
            <div class="py-16 text-center">
                <i class="fa-solid fa-clipboard-list text-4xl text-slate-200 mb-3 block"></i>
                <p class="text-slate-500 font-semibold">No audit sessions yet</p>
                <p class="text-slate-400 text-xs mt-1 mb-4">Create an audit session to begin the ISO 27001:2022 assessment.</p>
                <a href="{{ route('admin.sessions.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm">
                    <i class="fa-solid fa-rocket"></i> Launch First Session
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-4">Session Name</th>
                            <th class="px-5 py-4">Lead Auditor</th>
                            <th class="px-5 py-4">Auditors</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-center">Controls</th>
                            <th class="px-5 py-4">Maturity</th>
                            <th class="px-5 py-4">Updated</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sessions as $session)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.sessions.show', $session) }}"
                                    class="font-bold text-slate-900 hover:text-blue-600 transition-colors">
                                    {{ $session->name }}
                                </a>
                                <div class="text-[10px] text-slate-400 mt-0.5">Created {{ $session->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-bold text-[10px] shrink-0">
                                        {{ strtoupper(substr($session->user->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800 text-xs">{{ $session->user->name ?? 'Unknown' }}</div>
                                        <div class="text-[9px] text-amber-500 font-bold flex items-center gap-0.5">
                                            <i class="fa-solid fa-crown text-[8px]"></i> Lead
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @php $auditors = $session->invitedUsers->where('pivot.role', 'auditor'); @endphp
                                @if($auditors->count() > 0)
                                    <div class="flex -space-x-2">
                                        @foreach($auditors->take(4) as $auditor)
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-[9px] border-2 border-white shrink-0"
                                                title="{{ $auditor->name }}">
                                                {{ strtoupper(substr($auditor->name, 0, 2)) }}
                                            </div>
                                        @endforeach
                                        @if($auditors->count() > 4)
                                            <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-[9px] border-2 border-white shrink-0">
                                                +{{ $auditors->count() - 4 }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest
                                    {{ $session->status === 'completed'   ? 'bg-emerald-100 text-emerald-700' :
                                       ($session->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
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
                                    <div class="w-14 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full
                                            {{ $session->overall_maturity_score >= 4 ? 'bg-emerald-500' : ($session->overall_maturity_score >= 2.5 ? 'bg-amber-500' : 'bg-red-500') }}"
                                            style="width: {{ ($session->overall_maturity_score / 5) * 100 }}%">
                                        </div>
                                    </div>
                                    <span class="font-bold text-sm {{ $session->overall_maturity_score >= 4 ? 'text-emerald-600' : ($session->overall_maturity_score >= 2.5 ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ number_format($session->overall_maturity_score, 1) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $session->updated_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.sessions.show', $session) }}"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 border border-blue-200 bg-white transition-colors"
                                        title="View">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.sessions.edit', $session) }}"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-600 hover:bg-emerald-50 border border-emerald-200 bg-white transition-colors"
                                        title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
