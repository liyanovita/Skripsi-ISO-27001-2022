@extends('layouts.admin')

@section('title', 'Add User')
@section('header_title', 'Add User')

@section('content')
<style>
    .form-input { transition: border-color 0.15s, box-shadow 0.15s; }
    .form-input:focus { box-shadow: 0 0 0 3px rgba(139,92,246,0.1); border-color: #c4b5fd; outline: none; background: #fff; }
    .section-card { background: #fff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 4px 0 rgba(30,58,138,0.04); }
</style>

<div class="max-w-2xl">
    {{-- Back --}}
    <a href="{{ route('admin.users.index') }}"
        class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-violet-600 transition-colors mb-5 font-medium group">
        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to Users
    </a>

    {{-- Page Title --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-violet-600/20">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <div>
            <h2 class="text-lg font-black text-slate-800">Add New User</h2>
            <p class="text-xs text-slate-400 font-medium">Create an account and assign their role and organization</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5" x-data="{ role: '{{ old('role', 'user') }}' }">
        @csrf

        {{-- Identity --}}
        <div class="section-card p-6 space-y-4">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-id-card text-violet-400"></i> Account Identity
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="e.g., John Doe"
                            class="form-input w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    @error('name') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="e.g., john@example.com"
                            class="form-input w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    @error('email') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Password --}}
        <div class="section-card p-6 space-y-4"
            x-data="{ password: '', password_confirmation: '', showPass: false, generate() {
                const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
                let pass = '';
                for (let i = 0; i < 12; i++) { pass += chars.charAt(Math.floor(Math.random() * chars.length)); }
                this.password = pass; this.password_confirmation = pass; this.showPass = true;
            }}">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-lock text-violet-400"></i> Password
                </h3>
                <button type="button" @click="generate()"
                    class="text-[11px] font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1.5 bg-amber-50 px-2.5 py-1.5 rounded-lg hover:bg-amber-100 transition-colors">
                    <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> Auto Generate
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="password" required
                            class="form-input w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <button type="button" @click="showPass = !showPass"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fa-solid text-sm" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Confirm Password <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                        <input :type="showPass ? 'text' : 'password'" name="password_confirmation" x-model="password_confirmation" required
                            class="form-input w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                </div>
            </div>
        </div>

        {{-- Role & Status --}}
        <div class="section-card p-6 space-y-4">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-violet-400"></i> Role & Access
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Role <span class="text-red-400">*</span></label>
                    <select name="role" x-model="role" required class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                        <option value="user">User</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Account Status <span class="text-red-400">*</span></label>
                    <select name="status" required class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                        <option value="active"    {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Organization --}}
        <div class="section-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-building text-violet-400"></i> Organization
                    <span class="text-[9px] font-medium text-slate-300 normal-case tracking-normal">Optional</span>
                </h3>
                <a href="{{ route('admin.organizations.create') }}"
                    class="text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1.5 bg-blue-50 px-2.5 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fa-solid fa-plus-circle text-[10px]"></i> Add New Organization
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div :class="role === 'user' ? '' : 'sm:col-span-2'">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Organization</label>
                    <select name="organization_id" class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                        <option value="">— No organization —</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}{{ $org->code ? ' (' . $org->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('organization_id') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
                <div x-show="role === 'user'">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Position / Job Title</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}"
                        placeholder="e.g. CISO, IT Auditor, Risk Manager"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('job_title') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
            </div>
            <div x-show="role === 'user'">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Role Description & Responsibilities</label>
                <textarea name="role_description" rows="2"
                    placeholder="Describe user's role and security responsibilities within the organization…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('role_description') }}</textarea>
                @error('role_description') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.users.index') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-bold hover:bg-violet-700 active:scale-95 transition-all shadow-md shadow-violet-600/20 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-xs"></i> Create User
            </button>
        </div>
    </form>
</div>
@endsection
