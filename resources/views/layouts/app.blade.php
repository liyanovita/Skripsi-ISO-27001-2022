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
            sidebarOpen: {{ session('just_logged_in') ? 'true' : "(localStorage.getItem('sidebarOpen') !== null ? localStorage.getItem('sidebarOpen') === 'true' : window.innerWidth > 1024)" }},
            toasts: [],
            quickSearchOpen: false,
            quickSearchQuery: '',
            quickSearchResults: [],
            quickSearchLoading: false,
            quickSearchActive: 0,
            showWelcomeGuide: false,
            helpPanelOpen: false,
            init() {
                window.appComponent = this;
                @if(session('just_logged_in'))
                    localStorage.setItem('sidebarOpen', 'true');
                @endif
                // Show welcome guide only on first visit
                if (!localStorage.getItem('guideShown')) {
                    this.showWelcomeGuide = true;
                }
            },
            dismissGuide() {
                this.showWelcomeGuide = false;
                localStorage.setItem('guideShown', 'true');
            },
            resetGuide() {
                localStorage.removeItem('guideShown');
                this.showWelcomeGuide = true;
            },
            addToast(message, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, message, type });
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 6000);
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
        <div class="fixed bottom-6 right-6 z-[500] flex flex-col items-end gap-3 w-full max-w-sm pointer-events-none">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-transition:enter="transition ease-out duration-400"
                     x-transition:enter-start="opacity-0 scale-90 translate-y-6"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-90 translate-y-6"
                     :class="{
                         'bg-emerald-600 shadow-emerald-600/40': toast.type === 'success',
                         'bg-rose-600 shadow-rose-600/40': toast.type === 'error',
                         'bg-blue-600 shadow-blue-600/40': toast.type === 'info',
                         'bg-amber-500 shadow-amber-500/40': toast.type === 'warning'
                     }"
                     class="w-full rounded-2xl px-5 py-4 shadow-2xl flex items-center gap-4 pointer-events-auto">
                    {{-- Icon --}}
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0 border border-white/20">
                        <i class="fa-solid text-white text-base" :class="{
                            'fa-circle-check': toast.type === 'success',
                            'fa-circle-xmark': toast.type === 'error',
                            'fa-circle-info': toast.type === 'info',
                            'fa-triangle-exclamation': toast.type === 'warning'
                        }"></i>
                    </div>
                    {{-- Text --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/70 leading-none" x-text="toast.type === 'error' ? 'Error' : toast.type === 'success' ? 'Success' : toast.type === 'warning' ? 'Warning' : 'Info'"></p>
                        <p class="text-sm font-bold text-white leading-snug mt-0.5" x-text="toast.message"></p>
                    </div>
                    {{-- Close Button --}}
                    <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="w-7 h-7 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center text-white transition-all shrink-0 border border-white/20">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
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
                   id="sidebar-dashboard"
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
                   id="sidebar-sessions"
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
                   id="sidebar-workspace"
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
                   id="sidebar-analytics"
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
                   id="sidebar-knowledge"
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
                   id="sidebar-community"
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
                   id="sidebar-audit-trail"
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
                        if(auth()->check()) {
                            $baseTaskQuery = \App\Models\AssessmentResult::with('standard')
                                ->whereHas('session', fn($q) => $q->where('user_id', auth()->id()))
                                ->whereNotNull('treatment_due_date')
                                ->whereBetween('maturity_rating', [1, 3]);

                            // 1. Overdue Tasks (Telat)
                            $overdueTasks = (clone $baseTaskQuery)->whereDate('treatment_due_date', '<', now())->get();
                            
                            // 2. Upcoming Tasks (Jatuh tempo < 3 hari)
                            $upcomingTasks = (clone $baseTaskQuery)->whereBetween('treatment_due_date', [now(), now()->addDays(3)])->get();
                            
                            // 3. Stagnant Sessions (Tidak diperbarui > 7 hari)
                            $stagnantSessions = \App\Models\AssessmentSession::where('user_id', auth()->id())
                                ->where('status', '!=', 'completed')
                                ->where('updated_at', '<', now()->subDays(7))
                                ->get();
                                
                            $totalNotifs = $overdueTasks->count() + $upcomingTasks->count() + $stagnantSessions->count();
                        } else {
                            $overdueTasks = collect();
                            $upcomingTasks = collect();
                            $stagnantSessions = collect();
                            $totalNotifs = 0;
                        }
                    @endphp
                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen" 
                            class="relative w-8 h-8 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition-all border border-transparent hover:border-slate-200">
                            <i class="fa-solid fa-bell text-sm"></i>
                            @if($totalNotifs > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 border-2 border-white rounded-full flex items-center justify-center text-[7px] font-black text-white">
                                    {{ min($totalNotifs, 9) }}{{ $totalNotifs > 9 ? '+' : '' }}
                                </span>
                            @endif
                        </button>
                        
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak 
                            class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-black text-slate-900">{{ __('Action Center') }}</h4>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                        {{ $totalNotifs }} {{ __('Pending Alerts') }}
                                    </p>
                                </div>
                                @if($overdueTasks->count() > 0)
                                <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                </div>
                                @elseif($totalNotifs > 0)
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <i class="fa-solid fa-bell text-xs"></i>
                                </div>
                                @endif
                            </div>
                            
                            <div class="max-h-80 overflow-y-auto custom-scrollbar p-2">
                                
                                {{-- Overdue Tasks --}}
                                @if($overdueTasks->count() > 0)
                                    <div class="px-3 py-1 mt-1 mb-1 text-[8px] font-black text-rose-500 uppercase tracking-widest">{{ __('Overdue Tasks') }}</div>
                                    @foreach($overdueTasks as $task)
                                    <a href="{{ route('workspace.index', ['session_id' => $task->session_id, 'focus' => $task->id]) }}" 
                                        class="flex items-start gap-3 p-3 hover:bg-slate-50 rounded-xl transition-colors group">
                                        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 border border-rose-100">
                                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold text-slate-900 leading-tight truncate">
                                                <span class="text-rose-600 uppercase tracking-widest text-[8px] mr-1">{{ $task->standard->code }}</span>
                                                {{ $task->standard->title }}
                                            </p>
                                            <p class="text-[9px] text-slate-500 font-medium mt-0.5">PIC: <strong class="text-slate-700">{{ $task->treatment_pic }}</strong></p>
                                            <p class="text-[8px] font-bold text-rose-500 uppercase tracking-widest mt-1">{{ $task->treatment_due_date->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                    @endforeach
                                @endif

                                {{-- Upcoming Tasks --}}
                                @if($upcomingTasks->count() > 0)
                                    <div class="px-3 py-1 mt-1 mb-1 text-[8px] font-black text-amber-500 uppercase tracking-widest">{{ __('Upcoming Deadlines') }}</div>
                                    @foreach($upcomingTasks as $task)
                                    <a href="{{ route('workspace.index', ['session_id' => $task->session_id, 'focus' => $task->id]) }}" 
                                        class="flex items-start gap-3 p-3 hover:bg-slate-50 rounded-xl transition-colors group">
                                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center shrink-0 border border-amber-100">
                                            <i class="fa-solid fa-clock text-[10px]"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold text-slate-900 leading-tight truncate">
                                                <span class="text-amber-600 uppercase tracking-widest text-[8px] mr-1">{{ $task->standard->code }}</span>
                                                {{ $task->standard->title }}
                                            </p>
                                            <p class="text-[9px] text-slate-500 font-medium mt-0.5">PIC: <strong class="text-slate-700">{{ $task->treatment_pic }}</strong></p>
                                            <p class="text-[8px] font-bold text-amber-500 uppercase tracking-widest mt-1">{{ __('Due in') }} {{ $task->treatment_due_date->diffInDays(now()) }} {{ __('days') }}</p>
                                        </div>
                                    </a>
                                    @endforeach
                                @endif

                                {{-- Stagnant Sessions --}}
                                @if($stagnantSessions->count() > 0)
                                    <div class="px-3 py-1 mt-1 mb-1 text-[8px] font-black text-blue-500 uppercase tracking-widest">{{ __('Stagnant Audits') }}</div>
                                    @foreach($stagnantSessions as $sess)
                                    <a href="{{ route('workspace.index', ['session_id' => $sess->id]) }}" 
                                        class="flex items-start gap-3 p-3 hover:bg-slate-50 rounded-xl transition-colors group">
                                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 border border-blue-100">
                                            <i class="fa-solid fa-calendar-minus text-[10px]"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold text-slate-900 leading-tight truncate">{{ $sess->name }}</p>
                                            <p class="text-[9px] text-slate-500 font-medium mt-0.5">{{ __('No updates since') }} {{ $sess->updated_at->format('M d') }}</p>
                                            <p class="text-[8px] font-bold text-blue-500 uppercase tracking-widest mt-1">{{ __('Resume Assessment') }} →</p>
                                        </div>
                                    </a>
                                    @endforeach
                                @endif

                                @if($totalNotifs === 0)
                                <div class="py-8 text-center">
                                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                                        <i class="fa-regular fa-bell-slash text-lg text-slate-300"></i>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('All caught up!') }}</p>
                                    <p class="text-[9px] text-slate-400 mt-1">{{ __('No pending alerts at the moment.') }}</p>
                                </div>
                                @endif
                            </div>
                            
                            @if($totalNotifs > 0)
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

    {{-- ============================================================ --}}
    {{-- LAYER 1: WELCOME GUIDE MODAL                                 --}}
    {{-- ============================================================ --}}
    <div x-show="showWelcomeGuide" x-cloak
        class="fixed inset-0 z-[300] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="dismissGuide()"></div>
        
        {{-- Modal Panel --}}
        <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden z-10 max-h-[90vh] flex flex-col"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6 relative overflow-hidden shrink-0">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-4 -bottom-6 w-24 h-24 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
                <div class="relative z-10 flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 bg-white/20 text-white text-[9px] font-black uppercase tracking-widest rounded-lg border border-white/20">{{ __('Platform Guide') }}</span>
                        </div>
                        <h2 class="text-xl font-black text-white tracking-tight">{{ __('Welcome to OpenAudit-27001!') }}</h2>
                        <p class="text-blue-100 text-xs font-medium mt-1">{{ __('Here is a quick overview of the key modules to help you get started.') }}</p>
                    </div>
                    <button @click="dismissGuide()" class="w-8 h-8 bg-white/10 hover:bg-white/20 text-white rounded-xl flex items-center justify-center shrink-0 transition-all border border-white/20">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            {{-- Feature Cards Grid --}}
            <div class="overflow-y-auto flex-1 p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    {{-- Audit Sessions --}}
                    <a href="{{ route('sessions.index') }}" @click="dismissGuide()"
                        class="flex items-start gap-3 p-4 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-2xl transition-all group cursor-pointer">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-blue-600/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-list-check text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ __('Audit Sessions') }}</p>
                            <p class="text-[11px] font-medium text-slate-500 leading-snug mt-0.5">{{ __('Create and manage your ISO 27001 assessment sessions. Each session covers all 93 controls.') }}</p>
                        </div>
                    </a>

                    {{-- Compliance Center --}}
                    <a href="{{ route('workspace.index') }}" @click="dismissGuide()"
                        class="flex items-start gap-3 p-4 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-2xl transition-all group cursor-pointer">
                        <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-diagram-project text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ __('Compliance Center') }}</p>
                            <p class="text-[11px] font-medium text-slate-500 leading-snug mt-0.5">{{ __('Fill in control evaluations, identify gaps, and manage remediation plans (CAPA).') }}</p>
                        </div>
                    </a>

                    {{-- Strategic Analytics --}}
                    <a href="{{ route('reports.strategic') }}" @click="dismissGuide()"
                        class="flex items-start gap-3 p-4 bg-violet-50 hover:bg-violet-100 border border-violet-100 rounded-2xl transition-all group cursor-pointer">
                        <div class="w-10 h-10 bg-violet-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-violet-600/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-chart-line text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ __('Strategic Analytics') }}</p>
                            <p class="text-[11px] font-medium text-slate-500 leading-snug mt-0.5">{{ __('View compliance trend charts, maturity radar, and executive summary reports.') }}</p>
                        </div>
                    </a>

                    {{-- Knowledge Base --}}
                    <a href="{{ route('knowledge-base.index') }}" @click="dismissGuide()"
                        class="flex items-start gap-3 p-4 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-2xl transition-all group cursor-pointer">
                        <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-book-open text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ __('Knowledge Base') }}</p>
                            <p class="text-[11px] font-medium text-slate-500 leading-snug mt-0.5">{{ __('Access reference articles, implementation guides, and ISO 27001 best practices.') }}</p>
                        </div>
                    </a>

                    {{-- Community Hub --}}
                    <a href="{{ route('community.index') }}" @click="dismissGuide()"
                        class="flex items-start gap-3 p-4 bg-purple-50 hover:bg-purple-100 border border-purple-100 rounded-2xl transition-all group cursor-pointer">
                        <div class="w-10 h-10 bg-purple-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-purple-600/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-users text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ __('Community Hub') }}</p>
                            <p class="text-[11px] font-medium text-slate-500 leading-snug mt-0.5">{{ __('Share and download blank document templates with other ISO practitioners.') }}</p>
                        </div>
                    </a>

                    {{-- Audit Trail --}}
                    <a href="{{ route('audit-trail.index') }}" @click="dismissGuide()"
                        class="flex items-start gap-3 p-4 bg-amber-50 hover:bg-amber-100 border border-amber-100 rounded-2xl transition-all group cursor-pointer">
                        <div class="w-10 h-10 bg-amber-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-amber-600/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ __('Audit Trail') }}</p>
                            <p class="text-[11px] font-medium text-slate-500 leading-snug mt-0.5">{{ __('Track all activity history and changes made within the system.') }}</p>
                        </div>
                    </a>

                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-3 shrink-0">
                <button @click="localStorage.removeItem('guideShown'); showWelcomeGuide = false; addToast('{{ addslashes(__('Guide will be shown again on next login')) }}')"
                    class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 underline transition-colors">
                    {{ __('Remind me later') }}
                </button>
                <div class="flex items-center gap-2">
                    <button @click="dismissGuide()"
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black rounded-xl transition-all active:scale-95">
                        {{ __('Skip') }}
                    </button>
                    <button @click="dismissGuide()" onclick="window.GuidedTour && window.GuidedTour.start()"
                        class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                        <i class="fa-solid fa-play text-xs"></i>
                        {{ __('Start Tour') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- GUIDED TOUR BUBBLE COMPONENT (Vanilla JS - No Alpine deps)   --}}
    {{-- ============================================================ --}}
    <div id="gt-overlay" style="display:none" class="fixed inset-0 z-[350] pointer-events-none">
        <div id="gt-backdrop" class="absolute inset-0 bg-slate-900/20 pointer-events-auto"></div>
        <div id="gt-bubble" class="fixed bg-white rounded-2xl shadow-2xl border border-slate-100 w-80 pointer-events-auto flex flex-col z-[360] transition-all duration-500 ease-out" style="top:0;left:0">
            {{-- Left arrow (shown when bubble is to the right of target) --}}
            <div id="gt-arrow-left" class="absolute top-8 -left-2.5 w-0 h-0 border-t-[10px] border-t-transparent border-b-[10px] border-b-transparent border-r-[10px] border-r-white"></div>
            {{-- Right arrow (shown when bubble is to the left of target) --}}
            <div id="gt-arrow-right" style="display:none" class="absolute top-8 -right-2.5 w-0 h-0 border-t-[10px] border-t-transparent border-b-[10px] border-b-transparent border-l-[10px] border-l-white"></div>

            {{-- Header: step counter + close --}}
            <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div id="gt-icon-wrap" class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#2563eb1a">
                        <i id="gt-icon" class="fa-solid fa-gauge-high text-sm" style="color:#2563eb"></i>
                    </div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        {{ __('Step') }} <span id="gt-step-num">1</span> / <span id="gt-step-total">14</span>
                    </span>
                </div>
                <button onclick="window.GuidedTour && window.GuidedTour.end()" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-4 py-3">
                <h4 id="gt-title" class="text-xs font-bold text-slate-900 mb-1.5 leading-snug"></h4>
                <p id="gt-text" class="text-[11px] font-medium text-slate-500 leading-relaxed"></p>
            </div>

            {{-- Progress bar --}}
            <div class="px-4 pb-2">
                <div class="h-1 bg-slate-100 rounded-full overflow-hidden">
                    <div id="gt-progress" class="h-full bg-blue-500 rounded-full transition-all duration-300" style="width:7%"></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-4 py-3 border-t border-slate-100">
                <button id="gt-prev" onclick="window.GuidedTour && window.GuidedTour.prev()" class="px-3 py-1.5 text-[10px] font-black text-slate-500 rounded-lg transition-colors hover:bg-slate-50">{{ __('Back') }}</button>
                <div class="flex gap-1.5">
                    <button onclick="window.GuidedTour && window.GuidedTour.end()" class="px-3 py-1.5 text-[10px] font-bold text-slate-400 hover:text-slate-600 transition-colors">{{ __('End') }}</button>
                    <button id="gt-next" onclick="window.GuidedTour && window.GuidedTour.next()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black rounded-lg transition-colors">{{ __('Next') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.GuidedTour = (function() {
        var ROUTES = {
            dashboard:  '{{ route('dashboard') }}',
            sessions:   '{{ route('sessions.index') }}',
            workspace:  '{{ route('workspace.index') }}',
            analytics:  '{{ route('reports.strategic') }}',
            knowledge:  '{{ route('knowledge-base.index') }}',
            community:  '{{ route('community.index') }}',
            auditTrail: '{{ route('audit-trail.index') }}'
        };
        var LS_ACTIVE = 'gt_active';
        var LS_STEP   = 'gt_step';
        var steps = [
            /* ── 1: Dashboard Overview ──────────────────────── */
            { path: ROUTES.dashboard,  target: '#sidebar-dashboard',  icon: 'fa-gauge-high',       color: '#6366f1',
              title: @js(__('Dashboard – Overview')),
              text:  @js(__('Welcome to the ISO 27001:2022 Compliance Platform. This is your central hub for monitoring compliance posture.')) },
            /* ── 2: Dashboard KPI Grid ──────────────────────── */
            { path: ROUTES.dashboard,  target: '#dashboard-kpi-grid', icon: 'fa-chart-pie',         color: '#6366f1',
              title: @js(__('Dashboard – Key Indicators')),
              text:  @js(__('Track your Overall Compliance, Priority Gaps, and Maturity Level here. These stats update instantly as you audit.')) },
            /* ── 3: Dashboard Resume Banner ─────────────────── */
            { path: ROUTES.dashboard,  target: '#dashboard-resume-banner', icon: 'fa-rocket',       color: '#6366f1',
              title: @js(__('Dashboard – Continue Audit')),
              text:  @js(__('Ready to work? Click \'Continue\' to jump directly back into your latest active assessment session.')) },
            
            /* ── 4: Sessions Sidebar ────────────────────────── */
            { path: ROUTES.sessions,   target: '#sidebar-sessions',   icon: 'fa-list-check',        color: '#2563eb',
              title: @js(__('Audit Sessions – Management')),
              text:  @js(__('Let\'s learn how to manage audit cycles. Click \'Audit Sessions\' in the sidebar to navigate to the session management area.')) },
            /* ── 5: Sessions New Button ─────────────────────── */
            { path: ROUTES.sessions,   target: '#btn-new-session',     icon: 'fa-plus-circle',       color: '#2563eb',
              title: @js(__('Audit Sessions – Create Session')),
              text:  @js(__('Click \'+ New Assessment\' to start a new audit cycle. Give it a name and period (e.g. Q1 2025 Audit).')) },
            /* ── 6: Sessions Table Header ───────────────────── */
            { path: ROUTES.sessions,   target: '#session-table-header', icon: 'fa-table',            color: '#2563eb',
              title: @js(__('Audit Sessions – Session History')),
              text:  @js(__('This table lists all past and active assessment cycles, showing their progress rate, average maturity score, and completion status.')) },
            /* ── 7: Sessions First Session Link ─────────────── */
            { path: ROUTES.sessions,   target: '#btn-start-eval-first', icon: 'fa-folder-open',      color: '#2563eb',
              title: @js(__('Audit Sessions – Evaluate Controls')),
              text:  @js(__('Click on a session\'s name to open its Compliance Center and begin scoring controls.')) },

            /* ── 8: Compliance Center Sidebar ───────────────── */
            { path: ROUTES.workspace,  target: '#sidebar-workspace',   icon: 'fa-diagram-project',   color: '#0891b2',
              title: @js(__('Compliance Center – Main Area')),
              text:  @js(__('This is where the audit work happens. Click \'Compliance Center\' in the sidebar to view the controls checklist.')) },
            /* ── 9: Compliance Center Session Select ────────── */
            { path: ROUTES.workspace,  target: '#workspace-session-select', icon: 'fa-filter',       color: '#0891b2',
              title: @js(__('Compliance Center – Session Selector')),
              text:  @js(__('Use this dropdown to switch between different assessment sessions to score or review their controls.')) },
            /* ── 10: Compliance Center Gap Tab ───────────────── */
            { path: ROUTES.workspace,  target: '#tab-gap-report',      icon: 'fa-circle-exclamation', color: '#0891b2',
              title: @js(__('Compliance Center – Gap Report')),
              text:  @js(__('Click this tab to view a focused list of all controls that fell below your target level, helping you prioritize improvements.')) },
            /* ── 11: Compliance Center Workspace Tab ─────────── */
            { path: ROUTES.workspace,  target: '#tab-soa-workspace',   icon: 'fa-table-cells-large', color: '#0891b2',
              title: @js(__('Compliance Center – Statement of Applicability')),
              text:  @js(__('Click this tab to manage your Statement of Applicability (SoA) and compile the CAPA remediation plan.')) },
            /* ── 12: Compliance Center Controls Table ───────── */
            { path: ROUTES.workspace,  target: '#workspace-controls-table', icon: 'fa-list-check',   color: '#0891b2',
              title: @js(__('Compliance Center – Controls Table')),
              text:  @js(__('For each control, you can mark applicability, add justification, select a Maturity Rating (0-5), assign a PIC, set due dates, and update actions.')) },

            /* ── 13: Analytics Sidebar ──────────────────────── */
            { path: ROUTES.analytics,  target: '#sidebar-analytics',   icon: 'fa-chart-line',        color: '#059669',
              title: @js(__('Strategic Analytics – Trends')),
              text:  @js(__('View your compliance progress over time with trend charts. Compare scores across multiple audit sessions to measure improvement.')) },
            /* ── 14: Analytics Radar Scope ───────────────────── */
            { path: ROUTES.analytics,  target: '#analytics-radar-section', icon: 'fa-compass',        color: '#059669',
              title: @js(__('Strategic Analytics – Domain Pillars')),
              text:  @js(__('The maturity scope radar and pillar breakdown show your organization\'s strengths and weaknesses across ISO 27001:2022 pillars.')) },
            /* ── 15: Analytics Generate Summary ──────────────── */
            { path: ROUTES.analytics,  target: '#btn-generate-summary', icon: 'fa-wand-magic-sparkles', color: '#059669',
              title: @js(__('Strategic Analytics – AI Executive Summary')),
              text:  @js(__('Click \'Regenerate Summary\' to generate a one-click AI Executive Summary report powered by the local Ollama engine.')) },

            /* ── 16: Knowledge Base Sidebar ──────────────────── */
            { path: ROUTES.knowledge,  target: '#sidebar-knowledge',   icon: 'fa-book-open',         color: '#d97706',
              title: @js(__('Knowledge Base – Find Articles')),
              text:  @js(__('Search for ISO 27001:2022 implementation guides, policy templates, and best-practice articles. Use categories to narrow results.')) },
            /* ── 17: Knowledge Base Search Bar ───────────────── */
            { path: ROUTES.knowledge,  target: '#kb-search-bar',       icon: 'fa-magnifying-glass',  color: '#d97706',
              title: @js(__('Knowledge Base – Search Bar')),
              text:  @js(__('Use the search input to quickly find specific compliance standards, control notes, or uploaded policies.')) },
            /* ── 18: Knowledge Base Create Article ───────────── */
            { path: ROUTES.knowledge,  target: '#btn-create-article',  icon: 'fa-plus-circle',       color: '#d97706',
              title: @js(__('Knowledge Base – Create Articles')),
              text:  @js(__('Click \'Add New\' to create your own knowledge article. You can attach PDF or DOCX files as evidence references.')) },

            /* ── 19: Community Sidebar ──────────────────────── */
            { path: ROUTES.community,  target: '#sidebar-community',   icon: 'fa-users',             color: '#9333ea',
              title: @js(__('Community Hub – Browse Templates')),
              text:  @js(__('Explore blank document templates shared by other ISO 27001:2022 practitioners. Templates contain NO real company data – only structural formats.')) },
            /* ── 20: Community Template Grid ────────────────── */
            { path: ROUTES.community,  target: '#community-template-grid', icon: 'fa-grip',          color: '#9333ea',
              title: @js(__('Community Hub – Template Grid')),
              text:  @js(__('Review available templates, check their ratings, popularity, and descriptions before importing them.')) },
            /* ── 21: Community Clone Button ──────────────────── */
            { path: ROUTES.community,  target: '#btn-clone-first',     icon: 'fa-clone',             color: '#9333ea',
              title: @js(__('Community Hub – Clone a Template')),
              text:  @js(__('Found a useful template? Click \'Clone\' to instantly copy it into your own Knowledge Base for customization.')) },

            /* ── 22: Audit Trail Sidebar ────────────────────── */
            { path: ROUTES.auditTrail, target: '#sidebar-audit-trail', icon: 'fa-clock-rotate-left', color: '#ea580c',
              title: @js(__('Audit Trail – Activity History')),
              text:  @js(__('Every change to a control score or evidence note is recorded here with a timestamp and the responsible user.')) },
            /* ── 23: Audit Trail Filter ─────────────────────── */
            { path: ROUTES.auditTrail, target: '#audit-trail-filter',  icon: 'fa-filter',            color: '#ea580c',
              title: @js(__('Audit Trail – Search & Filters')),
              text:  @js(__('Use the search input or session dropdown to filter activity logs and pinpoint specific compliance changes.')) },
            /* ── 24: Audit Trail Export ─────────────────────── */
            { path: ROUTES.auditTrail, target: '#btn-export-csv',      icon: 'fa-download',          color: '#ea580c',
              title: @js(__('Audit Trail – Export Log')),
              text:  @js(__('Click \'Export CSV\' to download the full audit log for offline review or to present as evidence during external certification audits.')) }
        ];
        var current = 0;

        /* ─── helpers ─────────────────────────────────────────── */
        function saveState()  { localStorage.setItem(LS_ACTIVE,'true'); localStorage.setItem(LS_STEP, current); }
        function clearState() { localStorage.removeItem(LS_ACTIVE); localStorage.removeItem(LS_STEP); }

        function pageMatchesStep(step) {
            if (!step || !step.path) return true;
            return window.location.pathname === new URL(step.path, window.location.origin).pathname;
        }

        function goToStep(idx) {
            current = idx;
            saveState();
            if (typeof Turbo !== 'undefined') {
                Turbo.visit(steps[idx].path);
            } else {
                window.location.href = steps[idx].path;
            }
        }

        /* ─── render / position ──────────────────────────────── */
        function render() {
            isNavigating = false;
            var s = steps[current];
            document.getElementById('gt-step-num').textContent   = current + 1;
            document.getElementById('gt-step-total').textContent = steps.length;
            document.getElementById('gt-title').textContent = s.title;
            document.getElementById('gt-text').textContent  = s.text;
            
            var btnPrev = document.getElementById('gt-prev');
            if (btnPrev) { btnPrev.style.opacity = current === 0 ? '0.4' : '1'; btnPrev.style.pointerEvents = current === 0 ? 'none' : 'auto'; }
            
            var btnNext = document.getElementById('gt-next');
            if (btnNext) { btnNext.textContent = current === steps.length - 1 ? '{{ addslashes(__('Finish')) }}' : '{{ addslashes(__('Next')) }}'; btnNext.style.opacity = '1'; btnNext.style.pointerEvents = 'auto'; }
            var iconEl = document.getElementById('gt-icon');
            if (iconEl && s.icon) {
                iconEl.className = 'fa-solid ' + s.icon + ' text-sm';
                iconEl.parentElement.style.backgroundColor = (s.color || '#2563eb') + '1a';
                iconEl.style.color = s.color || '#2563eb';
            }
            var pct = Math.round(((current + 1) / steps.length) * 100);
            var bar = document.getElementById('gt-progress');
            if (bar) bar.style.width = pct + '%';
            saveState();
            position();
        }

        function position() {
            var s = steps[current];
            var el = document.querySelector(s.target);
            var bubble = document.getElementById('gt-bubble');
            if (!el || !bubble) return;
            document.querySelectorAll('.gt-highlight').forEach(function(e){ e.classList.remove('gt-highlight','ring-2','ring-blue-400','ring-offset-2','rounded-xl'); });
            el.classList.add('gt-highlight','ring-2','ring-blue-400','ring-offset-2','rounded-xl');
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            var rect    = el.getBoundingClientRect();
            var bubbleW = bubble.offsetWidth  || 320;
            var bubbleH = bubble.offsetHeight || 220;
            var vw = window.innerWidth, vh = window.innerHeight;
            
            // Default: try placing to the right of the element
            var top  = rect.top + (rect.height / 2) - (bubbleH / 2);
            var left = rect.right + 14;
            
            var arL  = document.getElementById('gt-arrow-left');
            var arR  = document.getElementById('gt-arrow-right');
            
            if (arL) arL.style.display = '';
            if (arR) arR.style.display = 'none';

            // If overflows right...
            if (left + bubbleW > vw - 16) {
                // Try placing to the left
                left = rect.left - bubbleW - 14;
                if (arL) arL.style.display = 'none';
                if (arR) arR.style.display = '';
                
                // If it ALSO overflows left (element is too wide or too centered)
                if (left < 16) {
                    // Fallback: Center horizontally
                    left = (vw / 2) - (bubbleW / 2);
                    // Place below the element
                    top = rect.bottom + 14;
                    // Hide side arrows because we are positioned above/below now
                    if (arL) arL.style.display = 'none';
                    if (arR) arR.style.display = 'none';
                    
                    // If placing below overflows the bottom of the viewport, place it above instead
                    if (top + bubbleH > vh - 16) {
                        top = rect.top - bubbleH - 14;
                    }
                }
            }
            
            // Ensure the bubble itself doesn't bleed off the screen in edge cases
            left = Math.max(16, Math.min(left, vw - bubbleW - 16));
            top = Math.max(16, Math.min(top, vh - bubbleH - 16));
            
            bubble.style.top  = top  + 'px';
            bubble.style.left = left + 'px';
        }

        /* ─── overlay helper ─────────────────────────────────── */
        function openOverlay() {
            try { var r = document.querySelector('[x-data]'); if (r && r._x_dataStack) r._x_dataStack[0].sidebarOpen = true; } catch(e) {}
            document.getElementById('gt-overlay').style.display = '';
        }

        /* ─── public API ─────────────────────────────────────── */
        function start() {
            var modal = document.querySelector('[x-show="showWelcomeGuide"]');
            if (modal) modal.style.display = 'none';
            current = 0;
            openOverlay();
            setTimeout(render, 380);
        }

        function resume() {
            var n = parseInt(localStorage.getItem(LS_STEP) || '0', 10);
            if (isNaN(n) || n < 0 || n >= steps.length) n = 0;
            current = n;
            openOverlay();
            setTimeout(render, 380);
        }

        var isNavigating = false;

        function next() {
            if (isNavigating) return;
            if (current >= steps.length - 1) { end(); return; }
            var ni = current + 1;
            if (!pageMatchesStep(steps[ni])) { 
                isNavigating = true; 
                var btn = document.getElementById('gt-next');
                if (btn) { btn.style.opacity = '0.5'; btn.style.pointerEvents = 'none'; btn.textContent = '...'; }
                goToStep(ni); 
                return; 
            }
            current = ni; render();
        }

        function prev() {
            if (isNavigating) return;
            if (current <= 0) return;
            var pi = current - 1;
            if (!pageMatchesStep(steps[pi])) { 
                isNavigating = true; 
                var btn = document.getElementById('gt-prev');
                if (btn) { btn.style.opacity = '0.5'; btn.style.pointerEvents = 'none'; }
                goToStep(pi); 
                return; 
            }
            current = pi; render();
        }

        function end() {
            clearState();
            document.getElementById('gt-overlay').style.display = 'none';
            document.querySelectorAll('.gt-highlight').forEach(function(e){ e.classList.remove('gt-highlight','ring-2','ring-blue-400','ring-offset-2','rounded-xl'); });
        }

        /* ─── auto-resume on load ────────────────────────────── */
        function initOnLoad() {
            if (localStorage.getItem(LS_ACTIVE) !== 'true') return;
            var n = parseInt(localStorage.getItem(LS_STEP) || '0', 10);
            if (isNaN(n) || n < 0 || n >= steps.length) { clearState(); return; }
            if (pageMatchesStep(steps[n])) { resume(); }
        }

        document.addEventListener('DOMContentLoaded', initOnLoad);
        document.addEventListener('turbo:load', initOnLoad);
        window.addEventListener('resize', function(){ if(document.getElementById('gt-overlay').style.display !== 'none') position(); });

        return { start: start, resume: resume, next: next, prev: prev, end: end };
    })();
    </script>

    {{-- ============================================================ --}}
    {{-- LAYER 2: FLOATING HELP BUTTON & CONTEXTUAL PANEL             --}}
    {{-- ============================================================ --}}
    <div class="fixed bottom-6 right-6 z-[250]" x-data="{ helpPanelOpen: false }">
        
        {{-- Floating Button --}}
        <button @click="helpPanelOpen = !helpPanelOpen" id="floating-help-btn"
            class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-2xl shadow-xl shadow-blue-600/30 flex items-center justify-center transition-all hover:scale-110 active:scale-95 border-2 border-white/20"
            :class="helpPanelOpen ? 'rotate-45 scale-110' : ''"
            title="{{ __('Need Help?') }}">
            <i class="fa-solid fa-circle-question text-lg" x-show="!helpPanelOpen"></i>
            <i class="fa-solid fa-xmark text-lg" x-show="helpPanelOpen" x-cloak></i>
        </button>

        {{-- Help Panel --}}
        <div x-show="helpPanelOpen" @click.away="helpPanelOpen = false" x-cloak
            class="absolute bottom-16 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2">

            {{-- Panel Header --}}
            <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center border border-white/20">
                        <i class="fa-solid fa-circle-question text-xs"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black tracking-tight">{{ __('Quick Help') }}</p>
                        <p class="text-[9px] text-blue-200 font-bold uppercase tracking-widest">{{ __('Contextual Guide') }}</p>
                    </div>
                </div>
            </div>

            {{-- Contextual Tips --}}
            <div class="p-4 space-y-3 max-h-72 overflow-y-auto">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('How to use this page:') }}</p>

                @php
                    $helpItems = [];
                    $helpTitle = '';
                    $helpLinks = [];

                    if (request()->routeIs('dashboard')) {
                        $helpTitle = __('Dashboard Guide');
                        $helpItems = [
                            ['icon' => 'fa-chart-pie', 'color' => 'blue', 'text' => __('This dashboard shows your overall compliance posture. The cards at the top display your compliance score, priority gaps, and maturity level.')],
                            ['icon' => 'fa-play', 'color' => 'indigo', 'text' => __("Click 'Continue' on the session banner to resume your latest audit.")],
                            ['icon' => 'fa-chart-line', 'color' => 'violet', 'text' => __('The compliance trend chart shows your progress across completed audit sessions.')],
                        ];
                        $helpLinks = [
                            ['label' => __('Audit Sessions'), 'url' => route('sessions.index')],
                            ['label' => __('Compliance Center'), 'url' => route('workspace.index')],
                        ];
                    } elseif (request()->routeIs('sessions.*')) {
                        $helpTitle = __('Audit Sessions Guide');
                        $helpItems = [
                            ['icon' => 'fa-plus', 'color' => 'blue', 'text' => __("Click '+ New Assessment' in the sidebar or the button on this page to create a new session.")],
                            ['icon' => 'fa-layer-group', 'color' => 'indigo', 'text' => __('Each session is independent — you can run multiple sessions for different periods or departments.')],
                            ['icon' => 'fa-arrow-right', 'color' => 'emerald', 'text' => __('After creating a session, click on it to start answering the 93 ISO 27001:2022 controls.')],
                        ];
                        $helpLinks = [
                            ['label' => __('Compliance Center'), 'url' => route('workspace.index')],
                        ];
                    } elseif (request()->routeIs('results.*')) {
                        $helpTitle = __('Assessment Guide');
                        $helpItems = [
                            ['icon' => 'fa-star', 'color' => 'amber', 'text' => __('For each control, select a maturity score from 1 (None) to 5 (Optimized).')],
                            ['icon' => 'fa-pen', 'color' => 'blue', 'text' => __('Write your implementation evidence or notes in the text box provided.')],
                            ['icon' => 'fa-robot', 'color' => 'violet', 'text' => __('Use the AI button to get intelligent recommendations for each control.')],
                        ];
                        $helpLinks = [];
                    } elseif (request()->routeIs('workspace.*')) {
                        $helpTitle = __('Compliance Center Guide');
                        $helpItems = [
                            ['icon' => 'fa-filter', 'color' => 'blue', 'text' => __('Select an active session from the dropdown to view its controls and gaps.')],
                            ['icon' => 'fa-triangle-exclamation', 'color' => 'rose', 'text' => __("Controls with maturity score 1-3 are flagged as 'Gaps' and need remediation.")],
                            ['icon' => 'fa-file-alt', 'color' => 'indigo', 'text' => __("Use the 'Gap Report' tab to see a summary of all identified gaps.")],
                        ];
                        $helpLinks = [
                            ['label' => __('Audit Sessions'), 'url' => route('sessions.index')],
                        ];
                    } elseif (request()->routeIs('community.*')) {
                        $helpTitle = __('Community Guide');
                        $helpItems = [
                            ['icon' => 'fa-search', 'color' => 'blue', 'text' => __('Browse templates shared by other ISO 27001 practitioners.')],
                            ['icon' => 'fa-shield-halved', 'color' => 'rose', 'text' => __('Only share BLANK templates — never upload documents containing real company data.')],
                            ['icon' => 'fa-clone', 'color' => 'purple', 'text' => __("Click 'Clone' to copy a template into your own Knowledge Base for customization.")],
                        ];
                        $helpLinks = [
                            ['label' => __('Knowledge Base'), 'url' => route('knowledge-base.index')],
                        ];
                    } elseif (request()->routeIs('knowledge-base.*')) {
                        $helpTitle = __('Knowledge Base Guide');
                        $helpItems = [
                            ['icon' => 'fa-magnifying-glass', 'color' => 'blue', 'text' => __('Search for articles, policies, or guides using the search bar at the top.')],
                            ['icon' => 'fa-plus-circle', 'color' => 'emerald', 'text' => __("Create your own knowledge articles using the 'Add New' button.")],
                            ['icon' => 'fa-paperclip', 'color' => 'indigo', 'text' => __('Attach files (PDF, DOCX) as evidence references to each article.')],
                        ];
                        $helpLinks = [
                            ['label' => __('Community Hub'), 'url' => route('community.index')],
                        ];
                    } elseif (request()->routeIs('audit-trail.*')) {
                        $helpTitle = __('Audit Trail Guide');
                        $helpItems = [
                            ['icon' => 'fa-clock-rotate-left', 'color' => 'amber', 'text' => __('This page records all changes made to assessment controls in the system.')],
                            ['icon' => 'fa-calendar', 'color' => 'blue', 'text' => __('Use the date filter to narrow down activity for a specific period.')],
                            ['icon' => 'fa-download', 'color' => 'emerald', 'text' => __('Export the log as CSV for offline review or compliance reporting.')],
                        ];
                        $helpLinks = [];
                    } else {
                        $helpTitle = __('Platform Guide');
                        $helpItems = [
                            ['icon' => 'fa-list-check', 'color' => 'blue', 'text' => __('Audit Sessions') . ': ' . __('Create and manage your ISO 27001:2022 assessment sessions. Each session covers all 93 controls.')],
                            ['icon' => 'fa-diagram-project', 'color' => 'indigo', 'text' => __('Compliance Center') . ': ' . __('Fill in control evaluations, identify gaps, and manage remediation plans (CAPA).')],
                        ];
                        $helpLinks = [
                            ['label' => __('Audit Sessions'), 'url' => route('sessions.index')],
                            ['label' => __('Compliance Center'), 'url' => route('workspace.index')],
                            ['label' => __('Strategic Analytics'), 'url' => route('reports.strategic')],
                        ];
                    }
                @endphp

                <p class="text-[10px] font-black text-slate-700">{{ $helpTitle }}</p>

                @foreach($helpItems as $item)
                <div class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-{{ $item['color'] }}-50 text-{{ $item['color'] }}-600 flex items-center justify-center shrink-0 border border-{{ $item['color'] }}-100 mt-0.5">
                        <i class="fa-solid {{ $item['icon'] }} text-[9px]"></i>
                    </div>
                    <p class="text-[11px] font-medium text-slate-600 leading-snug">{{ $item['text'] }}</p>
                </div>
                @endforeach

                @if(count($helpLinks) > 0)
                <div class="pt-2 border-t border-slate-100 space-y-1">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('Useful Links') }}</p>
                    @foreach($helpLinks as $link)
                    <a href="{{ $link['url'] }}" 
                        class="flex items-center gap-1.5 text-[10px] font-bold text-blue-600 hover:text-blue-800 transition-colors group">
                        <i class="fa-solid fa-arrow-right text-[8px] group-hover:translate-x-0.5 transition-transform"></i>
                        {{ $link['label'] }}
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Panel Footer --}}
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                <button @click="resetGuide(); helpPanelOpen = false" 
                    class="text-[9px] font-bold text-slate-400 hover:text-indigo-600 transition-colors underline">
                    {{ __('Show this guide again') }}
                </button>
                <button @click="helpPanelOpen = false" onclick="window.GuidedTour && window.GuidedTour.start()"
                    class="px-2.5 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 text-[9px] font-black rounded-lg transition-colors border border-blue-100">
                    <i class="fa-solid fa-play text-[8px] mr-1"></i>{{ __('Start Tour') }}
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
