<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%232563eb'/><text y='.9em' font-size='55' font-family='sans-serif' font-weight='bold' fill='white' x='50%' text-anchor='middle'>{{ __('ISO') }}</text></svg>">
    <meta name="description" content="Login to your ISO 27001:2022 Self-Assessment account. Access saved assessments, track compliance progress, and export professional reports.">
    <meta name="robots" content="noindex, nofollow">
    <title>Login | ISO 27001:2022 Self-Assessment</title>
    
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
        
        /* Caps lock warning animation */
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        .shake { animation: shake 0.3s ease-in-out; }
        
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
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-sky-50 flex items-center justify-center p-4">
    
    <!-- Enhanced Background Decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-20 blur-3xl floating"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-r from-sky-400 to-pink-400 rounded-full opacity-20 blur-3xl floating" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-gradient-to-r from-blue-400 to-blue-400 rounded-full opacity-10 blur-2xl floating" style="animation-delay: -1.5s;"></div>
    </div>

    <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center relative z-10">
        <!-- Left Side - Branding -->
        <div class="hidden lg:block">
            <div class="mb-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-3 mb-6 group">
                    <img src="{{ asset('images/logo.jpg') }}" alt="AuditGuard" class="w-12 h-12 rounded-xl object-contain bg-white p-0.5 shadow-md shadow-blue-600/20 group-hover:scale-105 transition-all">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 leading-none">{{ __('ISO 27001:2022') }}</h1>
                        <p class="text-[10px] text-gray-600 uppercase tracking-widest mt-0.5 font-bold">{{ __('Self-Assessment Tool') }}</p>
                    </div>
                </a>
                <h2 class="text-3xl font-bold text-gray-900 mb-3 leading-tight">{{ __('Welcome Back!') }}</h2>
                <p class="text-lg text-gray-600 mb-6 leading-normal">
                    Continue your ISO 27001:2022 compliance journey with saved assessments and personalized recommendations.
                </p>
            </div>

            <!-- Features -->
            <div class="space-y-3">
                @foreach(['Save assessment progress', 'Access across devices', 'Export compliance reports', 'Track historical data'] as $feature)
                <div class="flex items-center gap-3 text-gray-700 font-medium">
                    <div class="w-6 h-6 bg-teal-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-check text-teal-600 text-xs"></i>
                    </div>
                    <span>{{ $feature }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="glass-card rounded-2xl p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500/10 to-sky-500/10 rounded-full -mr-16 -mt-16 blur-xl"></div>
            <!-- Mobile Branding -->
            <div class="lg:hidden mb-4 text-center">
                <a href="{{ route('landing') }}" class="inline-block">
                    <img src="{{ asset('images/logo.jpg') }}" alt="AuditGuard" class="w-12 h-12 rounded-xl object-contain bg-white p-0.5 shadow-md mx-auto mb-3">
                </a>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Welcome Back') }}</h1>
                <p class="text-sm text-gray-600">{{ __('Login to your account') }}</p>
            </div>

            <div class="mb-6">
                <h2 class="text-2xl font-bold gradient-text mb-2">{{ __('Login to Your Account') }}</h2>
                <p class="text-sm text-gray-500">
                    {{ __('Please use the credentials provided by your system administrator.') }}
                </p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 text-sm font-medium" role="alert">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium" role="alert">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold mb-2">{{ __('Please fix the following errors:') }}</h3>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- OAuth Success Message -->
            @if (session('oauth_success'))
                <div class="mb-4 p-4 bg-teal-50 border border-teal-200 rounded-xl text-teal-700 text-sm font-medium" role="alert">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-teal-500"></i>
                        <span>{{ session('oauth_success') }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4" 
                x-data="{ 
                    loading: false, 
                    capsLockOn: false,
                    emailValid: false,
                    checkCapsLock(event) {
                        this.capsLockOn = event.getModifierState && event.getModifierState('CapsLock');
                    },
                    validateEmail(email) {
                        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        this.emailValid = re.test(email);
                    }
                }" 
                @submit="loading = true">
                @csrf

                <!-- Email -->
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
                            @input="validateEmail($event.target.value)"
                            class="w-full pl-12 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2">
                            <i x-show="emailValid" class="fa-solid fa-circle-check text-teal-500" x-cloak></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-1">{{ __('We\'ll never share your email') }}</p>
                </div>

                <!-- Password -->
                <div x-data="{ show: false }">
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2 ml-1">
                        {{ __('Password') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                            placeholder="{{ __('Enter your password') }}"
                            aria-label="Password"
                            @keydown="checkCapsLock($event)"
                            @keyup="checkCapsLock($event)"
                            class="w-full pl-12 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium">
                        <button type="button" @click="show = !show" 
                            :aria-label="show ? 'Hide password' : 'Show password'"
                            tabindex="-1"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <!-- Caps Lock Warning -->
                    <div x-show="capsLockOn" x-cloak class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-700 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>{{ __('Caps Lock is on') }}</span>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">{{ __('Remember me') }}</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 font-bold">{{ __('Forgot password?') }}</a>
                    @endif
                </div>

                <!-- Login Button -->
                <button type="submit" 
                    :disabled="loading"
                    :class="loading ? 'opacity-75 cursor-not-allowed' : 'enhanced-button'"
                    class="w-full bg-gradient-to-r from-blue-600 via-sky-600 to-blue-600 text-white py-3.5 rounded-xl font-bold text-sm uppercase tracking-widest transition-all shadow-lg shadow-blue-600/20 active:scale-[0.98] flex items-center justify-center gap-2 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-sky-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                    <span x-show="!loading">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        {{ __('Login Account') }}
                    </span>
                    <span x-show="loading" class="flex items-center gap-2" x-cloak>
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        {{ __('Logging in...') }}
                    </span>
                </button>
                
                <!-- Keyboard Hint -->
                <p class="text-xs text-center text-gray-400 -mt-1">
                    <i class="fa-solid fa-keyboard text-[10px]"></i>
                    {{ __('Press Enter to login') }}
                </p>
            </form>


            <!-- Security Badge -->
            <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-400">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-teal-500"></i>
                    <span>{{ __('Secure Login') }}</span>
                </div>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-blue-500"></i>
                    <span>{{ __('Encrypted') }}</span>
                </div>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-user-shield text-blue-500"></i>
                    <span>{{ __('Privacy Protected') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js x-cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
