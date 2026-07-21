@extends('layouts.app')
@section('title', 'Profile')
@section('view_name', 'Profile Management')

@section('content')
<div class="max-w-5xl mx-auto space-y-5 pb-8">
    {{-- Header --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-40 h-40 bg-blue-600/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                    <i class="fa-solid fa-user-shield text-base"></i>
                </div>
                <div class="leading-none">
                    <div class="flex items-center gap-2 mb-0.5">
                        <div class="w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                        <span class="text-[9px] font-bold text-blue-600 uppercase tracking-widest">{{ __('Profile Settings') }}</span>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tighter">User & Organization Profile</h2>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Configure your personal details, organization, and ISMS scope.') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Profile Section --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm lg:col-span-2">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100">
                    <i class="fa-solid fa-address-card text-base"></i>
                </div>
                <div class="leading-none">
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">{{ __('Profile Details') }}</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('Update your personal information and organization settings.') }}</p>
                </div>
            </div>
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Password Section --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm lg:col-span-1">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center border border-slate-200">
                    <i class="fa-solid fa-key text-base"></i>
                </div>
                <div class="leading-none">
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">{{ __('Change Password') }}</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('Update your account password to maintain security.') }}</p>
                </div>
            </div>
            @include('profile.partials.update-password-form')
        </div>
    </div>
</div>
@endsection
