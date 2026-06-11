@extends('layouts.app')
@section('title', 'Template Preview')
@section('view_name', 'Template Preview')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-20">
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('community.index') }}" class="text-slate-400 hover:text-indigo-600 transition-colors flex items-center gap-2 text-sm font-bold uppercase tracking-widest">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Back to Community') }}
        </a>
        <div class="flex items-center gap-2">
            <form action="{{ route('community.upvote', $template->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 rounded-lg text-xs font-bold uppercase tracking-widest transition-all flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-arrow-up"></i> {{ $template->upvotes }}
                </button>
            </form>
            {{-- Star Rating Form --}}
            <form action="{{ route('community.rate', $template->id) }}" method="POST" class="flex items-center gap-1 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200">
                @csrf
                <span class="text-[9px] font-black text-slate-400 uppercase mr-1">{{ __('Rate:') }}</span>
                @for($star = 1; $star <= 5; $star++)
                <label class="cursor-pointer text-amber-400 hover:scale-110 transition-transform">
                    <input type="radio" name="stars" value="{{ $star }}" class="hidden" onchange="this.form.submit()">
                    <i class="fa-{{ $star <= $template->avg_rating ? 'solid' : 'regular' }} fa-star text-sm"></i>
                </label>
                @endfor
                <span class="text-[10px] font-black text-amber-500 ml-1">{{ $template->avg_rating }}/5</span>
            </form>
            <form action="{{ route('community.clone', $template->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold uppercase tracking-widest transition-all shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i> {{ __('Clone Template') }}
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <i class="fa-solid fa-book-bookmark text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $template->title }}</h1>
                <p class="text-slate-500 font-medium flex items-center gap-2">
                    by <span class="font-bold text-slate-900">{{ $template->author_name ?? 'Anonymous' }}</span>
                    <span class="text-slate-300">&bull;</span>
                    {{ $template->downloads_count }} Downloads
                </p>
            </div>
        </div>
        
        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 text-sm text-slate-700 leading-relaxed font-medium">
            {{ $template->description }}
        </div>
    </div>

    <div class="flex items-center gap-3 mt-8 mb-4">
        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tighter">{{ __('Implemented Controls') }}</h2>
        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ count($results) }} Items</span>
    </div>

    <div class="space-y-4">
        @foreach($results as $res)
        <div class="bg-white p-6 rounded-2xl border border-slate-100 hover:border-indigo-200 transition-all shadow-sm flex flex-col md:flex-row gap-6">
            <div class="md:w-1/3">
                <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded uppercase tracking-widest mb-2 inline-block">
                    {{ $res->standard->type ?? 'Clause' }} {{ $res->standard->code }}
                </span>
                <h3 class="text-sm font-bold text-slate-900 leading-tight mb-2">{{ __($res->standard->title) }}</h3>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Maturity:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest {{ $res->maturity_rating >= 4 ? 'bg-emerald-50 text-emerald-600' : ($res->maturity_rating >= 2 ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600') }}">
                        Level {{ $res->maturity_rating }}
                    </span>
                </div>
            </div>
            <div class="md:w-2/3 md:border-l md:border-slate-100 md:pl-6">
                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5"><i class="fa-solid fa-file-shield"></i> {{ __('Reference Evidence') }}</h4>
                @if($res->notes)
                    <p class="text-xs text-slate-700 leading-relaxed font-medium italic bg-slate-50 p-4 rounded-xl border border-slate-100">"{{ $res->notes }}"</p>
                @else
                    <p class="text-xs text-slate-400 leading-relaxed italic p-4">{{ __('No evidence reference provided.') }}</p>
                @endif
            </div>
        </div>
        @endforeach

        @if(count($results) === 0)
        <div class="p-10 bg-white rounded-2xl border border-slate-100 text-center">
            <p class="text-slate-400 font-medium text-sm">{{ __('This template does not contain any completed controls.') }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
