@extends('layouts.admin')

@section('title', 'Add Knowledge Base Item')
@section('header_title', 'Add Knowledge Base Item')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.knowledge.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Back to Knowledge Base
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl">
    <div class="p-6 border-b border-slate-200 bg-slate-50">
        <h2 class="text-xl font-black text-slate-800">Add New Document or Article</h2>
        <p class="text-sm text-slate-500">Upload a file or write an article for the knowledge base.</p>
    </div>

    <form method="POST" action="{{ route('admin.knowledge.store') }}" enctype="multipart/form-data" class="p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Title --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Panduan Audit Internal ISO 27001" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Category <span class="text-red-500">*</span></label>
                <input type="text" list="categories" name="category" value="{{ old('category') }}" placeholder="e.g. Audit Guides, Templates" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                <datalist id="categories">
                    <option value="Audit Guides">
                    <option value="Templates">
                    <option value="Policies">
                    <option value="General Information">
                </datalist>
                @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Icon --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Icon (FontAwesome Class)</label>
                <input type="text" name="icon" value="{{ old('icon', 'fa-solid fa-file-pdf') }}" placeholder="e.g. fa-solid fa-file-pdf" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <p class="text-[10px] text-slate-500 mt-1">Default is fa-file-lines.</p>
                @error('icon') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Short Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>{{ old('description') }}</textarea>
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Content --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Full Content / Article <span class="text-red-500">*</span></label>
                <textarea name="content" rows="6" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required placeholder="Write the main instructions or content here...">{{ old('content') }}</textarea>
                @error('content') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- File Attachment --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">File Attachment (Optional)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg bg-slate-50 relative group hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                        <div class="flex text-sm text-slate-600 justify-center">
                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none px-1">
                                <span>Upload a file</span>
                                <input id="file-upload" name="attachment" type="file" class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-slate-500">PDF, DOCX, XLSX, JPG up to 10MB</p>
                    </div>
                </div>
                <div id="file-name-display" class="mt-2 text-sm font-bold text-blue-600 hidden"></div>
                @error('attachment') <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-200">
            <a href="{{ route('admin.knowledge.index') }}" class="px-6 py-2.5 rounded-lg font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-lg font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                <i class="fa-solid fa-upload"></i> Publish Item
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('file-upload').addEventListener('change', function(e) {
        const display = document.getElementById('file-name-display');
        if (this.files && this.files[0]) {
            display.textContent = 'Selected file: ' + this.files[0].name;
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    });
</script>
@endsection
