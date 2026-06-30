@extends('layouts.admin')

@section('title', 'User Management')
@section('header_title', 'User Management')

@section('content')

{{-- Page Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">User Management</h2>
        <p class="text-sm text-slate-500">Manage accounts, roles, and access control across the platform.</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm self-start">
        <i class="fa-solid fa-plus"></i> Add User
    </a>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-lg shrink-0">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Users</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($totalUsers) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-lg shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Active Accounts</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($activeUsers) }}</span>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 text-lg shrink-0">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Administrators</span>
            <span class="block text-2xl font-black text-slate-800 mt-0.5">{{ number_format($adminCount) }}</span>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

    {{-- Toolbar --}}
    <div class="p-5 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('admin.users.index') }}" x-data
              class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3 flex-1 flex-wrap">
                <span class="text-xs font-bold text-slate-400 bg-white border border-slate-200 px-2.5 py-1 rounded-full shrink-0">
                    {{ $users->total() }} users
                </span>
                <div class="relative flex-1 min-w-[180px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search name, email, org..."
                        x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                        class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <select name="role" onchange="this.form.requestSubmit()"
                    class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white font-semibold text-slate-700">
                    <option value="">All Roles</option>
                    <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user"  {{ $roleFilter === 'user'  ? 'selected' : '' }}>User</option>
                </select>
                <select name="status" onchange="this.form.requestSubmit()"
                    class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white font-semibold text-slate-700">
                    <option value="">All Status</option>
                    <option value="active"    {{ $statusFilter === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                @if($search || $roleFilter || $statusFilter)
                <a href="{{ route('admin.users.index') }}"
                    class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-xs"></i> Clear
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
                    <th class="px-5 py-4">User</th>
                    <th class="px-5 py-4">Organization</th>
                    <th class="px-5 py-4">Role</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Sessions</th>
                    <th class="px-5 py-4">Joined</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3 hover:opacity-80">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                <div class="text-xs text-slate-400">{{ $user->email }}</div>
                            </div>
                        </a>
                    </td>
                    <td class="px-5 py-4">
                        @if($user->organization_name)
                        <div class="font-semibold text-slate-800 text-xs">{{ $user->organization_name }}</div>
                        <div class="text-[10px] text-slate-400">{{ $user->business_sector ?? '—' }}</div>
                        @else
                        <span class="text-slate-300 italic text-xs">Not specified</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($user->isAdmin())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold uppercase tracking-widest">
                            <i class="fa-solid fa-shield-halved text-[9px]"></i> Admin
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold uppercase tracking-widest">
                            User
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($user->isActive())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Suspended
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-bold text-slate-700">{{ $user->assessment_sessions_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        @if($user->id !== auth()->id())
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.users.show', $user) }}"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 border border-blue-200 bg-white transition-colors" title="View">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-50 border border-slate-200 bg-white transition-colors" title="Edit">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors {{ $user->isActive() ? 'text-amber-600 hover:bg-amber-50 border border-amber-200 bg-white' : 'text-emerald-600 hover:bg-emerald-50 border border-emerald-200 bg-white' }}"
                                    title="{{ $user->isActive() ? 'Suspend User' : 'Activate User' }}">
                                    <i class="fa-solid {{ $user->isActive() ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                x-data
                                @submit.prevent="
                                    Swal.fire({
                                        title: '{{ addslashes(__('Delete User?')) }}',
                                        text: '{{ addslashes(__('Are you sure you want to delete user ":name"? This action cannot be undone.', ['name' => $user->name])) }}',
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
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 border border-red-200 bg-white transition-colors" title="Delete">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-xs text-slate-300 italic">It's you</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <i class="fa-solid fa-users-slash text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-500 font-semibold">No users found.</p>
                        <p class="text-slate-400 text-xs mt-1">Try adjusting your search or filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
