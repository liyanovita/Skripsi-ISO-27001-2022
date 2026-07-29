@extends('layouts.admin')

@section('title', 'Edit ISO Standard')
@section('header_title', 'Edit ISO Standard')

@section('content')
<style>
    .form-input {
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-input:focus {
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        border-color: #60a5fa;
        outline: none;
        background: #fff;
    }
    .section-card {
        background: #fff;
        border-radius: 1.25rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 4px 0 rgba(30,58,138,0.04);
    }
</style>

<div class="max-w-3xl">

    {{-- Back Link --}}
    <a href="{{ route('admin.standards.index') }}"
        class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-blue-600 transition-colors mb-5 font-medium group">
        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to Standards
    </a>

    {{-- Page Title --}}
    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/20">
                <i class="fa-solid fa-pen"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-800">Edit Standard / Control</h2>
                <p class="text-xs text-slate-400 font-medium">Update details for <strong class="text-slate-600">{{ $standard->code }} - {{ $standard->title }}</strong></p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider shrink-0
            {{ in_array($standard->type, ['clause', 'clausa']) ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100' }}">
            <i class="fa-solid {{ in_array($standard->type, ['clause', 'clausa']) ? 'fa-folder-tree' : 'fa-shield-halved' }} text-[10px]"></i>
            {{ $standard->type }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.standards.update', $standard) }}" class="space-y-5" x-data="standardForm()">
        @csrf
        @method('PUT')

        {{-- Basic Properties Card --}}
        <div class="section-card p-6 space-y-5">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-blue-500"></i> Classification & Code
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Type --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Type <span class="text-red-400">*</span></label>
                    <select name="type" required class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                        <option value="clause" {{ old('type', $standard->type) == 'clause' ? 'selected' : '' }}>Clause (Klausul 4-10)</option>
                        <option value="control" {{ old('type', $standard->type) == 'control' ? 'selected' : '' }}>Control (Annex A Kontrol)</option>
                    </select>
                    @error('type') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>

                {{-- Level --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Hierarchy Level <span class="text-red-400">*</span></label>
                    <input type="number" name="level" value="{{ old('level', $standard->level) }}" min="1" required
                        placeholder="1 = Root, 2 = Sub-clause"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('level') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>

                {{-- Code --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Standard Code <span class="text-red-400">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $standard->code) }}" required
                        placeholder="e.g. 4.1 or A.5.1"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono">
                    @error('code') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $standard->title) }}" required
                        placeholder="e.g. Understanding the organization and its context"
                        class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('title') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Parent Standard --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Parent Standard (Optional)</label>
                <select name="parent_id" class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm cursor-pointer">
                    <option value="">— No Parent (Root Level) —</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id', $standard->parent_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->title }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Select parent if this is a sub-clause or specific control requirement.</p>
                @error('parent_id') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Content & Guidance Card --}}
        <div class="section-card p-6 space-y-5">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-align-left text-blue-500"></i> Description & Guidance
            </h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Description</label>
                <textarea name="description" rows="3"
                    placeholder="Provide detailed description of what this clause/control requires…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('description', $standard->description) }}</textarea>
                @error('description') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Implementation Guidance</label>
                <textarea name="implementation_guidance" rows="3"
                    placeholder="Provide practical steps or hints for auditors when assessing this control…"
                    class="form-input w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none">{{ old('implementation_guidance', $standard->implementation_guidance) }}</textarea>
                @error('implementation_guidance') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Dynamic Questions Builder Card --}}
        <div class="section-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-question text-blue-500"></i> Assessment Questions
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Define audit criteria questions for evaluation during assessment sessions.</p>
                </div>
                <button type="button" @click="addQuestion()"
                    class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 text-xs font-bold flex items-center gap-1.5 transition-colors border border-blue-100">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add Question
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(question, index) in questions" :key="question.id">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-500 font-bold text-xs flex items-center justify-center shrink-0" x-text="index + 1"></span>
                        <input type="text" name="questions[]" x-model="question.text" placeholder="Enter assessment question..."
                            class="form-input flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <button type="button" @click="removeQuestion(index)" x-show="questions.length > 1"
                            class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition-colors shrink-0">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-between items-center pt-2">
            <a href="{{ route('admin.standards.index') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 active:scale-95 transition-all shadow-md shadow-blue-600/20 flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i> Save Changes
            </button>
        </div>

    </form>
</div>

<script>
function standardForm() {
    return {
        questions: @json(old('questions', $standard->questions ?? [''])).map((q, i) => ({ id: Date.now() + i, text: q })),
        addQuestion() {
            this.questions.push({ id: Date.now(), text: '' });
        },
        removeQuestion(index) {
            if (this.questions.length > 1) {
                this.questions.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
