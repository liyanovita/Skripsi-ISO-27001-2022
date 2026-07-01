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
                        ['label' => 'Avg Rating', 'value' => $stats['avg_rating'] . '<i class="fa-solid fa-star text-sm ml-1 mb-0.5"></i>', 'color' => 'text-amber-400', 'bg' => 'bg-amber-400/10'],
                    ];
                @endphp
                @foreach($displayStats as $stat)
                <div class="w-28 h-28 {{ $stat['bg'] }} backdrop-blur-xl border border-white/5 rounded-2xl flex flex-col items-center justify-center gap-1 p-4 hover:scale-105 transition-all">
                    <div class="text-xl font-black {{ $stat['color'] }} tracking-tighter flex items-center justify-center">{!! $stat['value'] !!}</div>
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
                                    <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ $item->author_name ?? $item->user->name }} &middot; {{ $item->created_at->diffForHumans() }}</p>
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
                    <form action="{{ route('community.index') }}" method="GET" class="relative w-full sm:w-64" x-data>
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                        <input type="text" name="search" id="search-input" value="{{ $search ?? '' }}" placeholder="{{ __('Search templates...') }}" 
                            x-on:input.debounce.500ms="$el.closest('form').requestSubmit()"
                            class="w-full pl-8 {{ !empty($search) ? 'pr-8' : 'pr-3' }} py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/30 transition-all placeholder:text-slate-400">
                        @if(!empty($search))
                            <a href="{{ route('community.index', array_merge(request()->except(['search', 'page']))) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fa-solid fa-circle-xmark text-xs"></i>
                            </a>
                        @endif
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
                                <a href="{{ route('community.show', $temp->id) }}" title="{{ __('View Details') }}" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-blue-100 text-slate-400 hover:text-blue-600 rounded-md transition-all">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                </a>
                                {{-- Clone button if it has JSON content --}}
                                @if($temp->has_content)
                                <form action="{{ route('community.clone', $temp->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" @if($loop->first) id="btn-clone-first" @endif title="{{ __('Clone as new session') }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white rounded-lg font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-cloud-arrow-down text-[10px]"></i>
                                        <span>{{ __('Clone') }}</span>
                                    </button>
                                </form>
                                @endif
                                {{-- Download button if it has attachment --}}
                                @if($temp->has_attachment)
                                <a href="{{ route('community.attachment', ['id' => $temp->id, 'download' => 1]) }}" title="{{ __('Download') }}" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-emerald-100 text-slate-400 hover:text-emerald-600 rounded-md transition-all">
                                    <i class="fa-solid fa-download text-[10px]"></i>
                                </a>
                                @endif
                                
                                @if(auth()->id() === $temp->user_id || auth()->user()->is_admin)
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" @click.away="open = false" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 rounded-md transition-all">
                                        <i class="fa-solid fa-ellipsis-vertical text-[10px]"></i>
                                    </button>
                                    <div x-show="open" style="display: none;" class="absolute right-0 bottom-full mb-2 w-32 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden z-20">
                                        <a href="{{ route('community.edit', $temp->id) }}" class="block px-4 py-2 text-[10px] font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                            <i class="fa-solid fa-pen-to-square mr-2"></i> {{ __('Edit') }}
                                        </a>
                                        <form action="{{ route('community.destroy', $temp->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this asset?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-4 py-2 text-[10px] font-bold text-slate-600 hover:bg-red-50 hover:text-red-600 transition-colors">
                                                <i class="fa-solid fa-trash mr-2"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endif
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
                    <a href="{{ route('community.create') }}"
                        class="w-full flex items-center justify-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md">
                        <i class="fa-solid fa-cloud-arrow-up"></i> {{ __('Submit Asset') }}
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4">{{ __('Contribution Protocol') }}</h3>
                <div class="space-y-3">
                    @php
                        $guide = [
                            ['step' => 1, 'title' => 'Design & Test', 'desc' => 'Develop blueprints in your local sandbox.', 'color' => 'blue'],
                            ['step' => 2, 'title' => 'Submit PR', 'desc' => 'Upload your contribution document.', 'color' => 'purple'],
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
</div>
@endsection
