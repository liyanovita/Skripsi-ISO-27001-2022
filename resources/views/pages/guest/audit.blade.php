<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Assessment | Audit Intelligence Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
        
        /* Mobile adjustments */
        @media (max-width: 1024px) {
            body { padding-bottom: 80px; }
        }
        
        /* Keyboard shortcuts hint */
        .keyboard-hint {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }
        
        .keyboard-hint.show {
            opacity: 1;
        }
        
        /* Scroll indicator for modals */
        .modal-scroll-indicator {
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 2px;
        }
        
        .modal-scroll-thumb {
            width: 100%;
            background: rgba(59, 130, 246, 0.5);
            border-radius: 2px;
            transition: background 0.3s;
        }
        
        .modal-scroll-thumb:hover {
            background: rgba(59, 130, 246, 0.8);
        }
        
        /* Better mobile modal sizing */
        @media (max-height: 600px) {
            .onboarding-modal {
                max-height: 95vh;
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="h-full text-slate-700 overflow-y-auto custom-scrollbar">

    <div x-data="guestAudit()" x-init="init()" @keydown.window="handleKeyboard($event)" class="min-h-screen flex flex-col">
        {{-- Header --}}
        <header class="bg-slate-900 text-white py-5 px-10 flex items-center justify-between sticky top-0 z-50 shadow-xl">
            <div class="flex items-center gap-4">
                <a href="{{ route('landing') }}" class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-blue-700 transition-all">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </a>
                <div>
                    <h1 class="text-sm font-bold uppercase tracking-widest">{{ __('Anonymous Assessment') }}</h1>
                    <p class="text-[9px] text-blue-400 font-bold uppercase tracking-widest">ISO 27001:2022 Framework</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-3 px-6 py-2 bg-white/5 border border-white/10 rounded-full">
                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold uppercase tracking-widest">Progress: <span x-text="progressPercentage"></span>%</span>
                </div>
                
                <!-- Help Button -->
                <button @click="showOnboarding = true" class="px-4 py-2 bg-white/10 border border-white/10 rounded-full text-[10px] font-bold uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-question-circle"></i>{{ __('Help') }}</button>
                
                <!-- Export Dropdown -->
                <div x-data="{ exportOpen: false }" class="relative">
                    <button @click="exportOpen = !exportOpen" class="px-6 py-2 bg-white/10 border border-white/10 rounded-full text-[10px] font-bold uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-download"></i>{{ __('Export') }}<i class="fa-solid fa-chevron-down text-[8px]"></i>
                    </button>
                    <div x-show="exportOpen" @click.away="exportOpen = false" x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-slate-200 py-2 z-50">
                        <button @click="exportToPdf(); exportOpen = false" 
                                :disabled="isExporting"
                                class="w-full text-left px-4 py-2 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid" :class="isExporting ? 'fa-spinner fa-spin text-blue-500' : 'fa-file-pdf text-red-500'"></i>
                            <span x-text="isExporting ? 'Generating PDF...' : 'Export to PDF'"></span>
                        </button>
                        <button @click="exportToJson(); exportOpen = false" 
                                :disabled="isExporting"
                                class="w-full text-left px-4 py-2 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-file-code text-blue-500"></i>{{ __('Export to JSON') }}</button>
                        <div class="border-t border-slate-100 my-2"></div>
                        <button @click="importFromJson(); exportOpen = false" 
                                class="w-full text-left px-4 py-2 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-3">
                            <i class="fa-solid fa-file-import text-green-500"></i>{{ __('Import JSON') }}</button>
                    </div>
                </div>
                
                <a href="{{ route('register') }}" class="px-5 py-2 bg-blue-600 text-white rounded-full text-[10px] font-bold uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-xs"></i>{{ __('Save to Account') }}<span x-show="Object.keys(answers).length > 0" 
                          class="bg-white/20 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full"
                          x-text="Object.keys(answers).length"></span>
                </a>
            </div>
        </header>

        {{-- Warning Banner --}}
        <div class="bg-amber-50 border-b border-amber-100 px-10 py-2.5 flex items-center justify-between gap-3 text-amber-700">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Data is stored locally in your browser. Do not clear the cache before exporting results!') }}</span>
            </div>
            <button @click="showWarningModal = true" class="text-[9px] font-bold uppercase tracking-widest text-amber-600 hover:text-amber-800 underline">{{ __('Learn More') }}</button>
        </div>
        
        {{-- Progress Bar --}}
        <div class="h-0.5 bg-slate-200 sticky top-[62px] z-40">
            <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-700" :style="'width: ' + progressPercentage + '%'"></div>
        </div>

        {{-- Mobile Bottom Navigation --}}
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-40 safe-area-bottom">
            <div class="grid grid-cols-4 gap-1 p-2">
                <button @click="mobileMenuOpen = true; $nextTick(() => $refs.mobileSearchInput?.focus())" class="flex flex-col items-center gap-1 py-2 text-slate-600 hover:text-blue-600 transition-colors">
                    <i class="fa-solid fa-bars text-lg"></i>
                    <span class="text-[8px] font-bold uppercase tracking-widest">{{ __('Menu') }}</span>
                </button>
                <button @click="scrollToTop()" class="flex flex-col items-center gap-1 py-2 text-slate-600 hover:text-blue-600 transition-colors">
                    <i class="fa-solid fa-arrow-up text-lg"></i>
                    <span class="text-[8px] font-bold uppercase tracking-widest">{{ __('Top') }}</span>
                </button>
                <button @click="exportToPdf()" class="flex flex-col items-center gap-1 py-2 text-slate-600 hover:text-blue-600 transition-colors">
                    <i class="fa-solid fa-file-pdf text-lg"></i>
                    <span class="text-[8px] font-bold uppercase tracking-widest">{{ __('Export') }}</span>
                </button>
                <button @click="window.location.href = '{{ route('register') }}'" class="flex flex-col items-center gap-1 py-2 text-blue-600 hover:text-blue-700 transition-colors">
                    <i class="fa-solid fa-save text-lg"></i>
                    <span class="text-[8px] font-bold uppercase tracking-widest">{{ __('Save') }}</span>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Overlay --}}
        <div x-show="mobileMenuOpen" 
             x-cloak
             @click="mobileMenuOpen = false"
             class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50">
            <div @click.stop class="absolute left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-slate-200 p-4 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">{{ __('Controls') }}</h3>
                    <button @click="mobileMenuOpen = false" class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                
                {{-- Mobile Search --}}
                <div class="p-4 border-b border-slate-100">
                    <div class="relative">
                        <input type="text" 
                               x-model="searchQuery" 
                               @input="filterStandards()"
                               x-ref="mobileSearchInput"
                               placeholder="{{ __('Search controls...') }}"
                               class="w-full px-4 py-2 pl-10 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    </div>
                    
                    {{-- Mobile Filter --}}
                    <div class="flex gap-2 mt-3">
                        <button @click="filterMode = 'all'; filterStandards()" 
                                :class="filterMode === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'"
                                class="flex-1 px-3 py-2 rounded-lg text-[9px] font-bold uppercase tracking-widest">{{ __('All') }}</button>
                        <button @click="filterMode = 'answered'; filterStandards()" 
                                :class="filterMode === 'answered' ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-600'"
                                class="flex-1 px-3 py-2 rounded-lg text-[9px] font-bold uppercase tracking-widest">{{ __('Done') }}</button>
                        <button @click="filterMode = 'unanswered'; filterStandards()" 
                                :class="filterMode === 'unanswered' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600'"
                                class="flex-1 px-3 py-2 rounded-lg text-[9px] font-bold uppercase tracking-widest">{{ __('Todo') }}</button>
                    </div>
                </div>
                
                {{-- Mobile Control List --}}
                <div class="p-4 space-y-2">
                    <template x-for="item in filteredStandards" :key="item.id">
                        <div>
                            <template x-if="!item.description && !item.questions">
                                <div class="px-3 py-2 text-[10px] font-bold text-slate-900 uppercase tracking-widest mt-3 border-l-2 border-blue-600 bg-slate-50/50 rounded-r-lg" x-text="item.code + ' ' + item.title"></div>
                            </template>
                            
                            <template x-if="item.description || item.questions">
                                <button @click="scrollToItem(item.id); openItems[item.id] = true; mobileMenuOpen = false;" 
                                    :class="answers[item.id] !== undefined ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-slate-200 text-slate-600'"
                                    class="w-full text-left px-3 py-2 rounded-xl border transition-all flex items-center justify-between">
                                    <div class="min-w-0 pr-2">
                                        <p class="text-[10px] font-bold" x-text="item.code"></p>
                                        <p class="text-[9px] font-medium truncate opacity-60" x-text="item.title"></p>
                                    </div>
                                    <i x-show="answers[item.id] !== undefined" class="fa-solid fa-circle-check text-[10px]"></i>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex-1 flex gap-6 p-6 max-w-7xl mx-auto w-full">
            {{-- Navigation Sidebar --}}
            <aside class="w-64 shrink-0 hidden lg:block sticky top-28 h-[calc(100vh-140px)]">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 h-full flex flex-col shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Control List') }}</h2>
                        <div class="flex gap-2">
                            <button @click="expandAll()" class="text-[8px] font-bold uppercase text-blue-600 hover:underline">{{ __('Open All') }}</button>
                            <button @click="collapseAll()" class="text-[8px] font-bold uppercase text-slate-400 hover:underline">{{ __('Close All') }}</button>
                        </div>
                    </div>
                    
                    {{-- Search Box --}}
                    <div class="mb-3">
                        <div class="relative">
                            <input type="text" 
                                   x-model="searchQuery" 
                                   @input="filterStandards()"
                                   placeholder="{{ __('Search controls...') }}"
                                   class="w-full px-3 py-1.5 pl-8 text-xs font-medium bg-slate-50 border border-slate-200 rounded-lg outline-none focus:bg-white focus:border-blue-600 transition-all">
                            <i class="fa-solid fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            <button x-show="searchQuery" @click="searchQuery = ''; filterStandards()" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-times text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Filter Buttons --}}
                    <div class="mb-3 flex gap-1.5">
                        <button @click="filterMode = 'all'; filterStandards()" 
                                :class="filterMode === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="flex-1 px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider transition-all">{{ __('All') }}</button>
                        <button @click="filterMode = 'answered'; filterStandards()" 
                                :class="filterMode === 'answered' ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="flex-1 px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider transition-all">{{ __('Done') }}</button>
                        <button @click="filterMode = 'unanswered'; filterStandards()" 
                                :class="filterMode === 'unanswered' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="flex-1 px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider transition-all">{{ __('Todo') }}</button>
                    </div>
                    
                    {{-- Results Count --}}
                    <div class="mb-2 px-1">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                            <span x-text="filteredStandards.filter(i => i.description || i.questions).length"></span> of <span x-text="standards.filter(i => i.description || i.questions).length"></span> controls
                        </p>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                        <template x-for="item in filteredStandards" :key="item.id">
                            <div>
                                {{-- Header / Parent --}}
                                <template x-if="!item.description && !item.questions">
                                    <div class="px-3 py-2 text-[9px] font-bold text-slate-900 uppercase tracking-widest mt-3 border-l-2 border-blue-600 bg-slate-50/50 rounded-r-lg" x-text="item.code + ' ' + item.title"></div>
                                </template>
                                
                                {{-- Actionable Requirement --}}
                                <template x-if="item.description || item.questions">
                                    <button @click="scrollToItem(item.id); openItems[item.id] = true;" 
                                        :class="{
                                            'bg-emerald-50 border-emerald-200 text-emerald-700': answers[item.id] >= 3,
                                            'bg-amber-50 border-amber-200 text-amber-700': answers[item.id] >= 1 && answers[item.id] < 3,
                                            'bg-red-50 border-red-200 text-red-600': answers[item.id] === 0,
                                            'bg-white border-slate-100 text-slate-500 hover:border-blue-300': answers[item.id] === undefined
                                        }"
                                        class="w-full text-left px-3 py-2 rounded-lg border transition-all flex items-center justify-between group ml-1.5 mt-0.5">
                                        <div class="min-w-0 pr-2">
                                            <p class="text-[10px] font-bold tracking-tight" x-text="item.code"></p>
                                            <p class="text-[9px] font-medium truncate opacity-60" x-text="item.title"></p>
                                        </div>
                                        <template x-if="answers[item.id] !== undefined">
                                            <span class="text-[9px] font-bold shrink-0 w-4 h-4 rounded flex items-center justify-center"
                                                  :class="{
                                                      'bg-emerald-500 text-white': answers[item.id] >= 3,
                                                      'bg-amber-400 text-white': answers[item.id] >= 1 && answers[item.id] < 3,
                                                      'bg-red-400 text-white': answers[item.id] === 0
                                                  }"
                                                  x-text="answers[item.id]"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </template>
                        
                        {{-- No Results --}}
                        <div x-show="filteredStandards.filter(i => i.description || i.questions).length === 0" class="text-center py-6">
                            <i class="fa-solid fa-search text-2xl text-slate-300 mb-2"></i>
                            <p class="text-xs font-bold text-slate-400">{{ __('No controls found') }}</p>
                            <button @click="searchQuery = ''; filterMode = 'all'; filterStandards()" class="mt-2 text-[9px] font-bold text-blue-500 hover:underline uppercase tracking-widest">{{ __('Clear filters') }}</button>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Assessment Wizard --}}
            <main class="flex-1 space-y-4">
                <template x-for="item in filteredStandards" :key="item.id">
                    <div :id="'item-' + item.id">
                        {{-- Parent Section Header --}}
                        <template x-if="!item.description && !item.questions">
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-5 mb-2 border border-blue-200/60 shadow-sm">
                                <div class="absolute right-0 top-0 bottom-0 w-32 bg-blue-400/10 blur-2xl"></div>
                                <div class="relative flex items-center gap-4">
                                    <div class="w-12 h-12 bg-blue-600/10 border border-blue-300/50 rounded-xl flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-layer-group text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-bold text-blue-500 uppercase tracking-widest mb-0.5" x-text="item.code"></p>
                                        <h2 class="text-base font-bold text-slate-800 tracking-tight" x-text="item.title"></h2>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Actionable Requirement Card --}}
                        <template x-if="item.description || item.questions">
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all scroll-mt-28 overflow-hidden"
                                 :class="{
                                     'border-l-4 border-l-emerald-500': answers[item.id] >= 3,
                                     'border-l-4 border-l-amber-400': answers[item.id] >= 1 && answers[item.id] < 3,
                                     'border-l-4 border-l-red-400': answers[item.id] === 0
                                 }">
                                {{-- Card Header / Toggle --}}
                                <div @click="openItems[item.id] = !openItems[item.id]" class="p-5 cursor-pointer flex items-center justify-between group bg-white hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-slate-800 to-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-[10px] shadow-md shrink-0 border border-slate-700/50 tracking-tight leading-none text-center px-1">
                                            <span x-text="item.code"></span>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-900 tracking-tight leading-tight group-hover:text-blue-600 transition-colors" x-text="item.title"></h3>
                                            <div class="flex gap-1.5 mt-1">
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[8px] font-bold rounded uppercase tracking-widest" x-text="item.level"></span>
                                                <span x-show="answers[item.id] !== undefined" class="px-2 py-0.5 bg-blue-600 text-white rounded text-[8px] font-bold uppercase tracking-widest">{{ __('Score L') }}<span x-text="answers[item.id]"></span></span>
                                                <span x-show="answers[item.id] !== undefined" class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[8px] font-bold uppercase tracking-widest" x-text="getComplianceLabel(answers[item.id])"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i x-show="answers[item.id] !== undefined" class="fa-solid fa-circle-check text-blue-500 text-sm"></i>
                                        <div class="w-7 h-7 rounded-full border border-slate-100 flex items-center justify-center text-slate-400 group-hover:border-blue-200 group-hover:text-blue-600 transition-all">
                                            <i class="fa-solid transition-transform duration-300 text-xs" :class="openItems[item.id] ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div x-show="openItems[item.id]" x-collapse>
                                    <div class="p-5 border-t border-slate-100">
                                        <div class="grid lg:grid-cols-2 gap-6">
                                            <div class="space-y-4">
                                                <div class="space-y-2">
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Requirement') }}</p>
                                                    <div class="p-4 bg-slate-50 rounded-xl text-xs leading-relaxed font-medium text-slate-600 border border-slate-100" x-text="item.description"></div>
                                                </div>

                                                <template x-if="item.implementation_guidance">
                                                    <div class="space-y-2">
                                                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">{{ __('Implementation Roadmap') }}</p>
                                                        <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100 text-[11px] text-blue-900 leading-relaxed font-bold italic" x-text="item.implementation_guidance"></div>
                                                    </div>
                                                </template>
                                                
                                                <template x-if="item.questions">
                                                    <div class="space-y-2">
                                                        <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">{{ __('Guiding Questions') }}</p>
                                                        <div class="space-y-1.5">
                                                            <template x-for="q in (Array.isArray(item.questions) ? item.questions : [item.questions])">
                                                                <div class="flex items-start gap-3 p-3 bg-blue-50/30 border border-blue-100/50 rounded-lg">
                                                                    <i class="fa-solid fa-circle-question text-blue-600 mt-0.5 text-[10px] shrink-0"></i>
                                                                    <p class="text-xs font-bold text-blue-950 leading-relaxed" x-text="q"></p>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <div class="space-y-4">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Maturity Assessment') }}</p>
                                                
                                                {{-- Maturity Scale Labels --}}
                                                <div class="grid grid-cols-6 gap-2">
                                                    <template x-for="val in [0,1,2,3,4,5]">
                                                        <button @click="saveAnswer(item.id, val)" 
                                                            :class="answers[item.id] === val 
                                                                ? 'bg-blue-600 text-white border-blue-600 shadow-lg scale-105 ring-2 ring-offset-1 ring-blue-500 z-10' 
                                                                : 'bg-white text-slate-300 border-slate-200 opacity-50 hover:opacity-100 hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50'"
                                                            class="h-10 rounded-lg border-2 flex items-center justify-center font-bold transition-all duration-200 text-sm">
                                                            <span x-text="val"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                                
                                                {{-- Scale Description --}}
                                                <div class="grid grid-cols-6 gap-2 -mt-1">
                                                    <template x-for="(label, idx) in ['Non-existent', 'Initial', 'Limited/Repeatable', 'Defined', 'Managed', 'Optimized']">
                                                        <p class="text-center text-[8px] font-bold uppercase tracking-wide leading-tight"
                                                           :class="{
                                                               'text-red-400': idx === 0,
                                                               'text-orange-400': idx === 1,
                                                               'text-yellow-500': idx === 2,
                                                               'text-lime-500': idx === 3,
                                                               'text-emerald-500': idx === 4,
                                                               'text-blue-500': idx === 5
                                                           }"
                                                           x-text="label"></p>
                                                    </template>
                                                </div>
                                                
                                                {{-- Notes --}}
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">{{ __('Evidence / Notes') }}</p>
                                                    <textarea placeholder="{{ __('Write implementation evidence or notes here...') }}" 
                                                        x-model="notes[item.id]" @input="saveNotes(item.id, $event.target.value)"
                                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs font-medium outline-none focus:bg-white focus:border-blue-600 transition-all min-h-[90px] resize-y"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <div class="h-10"></div>
            </main>
        </div>

        {{-- Floating Insight Widget - Enhanced Dashboard --}}
        <div x-data="{ open: true }" class="fixed bottom-6 left-6 z-[100] transition-all duration-500 hidden lg:block" :class="open ? 'w-72' : 'w-14 h-14'">
            <button @click="open = !open" class="absolute -top-2.5 -right-2.5 w-7 h-7 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-500 shadow-lg z-10 hover:text-blue-600 transition-colors">
                <i class="fa-solid text-[10px]" :class="open ? 'fa-chevron-down' : 'fa-chart-pie'"></i>
            </button>
            <div x-show="open" x-collapse>
                <div class="bg-slate-900 rounded-2xl p-4 text-white shadow-2xl relative overflow-hidden border border-white/10">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-600/10 rounded-full blur-xl"></div>
                    
                    {{-- Maturity Score --}}
                    <h4 class="text-[9px] font-bold text-blue-400 uppercase tracking-widest mb-2">{{ __('Current Maturity') }}</h4>
                    <div class="flex items-end gap-2 mb-1">
                        <p class="text-3xl font-bold tracking-tight" x-text="avgMaturity"></p>
                        <p class="text-[9px] font-bold text-blue-300 uppercase mb-1">{{ __('/ 5.0') }}</p>
                    </div>
                    <p class="text-[9px] text-slate-400 font-bold leading-relaxed mb-3" x-text="statusText"></p>
                    <div class="w-full h-1 bg-white/10 rounded-full overflow-hidden mb-4">
                        <div class="h-full bg-blue-500 transition-all duration-1000" :style="'width: ' + (avgMaturity / 5 * 100) + '%'"></div>
                    </div>
                    
                    {{-- Compliance Summary --}}
                    <div class="border-t border-white/10 pt-3 space-y-2">
                        <h4 class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest">{{ __('Compliance Status') }}</h4>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">{{ __('Compliant') }}</span>
                            <span class="text-base font-bold text-emerald-400" x-text="compliancePercentage + '%'"></span>
                        </div>
                        
                        <div class="w-full h-1 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 transition-all duration-1000" :style="'width: ' + compliancePercentage + '%'"></div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-1.5 mt-3">
                            <div class="bg-white/5 rounded-lg p-2 text-center">
                                <p class="text-xs font-bold" x-text="compliantCount"></p>
                                <p class="text-[8px] text-slate-400 uppercase font-bold">{{ __('Compliant') }}</p>
                            </div>
                            <div class="bg-white/5 rounded-lg p-2 text-center">
                                <p class="text-xs font-bold text-amber-400" x-text="partialCount"></p>
                                <p class="text-[8px] text-slate-400 uppercase font-bold">{{ __('Partially Compliant') }}</p>
                            </div>
                            <div class="bg-white/5 rounded-lg p-2 text-center">
                                <p class="text-xs font-bold text-red-400" x-text="nonCompliantCount"></p>
                                <p class="text-[8px] text-slate-400 uppercase font-bold">{{ __('Non-Compliant') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div x-show="!open" class="w-14 h-14 bg-slate-900 rounded-xl flex items-center justify-center text-blue-400 shadow-2xl cursor-pointer" @click="open = true">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>

        {{-- Notification --}}
        <div x-show="showToast" x-transition x-cloak class="fixed top-24 left-1/2 -translate-x-1/2 bg-blue-600 text-white px-8 py-3 rounded-2xl shadow-2xl z-[200] font-bold text-[10px] uppercase tracking-widest">
            <i class="fa-solid fa-check mr-2"></i> <span x-text="toastMessage"></span>
        </div>

        {{-- Keyboard Shortcuts Hint --}}
        <div x-show="showKeyboardHint" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             x-cloak
             class="fixed bottom-6 right-6 bg-slate-900/90 backdrop-blur-sm text-white px-4 py-2 rounded-xl shadow-2xl z-[500] font-bold text-[10px] uppercase tracking-widest flex items-center gap-2 border border-white/10">
            <i class="fa-solid fa-keyboard text-blue-400"></i>
            <span x-text="keyboardHintText"></span>
        </div>

        {{-- Warning Modal --}}
        <div x-show="showWarningModal" 
             x-cloak
             class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[300] p-4">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" 
                 @click.away="closeWarningModal()">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fa-solid fa-triangle-exclamation text-white text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-center mb-2 text-slate-900">Important: Data Storage Notice</h3>
                <p class="text-xs text-slate-600 text-center mb-4 leading-relaxed">{{ __('Your assessment data is stored') }}<strong>locally in your browser</strong>. 
                    Please export your results regularly to avoid data loss.
                </p>
                <div class="space-y-2 mb-5 bg-slate-50 rounded-xl p-4">
                    <div class="flex items-start gap-3 text-xs">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-check text-green-600 text-[9px]"></i>
                        </div>
                        <span class="text-slate-700 font-medium">{{ __('Data is saved automatically as you work') }}</span>
                    </div>
                    <div class="flex items-start gap-3 text-xs">
                        <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-xmark text-red-600 text-[9px]"></i>
                        </div>
                        <span class="text-slate-700 font-medium">{{ __('Clearing browser cache will delete your progress') }}</span>
                    </div>
                    <div class="flex items-start gap-3 text-xs">
                        <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-download text-blue-600 text-[9px]"></i>
                        </div>
                        <span class="text-slate-700 font-medium">{{ __('Export to PDF or JSON to save permanently') }}</span>
                    </div>
                </div>
                <button @click="closeWarningModal()" 
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-xl font-bold hover:shadow-xl transition-all text-xs uppercase tracking-widest">{{ __('I Understand') }}</button>
                <p class="text-[10px] text-center text-slate-400 mt-3">
                    <i class="fa-solid fa-info-circle mr-1"></i>{{ __('This message will only show once') }}</p>
            </div>
        </div>

        {{-- Onboarding Tour Modal --}}
        <div x-show="showOnboarding" 
             x-cloak
             class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[300] p-4">
            <div class="bg-white rounded-2xl p-6 max-w-xl w-full shadow-2xl max-h-[88vh] overflow-y-auto onboarding-modal">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-rocket text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">{{ __('Quick Tour') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Learn how to use this tool') }}</p>
                        </div>
                    </div>
                    <button @click="closeOnboarding()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors">
                        <i class="fa-solid fa-times text-sm"></i>
                    </button>
                </div>

                {{-- Tour Steps --}}
                <div class="space-y-2.5 mb-5">
                    <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="w-7 h-7 bg-blue-600 text-white rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0">1</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm mb-0.5">{{ __('Navigate Controls') }}</h4>
                            <p class="text-xs text-slate-600">{{ __('Use the sidebar to browse ISO 27001:2022 controls. Click any control to expand and assess it.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-purple-50 rounded-xl border border-purple-100">
                        <div class="w-7 h-7 bg-purple-600 text-white rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0">2</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm mb-0.5">{{ __('Score Maturity') }}</h4>
                            <p class="text-xs text-slate-600">Rate each control from 0â€“5 based on your implementation level. Add notes for evidence.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                        <div class="w-7 h-7 bg-emerald-600 text-white rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0">3</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm mb-0.5">Search & Filter</h4>
                            <p class="text-xs text-slate-600">{{ __('Use the search box and filter buttons (All/Done/Todo) to quickly find specific controls.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="w-7 h-7 bg-amber-600 text-white rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0">4</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm mb-0.5">{{ __('Track Progress') }}</h4>
                            <p class="text-xs text-slate-600">{{ __('Check the floating dashboard (bottom-left) for real-time maturity scores and compliance metrics.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-rose-50 rounded-xl border border-rose-100">
                        <div class="w-7 h-7 bg-rose-600 text-white rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0">5</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm mb-0.5">{{ __('Export Results') }}</h4>
                            <p class="text-xs text-slate-600">Click "Export" to download as PDF or JSON. You can import JSON later to continue.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <div class="w-7 h-7 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0">âŒ¨</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm mb-1">{{ __('Keyboard Shortcuts') }}</h4>
                            <div class="text-xs text-slate-600 space-y-1">
                                <p><kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">H</kbd> Help &nbsp;â€¢&nbsp; <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">/</kbd> Search &nbsp;â€¢&nbsp; <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">{{ __('Esc') }}</kbd>{{ __('Close') }}</p>
                                <p><kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">1</kbd> All &nbsp;â€¢&nbsp; <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">2</kbd> Done &nbsp;â€¢&nbsp; <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">3</kbd>{{ __('Todo') }}</p>
                                <p><kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">Ctrl+E</kbd> PDF &nbsp;â€¢&nbsp; <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-[10px]">Ctrl+S</kbd>{{ __('JSON') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="closeOnboarding()" 
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-xl font-bold hover:shadow-xl transition-all text-xs uppercase tracking-widest">
                    <i class="fa-solid fa-check mr-2"></i>Got It, Let's Start!
                </button>

                <div class="mt-3 flex items-center justify-center gap-2">
                    <input type="checkbox" 
                           id="dontShowAgain" 
                           @change="if($event.target.checked) localStorage.setItem('guest_onboarding_shown', 'true')"
                           class="w-3.5 h-3.5 text-blue-600 rounded">
                    <label for="dontShowAgain" class="text-[10px] text-slate-500 cursor-pointer">Don't show this again</label>
                </div>
            </div>
        </div>
    </div>

    <script>
        function guestAudit() {
            return {
                standards: {!! json_encode($standards) !!},
                answers: {},
                notes: {},
                openItems: {},
                showToast: false,
                toastMessage: 'Progress Successfully Saved Locally!',
                showWarningModal: false,
                showOnboarding: false,
                searchQuery: '',
                filterMode: 'all',
                filteredStandards: [],
                mobileMenuOpen: false,
                showKeyboardHint: false,
                keyboardHintText: '',
                isExporting: false,
                
                init() {
                    this.filteredStandards = this.standards;
                    const savedAnswers = localStorage.getItem('guest_answers');
                    const savedNotes = localStorage.getItem('guest_notes');
                    if (savedAnswers) this.answers = JSON.parse(savedAnswers);
                    if (savedNotes) this.notes = JSON.parse(savedNotes);
                    
                    // Show warning modal on first visit
                    if (!localStorage.getItem('guest_warning_shown')) {
                        setTimeout(() => {
                            this.showWarningModal = true;
                        }, 500);
                    }
                    
                    // Default: Open the first item to give context
                    const firstActionable = this.standards.find(item => item.description || item.questions);
                    if (firstActionable) {
                        this.openItems[firstActionable.id] = true;
                    }
                },

                closeWarningModal() {
                    this.showWarningModal = false;
                    localStorage.setItem('guest_warning_shown', 'true');
                    
                    // Show onboarding after warning modal if not shown before
                    if (!localStorage.getItem('guest_onboarding_shown')) {
                        setTimeout(() => {
                            this.showOnboarding = true;
                        }, 500);
                    }
                },

                closeOnboarding() {
                    this.showOnboarding = false;
                },

                filterStandards() {
                    let filtered = this.standards;
                    
                    // Apply search filter
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        // Get matching actionable items
                        const matchingIds = new Set(
                            this.standards
                                .filter(item => (item.description || item.questions) && (
                                    item.code.toLowerCase().includes(query) ||
                                    item.title.toLowerCase().includes(query) ||
                                    (item.description && item.description.toLowerCase().includes(query))
                                ))
                                .map(item => item.id)
                        );
                        
                        // Keep section headers that have matching children
                        filtered = filtered.filter(item => {
                            if (!item.description && !item.questions) {
                                // It's a section header â€” keep if any child matches
                                const sectionIdx = this.standards.indexOf(item);
                                for (let i = sectionIdx + 1; i < this.standards.length; i++) {
                                    const next = this.standards[i];
                                    if (!next.description && !next.questions) break; // next section
                                    if (matchingIds.has(next.id)) return true;
                                }
                                return false;
                            }
                            return matchingIds.has(item.id);
                        });
                    }
                    
                    // Apply status filter
                    if (this.filterMode === 'answered') {
                        filtered = filtered.filter(item => 
                            (!item.description && !item.questions) || // keep section headers
                            this.answers[item.id] !== undefined
                        );
                    } else if (this.filterMode === 'unanswered') {
                        filtered = filtered.filter(item => 
                            (!item.description && !item.questions) || // keep section headers
                            this.answers[item.id] === undefined
                        );
                    }
                    
                    this.filteredStandards = filtered;
                },

                saveAnswer(id, val) {
                    this.answers[id] = val;
                    localStorage.setItem('guest_answers', JSON.stringify(this.answers));
                    this.triggerToast();
                },

                getComplianceLabel(score) {
                    const labels = {
                        0: 'Non-existent',
                        1: 'Initial',
                        2: 'Limited/Repeatable',
                        3: 'Defined',
                        4: 'Managed',
                        5: 'Optimized'
                    };
                    return labels[score] || 'Unknown';
                },

                saveNotes(id, val) {
                    this.notes[id] = val;
                    localStorage.setItem('guest_notes', JSON.stringify(this.notes));
                },

                triggerToast(message = 'Progress Successfully Saved Locally!') {
                    this.toastMessage = message;
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 3000);
                },

                scrollToItem(id) {
                    const el = document.getElementById('item-' + id);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                },

                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                expandAll() {
                    this.standards.forEach(item => {
                        if (item.description || item.questions) {
                            this.openItems[item.id] = true;
                        }
                    });
                },

                collapseAll() {
                    this.openItems = {};
                },

                handleKeyboard(event) {
                    // Ignore if user is typing in input/textarea
                    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
                        return;
                    }

                    const key = event.key.toLowerCase();
                    
                    // Export shortcuts
                    if (event.ctrlKey && key === 'e') {
                        event.preventDefault();
                        this.exportToPdf();
                        this.showKeyboardHintMessage('PDF Exported', 'Ctrl+E');
                        return;
                    }
                    
                    if (event.ctrlKey && key === 's') {
                        event.preventDefault();
                        this.exportToJson();
                        this.showKeyboardHintMessage('JSON Exported', 'Ctrl+S');
                        return;
                    }
                    
                    // Navigation shortcuts
                    if (key === 'h') {
                        event.preventDefault();
                        this.showOnboarding = true;
                        this.showKeyboardHintMessage('Help Opened', 'H');
                        return;
                    }
                    
                    if (key === '/') {
                        event.preventDefault();
                        const searchInput = document.querySelector('input[x-model="searchQuery"]');
                        if (searchInput) {
                            searchInput.focus();
                            this.showKeyboardHintMessage('Search Focus', '/');
                        }
                        return;
                    }
                    
                    if (key === 'escape') {
                        this.mobileMenuOpen = false;
                        this.showOnboarding = false;
                        this.showWarningModal = false;
                        this.showKeyboardHintMessage('Closed', 'Esc');
                        return;
                    }
                    
                    // Filter shortcuts
                    if (key === '1') {
                        event.preventDefault();
                        this.filterMode = 'all';
                        this.filterStandards();
                        this.showKeyboardHintMessage('Show All', '1');
                        return;
                    }
                    
                    if (key === '2') {
                        event.preventDefault();
                        this.filterMode = 'answered';
                        this.filterStandards();
                        this.showKeyboardHintMessage('Show Done', '2');
                        return;
                    }
                    
                    if (key === '3') {
                        event.preventDefault();
                        this.filterMode = 'unanswered';
                        this.filterStandards();
                        this.showKeyboardHintMessage('Show Todo', '3');
                        return;
                    }
                },

                showKeyboardHintMessage(action, key) {
                    this.keyboardHintText = `${action} (${key})`;
                    this.showKeyboardHint = true;
                    setTimeout(() => {
                        this.showKeyboardHint = false;
                    }, 2000);
                },

                get progressPercentage() {
                    const total = this.standards.filter(i => i.description || i.questions).length;
                    const answered = Object.keys(this.answers).length;
                    return total > 0 ? Math.round((answered / total) * 100) : 0;
                },

                get avgMaturity() {
                    const vals = Object.values(this.answers);
                    if (vals.length === 0) return "0.0";
                    const sum = vals.reduce((a, b) => a + b, 0);
                    return (sum / vals.length).toFixed(1);
                },

                get statusText() {
                    const avg = parseFloat(this.avgMaturity);
                    if (avg >= 4) return "Strategic & Optimized";
                    if (avg >= 3) return "Fully Documented";
                    if (avg >= 2) return "Partial Implementation";
                    if (avg >= 1) return "Initial / Ad-hoc";
                    return "No Implementation Yet";
                },

                // Compliance metrics
                get compliantCount() {
                    return Object.values(this.answers).filter(score => score >= 4).length;
                },

                get partialCount() {
                    return Object.values(this.answers).filter(score => score >= 2 && score < 4).length;
                },

                get nonCompliantCount() {
                    return Object.values(this.answers).filter(score => score < 2).length;
                },

                get compliancePercentage() {
                    const total = Object.keys(this.answers).length;
                    if (total === 0) return 0;
                    return Math.round((this.compliantCount / total) * 100);
                },

                exportToJson() {
                    const data = {
                        app: 'ISO 27001:2022 Self-Assessment',
                        mode: 'Guest',
                        version: '1.0',
                        exported_at: new Date().toISOString(),
                        answers: this.answers,
                        notes: this.notes,
                        summary: {
                            avg_maturity: this.avgMaturity,
                            progress: this.progressPercentage,
                            status: this.statusText,
                            total_controls: this.standards.filter(i => i.description || i.questions).length,
                            answered_controls: Object.keys(this.answers).length
                        }
                    };
                    
                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `ISO27001:2022_Assessment_${new Date().toISOString().slice(0,10)}.json`;
                    link.click();
                    this.triggerToast('JSON exported successfully!');
                },

                importFromJson() {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = '.json';
                    input.onchange = (e) => {
                        const file = e.target.files[0];
                        if (!file) return;
                        
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            try {
                                const data = JSON.parse(event.target.result);
                                
                                // Validate data structure
                                if (!data.answers && !data.notes) {
                                    throw new Error('Invalid file format');
                                }
                                
                                // Import data
                                if (data.answers) {
                                    this.answers = data.answers;
                                    localStorage.setItem('guest_answers', JSON.stringify(this.answers));
                                }
                                if (data.notes) {
                                    this.notes = data.notes;
                                    localStorage.setItem('guest_notes', JSON.stringify(this.notes));
                                }
                                
                                this.triggerToast('Data imported successfully!');
                                
                                // Scroll to top
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } catch (error) {
                                alert('Error: Invalid JSON file format. Please select a valid assessment export file.');
                            }
                        };
                        reader.readAsText(file);
                    };
                    input.click();
                },

                async exportToPdf() {
                    if (this.isExporting) return;
                    this.isExporting = true;
                    try {
                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF();
                        
                        // Colors
                        const primaryColor = [37, 99, 235]; // blue-600
                        const textColor = [15, 23, 42]; // slate-900
                        const grayColor = [100, 116, 139]; // slate-500
                        
                        let yPos = 20;
                        
                        // Cover Page
                        doc.setFillColor(...primaryColor);
                        doc.rect(0, 0, 210, 60, 'F');
                        
                        doc.setTextColor(255, 255, 255);
                        doc.setFontSize(28);
                        doc.setFont(undefined, 'bold');
                        doc.text('ISO 27001:2022', 105, 25, { align: 'center' });
                        doc.setFontSize(16);
                        doc.text('Self-Assessment Report', 105, 35, { align: 'center' });
                        
                        doc.setFontSize(10);
                        doc.setFont(undefined, 'normal');
                        doc.text('Guest Mode Assessment', 105, 45, { align: 'center' });
                        doc.text(`Generated: ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`, 105, 52, { align: 'center' });
                        
                        yPos = 80;
                        
                        // Executive Summary
                        doc.setTextColor(...textColor);
                        doc.setFontSize(18);
                        doc.setFont(undefined, 'bold');
                        doc.text('Executive Summary', 20, yPos);
                        yPos += 15;
                        
                        // Summary boxes
                        const summaryData = [
                            { label: 'Average Maturity', value: `${this.avgMaturity} / 5.0`, color: [37, 99, 235] },
                            { label: 'Maturity Status', value: this.statusText, color: [16, 185, 129] },
                            { label: 'Progress', value: `${this.progressPercentage}%`, color: [139, 92, 246] },
                            { label: 'Controls Assessed', value: `${Object.keys(this.answers).length} / ${this.standards.filter(i => i.description || i.questions).length}`, color: [234, 88, 12] }
                        ];
                        
                        summaryData.forEach((item, index) => {
                            const xPos = 20 + (index % 2) * 95;
                            const yBox = yPos + Math.floor(index / 2) * 35;
                            
                            doc.setFillColor(item.color[0], item.color[1], item.color[2], 0.1);
                            doc.roundedRect(xPos, yBox, 85, 28, 3, 3, 'F');
                            
                            doc.setFontSize(8);
                            doc.setFont(undefined, 'bold');
                            doc.setTextColor(...grayColor);
                            doc.text(item.label.toUpperCase(), xPos + 5, yBox + 8);
                            
                            doc.setFontSize(14);
                            doc.setFont(undefined, 'bold');
                            doc.setTextColor(...item.color);
                            doc.text(item.value, xPos + 5, yBox + 20);
                        });
                        
                        yPos += 85;
                        
                        // Compliance Distribution
                        doc.setFontSize(14);
                        doc.setFont(undefined, 'bold');
                        doc.setTextColor(...textColor);
                        doc.text('Maturity Distribution', 20, yPos);
                        yPos += 10;
                        
                        const distribution = {};
                        Object.values(this.answers).forEach(score => {
                            distribution[score] = (distribution[score] || 0) + 1;
                        });
                        
                        const maturityLabels = ['Non-existent', 'Initial', 'Limited/Repeatable', 'Defined', 'Managed', 'Optimized'];
                        const barColors = [[239, 68, 68], [251, 146, 60], [234, 179, 8], [132, 204, 22], [34, 197, 94], [16, 185, 129]];
                        
                        for (let i = 0; i <= 5; i++) {
                            const count = distribution[i] || 0;
                            const percentage = Object.keys(this.answers).length > 0 ? (count / Object.keys(this.answers).length * 100) : 0;
                            
                            doc.setFontSize(9);
                            doc.setTextColor(...grayColor);
                            doc.text(`Level ${i} - ${maturityLabels[i]}`, 20, yPos);
                            
                            doc.setFillColor(...barColors[i]);
                            doc.roundedRect(70, yPos - 4, percentage * 1.1, 6, 2, 2, 'F');
                            
                            doc.setFontSize(8);
                            doc.setFont(undefined, 'bold');
                            doc.text(`${count} (${percentage.toFixed(0)}%)`, 185, yPos, { align: 'right' });
                            
                            yPos += 10;
                        }
                        
                        // New page for detailed results
                        doc.addPage();
                        yPos = 20;
                        
                        doc.setFontSize(18);
                        doc.setFont(undefined, 'bold');
                        doc.setTextColor(...textColor);
                        doc.text('Detailed Assessment Results', 20, yPos);
                        yPos += 15;
                        
                        // Table data
                        const tableData = [];
                        this.standards.forEach(item => {
                            if (item.description || item.questions) {
                                const score = this.answers[item.id];
                                if (score !== undefined) {
                                    tableData.push([
                                        item.code,
                                        item.title.substring(0, 40) + (item.title.length > 40 ? '...' : ''),
                                        score.toString(),
                                        maturityLabels[score],
                                        this.notes[item.id] ? 'Yes' : 'No'
                                    ]);
                                }
                            }
                        });
                        
                        doc.autoTable({
                            startY: yPos,
                            head: [['Code', 'Control', 'Score', 'Level', 'Notes']],
                            body: tableData,
                            theme: 'grid',
                            headStyles: {
                                fillColor: primaryColor,
                                textColor: [255, 255, 255],
                                fontStyle: 'bold',
                                fontSize: 9
                            },
                            bodyStyles: {
                                fontSize: 8,
                                textColor: textColor
                            },
                            columnStyles: {
                                0: { cellWidth: 20 },
                                1: { cellWidth: 80 },
                                2: { cellWidth: 15, halign: 'center' },
                                3: { cellWidth: 30 },
                                4: { cellWidth: 20, halign: 'center' }
                            },
                            alternateRowStyles: {
                                fillColor: [248, 250, 252]
                            }
                        });
                        
                        // Footer on all pages
                        const pageCount = doc.internal.getNumberOfPages();
                        for (let i = 1; i <= pageCount; i++) {
                            doc.setPage(i);
                            doc.setFontSize(8);
                            doc.setTextColor(...grayColor);
                            doc.text(`ISO 27001:2022 Self-Assessment Report | Page ${i} of ${pageCount}`, 105, 285, { align: 'center' });
                            doc.text(`Generated: ${new Date().toLocaleDateString()}`, 105, 290, { align: 'center' });
                        }
                        
                        // Save PDF
                        doc.save(`ISO27001:2022_Assessment_Report_${new Date().toISOString().slice(0,10)}.pdf`);
                        this.triggerToast('PDF exported successfully!');
                        
                    } catch (error) {
                        console.error('PDF Export Error:', error);
                        alert('Error generating PDF. Please try again or export as JSON instead.');
                    } finally {
                        this.isExporting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
