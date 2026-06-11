<section>
    <header class="mb-5">
        <h2 class="text-sm font-black text-slate-900 tracking-tight uppercase">{{ __('Access Credentials') }}</h2>
        <p class="mt-0.5 text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Rotate and maintain high-entropy security credentials for artifact integrity.') }}</p>
    </header>

    <form method="post" action="{{ route('profile.password.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Current Password --}}
            <div class="space-y-1.5">
                <label for="current_password" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Current Password') }}</label>
                <input id="current_password" name="current_password" type="password" placeholder="{{ __('••••••••') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none" autocomplete="current-password" />
                <x-input-error class="mt-1" :messages="$errors->get('current_password')" />
            </div>

            {{-- New Password --}}
            <div class="space-y-1.5">
                <label for="password" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('New Security Password') }}</label>
                <input id="password" name="password" type="password" placeholder="{{ __('••••••••') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none" autocomplete="new-password" />
                <x-input-error class="mt-1" :messages="$errors->get('password')" />
            </div>

            {{-- Confirm Password --}}
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">{{ __('Verify New Password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="{{ __('••••••••') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all outline-none" autocomplete="new-password" />
                <x-input-error class="mt-1" :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg active:scale-95">{{ __('Rotate Credentials') }}</button>

            @if (session('success'))
                <div 
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition.opacity
                    x-init="setTimeout(() => show = false, 5000)"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-100 rounded-xl"
                >
                    <i class="fa-solid fa-shield-halved text-blue-600 text-xs"></i>
                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">{{ session('success') }}</span>
                </div>
            @endif
        </div>
    </form>
</section>
