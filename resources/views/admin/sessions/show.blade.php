@extends('layouts.admin')

@section('title', $session->name . ' — Session Detail')
@section('header_title', 'Session Detail')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Back to Sessions
    </a>
</div>

{{-- Session Header --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                {{ $session->name }}
                <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-widest
                    {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($session->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                    {{ str_replace('_', ' ', $session->status) }}
                </span>
            </h2>
            <div class="flex items-center gap-4 mt-2 text-sm text-slate-500">
                <a href="{{ route('admin.users.show', $session->user_id) }}" class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                    <i class="fa-solid fa-user"></i>
                    {{ $session->user->name ?? 'Unknown' }}
                </a>
                <span class="flex items-center gap-1"><i class="fa-solid fa-calendar"></i> {{ $session->created_at->format('M d, Y H:i') }}</span>
                <span class="flex items-center gap-1"><i class="fa-solid fa-clock"></i> Updated {{ $session->updated_at->diffForHumans() }}</span>
            </div>
        </div>
        @php
            $score = $session->overall_maturity_score;
            $label = 'Initial';
            if ($score >= 4.5) $label = 'Optimized';
            elseif ($score >= 3.5) $label = 'Managed';
            elseif ($score >= 2.5) $label = 'Defined';
            elseif ($score >= 1.5) $label = 'Limited/Repeatable';
        @endphp
        <div class="text-center bg-slate-50 rounded-xl px-6 py-3 border border-slate-200">
            <div class="text-3xl font-black {{ $session->overall_maturity_score >= 4 ? 'text-emerald-600' : ($session->overall_maturity_score >= 2.5 ? 'text-amber-600' : 'text-red-600') }}">
                {{ number_format($session->overall_maturity_score, 2) }}
            </div>
            <div class="text-[9px] text-slate-400 font-black uppercase tracking-wider">Maturity: {{ $label }}</div>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    <div class="bg-white rounded-lg border border-slate-200 p-4 text-center">
        <div class="text-xl font-black text-slate-700">{{ $stats['total_controls'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4 text-center">
        <div class="text-xl font-black text-blue-600">{{ $stats['applicable'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Applicable</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4 text-center">
        <div class="text-xl font-black text-slate-700">{{ $stats['completed'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Assessed</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4 text-center">
        <div class="text-xl font-black text-emerald-600">{{ $stats['compliant'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Compliant</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4 text-center">
        <div class="text-xl font-black text-amber-600">{{ $stats['partial'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Partial</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4 text-center">
        <div class="text-xl font-black text-red-600">{{ $stats['non_compliant'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Non-Comp</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4 text-center">
        <div class="text-xl font-black text-slate-400">{{ $stats['excluded'] }}</div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Excluded</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Maturity Distribution Chart --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-signal text-purple-500"></i> Maturity Distribution
        </h3>
        <div class="relative h-64 w-full">
            <canvas id="maturityChart"></canvas>
        </div>
    </div>

    {{-- Completion Progress --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-tasks text-blue-500"></i> Completion Progress
        </h3>
        <div class="flex flex-col items-center justify-center h-48">
            <div class="relative w-32 h-32">
                <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                    <circle cx="60" cy="60" r="50" fill="none" stroke="{{ $stats['completion_pct'] >= 80 ? '#10b981' : ($stats['completion_pct'] >= 50 ? '#f59e0b' : '#ef4444') }}" stroke-width="10" stroke-linecap="round"
                            stroke-dasharray="{{ 314 * $stats['completion_pct'] / 100 }} 314"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-black text-slate-800">{{ $stats['completion_pct'] }}%</span>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-3">{{ $stats['completed'] }} of {{ $stats['applicable'] }} controls assessed</p>
        </div>
    </div>

    {{-- AI Summary Preview --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-robot text-indigo-500"></i> AI Summary
        </h3>
        @if($session->ai_summary)
        <div class="text-xs text-slate-600 leading-relaxed max-h-52 overflow-y-auto prose prose-xs
                    [&::-webkit-scrollbar]:w-1.5
                    [&::-webkit-scrollbar-track]:bg-slate-100
                    [&::-webkit-scrollbar-track]:rounded-full
                    [&::-webkit-scrollbar-thumb]:bg-slate-300
                    [&::-webkit-scrollbar-thumb]:rounded-full">
            {!! \Illuminate\Support\Str::markdown(e($session->ai_summary)) !!}
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-48 text-slate-400">
            <i class="fa-solid fa-robot text-3xl mb-2"></i>
            <p class="text-sm">No AI summary generated yet</p>
        </div>
        @endif
    </div>
</div>

{{-- Critical Findings --}}
@if($criticalFindings->count() > 0)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
        <h3 class="font-bold text-slate-800">Critical Findings</h3>
        <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">{{ $criticalFindings->count() }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-red-50 text-xs uppercase font-bold text-red-500 border-b border-red-100">
                <tr>
                    <th class="px-6 py-3">Control</th>
                    <th class="px-6 py-3">Title</th>
                    <th class="px-6 py-3">Score</th>
                    <th class="px-6 py-3">CAPA Status</th>
                    <th class="px-6 py-3">PIC</th>
                    <th class="px-6 py-3">Due Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($criticalFindings as $finding)
                <tr class="hover:bg-red-50/50 transition-colors">
                    <td class="px-6 py-3 font-bold text-slate-800">{{ $finding->standard->code ?? 'N/A' }}</td>
                    <td class="px-6 py-3 text-slate-600 text-xs">{{ \Illuminate\Support\Str::limit($finding->standard->title ?? '', 50) }}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-bold">{{ $finding->maturity_rating }}/5</span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="text-xs font-bold {{ $finding->treatment_status === 'closed' ? 'text-emerald-600' : ($finding->treatment_status === 'in_progress' ? 'text-amber-600' : 'text-slate-500') }}">
                            {{ ucfirst(str_replace('_', ' ', $finding->treatment_status ?? 'open')) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-xs text-slate-600">{{ $finding->treatment_pic ?? '—' }}</td>
                    <td class="px-6 py-3 text-xs {{ $finding->treatment_due_date && $finding->treatment_due_date->isPast() ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                        {{ $finding->treatment_due_date ? $finding->treatment_due_date->format('M d, Y') : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Charts JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('turbo:load', function() {
    const canvas = document.getElementById('maturityChart');
    if (!canvas) return;

    // Destroy existing chart if it exists (Turbo.js fix)
    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Initial (L1)', 'Repeatable (L2)', 'Defined (L3)', 'Managed (L4)', 'Optimized (L5)'],
            datasets: [{
                data: @json($maturityDistribution),
                backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6'],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false }
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    ticks: { 
                        stepSize: 1,
                        color: '#94a3b8',
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: {
                        color: '#94a3b8',
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 9, weight: 'bold' }
                    },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endsection
