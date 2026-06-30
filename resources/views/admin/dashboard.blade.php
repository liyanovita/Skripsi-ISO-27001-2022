@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header_title', 'System Overview')

@section('content')
<div class="space-y-8 pb-8">
    {{-- Header banner / welcome card --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 rounded-2xl text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-20 bottom-0 w-60 h-60 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-ping"></span>
                    <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest">System Active & Secure</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">Welcome back, Admin!</h1>
                <p class="text-slate-400 text-xs mt-1 max-w-xl">Monitor your organization's global ISO 27001:2022 security governance, manage users, customize standards, and audit platform activities from this command center.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0 bg-white/5 backdrop-blur-md px-4 py-3 rounded-xl border border-white/10 shadow-lg">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                    <i class="fa-solid fa-server text-base"></i>
                </div>
                <div class="leading-none">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Laravel Framework</p>
                    <p class="text-sm font-black text-white mt-0.5">v11.x Stable</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        {{-- Card 1 --}}
        <a href="{{ route('admin.users.index') }}" class="block bg-white rounded-2xl border border-slate-100 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Users</h3>
                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-users text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">{{ number_format($totalUsers) }}</div>
            <div class="mt-4 flex items-center justify-between relative z-10">
                <span class="text-[10px] font-semibold text-slate-400">Registered Platform Accounts</span>
            </div>
        </a>

        {{-- Card 2 --}}
        <a href="{{ route('admin.sessions.index', ['status' => 'in_progress']) }}" class="block bg-white rounded-2xl border border-slate-100 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-amber-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Sessions</h3>
                <div class="w-9 h-9 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-spinner text-sm animate-spin-slow"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">{{ number_format($activeSessions) }}</div>
            <div class="mt-4 flex items-center justify-between relative z-10">
                <span class="text-[10px] font-semibold text-slate-400">Out of {{ $totalSessions }} total sessions</span>
            </div>
        </a>

        {{-- Card 3 --}}
        <a href="{{ route('admin.sessions.index', ['status' => 'completed']) }}" class="block bg-white rounded-2xl border border-slate-100 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Completed Sessions</h3>
                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-check-double text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">{{ number_format($completedSessions) }}</div>
            <div class="mt-4 flex items-center justify-between relative z-10">
                <span class="text-[10px] font-semibold text-slate-400">100% Finalized Audits</span>
            </div>
        </a>

        {{-- Card 4 --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-purple-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg Maturity</h3>
                <div class="w-9 h-9 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-chart-pie text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight relative z-10">
                {{ number_format($averageScore, 1) }} <span class="text-sm text-slate-400 font-medium">/ 5.0</span>
            </div>
            <div class="mt-4 flex items-center justify-between relative z-10">
                <span class="text-[10px] font-semibold text-slate-400">Global compliance level</span>
                <span class="px-2 py-0.5 bg-purple-50 text-purple-600 rounded text-[9px] font-black uppercase tracking-widest">
                    @if($averageScore >= 4.5) Optimized (Level 5) @elseif($averageScore >= 3.5) Managed (Level 4) @elseif($averageScore >= 2.5) Defined (Level 3) @elseif($averageScore >= 1.5) Repeatable (Level 2) @elseif($averageScore >= 0.5) Initial (Level 1) @else Non-existent (Level 0) @endif
                </span>
            </div>
        </div>

        {{-- Card 5: Suspended Users --}}
        <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}" class="block bg-white rounded-2xl border border-rose-100 p-5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-rose-500/5 rounded-full blur-xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <h3 class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Suspended</h3>
                <div class="w-9 h-9 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center shadow-sm border border-rose-100">
                    <i class="fa-solid fa-user-slash text-sm"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-rose-600 tracking-tight relative z-10">{{ number_format(\App\Models\User::where('status', 'suspended')->count()) }}</div>
            <div class="mt-4 flex items-center justify-between relative z-10">
                <span class="text-[10px] font-semibold text-slate-400">Blocked Platform Accounts</span>
            </div>
        </a>
    </div>

    {{-- CAPA Alert Banner --}}
    @if($pendingCapa > 0 || $overdueCapa > 0)
    <div class="bg-white rounded-2xl border border-rose-100 p-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-rose-500"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pl-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center shrink-0 border border-rose-100">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-950">CAPA Tasks Require Immediate Attention</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        There are <span class="font-extrabold text-rose-600">{{ $overdueCapa }} overdue</span> and <span class="font-extrabold text-amber-500">{{ $pendingCapa }} pending</span> corrective action tasks across active sessions.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.capa.index') }}" class="px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-black uppercase tracking-widest border border-rose-100 transition-all text-center shrink-0">
                Review CAPA Plans
            </a>
        </div>
    </div>
    @endif

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- User Growth Chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-blue-500"></i> Platform User Growth
                </h3>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Last 6 Months</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        {{-- Session Activity Chart --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-chart-bar text-emerald-500"></i> Audit Session Activity
                </h3>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Sessions Created</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="sessionActivityChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Distributions & Quick Stats Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Maturity Distribution --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex flex-col">
            <h3 class="text-sm font-black text-slate-900 tracking-tight mb-5 flex items-center gap-2">
                <i class="fa-solid fa-signal text-purple-500"></i> Global Maturity Level
            </h3>
            <div class="relative h-56 w-full flex-1">
                <canvas id="maturityChart"></canvas>
            </div>
        </div>

        {{-- Sector Distribution --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex flex-col">
            <h3 class="text-sm font-black text-slate-900 tracking-tight mb-5 flex items-center gap-2">
                <i class="fa-solid fa-building text-teal-500"></i> Industry Demographics
            </h3>
            @if(count($sectorDistribution) > 0)
            <div class="relative h-56 w-full flex-1">
                <canvas id="sectorChart"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-56 text-slate-400 flex-1">
                <i class="fa-solid fa-chart-pie text-3xl mb-2 text-slate-300"></i>
                <p class="text-[10px] font-bold uppercase tracking-widest">No sector data yet</p>
            </div>
            @endif
        </div>

        {{-- Platform Summary Card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <h3 class="text-sm font-black text-slate-900 tracking-tight mb-5 flex items-center gap-2">
                <i class="fa-solid fa-cubes text-amber-500"></i> Governance Indicators
            </h3>
            <div class="divide-y divide-slate-100">
                @php
                    $indicators = [
                        ['label' => 'Total Registered Users', 'value' => $totalUsers, 'icon' => 'fa-users', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50'],
                        ['label' => 'Total Audit Sessions', 'value' => $totalSessions, 'icon' => 'fa-clipboard-list', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
                        ['label' => 'Community Templates', 'value' => $totalTemplates, 'icon' => 'fa-file-contract', 'color' => 'text-teal-500', 'bg' => 'bg-teal-50'],
                        ['label' => 'Knowledge Base Articles', 'value' => $totalArticles ?? 0, 'icon' => 'fa-book-open', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
                        ['label' => 'Template Downloads', 'value' => $totalDownloads, 'icon' => 'fa-download', 'color' => 'text-slate-600', 'bg' => 'bg-slate-100'],
                        ['label' => 'Pending CAPA Items', 'value' => $pendingCapa, 'icon' => 'fa-clock', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
                        ['label' => 'Overdue CAPA alerts', 'value' => $overdueCapa, 'icon' => 'fa-triangle-exclamation', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50'],
                    ];
                @endphp
                @foreach($indicators as $ind)
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-slate-600 flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg {{ $ind['bg'] }} {{ $ind['color'] }} flex items-center justify-center shrink-0">
                            <i class="fa-solid {{ $ind['icon'] }} text-[10px]"></i>
                        </div>
                        {{ $ind['label'] }}
                    </span>
                    <span class="text-xs font-black text-slate-900">{{ $ind['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Activity Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Users --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-blue-500"></i> Recent User Registrations
                </h3>
                <a href="{{ route('admin.users.index') }}" class="text-[10px] font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest">
                    View All
                </a>
            </div>
            <div class="divide-y divide-slate-100 flex-1">
                @forelse($recentUsers as $user)
                <a href="{{ route('admin.users.show', $user) }}" class="p-4 hover:bg-slate-50 flex items-center justify-between transition-colors block">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-slate-900 truncate">{{ $user->name }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="text-xs font-medium text-slate-400 text-right shrink-0">
                        <div>{{ $user->created_at->diffForHumans() }}</div>
                        @if($user->organization_name)
                        <div class="text-[8px] font-black uppercase tracking-widest text-slate-500 bg-slate-50 border border-slate-200 px-1.5 py-0.5 rounded mt-1 inline-block">{{ $user->organization_name }}</div>
                        @endif
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs py-16 flex flex-col items-center justify-center">
                    <i class="fa-regular fa-user-circle text-3xl mb-2 text-slate-300"></i>
                    <p class="font-bold uppercase tracking-widest">No users registered yet.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Sessions --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> Recent Audit Activity
                </h3>
                <a href="{{ route('admin.sessions.index') }}" class="text-[10px] font-black text-emerald-600 hover:text-emerald-700 uppercase tracking-widest">
                    View All
                </a>
            </div>
            <div class="divide-y divide-slate-100 flex-1">
                @forelse($recentSessions as $session)
                <a href="{{ route('admin.sessions.show', $session) }}" class="p-4 hover:bg-slate-50 block transition-colors">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-bold text-sm text-slate-900 truncate max-w-[200px]">{{ $session->name }}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest {{ $session->status === 'completed' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : ($session->status === 'in_progress' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-slate-50 text-slate-500 border border-slate-200') }}">
                            {{ str_replace('_', ' ', $session->status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-5 h-5 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 text-[8px] font-bold">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span class="truncate text-[10px] font-bold text-slate-600">{{ $session->user->name ?? 'Unknown User' }}</span>
                        </div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $session->updated_at->diffForHumans() }}</div>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs py-16 flex flex-col items-center justify-center">
                    <i class="fa-regular fa-clipboard text-3xl mb-2 text-slate-300"></i>
                    <p class="font-bold uppercase tracking-widest">No recent audit sessions.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Charts JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('turbo:load', function() {
    // Style chart tooltips and fonts globally
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 10;
    Chart.defaults.color = '#94a3b8';

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 10,
                titleFont: { size: 11, weight: 'bold' },
                bodyFont: { size: 10 },
                cornerRadius: 8,
                displayColors: false
            }
        }
    };

    // Helper to safely initialize and destroy existing charts (Turbo.js fix)
    const initChart = (id, config) => {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }
        
        new Chart(canvas, config);
    };

    // User Growth Line Chart
    initChart('userGrowthChart', {
        type: 'line',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                label: 'New Users',
                data: @json($userGrowthData),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                fill: true,
                tension: 0.35,
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#3b82f6',
                pointHoverBorderWidth: 3,
                pointHoverRadius: 6,
                pointRadius: 3,
            }]
        },
        options: { 
            ...chartDefaults, 
            scales: { 
                y: { 
                    grid: { color: '#f1f5f9' },
                    border: { dash: [5, 5] },
                    beginAtZero: true, 
                    ticks: { stepSize: 1 } 
                },
                x: {
                    grid: { display: false }
                }
            } 
        }
    });

    // Session Activity Bar Chart
    initChart('sessionActivityChart', {
        type: 'bar',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                label: 'Sessions Created',
                data: @json($sessionActivityData),
                backgroundColor: '#10b981',
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 32,
            }]
        },
        options: { 
            ...chartDefaults, 
            scales: { 
                y: { 
                    grid: { color: '#f1f5f9' },
                    border: { dash: [5, 5] },
                    beginAtZero: true, 
                    ticks: { stepSize: 1 } 
                },
                x: {
                    grid: { display: false }
                }
            } 
        }
    });

    // Maturity Distribution Doughnut Chart
    initChart('maturityChart', {
        type: 'doughnut',
        data: {
            labels: ['Lvl 1 (Initial)', 'Lvl 2 (Limited/Repeatable)', 'Lvl 3 (Defined)', 'Lvl 4 (Managed)', 'Lvl 5 (Optimized)'],
            datasets: [{
                data: @json($maturityDistribution),
                backgroundColor: ['#ef4444', '#f97316', '#eab308', '#10b981', '#3b82f6'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { 
                    display: true, 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 8, 
                        boxHeight: 8, 
                        padding: 10,
                        usePointStyle: true, 
                        pointStyle: 'circle',
                        font: { size: 9, weight: 'bold' } 
                    } 
                }
            }
        }
    });

    // Sector Distribution Pie Chart
    @if(count($sectorDistribution) > 0)
    initChart('sectorChart', {
        type: 'pie',
        data: {
            labels: @json(array_keys($sectorDistribution)),
            datasets: [{
                data: @json(array_values($sectorDistribution)),
                backgroundColor: ['#6366f1', '#a855f7', '#ec4899', '#f43f5e', '#f59e0b', '#3b82f6'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true, 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 8, 
                        boxHeight: 8, 
                        padding: 10,
                        usePointStyle: true, 
                        pointStyle: 'circle',
                        font: { size: 9, weight: 'bold' } 
                    } 
                }
            }
        }
    });
    @endif
});
</script>
@endsection
