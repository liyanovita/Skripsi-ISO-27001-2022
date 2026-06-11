@extends('layouts.admin')

@section('title', 'User Management')
@section('header_title', 'User Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Header with search and filters --}}
    <div class="p-5 border-b border-slate-200">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-slate-800">All Registered Users</h3>
                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-full">{{ $users->total() }} total</span>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full">
                    <div class="relative flex-1 sm:w-56">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, org..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                    <select name="role" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ $roleFilter === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                    <select name="status" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $statusFilter === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </form>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors whitespace-nowrap">
                    <i class="fa-solid fa-plus"></i> Add User
                </a>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Organization</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Sessions</th>
                    <th class="px-6 py-4">Joined</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3 hover:opacity-80">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $user->email }}</div>
                            </div>
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->organization_name)
                            <div class="font-medium text-slate-800">{{ $user->organization_name }}</div>
                            <div class="text-[10px] text-slate-500">{{ $user->business_sector ?? 'N/A' }}</div>
                        @else
                            <span class="text-slate-400 italic text-xs">Not specified</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($user->isAdmin())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold uppercase tracking-widest">
                                <i class="fa-solid fa-shield-halved"></i> Admin
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold uppercase tracking-widest">User</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($user->isActive())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-bold uppercase tracking-widest">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-100 text-red-700 rounded text-xs font-bold uppercase tracking-widest">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Suspended
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-700">{{ $user->assessment_sessions_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($user->id !== auth()->id())
                        <div class="flex items-center justify-end gap-2" x-data="{ showDeleteConfirm: false }">
                            <a href="{{ route('admin.users.show', $user) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 bg-white border border-blue-200 transition-colors" title="View Detail">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-50 bg-white border border-slate-200 transition-colors" title="Edit">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors {{ $user->isActive() ? 'text-amber-600 hover:bg-amber-50 bg-white border border-amber-200' : 'text-emerald-600 hover:bg-emerald-50 bg-white border border-emerald-200' }}" title="{{ $user->isActive() ? 'Suspend' : 'Activate' }}">
                                    <i class="fa-solid {{ $user->isActive() ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                                </button>
                            </form>
                            <button @click="showDeleteConfirm = true" x-show="!showDeleteConfirm" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50 bg-white border border-red-200 transition-colors" title="Delete">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" x-show="showDeleteConfirm" class="flex gap-1" x-cloak>
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">Confirm</button>
                                <button type="button" @click="showDeleteConfirm = false" class="px-3 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded hover:bg-slate-300">Cancel</button>
                            </form>
                        </div>
                        @else
                            <span class="text-xs text-slate-400 italic">It's you</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-users-slash text-3xl mb-3 text-slate-300"></i>
                        <p>No users found.</p>
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
