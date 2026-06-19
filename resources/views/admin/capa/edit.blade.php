@extends('layouts.admin')

@section('title', 'Manage CAPA Plan')
@section('header_title', 'Manage CAPA Plan')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.capa.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Back to CAPA Plan
    </a>
</div>

<div class="space-y-6 max-w-4xl">
    {{-- Main CAPA Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-xl font-black text-slate-800">Manage Corrective Action</h2>
                    <p class="text-sm text-slate-500">For {{ $capa->standard->code }} - {{ $capa->standard->title }} (User: {{ $capa->session->user->name }})</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                    Maturity Rating: {{ $capa->maturity_rating }} / 5
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.capa.update', $capa) }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- User Details (ReadOnly) --}}
                <div>
                    <label class="block text-sm font-bold text-slate-500 mb-1">User & Session Details</label>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs leading-relaxed">
                        <strong>User:</strong> {{ $capa->session->user->name }} ({{ $capa->session->user->email }})<br>
                        <strong>Session:</strong> {{ $capa->session->name }}<br>
                        <strong>Sector/Scale:</strong> {{ $capa->session->user->business_sector ?: '-' }} / {{ $capa->session->user->organization_scale ?: '-' }}
                    </div>
                </div>

                {{-- Findings / Audit Notes (ReadOnly) --}}
                <div>
                    <label class="block text-sm font-bold text-slate-500 mb-1">Audit Findings & Notes</label>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs h-[82px] overflow-y-auto leading-relaxed
                        [&::-webkit-scrollbar]:w-1.5
                        [&::-webkit-scrollbar-track]:bg-slate-100
                        [&::-webkit-scrollbar-thumb]:bg-slate-300
                        [&::-webkit-scrollbar-thumb]:rounded-full">
                        {{ $capa->notes ?: 'No finding notes provided during assessment.' }}
                    </div>
                </div>

                <hr class="md:col-span-2 border-slate-200">

                {{-- CAPA Status --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">CAPA Status <span class="text-red-500">*</span></label>
                    <select name="treatment_status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="open" {{ old('treatment_status', $capa->treatment_status ?: 'open') == 'open' ? 'selected' : '' }}>Open (Belum Ditindaklanjuti)</option>
                        <option value="in_progress" {{ old('treatment_status', $capa->treatment_status) == 'in_progress' ? 'selected' : '' }}>In Progress (Sedang Dikerjakan)</option>
                        <option value="completed" {{ old('treatment_status', $capa->treatment_status) == 'completed' ? 'selected' : '' }}>Completed / Resolved (Selesai/Lunas)</option>
                    </select>
                    @error('treatment_status') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Risk Priority --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Risk Priority <span class="text-red-500">*</span></label>
                    <select name="risk_priority" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="Low" {{ old('risk_priority', $capa->risk_priority ?: 'Low') == 'Low' ? 'selected' : '' }}>Low Risk</option>
                        <option value="Medium" {{ old('risk_priority', $capa->risk_priority) == 'Medium' ? 'selected' : '' }}>Medium Risk</option>
                        <option value="High" {{ old('risk_priority', $capa->risk_priority) == 'High' ? 'selected' : '' }}>High Risk</option>
                        <option value="Critical" {{ old('risk_priority', $capa->risk_priority) == 'Critical' ? 'selected' : '' }}>Critical Risk</option>
                    </select>
                    @error('risk_priority') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- PIC --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Person in Charge (PIC)</label>
                    <input type="text" name="treatment_pic" value="{{ old('treatment_pic', $capa->treatment_pic) }}" placeholder="e.g. Budi (IT Manager)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('treatment_pic') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Due Date --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Due Date</label>
                    <input type="date" name="treatment_due_date" value="{{ old('treatment_due_date', $capa->treatment_due_date ? $capa->treatment_due_date->format('Y-m-d') : '') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('treatment_due_date') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Action Plan Text --}}
                @php
                    $planData = $capa->corrective_action_plan ?: [];
                    $actionText = is_array($planData) ? ($planData['action'] ?? '') : '';
                @endphp
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Corrective & Preventive Action Plan <span class="text-red-500">*</span></label>
                    <textarea name="corrective_action_plan_text" rows="4" placeholder="Describe the remediation steps, policies to implement, or technical actions needed..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>{{ old('corrective_action_plan_text', $actionText) }}</textarea>
                    @error('corrective_action_plan_text') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-200">
                <a href="{{ route('admin.capa.index') }}" class="px-6 py-2.5 rounded-lg font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all hover:shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Audit Trail Timeline --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
            <h3 class="font-bold text-slate-800 text-sm">CAPA Modification Timeline</h3>
        </div>
        <div class="p-6">
            @forelse($history as $log)
                <div class="relative pl-6 pb-6 last:pb-0 border-l border-slate-200 last:border-transparent">
                    <span class="absolute left-0 top-1.5 -translate-x-1/2 w-3.5 h-3.5 rounded-full bg-blue-50 border-2 border-blue-500 flex items-center justify-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    </span>
                    <div class="text-xs">
                        <span class="font-bold text-slate-800">{{ $log->user->name ?? 'System' }}</span>
                        <span class="text-slate-400 font-medium ml-2">{{ $log->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 mt-1 uppercase tracking-wider">
                        Changed field: <span class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">{{ str_replace('_', ' ', $log->field_changed) }}</span>
                    </p>
                    <div class="grid grid-cols-2 gap-4 mt-2 max-w-lg bg-slate-50 p-2 rounded-lg border border-slate-150 text-[11px]">
                        <div>
                            <span class="text-slate-400 uppercase font-black tracking-widest text-[8px] block">Previous Value</span>
                            <span class="text-slate-600 font-bold italic">{{ $log->old_value ?: 'None' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 uppercase font-black tracking-widest text-[8px] block">New Value</span>
                            <span class="text-slate-800 font-bold">{{ $log->new_value ?: 'None' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-slate-500 text-xs">
                    <i class="fa-solid fa-history text-2xl mb-2 text-slate-300 block"></i>
                    <p>No modifications tracked yet for this CAPA Plan.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
