@extends('layouts.admin')

@section('title', $user->name . ' — User Detail')
@section('header_title', 'User Detail')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Back to Users
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white flex items-center justify-center font-bold text-2xl mb-4">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h2 class="text-xl font-black text-slate-800">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-3">
                    @if($user->isAdmin())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold uppercase tracking-widest">
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold uppercase tracking-widest">User</span>
                    @endif
                    @if($user->isActive())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Suspended
                        </span>
                    @endif
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Organization</span>
                    <span class="font-bold text-slate-800">{{ $user->organization_name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Sector</span>
                    <span class="font-bold text-slate-800">{{ $user->business_sector ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Scale</span>
                    <span class="font-bold text-slate-800">{{ $user->organization_scale ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Joined</span>
                    <span class="font-bold text-slate-800">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-500">Provider</span>
                    <span class="font-bold text-slate-800">{{ $user->provider ? ucfirst($user->provider) : 'Email' }}</span>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors text-center flex items-center justify-center gap-2">
                    <i class="fa-solid fa-pen"></i> Edit User
                </a>
                @if($user->id !== auth()->id())
                <div x-data="{ showConfirm: false }">
                    <button
                        type="button"
                        @click="showConfirm = true"
                        x-show="!showConfirm"
                        class="w-full px-4 py-2.5 rounded-lg text-sm font-bold transition-colors text-center flex items-center justify-center gap-2 {{ $user->isActive() ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}"
                    >
                        <i class="fa-solid {{ $user->isActive() ? 'fa-ban' : 'fa-check' }}"></i>
                        {{ $user->isActive() ? 'Suspend User' : 'Activate User' }}
                    </button>
                    <div x-show="showConfirm" x-cloak class="rounded-lg border {{ $user->isActive() ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }} p-3">
                        <p class="text-xs font-bold {{ $user->isActive() ? 'text-amber-700' : 'text-emerald-700' }} mb-2 text-center">
                            {{ $user->isActive() ? 'Suspend user ini?' : 'Aktifkan user ini?' }}
                        </p>
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="flex gap-2">
                            @csrf @method('PATCH')
                            <button type="submit" class="flex-1 px-3 py-1.5 text-xs font-bold rounded {{ $user->isActive() ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                Ya, Konfirmasi
                            </button>
                            <button type="button" @click="showConfirm = false" class="flex-1 px-3 py-1.5 text-xs font-bold rounded bg-slate-200 text-slate-700 hover:bg-slate-300">
                                Batal
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Activity Stats --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-blue-500"></i> Activity Stats
            </h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-black text-blue-600">{{ $user->assessment_sessions_count }}</div>
                    <div class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mt-1">Sessions</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-black text-purple-600">{{ $user->community_templates_count }}</div>
                    <div class="text-[10px] font-bold text-purple-500 uppercase tracking-widest mt-1">Templates</div>
                </div>
                <div class="text-center p-3 bg-slate-50 rounded-lg">
                    <div class="text-2xl font-black text-slate-600">{{ $user->audit_trails_count }}</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Actions</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions History --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-list text-emerald-500"></i> Audit Sessions
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $sessions->count() }}</span>
                </h3>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($sessions as $session)
                <div class="p-5 hover:bg-slate-50 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-bold text-slate-800">{{ $session->name }}</h4>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($session->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ str_replace('_', ' ', $session->status) }}
                                </span>
                                <span class="text-xs text-slate-400">{{ $session->results_count }} controls</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black {{ $session->overall_maturity_score >= 4 ? 'text-emerald-600' : ($session->overall_maturity_score >= 2.5 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ number_format($session->overall_maturity_score, 1) }}
                            </div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-widest">Maturity</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Created {{ $session->created_at->format('M d, Y') }}</span>
                        <span>Updated {{ $session->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-slate-500">
                    <i class="fa-solid fa-clipboard text-3xl mb-3 text-slate-300"></i>
                    <p class="text-sm">This user has not created any audit sessions yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
