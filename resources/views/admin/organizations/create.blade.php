@extends('layouts.admin')

@section('title', 'Register Organization')
@section('header_title', 'Register Organization')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('admin.organizations.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Back to Organizations
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-building text-blue-500"></i> New Organization Details
            </h3>
        </div>

        <form method="POST" action="{{ route('admin.organizations.store') }}" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Organization Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g., PT Cyber Security Indonesia" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Short Code (Unique, e.g. ACME)</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g., CYBER" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="e.g., info@organization.com" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('contact_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="e.g., +62 812-3456-7890" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('contact_phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Business Sector</label>
                    <input type="text" name="business_sector" value="{{ old('business_sector') }}" placeholder="e.g., Banking, Tech, Healthcare" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('business_sector') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Organization Scale</label>
                    <select name="organization_scale" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select Scale</option>
                        <option value="Small" {{ old('organization_scale') == 'Small' ? 'selected' : '' }}>Small (1-50 Employees)</option>
                        <option value="Medium" {{ old('organization_scale') == 'Medium' ? 'selected' : '' }}>Medium (51-250 Employees)</option>
                        <option value="Large" {{ old('organization_scale') == 'Large' ? 'selected' : '' }}>Large (>250 Employees)</option>
                    </select>
                    @error('organization_scale') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">IT Governance Structure</label>
                <textarea name="it_governance_structure" rows="2" placeholder="Describe the IT reporting lines, roles, and security responsibilities..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('it_governance_structure') }}</textarea>
                @error('it_governance_structure') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">ISMS Scope</label>
                <textarea name="isms_scope" rows="2" placeholder="Define the boundaries and applicability of the Information Security Management System..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('isms_scope') }}</textarea>
                @error('isms_scope') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Description</label>
                <textarea name="description" rows="3" placeholder="Brief description of the organization and its business focus..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('description') }}</textarea>
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Office Address</label>
                <textarea name="address" rows="3" placeholder="Full street address..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('address') }}</textarea>
                @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <a href="{{ route('admin.organizations.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm">
                    Register Organization
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
