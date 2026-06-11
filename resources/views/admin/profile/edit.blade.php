@extends('layouts.admin')

@section('title', 'My Profile')
@section('header_title', 'My Profile & Settings')

@section('content')
<div class="max-w-2xl">

    {{-- Profile Info Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-5 border-b border-slate-200 bg-slate-50">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-user-gear text-blue-600"></i> Profile Information
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">Update admin display name and email address.</p>
        </div>
        <form method="POST" action="{{ route('admin.profile.update') }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-5 mb-6 pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-2xl font-black shrink-0">
                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                </div>
                <div>
                    <p class="font-bold text-slate-800 text-lg">{{ $admin->name }}</p>
                    <p class="text-sm text-slate-500">{{ $admin->email }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase mt-1">
                        <i class="fa-solid fa-shield-halved mr-1"></i> Administrator
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        required>
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Save Profile
                </button>
            </div>
        </form>
    </div>

    {{-- Password Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-key text-amber-500"></i> Change Password
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">For security, use a strong password of at least 8 characters.</p>
        </div>
        <form method="POST" action="{{ route('admin.profile.password') }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-5 mb-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Current Password <span class="text-red-500">*</span></label>
                    <input type="password" name="current_password"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        required>
                    @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">New Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        required>
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Confirm New Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        required>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit"
                    class="px-6 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-bold hover:bg-amber-600 shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-lock"></i> Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
