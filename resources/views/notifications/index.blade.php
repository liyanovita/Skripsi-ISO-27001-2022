@extends('layouts.app')
@section('title', __('Notification Center'))
@section('view_name', __('Notification Center'))

@section('content')
<div class="max-w-5xl mx-auto space-y-5 pb-8">
    {{-- Header --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-40 h-40 bg-indigo-600/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <i class="fa-solid fa-bell text-base"></i>
                </div>
                <div class="leading-none">
                    <div class="flex items-center gap-2 mb-0.5">
                        <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></div>
                        <span class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest">{{ __('Alerts & Info') }}</span>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tighter">{{ __('Notification Center') }}</h2>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Stay updated with audit assignments and CAPA follow-up reminders.') }}</p>
                </div>
            </div>
            
            {{-- Quick Action (Mark All as Read) --}}
            @if(auth()->user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}" class="shrink-0">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 text-indigo-600 font-black text-[10px] uppercase tracking-wider rounded-xl transition-all active:scale-95">
                    <i class="fa-solid fa-check-double text-xs"></i>
                    <span>{{ __('Mark All as Read') }}</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Filters & Content --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {{-- Tabs --}}
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('notifications.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all {{ !$filter ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    {{ __('All') }}
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all {{ $filter === 'unread' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    {{ __('Unread') }}
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 bg-rose-500 text-white text-[8px] font-black rounded-md leading-none">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                   class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all {{ $filter === 'read' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    {{ __('Read') }}
                </a>
            </div>
            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">ISO 27001:2022</span>
        </div>

        {{-- Notifications List --}}
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $type = $notif->data['type'] ?? 'general';
                @endphp
                <div class="p-5 flex items-start gap-4 transition-colors hover:bg-slate-50/50 {{ $isUnread ? 'bg-blue-50/10' : '' }}">
                    {{-- Icon type --}}
                    <div class="shrink-0 mt-0.5">
                        @if($type === 'audit_session')
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-list-check text-sm"></i>
                            </div>
                        @elseif($type === 'corrective_action')
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                            </div>
                        @else
                            <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-info text-sm"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            @if($type === 'audit_session')
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-[8px] font-black uppercase tracking-widest">
                                    {{ __('Audit Session') }}
                                </span>
                            @elseif($type === 'corrective_action')
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-md text-[8px] font-black uppercase tracking-widest">
                                    {{ __('Improvement Tracking') }}
                                </span>
                            @endif

                            @if($isUnread)
                                <span class="w-2 h-2 rounded-full bg-rose-500 inline-block animate-pulse"></span>
                            @endif
                        </div>

                        <p class="text-sm font-bold text-slate-800 leading-snug">
                            {{ $notif->data['message'] ?? '' }}
                        </p>

                        {{-- Metadata --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[10px] text-slate-400 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-xs"></i>
                                {{ $notif->created_at->diffForHumans() }}
                            </span>

                            @if($type === 'audit_session' && !empty($notif->data['session_id']))
                                <a href="{{ route('workspace.index', ['session_id' => $notif->data['session_id']]) }}" class="text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    {{ __('Open Assessment') }}
                                </a>
                            @elseif($type === 'corrective_action' && !empty($notif->data['session_id']))
                                <a href="{{ route('workspace.index', ['session_id' => $notif->data['session_id'], 'focus' => $notif->data['result_id'] ?? null]) }}" class="text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    {{ __('View Action Item') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Actions (Read/Unread status toggler) --}}
                    <div class="shrink-0 self-center">
                        @if($isUnread)
                            <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" title="{{ __('Mark as read') }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-envelope-open text-xs"></i>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('notifications.unread', $notif->id) }}">
                                @csrf
                                <button type="submit" title="{{ __('Mark as unread') }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-envelope text-xs"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-slate-100">
                        <i class="fa-regular fa-bell-slash text-xl text-slate-300"></i>
                    </div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('No Notifications') }}</h3>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-1">{{ __('You are all caught up!') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
