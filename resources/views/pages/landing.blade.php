<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%232563eb'/><text y='.9em' font-size='55' font-family='sans-serif' font-weight='bold' fill='white' x='50%' text-anchor='middle'>AG</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    <meta name="description" content="AuditGuard — Internal ISO 27001:2022 Compliance Assessment & ISMS Portal.">
    <meta name="keywords" content="AuditGuard, ISO 27001:2022, compliance, ISMS, security assessment, AI audit, gap analysis">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('AuditGuard | ISO 27001:2022 Internal ISMS Portal') }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        .gradient-text { background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 25px 50px rgba(0,0,0,0.15); }
        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.2s; opacity: 0; }
        .delay-3 { animation-delay: 0.3s; opacity: 0; }
        .delay-4 { animation-delay: 0.4s; opacity: 0; }
        [x-cloak] { display: none !important; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; scroll-behavior: auto !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-sky-50 text-slate-900">

    <!-- Navigation -->
    <nav class="bg-white/85 backdrop-blur-md border-b border-slate-200/60 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.jpg') }}" alt="AuditGuard" class="w-9 h-9 rounded-lg shrink-0 shadow-md object-contain bg-white p-0.5">
                    <div>
                        <div class="font-bold text-base leading-none">
                            <span style="color: #2563eb;">Audit</span><span style="color: #0284c7;">Guard</span>
                        </div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-widest mt-0.5 font-bold">{{ __('ISMS INTERNAL PORTAL') }}</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-6 text-xs font-bold uppercase tracking-wider text-slate-600">
                    <a href="#features" class="hover:text-blue-600 transition-colors">{{ __('Portal Modules') }}</a>
                    <a href="#how-it-works" class="hover:text-blue-600 transition-colors">{{ __('Audit Cycle') }}</a>
                    <a href="#faq" class="hover:text-blue-600 transition-colors">{{ __('FAQ') }}</a>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Language Switcher --}}
                    @php
                        $currentLang = app()->getLocale();
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                            <i class="fa-solid fa-globe text-slate-400"></i>
                            <span>{{ strtoupper($currentLang) }}</span>
                            <i class="fa-solid fa-chevron-down text-[8px] text-slate-400"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 bg-white border border-slate-100 rounded-lg shadow-xl py-1 w-24 z-50" x-cloak>
                            <a href="{{ route('lang.switch', 'en') }}" class="block px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">EN</a>
                            <a href="{{ route('lang.switch', 'id') }}" class="block px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">ID</a>
                        </div>
                    </div>

                    @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-sky-600 text-white rounded-lg hover:shadow-lg font-bold text-xs uppercase tracking-wider transition-all shadow-md">{{ __('Enter Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-sky-600 text-white rounded-lg hover:shadow-lg font-bold text-xs uppercase tracking-wider transition-all shadow-md">{{ __('Portal Login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold mb-5 animate-fade-in-up">
            <i class="fa-solid fa-shield-halved text-xs animate-pulse"></i>
            {{ __('Authorized Organizational Access Only') }}
        </div>

        <!-- Headline -->
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight mb-5 animate-fade-in-up delay-1 uppercase tracking-tight">
            {{ __('Internal') }} <span class="gradient-text">ISO 27001:2022</span><br>{{ __('Compliance & ISMS Portal') }}
        </h1>

        <!-- Subheadline -->
        <p class="text-sm md:text-base text-slate-500 max-w-2xl mx-auto mb-8 leading-relaxed animate-fade-in-up delay-2 font-medium">
            {{ __('Welcome to the central security management system. AuditGuard facilitates organization-wide ISMS self-assessment, gap analysis, corrective action plans (CAPA), and real-time security posture tracking.') }}
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-wrap justify-center gap-4 mb-12 animate-fade-in-up delay-3">
            @auth
                <a href="{{ route('dashboard') }}" class="group inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-blue-600 to-sky-600 text-white rounded-xl hover:shadow-xl font-bold text-xs uppercase tracking-widest shadow-lg transition-all transform hover:-translate-y-1">
                    {{ __('Go to Workspace') }} <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </a>
            @else
                <a href="{{ route('login') }}" class="group inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-blue-600 to-sky-600 text-white rounded-xl hover:shadow-xl font-bold text-xs uppercase tracking-widest shadow-lg transition-all transform hover:-translate-y-1">
                    {{ __('Portal Login') }} <i class="fa-solid fa-right-to-bracket group-hover:translate-x-1 transition-transform"></i>
                </a>
            @endauth
        </div>

        <!-- Trust badges -->
        <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-bold uppercase tracking-wider text-slate-400 mb-12 animate-fade-in-up delay-4">
            <div class="flex items-center gap-2"><i class="fa-solid fa-check-circle text-blue-500"></i> {{ __('93 Security Controls Covered') }}</div>
            <div class="flex items-center gap-2"><i class="fa-solid fa-check-circle text-sky-500"></i> {{ __('AI-Powered Corrective Plans') }}</div>
            <div class="flex items-center gap-2"><i class="fa-solid fa-check-circle text-teal-600"></i> {{ __('Internal Audit & Reporting') }}</div>
        </div>

        <!-- Dashboard Mockup (below text) -->
        <div class="relative mx-auto max-w-4xl animate-fade-in-up delay-4">
            <div class="absolute -top-10 left-1/4 w-64 h-64 bg-blue-300 rounded-full opacity-10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 right-1/4 w-64 h-64 bg-sky-300 rounded-full opacity-10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden card-hover">
                <!-- Browser Bar -->
                <div class="bg-slate-50 px-4 py-2.5 flex items-center justify-between border-b border-slate-200/60">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                    </div>
                    <div class="bg-white rounded-md px-3 py-1 text-[10px] font-bold text-slate-400 ml-2 flex items-center gap-1.5 w-80 shadow-inner">
                        <i class="fa-solid fa-lock text-emerald-500"></i>
                        <span>isms.internal-organization.org/compliance</span>
                    </div>
                    <div class="w-4"></div>
                </div>
                <!-- Dashboard Content -->
                <div class="p-6 bg-gradient-to-br from-slate-50 to-blue-50/30">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-left">
                            <div class="h-4 w-40 bg-slate-300 rounded-md mb-2 animate-pulse"></div>
                            <div class="h-2.5 w-24 bg-slate-200 rounded-md animate-pulse"></div>
                        </div>
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black shadow overflow-hidden bg-white p-0.5 border border-slate-100">
                            <img src="{{ asset('images/logo.jpg') }}" alt="AuditGuard" class="w-full h-full object-contain">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-5">
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-left">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Compliance Score') }}</div>
                            <div class="text-2xl font-black text-blue-600">72%</div>
                        </div>
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-left">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Assessed Controls') }}</div>
                            <div class="text-2xl font-black text-sky-600">67/93</div>
                        </div>
                        <div class="bg-white rounded-xl p-3 shadow-sm border border-slate-100 text-left">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Remaining Gaps') }}</div>
                            <div class="text-2xl font-black text-rose-500">26</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-left">
                        <div class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-4">{{ __('Compliance by Domain') }}</div>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1"><span>{{ __('Organizational Controls') }}</span><span class="font-black text-blue-600">85%</span></div>
                                <div class="h-1.5 bg-slate-100 rounded-full"><div class="h-1.5 bg-blue-500 rounded-full" style="width:85%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1"><span>{{ __('People Controls') }}</span><span class="font-black text-sky-600">70%</span></div>
                                <div class="h-1.5 bg-slate-100 rounded-full"><div class="h-1.5 bg-sky-500 rounded-full" style="width:70%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1"><span>{{ __('Physical Controls') }}</span><span class="font-black text-emerald-600">60%</span></div>
                                <div class="h-1.5 bg-slate-100 rounded-full"><div class="h-1.5 bg-emerald-500 rounded-full" style="width:60%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1"><span>{{ __('Technological Controls') }}</span><span class="font-black text-amber-600">75%</span></div>
                                <div class="h-1.5 bg-slate-100 rounded-full"><div class="h-1.5 bg-amber-500 rounded-full" style="width:75%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating badges -->
            <div class="absolute -bottom-4 -left-6 bg-white rounded-xl shadow-xl px-4 py-2.5 border border-slate-100 z-20 card-hover hidden md:flex items-center gap-2">
                <div class="w-8 h-8 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-robot text-sm"></i>
                </div>
                <div class="text-left leading-tight">
                    <div class="text-[10px] font-black text-slate-900 uppercase tracking-wide">{{ __('AI Copilot') }}</div>
                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Recommendations') }}</div>
                </div>
            </div>
            <div class="absolute -top-4 -right-6 bg-white rounded-xl shadow-xl px-4 py-2.5 border border-slate-100 z-20 card-hover hidden md:flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-server text-sm"></i>
                </div>
                <div class="text-left leading-tight">
                    <div class="text-[10px] font-black text-slate-900 uppercase tracking-wide">93 Controls</div>
                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">ISO 27001:2022</div>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-16">
            <div class="bg-white rounded-2xl p-4 shadow-md border border-slate-100 text-center hover-lift card-hover">
                <div class="text-3xl font-black text-blue-600 mb-0.5">93</div>
                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Standard Controls') }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-md border border-slate-100 text-center hover-lift card-hover">
                <div class="text-3xl font-black text-sky-600 mb-0.5">4</div>
                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Audit Domains') }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-md border border-slate-100 text-center hover-lift card-hover">
                <div class="text-3xl font-black text-emerald-600 mb-0.5">100%</div>
                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Auditable Logs') }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-md border border-slate-100 text-center hover-lift card-hover">
                <div class="text-3xl font-black text-amber-500 mb-0.5">n8n</div>
                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ __('CAPA Automation') }}</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="bg-white py-12 border-t border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold mb-3 uppercase tracking-wide">
                    <i class="fa-solid fa-cube text-xs"></i>{{ __('Core Modules') }}</div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-1.5 uppercase tracking-tight">{{ __('Centralized Compliance Toolkit') }}</h2>
                <p class="text-sm text-slate-500 max-w-2xl mx-auto font-medium">{{ __('Empowering organizational security with unified compliance workflows') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-5 border border-blue-100 hover:shadow-xl hover:border-blue-200 transition-all card-hover text-left">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <i class="fa-solid fa-clipboard-check text-xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5">{{ __('Guided Self-Assessment') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-3 font-medium">{{ __('Complete structured self-assessments covering all 93 controls. Easily track implementation levels, compliance scores, and record evidence.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('93 Controls') }}</span>
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('4 Domains') }}</span>
                    </div>
                </div>
                <div class="group bg-gradient-to-br from-sky-50 to-white rounded-2xl p-5 border border-sky-100 hover:shadow-xl hover:border-sky-200 transition-all card-hover text-left">
                    <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-sky-600 group-hover:text-white transition-all">
                        <i class="fa-solid fa-robot text-xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5">{{ __('AI Gap & CAPA Analysis') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-3 font-medium">{{ __('Generate strategic remediation recommendations and Corrective Action Plans (CAPA) customized to your organization\'s findings.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 bg-sky-100 text-sky-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('Gap Detection') }}</span>
                        <span class="px-2.5 py-1 bg-sky-100 text-sky-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('CAPA Generator') }}</span>
                    </div>
                </div>
                <div class="group bg-gradient-to-br from-sky-50 to-white rounded-2xl p-5 border border-sky-100 hover:shadow-xl hover:border-sky-200 transition-all card-hover text-left">
                    <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-sky-600 group-hover:text-white transition-all">
                        <i class="fa-solid fa-file-pdf text-xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5">{{ __('Audit-Ready Exports') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-3 font-medium">{{ __('Export Statement of Applicability (SoA) and gap assessment reports in PDF or Excel. Perfectly prepared for official ISO certification audits.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 bg-sky-100 text-sky-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('PDF / Excel Reports') }}</span>
                        <span class="px-2.5 py-1 bg-sky-100 text-sky-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('SoA Exporter') }}</span>
                    </div>
                </div>
                <div class="group bg-gradient-to-br from-orange-50 to-white rounded-2xl p-5 border border-orange-100 hover:shadow-xl hover:border-orange-200 transition-all card-hover text-left">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-orange-600 group-hover:text-white transition-all">
                        <i class="fa-solid fa-book-bookmark text-xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5">{{ __('ISMS Knowledge Base') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-3 font-medium">{{ __('Maintain centralized security policies, reference guidelines, and implementation documentation accessible to all internal auditors.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 bg-orange-100 text-orange-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('Internal Policies') }}</span>
                        <span class="px-2.5 py-1 bg-orange-100 text-orange-700 rounded-lg text-[9px] font-bold uppercase tracking-wider">{{ __('Reference Standard') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works / Audit Cycle -->
    <section id="how-it-works" class="bg-slate-50/50 py-12 border-t border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold mb-3 uppercase tracking-wide">
                    <i class="fa-solid fa-arrows-spin text-xs"></i>{{ __('Compliance Cycle') }}</div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-1.5 uppercase tracking-tight">{{ __('The Internal Audit Process') }}</h2>
                <p class="text-sm text-slate-500 max-w-2xl mx-auto font-medium">{{ __('How organizations systematically evaluate and improve their security posture') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 text-center hover-lift card-hover">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-xl font-black mb-4 mx-auto shadow-lg">1</div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5 uppercase tracking-tight">{{ __('Assess Controls') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ __('Auditees evaluate individual control implementations against the ISO standard, logging evidence and justifications.') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 text-center hover-lift card-hover">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-500 to-sky-600 rounded-full flex items-center justify-center text-white text-xl font-black mb-4 mx-auto shadow-lg">2</div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5 uppercase tracking-tight">{{ __('Analyze Gaps') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ __('Lead auditors review findings alongside AI recommendations to highlight system gaps and security exposure.') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 text-center hover-lift card-hover">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-sky-500 rounded-full flex items-center justify-center text-white text-xl font-black mb-4 mx-auto shadow-lg">3</div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5 uppercase tracking-tight">{{ __('Track CAPA Plans') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ __('Assign PICs, set due dates, and monitor corrective actions directly within the Compliance Center until completion.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-12 bg-white border-t border-slate-200/60">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold mb-3 uppercase tracking-wide">
                    <i class="fa-solid fa-circle-question text-xs"></i>{{ __('FAQ') }}</div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-1.5 uppercase tracking-tight">{{ __('Frequently Asked Questions') }}</h2>
                <p class="text-sm text-slate-500 font-medium">{{ __('Answers to common questions regarding the ISMS audit workflow') }}</p>
            </div>
            <div class="space-y-3">
                <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-slate-50 transition-colors" aria-label="Toggle FAQ" :aria-expanded="open">
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">{{ __('What is this system used for?') }}</span>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse x-cloak>
                        <div class="px-4 pb-4">
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ __('This is a web-based decision support system designed to help organizations systematically evaluate, manage, and report information security compliance against the ISO 27001:2022 standard.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-slate-50 transition-colors" aria-label="Toggle FAQ" :aria-expanded="open">
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">{{ __('Who has access to this portal?') }}</span>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse x-cloak>
                        <div class="px-4 pb-4">
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ __('Access is restricted to authorized organizational members. Auditors, IT security personnel, compliance managers, and executive stakeholders are assigned roles based on their access requirements.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-slate-50 transition-colors" aria-label="Toggle FAQ" :aria-expanded="open">
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">{{ __('Does this cover the 2022 version of ISO 27001?') }}</span>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse x-cloak>
                        <div class="px-4 pb-4">
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ __('Yes, the system is fully updated to cover ISO 27001:2022, which includes the revised structure of 93 controls classified into 4 domains (Organizational, People, Physical, and Technological).') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scroll to Top Button -->
    <button id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="fixed bottom-8 right-8 w-12 h-12 bg-gradient-to-br from-blue-600 to-sky-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 z-50 hidden flex items-center justify-center">
        <i class="fa-solid fa-chevron-up"></i>
    </button>

    <script>
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

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    </script>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 text-left">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-sky-600 rounded-lg overflow-hidden border border-slate-800">
                            <img src="{{ asset('images/logo.jpg') }}" alt="AuditGuard" class="w-full h-full object-contain bg-white p-0.5">
                        </div>
                        <span class="font-bold text-lg text-white">
                            <span>Audit</span><span style="color: #38bdf8;">Guard</span>
                        </span>
                    </div>
                    <p class="text-xs leading-relaxed font-medium">{{ __('AI-powered audit decision support system for ISO 27001:2022 compliance & information security governance.') }}</p>
                </div>
                <div>
                    <h4 class="font-black text-white mb-2 text-[10px] uppercase tracking-widest">{{ __('Internal Access') }}</h4>
                    <ul class="space-y-2 text-xs font-bold">
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">{{ __('Portal Dashboard') }}</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">{{ __('Auditor Login') }}</a></li>
                        @endauth
                        <li><a href="{{ route('knowledge-base.index') }}" class="hover:text-white transition-colors">{{ __('Reference Knowledge') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-black text-white mb-2 text-[10px] uppercase tracking-widest">{{ __('System') }}</h4>
                    <ul class="space-y-2 text-xs font-bold">
                        <li><a href="/api/documentation" class="hover:text-white transition-colors">{{ __('API Specifications') }}</a></li>
                        <li><a href="/api/health" class="hover:text-white transition-colors">{{ __('Health Status') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-900 pt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-left">
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                    &copy; {{ date('Y') }} AuditGuard &mdash; {{ __('ISO 27001:2022 Compliance Assessment Platform.') }}
                </p>
                <p class="text-[10px] text-slate-600 font-bold uppercase tracking-wider">{{ __('Authorized Use Only') }}</p>
            </div>
        </div>
    </footer>

</body>
</html>