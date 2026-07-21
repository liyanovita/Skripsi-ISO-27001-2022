@extends('layouts.admin')

@section('title', 'Organizations Management')
@section('header_title', 'Organizations')

@section('content')
<div>
    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800">Organizations</h2>
            <p class="text-sm text-slate-500">Manage client organizations and view their audit assessment cycles.</p>
        </div>
        <a href="{{ route('admin.organizations.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Register Organization
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($totalOrganizations) }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Organizations</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">{{ number_format($totalSessions) }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Audit Sessions</div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Filter Bar --}}
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <form method="GET" action="{{ route('admin.organizations.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search name, code, or email..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold transition-colors">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.organizations.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center justify-center gap-1">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Organization Name</th>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4 text-center">Sessions</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($organizations as $org)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 hover:text-blue-600 transition-colors">
                                <a href="{{ route('admin.organizations.show', $org) }}">{{ $org->name }}</a>
                            </div>
                            @if($org->description)
                                <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $org->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($org->code)
                                <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-700 text-[10px] font-bold rounded uppercase tracking-wider">
                                    {{ $org->code }}
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($org->contact_email)
                                <div class="flex items-center gap-1.5 text-slate-700">
                                    <i class="fa-regular fa-envelope text-slate-400"></i> {{ $org->contact_email }}
                                </div>
                            @endif
                            @if($org->contact_phone)
                                <div class="flex items-center gap-1.5 text-slate-500 mt-0.5">
                                    <i class="fa-solid fa-phone text-slate-400"></i> {{ $org->contact_phone }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-slate-700">
                            {{ $org->sessions_count }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.organizations.edit', $org) }}"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-600 hover:bg-emerald-50 border border-emerald-200 bg-white transition-colors"
                                    title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.organizations.destroy', $org) }}"
                                    x-data
                                    @submit.prevent="
                                        Swal.fire({
                                            title: 'Delete Organization?',
                                            text: 'Are you sure you want to delete organization &quot;{{ addslashes($org->name) }}&quot;? All associated audit sessions will remain but organization linkage will be removed.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: 'Yes, Delete!',
                                            cancelButtonText: 'Cancel',
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
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 border border-red-200 bg-white transition-colors"
                                        title="Delete">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <i class="fa-solid fa-building text-4xl text-slate-200 mb-3 block"></i>
                            <p class="text-slate-500 font-semibold">No organizations registered.</p>
                            <p class="text-slate-400 text-xs mt-1">Get started by registering a client organization.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($organizations->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $organizations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
