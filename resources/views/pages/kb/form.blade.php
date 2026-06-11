@extends('layouts.app')
@section('title', isset($resource) ? 'Edit Knowledge Base Resource' : 'Add Knowledge Base Resource')
@section('view_name', isset($resource) ? 'Edit Knowledge Base Resource' : 'Add Knowledge Base Resource')

@section('content')
<div class="max-w-4xl mx-auto pb-12" x-data="{
    content: @js(old('content', $resource->content ?? '')),
    previewHtml: '',
    previewTimer: null,
    refreshPreview() {
        clearTimeout(this.previewTimer);
        this.previewTimer = setTimeout(async () => {
            const response = await fetch(@js(route('knowledge-base.preview')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @js(csrf_token()),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content: this.content })
            });
            const data = await response.json();
            this.previewHtml = data.html;
        }, 250);
    }
}" x-init="refreshPreview()">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        
        {{-- Header --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center text-white shadow-lg shadow-slate-800/20">
                    <i class="fa-solid fa-file-pen text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase">{{ isset($resource) ? __('Edit Knowledge Base Resource') : __('Add New Knowledge Base Resource') }}</h1>
                    <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px] mt-1">{{ __('Knowledge Base Management') }}</p>
                </div>
            </div>
            <a href="{{ route('knowledge-base.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> {{ __('Back') }}
            </a>
        </div>

        {{-- Form --}}
        <div class="p-8">
            <form action="{{ isset($resource) ? route('knowledge-base.update', $resource->id) : route('knowledge-base.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if(isset($resource))
                    @method('PUT')
                @endif

                {{-- Title & Category --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Resource Title') }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $resource->title ?? '') }}" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm"
                            placeholder="{{ __('e.g. Password Policy 2026') }}">
                        @error('title') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Category') }} <span class="text-rose-500">*</span></label>
                        <select name="category" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm cursor-pointer">
                            <option value="">-- {{ __('Select Category') }} --</option>
                            <option value="guides" {{ old('category', $resource->category ?? '') == 'guides' ? 'selected' : '' }}>{{ __('Implementation Guides') }}</option>
                            <option value="templates" {{ old('category', $resource->category ?? '') == 'templates' ? 'selected' : '' }}>{{ __('Policy Templates') }}</option>
                            <option value="sop" {{ old('category', $resource->category ?? '') == 'sop' ? 'selected' : '' }}>{{ __('Standard Operating Procedures') }}</option>
                            <option value="evidence" {{ old('category', $resource->category ?? '') == 'evidence' ? 'selected' : '' }}>{{ __('Evidence Examples') }}</option>
                        </select>
                        @error('category') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Short Description') }}</label>
                    <textarea name="description" rows="2"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm resize-y"
                        placeholder="{{ __('Brief summary of this document...') }}">{{ old('description', $resource->description ?? '') }}</textarea>
                    @error('description') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Format, Size, Icon --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('File Format') }}</label>
                        <input type="text" name="format" value="{{ old('format', $resource->format ?? 'DOCX') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm"
                            placeholder="{{ __('DOCX, PDF, XLSX') }}">
                        @error('format') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('File Size (Display)') }}</label>
                        <input type="text" name="size" value="{{ old('size', $resource->size ?? '25 KB') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm"
                            placeholder="{{ __('e.g. 1.2 MB') }}">
                        @error('size') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('FontAwesome Icon') }}</label>
                        <input type="text" name="icon" value="{{ old('icon', $resource->icon ?? 'fa-solid fa-file-word') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm"
                            placeholder="{{ __('fa-solid fa-file-word') }}">
                        @error('icon') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Attachment --}}
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 space-y-3">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Original Attachment') }}</label>
                            <p class="text-xs text-slate-500 font-medium mt-1">{{ __('Optional. Upload the source file if users should download DOCX, XLSX, PDF, TXT, MD, or CSV directly.') }}</p>
                        </div>
                        @if(isset($resource) && $resource->attachment_name)
                            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-600">
                                <i class="fa-solid fa-paperclip text-indigo-500"></i>
                                {{ $resource->attachment_name }}
                            </span>
                        @endif
                    </div>
                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.md,.csv"
                        class="block w-full text-xs font-bold text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2.5 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:text-white hover:file:bg-indigo-600">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Max 10 MB. Uploading a new attachment replaces the existing file.') }}</p>
                    @error('attachment') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Content --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-between">
                        <span>{{ __('Knowledge Base Content') }} <span class="text-rose-500">*</span></span>
                        <span class="text-slate-300">{{ __('Plain text / Markdown (will be exported to PDF)') }}</span>
                    </label>
                    <textarea name="content" rows="12" required x-model="content" @input="refreshPreview()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-mono text-slate-700 focus:bg-white outline-none focus:ring-4 focus:ring-slate-800/5 focus:border-slate-400 transition-all shadow-sm resize-y custom-scrollbar"
                        placeholder="{{ __('Enter the full policy text, SOP steps, or guide content here...') }}">{{ old('content', $resource->content ?? '') }}</textarea>
                    @error('content') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Content Preview') }}</label>
                        <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">{{ __('Markdown-safe preview') }}</span>
                    </div>
                    <div class="prose prose-sm prose-slate max-w-none min-h-32 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700" x-html="previewHtml"></div>
                </div>

                {{-- Submit --}}
                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-8 py-3 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg active:scale-95 flex items-center gap-2">
                        <i class="fa-solid {{ isset($resource) ? 'fa-save' : 'fa-plus' }}"></i>
                        {{ isset($resource) ? __('Update Resource') : __('Save Resource') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
