@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header_title', 'System Overview')

@section('content')
{{-- Top Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Total Users</h3>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-users text-lg"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ number_format($totalUsers) }}</div>
        <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium mt-2 inline-block">View all →</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Active Sessions</h3>
            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-spinner text-lg"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ number_format($activeSessions) }}</div>
        <span class="text-xs text-slate-400">of {{ $totalSessions }} total sessions</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Completed</h3>
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-check-double text-lg"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ number_format($completedSessions) }}</div>
        <a href="{{ route('admin.sessions.index', ['status' => 'completed']) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium mt-2 inline-block">View completed →</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Avg Maturity</h3>
            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-chart-pie text-lg"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-slate-800">{{ number_format($averageScore, 1) }} <span class="text-base text-slate-400 font-medium">/ 5.0</span></div>
    </div>
</div>

{{-- CAPA Alert Banner --}}
@if($pendingCapa > 0 || $overdueCapa > 0)
<div class="mb-8 bg-gradient-to-r from-red-50 to-amber-50 border border-red-200 rounded-xl p-5 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-800">CAPA Tasks Require Attention</h3>
            <p class="text-sm text-slate-600 mt-0.5">
                <span class="font-bold text-red-600">{{ $overdueCapa }} overdue</span> · 
                <span class="font-bold text-amber-600">{{ $pendingCapa }} pending</span> corrective action tasks
            </p>
        </div>
    </div>
</div>
@endif

{{-- System Alert Banner (Avg Score) --}}
@if($averageScore > 0 && $averageScore < 2.5)
<div class="mb-8 bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl p-5 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-engine-warning text-xl"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-800">Critical Compliance Alert</h3>
            <p class="text-sm text-slate-600 mt-0.5">
                The global average maturity score is <span class="font-bold text-red-600">{{ number_format($averageScore, 2) }}</span>. This indicates a critical lack of compliance across all users.
            </p>
        </div>
    </div>
</div>
@endif

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    {{-- User Growth Chart --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-area text-blue-500"></i> User Registrations
        </h3>
        <div class="relative h-64 w-full">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    {{-- Session Activity Chart --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-emerald-500"></i> Audit Sessions Created
        </h3>
        <div class="relative h-64 w-full">
            <canvas id="sessionActivityChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Maturity Distribution --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-signal text-purple-500"></i> Global Maturity Distribution
        </h3>
        <div class="relative h-64 w-full">
            <canvas id="maturityChart"></canvas>
        </div>
    </div>

    {{-- Sector Distribution --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-building text-teal-500"></i> Business Sectors
        </h3>
        @if(count($sectorDistribution) > 0)
        <div class="relative h-64 w-full">
            <canvas id="sectorChart"></canvas>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-64 text-slate-400">
            <i class="fa-solid fa-chart-pie text-3xl mb-2"></i>
            <p class="text-sm">No sector data yet</p>
        </div>
        @endif
    </div>

    {{-- Quick Stats --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-layer-group text-amber-500"></i> Platform Summary
        </h3>
        <div class="space-y-3">
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <span class="text-sm text-slate-600 flex items-center gap-2"><i class="fa-solid fa-users text-slate-400 w-4"></i> Total Users</span>
                <span class="font-bold text-slate-800">{{ $totalUsers }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <span class="text-sm text-slate-600 flex items-center gap-2"><i class="fa-solid fa-clipboard-list text-slate-400 w-4"></i> Total Sessions</span>
                <span class="font-bold text-slate-800">{{ $totalSessions }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <span class="text-sm text-slate-600 flex items-center gap-2"><i class="fa-solid fa-file-contract text-slate-400 w-4"></i> Community Templates</span>
                <span class="font-bold text-slate-800">{{ $totalTemplates }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <span class="text-sm text-slate-600 flex items-center gap-2"><i class="fa-solid fa-book-open text-slate-400 w-4"></i> KB Articles</span>
                <span class="font-bold text-slate-800">{{ $totalArticles ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <span class="text-sm text-slate-600 flex items-center gap-2"><i class="fa-solid fa-download text-slate-400 w-4"></i> Template Downloads</span>
                <span class="font-bold text-slate-800">{{ $totalDownloads }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <span class="text-sm text-slate-600 flex items-center gap-2"><i class="fa-solid fa-clock text-amber-400 w-4"></i> Pending CAPA</span>
                <span class="font-bold text-amber-600">{{ $pendingCapa }}</span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-600 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-red-400 w-4"></i> Overdue CAPA</span>
                <span class="font-bold text-red-600">{{ $overdueCapa }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity Tables --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recent Users --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">Recent Registrations</h3>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">View All</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentUsers as $user)
            <a href="{{ route('admin.users.show', $user) }}" class="p-4 hover:bg-slate-50 flex items-center justify-between block">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900">{{ $user->name }}</div>
                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="text-xs font-medium text-slate-400 text-right">
                    <div>{{ $user->created_at->diffForHumans() }}</div>
                    @if($user->organization_name)
                    <div class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded inline-block mt-1">{{ $user->organization_name }}</div>
                    @endif
                </div>
            </a>
            @empty
            <div class="p-8 text-center text-slate-500 text-sm">No users registered yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Sessions --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">Recent Audit Activity</h3>
            <a href="{{ route('admin.sessions.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">View All</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentSessions as $session)
            <a href="{{ route('admin.sessions.show', $session) }}" class="p-4 hover:bg-slate-50 block">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-bold text-sm text-slate-900">{{ $session->name }}</div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($session->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ str_replace('_', ' ', $session->status) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-user text-slate-400"></i>
                        {{ $session->user->name ?? 'Unknown User' }}
                    </div>
                    <div>{{ $session->updated_at->diffForHumans() }}</div>
                </div>
            </a>
            @empty
            <div class="p-8 text-center text-slate-500 text-sm">No recent audit sessions.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Charts JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('turbo:load', function() {
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
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

    // User Growth
    initChart('userGrowthChart', {
        type: 'line',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                label: 'New Users',
                data: @json($userGrowthData),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
                pointRadius: 4,
            }]
        },
        options: { ...chartDefaults, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // Session Activity
    initChart('sessionActivityChart', {
        type: 'bar',
        data: {
            labels: @json($monthLabels),
            datasets: [{
                label: 'Sessions Created',
                data: @json($sessionActivityData),
                backgroundColor: ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#d1fae5', '#ecfdf5'],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: { ...chartDefaults, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // Maturity Distribution
    initChart('maturityChart', {
        type: 'doughnut',
        data: {
            labels: ['Level 1 (Initial)', 'Level 2 (Managed)', 'Level 3 (Defined)', 'Level 4 (Quantified)', 'Level 5 (Optimized)'],
            datasets: [{
                data: @json($maturityDistribution),
                backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { display: true, position: 'bottom', labels: { font: { size: 10 }, padding: 8, usePointStyle: true, pointStyle: 'circle' } }
            }
        }
    });

    // Sector Distribution
    @if(count($sectorDistribution) > 0)
    initChart('sectorChart', {
        type: 'pie',
        data: {
            labels: @json(array_keys($sectorDistribution)),
            datasets: [{
                data: @json(array_values($sectorDistribution)),
                backgroundColor: ['#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd', '#ddd6fe', '#ede9fe'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'bottom', labels: { font: { size: 10 }, padding: 8, usePointStyle: true, pointStyle: 'circle' } }
            }
        }
    });
    @endif
});
</script>
@endsection
