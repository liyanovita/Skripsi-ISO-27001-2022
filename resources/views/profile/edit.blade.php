@extends('layouts.app')
@section('title', 'Profile & Account Settings')
@section('view_name', 'Profile Settings')

@section('content')
<div class="space-y-6 pb-8">

    {{-- Profile Header Card --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-48 h-48 bg-blue-600/5 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-slate-900/20 shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-lg font-black text-slate-800 tracking-tight">{{ $user->name }}</span>
                        @if($user->isAdmin())
                            <span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-full text-[10px] font-black uppercase tracking-wider">
                                <i class="fa-solid fa-shield-halved text-[8px]"></i> Admin
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-wider">User</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 font-medium">{{ $user->email }}</p>
                    
                    @if($user->job_title)
                        <div class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold border border-blue-100">
                            <i class="fa-solid fa-briefcase text-[10px]"></i> {{ $user->job_title }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Organization Tag --}}
            @if($user->organization)
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs shrink-0 space-y-1">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Organization</span>
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-building text-blue-500 text-xs"></i>
                        {{ $user->organization->name }}
                    </div>
                    <div class="text-[10px] text-slate-400 font-medium">
                        {{ $user->organization->business_sector ?? 'N/A' }} • {{ $user->organization->organization_scale ?? 'Scale N/A' }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Main Form Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Profile & Organization Info Form (2 cols) --}}
        <div class="lg:col-span-2">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Security / Change Password Form (1 col) --}}
        <div class="lg:col-span-1">
            @include('profile.partials.update-password-form')
        </div>

    </div>

</div>
@endsection
