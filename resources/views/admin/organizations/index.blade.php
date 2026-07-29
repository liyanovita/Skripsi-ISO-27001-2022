@extends('layouts.admin')

@section('title', 'Organizations Management')
@section('header_title', 'Organizations')

@section('content')
<style>
    .search-input:focus {
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
</style>

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">
                    <i class="fa-solid fa-building"></i>
                </span>
                Organizations
            </h2>
            <p class="text-sm text-slate-500 mt-0.5 ml-9">Manage client organizations and their ISO 27001:2022 audit cycles.</p>
        </div>
        <a href="{{ route('admin.organizations.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20">
            <i class="fa-solid fa-plus"></i> Add Organization
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Organizations</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-building text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalOrganizations) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Total registered</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sessions</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-list text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalSessions) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Audit sessions</div>
        </div>
    </div>

    {{-- Search + Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Filter Bar --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white">
            <form method="GET" action="{{ route('admin.organizations.index') }}" id="search-form" class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none" id="search-icon"></i>
                    <i class="fa-solid fa-spinner fa-spin absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-400 text-sm pointer-events-none hidden" id="search-spinner"></i>
                    <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                        placeholder="Search by name, code, or email…"
                        autocomplete="off"
                        class="search-input w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-blue-400 transition-all bg-white">
                    {{-- Clear X button (inside input) --}}
                    <button type="button" id="clear-btn"
                        onclick="clearSearch()"
                        class="{{ request('search') ? '' : 'hidden' }} absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-500 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </div>
            </form>
            <script>
                let searchTimer = null;
                const searchInput = document.getElementById('search-input');
                const searchForm = document.getElementById('search-form');
                const clearBtn = document.getElementById('clear-btn');
                const searchIcon = document.getElementById('search-icon');
                const searchSpinner = document.getElementById('search-spinner');

                searchInput.addEventListener('input', function () {
                    const val = this.value;

                    // Show/hide clear button
                    clearBtn.classList.toggle('hidden', val === '');

                    // Show spinner
                    searchIcon.classList.add('hidden');
                    searchSpinner.classList.remove('hidden');

                    // Debounce 400ms
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => {
                        searchSpinner.classList.add('hidden');
                        searchIcon.classList.remove('hidden');
                        if (searchForm.requestSubmit) {
                            searchForm.requestSubmit();
                        } else {
                            searchForm.submit();
                        }
                    }, 400);
                });

                function clearSearch() {
                    searchInput.value = '';
                    if (searchForm.requestSubmit) {
                        searchForm.requestSubmit();
                    } else {
                        searchForm.submit();
                    }
                }

                // Auto-focus search on page load if there was a search
                @if(request('search'))
                    searchInput.focus();
                    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                @endif
            </script>
        </div>

        {{-- Organizations Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Organization</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Sector</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Sessions</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($organizations as $i => $org)
                    <tr class="org-card hover:bg-slate-50/60 transition-colors">
                        {{-- Name --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs shrink-0
                                    {{ collect(['bg-blue-100 text-blue-700','bg-blue-100 text-blue-700','bg-emerald-100 text-emerald-700','bg-amber-100 text-amber-700','bg-rose-100 text-rose-700'])->get($i % 5) }}">
                                    {{ strtoupper(substr($org->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a href="{{ route('admin.organizations.show', $org) }}"
                                            class="font-bold text-slate-900 hover:text-blue-600 transition-colors">
                                            {{ $org->name }}
                                        </a>
                                        @if($org->code)
                                            <span class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 text-slate-400 text-[9px] font-black rounded uppercase tracking-widest">
                                                {{ $org->code }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($org->description)
                                        <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-xs">{{ $org->description }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        {{-- Sector --}}
                        <td class="px-5 py-3.5">
                            <div class="text-xs text-slate-600 font-medium">{{ $org->business_sector ?? '—' }}</div>
                            @if($org->organization_scale)
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $org->organization_scale }}</div>
                            @endif
                        </td>
                        {{-- Contact --}}
                        <td class="px-5 py-3.5">
                            @if($org->contact_email)
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                    <i class="fa-regular fa-envelope text-slate-300 text-xs"></i>
                                    {{ $org->contact_email }}
                                </div>
                            @endif
                            @if($org->contact_phone)
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-0.5">
                                    <i class="fa-solid fa-phone text-slate-300 text-xs"></i>
                                    {{ $org->contact_phone }}
                                </div>
                            @endif
                            @if(!$org->contact_email && !$org->contact_phone)
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        {{-- Sessions --}}
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-50 text-blue-700 font-black text-sm">
                                {{ $org->sessions_count }}
                            </span>
                        </td>
                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.organizations.show', $org) }}"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-blue-500 hover:bg-blue-50 border border-blue-100 bg-white transition-colors" title="View">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('admin.organizations.edit', $org) }}"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-amber-600 hover:bg-amber-50 border border-amber-100 bg-white transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.organizations.destroy', $org) }}"
                                    x-data
                                    @submit.prevent="
                                        Swal.fire({
                                            title: 'Delete Organization?',
                                            text: 'Are you sure you want to delete &quot;{{ addslashes($org->name) }}&quot;? All associated audit sessions will remain.',
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
                                            if (result.isConfirmed) { $el.submit(); }
                                        });
                                    ">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-xl flex items-center justify-center text-red-400 hover:bg-red-50 border border-red-100 bg-white transition-colors" title="Delete">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-20 text-center">
                            <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-building text-3xl text-slate-200"></i>
                            </div>
                            <p class="text-slate-500 font-bold text-sm">No organizations found</p>
                            <p class="text-slate-400 text-xs mt-1">
                                @if(request('search'))
                                    No results for "<strong>{{ request('search') }}</strong>". <a href="{{ route('admin.organizations.index') }}" class="text-blue-500 underline">Clear search</a>.
                                @else
                                    Get started by adding your first client organization.
                                @endif
                            </p>
                            @unless(request('search'))
                            <a href="{{ route('admin.organizations.create') }}"
                                class="inline-flex items-center gap-2 mt-5 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-600/20">
                                <i class="fa-solid fa-plus"></i> Add First Organization
                            </a>
                            @endunless
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($organizations->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $organizations->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
