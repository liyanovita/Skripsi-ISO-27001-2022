<section>
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
            <i class="fa-solid fa-key text-amber-500"></i> {{ __('Change Password') }}
        </h3>
        <p class="text-xs text-slate-400 font-medium">Ensure your account is using a long, random password to stay secure.</p>

        <form method="post" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Current Password --}}
            <div>
                <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Current Password') }} <span class="text-red-400">*</span></label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10 transition-all outline-none" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
            </div>

            {{-- New Password --}}
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('New Password') }} <span class="text-red-400">*</span></label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10 transition-all outline-none" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Confirm New Password') }} <span class="text-red-400">*</span></label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10 transition-all outline-none" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <button type="submit"
                    class="w-full py-2.5 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition-all shadow-md shadow-amber-500/20 active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-lock text-xs"></i> {{ __('Update Password') }}
                </button>
            </div>
        </form>
    </div>
</section>
