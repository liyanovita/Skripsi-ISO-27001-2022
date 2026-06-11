<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OpenAudit-27001:2022') | ISO 27001:2022 Compliance</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@hotwired/turbo@7.3.0/dist/turbo.es2017-umd.js"></script>
    {{-- Chart.js hanya di-load jika halaman membutuhkannya --}}
    @stack('head_scripts')
    @stack('styles')
    
    <style>
        .turbo-progress-bar { display: none !important; }
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; scroll-behavior: smooth; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        .sidebar-text { white-space: nowrap; }
        a, button { transition: all 0.2s ease; }
        .turbo-loading #main-content { opacity: 0.7; pointer-events: none; transition: opacity 0.1s ease; }

        /* Quick Search */
        #quick-search-input:focus { outline: none; }
        .search-result-item:hover { background: #f1f5f9; }
        .search-result-item.active { background: #eef2ff; }

        /* Sidebar tooltip for collapsed state */
        .sidebar-tooltip {
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: #f8fafc;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 8px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            z-index: 999;
        }
        .sidebar-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #1e293b;
        }
        .sidebar-nav-item:hover .sidebar-tooltip { opacity: 1; }
    </style>
</head>
<body class="h-full overflow-hidden text-slate-700">

    <div class="flex h-full overflow-hidden" 
         x-data="{ 
            sidebarOpen: localStorage.getItem('sidebarOpen') !== null 
                ? localStorage.getItem('sidebarOpen') === 'true' 
                : window.innerWidth > 1024,
            toasts: [],
            quickSearchOpen: false,
            quickSearchQuery: '',
            quickSearchResults: [],
            quickSearchLoading: false,
            quickSearchActive: 0,
            addToast(message, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, message, type });
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 5000);
            },
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                localStorage.setItem('sidebarOpen', this.sidebarOpen);
            },
            async doQuickSearch(q) {
                if (q.length < 2) { this.quickSearchResults = []; return; }
                this.quickSearchLoading = true;
                try {
                    const res = await fetch(`/api/quick-search?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
                    });
                    const data = await res.json();
                    this.quickSearchResults = data.results || [];
                    this.quickSearchActive = 0;
                } catch(e) { this.quickSearchResults = []; }
                finally { this.quickSearchLoading = false; }
            },
            openQuickSearch() {
                this.quickSearchOpen = true;
                this.quickSearchQuery = '';
                this.quickSearchResults = [];
                this.$nextTick(() => document.getElementById('quick-search-input')?.focus());
            },
            closeQuickSearch() {
                this.quickSearchOpen = false;
                this.quickSearchQuery = '';
                this.quickSearchResults = [];
            },
            navigateSearch(dir) {
                const len = this.quickSearchResults.length;
                if (!len) return;
                this.quickSearchActive = (this.quickSearchActive + dir + len) % len;
            },
            selectSearchResult() {
                const item = this.quickSearchResults[this.quickSearchActive];
                if (item?.url) { window.location.href = item.url; this.closeQuickSearch(); }
            }
         }"
         @notify.window="addToast($event.detail.message, $event.detail.type)"
         @keydown.window.ctrl.k.prevent="openQuickSearch()"
         @keydown.window.meta.k.prevent="openQuickSearch()"
         @keydown.window.escape="closeQuickSearch()"
         @resize.window="if(window.innerWidth > 1024 && !sidebarOpen) { sidebarOpen = true; localStorage.setItem('sidebarOpen', true); }">
        
        {{-- Mobile Sidebar Overlay --}}
        <div x-show="sidebarOpen && window.innerWidth < 1024" 
             @click="sidebarOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[45] lg:hidden">
        </div>

        {{-- Mobile Toggle Button --}}
        <button 
            @click="toggleSidebar()"
            class="lg:hidden fixed top-4 left-4 z-[60] w-10 h-10 bg-white rounded-xl shadow-2xl flex items-center justify-center text-slate-600 border border-slate-100 transition-all active:scale-90"
        >
            <i class="fa-solid transition-transform duration-300" :class="sidebarOpen ? 'fa-xmark scale-110' : 'fa-bars-staggered'"></i>
        </button>

        {{-- Global Toast Container --}}
        <div class="fixed top-5 right-5 z-[100] space-y-2 w-72">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-x-8"
                     x-transition:enter-end="opacity-100 scale-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-x-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-x-8"
                     class="bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-xl flex items-center gap-3">
                    <div :class="{
                        'bg-emerald-50 text-emerald-600': toast.type === 'success',
                        'bg-rose-50 text-rose-600': toast.type === 'error',
                        'bg-blue-50 text-blue-600': toast.type === 'info',
                        'bg-amber-50 text-amber-600': toast.type === 'warning'
                    }" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid text-xs" :class="{
                            'fa-check': toast.type === 'success',
                            'fa-xmark': toast.type === 'error',
                            'fa-info': toast.type === 'info',
                            'fa-triangle-exclamation': toast.type === 'warning'
                        }"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 leading-none" x-text="toast.type"></p>
                        <p class="text-[11px] font-bold text-slate-700 leading-snug mt-0.5" x-text="toast.message"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Sidebar --}}
        <aside 
            class="bg-gradient-to-b from-blue-900 to-blue-950 flex flex-col transition-all duration-500 z-50 fixed lg:sticky top-0 left-0 h-screen shadow-2xl shrink-0 overflow-hidden"
            :class="{
                'w-60 translate-x-0': sidebarOpen,
                'w-24 translate-x-0': !sidebarOpen && window.innerWidth >= 1024,
                '-translate-x-full': !sidebarOpen && window.innerWidth < 1024
            }">
            
            <div class="h-20 flex items-center px-5 gap-3 shrink-0 border-b border-blue-800/50">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/30 shrink-0 font-bold text-lg">{{ __('ISO') }}</div>
                <div x-show="sidebarOpen" x-transition.opacity.duration.500ms class="overflow-hidden flex-1">
                    <p class="font-bold text-white text-base leading-none tracking-tight">27001:2022</p>
                    <p class="text-[8px] text-blue-300 font-bold mt-1 uppercase tracking-widest whitespace-nowrap">{{ __('Self-Assessment') }}</p>
                </div>
                <button @click="toggleSidebar()" class="hidden lg:flex w-8 h-8 rounded-lg items-center justify-center text-blue-300 hover:bg-white/10 hover:text-white transition-all outline-none">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-angle-left' : 'fa-angle-right'"></i>
                </button>
            </div>

            <nav class="flex-1 px-3 space-y-0.5 mt-4 pb-4">

                {{-- Quick Action --}}
                <div class="px-2 mb-6" x-show="sidebarOpen">
                    <a href="{{ route('sessions.index', ['create' => 'true']) }}" 
                       class="flex items-center justify-center gap-2 w-full py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 text-blue-100 font-black text-[11px] uppercase tracking-wider rounded-xl transition-all hover:-translate-y-0.5 backdrop-blur-sm">
                        <i class="fa-solid fa-plus"></i>
                        <span>{{ __('New Assessment') }}</span>
                    </a>
                </div>
                <div class="px-2 mb-6 flex justify-center" x-show="!sidebarOpen">
                    <a href="{{ route('sessions.index', ['create' => 'true']) }}" 
                       class="flex items-center justify-center w-10 h-10 bg-white/5 hover:bg-white/10 border border-white/10 text-blue-100 rounded-xl transition-all hover:-translate-y-0.5 backdrop-blur-sm">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </a>
                </div>

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" 
                   @click="if(window.innerWidth < 1024) toggleSidebar()"
                   title="{{ __('Dashboard') }}"
                   class="sidebar-nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('dashboard') ? 'bg-blue-600/80 text-white shadow-[0_0_15px_rgba(37,99,235,0.3)] border border-blue-500/50' : 'text-blue-100 hover:bg-white/10' }}">
                    <div class="w-6 flex justify-center shrink-0">
                        <i class="fa-solid fa-chart-pie text-base"></i>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text">{{ __('Dashboard') }}</span>
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">{{ __('Dashboard') }}</span>
                </a>

                {{-- Divider: CORE OPERATIONS --}}
                <div x-show="sidebarOpen" class="pt-2 pb-1 px-3">
                    <p class="text-[9px] font-black text-blue-400/60 uppercase tracking-[0.2em]">{{ __('Core Operations') }}</p>
                </div>
                <div x-show="!sidebarOpen" class="border-t border-blue-800/40 my-2 mx-2"></div>

                {{-- Audit Sessions --}}
                <a href="{{ route('sessions.index') }}" 
                   @click="if(window.innerWidth < 1024) toggleSidebar()"
                   title="{{ __('Audit Sessions') }}"
                   class="sidebar-nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('sessions.*') ? 'bg-blue-600/80 text-white shadow-[0_0_15px_rgba(37,99,235,0.3)] border border-blue-500/50' : 'text-blue-100 hover:bg-white/10' }}">
                    <div class="w-6 flex justify-center shrink-0 relative">
                        <i class="fa-solid fa-list-check text-base"></i>
                        @if($sidebarInProgressSessions > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-3.5 h-3.5 bg-amber-400 rounded-full flex items-center justify-center text-[7px] font-black text-slate-900 leading-none">{{ $sidebarInProgressSessions }}</span>
                        @endif
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text flex-1">{{ __('Audit Sessions') }}</span>
                    @if($sidebarInProgressSessions > 0)
                    <span x-show="sidebarOpen" class="ml-auto px-1.5 py-0.5 bg-amber-400/20 text-amber-300 rounded text-[8px] font-black leading-none">{{ $sidebarInProgressSessions }}</span>
                    @endif
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">{{ __('Audit Sessions') }}{{ $sidebarInProgressSessions > 0 ? ' Ã‚Â· '.$sidebarInProgressSessions.' active' : '' }}</span>
                </a>



                {{-- Compliance Center --}}
                <a href="{{ route('workspace.index') }}" 
                   @click="if(window.innerWidth < 1024) toggleSidebar()"
                   title="{{ __('Compliance Center') }}"
                   class="sidebar-nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('workspace.*') ? 'bg-blue-600/80 text-white shadow-[0_0_15px_rgba(37,99,235,0.3)] border border-blue-500/50' : 'text-blue-100 hover:bg-white/10' }}">
                    <div class="w-6 flex justify-center shrink-0 relative">
                        <i class="fa-solid fa-diagram-project text-base"></i>
                        @if($sidebarOpenGaps > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-3.5 h-3.5 bg-rose-500 rounded-full flex items-center justify-center text-[7px] font-black text-white leading-none">{{ min($sidebarOpenGaps, 9) }}{{ $sidebarOpenGaps > 9 ? '+' : '' }}</span>
                        @endif
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text flex-1">{{ __('Compliance Center') }}</span>
                    @if($sidebarOpenGaps > 0)
                    <span x-show="sidebarOpen" class="ml-auto px-1.5 py-0.5 bg-rose-500/20 text-rose-300 rounded text-[8px] font-black leading-none">{{ $sidebarOpenGaps }} gaps</span>
                    @endif
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">{{ __('Compliance Center') }}{{ $sidebarOpenGaps > 0 ? ' Ã‚Â· '.$sidebarOpenGaps.' gaps' : '' }}</span>
                </a>

                {{-- Divider: INTELLIGENCE & REPORTS --}}
                <div x-show="sidebarOpen" class="pt-4 pb-1 px-3">
                    <p class="text-[9px] font-black text-blue-400/60 uppercase tracking-[0.2em]">{{ __('Intelligence & Reports') }}</p>
                </div>
                <div x-show="!sidebarOpen" class="border-t border-blue-800/40 my-2 mx-2"></div>

                {{-- Strategic Analytics --}}
                <a href="{{ route('reports.strategic') }}" 
                   @click="if(window.innerWidth < 1024) toggleSidebar()"
                   title="{{ __('Strategic Analytics') }}"
                   class="sidebar-nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('reports.strategic') ? 'bg-blue-600/80 text-white shadow-[0_0_15px_rgba(37,99,235,0.3)] border border-blue-500/50' : 'text-blue-100 hover:bg-white/10' }}">
                    <div class="w-6 flex justify-center shrink-0">
                        <i class="fa-solid fa-chart-line text-base"></i>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text">{{ __('Strategic Analytics') }}</span>
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">{{ __('Strategic Analytics') }}</span>
                </a>

                {{-- Divider: RESOURCES & GOVERNANCE --}}
                <div x-show="sidebarOpen" class="pt-4 pb-1 px-3">
                    <p class="text-[9px] font-black text-blue-400/60 uppercase tracking-[0.2em]">{{ __('Resources & Governance') }}</p>
                </div>
                <div x-show="!sidebarOpen" class="border-t border-blue-800/40 my-2 mx-2"></div>

                {{-- Knowledge Base --}}
                <a href="{{ route('knowledge-base.index') }}" 
                   @click="if(window.innerWidth < 1024) toggleSidebar()"
                   title="{{ __('Knowledge Base') }}"
                   class="sidebar-nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('knowledge-base.*') ? 'bg-blue-600/80 text-white shadow-[0_0_15px_rgba(37,99,235,0.3)] border border-blue-500/50' : 'text-blue-100 hover:bg-white/10' }}">
                    <div class="w-6 flex justify-center shrink-0">
                        <i class="fa-solid fa-book-open text-base"></i>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text flex-1">{{ __('Knowledge Base') }}</span>
                    @if($sidebarKbCustomCount > 0)
                    <span x-show="sidebarOpen" class="ml-auto px-1.5 py-0.5 bg-white/10 text-blue-200 rounded text-[8px] font-black leading-none">{{ $sidebarKbCustomCount }}</span>
                    @endif
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">{{ __('Knowledge Base') }}</span>
                </a>

                {{-- Community Hub --}}
                <a href="{{ route('community.index') }}" 
                   @click="if(window.innerWidth < 1024) toggleSidebar()"
                   title="{{ __('Community Hub') }}"
                   class="sidebar-nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('community.*') ? 'bg-blue-600/80 text-white shadow-[0_0_15px_rgba(37,99,235,0.3)] border border-blue-500/50' : 'text-blue-100 hover:bg-white/10' }}">
                    <div class="w-6 flex justify-center shrink-0">
                        <i class="fa-solid fa-users text-base"></i>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text flex-1">{{ __('Community Hub') }}</span>
                    @if($sidebarCommunityCount > 0)
                    <span x-show="sidebarOpen" class="ml-auto px-1.5 py-0.5 bg-white/10 text-blue-200 rounded text-[8px] font-black leading-none">{{ $sidebarCommunityCount }}</span>
                    @endif
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">{{ __('Community Hub') }}</span>
                </a>



                {{-- Audit Trail --}}
                <a href="{{ route('audit-trail.index') }}" 
                   @click="if(window.innerWidth < 1024) toggleSidebar()"
                   title="{{ __('Audit Trail') }}"
                   class="sidebar-nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('audit-trail.*') ? 'bg-blue-600/80 text-white shadow-[0_0_15px_rgba(37,99,235,0.3)] border border-blue-500/50' : 'text-blue-100 hover:bg-white/10' }}">
                    <div class="w-6 flex justify-center shrink-0 relative">
                        <i class="fa-solid fa-clock-rotate-left text-base"></i>
                        @if($sidebarTodayTrail > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-3.5 h-3.5 bg-emerald-400 rounded-full flex items-center justify-center text-[7px] font-black text-slate-900 leading-none">{{ min($sidebarTodayTrail, 9) }}</span>
                        @endif
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text flex-1">{{ __('Audit Trail') }}</span>
                    @if($sidebarTodayTrail > 0)
                    <span x-show="sidebarOpen" class="ml-auto px-1.5 py-0.5 bg-emerald-400/20 text-emerald-300 rounded text-[8px] font-black leading-none">{{ $sidebarTodayTrail }} today</span>
                    @endif
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">{{ __('Audit Trail') }}{{ $sidebarTodayTrail > 0 ? ' Ã‚Â· '.$sidebarTodayTrail.' today' : '' }}</span>
                </a>

            </nav>

            {{-- Admin Panel Link (Admins Only) --}}
            @if(Auth::user()->isAdmin())
            <div class="px-3 py-3 border-t border-white/10">
                <a href="{{ route('admin.dashboard') }}" 
                   title="{{ __('Admin Panel') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group bg-purple-600/40 border border-purple-500/50 text-purple-200 hover:bg-purple-600/60">
                    <div class="w-6 flex justify-center shrink-0">
                        <i class="fa-solid fa-shield-halved text-base text-purple-300"></i>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-bold tracking-tight sidebar-text flex-1">Admin Panel</span>
                    <span x-show="sidebarOpen" class="ml-auto px-1.5 py-0.5 bg-purple-500/30 text-purple-300 rounded text-[8px] font-black leading-none">ADMIN</span>
                    <span x-show="!sidebarOpen" class="sidebar-tooltip">Admin Panel</span>
                </a>
            </div>
            @endif

        </aside>

        <main class="flex-1 flex flex-col min-w-0 bg-[#F8FAFC] overflow-hidden transition-all duration-300"
              @click="if(sidebarOpen && window.innerWidth >= 1024) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }">
            
            <header class="h-16 border-b border-slate-200 flex items-center justify-between px-6 z-40 bg-white shrink-0">
                {{-- Breadcrumb --}}
                <div class="flex items-center gap-2 min-w-0">
                    @hasSection('breadcrumb')
                        <nav class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest">
                            @yield('breadcrumb')
                        </nav>
                    @else
                        @php
                            $crumbs = [];
                            if (request()->routeIs('dashboard'))             $crumbs = [['label' => 'Dashboard', 'url' => null]];
                            elseif (request()->routeIs('sessions.*'))        $crumbs = [['label' => 'Core Operations', 'url' => null], ['label' => 'Sessions', 'url' => null]];
                            elseif (request()->routeIs('results.*'))         $crumbs = [['label' => 'Core Operations', 'url' => null], ['label' => 'Assessment', 'url' => null]];
                            elseif (request()->routeIs('workspace.*'))       $crumbs = [['label' => 'Core Operations', 'url' => null], ['label' => 'Compliance Center', 'url' => null]];
                            elseif (request()->routeIs('reports.strategic')) $crumbs = [['label' => 'Intelligence & Reports', 'url' => null], ['label' => 'Strategic Analytics', 'url' => null]];
                            elseif (request()->routeIs('knowledge-base.*'))  $crumbs = [['label' => 'Resources & Governance', 'url' => null], ['label' => 'Knowledge Base', 'url' => null]];
                            elseif (request()->routeIs('community.*'))       $crumbs = [['label' => 'Resources & Governance', 'url' => null], ['label' => 'Community Hub', 'url' => null]];
                            elseif (request()->routeIs('audit-trail.*'))     $crumbs = [['label' => 'Resources & Governance', 'url' => null], ['label' => 'Audit Trail', 'url' => null]];
                            elseif (request()->routeIs('profile.*'))         $crumbs = [['label' => 'Account', 'url' => null], ['label' => 'Profile', 'url' => null]];
                            else $crumbs = [['label' => 'Page', 'url' => null]];
                        @endphp
                        <nav class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest">
                            <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-indigo-500 transition-colors">
                                <i class="fa-solid fa-house text-[9px]"></i>
                            </a>
                            @foreach($crumbs as $i => $crumb)
                                <i class="fa-solid fa-chevron-right text-[7px] text-slate-300"></i>
                                @if($i === count($crumbs) - 1)
                                    <span class="text-slate-700">{{ $crumb['label'] }}</span>
                                @else
                                    <span class="text-slate-400">{{ $crumb['label'] }}</span>
                                @endif
                            @endforeach
                        </nav>
                    @endif {{-- end @hasSection --}}
                </div>

                <div class="flex items-center gap-2 ml-4">

                    {{-- Quick Search Button --}}
                    <button @click="openQuickSearch()"
                        class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-400 hover:text-slate-600 hover:border-slate-300 hover:bg-white transition-all group">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        <span class="hidden md:inline">{{ __('Quick search...') }}</span>
                        <kbd class="hidden md:inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[8px] font-black text-slate-400 group-hover:border-slate-300 shadow-sm">
                            <span class="text-[9px]">⌘</span>K
                        </kbd>
                    </button>

                    <div class="w-px h-6 bg-slate-200 mx-1"></div>

                    {{-- Language Switcher --}}
                    <div class="relative" x-data="{ langOpen: false }">
                        <button @click="langOpen = !langOpen" 
                            class="flex items-center gap-1.5 px-2.5 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition-all border border-transparent hover:border-slate-200">
                            <i class="fa-solid fa-globe text-xs"></i> 
                            {{ app()->getLocale() == 'id' ? 'ID' : 'EN' }}
                        </button>
                        <div x-show="langOpen" @click.away="langOpen = false" x-cloak 
                            class="absolute right-0 mt-2 w-28 bg-white border border-slate-200 rounded-xl shadow-xl py-1.5 z-50">
                            <a href="{{ route('lang.switch', 'en') }}" 
                                class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold hover:bg-slate-50 rounded-lg mx-1 transition-all {{ app()->getLocale() == 'en' ? 'text-indigo-600' : 'text-slate-600' }}">
                                <img src="https://flagcdn.com/w20/us.png" srcset="https://flagcdn.com/w40/us.png 2x" width="16" alt="US" class="rounded-sm shadow-sm"> {{ __('English') }}</a>
                            <a href="{{ route('lang.switch', 'id') }}" 
                                class="flex items-center gap-2.5 px-3 py-2 text-xs font-bold hover:bg-slate-50 rounded-lg mx-1 transition-all {{ app()->getLocale() == 'id' ? 'text-indigo-600' : 'text-slate-600' }}">
                                <img src="https://flagcdn.com/w20/id.png" srcset="https://flagcdn.com/w40/id.png 2x" width="16" alt="ID" class="rounded-sm shadow-sm"> {{ __('Indonesia') }}</a>
                        </div>
                    </div>

                    {{-- Notification Bell --}}
                    @php
                        $overdueTasks = auth()->check()
                            ? \App\Models\AssessmentResult::with('standard')
                                ->whereHas('session', fn($q) => $q->where('user_id', auth()->id()))
                                ->whereNotNull('treatment_due_date')
                                ->whereDate('treatment_due_date', '<', now())
                                ->whereBetween('maturity_rating', [1, 3])
                                ->get()
                            : collect();
                    @endphp
                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen" 
                            class="relative w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition-all border border-transparent hover:border-slate-200">
                            <i class="fa-solid fa-bell text-sm"></i>
                            @if($overdueTasks->count() > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 border-2 border-white rounded-full flex items-center justify-center text-[7px] font-black text-white">
                                    {{ min($overdueTasks->count(), 9) }}{{ $overdueTasks->count() > 9 ? '+' : '' }}
                                </span>
                            @endif
                        </button>
                        
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak 
                            class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-black text-slate-900">{{ __('Notifications') }}</h4>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                        {{ $overdueTasks->count() }} {{ __('Overdue Tasks') }}
                                    </p>
                                </div>
                                @if($overdueTasks->count() > 0)
                                <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                </div>
                                @endif
                            </div>
                            
                            <div class="max-h-72 overflow-y-auto custom-scrollbar p-2">
                                @forelse($overdueTasks as $task)
                                <a href="{{ route('workspace.index', ['session_id' => $task->session_id, 'focus' => $task->id]) }}" 
                                    class="flex items-start gap-3 p-3 hover:bg-slate-50 rounded-xl transition-colors group">
                                    <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 border border-rose-100">
                                        <i class="fa-solid fa-clock text-[10px]"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold text-slate-900 leading-tight truncate">
                                            <span class="text-rose-600 uppercase tracking-widest text-[8px] mr-1">{{ $task->standard->code }}</span>
                                            {{ $task->standard->title }}
                                        </p>
                                        <p class="text-[9px] text-slate-500 font-medium mt-0.5">
                                            PIC: <strong class="text-slate-700">{{ $task->treatment_pic }}</strong>
                                        </p>
                                        <p class="text-[8px] font-bold text-rose-500 uppercase tracking-widest mt-1">
                                            {{ $task->treatment_due_date->diffForHumans() }}
                                        </p>
                                    </div>
                                </a>
                                @empty
                                <div class="py-8 text-center">
                                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                                        <i class="fa-regular fa-bell-slash text-lg text-slate-300"></i>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('All caught up!') }}</p>
                                </div>
                                @endforelse
                            </div>
                            
                            @if($overdueTasks->count() > 0)
                            <div class="p-2 border-t border-slate-100">
                                <a href="{{ route('workspace.index') }}" 
                                    class="block w-full py-2 text-center text-[9px] font-black text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all uppercase tracking-widest">
                                    {{ __('Open Compliance Center') }} →
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- User Menu --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                            class="flex items-center gap-2 pl-3 border-l border-slate-200 hover:opacity-80 transition-all outline-none">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white text-[10px] font-black shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="hidden md:block text-left leading-none">
                                <p class="text-[10px] font-black text-slate-700 truncate max-w-[100px]">{{ auth()->user()->name }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('User') }}</p>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 hidden md:block" :class="open && 'rotate-180'" style="transition: transform 0.2s"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak 
                            class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-2xl shadow-2xl py-2 z-50">
                            <div class="px-4 py-2.5 border-b border-slate-100 mb-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Signed in as') }}</p>
                                <p class="text-xs font-black text-slate-800 truncate mt-0.5">{{ auth()->user()->name }}</p>
                                <p class="text-[9px] text-slate-400 font-medium truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" 
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-all">
                                <i class="fa-solid fa-user-gear text-slate-400 text-xs"></i> 
                                {{ __('Profile Settings') }}
                            </a>

                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-500 hover:bg-rose-50 transition-all">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i> 
                                        {{ __('Sign Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ============================================================ --}}
            {{-- QUICK SEARCH MODAL                                           --}}
            {{-- ============================================================ --}}
            <div x-show="quickSearchOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-start justify-center pt-[10vh] px-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeQuickSearch()"></div>
                <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-10"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-4">
                    {{-- Input --}}
                    <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-100">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm shrink-0"></i>
                        <input id="quick-search-input" type="text"
                            x-model="quickSearchQuery"
                            @input.debounce.300ms="doQuickSearch(quickSearchQuery)"
                            @keydown.arrow-down.prevent="navigateSearch(1)"
                            @keydown.arrow-up.prevent="navigateSearch(-1)"
                            @keydown.enter.prevent="selectSearchResult()"
                            placeholder="{{ __('Search sessions, controls, resources...') }}"
                            class="flex-1 text-sm font-medium text-slate-700 placeholder:text-slate-400 outline-none bg-transparent">
                        <div x-show="quickSearchLoading" class="shrink-0">
                            <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                        <kbd @click="closeQuickSearch()" class="shrink-0 px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[9px] font-black text-slate-400 cursor-pointer hover:bg-slate-200 transition-all">{{ __('ESC') }}</kbd>
                    </div>
                    {{-- Dynamic Results --}}
                    <div x-show="quickSearchResults.length > 0" class="max-h-72 overflow-y-auto custom-scrollbar py-2">
                        <template x-for="(result, idx) in quickSearchResults" :key="idx">
                            <a :href="result.url" @click="closeQuickSearch()" @mouseenter="quickSearchActive = idx"
                                :class="quickSearchActive === idx ? 'bg-indigo-50' : 'hover:bg-slate-50'"
                                class="flex items-center gap-3 px-4 py-2.5 transition-colors cursor-pointer">
                                <div :class="{
                                    'bg-blue-100 text-blue-600': result.type === 'session',
                                    'bg-rose-100 text-rose-600': result.type === 'gap',
                                    'bg-emerald-100 text-emerald-600': result.type === 'kb',
                                    'bg-purple-100 text-purple-600': result.type === 'community'
                                }" class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0">
                                    <i class="text-[10px]" :class="{
                                        'fa-solid fa-list-check': result.type === 'session',
                                        'fa-solid fa-triangle-exclamation': result.type === 'gap',
                                        'fa-solid fa-book-open': result.type === 'kb',
                                        'fa-solid fa-users': result.type === 'community'
                                    }"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate" x-text="result.title"></p>
                                    <p class="text-[9px] font-medium text-slate-400 truncate" x-text="result.subtitle"></p>
                                </div>
                                <span class="shrink-0 px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest"
                                    :class="{
                                        'bg-blue-50 text-blue-500': result.type === 'session',
                                        'bg-rose-50 text-rose-500': result.type === 'gap',
                                        'bg-emerald-50 text-emerald-500': result.type === 'kb',
                                        'bg-purple-50 text-purple-500': result.type === 'community'
                                    }" x-text="result.type"></span>
                            </a>
                        </template>
                    </div>
                    {{-- No results --}}
                    <div x-show="quickSearchQuery.length >= 2 && !quickSearchLoading && quickSearchResults.length === 0" class="py-10 text-center">
                        <i class="fa-solid fa-magnifying-glass text-2xl text-slate-200 mb-2 block"></i>
                        <p class="text-xs font-bold text-slate-400">No results for "<span x-text="quickSearchQuery"></span>"</p>
                    </div>
                    {{-- Quick nav shortcuts (shown when query is empty) --}}
                    <div x-show="quickSearchQuery.length < 2" class="p-3">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1 mb-2">{{ __('Quick Navigation') }}</p>
                        <div class="grid grid-cols-2 gap-1">
                            @foreach([
                                ['label' => 'Audit Sessions',      'icon' => 'fa-list-check',        'url' => route('sessions.index'),        'color' => 'blue'],
                                ['label' => 'Compliance Center',   'icon' => 'fa-diagram-project',   'url' => route('workspace.index'),       'color' => 'indigo'],
                                ['label' => 'Strategic Analytics', 'icon' => 'fa-chart-line',        'url' => route('reports.strategic'),     'color' => 'violet'],
                                ['label' => 'Knowledge Base',      'icon' => 'fa-book-open',         'url' => route('knowledge-base.index'),  'color' => 'emerald'],
                                ['label' => 'Community Hub',       'icon' => 'fa-users',             'url' => route('community.index'),       'color' => 'purple'],
                                ['label' => 'Audit Trail',         'icon' => 'fa-clock-rotate-left', 'url' => route('audit-trail.index'),     'color' => 'amber'],
                            ] as $shortcut)
                            <a href="{{ $shortcut['url'] }}" @click="closeQuickSearch()"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-50 transition-all group">
                                <div class="w-6 h-6 rounded-lg bg-{{ $shortcut['color'] }}-50 text-{{ $shortcut['color'] }}-500 flex items-center justify-center shrink-0">
                                    <i class="fa-solid {{ $shortcut['icon'] }} text-[9px]"></i>
                                </div>
                                <span class="text-[10px] font-bold text-slate-600 group-hover:text-slate-900 truncate">{{ $shortcut['label'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3 text-[9px] font-bold text-slate-400">
                            <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[8px] shadow-sm">↑↓</kbd>{{ __('Navigate') }}</span>
                            <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[8px] shadow-sm">↵</kbd>{{ __('Open') }}</span>
                            <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded text-[8px] shadow-sm">{{ __('ESC') }}</kbd>{{ __('Close') }}</span>
                        </div>
                        <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">ISO 27001:2022</span>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto bg-[#F8FAFC] p-6 custom-scrollbar">
                <div class="w-full">
                    {{-- Global Alerts (Triggering Alpine Toasts) --}}
                    @if(session('success'))
                    <div x-init="$nextTick(() => { addToast('{{ addslashes(session('success')) }}', 'success') })"></div>
                    @endif
                    @if(session('error'))
                    <div x-init="$nextTick(() => { addToast('{{ addslashes(session('error')) }}', 'error') })"></div>
                    @endif
                    @if(session('warning'))
                    <div x-init="$nextTick(() => { addToast('{{ addslashes(session('warning')) }}', 'warning') })"></div>
                    @endif
                    @if(session('info'))
                    <div x-init="$nextTick(() => { addToast('{{ addslashes(session('info')) }}', 'info') })"></div>
                    @endif


                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
