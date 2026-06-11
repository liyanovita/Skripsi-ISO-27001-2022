<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%232563eb'/><text y='.9em' font-size='55' font-family='sans-serif' font-weight='bold' fill='white' x='50%' text-anchor='middle'>{{ __('ISO') }}</text></svg>">
    <meta name="description" content="Set a new password for your ISO 27001:2022 Self-Assessment account. Secure password reset process.">
    <meta name="robots" content="noindex, nofollow">
    <title>Reset Password | ISO 27001:2022 Self-Assessment</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
        
        /* Enhanced Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(5px) rotate(-1deg); }
        }
        .floating { animation: float 6s ease-in-out infinite; }
        
        /* Enhanced Focus Ring */
        input:focus { 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1), 0 0 20px rgba(37, 99, 235, 0.1); 
            transform: translateY(-1px);
        }
        
        /* Smooth transitions */
        * { transition-property: color, background-color, border-color, box-shadow, transform; transition-duration: 200ms; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Enhanced Button Hover */
        .enhanced-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-20 blur-3xl floating"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-20 blur-3xl floating" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-gradient-to-r from-cyan-400 to-blue-400 rounded-full opacity-10 blur-2xl floating" style="animation-delay: -1.5s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="glass-card rounded-2xl p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 rounded-full -mr-16 -mt-16 blur-xl"></div>

            {{-- Header --}}
            <div class="text-center mb-6 relative z-10">
                <a href="{{ route('landing') }}" class="inline-block mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/50 transition-all">
                        <i class="fa-solid fa-lock-open text-white text-2xl"></i>
                    </div>
                </a>
                <h1 class="text-2xl font-bold gradient-text">{{ __('Set New Password') }}</h1>
                <p class="text-sm text-gray-500 mt-2">{{ __('Enter your new password below.') }}</p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium" role="alert">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4" 
                x-data="{ show: false, showConfirm: false, loading: false }" 
                @submit="loading = true">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Email (read-only display) --}}
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-3">
                    <i class="fa-solid fa-envelope text-slate-400 text-sm"></i>
                    <span class="text-sm font-bold text-slate-600">{{ $email }}</span>
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">{{ __('New Password') }}</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input id="password" name="password" :type="show ? 'text' : 'password'" required
                            placeholder="{{ __('Minimum 8 characters') }}"
                            class="w-full pl-12 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium">
                        <button type="button" @click="show = !show"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">{{ __('Confirm Password') }}</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" :type="showConfirm ? 'text' : 'password'" required
                            placeholder="{{ __('Re-enter new password') }}"
                            class="w-full pl-12 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fa-solid" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    :disabled="loading"
                    :class="loading ? 'opacity-75 cursor-not-allowed' : 'enhanced-button'"
                    class="w-full bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest transition-all shadow-xl shadow-emerald-600/20 active:scale-[0.98] flex items-center justify-center gap-2 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                    <span x-show="!loading">
                        <i class="fa-solid fa-check"></i>
                        {{ __('Reset Password') }}
                    </span>
                    <span x-show="loading" class="flex items-center gap-2" x-cloak>
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        {{ __('Resetting...') }}
                    </span>
                </button>
                
                <!-- Keyboard Hint -->
                <p class="text-xs text-center text-gray-400 -mt-1">
                    <i class="fa-solid fa-keyboard text-[10px]"></i>
                    {{ __('Press Enter to reset') }}
                </p>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-blue-600 font-bold transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    {{ __('Back to Login') }}
                </a>
            </div>

            <!-- Security Badge -->
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-center gap-4 text-xs text-gray-400">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-green-500"></i>
                    <span>{{ __('Secure') }}</span>
                </div>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-green-500"></i>
                    <span>{{ __('Encrypted') }}</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
