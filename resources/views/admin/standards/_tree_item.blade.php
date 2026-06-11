<div class="border border-slate-200 rounded-lg overflow-hidden mb-2" x-data="{ expanded: false }">
    <div class="flex items-center justify-between p-3 bg-slate-50 hover:bg-slate-100 transition-colors">
        <div class="flex items-center gap-3 flex-1 cursor-pointer" @click="expanded = !expanded">
            <button type="button" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-chevron-right text-xs transition-transform duration-200" :class="expanded ? 'rotate-90' : ''"></i>
            </button>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest {{ in_array($item->type, ['clause', 'clausa']) ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $item->type }}
                </span>
                <span class="font-bold text-slate-800">{{ $item->code }}</span>
                <span class="text-sm text-slate-600">{{ $item->title }}</span>
            </div>
        </div>
        
        <div class="flex items-center gap-2" x-data="{ showDelete: false }">
            <a href="{{ route('admin.standards.edit', $item) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600 hover:bg-slate-200 transition-colors" title="Edit">
                <i class="fa-solid fa-pen text-xs"></i>
            </a>
            
            <button @click="showDelete = true" x-show="!showDelete" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                <i class="fa-solid fa-trash-can text-xs"></i>
            </button>
            
            <form method="POST" action="{{ route('admin.standards.destroy', $item) }}" x-show="showDelete" class="flex gap-1" x-cloak>
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">Confirm</button>
                <button type="button" @click="showDelete = false" class="px-3 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded hover:bg-slate-300">Cancel</button>
            </form>
        </div>
    </div>
    
    <div x-show="expanded" x-collapse x-cloak class="border-t border-slate-200 bg-white p-4 pl-12">
        <div class="text-sm text-slate-600 mb-4 prose prose-sm max-w-none">
            @if($item->description)
                <div class="mb-3">
                    <strong>Description:</strong><br>
                    {{ $item->description }}
                </div>
            @endif
            
            @if($item->implementation_guidance)
                <div class="mb-3">
                    <strong>Guidance:</strong><br>
                    {{ $item->implementation_guidance }}
                </div>
            @endif
            
            @if($item->questions && count($item->questions) > 0)
                <div class="mb-3">
                    <strong>Questions ({{ count($item->questions) }}):</strong>
                    <ul class="list-disc pl-5 mt-1 space-y-1">
                        @foreach($item->questions as $q)
                            <li>{{ $q }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if($item->children->count() > 0)
            <div class="mt-4 border-l-2 border-slate-100 pl-4 space-y-2">
                @foreach($item->children as $child)
                    @include('admin.standards._tree_item', ['item' => $child])
                @endforeach
            </div>
        @else
            <div class="text-xs italic text-slate-400 mt-2">No child items.</div>
        @endif
    </div>
</div>
