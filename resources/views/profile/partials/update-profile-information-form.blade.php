<section>
    <header class="mb-5">
        <h2 class="text-sm font-black text-slate-900 tracking-tight uppercase">{{ __('Identity Registry') }}</h2>
        <p class="mt-0.5 text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Sync your professional artifacts and organizational scope.') }}</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Core Identity --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-blue-600/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-3 bg-blue-600 rounded-full"></div>
                    <h3 class="text-[9px] font-bold text-blue-600 uppercase tracking-widest">Module 01: Core Parameters</h3>
                </div>
            </div>
            
            {{-- Name --}}
            <div class="space-y-1.5">
                <label for="name" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Username') }}</label>
                <input id="name" name="name" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>

            {{-- Email --}}
            <div class="space-y-1.5">
                <label for="email" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Security Email') }}</label>
                <input id="email" name="email" type="email" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                <x-input-error class="mt-1" :messages="$errors->get('email')" />
            </div>

            {{-- Organization Name --}}
            <div class="space-y-1.5">
                <label for="organization_name" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Entity Name') }}</label>
                <input id="organization_name" name="organization_name" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none" value="{{ old('organization_name', $user->organization_name) }}" required />
                <x-input-error class="mt-1" :messages="$errors->get('organization_name')" />
            </div>

            {{-- Business Sector --}}
            <div class="space-y-1.5">
                <label for="business_sector" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Operational Sector') }}</label>
                <input id="business_sector" name="business_sector" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none" value="{{ old('business_sector', $user->business_sector) }}" required />
                <x-input-error class="mt-1" :messages="$errors->get('business_sector')" />
            </div>

            {{-- Organization Scale --}}
            <div class="space-y-1.5">
                <label for="organization_scale" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Personnel Scale') }}</label>
                <select id="organization_scale" name="organization_scale" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none appearance-none">
                    <option value="" disabled {{ old('organization_scale', $user->organization_scale) ? '' : 'selected' }}>{{ __('Select Organizational Scale') }}</option>
                    <option value="Small" {{ old('organization_scale', $user->organization_scale) == 'Small' ? 'selected' : '' }}>{{ __('Small (1-50 Employees)') }}</option>
                    <option value="Medium" {{ old('organization_scale', $user->organization_scale) == 'Medium' ? 'selected' : '' }}>{{ __('Medium (51-250 Employees)') }}</option>
                    <option value="Large" {{ old('organization_scale', $user->organization_scale) == 'Large' ? 'selected' : '' }}>Large (>250 Employees)</option>
                </select>
                <x-input-error class="mt-1" :messages="$errors->get('organization_scale')" />
            </div>
        </div>

        {{-- Governance Framework --}}
        <div class="grid grid-cols-1 gap-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-24 h-24 bg-indigo-600/5 rounded-full blur-2xl pointer-events-none"></div>
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-3 bg-indigo-600 rounded-full"></div>
                    <h3 class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest">Module 02: Governance Topology</h3>
                </div>
            </div>

            {{-- IT Governance Structure --}}
            <div class="space-y-1.5">
                <label for="it_governance_structure" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Structural Hierarchy') }}</label>
                <textarea id="it_governance_structure" name="it_governance_structure" rows="2" placeholder="{{ __('Define secure roles, responsibilities, and reporting lines...') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-700 leading-relaxed focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none">{{ old('it_governance_structure', $user->it_governance_structure) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('it_governance_structure')" />
            </div>

            {{-- ISMS Scope --}}
            <div class="space-y-1.5">
                <label for="isms_scope" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Operational Scope Boundaries') }}</label>
                <textarea id="isms_scope" name="isms_scope" rows="2" placeholder="{{ __('Define the formal boundaries of ISMS and applicability vectors...') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-700 leading-relaxed focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none">{{ old('isms_scope', $user->isms_scope) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('isms_scope')" />
            </div>

            {{-- Organization Description --}}
            <div class="space-y-1.5">
                <label for="organization_description" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Organization Description') }}</label>
                <textarea id="organization_description" name="organization_description" rows="2" placeholder="{{ __('Brief description of your organization and its core activities...') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-700 leading-relaxed focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none">{{ old('organization_description', $user->organization_description) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('organization_description')" />
            </div>
        </div>
            <button type="submit" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg active:scale-95">{{ __('Finalize Sync') }}</button>

            @if (session('success'))
                <div 
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition.opacity
                    x-init="setTimeout(() => show = false, 5000)"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-100 rounded-xl"
                >
                    <i class="fa-solid fa-circle-check text-blue-600 text-xs"></i>
                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">{{ session('success') }}</span>
                </div>
            @endif
        </div>
    </form>
</section>
