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
            {{-- User Badge --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20 text-lg font-black">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="leading-none">
                    <p class="font-black text-slate-900 text-sm">{{ $user->name }}</p>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $user->organization_name ?? 'No organization set' }}</p>
                    <span class="mt-1 inline-flex px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md text-[8px] font-black uppercase tracking-widest border border-blue-100">{{ __('Assessor') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Statistics Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @php
            $profileStats = [
                ['label' => 'Audit Sessions', 'value' => $auditStats['total_sessions'], 'icon' => 'fa-layer-group', 'color' => 'blue'],
                ['label' => 'Avg Maturity', 'value' => number_format($auditStats['avg_maturity'], 2) . ' / 5', 'icon' => 'fa-gauge-high', 'color' => 'indigo'],
                ['label' => 'Compliance Score', 'value' => $auditStats['compliance_score'] . '%', 'icon' => 'fa-chart-line', 'color' => 'emerald'],
                ['label' => 'Controls Assessed', 'value' => $auditStats['total_controls'], 'icon' => 'fa-circle-check', 'color' => 'green'],
                ['label' => 'Templates Shared', 'value' => $auditStats['community_shared'], 'icon' => 'fa-users', 'color' => 'purple'],
            ];
            $colorMap = [
                'blue'   => ['bg' => 'bg-blue-50',   'text' => 'text-blue-600',   'icon' => 'bg-blue-100'],
                'indigo' => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'icon' => 'bg-indigo-100'],
                'emerald'=> ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'icon' => 'bg-emerald-100'],
                'green'  => ['bg' => 'bg-green-50',   'text' => 'text-green-600',   'icon' => 'bg-green-100'],
                'purple' => ['bg' => 'bg-purple-50',  'text' => 'text-purple-600',  'icon' => 'bg-purple-100'],
            ];
        @endphp
        @foreach($profileStats as $stat)
        @php $c = $colorMap[$stat['color']]; @endphp
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-tight">{{ $stat['label'] }}</p>
                <div class="w-7 h-7 {{ $c['icon'] }} {{ $c['text'] }} rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $stat['icon'] }} text-[10px]"></i>
                </div>
            </div>
            <p class="text-xl font-black {{ $c['text'] }} tracking-tight">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-5">
        {{-- Profile Section --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100">
                    <i class="fa-solid fa-address-card text-base"></i>
                </div>
                <div class="leading-none">
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">{{ __('Profile Details') }}</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('Update your personal information and organization settings.') }}</p>
                </div>
            </div>
            <div class="max-w-2xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Password Section --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center border border-slate-200">
                    <i class="fa-solid fa-key text-base"></i>
                </div>
                <div class="leading-none">
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">{{ __('Change Password') }}</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('Update your account password to maintain security.') }}</p>
                </div>
            </div>
            <div class="max-w-2xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</div>
@endsection
