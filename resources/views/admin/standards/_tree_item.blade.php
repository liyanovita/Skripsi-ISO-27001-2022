@php
    $isClause = in_array($item->type, ['clause', 'clausa']);
    $hasChildren = $item->children && $item->children->count() > 0;
    $hasQuestions = $item->questions && is_array($item->questions) && count($item->questions) > 0;
@endphp

<div class="border border-slate-200/80 rounded-2xl overflow-hidden mb-2.5 bg-white transition-all shadow-sm"
    x-data="{ expanded: false }"
    x-show="!search || '{{ strtolower($item->code) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($item->title)) }}'.includes(search.toLowerCase())">
    
    {{-- Header Row --}}
    <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 hover:bg-slate-100/60 transition-colors border-b border-transparent"
        :class="expanded ? 'border-slate-100 bg-slate-100/50' : ''">
        
        <div class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer select-none" @click="expanded = !expanded">
            {{-- Expand Chevron Button --}}
            <button type="button"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-all shrink-0">
                <i class="fa-solid fa-chevron-right text-xs transition-transform duration-200" :class="expanded ? 'rotate-90 text-blue-600' : ''"></i>
            </button>

            {{-- Type Tag --}}
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider shrink-0
                {{ $isClause ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100' }}">
                <i class="fa-solid {{ $isClause ? 'fa-folder-tree' : 'fa-shield-halved' }} text-[8px]"></i>
                {{ $item->type }}
            </span>

            {{-- Code Tag --}}
            <span class="px-2 py-0.5 bg-slate-900 text-white rounded-md text-xs font-mono font-bold shrink-0">
                {{ $item->code }}
            </span>

            {{-- Title --}}
            <span class="text-xs font-bold text-slate-800 truncate" title="{{ $item->title }}">
                {{ $item->title }}
            </span>

            {{-- Sub-items or Questions Pill Counts --}}
            <div class="hidden md:flex items-center gap-2 shrink-0 ml-auto mr-4">
                @if($hasChildren)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200/70 text-slate-600">
                        {{ $item->children->count() }} {{ Str::plural('item', $item->children->count()) }}
                    </span>
                @endif
                @if($hasQuestions)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">
                        <i class="fa-solid fa-circle-question text-[8px]"></i> {{ count($item->questions) }} Qs
                    </span>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1.5 shrink-0 ml-2">
            <a href="{{ route('admin.standards.edit', $item) }}"
                class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-500 hover:text-amber-600 hover:bg-amber-50 border border-slate-200 hover:border-amber-100 bg-white transition-all"
                title="Edit Standard">
                <i class="fa-solid fa-pen text-xs"></i>
            </a>

            <form method="POST" action="{{ route('admin.standards.destroy', $item) }}"
                x-data
                @submit.prevent="
                    Swal.fire({
                        title: 'Delete Standard Item?',
                        text: 'Are you sure you want to delete &quot;{{ addslashes($item->code) }} - {{ addslashes($item->title) }}&quot;? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, Delete!',
                        cancelButtonText: 'Cancel',
                        width: '22rem',
                        customClass: {
                            title: 'text-base font-bold text-slate-800',
                            htmlContainer: 'text-xs text-slate-500',
                            confirmButton: 'text-xs px-3 py-2 rounded-lg font-semibold',
                            cancelButton: 'text-xs px-3 py-2 rounded-lg font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) { $el.submit(); }
                    });
                ">
                @csrf @method('DELETE')
                <button type="submit"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-100 bg-white transition-all"
                    title="Delete Standard">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Expanded Details Panel --}}
    <div x-show="expanded" x-collapse x-cloak class="p-4 bg-white border-t border-slate-100 space-y-4">
        
        {{-- Description --}}
        @if($item->description)
            <div class="bg-slate-50/80 rounded-xl p-3.5 border border-slate-100 space-y-1">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Description</span>
                <p class="text-xs text-slate-700 leading-relaxed font-medium">{{ $item->description }}</p>
            </div>
        @endif

        {{-- Implementation Guidance --}}
        @if($item->implementation_guidance)
            <div class="bg-amber-50/60 rounded-xl p-3.5 border border-amber-100/80 space-y-1">
                <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest flex items-center gap-1.5">
                    <i class="fa-solid fa-lightbulb text-amber-500"></i> Implementation Guidance
                </span>
                <p class="text-xs text-slate-700 leading-relaxed font-medium">{{ $item->implementation_guidance }}</p>
            </div>
        @endif

        {{-- Assessment Questions --}}
        @if($hasQuestions)
            <div class="bg-blue-50/40 rounded-xl p-3.5 border border-blue-100/80 space-y-2">
                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-1.5">
                    <i class="fa-solid fa-clipboard-question text-blue-500"></i> Assessment Criteria / Questions ({{ count($item->questions) }})
                </span>
                <div class="space-y-1.5">
                    @foreach($item->questions as $qIndex => $question)
                        <div class="flex items-start gap-2 text-xs text-slate-700 font-medium">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                {{ $qIndex + 1 }}
                            </span>
                            <span class="leading-normal">{{ $question }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Nested Children Items --}}
        @if($hasChildren)
            <div class="pt-2">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sub-items / Children</span>
                    <span class="h-px bg-slate-100 flex-1"></span>
                </div>
                <div class="pl-3 border-l-2 border-slate-200/80 space-y-2">
                    @foreach($item->children as $child)
                        @include('admin.standards._tree_item', ['item' => $child])
                    @endforeach
                </div>
            </div>
        @endif

        @if(!$item->description && !$item->implementation_guidance && !$hasQuestions && !$hasChildren)
            <p class="text-xs text-slate-400 italic">No additional description or questions configured for this item.</p>
        @endif

    </div>
</div>
