<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%232563eb'/><text y='.9em' font-size='55' font-family='sans-serif' font-weight='bold' fill='white' x='50%' text-anchor='middle'>{{ __('ISO') }}</text></svg>">
    <meta name="description" content="Create your free ISO 27001:2022 Self-Assessment account. Save progress, access AI recommendations, and export professional compliance reports.">
    <meta name="robots" content="noindex, nofollow">
    <title>Register | ISO 27001:2022 Self-Assessment</title>
    
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
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 flex items-center justify-center p-4">
    
    <!-- Enhanced Background Decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-20 w-64 h-64 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-20 blur-3xl floating"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-20 blur-3xl floating" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full opacity-10 blur-2xl floating" style="animation-delay: -1.5s;"></div>
    </div>

    <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center relative z-10">
        <!-- Left Side - Branding -->
        <div class="hidden lg:block">
            <div class="mb-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-3 mb-6 group">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/30 group-hover:shadow-indigo-600/50 transition-all">
                        <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 leading-none">{{ __('ISO 27001:2022') }}</h1>
                        <p class="text-[10px] text-gray-600 uppercase tracking-widest mt-0.5 font-bold">{{ __('Self-Assessment Tool') }}</p>
                    </div>
                </a>
                <h2 class="text-3xl font-bold text-gray-900 mb-3 leading-tight">{{ __('Start Your Compliance Journey') }}</h2>
                <p class="text-lg text-gray-600 mb-6 leading-normal">
                    Join thousands of organizations using our open-source tool to achieve ISO 27001:2022 compliance.
                </p>
            </div>

            <!-- Benefits -->
            <div class="space-y-4">
                @php
                    $benefits = [
                        ['title' => 'Free Forever', 'desc' => '100% open source, no hidden costs'],
                        ['title' => 'AI-Powered', 'desc' => 'Get intelligent recommendations'],
                        ['title' => 'Community Driven', 'desc' => 'Access shared templates and resources'],
                        ['title' => 'Export Reports', 'desc' => 'Professional PDF and Excel reports'],
                    ];
                @endphp
                @foreach($benefits as $benefit)
                <div class="flex items-start gap-3 p-4 bg-white/40 backdrop-blur-sm rounded-2xl border border-white/50">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-500/20">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">{{ $benefit['title'] }}</h3>
                        <p class="text-xs text-gray-600 font-medium">{{ $benefit['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right Side - Register Form -->
        <div class="glass-card rounded-2xl p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-500/10 to-blue-500/10 rounded-full -mr-16 -mt-16 blur-xl"></div>
            
            <!-- Mobile Branding -->
            <div class="lg:hidden mb-4 text-center">
                <a href="{{ route('landing') }}" class="inline-block">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-indigo-600/30">
                        <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
                    </div>
                </a>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Create Account') }}</h1>
                <p class="text-sm text-gray-600">{{ __('Start your compliance journey') }}</p>
            </div>

            <div class="mb-4">
                <h2 class="text-2xl font-bold gradient-text mb-2">{{ __('Create Your Account') }}</h2>
                <p class="text-sm text-gray-600">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-bold">{{ __('Login') }}</a>
                </p>
            </div>

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

            <form method="POST" action="{{ route('register') }}" class="space-y-3" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1 ml-1">Full Name *</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                            placeholder="{{ __('John Doe') }}"
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium text-sm">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1 ml-1">Email Address *</label>
                    <div class="relative group" x-data="{ emailValid: false }" @input="emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($event.target.value)">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            placeholder="{{ __('your.email@company.com') }}"
                            class="w-full pl-12 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium text-sm">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2">
                            <i x-show="emailValid" class="fa-solid fa-circle-check text-green-500" x-cloak></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-1">{{ __('We\'ll never share your email') }}</p>
                </div>

                <!-- Organization -->
                <div>
                    <label for="organization" class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1 ml-1">{{ __('Organization (Optional)') }}</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <input id="organization" name="organization" type="text" value="{{ old('organization') }}"
                            placeholder="{{ __('Your Company Name') }}"
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium text-sm">
                    </div>
                </div>

                <!-- Password Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div x-data="{ show: false, strength: 0, checkStrength(password) { 
                        let score = 0;
                        if (password.length >= 8) score++;
                        if (/[A-Z]/.test(password)) score++;
                        if (/[a-z]/.test(password)) score++;
                        if (/[0-9]/.test(password)) score++;
                        if (/[^A-Za-z0-9]/.test(password)) score++;
                        this.strength = score;
                    }}">
                        <label for="password" class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1 ml-1">Password *</label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input id="password" name="password" :type="show ? 'text' : 'password'" required
                                placeholder="{{ __('8+ chars') }}"
                                @input="checkStrength($event.target.value)"
                                class="w-full pl-12 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium text-sm">
                            <button type="button" @click="show = !show" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div class="mt-2" x-show="strength > 0" x-cloak>
                            <div class="flex gap-1 mb-1">
                                <div class="h-1 flex-1 rounded-full" :class="strength >= 1 ? (strength >= 4 ? 'bg-green-500' : strength >= 3 ? 'bg-yellow-500' : 'bg-red-500') : 'bg-gray-200'"></div>
                                <div class="h-1 flex-1 rounded-full" :class="strength >= 2 ? (strength >= 4 ? 'bg-green-500' : strength >= 3 ? 'bg-yellow-500' : 'bg-red-500') : 'bg-gray-200'"></div>
                                <div class="h-1 flex-1 rounded-full" :class="strength >= 3 ? (strength >= 4 ? 'bg-green-500' : 'bg-yellow-500') : 'bg-gray-200'"></div>
                                <div class="h-1 flex-1 rounded-full" :class="strength >= 4 ? 'bg-green-500' : 'bg-gray-200'"></div>
                                <div class="h-1 flex-1 rounded-full" :class="strength >= 5 ? 'bg-green-500' : 'bg-gray-200'"></div>
                            </div>
                            <p class="text-xs font-medium" :class="strength >= 4 ? 'text-green-600' : strength >= 3 ? 'text-yellow-600' : 'text-red-600'">
                                <span x-text="strength >= 4 ? 'Strong password' : strength >= 3 ? 'Good password' : 'Weak password'"></span>
                            </p>
                        </div>
                    </div>

                    <div x-data="{ show: false }">
                        <label for="password_confirmation" class="block text-[10px] font-bold text-gray-700 uppercase tracking-widest mb-1 ml-1">Confirm *</label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required
                                placeholder="{{ __('Re-enter') }}"
                                class="w-full pl-12 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-medium text-sm">
                            <button type="button" @click="show = !show" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div>
                    <label class="flex items-start gap-2 cursor-pointer group">
                        <input type="checkbox" required class="w-4 h-4 text-blue-600 border-gray-200 rounded focus:ring-blue-500 mt-0.5 cursor-pointer">
                        <span class="text-[11px] text-gray-600 group-hover:text-gray-900 transition-colors leading-relaxed">{{ __('I agree to the') }}<a href="#" class="text-blue-600 hover:text-blue-700 font-bold">{{ __('Terms of Service') }}</a> {{ __('and') }} <a href="#" class="text-blue-600 hover:text-blue-700 font-bold">{{ __('Privacy Policy') }}</a>
                        </span>
                    </label>
                </div>

                <!-- Register Button -->
                <button type="submit" 
                    :disabled="loading"
                    :class="loading ? 'opacity-75 cursor-not-allowed' : 'enhanced-button'"
                    class="w-full bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest transition-all shadow-xl shadow-indigo-600/20 active:scale-[0.98] flex items-center justify-center gap-2 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-purple-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                    <span x-show="!loading">
                        <i class="fa-solid fa-user-plus"></i>
                        {{ __('Create Account') }}
                    </span>
                    <span x-show="loading" class="flex items-center gap-2" x-cloak>
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        {{ __('Creating account...') }}
                    </span>
                </button>
                
                <!-- Keyboard Hint -->
                <p class="text-xs text-center text-gray-400 -mt-1">
                    <i class="fa-solid fa-keyboard text-[10px]"></i>
                    {{ __('Press Enter to register') }}
                </p>
            </form>

            <!-- Divider -->
            <div class="my-4 flex items-center gap-4">
                <div class="flex-1 border-t border-gray-100"></div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('or sign up with') }}</span>
                <div class="flex-1 border-t border-gray-100"></div>
            </div>

            <!-- Social Signup -->
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('auth.redirect', 'github') }}" class="flex items-center justify-center gap-3 px-4 py-3.5 border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 font-bold text-[10px] uppercase tracking-widest text-gray-700 transition-all">
                    <i class="fa-brands fa-github text-base"></i>{{ __('GitHub') }}</a>
                <a href="{{ route('auth.redirect', 'google') }}" class="flex items-center justify-center gap-3 px-4 py-3.5 border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 font-bold text-[10px] uppercase tracking-widest text-gray-700 transition-all">
                    <i class="fa-brands fa-google text-base"></i>{{ __('Google') }}</a>
            </div>



            <!-- Security Badge -->
            <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-400">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-green-500"></i>
                    <span>{{ __('Secure') }}</span>
                </div>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-green-500"></i>
                    <span>{{ __('Encrypted') }}</span>
                </div>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-user-shield text-green-500"></i>
                    <span>{{ __('Privacy Protected') }}</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
