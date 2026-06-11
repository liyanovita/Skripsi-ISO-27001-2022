@extends('layouts.admin')

@section('title', 'Add New ISO Standard')
@section('header_title', 'Add ISO Standard')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.standards.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Back to Standards
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl">
    <div class="p-6 border-b border-slate-200 bg-slate-50">
        <h2 class="text-xl font-black text-slate-800">Add New Standard / Control</h2>
        <p class="text-sm text-slate-500">Create a new clause or control for the ISO 27001 structure.</p>
    </div>

    <form method="POST" action="{{ route('admin.standards.store') }}" class="p-6" x-data="standardForm()">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Type --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                    <option value="clause" {{ old('type') == 'clause' ? 'selected' : '' }}>Clause (Klausul)</option>
                    <option value="control" {{ old('type') == 'control' ? 'selected' : '' }}>Control (Kontrol)</option>
                </select>
                @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Level --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Level (Hierarchy Depth) <span class="text-red-500">*</span></label>
                <input type="number" name="level" value="{{ old('level', 1) }}" min="1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                @error('level') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Code --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. 4.1 or A.5.1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                @error('code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Title --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Understanding the organization..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Parent --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Parent Standard (Optional)</label>
                <select name="parent_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">-- No Parent (Root Level) --</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->title }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Select the parent clause if this is a sub-clause or control.</p>
                @error('parent_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('description') }}</textarea>
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Implementation Guidance --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Implementation Guidance</label>
                <textarea name="implementation_guidance" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('implementation_guidance') }}</textarea>
                <p class="text-xs text-slate-500 mt-1">This will be shown to users as a hint during assessment.</p>
                @error('implementation_guidance') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <hr class="border-slate-200 my-6">

        {{-- Dynamic Questions Builder --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Assessment Questions</h3>
                    <p class="text-sm text-slate-500">Add questions that users need to answer for this standard.</p>
                </div>
                <button type="button" @click="addQuestion()" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 border border-indigo-200 rounded hover:bg-indigo-100 text-sm font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-plus"></i> Add Question
                </button>
            </div>

            <div class="space-y-3" id="questions-container">
                <template x-for="(question, index) in questions" :key="question.id">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-10 flex items-center justify-center font-bold text-slate-400 bg-slate-50 rounded border border-slate-200 shrink-0" x-text="index + 1"></div>
                        <div class="flex-1 relative">
                            <textarea :name="`questions[]`" x-model="question.text" rows="2" class="w-full pl-3 pr-10 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required placeholder="Enter assessment question..."></textarea>
                            <button type="button" @click="removeQuestion(index)" class="absolute right-2 top-2 w-6 h-6 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Remove question">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </template>
                
                <div x-show="questions.length === 0" class="text-center p-6 border-2 border-dashed border-slate-200 rounded-lg text-slate-400 bg-slate-50">
                    <i class="fa-solid fa-clipboard-question text-2xl mb-2 text-slate-300"></i>
                    <p class="text-sm font-medium">No questions added yet.</p>
                    <p class="text-xs mt-1">Click "Add Question" if this control is assessable.</p>
                </div>
            </div>
            @error('questions') <p class="text-xs text-red-500 mt-2 font-bold">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3 mt-8">
            <a href="{{ route('admin.standards.index') }}" class="px-6 py-2.5 rounded-lg font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-lg font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save Standard
            </button>
        </div>
    </form>
</div>

<script>
    function standardForm() {
        return {
            questions: [
                @if(old('questions'))
                    @foreach(old('questions') as $index => $q)
                        { id: {{ $index }}, text: @json($q) },
                    @endforeach
                @endif
            ],
            nextId: {{ old('questions') ? count(old('questions')) : 0 }},
            addQuestion() {
                this.questions.push({ id: this.nextId++, text: '' });
            },
            removeQuestion(index) {
                this.questions.splice(index, 1);
            }
        }
    }
</script>
@endsection
