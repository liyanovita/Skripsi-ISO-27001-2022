@extends('layouts.admin')

@section('title', 'My Profile Settings')
@section('header_title', 'My Profile & Settings')

@section('content')
<style>
    .form-input { transition: border-color 0.15s, box-shadow 0.15s; }
    .form-input:focus { box-shadow: 0 0 0 3px rgba(59,130,246,0.12); border-color: #60a5fa; outline: none; background: #fff; }
    .section-card { background: #fff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 4px 0 rgba(30,58,138,0.04); }
</style>

<div class="space-y-6 pb-8">

    {{-- Profile Header Card --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-700 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-blue-600/20 shrink-0">
                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                </div>
                
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-lg font-black text-slate-800 tracking-tight">{{ $admin->name }}</span>
                        <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-[10px] font-black uppercase tracking-wider">
                            <i class="fa-solid fa-shield-halved text-[8px]"></i> Administrator
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">{{ $admin->email }}</p>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs shrink-0 space-y-1">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">System Role</span>
                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-shield text-blue-600 text-xs"></i>
                    Administrator
                </div>
                <div class="text-[10px] text-slate-400 font-medium">Joined {{ $admin->created_at->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Profile Information Card --}}
        <div class="section-card p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2 mb-5">
                    <i class="fa-solid fa-user-gear text-blue-500"></i> Admin Display & Account Info
                </h3>

                <form id="admin-profile-form" method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Full Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                            class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        @error('name') <p class="text-xs text-red-500 mt-1 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                    </div>

                    {{-- Email Address --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                            class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        @error('email') <p class="text-xs text-red-500 mt-1 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                    </div>
                </form>
            </div>

            <div class="flex justify-end pt-5 mt-6 border-t border-slate-100">
                <button type="submit" form="admin-profile-form"
                    class="px-6 py-2.5 bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20 flex items-center gap-2 rounded-xl">
                    <i class="fa-solid fa-floppy-disk text-xs"></i> Save Profile
                </button>
            </div>
        </div>

        {{-- Change Password Card --}}
        <div class="section-card p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-key text-amber-500"></i> Change Account Password
                </h3>
                <p class="text-xs text-slate-400 font-medium mb-5">For security, use a strong password of at least 8 characters.</p>

                <form id="admin-password-form" method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Current Password --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Current Password <span class="text-red-400">*</span></label>
                        <input type="password" name="current_password" required placeholder="••••••••"
                            class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        @error('current_password') <p class="text-xs text-red-500 mt-1 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">New Password <span class="text-red-400">*</span></label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        @error('password') <p class="text-xs text-red-500 mt-1 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Confirm New Password <span class="text-red-400">*</span></label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                            class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                </form>
            </div>

            <div class="flex justify-end pt-5 mt-6 border-t border-slate-100">
                <button type="submit" form="admin-password-form"
                    class="px-6 py-2.5 bg-amber-500 text-white text-sm font-bold hover:bg-amber-600 active:scale-95 transition-all shadow-md shadow-amber-500/20 flex items-center gap-2 rounded-xl">
                    <i class="fa-solid fa-lock text-xs"></i> Update Password
                </button>
            </div>
        </div>

    </div>

</div>
@endsection
