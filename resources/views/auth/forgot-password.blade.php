<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%232563eb'/><text y='.9em' font-size='55' font-family='sans-serif' font-weight='bold' fill='white' x='50%' text-anchor='middle'>{{ __('ISO') }}</text></svg>">
    <meta name="description" content="Reset your ISO 27001:2022 Self-Assessment password. Secure password recovery for your compliance account.">
    <meta name="robots" content="noindex, nofollow">
    <title>Forgot Password | ISO 27001:2022 Self-Assessment</title>
    
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
        /* Custom focus ring */
        input:focus { box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        /* Smooth transitions */
        * { transition-property: color, background-color, border-color, box-shadow, transform; transition-duration: 200ms; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-blue-400 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-purple-400 rounded-full opacity-10 blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-white/20 backdrop-blur-sm">
            
            {{-- Header --}}
            <div class="text-center mb-6">
                <a href="{{ route('landing') }}" class="inline-block mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all">
                        <i class="fa-solid fa-key text-white text-2xl"></i>
                    </div>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('Forgot Password?') }}</h1>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                    {{ __('Enter your registered email and we\'ll send you a reset link.') }}
                </p>
            </div>

            {{-- Success --}}
            @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl" role="alert">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
                    <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            {{-- Dev mode: show reset link directly --}}
            @if(session('dev_reset_url'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <div class="flex items-start gap-3 mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5"></i>
                    <p class="text-xs text-amber-700 font-bold uppercase tracking-widest">{{ session('info', 'Development Mode') }}</p>
                </div>
                <a href="{{ session('dev_reset_url') }}" 
                   class="block text-xs text-blue-600 font-bold break-all hover:underline bg-blue-50 p-3 rounded-lg border border-blue-100">
                    {{ session('dev_reset_url') }}
                </a>
            </div>
            @endif

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

            {{-- Form --}}
            <form method="POST" action="{{ route('password.send') }}" class="space-y-4" 
                x-data="{ loading: false }" 
                @submit="loading = true">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2 ml-1">
                        {{ __('Email Address') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                            placeholder="{{ __('your.email@company.com') }}"
                            aria-label="Email address"
                            class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium">
                    </div>
                </div>

                <button type="submit" 
                    :disabled="loading"
                    :class="loading ? 'opacity-75 cursor-not-allowed' : 'hover:shadow-2xl hover:scale-[1.02]'"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest transition-all shadow-xl shadow-blue-600/20 active:scale-[0.98] flex items-center justify-center gap-2">
                    <span x-show="!loading">
                        <i class="fa-solid fa-paper-plane"></i>
                        {{ __('Send Reset Link') }}
                    </span>
                    <span x-show="loading" class="flex items-center gap-2" x-cloak>
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        {{ __('Sending...') }}
                    </span>
                </button>
                
                <!-- Keyboard Hint -->
                <p class="text-xs text-center text-gray-400 -mt-2">
                    <i class="fa-solid fa-keyboard text-[10px]"></i>
                    {{ __('Press Enter to submit') }}
                </p>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-blue-600 font-bold transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    {{ __('Back to Login') }}
                </a>
            </div>

            <!-- Security Badge -->
            <div class="mt-6 pt-6 border-t border-gray-100 flex items-center justify-center gap-4 text-xs text-gray-400">
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
