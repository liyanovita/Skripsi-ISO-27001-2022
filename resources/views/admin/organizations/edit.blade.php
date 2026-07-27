@extends('layouts.admin')

@section('title', 'Edit Organization')
@section('header_title', 'Edit Organization')

@section('content')
<style>
    .form-input {
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-input:focus {
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        border-color: #60a5fa;
        outline: none;
        background: #fff;
    }
    .field-group {
        transition: transform 0.2s ease;
    }
    .section-card {
        background: #fff;
        border-radius: 1.25rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 4px 0 rgba(30,58,138,0.04);
    }
</style>

<div class="max-w-2xl">
    {{-- Back Link --}}
    <a href="{{ route('admin.organizations.show', $organization) }}"
        class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-blue-600 transition-colors mb-5 font-medium group">
        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to {{ $organization->name }}
    </a>

    {{-- Page Title --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-blue-600/20">
            <i class="fa-solid fa-pen"></i>
        </div>
        <div>
            <h2 class="text-lg font-black text-slate-800">Edit Organization</h2>
            <p class="text-xs text-slate-400 font-medium">Update details for <strong class="text-slate-600">{{ $organization->name }}</strong></p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.organizations.update', $organization) }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Basic Info --}}
        <div class="section-card p-6 space-y-5">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-id-card text-blue-500"></i> Basic Information
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="field-group">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Organization Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $organization->name) }}" required
                        placeholder="e.g., PT Cyber Security Indonesia"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('name') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
                <div class="field-group">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Short Code <span class="text-slate-400 font-normal">(e.g. ACME)</span></label>
                    <input type="text" name="code" value="{{ old('code', $organization->code) }}"
                        placeholder="e.g., CYBER"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase">
                    @error('code') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="field-group">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Contact Email</label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $organization->contact_email) }}"
                            placeholder="info@organization.com"
                            class="form-input w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    @error('contact_email') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
                <div class="field-group">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Contact Phone</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $organization->contact_phone) }}"
                            placeholder="+62 812-3456-7890"
                            class="form-input w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    @error('contact_phone') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="field-group">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Business Sector') }}</label>
                    <select name="business_sector" class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="">{{ __('Select Business Sector…') }}</option>
                        @foreach(\App\Models\Organization::getBusinessSectors() as $sector)
                            <option value="{{ $sector }}" {{ old('business_sector', $organization->business_sector) == $sector ? 'selected' : '' }}>{{ $sector }}</option>
                        @endforeach
                    </select>
                    @error('business_sector') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
                <div class="field-group">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Organization Scale</label>
                    <select name="organization_scale"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="">Select Scale…</option>
                        <option value="Small" {{ old('organization_scale', $organization->organization_scale) == 'Small' ? 'selected' : '' }}>Small (1–50 Employees)</option>
                        <option value="Medium" {{ old('organization_scale', $organization->organization_scale) == 'Medium' ? 'selected' : '' }}>Medium (51–250 Employees)</option>
                        <option value="Large" {{ old('organization_scale', $organization->organization_scale) == 'Large' ? 'selected' : '' }}>Large (250+ Employees)</option>
                    </select>
                    @error('organization_scale') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            <div class="field-group">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Description</label>
                <textarea name="description" rows="2"
                    placeholder="Brief description of the organization and its business focus…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('description', $organization->description) }}</textarea>
                @error('description') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>

            <div class="field-group">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Office Address</label>
                <textarea name="address" rows="2"
                    placeholder="Full street address…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('address', $organization->address) }}</textarea>
                @error('address') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ISMS Context --}}
        <div class="section-card p-6 space-y-5">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-indigo-400"></i> ISMS Context <span class="text-[9px] font-medium text-slate-300 normal-case tracking-normal">ISO 27001:2022</span>
            </h3>

            <div class="field-group">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">IT Governance Structure</label>
                <textarea name="it_governance_structure" rows="3"
                    placeholder="Describe the IT reporting lines, roles, and security responsibilities…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('it_governance_structure', $organization->it_governance_structure) }}</textarea>
                @error('it_governance_structure') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>

            <div class="field-group">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">ISMS Scope</label>
                <textarea name="isms_scope" rows="3"
                    placeholder="Define the boundaries and applicability of the Information Security Management System…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('isms_scope', $organization->isms_scope) }}</textarea>
                @error('isms_scope') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-between items-center">
            <a href="{{ route('admin.organizations.show', $organization) }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20 flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
