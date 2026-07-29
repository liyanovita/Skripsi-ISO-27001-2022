@extends('layouts.admin')

@section('title', 'Launch Audit Session')
@section('header_title', 'Launch Audit Session')

@push('styles')
<style>
    .user-checkbox-item:has(input:checked) {
        background-color: #f0f9ff;
        border-color: #93c5fd;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl space-y-6 pb-12">

    {{-- Top Back Navigation & Breadcrumb --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sessions.index') }}" 
           class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-slate-600 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all group">
            <i class="fa-solid fa-arrow-left text-slate-400 group-hover:-translate-x-0.5 group-hover:text-blue-600 transition-transform"></i>
            {{ __('Back to Audit Sessions') }}
        </a>
    </div>

    {{-- Form Container --}}
    <form method="POST" action="{{ route('admin.sessions.store') }}" class="space-y-6">
        @csrf

        {{-- Main Header Card --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm relative overflow-hidden flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-lg shrink-0 shadow-sm">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Launch Audit Session') }}</h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">{{ __('Initialize a new ISO 27001:2022 assessment session and assign users') }}</p>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    {{ __('New Session Setup') }}
                </span>
            </div>
        </div>

        {{-- Card 1: Primary Information --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('Session Information') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Specify the title, organization, and assessment target dates') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Session Title --}}
                <div class="md:col-span-2 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Audit Title / Session Name') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="e.g., ISO 27001:2022 Internal Audit Q3 2026"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('name') border-rose-400 bg-rose-50/30 @enderror">
                        <i class="fa-solid fa-pen-nib absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>
                    @error('name') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Organization --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Assessed Organization') }}
                    </label>
                    <div class="relative">
                        <select name="organization_id" 
                                onchange="if(this.value === '__CREATE_ORGANIZATION__') { window.open('{{ route('admin.organizations.create') }}', '_blank'); this.value = ''; }"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none cursor-pointer">
                            <option value="">-- Independent Assessment --</option>
                            <option value="__CREATE_ORGANIZATION__" class="font-bold text-blue-600 bg-blue-50">+ {{ __('Add New Organization...') }}</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }} {{ $org->code ? "({$org->code})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-building absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                    @error('organization_id') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Deadline --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Audit Deadline') }}
                    </label>
                    <div class="relative">
                        <input type="date" name="deadline" value="{{ old('deadline') }}"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all @error('deadline') border-rose-400 @enderror">
                        <i class="fa-regular fa-calendar-check absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>
                    @error('deadline') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Card 2: PIC & Initial Status --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('PIC & Initial Status') }}</h2>
                    <p class="text-xs text-slate-400">{{ __('Assign primary PIC and set initial launch status') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- PIC / User --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Assigned PIC') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="user_id" required
                                onchange="if(this.value === '__CREATE_USER__') { window.open('{{ route('admin.users.create') }}', '_blank'); this.value = ''; }"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none cursor-pointer @error('user_id') border-rose-400 @enderror">
                            <option value="">-- Select Assigned User --</option>
                            <option value="__CREATE_USER__" class="font-bold text-blue-600 bg-blue-50">+ {{ __('Add New User...') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                    @if($user->role !== 'user') — {{ ucfirst($user->role) }} @endif
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-user-check absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                    @error('user_id') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Initial Status Stage') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="status" required
                                class="w-full pl-10 pr-4 py-3 bg-slate-50/70 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all appearance-none cursor-pointer @error('status') border-rose-400 @enderror">
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft (Preparatory / Private)</option>
                            <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Active (Public to Auditee)</option>
                        </select>
                        <i class="fa-solid fa-flag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                    @error('status') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Card 3: Additional Collaborators --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm space-y-4" x-data="{ search: '' }">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ __('Additional Users & Collaborators') }}</h2>
                        <p class="text-xs text-slate-400">{{ __('Allow co-users to view and evaluate this session') }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.users.create') }}" target="_blank" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200/80 rounded-xl text-xs font-bold transition-all">
                    <i class="fa-solid fa-user-plus text-[10px]"></i> {{ __('Add User') }}
                </a>
            </div>

            <div class="relative">
                <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="search" placeholder="Search team members by name or email..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
            </div>

            <div class="border border-slate-200/80 rounded-2xl overflow-hidden max-h-64 overflow-y-auto divide-y divide-slate-100">
                @forelse($users as $user)
                    <label class="user-checkbox-item flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50/80 transition-colors"
                        x-show="search === '' || '{{ strtolower($user->name) }} {{ strtolower($user->email) }}'.includes(search.toLowerCase())">
                        <input type="checkbox" name="invited_users[]" value="{{ $user->id }}"
                            {{ in_array($user->id, old('invited_users', [])) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $user->name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 border border-slate-200/60">
                            {{ $user->role }}
                        </span>
                    </label>
                @empty
                    <div class="px-4 py-6 text-center text-slate-400 text-xs">No users found.</div>
                @endforelse
            </div>
            @error('invited_users') <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Bottom Form Action Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.sessions.index') }}" 
               class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                {{ __('Cancel') }}
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold tracking-wide transition-all shadow-md shadow-blue-600/20 hover:scale-[1.02] active:scale-95 inline-flex items-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i> {{ __('Launch Audit Session') }}
            </button>
        </div>

    </form>
</div>
@endsection
