<section class="space-y-5">
    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Part 1: Personal & Position Info --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-id-card text-blue-500"></i> Personal & Role Details
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Full Name') }} <span class="text-red-400">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none" />
                    <x-input-error class="mt-1" :messages="$errors->get('name')" />
                </div>

                {{-- Email Address --}}
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Email Address') }} <span class="text-red-400">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none" />
                    <x-input-error class="mt-1" :messages="$errors->get('email')" />
                </div>

                {{-- Position / Job Title --}}
                <div class="sm:col-span-2">
                    <label for="job_title" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Position / Job Title') }}</label>
                    <input id="job_title" name="job_title" type="text" value="{{ old('job_title', $user->job_title) }}"
                        placeholder="e.g. Chief Information Security Officer (CISO), IT Compliance Officer, Lead Auditor"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none" />
                    <x-input-error class="mt-1" :messages="$errors->get('job_title')" />
                </div>

                {{-- Role Description & Responsibilities --}}
                <div class="sm:col-span-2">
                    <label for="role_description" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Role Description & Responsibilities') }}</label>
                    <textarea id="role_description" name="role_description" rows="2"
                        placeholder="Describe your security duties, compliance responsibilities, or audit authority in the organization…"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none resize-none">{{ old('role_description', $user->role_description) }}</textarea>
                    <x-input-error class="mt-1" :messages="$errors->get('role_description')" />
                </div>
            </div>
        </div>

        {{-- Part 2: Organization & Governance Scope --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-building text-indigo-500"></i> Organization & ISMS Governance
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Organization Name --}}
                <div>
                    <label for="organization_name" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Organization Name') }}</label>
                    <input id="organization_name" name="organization_name" type="text" value="{{ old('organization_name', $user->organization?->name) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none" />
                    <x-input-error class="mt-1" :messages="$errors->get('organization_name')" />
                </div>

                {{-- Business Sector --}}
                <div>
                    <label for="business_sector" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Business Sector') }}</label>
                    <select id="business_sector" name="business_sector" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none">
                        <option value="">{{ __('Select Business Sector…') }}</option>
                        @foreach(\App\Models\Organization::getBusinessSectors() as $sector)
                            <option value="{{ $sector }}" {{ old('business_sector', $user->organization?->business_sector) == $sector ? 'selected' : '' }}>{{ $sector }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('business_sector')" />
                </div>

                {{-- Organization Scale --}}
                <div class="sm:col-span-2">
                    <label for="organization_scale" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Organization Scale') }}</label>
                    <select id="organization_scale" name="organization_scale"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none cursor-pointer">
                        <option value="" disabled>{{ __('Select Organizational Scale') }}</option>
                        <option value="Small" {{ old('organization_scale', $user->organization?->organization_scale) == 'Small' ? 'selected' : '' }}>{{ __('Small (1-50 Employees)') }}</option>
                        <option value="Medium" {{ old('organization_scale', $user->organization?->organization_scale) == 'Medium' ? 'selected' : '' }}>{{ __('Medium (51-250 Employees)') }}</option>
                        <option value="Large" {{ old('organization_scale', $user->organization?->organization_scale) == 'Large' ? 'selected' : '' }}>{{ __('Large (>250 Employees)') }}</option>
                    </select>
                    <x-input-error class="mt-1" :messages="$errors->get('organization_scale')" />
                </div>

                {{-- IT Governance Structure --}}
                <div class="sm:col-span-2">
                    <label for="it_governance_structure" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('IT Governance Structure') }}</label>
                    <textarea id="it_governance_structure" name="it_governance_structure" rows="2"
                        placeholder="{{ __('Define roles, responsibilities, and reporting lines...') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none resize-none">{{ old('it_governance_structure', $user->organization?->it_governance_structure) }}</textarea>
                    <x-input-error class="mt-1" :messages="$errors->get('it_governance_structure')" />
                </div>

                {{-- ISMS Scope --}}
                <div class="sm:col-span-2">
                    <label for="isms_scope" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('ISMS Scope') }}</label>
                    <textarea id="isms_scope" name="isms_scope" rows="2"
                        placeholder="{{ __('Define the boundaries of your ISMS scope...') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none resize-none">{{ old('isms_scope', $user->organization?->isms_scope) }}</textarea>
                    <x-input-error class="mt-1" :messages="$errors->get('isms_scope')" />
                </div>

                {{-- Organization Description --}}
                <div class="sm:col-span-2">
                    <label for="organization_description" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Organization Description') }}</label>
                    <textarea id="organization_description" name="organization_description" rows="2"
                        placeholder="{{ __('Brief description of your organization and its core activities...') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all outline-none resize-none">{{ old('organization_description', $user->organization?->description) }}</textarea>
                    <x-input-error class="mt-1" :messages="$errors->get('organization_description')" />
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex items-center gap-4">
            <button type="submit"
                class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-600/20 active:scale-95 flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('Save Changes') }}
            </button>

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition.opacity x-init="setTimeout(() => show = false, 4000)"
                    class="flex items-center gap-2 px-3.5 py-2 bg-emerald-50 border border-emerald-100 rounded-xl">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                    <span class="text-xs font-bold text-emerald-700">{{ session('success') }}</span>
                </div>
            @endif
        </div>
    </form>
</section>
