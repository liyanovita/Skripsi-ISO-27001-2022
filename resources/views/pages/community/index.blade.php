@extends('layouts.app')
@section('title', 'Community Hub')
@section('view_name', 'Community Hub')

@section('content')
<div class="max-w-6xl mx-auto space-y-4 pb-8" x-data="{ showUploadModal: false }">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-users text-lg"></i>
            </div>
            <div class="leading-none">
                <h1 class="text-xl font-black text-slate-900 tracking-tighter uppercase">{{ __('Community Hub') }}</h1>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[8px] mt-0.5">{{ __('Global Collaboration & Shared Templates') }}</p>
            </div>
        </div>
    </div>

    {{-- Open Source Banner --}}
    <div class="group relative bg-slate-900 rounded-2xl p-8 overflow-hidden shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-600/30 via-blue-600/30 to-emerald-600/30 opacity-60"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="max-w-2xl">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-white/10 backdrop-blur-xl rounded-xl flex items-center justify-center border border-white/10 shadow-xl">
                        <i class="fa-brands fa-github text-2xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-white tracking-tighter uppercase">{{ __('Open Collaboration') }}</h2>
                        <p class="text-blue-200/60 font-bold uppercase tracking-widest text-[9px] mt-0.5">{{ __('100% Open Source Audit Framework') }}</p>
                    </div>
                </div>
                <p class="text-sm text-white font-medium leading-relaxed mb-6">
                    {{ __('Contribute templates, share governance policies, and help redefine global ISO 27001:2022 compliance for the digital era.') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="#" class="px-5 py-2.5 bg-white text-slate-900 rounded-xl text-[9px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-xl flex items-center gap-2">
                        <i class="fa-brands fa-github"></i> {{ __('View on GitHub') }}
                    </a>
                    <button class="px-5 py-2.5 bg-white/10 backdrop-blur-xl text-white border border-white/10 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-code-fork"></i> {{ __('Fork Repository') }}
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @php
                    $displayStats = [
                        ['label' => 'Downloads', 'value' => number_format($stats['total_downloads']), 'color' => 'text-blue-400', 'bg' => 'bg-blue-400/10'],
                        ['label' => 'Contributors', 'value' => $stats['total_contributors'], 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-400/10'],
                        ['label' => 'Assets', 'value' => $stats['total_templates'], 'color' => 'text-purple-400', 'bg' => 'bg-purple-400/10'],
                        ['label' => 'Avg Rating', 'value' => $stats['avg_rating'] . 'â˜…', 'color' => 'text-amber-400', 'bg' => 'bg-amber-400/10'],
                    ];
                @endphp
                @foreach($displayStats as $stat)
                <div class="w-28 h-28 {{ $stat['bg'] }} backdrop-blur-xl border border-white/5 rounded-2xl flex flex-col items-center justify-center gap-1 p-4 hover:scale-105 transition-all">
                    <div class="text-xl font-black {{ $stat['color'] }} tracking-tighter">{{ $stat['value'] }}</div>
                    <div class="text-[8px] font-bold text-white/40 uppercase tracking-widest text-center">{{ __($stat['label']) }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- Main Content --}}
        <div class="lg:col-span-8 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Top Contributors --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center border border-slate-100">
                            <i class="fa-solid fa-users text-slate-700 text-sm"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">{{ __('Top Contributors') }}</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($topContributors as $cont)
                        <div class="flex items-center gap-3 group">
                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center text-white font-black text-xs shadow-md group-hover:scale-110 transition-transform">
                                {{ $cont['initials'] }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ $cont['name'] }}</h4>
                                <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">{{ $cont['role'] }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-black text-slate-900">{{ $cont['count'] }}</div>
                                <div class="text-[7px] text-slate-400 font-bold uppercase">{{ __('Commits') }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center border border-slate-100">
                            <i class="fa-solid fa-code-pull-request text-slate-700 text-sm"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">{{ __('Live Activity') }}</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentActivity as $item)
                        <div class="relative pl-4 before:content-[''] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-0.5 before:bg-emerald-500 before:rounded-full">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 tracking-tight">{{ $item->title }}</h4>
                                    <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ $item->author_name ?? $item->user->name }} Â· {{ $item->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[7px] font-black uppercase border border-blue-100 shrink-0">{{ count($item->tags ?? []) > 0 ? $item->tags[0] : 'Asset' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Popular Templates --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center border border-slate-100">
                            <i class="fa-solid fa-star text-slate-700 text-sm"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">{{ __('Popular Intelligence Assets') }}</h3>
                    </div>
                    
                    {{-- Search Form --}}
                    <form action="{{ route('community.index') }}" method="GET" class="relative w-full sm:w-64">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                        <input type="text" name="search" id="search-input" value="{{ $search ?? '' }}" placeholder="{{ __('Search templates...') }}" 
                            class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/30 transition-all placeholder:text-slate-400">
                    </form>
                </div>
                <div id="community-template-grid" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @forelse($templates as $temp)
                    <div class="p-5 bg-white rounded-2xl border border-slate-100 hover:border-indigo-300 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-full blur-2xl -mr-10 -mt-10 transition-all duration-500 group-hover:scale-150"></div>
                        <div class="relative z-10">
                            <h4 class="text-xs font-bold text-slate-900 tracking-tight mb-2 group-hover:text-indigo-600 transition-colors">{{ $temp->name }}</h4>
                            <div class="flex items-center gap-1.5 mb-3">
                                @php
                                    $fullStars = (int)floor($temp->rating);
                                    $hasHalf = ($temp->rating - $fullStars) >= 0.5;
                                @endphp
                                @for($s = 1; $s <= 5; $s++)
                                    @if($s <= $fullStars)
                                        <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                    @elseif($s == $fullStars + 1 && $hasHalf)
                                        <i class="fa-solid fa-star-half-stroke text-amber-400 text-[10px]"></i>
                                    @else
                                        <i class="fa-regular fa-star text-slate-300 text-[10px]"></i>
                                    @endif
                                @endfor
                                <span class="text-amber-500 text-[10px] font-black ml-0.5">{{ number_format($temp->rating, 1) }}</span>
                                <span class="text-[9px] text-slate-400">({{ $temp->rating_count }})</span>
                            </div>

                            {{-- Stats row --}}
                            <div class="flex items-center gap-3 mb-3">
                                <span class="flex items-center gap-1 text-[9px] text-slate-400 font-bold">
                                    <i class="fa-solid fa-arrow-up text-indigo-400"></i>
                                    {{ number_format($temp->upvotes) }}
                                </span>
                                <span class="flex items-center gap-1 text-[9px] text-slate-400 font-bold">
                                    <i class="fa-solid fa-cloud-arrow-down text-slate-400"></i>
                                    {{ number_format($temp->downloads) }}
                                </span>
                            </div>
                            
                            @if(count($temp->tags) > 0)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach(array_slice($temp->tags, 0, 2) as $tag)
                                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[8px] font-bold uppercase tracking-widest rounded">{{ $tag }}</span>
                                @endforeach
                                @if(count($temp->tags) > 2)
                                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[8px] font-bold uppercase tracking-widest rounded">+{{ count($temp->tags) - 2 }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        <div class="pt-4 mt-2 border-t border-slate-100 flex items-center justify-between relative z-10">
                            <span class="text-[9px] text-slate-400 font-bold uppercase truncate max-w-[80px]">by {{ $temp->author }}</span>
                            <div class="flex items-center gap-1">
                                {{-- Upvote button with count --}}
                                <form action="{{ route('community.upvote', $temp->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="{{ __('Upvote') }}" class="flex items-center gap-1 px-2 py-1 bg-slate-100 hover:bg-indigo-100 text-slate-500 hover:text-indigo-600 rounded-md transition-all">
                                        <i class="fa-solid fa-arrow-up text-[10px]"></i>
                                        <span class="text-[9px] font-black">{{ $temp->upvotes }}</span>
                                    </button>
                                </form>
                                {{-- Preview button --}}
                                <a href="{{ route('community.preview', $temp->id) }}" title="{{ __('Preview') }}" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-blue-100 text-slate-400 hover:text-blue-600 rounded-md transition-all">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                </a>
                                {{-- Clone button with label --}}
                                <form action="{{ route('community.clone', $temp->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" @if($loop->first) id="btn-clone-first" @endif title="{{ __('Clone as new session') }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white rounded-lg font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-cloud-arrow-down text-[10px]"></i>
                                        <span>{{ __('Clone') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-16 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <i class="fa-solid fa-search text-3xl text-slate-300 mb-3 block"></i>
                        <h3 class="text-slate-900 font-bold text-sm tracking-tight">{{ __('No Templates Found') }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('Try adjusting your search query') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4">{{ __('Quick Actions') }}</h3>
                <div class="space-y-2">
                    <button @click="showUploadModal = true"
                        class="w-full flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md">
                        <i class="fa-solid fa-cloud-arrow-up"></i> {{ __('Submit Template') }}
                    </button>
                    <a href="{{ route('sessions.index') }}"
                        class="w-full flex items-center gap-3 px-4 py-3 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                        <i class="fa-solid fa-file-import text-slate-500"></i> {{ __('Import JSON Session') }}
                    </a>
                    <a href="{{ route('sessions.index') }}"
                        class="w-full flex items-center gap-3 px-4 py-3 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                        <i class="fa-solid fa-download text-slate-500"></i> {{ __('Export Session JSON') }}
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4">{{ __('Contribution Protocol') }}</h3>
                <div class="space-y-3">
                    @php
                        $guide = [
                            ['step' => 1, 'title' => 'Design & Test', 'desc' => 'Develop blueprints in your local sandbox.', 'color' => 'blue'],
                            ['step' => 2, 'title' => 'Submit PR', 'desc' => 'Fork and submit your contribution.', 'color' => 'purple'],
                            ['step' => 3, 'title' => 'Review & Merge', 'desc' => 'Community peer-review process.', 'color' => 'emerald'],
                        ];
                    @endphp
                    @foreach($guide as $g)
                    <div class="flex gap-3">
                        <div class="w-7 h-7 bg-{{ $g['color'] }}-50 text-{{ $g['color'] }}-600 rounded-lg flex items-center justify-center text-xs font-black flex-shrink-0 border border-{{ $g['color'] }}-100">
                            {{ $g['step'] }}
                        </div>
                        <div>
                            <h4 class="text-[10px] font-bold text-slate-900 uppercase tracking-widest">{{ $g['title'] }}</h4>
                            <p class="text-xs text-slate-400 font-medium leading-snug mt-0.5">{{ $g['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===== UPLOAD MODAL ===== --}}
    <div x-show="showUploadModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="showUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div x-show="showUploadModal" @click.away="showUploadModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-8 border border-slate-100 relative z-10">
                <div class="absolute top-0 right-0 pt-6 pr-6">
                    <button @click="showUploadModal = false" type="button" class="text-slate-400 bg-slate-50 rounded-xl p-2 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        <span class="sr-only">{{ __('Close') }}</span>
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div class="sm:flex sm:items-start mb-6">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-indigo-50 rounded-xl sm:mx-0 sm:h-12 sm:w-12 border border-indigo-100">
                        <i class="fa-solid fa-cloud-arrow-up text-indigo-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase" id="modal-title">{{ __('Submit Intelligence Asset') }}</h3>
                        <div class="mt-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Share your ISO 27001:2022 configurations to the global network.') }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('community.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label for="title" class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-2">{{ __('Asset Title') }}</label>
                        <input type="text" name="title" id="title" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all shadow-inner" placeholder="{{ __('e.g. Fintech Cloud Policy Blueprint') }}">
                    </div>
                    
                    <div>
                        <label for="description" class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-2">{{ __('Description') }}</label>
                        <textarea name="description" id="description" rows="3" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-medium text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all shadow-inner" placeholder="{{ __('Describe the use-cases and coverage of this template...') }}"></textarea>
                    </div>

                    <div>
                        <label for="tags" class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-2">{{ __('Tags (Comma Separated)') }}</label>
                        <input type="text" name="tags" id="tags" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 outline-none focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-600/5 transition-all shadow-inner" placeholder="{{ __('e.g. Fintech, AWS, Remote Work') }}">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-widest mb-2">{{ __('Payload File (JSON)') }}</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-indigo-400 hover:bg-indigo-50/50 transition-all cursor-pointer relative group">
                            <div class="space-y-2 text-center relative z-10 pointer-events-none">
                                <i class="fa-solid fa-file-code text-3xl text-slate-300 group-hover:text-indigo-400 transition-colors"></i>
                                <div class="flex text-xs text-slate-600 justify-center">
                                    <span class="relative font-bold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>{{ __('Upload a file') }}</span>
                                    </span>
                                    <p class="pl-1">{{ __('or drag and drop') }}</p>
                                </div>
                                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">{{ __('JSON format only') }}</p>
                            </div>
                            <input id="json_file" name="json_file" type="file" required accept=".json" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        </div>
                    </div>

                    <div class="mt-6 sm:mt-8 sm:flex sm:flex-row-reverse border-t border-slate-100 pt-6">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-indigo-600 text-xs font-black uppercase tracking-widest text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-all">{{ __('Submit Asset') }}</button>
                        <button @click="showUploadModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-200 shadow-sm px-6 py-3 bg-white text-xs font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
