@extends('layouts.admin')

@section('title', __('Admin Notification Center'))
@section('header_title', __('Notifications'))

@section('content')
<div class="space-y-6 pb-8">
{{-- Page Header --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">{{ __('Notification Center') }}</h2>
        <p class="text-sm text-slate-500">{{ __('Stay informed about global audit progressions, CAPA PIC assignments, and compliance updates.') }}</p>
    </div>
    @if(auth()->user()->unreadNotifications->count() > 0)
    <form method="POST" action="{{ route('admin.notifications.read-all') }}" class="shrink-0">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold transition-colors shadow-sm self-start">
            <i class="fa-solid fa-check-double text-xs"></i>
            <span>{{ __('Mark All as Read') }}</span>
        </button>
    </form>
    @endif
</div>

    {{-- Content Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Filters / Tabs --}}
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.notifications.index') }}" 
                   class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ !$filter ? 'bg-blue-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-100' }}">
                    {{ __('All') }}
                </a>
                <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}" 
                   class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'unread' ? 'bg-blue-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Unread') }}
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="ml-1.5 px-2 py-0.5 bg-rose-500 text-white text-[9px] font-black rounded-md leading-none">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.notifications.index', ['filter' => 'read']) }}" 
                   class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'read' ? 'bg-blue-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Read') }}
                </a>
            </div>
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">AuditGuard Control Center</span>
        </div>

        {{-- Notifications List --}}
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $type = $notif->data['type'] ?? 'general';
                @endphp
                <div class="p-6 flex items-start gap-4 transition-all hover:bg-slate-50/50 {{ $isUnread ? 'bg-blue-50/5' : '' }}">
                    {{-- Icon type --}}
                    <div class="shrink-0 mt-0.5">
                        @if($type === 'audit_session')
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-list-check text-base"></i>
                            </div>
                        @elseif($type === 'corrective_action')
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-triangle-exclamation text-base"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-info text-base"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            @if($type === 'audit_session')
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-[9px] font-black uppercase tracking-widest">
                                    {{ __('Audit Session') }}
                                </span>
                            @elseif($type === 'corrective_action')
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-md text-[9px] font-black uppercase tracking-widest">
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
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2.5 text-xs text-slate-400 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-sm"></i>
                                {{ $notif->created_at->diffForHumans() }}
                            </span>

                            @if($type === 'audit_session' && !empty($notif->data['session_id']))
                                <a href="{{ route('admin.sessions.workspace', ['session' => $notif->data['session_id']]) }}" class="text-blue-600 hover:text-blue-700 font-black uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    {{ __('Open Admin Workspace') }}
                                </a>
                            @elseif($type === 'corrective_action' && !empty($notif->data['session_id']))
                                @if(!empty($notif->data['result_id']))
                                    <a href="{{ route('admin.capa.edit', ['capa' => $notif->data['result_id']]) }}" class="text-blue-600 hover:text-blue-700 font-black uppercase tracking-wider text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        {{ __('Edit CAPA Item') }}
                                    </a>
                                @else
                                    <a href="{{ route('admin.sessions.workspace', ['session' => $notif->data['session_id']]) }}" class="text-blue-600 hover:text-blue-700 font-black uppercase tracking-wider text-[10px] flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        {{ __('Open Admin Workspace') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Actions (Read/Unread status) --}}
                    <div class="shrink-0 self-center">
                        @if($isUnread)
                            <form method="POST" action="{{ route('admin.notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" title="{{ __('Mark as read') }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-blue-600 flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-envelope-open text-sm"></i>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.notifications.unread', $notif->id) }}">
                                @csrf
                                <button type="submit" title="{{ __('Mark as unread') }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-blue-600 flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-envelope text-sm"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-16 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100 text-slate-300">
                        <i class="fa-regular fa-bell-slash text-2xl"></i>
                    </div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ __('No Notifications') }}</h3>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-1">{{ __('You are all caught up!') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
