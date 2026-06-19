<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%232563eb'/><text y='.9em' font-size='55' font-family='sans-serif' font-weight='bold' fill='white' x='50%' text-anchor='middle'>{{ __('ISO') }}</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    <meta name="description" content="Free, open-source ISO 27001:2022 self-assessment tool with AI-powered recommendations, professional reports, and community templates. No registration required.">
    <meta name="keywords" content="ISO 27001:2022, ISO 27001:2022, compliance, ISMS, security assessment, open source, free tool, gap analysis">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="ISO 27001:2022 Self-Assessment | Free Open Source Tool">
    <meta property="og:description" content="Free, open-source ISO 27001:2022 self-assessment with AI recommendations and professional reports.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ISO 27001:2022 Self-Assessment | Free Open Source Tool">
    <meta name="twitter:description" content="Free ISO 27001:2022 self-assessment tool with AI-powered gap analysis.">
    <title>ISO 27001:2022 Self-Assessment | Open Source Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .gradient-text { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 25px 50px rgba(0,0,0,0.15); }
        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.2s; opacity: 0; }
        .delay-3 { animation-delay: 0.3s; opacity: 0; }
        .delay-4 { animation-delay: 0.4s; opacity: 0; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; scroll-behavior: auto !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-purple-50 text-slate-900">

    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg">{{ __('ISO') }}</div>
                    <div>
                        <div class="font-bold text-base text-gray-900 leading-none">ISO 27001:2022</div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-widest mt-0.5 font-semibold">{{ __('Self-Assessment Tool') }}</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                    <a href="#features" class="hover:text-blue-600 transition-colors">{{ __('Features') }}</a>
                    <a href="#how-it-works" class="hover:text-blue-600 transition-colors">{{ __('How It Works') }}</a>
                    <a href="#faq" class="hover:text-blue-600 transition-colors">{{ __('FAQ') }}</a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium text-sm transition-colors">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:shadow-lg font-medium text-sm transition-all shadow-md">{{ __('Get Started') }}</a>
                    <button id="mobileMenuBtn" onclick="toggleMobileMenu()" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors" aria-label="Toggle mobile menu" aria-expanded="false">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-white border-b border-gray-200 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3 space-y-1">
            <a href="#features" onclick="toggleMobileMenu()" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg font-medium transition-colors">{{ __('Features') }}</a>
            <a href="#how-it-works" onclick="toggleMobileMenu()" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg font-medium transition-colors">{{ __('How It Works') }}</a>
            <a href="#faq" onclick="toggleMobileMenu()" class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg font-medium transition-colors">{{ __('FAQ') }}</a>
            <div class="pt-1.5 border-t border-gray-100">
                <a href="{{ route('register') }}" class="block px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold text-center">{{ __('Start Assessment') }}</a>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-medium mb-5 animate-fade-in-up">
            <i class="fa-solid fa-sparkles text-xs animate-pulse"></i>
            For Academic &amp; Professional Use
        </div>

        <!-- Headline -->
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-5 animate-fade-in-up delay-1">
            Simplify Your <span class="gradient-text">ISO 27001:2022</span><br>Compliance Assessment
        </h1>

        <!-- Subheadline -->
        <p class="text-lg text-gray-500 max-w-2xl mx-auto mb-8 leading-relaxed animate-fade-in-up delay-2">
            A structured, AI-powered platform to evaluate information security compliance across all 93 ISO 27001:2022 controls &mdash; with gap analysis, professional reports, and actionable recommendations.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-wrap justify-center gap-4 mb-8 animate-fade-in-up delay-3">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:shadow-xl font-semibold shadow-lg transition-all transform hover:-translate-y-1">
                Start Assessment <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-gray-700 border-2 border-gray-200 rounded-xl hover:border-blue-400 hover:text-blue-600 font-semibold shadow-sm hover:shadow-md transition-all transform hover:-translate-y-1">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </a>
        </div>

        <!-- Trust badges -->
        <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400 mb-12 animate-fade-in-up delay-4">
            <div class="flex items-center gap-2"><i class="fa-solid fa-check-circle text-green-500"></i> 93 Controls Covered</div>
            <div class="flex items-center gap-2"><i class="fa-solid fa-check-circle text-green-500"></i> AI-Powered Gap Analysis</div>
            <div class="flex items-center gap-2"><i class="fa-solid fa-check-circle text-green-500"></i> Professional Reports</div>
        </div>

        <!-- Dashboard Mockup (below text) -->
        <div class="relative mx-auto max-w-4xl animate-fade-in-up delay-4">
            <div class="absolute -top-10 left-1/4 w-64 h-64 bg-blue-400 rounded-full opacity-10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 right-1/4 w-64 h-64 bg-purple-400 rounded-full opacity-10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden card-hover">
                <!-- Browser Bar -->
                <div class="bg-gray-100 px-4 py-2.5 flex items-center gap-2 border-b border-gray-200">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="flex-1 bg-white rounded-md px-3 py-1 text-xs text-gray-400 ml-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-lock text-green-500"></i>
                        audit-system.com/dashboard
                    </div>
                </div>
                <!-- Dashboard Content -->
                <div class="p-6 bg-gradient-to-br from-slate-50 to-blue-50">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <div class="h-5 w-48 bg-gray-300 rounded-md mb-2 animate-pulse"></div>
                            <div class="h-3 w-32 bg-gray-200 rounded-md animate-pulse"></div>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow">ISO</div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-5">
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-400 mb-1">Compliance Score</div>
                            <div class="text-2xl font-bold text-blue-600">72%</div>
                        </div>
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-400 mb-1">Controls Passed</div>
                            <div class="text-2xl font-bold text-green-600">67/93</div>
                        </div>
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-400 mb-1">Open Gaps</div>
                            <div class="text-2xl font-bold text-orange-500">26</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="text-sm font-semibold text-gray-600 mb-4">Compliance by Domain</div>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1"><span>Organizational Controls</span><span class="font-semibold text-blue-600">85%</span></div>
                                <div class="h-2 bg-gray-100 rounded-full"><div class="h-2 bg-blue-500 rounded-full" style="width:85%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1"><span>People Controls</span><span class="font-semibold text-purple-600">70%</span></div>
                                <div class="h-2 bg-gray-100 rounded-full"><div class="h-2 bg-purple-500 rounded-full" style="width:70%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1"><span>Physical Controls</span><span class="font-semibold text-green-600">60%</span></div>
                                <div class="h-2 bg-gray-100 rounded-full"><div class="h-2 bg-green-500 rounded-full" style="width:60%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1"><span>Technological Controls</span><span class="font-semibold text-orange-600">75%</span></div>
                                <div class="h-2 bg-gray-100 rounded-full"><div class="h-2 bg-orange-500 rounded-full" style="width:75%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating badges -->
            <div class="absolute -bottom-4 -left-6 bg-white rounded-xl shadow-xl px-3 py-2 border border-gray-100 z-20 card-hover hidden md:flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-brain text-blue-600 text-sm"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-900">AI-Powered</div>
                    <div class="text-[10px] text-gray-500">Recommendations</div>
                </div>
            </div>
            <div class="absolute -top-4 -right-6 bg-white rounded-xl shadow-xl px-3 py-2 border border-gray-100 z-20 card-hover hidden md:flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-green-600 text-sm"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-900">93 Controls</div>
                    <div class="text-[10px] text-gray-500">ISO 27001:2022</div>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-16">
            <div class="bg-white rounded-2xl p-4 shadow-md border border-gray-100 text-center hover-lift card-hover">
                <div class="text-3xl font-bold gradient-text mb-0.5">93</div>
                <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">ISO Controls</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-md border border-gray-100 text-center hover-lift card-hover">
                <div class="text-3xl font-bold gradient-text mb-0.5">46+</div>
                <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">API Endpoints</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-md border border-gray-100 text-center hover-lift card-hover">
                <div class="text-3xl font-bold gradient-text mb-0.5">4</div>
                <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Domains</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-md border border-gray-100 text-center hover-lift card-hover">
                <div class="text-3xl font-bold gradient-text mb-0.5">100%</div>
                <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Web-Based</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="bg-white py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-100 text-purple-700 rounded-full text-sm font-medium mb-3">
                    <i class="fa-solid fa-bolt text-xs"></i>{{ __('Core Features') }}</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1.5">{{ __('Everything You Need') }}</h2>
                <p class="text-base text-gray-500 max-w-2xl mx-auto">Complete ISO 27001:2022 compliance toolkit in one open-source platform</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-4 border border-blue-100 hover:shadow-xl hover:border-blue-200 transition-all card-hover">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-blue-600 transition-colors">
                        <i class="fa-solid fa-shield-halved text-blue-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Comprehensive Assessment') }}</h3>
                    <p class="text-gray-500 leading-normal mb-3">Complete ISO 27001:2022 self-assessment covering all 93 controls across 4 domains: Organizational, People, Physical, and Technological.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">93 Controls</span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">4 Domains</span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">ISO 27001:2022</span>
                    </div>
                </div>
                <div class="group bg-gradient-to-br from-purple-50 to-white rounded-2xl p-4 border border-purple-100 hover:shadow-xl hover:border-purple-200 transition-all card-hover">
                    <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-purple-600 transition-colors">
                        <i class="fa-solid fa-brain text-purple-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('AI-Powered Recommendations') }}</h3>
                    <p class="text-gray-500 leading-normal mb-3">{{ __('Get intelligent gap analysis and corrective action plans powered by multiple AI engines. Prioritized recommendations based on risk level.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">{{ __('Gap Analysis') }}</span>
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">{{ __('CAPA Plans') }}</span>
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">{{ __('Risk Scoring') }}</span>
                    </div>
                </div>
                <div class="group bg-gradient-to-br from-green-50 to-white rounded-2xl p-4 border border-green-100 hover:shadow-xl hover:border-green-200 transition-all card-hover">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-green-600 transition-colors">
                        <i class="fa-solid fa-file-lines text-green-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Professional Reports') }}</h3>
                    <p class="text-gray-500 leading-normal mb-3">{{ __('Generate audit-ready reports in PDF, Excel, and Word formats with one click. Includes executive summary, detailed findings, and action plans.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">{{ __('PDF Export') }}</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">{{ __('Excel Export') }}</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">{{ __('SoA Report') }}</span>
                    </div>
                </div>
                <div class="group bg-gradient-to-br from-orange-50 to-white rounded-2xl p-4 border border-orange-100 hover:shadow-xl hover:border-orange-200 transition-all card-hover">
                    <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-orange-600 transition-colors">
                        <i class="fa-solid fa-users text-orange-600 text-2xl group-hover:text-white transition-colors"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Community Templates') }}</h3>
                    <p class="text-gray-500 leading-normal mb-3">{{ __('Access community-contributed templates, policies, and best practices. Share your own templates and help others achieve compliance faster.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">{{ __('Policy Templates') }}</span>
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">{{ __('Best Practices') }}</span>
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">{{ __('Open Sharing') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="bg-gradient-to-b from-gray-50 to-white py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-medium mb-3">
                    <i class="fa-solid fa-rocket text-xs"></i>{{ __('Simple Process') }}</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1.5">{{ __('Get Started in 3 Easy Steps') }}</h2>
                <p class="text-base text-gray-500 max-w-2xl mx-auto">{{ __('From zero to compliance assessment in under 2 minutes') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-5 shadow-sm border-2 border-blue-100 hover:border-blue-300 hover:shadow-xl transition-all card-hover text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mb-3 mx-auto shadow-lg">1</div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Create Account') }}</h3>
                    <p class="text-gray-500 leading-normal mb-3">{{ __('Create a free account to track your progress, access AI recommendations, and save your audit history') }}</p>
                    <div class="inline-flex items-center gap-2 text-sm text-blue-600 font-semibold bg-blue-50 px-4 py-2 rounded-full">
                        <i class="fa-solid fa-clock"></i> 30 seconds
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border-2 border-purple-100 hover:border-purple-300 hover:shadow-xl transition-all card-hover text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mb-3 mx-auto shadow-lg">2</div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Complete Assessment') }}</h3>
                    <p class="text-gray-500 leading-normal mb-3">Answer guided questions about your security controls across all 93 ISO 27001:2022 requirements</p>
                    <div class="inline-flex items-center gap-2 text-sm text-purple-600 font-semibold bg-purple-50 px-4 py-2 rounded-full">
                        <i class="fa-solid fa-clock"></i> 30-60 minutes
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border-2 border-green-100 hover:border-green-300 hover:shadow-xl transition-all card-hover text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mb-3 mx-auto shadow-lg">3</div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Get AI Insights') }}</h3>
                    <p class="text-gray-500 leading-normal mb-3">{{ __('Receive detailed gap analysis, AI recommendations, and professional reports instantly') }}</p>
                    <div class="inline-flex items-center gap-2 text-sm text-green-600 font-semibold bg-green-50 px-4 py-2 rounded-full">
                        <i class="fa-solid fa-bolt"></i>{{ __('Instant results') }}</div>
                </div>
            </div>
            <div class="text-center mt-6">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:shadow-xl font-semibold shadow-lg transition-all transform hover:-translate-y-1">{{ __('Start Your Assessment Now') }}<i class="fa-solid fa-arrow-right"></i>
                </a>
                <p class="text-sm text-gray-400 mt-3">{{ __('Integrated System &bull; Web-Based &bull; Smart Analysis') }}</p>
            </div>
        </div>
    </section>

    <!-- Target Audience -->
    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-1.5">{{ __('Designed for Various Needs') }}</h2>
                <p class="text-base text-gray-500 max-w-2xl mx-auto">{{ __('This system can be used by various types of users to facilitate information security audits') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 border border-blue-200 hover:shadow-lg transition-all card-hover">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center mb-3 shadow-md">
                        <i class="fa-solid fa-building text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Organizations') }}</h3>
                    <p class="text-sm text-gray-600 leading-normal">{{ __('Conduct a self-assessment to determine the level of information security compliance in your organization.') }}</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-4 border border-purple-200 hover:shadow-lg transition-all card-hover">
                    <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center mb-3 shadow-md">
                        <i class="fa-solid fa-user-tie text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Internal Auditors') }}</h3>
                    <p class="text-sm text-gray-600 leading-normal">{{ __('Use this platform as a tool to conduct structured ISO 27001 compliance audits.') }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-4 border border-green-200 hover:shadow-lg transition-all card-hover">
                    <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center mb-3 shadow-md">
                        <i class="fa-solid fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Academics') }}</h3>
                    <p class="text-sm text-gray-600 leading-normal">{{ __('Learn the application of the ISO 27001:2022 information security standard and its controls.') }}</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-4 border border-orange-200 hover:shadow-lg transition-all card-hover">
                    <div class="w-10 h-10 bg-orange-600 rounded-xl flex items-center justify-center mb-3 shadow-md">
                        <i class="fa-solid fa-laptop-code text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('IT Practitioners') }}</h3>
                    <p class="text-sm text-gray-600 leading-normal">{{ __('Get AI-driven recommendations on corrective actions (CAPA) to address security gaps.') }}</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Integrations Section -->
    <section class="py-10 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium mb-3">
                    <i class="fa-solid fa-plug text-xs"></i>{{ __('Integrations') }}</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1.5">{{ __('Connect with Your Workflow') }}</h2>
                <p class="text-base text-gray-500 max-w-2xl mx-auto">{{ __('Integrate with your existing tools via REST API and webhooks') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:border-orange-200 transition-all card-hover text-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                        <i class="fa-solid fa-diagram-project text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">n8n Workflows</h3>
                    <p class="text-sm text-gray-500">{{ __('Automate CAPA reminders and compliance notifications via n8n') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:border-blue-200 transition-all card-hover text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                        <i class="fa-solid fa-code text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('REST API') }}</h3>
                    <p class="text-sm text-gray-500">46+ endpoints with OpenAPI docs and Laravel Sanctum auth</p>
                </div>
                <div class="bg-white rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:border-green-200 transition-all card-hover text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                        <i class="fa-brands fa-telegram text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('Telegram Alerts') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Receive real-time compliance alerts and notifications via Telegram') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:border-purple-200 transition-all card-hover text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                        <i class="fa-solid fa-webhook text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('Webhooks') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Send events to any endpoint when assessment status changes') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-medium mb-3">
                    <i class="fa-solid fa-lock text-xs"></i>{{ __('Enterprise-Grade Security') }}</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1.5">{{ __('Your Data is Safe') }}</h2>
                <p class="text-base text-gray-500 max-w-2xl mx-auto">{{ __('Built with security best practices from day one') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gray-50 rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:bg-white transition-all card-hover">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fa-solid fa-shield-halved text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('End-to-End Encryption') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('All data transmitted over HTTPS with TLS 1.3 encryption') }}</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:bg-white transition-all card-hover">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fa-solid fa-key text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('Token Authentication') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Secure API access with Laravel Sanctum bearer tokens') }}</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:bg-white transition-all card-hover">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fa-solid fa-bug text-green-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('Input Validation') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Protection against SQL injection and XSS attacks') }}</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:bg-white transition-all card-hover">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fa-solid fa-ban text-orange-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('Brute Force Protection') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Rate limiting and automatic account lockout mechanisms') }}</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:bg-white transition-all card-hover">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fa-solid fa-scroll text-red-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('Audit Logging') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Complete activity tracking for compliance and forensics') }}</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-3 border border-gray-200 hover:shadow-lg hover:bg-white transition-all card-hover">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fa-solid fa-code-branch text-indigo-600 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1.5">{{ __('Open Source Auditable') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Full source code available for security review and audit') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-10 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-medium mb-3">
                    <i class="fa-solid fa-circle-question text-xs"></i>{{ __('FAQ') }}</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1.5">{{ __('Frequently Asked Questions') }}</h2>
                <p class="text-base text-gray-500">{{ __('Everything you need to know about the tool') }}</p>
            </div>
            <div class="space-y-3" x-data="{ open: null }">
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(1)" class="w-full flex items-center justify-between p-4 text-left" aria-label="Toggle FAQ: What is this tool?" aria-expanded="false hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900">{{ __('What is this system used for?') }}</span>
                        <i id="faq-icon-1" class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div id="faq-1" class="hidden px-4 pb-4">
                        <p class="text-gray-500 leading-normal">{{ __('This is a web-based audit decision support system designed to help organizations, internal auditors, academics, and IT practitioners evaluate information security compliance against the ISO 27001:2022 standard.') }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(2)" class="w-full flex items-center justify-between p-4 text-left" aria-label="Toggle FAQ: Do I need to create an account?" aria-expanded="false hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900">{{ __('Do I need to create an account to use it?') }}</span>
                        <i id="faq-icon-2" class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div id="faq-2" class="hidden px-4 pb-4">
                        <p class="text-gray-500 leading-normal">{{ __('Yes. An account is required to access the system. Registration allows you to save and track assessment sessions, view history, access AI-generated recommendations, and use the knowledge base.') }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(3)" class="w-full flex items-center justify-between p-4 text-left" aria-label="Toggle FAQ: ISO 27001:2022 version coverage" aria-expanded="false hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900">Does this cover ISO 27001:2022 or the older 2013 version?</span>
                        <i id="faq-icon-3" class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div id="faq-3" class="hidden px-4 pb-4">
                        <p class="text-gray-500 leading-normal">The system covers ISO 27001:2022, the latest version with all 93 controls across 4 domains (Organizational, People, Physical, Technological). This replaces the older 114-control structure from the 2013 version.</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(4)" class="w-full flex items-center justify-between p-4 text-left" aria-label="Toggle FAQ: Can I export results?" aria-expanded="false hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900">{{ __('Can I export my assessment results?') }}</span>
                        <i id="faq-icon-4" class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div id="faq-4" class="hidden px-4 pb-4">
                        <p class="text-gray-500 leading-normal">{{ __('Yes. You can export your results as PDF, Excel, or Word documents. The reports include an executive summary, detailed findings per control, compliance score, and a Statement of Applicability (SoA).') }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <button onclick="toggleFaq(5)" class="w-full flex items-center justify-between p-4 text-left" aria-label="Toggle FAQ: AI recommendation" aria-expanded="false hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-900">{{ __('How does the AI recommendation work?') }}</span>
                        <i id="faq-icon-5" class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div id="faq-5" class="hidden px-4 pb-4">
                        <p class="text-gray-500 leading-normal">{{ __('After completing your assessment, the AI engine analyzes your gap areas and generates prioritized corrective action plans (CAPA) with specific implementation guidance for each non-compliant control.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scroll to Top Button -->
    <button id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="fixed bottom-8 right-8 w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 z-50 hidden flex items-center justify-center">
        <i class="fa-solid fa-chevron-up"></i>
    </button>

    <script>
        // FAQ Toggle
        function toggleFaq(id) {
            const content = document.getElementById('faq-' + id);
            const icon = document.getElementById('faq-icon-' + id);
            const isHidden = content.classList.contains('hidden');
            for (let i = 1; i <= 5; i++) {
                document.getElementById('faq-' + i).classList.add('hidden');
                document.getElementById('faq-icon-' + i).style.transform = '';
            }
            if (isHidden) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            }
        }

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const btn = document.getElementById('mobileMenuBtn');
            const isHidden = menu.classList.contains('hidden');
            menu.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            const icon = btn.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-xmark');
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // Close mobile menu if open
                        const menu = document.getElementById('mobileMenu');
                        if (!menu.classList.contains('hidden')) {
                            toggleMobileMenu();
                        }
                    }
                }
            });
        });

        // Scroll to top button
        window.addEventListener('scroll', function() {
            const btn = document.getElementById('scrollTop');
            if (window.scrollY > 400) {
                btn.classList.remove('hidden');
                btn.classList.add('flex');
            } else {
                btn.classList.add('hidden');
                btn.classList.remove('flex');
            }
        });

        // Active nav link on scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('nav a[href^="#"]');
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    navLinks.forEach(function(link) {
                        link.classList.remove('text-blue-600', 'font-semibold');
                        if (link.getAttribute('href') === '#' + entry.target.id) {
                            link.classList.add('text-blue-600', 'font-semibold');
                        }
                    });
                }
            });
        }, { threshold: 0.3 });
        sections.forEach(function(s) { observer.observe(s); });

        // Animate stats on scroll
        const statNumbers = document.querySelectorAll('.gradient-text');
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                }
            });
        }, { threshold: 0.5 });
        statNumbers.forEach(function(el) { statsObserver.observe(el); });
    </script>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-blue-600 via-blue-700 to-purple-700 py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/20 text-white rounded-full text-sm font-medium mb-3">
                <i class="fa-solid fa-shield-halved text-xs"></i>{{ __('Start Assessment') }}</div>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-4 leading-tight">{{ __('Ready to Measure ISO 27001:2022 Compliance?') }}</h2>
            <p class="text-lg text-blue-100 mb-4 leading-normal">{{ __('Start your assessment today. Create an account to track your progress and receive intelligent AI recommendations.') }}</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('login') }}" class="flex items-center gap-2 px-6 py-3 bg-white text-blue-600 rounded-xl hover:bg-blue-50 font-bold shadow-2xl transition-all transform hover:-translate-y-1">
                    <i class="fa-solid fa-right-to-bracket"></i>{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="flex items-center gap-2 px-6 py-3 bg-white/10 border-2 border-white/50 text-white rounded-xl hover:bg-white/20 font-bold transition-all transform hover:-translate-y-1">
                    <i class="fa-solid fa-user-plus"></i>{{ __('Create Account') }}</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-400 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center font-bold text-white text-sm">ISO</div>
                        <span class="font-bold text-white text-lg">{{ __('ISO 27001:2022 Audit Tool') }}</span>
                    </div>
                    <p class="text-sm leading-normal mb-3">{{ __('Audit decision support system for ISO 27001:2022 compliance.') }}</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-2 text-xs uppercase tracking-widest">{{ __('Platform') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">{{ __('Start Assessment') }}</a></li>
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                        <li><a href="{{ route('knowledge-base.index') }}" class="hover:text-white transition-colors">{{ __('Knowledge Base') }}</a></li>
                        <li><a href="{{ route('community.index') }}" class="hover:text-white transition-colors">{{ __('Community Templates') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-2 text-xs uppercase tracking-widest">{{ __('System') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/api/documentation" class="hover:text-white transition-colors">{{ __('API Documentation') }}</a></li>
                        <li><a href="/api/health" class="hover:text-white transition-colors">{{ __('System Status') }}</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">{{ __('Login') }}</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">{{ __('Register') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-600 font-medium">
                    &copy; {{ date('Y') }} ISO 27001:2022 Audit & Compliance Assessment System.
                </p>
                <p class="text-xs text-gray-700">{{ __('Built with Laravel · Powered by AI') }}</p>
            </div>
        </div>
    </footer>

</body>
</html>