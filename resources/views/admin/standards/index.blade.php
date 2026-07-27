@extends('layouts.admin')

@section('title', 'ISO 27001:2022 Standards')
@section('header_title', 'ISO 27001:2022 Standards')

@section('content')
<style>
    .standard-tab-active {
        background: white;
        color: #2563eb;
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.06);
        border-color: #e2e8f0;
    }
</style>

<div class="space-y-6" x-data="{ showImport: false, tab: 'clauses', search: '' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
                ISO 27001:2022 Standards
            </h2>
            <p class="text-sm text-slate-500 mt-0.5 ml-9">Manage clauses, Annex A controls, implementation guidance, and audit questions.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('admin.standards.export') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-download text-slate-400"></i> Export CSV
            </a>
            <button type="button" @click="showImport = true"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-file-import text-blue-500"></i> Import CSV
            </button>
            <a href="{{ route('admin.standards.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20">
                <i class="fa-solid fa-plus text-xs"></i> Add Standard
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Total Items') }}</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-list-check text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-blue-600 tracking-tight">{{ number_format($totalItems) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">{{ __('Total Catalog Elements') }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Total Questions') }}</span>
                <div class="w-8 h-8 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                    <i class="fa-solid fa-circle-question text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-violet-600 tracking-tight">{{ number_format($totalQuestions) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">{{ __('Audit Assessment Criteria') }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Main Clauses</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-folder-tree text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-indigo-600 tracking-tight">{{ $clauses->count() }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Clauses 4 – 10 Framework</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Annex A Domains</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-emerald-600 tracking-tight">{{ $controls->count() }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">A.5, A.6, A.7, A.8 Domains</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Standard Edition</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-award text-xs"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">ISO 27001:2022</div>
            <div class="text-[10px] text-slate-400 mt-0.5">93 Security Controls Structure</div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        {{-- Toolbar + Tab Switcher --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex flex-col sm:flex-row items-center justify-between gap-4">
            
            {{-- Tabs --}}
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl border border-slate-200/60 w-full sm:w-auto">
                <button type="button" @click="tab = 'clauses'"
                    :class="tab === 'clauses' ? 'standard-tab-active' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-folder-tree text-indigo-500"></i> Clauses (4–10)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $clauses->count() }}
                    </span>
                </button>
                <button type="button" @click="tab = 'controls'"
                    :class="tab === 'controls' ? 'standard-tab-active' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-500"></i> Annex A Controls
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-100">
                        {{ $controls->count() }}
                    </span>
                </button>
            </div>

            {{-- Filter Search --}}
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-xs pointer-events-none"></i>
                <input type="text" x-model="search" placeholder="Filter by code or title…"
                    class="w-full pl-9 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-blue-400 transition-all">
                <button type="button" x-show="search" @click="search = ''"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        {{-- Tab Contents --}}
        <div class="p-5">
            {{-- Clauses Tab --}}
            <div x-show="tab === 'clauses'">
                @if($clauses->count() > 0)
                    <div class="space-y-3">
                        @foreach($clauses as $clause)
                            @include('admin.standards._tree_item', ['item' => $clause])
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16">
                        <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-folder-open text-3xl text-slate-200"></i>
                        </div>
                        <p class="text-slate-500 font-bold text-sm">No ISO Clauses Found</p>
                        <p class="text-slate-400 text-xs mt-1">Get started by creating a new root clause or importing standards.</p>
                    </div>
                @endif
            </div>

            {{-- Controls Tab --}}
            <div x-show="tab === 'controls'" x-cloak>
                @if($controls->count() > 0)
                    <div class="space-y-3">
                        @foreach($controls as $control)
                            @include('admin.standards._tree_item', ['item' => $control])
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16">
                        <div class="w-16 h-16 rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-shield-halved text-3xl text-slate-200"></i>
                        </div>
                        <p class="text-slate-500 font-bold text-sm">No Annex A Controls Found</p>
                        <p class="text-slate-400 text-xs mt-1">Add Annex A controls or import them using a CSV file.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Import CSV Modal --}}
    <div x-show="showImport"
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
        x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-md w-full overflow-hidden" @click.away="showImport = false">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-file-import"></i>
                    </span>
                    Import ISO Standards
                </h3>
                <button type="button" @click="showImport = false" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.standards.import') }}" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Select CSV File</label>
                    <input type="file" name="csv_file" accept=".csv" required
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-2 bg-slate-50 cursor-pointer">
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-[11px] text-blue-700 mt-3 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-blue-500"></i> Expected Headers:
                        </p>
                        <code class="block font-mono text-[10px] bg-white p-1.5 rounded border border-blue-200 text-slate-600 break-all">
                            parent_code, type, level, code, title, description, questions, implementation_guidance
                        </code>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showImport = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-600/20 flex items-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
