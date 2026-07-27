@extends('layouts.admin')

@section('title', $user->name . ' — User Detail')
@section('header_title', 'User Detail')

@section('content')
<style>
    .info-card { transition: box-shadow 0.2s ease; }
    .info-card:hover { box-shadow: 0 6px 24px -6px rgba(99,102,241,0.1); }
    .session-card { transition: background 0.15s ease; }
    .session-card:hover { background: rgba(99,102,241,0.02); }
</style>

<div class="space-y-6 pb-8">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.users.index') }}" class="hover:text-violet-600 transition-colors font-medium flex items-center gap-1">
            <i class="fa-solid fa-users text-xs"></i> Users
        </a>
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
        <span class="font-bold text-slate-800 truncate">{{ $user->name }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Profile --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden info-card">
                {{-- Banner --}}
                <div class="h-20 bg-gradient-to-br from-violet-600 to-indigo-700 relative">
                    <div class="absolute -bottom-8 left-1/2 -translate-x-1/2">
                        <div class="w-16 h-16 rounded-2xl {{ $user->isAdmin() ? 'bg-gradient-to-br from-violet-500 to-indigo-600' : 'bg-gradient-to-br from-blue-500 to-cyan-600' }} text-white flex items-center justify-center font-black text-xl border-4 border-white shadow-lg">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>
                </div>

                <div class="pt-12 pb-6 px-5 text-center">
                    <h2 class="text-lg font-black text-slate-800 mt-1">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</p>
                    @if(!$user->isAdmin() && $user->job_title)
                        <div class="mt-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold border border-blue-100">
                                <i class="fa-solid fa-briefcase text-[10px]"></i> {{ $user->job_title }}
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center justify-center gap-2 mt-3">
                        @if($user->isAdmin())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-100 text-violet-700 rounded-full text-[10px] font-black uppercase tracking-widest">
                                <i class="fa-solid fa-shield-halved text-[8px]"></i> Admin
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest">User</span>
                        @endif
                        @if($user->isActive())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-100 text-red-600 rounded-full text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Suspended
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Info rows --}}
                <div class="px-5 pb-4 space-y-0 border-t border-slate-50">
                    @php
                        $rows = [];
                        if (!$user->isAdmin()) {
                            $rows[] = ['label' => 'Position', 'value' => $user->job_title ?? '—'];
                        }
                        $rows[] = ['label' => 'Organization', 'value' => $user->organization?->name ?? '—'];
                        $rows[] = ['label' => 'Sector',       'value' => $user->organization?->business_sector ?? '—'];
                        $rows[] = ['label' => 'Scale',        'value' => $user->organization?->organization_scale ?? '—'];
                        $rows[] = ['label' => 'Joined',       'value' => $user->created_at->format('M d, Y')];
                        $rows[] = ['label' => 'Auth Method',  'value' => $user->provider ? ucfirst($user->provider) : 'Email / Password'];
                    @endphp
                    @foreach($rows as $row)
                    <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 gap-3">
                        <span class="text-xs text-slate-400 font-medium shrink-0">{{ $row['label'] }}</span>
                        <span class="text-xs font-bold text-slate-700 text-right truncate">{{ $row['value'] }}</span>
                    </div>
                    @endforeach

                    @if(!$user->isAdmin() && $user->role_description)
                    <div class="pt-3 pb-1 border-t border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Role Description</span>
                        <p class="text-xs text-slate-600 font-medium leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            {{ $user->role_description }}
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="px-5 pb-5 space-y-2">
                    <a href="{{ route('admin.users.edit', $user) }}"
                        class="w-full px-4 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 active:scale-95 transition-all text-center flex items-center justify-center gap-2 shadow-md shadow-violet-600/20">
                        <i class="fa-solid fa-pen text-xs"></i> Edit Profile
                    </a>
                    @if($user->id !== auth()->id())
                    <div x-data="{ showConfirm: false }">
                        <button type="button" @click="showConfirm = true" x-show="!showConfirm"
                            class="w-full px-4 py-2.5 rounded-xl text-sm font-bold transition-colors text-center flex items-center justify-center gap-2
                                {{ $user->isActive() ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-100' }}">
                            <i class="fa-solid {{ $user->isActive() ? 'fa-ban' : 'fa-check' }} text-xs"></i>
                            {{ $user->isActive() ? 'Suspend User' : 'Activate User' }}
                        </button>
                        <div x-show="showConfirm" x-cloak
                            class="rounded-xl border p-3 {{ $user->isActive() ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }}">
                            <p class="text-xs font-bold {{ $user->isActive() ? 'text-amber-700' : 'text-emerald-700' }} mb-2 text-center">
                                {{ $user->isActive() ? 'Suspend this user?' : 'Activate this user?' }}
                            </p>
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="flex gap-2">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="flex-1 px-3 py-1.5 text-xs font-bold rounded-lg {{ $user->isActive() ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                    Yes, Confirm
                                </button>
                                <button type="button" @click="showConfirm = false"
                                    class="flex-1 px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Activity Stats --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 info-card">
                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-violet-400"></i> Activity Stats
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-4 bg-violet-50 rounded-2xl">
                        <div class="text-2xl font-black text-violet-600">{{ $user->assessment_sessions_count }}</div>
                        <div class="text-[9px] font-black text-violet-400 uppercase tracking-widest mt-1">Sessions</div>
                    </div>
                    <div class="text-center p-4 bg-slate-50 rounded-2xl">
                        <div class="text-2xl font-black text-slate-600">{{ $user->audit_trails_count }}</div>
                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Actions</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Sessions + Logs --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Audit Sessions --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-emerald-500"></i> Audit Sessions
                        <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $sessions->count() }}</span>
                    </h3>
                </div>

                <div class="divide-y divide-slate-50">
                    @forelse($sessions as $session)
                    <div class="session-card px-5 py-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">{{ $session->name }}</h4>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wide
                                        {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($session->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ str_replace('_', ' ', $session->status) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ $session->results_count }} controls assessed</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-2xl font-black {{ $session->overall_maturity_score >= 4 ? 'text-emerald-600' : ($session->overall_maturity_score >= 2.5 ? 'text-amber-600' : 'text-red-500') }}">
                                    {{ number_format($session->overall_maturity_score, 1) }}
                                </div>
                                <div class="text-[9px] text-slate-400 uppercase tracking-widest">Maturity</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Created {{ $session->created_at->format('M d, Y') }}</span>
                            <a href="{{ route('admin.sessions.show', $session) }}"
                                class="text-blue-500 hover:text-blue-700 font-bold flex items-center gap-1 transition-colors">
                                View <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-clipboard text-2xl text-slate-200"></i>
                        </div>
                        <p class="text-slate-400 text-sm">No audit sessions yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Activity Logs --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-400"></i> Recent Activity
                        <span class="text-[10px] font-bold text-slate-400 normal-case tracking-normal">Last 5 actions</span>
                    </h3>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recentLogs as $log)
                    <div class="px-5 py-3.5 hover:bg-slate-50/60 transition-colors">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-xs font-bold text-slate-700">{{ $log->action }}</span>
                            <span class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        @if($log->field_changed)
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            Changed <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-[10px] text-slate-600">{{ friendly_field_label($log->field_changed) }}</code>
                            @if($log->old_value !== null || $log->new_value !== null)
                                from <span class="line-through text-red-400">{{ Str::limit($log->old_value, 40) }}</span>
                                to <span class="font-bold text-emerald-600">{{ Str::limit($log->new_value, 40) }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="py-12 text-center">
                        <i class="fa-solid fa-clock-rotate-left text-2xl text-slate-200 mb-2 block"></i>
                        <p class="text-[11px] font-bold text-slate-300 uppercase tracking-widest">No activity recorded</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
