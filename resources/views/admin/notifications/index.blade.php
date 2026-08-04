@extends('layouts.admin')

@section('title', __('Admin Notification Center'))
@section('header_title', __('Notifications'))

@section('content')
<div class="space-y-3.5 pb-6 max-w-5xl mx-auto">

    {{-- Flash Success Alert --}}
    @if(session('success'))
    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-xs"></i>
            </div>
            <p class="text-[11px] font-bold text-emerald-800">{{ session('success') }}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold px-1.5 py-0.5">✕</button>
    </div>
    @endif

    {{-- Page Header (Compact) --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-800 text-white flex items-center justify-center shadow-md shadow-slate-900/20 shrink-0">
                <i class="fa-solid fa-bell text-sm text-blue-400"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5 mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-[8px] font-black text-blue-600 uppercase tracking-widest">{{ __('ADMIN CONTROL CENTER') }}</span>
                </div>
                <h2 class="text-base font-extrabold text-slate-800 font-heading tracking-tight leading-none">{{ __('Notification Center') }}</h2>
                <p class="text-[11px] text-slate-400 font-medium leading-none mt-1">{{ __('Stay informed about global audit progressions, CAPA PIC assignments, and organization updates.') }}</p>
            </div>
        </div>

        @if(auth()->user()->unreadNotifications->count() > 0)
        <form method="POST" action="{{ route('admin.notifications.read-all') }}" class="shrink-0">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] uppercase font-bold transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-check-double text-[10px]"></i>
                <span>{{ __('Mark All as Read') }}</span>
            </button>
        </form>
        @endif
    </div>

    {{-- Content Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Filters / Tabs (Compact) --}}
        <div class="p-3 border-b border-slate-200 bg-slate-50/80 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('admin.notifications.index') }}" 
                   class="px-3 py-1 text-[11px] font-bold rounded-lg transition-all {{ !$filter ? 'bg-blue-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-100' }}">
                    {{ __('All') }}
                </a>
                <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}" 
                   class="px-3 py-1 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1.5 {{ $filter === 'unread' ? 'bg-blue-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-100' }}">
                    <span>{{ __('Unread') }}</span>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="px-1.5 py-0.2 bg-rose-500 text-white text-[9px] font-black rounded-full leading-none">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.notifications.index', ['filter' => 'read']) }}" 
                   class="px-3 py-1 text-[11px] font-bold rounded-lg transition-all {{ $filter === 'read' ? 'bg-blue-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Read') }}
                </a>
            </div>
            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">AuditGuard Enterprise</span>
        </div>

        {{-- Notifications List (Compact Rows) --}}
        <div class="divide-y divide-slate-100 text-xs">
            {{-- 1. OVERDUE TASKS (GLOBAL) --}}
            @if(($filter === null || $filter === 'unread' || $filter === 'all') && isset($overdueTasks) && count($overdueTasks) > 0)
                @foreach($overdueTasks as $task)
                    <div class="p-3.5 sm:p-4 flex items-start gap-3 transition-all hover:bg-rose-50/40 bg-rose-50/20 border-l-4 border-rose-500">
                        <div class="shrink-0 mt-0.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-circle-exclamation text-xs"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 border border-rose-200 rounded text-[8px] font-black uppercase tracking-widest">
                                    {{ __('OVERDUE REMEDIATION TASK') }}
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block animate-pulse"></span>
                                @if($task->session && $task->session->organization)
                                    <span class="px-1.5 py-0.2 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[8px] font-bold">
                                        <i class="fa-solid fa-building text-[7px] text-slate-400 mr-0.5"></i>{{ $task->session->organization->name }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                <strong class="text-rose-600 font-extrabold mr-1">[{{ $task->standard->code }}]</strong> {{ __($task->standard->title) }}
                            </h4>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                                {{ __('PIC Assigned') }}: <strong class="text-slate-700 font-bold">{{ $task->treatment_pic ?: __('Unassigned') }}</strong>
                            </p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-[10px] font-medium">
                                <span class="flex items-center gap-1 text-rose-600 font-bold">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    {{ __('Overdue') }} {{ $task->treatment_due_date->diffForHumans() }}
                                </span>
                                <a href="{{ route('admin.capa.edit', ['capa' => $task->id]) }}" class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 group">
                                    <span>{{ __('Edit CAPA Item') }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- 2. UPCOMING DEADLINES (GLOBAL) --}}
            @if(($filter === null || $filter === 'unread' || $filter === 'all') && isset($upcomingTasks) && count($upcomingTasks) > 0)
                @foreach($upcomingTasks as $task)
                    <div class="p-3.5 sm:p-4 flex items-start gap-3 transition-all hover:bg-amber-50/40 bg-amber-50/15 border-l-4 border-amber-400">
                        <div class="shrink-0 mt-0.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 border border-amber-200 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-clock text-xs"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-200 rounded text-[8px] font-black uppercase tracking-widest">
                                    {{ __('UPCOMING DEADLINE') }}
                                </span>
                                @if($task->session && $task->session->organization)
                                    <span class="px-1.5 py-0.2 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[8px] font-bold">
                                        <i class="fa-solid fa-building text-[7px] text-slate-400 mr-0.5"></i>{{ $task->session->organization->name }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                <strong class="text-amber-600 font-extrabold mr-1">[{{ $task->standard->code }}]</strong> {{ __($task->standard->title) }}
                            </h4>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                                {{ __('PIC Assigned') }}: <strong class="text-slate-700 font-bold">{{ $task->treatment_pic ?: __('Unassigned') }}</strong>
                            </p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-[10px] font-medium">
                                <span class="flex items-center gap-1 text-amber-700 font-bold">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    {{ __('Due in') }} {{ $task->treatment_due_date->diffInDays(now()) }} {{ __('days') }} ({{ $task->treatment_due_date->format('d M Y') }})
                                </span>
                                <a href="{{ route('admin.capa.edit', ['capa' => $task->id]) }}" class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 group">
                                    <span>{{ __('Edit CAPA Item') }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- 3. STAGNANT SESSIONS (GLOBAL) --}}
            @if(($filter === null || $filter === 'unread' || $filter === 'all') && isset($stagnantSessions) && count($stagnantSessions) > 0)
                @foreach($stagnantSessions as $sess)
                    <div class="p-3.5 sm:p-4 flex items-start gap-3 transition-all hover:bg-blue-50/40 bg-blue-50/15 border-l-4 border-blue-400">
                        <div class="shrink-0 mt-0.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 border border-blue-200 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-calendar-minus text-xs"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 border border-blue-200 rounded text-[8px] font-black uppercase tracking-widest">
                                    {{ __('STAGNANT SESSION') }}
                                </span>
                                @if($sess->organization)
                                    <span class="px-1.5 py-0.2 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[8px] font-bold">
                                        <i class="fa-solid fa-building text-[7px] text-slate-400 mr-0.5"></i>{{ $sess->organization->name }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                {{ $sess->name }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-[10px] font-medium">
                                <span class="flex items-center gap-1 text-blue-600 font-bold">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    {{ __('No updates since') }} {{ $sess->updated_at->format('d M Y') }} ({{ $sess->updated_at->diffForHumans() }})
                                </span>
                                <a href="{{ route('admin.sessions.workspace', ['session' => $sess->id]) }}" class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 group">
                                    <span>{{ __('Open Admin Workspace') }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- 4. DATABASE NOTIFICATIONS --}}
            @forelse($notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $type = $notif->data['type'] ?? 'general';
                @endphp
                <div class="p-3.5 sm:p-4 flex items-start gap-3 transition-all hover:bg-slate-50/80 {{ $isUnread ? 'bg-blue-50/20' : '' }}">
                    {{-- Icon type --}}
                    <div class="shrink-0 mt-0.5">
                        @if($type === 'audit_session')
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-list-check text-xs"></i>
                            </div>
                        @elseif($type === 'corrective_action')
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-slate-50 text-slate-600 border border-slate-200 flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-info text-xs"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 mb-1">
                            @if($type === 'audit_session')
                                <span class="px-1.5 py-0.2 bg-blue-50 text-blue-700 border border-blue-100 rounded text-[8px] font-black uppercase tracking-widest">
                                    {{ __('Audit Session') }}
                                </span>
                            @elseif($type === 'corrective_action')
                                <span class="px-1.5 py-0.2 bg-amber-50 text-amber-700 border border-amber-100 rounded text-[8px] font-black uppercase tracking-widest">
                                    {{ __('Improvement Tracking') }}
                                </span>
                            @else
                                <span class="px-1.5 py-0.2 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[8px] font-black uppercase tracking-widest">
                                    {{ __('System Alert') }}
                                </span>
                            @endif

                            @if($isUnread)
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block animate-pulse" title="{{ __('Unread') }}"></span>
                            @endif
                        </div>

                        <h4 class="text-xs font-bold text-slate-900 leading-snug">
                            {{ $notif->data['message'] ?? '' }}
                        </h4>

                        {{-- Metadata & Actions --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-[10px] font-medium text-slate-400">
                            <span class="flex items-center gap-1 text-slate-500 font-semibold">
                                <i class="fa-regular fa-clock text-[10px]"></i>
                                {{ $notif->created_at->diffForHumans() }}
                            </span>

                            @if($type === 'audit_session' && !empty($notif->data['session_id']))
                                <a href="{{ route('admin.sessions.workspace', ['session' => $notif->data['session_id']]) }}" class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 group">
                                    <span>{{ __('Open Admin Workspace') }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                </a>
                            @elseif($type === 'corrective_action' && !empty($notif->data['session_id']))
                                @if(!empty($notif->data['result_id']))
                                    <a href="{{ route('admin.capa.edit', ['capa' => $notif->data['result_id']]) }}" class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 group">
                                        <span>{{ __('Edit CAPA Item') }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.sessions.workspace', ['session' => $notif->data['session_id']]) }}" class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 group">
                                        <span>{{ __('Open Admin Workspace') }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
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
                                <button type="submit" title="{{ __('Mark as read') }}" class="w-7 h-7 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 flex items-center justify-center transition-all border border-transparent hover:border-blue-100">
                                    <i class="fa-solid fa-envelope-open text-[10px]"></i>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.notifications.unread', $notif->id) }}">
                                @csrf
                                <button type="submit" title="{{ __('Mark as unread') }}" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-blue-600 flex items-center justify-center transition-all border border-transparent hover:border-slate-200">
                                    <i class="fa-solid fa-envelope text-[10px]"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                {{-- EMPTY STATE --}}
                @php
                    $hasOverdue = isset($overdueTasks) && count($overdueTasks) > 0;
                    $hasUpcoming = isset($upcomingTasks) && count($upcomingTasks) > 0;
                    $hasStagnant = isset($stagnantSessions) && count($stagnantSessions) > 0;
                    $showEmpty = ($filter === 'read') || (!$hasOverdue && !$hasUpcoming && !$hasStagnant);
                @endphp
                @if($showEmpty)
                <div class="py-10 text-center px-4">
                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-slate-200/60 shadow-sm text-slate-400">
                        <i class="fa-regular fa-bell-slash text-lg"></i>
                    </div>
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest">{{ __('No Notifications Found') }}</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ __('You are all caught up! There are no alerts in this view.') }}</p>
                </div>
                @endif
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
