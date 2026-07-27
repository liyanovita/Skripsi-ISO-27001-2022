@extends('layouts.admin')

@section('title', 'User Management')
@section('header_title', 'User Management')

@section('content')
<style>
    .user-row { transition: background 0.15s ease; }
    .search-box:focus { box-shadow: 0 0 0 3px rgba(59,130,246,0.1); border-color: #93c5fd; }
    .filter-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px; }
</style>

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-violet-600 text-white flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-users"></i>
                </span>
                User Management
            </h2>
            <p class="text-sm text-slate-400 mt-0.5 ml-9">Manage accounts, roles, and platform access control.</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 active:scale-95 transition-all shadow-md shadow-violet-600/20 shrink-0">
            <i class="fa-solid fa-user-plus text-xs"></i> Add User
        </a>
    </div>

    {{-- KPI Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Users</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-users text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalUsers) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Registered accounts</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-emerald-600 tracking-tight">{{ number_format($activeUsers) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Active accounts</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admins</span>
                <div class="w-8 h-8 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-violet-600 tracking-tight">{{ number_format($adminCount) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Administrators</div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Toolbar --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white">
            <form method="GET" action="{{ route('admin.users.index') }}" x-data
                  class="flex flex-col sm:flex-row items-center gap-3">
                {{-- Search --}}
                <div class="relative flex-1 w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search name, email, organization…"
                        x-on:input.debounce.400ms="$el.closest('form').requestSubmit()"
                        autocomplete="off"
                        class="search-box w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none transition-all bg-white">
                </div>
                {{-- Filters --}}
                <div class="flex items-center gap-2 shrink-0">
                    <select name="role" onchange="this.form.requestSubmit()"
                        class="filter-select px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none bg-white font-semibold text-slate-600 cursor-pointer">
                        <option value="">All Roles</option>
                        <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user"  {{ $roleFilter === 'user'  ? 'selected' : '' }}>User</option>
                    </select>
                    <select name="status" onchange="this.form.requestSubmit()"
                        class="filter-select px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none bg-white font-semibold text-slate-600 cursor-pointer">
                        <option value="">All Status</option>
                        <option value="active"    {{ $statusFilter === 'active'    ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                    @if($search || $roleFilter || $statusFilter)
                    <a href="{{ route('admin.users.index') }}"
                        class="px-3 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-sm font-bold hover:bg-slate-200 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-xmark text-xs"></i> Clear
                    </a>
                    @endif
                </div>
                {{-- Count badge --}}
                <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2.5 py-1.5 rounded-full shrink-0 hidden sm:block">
                    {{ $users->total() }} users
                </span>
            </form>
        </div>

        {{-- Users Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Organization</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Role</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Sessions</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Joined</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                    <tr class="user-row hover:bg-violet-50/20 transition-colors">
                        {{-- User --}}
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3 group">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs shrink-0
                                    {{ $user->isAdmin() ? 'bg-violet-100 text-violet-700' : 'bg-blue-50 text-blue-600' }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 group-hover:text-violet-600 transition-colors">{{ $user->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                    @if($user->job_title)
                                        <div class="text-[10px] font-bold text-blue-600 mt-0.5"><i class="fa-solid fa-briefcase text-[8px]"></i> {{ $user->job_title }}</div>
                                    @endif
                                </div>
                            </a>
                        </td>
                        {{-- Organization --}}
                        <td class="px-5 py-3.5">
                            @if($user->organization)
                                <div class="text-xs font-semibold text-slate-700">{{ $user->organization->name }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $user->organization->business_sector ?? '—' }}</div>
                            @else
                                <span class="text-xs text-slate-300 italic">No org</span>
                            @endif
                        </td>
                        {{-- Role --}}
                        <td class="px-5 py-3.5">
                            @if($user->isAdmin())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-violet-100 text-violet-700 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    <i class="fa-solid fa-shield-halved text-[8px]"></i> Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    User
                                </span>
                            @endif
                        </td>
                        {{-- Status --}}
                        <td class="px-5 py-3.5">
                            @if($user->isActive())
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-red-100 text-red-600 rounded-full text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Suspended
                                </span>
                            @endif
                        </td>
                        {{-- Sessions --}}
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-black text-slate-700">{{ $user->assessment_sessions_count }}</span>
                        </td>
                        {{-- Joined --}}
                        <td class="px-5 py-3.5 text-xs text-slate-400 whitespace-nowrap">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-right">
                            @if($user->id !== auth()->id())
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.users.show', $user) }}"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-blue-500 hover:bg-blue-50 border border-blue-100 bg-white transition-colors" title="View">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-violet-600 hover:bg-violet-50 border border-violet-100 bg-white transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-xl flex items-center justify-center transition-colors {{ $user->isActive() ? 'text-amber-500 hover:bg-amber-50 border border-amber-100 bg-white' : 'text-emerald-600 hover:bg-emerald-50 border border-emerald-100 bg-white' }}"
                                        title="{{ $user->isActive() ? 'Suspend User' : 'Activate User' }}">
                                        <i class="fa-solid {{ $user->isActive() ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                    x-data
                                    @submit.prevent="
                                        Swal.fire({
                                            title: 'Delete User?',
                                            text: 'Are you sure you want to delete user &quot;{{ addslashes($user->name) }}&quot;? This action cannot be undone.',
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
                            @else
                            <span class="text-[10px] text-slate-300 italic">You</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-20 text-center">
                            <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-users-slash text-3xl text-slate-200"></i>
                            </div>
                            <p class="text-slate-500 font-bold text-sm">No users found</p>
                            <p class="text-slate-400 text-xs mt-1">Try adjusting your search or filter criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    
    </div>

</div>
@endsection
