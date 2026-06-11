@extends('layouts.admin')

@section('title', 'ISO Standards Management')
@section('header_title', 'ISO 27001 Standards')

@section('content')
<div x-data="{ showImport: false }">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800">ISO 27001 Standards</h2>
            <p class="text-sm text-slate-500">Manage clauses, controls, and assessment questions.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.standards.export') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-sm font-bold transition-colors">
                <i class="fa-solid fa-download"></i> Export CSV
            </a>
            <button @click="showImport = true" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-sm font-bold transition-colors">
                <i class="fa-solid fa-upload"></i> Import CSV
            </button>
            <a href="{{ route('admin.standards.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">
                <i class="fa-solid fa-plus"></i> Add New Standard
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200" x-data="{ tab: 'clauses' }">
        <div class="flex border-b border-slate-200">
            <button @click="tab = 'clauses'" :class="tab === 'clauses' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 font-medium hover:text-slate-700'" class="px-6 py-4 text-sm transition-colors">
                <i class="fa-solid fa-folder-tree mr-2"></i> Clauses
                <span class="ml-2 text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ $clauses->count() }}</span>
            </button>
            <button @click="tab = 'controls'" :class="tab === 'controls' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 font-medium hover:text-slate-700'" class="px-6 py-4 text-sm transition-colors">
                <i class="fa-solid fa-shield-halved mr-2"></i> Controls (Annex A)
                <span class="ml-2 text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ $controls->count() }}</span>
            </button>
        </div>

        <div class="p-6">
            {{-- Clauses Tab --}}
            <div x-show="tab === 'clauses'">
                @if($clauses->count() > 0)
                    <div class="space-y-2">
                        @foreach($clauses as $clause)
                            @include('admin.standards._tree_item', ['item' => $clause])
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-slate-500">
                        <i class="fa-solid fa-folder-open text-3xl mb-3 text-slate-300"></i>
                        <p>No Clauses found.</p>
                    </div>
                @endif
            </div>

            {{-- Controls Tab --}}
            <div x-show="tab === 'controls'" x-cloak>
                @if($controls->count() > 0)
                    <div class="space-y-2">
                        @foreach($controls as $control)
                            @include('admin.standards._tree_item', ['item' => $control])
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-slate-500">
                        <i class="fa-solid fa-folder-open text-3xl mb-3 text-slate-300"></i>
                        <p>No Controls found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Import CSV Modal --}}
    <div x-show="showImport" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 max-w-md w-full overflow-hidden" @click.away="showImport = false">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-import text-blue-600"></i> Import ISO Standards
                </h3>
                <button @click="showImport = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.standards.import') }}" enctype="multipart/form-data" class="p-5">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Upload CSV File</label>
                    <input type="file" name="csv_file" accept=".csv" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-lg p-2 bg-slate-50">
                    <p class="text-xs text-slate-400 mt-2">Make sure the CSV has the following headers: <strong>parent_code, type, level, code, title, description, questions, implementation_guidance</strong>.</p>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="showImport = false" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-save"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
