@extends('layouts.admin')

@section('title', 'Edit Audit Session')
@section('header_title', 'Edit Audit Session')

@push('styles')
<style>
    .user-checkbox-item:has(input:checked) {
        background: #eff6ff;
        border-color: #93c5fd;
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Back to Audit Sessions
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-pen text-sm"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800">Edit Audit Session</h3>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Update session details and assigned auditors</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.sessions.update', $session) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Section 1: Session Info --}}
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Session Information</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Audit Title / Session Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $session->name) }}" required
                            placeholder="e.g., ISO 27001:2022 Internal Audit Q3 2026"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Assessed Organization</label>
                        <select name="organization_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="">-- None / Independent Assessment --</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id', $session->organization_id) == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }} {{ $org->code ? "({$org->code})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('organization_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            {{-- Section 2: Assigned User / Auditee --}}
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Assigned User / Auditee <span class="text-red-500">*</span></p>
                <select name="user_id" required
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('user_id') border-red-400 @enderror">
                    <option value="">-- Select Assigned User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $session->user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                            @if($user->role !== 'user') — {{ ucfirst($user->role) }} @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1"><i class="fa-solid fa-user text-slate-400 mr-0.5"></i>The user assigned to perform the self-assessment for this session.</p>
                @error('user_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <hr class="border-slate-100">

            {{-- Section 3: Audit Status --}}
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Audit Status <span class="text-red-500">*</span></p>
                <select name="status" required
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('status') border-red-400 @enderror">
                    <option value="draft" {{ old('status', $session->trashed() ? 'archive' : $session->status) == 'draft' ? 'selected' : '' }}>Draft (Preparatory / Private)</option>
                    <option value="in_progress" {{ old('status', $session->trashed() ? 'archive' : $session->status) == 'in_progress' ? 'selected' : '' }}>Active (Public to Auditee)</option>
                    <option value="completed" {{ old('status', $session->trashed() ? 'archive' : $session->status) == 'completed' ? 'selected' : '' }}>Completed (Finalized / Read-Only)</option>
                    <option value="archive" {{ old('status', $session->trashed() ? 'archive' : $session->status) == 'archive' ? 'selected' : '' }}>Archive (Soft-Deleted / Hidden)</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1"><i class="fa-solid fa-circle-info text-slate-400 mr-0.5"></i>Changing to Completed will finalize the session and freeze modifications. Changing to Archive will soft-delete the session.</p>
                @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <hr class="border-slate-100">

            {{-- Section 4: Audit Deadline --}}
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Audit Deadline</p>
                <input type="date" name="deadline" value="{{ old('deadline', $session->deadline ? $session->deadline->format('Y-m-d') : '') }}"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('deadline') border-red-400 @enderror">
                <p class="text-[11px] text-slate-400 mt-1"><i class="fa-regular fa-calendar-check text-slate-400 mr-0.5"></i>Optional deadline for completion of self-assessment.</p>
                @error('deadline') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <hr class="border-slate-100">

            {{-- Section 4: Additional Auditors --}}
            <div x-data="{ search: '' }">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Additional Auditors / Collaborators</p>
                    <span class="text-[10px] text-slate-400 font-medium">Optional — can collaborate on this session</span>
                </div>

                <div class="relative mb-3">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                    <input type="text" x-model="search" placeholder="Filter users by name or email..."
                        class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-slate-50">
                </div>

                <div class="border border-slate-200 rounded-lg overflow-hidden max-h-60 overflow-y-auto">
                    @forelse($users as $user)
                        <label class="user-checkbox-item flex items-center gap-3 px-4 py-2.5 border-b border-slate-100 last:border-0 cursor-pointer hover:bg-slate-50 transition-colors"
                            x-show="search === '' || '{{ strtolower($user->name) }} {{ strtolower($user->email) }}'.includes(search.toLowerCase())">
                            <input type="checkbox" name="invited_users[]" value="{{ $user->id }}"
                                {{ in_array($user->id, old('invited_users', $currentInvitedIds)) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $user->name }}</p>
                                <p class="text-[10px] text-slate-400 truncate">{{ $user->email }}</p>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-slate-100 text-slate-500">
                                {{ $user->role }}
                            </span>
                        </label>
                    @empty
                        <div class="px-4 py-6 text-center text-slate-400 text-sm">No active users found.</div>
                    @endforelse
                </div>
                <p class="text-[10px] text-slate-400 mt-1.5"><i class="fa-solid fa-info-circle text-slate-300 mr-1"></i>Users selected here will have access to view and work on this session alongside the assigned lead auditor.</p>
                @error('invited_users') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <a href="{{ route('admin.sessions.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
