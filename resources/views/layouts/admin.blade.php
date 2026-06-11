<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | ISO 27001:2022</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@hotwired/turbo@7.3.0/dist/turbo.es2017-umd.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        @media print {
            aside, header { display: none !important; }
            .flex-1 { margin-left: 0 !important; }
            main { overflow: visible !important; height: auto !important; padding: 0 !important; }
            body { overflow: visible !important; }
        }
    </style>
</head>
<body class="h-full overflow-hidden text-slate-800 flex" x-data="{ sidebarOpen: true }">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col h-full transition-all duration-300 relative z-20"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full fixed'">
        
        <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950">
            <div class="flex items-center gap-3 text-white">
                <i class="fa-solid fa-shield-halved text-blue-500 text-xl"></i>
                <div>
                    <h1 class="font-bold text-sm leading-none">Admin Panel</h1>
                    <span class="text-[10px] text-slate-500 uppercase tracking-widest">ISO 27001:2022</span>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-line w-5 text-center"></i>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
            
            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-bold text-slate-500 uppercase tracking-widest">Management</p>
            </div>
            
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-users w-5 text-center"></i>
                <span class="text-sm font-medium">Users</span>
            </a>

            <a href="{{ route('admin.sessions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.sessions.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
                <span class="text-sm font-medium">Audit Sessions</span>
            </a>

            <a href="{{ route('admin.standards.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.standards.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-list-check w-5 text-center"></i>
                <span class="text-sm font-medium">ISO Standards</span>
            </a>

            <a href="{{ route('admin.knowledge.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.knowledge.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-book-open w-5 text-center"></i>
                <span class="text-sm font-medium">Knowledge Base</span>
            </a>

            <a href="{{ route('admin.capa.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.capa.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-triangle-exclamation w-5 text-center"></i>
                <span class="text-sm font-medium">CAPA Plan</span>
            </a>

            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                <span class="text-sm font-medium">Compliance Reports</span>
            </a>

            <a href="{{ route('admin.logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.logs.index') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                <span class="text-sm font-medium">System Logs</span>
            </a>

            <a href="{{ route('admin.community.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.community.*') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-file-contract w-5 text-center"></i>
                <span class="text-sm font-medium">Community</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-3 py-2 mb-2 rounded-lg transition-colors {{ request()->routeIs('admin.profile.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800' }}">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest">Administrator</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-red-400 hover:text-red-300 hover:bg-slate-800 transition-colors text-left">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span class="text-sm font-medium">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-full min-w-0 transition-all duration-300 bg-slate-50 relative"
         :class="sidebarOpen ? 'ml-0' : '-ml-64'">
         
        <!-- Mobile Toggle Button (Visible only on small screens) -->
        <button @click="sidebarOpen = !sidebarOpen" class="fixed bottom-4 right-4 z-50 w-12 h-12 bg-slate-900 text-white rounded-full shadow-lg flex items-center justify-center lg:hidden">
            <i class="fa-solid fa-bars"></i>
        </button>

        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 flex-shrink-0 z-10">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-slate-600 hidden lg:block">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <h2 class="text-lg font-bold text-slate-800">@yield('header_title', 'Admin Dashboard')</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full"><i class="fa-solid fa-user-shield text-blue-500 mr-2"></i>{{ Auth::user()->name }}</span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6" id="main-content">
            @if(session('success'))
                <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 flex items-center gap-3">
                    <i class="fa-solid fa-circle-check"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
