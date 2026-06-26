@extends('layouts.app')
@section('title', 'Audit Sessions')
@section('view_name', 'Audit Sessions')

@section('content')
<div x-data="{ showSessionModal: {{ request('create') ? 'true' : 'false' }}, showImportModal: false, showEditModal: false, showCloneModal: false, showArchiveModal: false, showRestoreModal: false, showDeleteModal: false, editSessionId: '', editSessionName: '', cloneSessionId: '', archiveSessionId: '', restoreSessionId: '', deleteSessionId: '' }">
    <div class="max-w-6xl mx-auto space-y-6 pb-16">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">ISO 27001:2022</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ __('Audit Sessions') }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-0.5">{{ __('Manage audit cycles for ISO 27001:2022 assessment.') }}</p>
            </div>
            
            <div class="flex gap-2">
                <button @click="showImportModal = true" 
                    class="flex items-center gap-2 px-4 py-2 bg-white text-slate-700 border border-slate-200 rounded-xl text-xs font-bold shadow-sm hover:bg-slate-50 transition-all">
                    <i class="fa-solid fa-file-import text-slate-400"></i> {{ __('Import') }}
                </button>
                <button @click="showSessionModal = true" id="btn-new-session"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all active:scale-95">
                    <i class="fa-solid fa-plus"></i> {{ __('New Session') }}
                </button>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Total Cycles') }}</p>
                        <div class="flex items-baseline gap-1.5">
                            <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ $sessions->count() }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">{{ __('Sessions') }}</p>
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center border border-slate-100 group-hover:bg-slate-900 group-hover:text-white transition-all">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Active Assessments') }}</p>
                        <div class="flex items-center gap-2">
                            <p class="text-3xl font-bold text-blue-600 tracking-tight">{{ $sessions->filter(fn($session) => !$session->trashed() && $session->status !== 'completed')->count() }}</p>
                            <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <i class="fa-solid fa-spinner"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Completed Cycles') }}</p>
                        <p class="text-3xl font-bold text-green-600 tracking-tight">{{ $sessions->filter(fn($session) => !$session->trashed() && $session->status === 'completed')->count() }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center border border-green-100 group-hover:bg-green-600 group-hover:text-white transition-all">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sessions Table --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm" x-data="{ searchQuery: '', filterStatus: 'all' }">
            <div class="p-4 border-b border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="{{ __('Search sessions...') }}" class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                </div>
                <div class="flex bg-slate-100 p-1 rounded-xl w-full sm:w-auto">
                    <button @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 sm:flex-none px-4 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all">{{ __('All') }}</button>
                    <button @click="filterStatus = 'in_progress'" :class="filterStatus === 'in_progress' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 sm:flex-none px-4 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all">{{ __('Active') }}</button>
                    <button @click="filterStatus = 'completed'" :class="filterStatus === 'completed' ? 'bg-white text-green-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 sm:flex-none px-4 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all">{{ __('Completed') }}</button>
                    <button @click="filterStatus = 'archived'" :class="filterStatus === 'archived' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 sm:flex-none px-4 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all">{{ __('Archived') }}</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead id="session-table-header">
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ __('Session') }}</th>
                            <th class="px-6 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ __('Progress') }}</th>
                            <th class="px-6 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ __('Maturity') }}</th>
                            <th class="px-6 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ __('Status') }}</th>
                            <th class="px-6 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($sessions as $index => $session)
                        <tr x-data="{ name: '{{ addslashes(strtolower($session->name)) }}', status: '{{ $session->trashed() ? 'archived' : ($session->status == 'completed' ? 'completed' : 'in_progress') }}' }" 
                            x-show="(filterStatus === 'all' || filterStatus === status) && (searchQuery === '' || name.includes(searchQuery.toLowerCase()))"
                            class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5">
                                @if($session->trashed())
                                  <div class="block opacity-70">
                                      <p class="text-sm font-bold text-slate-900 tracking-tight">{{ $session->name }}</p>
                                      <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest flex items-center gap-2">
                                          <i class="fa-solid fa-calendar text-[8px] opacity-40"></i> {{ $session->created_at->format('d M Y') }}
                                      </p>
                                  </div>
                                @else
                                  <a href="{{ route('sessions.show', $session->id) }}" class="block" @if($index === 0) id="btn-start-eval-first" @endif>
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight">{{ $session->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest flex items-center gap-2">
                                        <i class="fa-solid fa-calendar text-[8px] opacity-40"></i> {{ $session->created_at->format('d M Y') }}
                                    </p>
                                </a>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $totalC = $session->results_count ?? 0;
                                    $ansC = $session->answered_count ?? 0;
                                    $prog = $totalC > 0 ? ($ansC / $totalC) * 100 : 0;
                                @endphp
                                <div class="flex flex-col gap-1.5 w-32">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">{{ $ansC }} / {{ $totalC }} Scored</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden flex items-center">
                                        <div class="h-full {{ $prog == 100 ? 'bg-green-500' : 'bg-blue-500' }} rounded-full" style="width: {{ $prog }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-slate-900">
                                        {{ number_format($session->overall_maturity_score ?? 0, 2) }}
                                    </span>
                                    <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden md:block">
                                        <div class="h-full bg-blue-600" style="width: {{ ($session->overall_maturity_score ?? 0) / 5 * 100 }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @if($session->trashed())
                                    <span class="px-2.5 py-1 bg-orange-50 text-orange-600 border-orange-100 text-[9px] font-bold rounded-lg uppercase tracking-widest border">{{ __('Archived') }}</span>
                                @else
                                    <span class="px-2.5 py-1 {{ $session->status == 'completed' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-blue-50 text-blue-600 border-blue-100' }} text-[9px] font-bold rounded-lg uppercase tracking-widest border">
                                        {{ $session->status == 'completed' ? 'Completed' : 'In Progress' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($session->trashed())
                                        <button @click.prevent="restoreSessionId = '{{ $session->id }}'; showRestoreModal = true" class="w-9 h-9 flex items-center justify-center bg-white text-slate-400 hover:text-green-600 border border-slate-200 rounded-lg transition-all" title="{{ __('Restore Session') }}">
                                            <i class="fa-solid fa-trash-can-arrow-up text-xs"></i>
                                        </button>

                                        <button @click.prevent="deleteSessionId = '{{ $session->id }}'; showDeleteModal = true" class="w-9 h-9 flex items-center justify-center bg-white text-slate-400 hover:text-red-600 border border-slate-200 rounded-lg transition-all" title="{{ __('Delete Permanently') }}">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    @else
                                        <button @click.prevent="editSessionId = '{{ $session->id }}'; editSessionName = '{{ addslashes($session->name) }}'; showEditModal = true" class="w-9 h-9 flex items-center justify-center bg-white text-slate-400 hover:text-emerald-600 border border-slate-200 rounded-lg transition-all" title="{{ __('Edit Session Name') }}">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>

                                        <button @click.prevent="cloneSessionId = '{{ $session->id }}'; showCloneModal = true" class="w-9 h-9 flex items-center justify-center bg-white text-slate-400 hover:text-indigo-600 border border-slate-200 rounded-lg transition-all" title="{{ __('Duplicate Cycle') }}">
                                            <i class="fa-solid fa-copy text-xs"></i>
                                        </button>

                                        <a href="{{ route('sessions.export-json', $session->id) }}" 
                                           class="w-9 h-9 flex items-center justify-center bg-white text-slate-400 hover:text-blue-600 border border-slate-200 rounded-lg transition-all" title="{{ __('Export Template') }}">
                                            <i class="fa-solid fa-download text-xs"></i>
                                        </a>
                                        
                                        <a href="{{ route('sessions.show', $session->id) }}" 
                                           class="w-9 h-9 flex items-center justify-center bg-blue-600 text-white rounded-lg transition-all shadow-md shadow-blue-600/20" title="{{ __('Launch Assessment') }}">
                                            <i class="fa-solid fa-rocket text-xs"></i>
                                        </a>
                                        
                                        <button @click.prevent="archiveSessionId = '{{ $session->id }}'; showArchiveModal = true" class="w-9 h-9 flex items-center justify-center bg-white text-slate-400 hover:text-orange-500 border border-slate-200 rounded-lg transition-all" title="{{ __('Archive Cycle') }}">
                                            <i class="fa-solid fa-box-archive text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-slate-50 text-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-inbox text-2xl"></i>
                                </div>
                                <h3 class="text-slate-900 font-bold text-base">{{ __('No Audit Cycles Yet') }}</h3>
                                <p class="text-slate-400 text-sm font-medium mt-1">{{ __('Waiting for the first assessment launch.') }}</p>
                                <button @click="showSessionModal = true" class="mt-4 inline-flex items-center gap-1.5 text-blue-600 font-bold text-xs hover:underline group">{{ __('Start your first session') }}<i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform ml-1"></i></button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    <div x-show="showSessionModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
        <div x-show="showSessionModal" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showSessionModal = false"></div>
        
        <div x-show="showSessionModal" x-transition.scale.95 class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 relative z-10 border border-slate-100">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-11 h-11 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20">
                    <i class="fa-solid fa-plus text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Launch New Session') }}</h3>
                    <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mt-0.5">{{ __('Initialize Assessment') }}</p>
                </div>
            </div>

            <form action="{{ route('sessions.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5 ml-1">{{ __('Session Name') }}</label>
                    <input type="text" name="name" required placeholder="{{ __('e.g., Enterprise Audit Q1 2026') }}" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all">
                </div>
                
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showSessionModal = false" class="flex-1 px-5 py-2.5 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">{{ __('Cancel') }}</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20">{{ __('Initialize') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showImportModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
        <div x-show="showImportModal" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showImportModal = false"></div>
        
        <div x-show="showImportModal" x-transition.scale.95 class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 relative z-10 border border-slate-100">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-11 h-11 bg-slate-900 text-white rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-file-import text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Import Audit Template') }}</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ __('External Collaboration') }}</p>
                </div>
            </div>

            <form action="{{ route('sessions.import-json') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5 ml-1">{{ __('Select JSON Template File') }}</label>
                    <input type="file" name="json_file" required accept=".json,.txt"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:border-slate-600 transition-all">
                    <p class="text-[10px] text-slate-400 mt-2 font-medium italic">{{ __('Make sure the file was exported from OpenAudit-27001:2022.') }}</p>
                </div>
                
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showImportModal = false" class="flex-1 px-5 py-2.5 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">{{ __('Cancel') }}</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-black transition-all">{{ __('Import Session') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div x-show="showEditModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
        <div x-show="showEditModal" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showEditModal = false"></div>
        
        <div x-show="showEditModal" x-transition.scale.95 class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 relative z-10 border border-slate-100">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-11 h-11 bg-emerald-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20">
                    <i class="fa-solid fa-pen text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Edit Session Name') }}</h3>
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mt-0.5">{{ __('Update Identity') }}</p>
                </div>
            </div>

            <form :action="'{{ url('sessions') }}/' + editSessionId" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1.5 ml-1">{{ __('Session Name') }}</label>
                    <input type="text" name="name" x-model="editSessionName" required placeholder="{{ __('e.g., Enterprise Audit Q1 2026') }}" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/5 transition-all">
                </div>
                
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showEditModal = false" class="flex-1 px-5 py-2.5 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">{{ __('Cancel') }}</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Clone Confirmation Modal --}}
    <div x-show="showCloneModal"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
        <div x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showCloneModal = false"></div>
        <div x-transition.scale.95 class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 z-10 border border-slate-100 text-center">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm">
                <i class="fa-solid fa-copy text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">{{ __('Clone Session') }}</h3>
            <p class="text-sm text-slate-500 mt-2">{{ __('Are you sure you want to duplicate this session? All assessment data and scores will be copied into a new In-Progress session.') }}</p>
            <form :action="'{{ url('sessions') }}/' + cloneSessionId + '/clone'" method="POST" class="mt-6 flex gap-3">
                @csrf
                <button type="button" @click="showCloneModal = false" class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold uppercase tracking-wider hover:bg-slate-200 transition-all text-xs">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-5 py-3 rounded-xl bg-indigo-600 text-white font-bold uppercase tracking-wider hover:bg-indigo-700 transition-all text-xs shadow-md shadow-indigo-600/20">{{ __('Clone Now') }}</button>
            </form>
        </div>
    </div>

    {{-- Archive Confirmation Modal --}}
    <div x-show="showArchiveModal"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
        <div x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showArchiveModal = false"></div>
        <div x-transition.scale.95 class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 z-10 border border-slate-100 text-center">
            <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm">
                <i class="fa-solid fa-box-archive text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">{{ __('Archive Session') }}</h3>
            <p class="text-sm text-slate-500 mt-2">{{ __('Are you sure you want to archive this session? It will be moved to the archive and can be restored later.') }}</p>
            <form :action="'{{ url('sessions') }}/' + archiveSessionId" method="POST" class="mt-6 flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showArchiveModal = false" class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold uppercase tracking-wider hover:bg-slate-200 transition-all text-xs">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-5 py-3 rounded-xl bg-orange-500 text-white font-bold uppercase tracking-wider hover:bg-orange-600 transition-all text-xs shadow-md shadow-orange-500/20">{{ __('Archive') }}</button>
            </form>
        </div>
    </div>
    {{-- Restore Confirmation Modal --}}
    <div x-show="showRestoreModal"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
        <div x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showRestoreModal = false"></div>
        <div x-transition.scale.95 class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 z-10 border border-slate-100 text-center">
            <div class="w-16 h-16 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm">
                <i class="fa-solid fa-trash-can-arrow-up text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">{{ __('Restore Session') }}</h3>
            <p class="text-sm text-slate-500 mt-2">{{ __('Are you sure you want to restore this session? It will be moved back to your active sessions list.') }}</p>
            <form :action="'{{ url('sessions') }}/' + restoreSessionId + '/restore'" method="POST" class="mt-6 flex gap-3">
                @csrf
                <button type="button" @click="showRestoreModal = false" class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold uppercase tracking-wider hover:bg-slate-200 transition-all text-xs">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-5 py-3 rounded-xl bg-green-600 text-white font-bold uppercase tracking-wider hover:bg-green-700 transition-all text-xs shadow-md shadow-green-600/20">{{ __('Restore') }}</button>
            </form>
        </div>
    </div>

    {{-- Permanent Delete Confirmation Modal --}}
    <div x-show="showDeleteModal"
         class="fixed inset-0 z-[100] flex items-center justify-center p-6" x-cloak>
        <div x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div x-transition.scale.95 class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-7 z-10 border border-slate-100 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-sm">
                <i class="fa-solid fa-trash text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900">{{ __('Delete Permanently') }}</h3>
            <p class="text-sm text-slate-500 mt-2">{{ __('This action is irreversible. All assessment data, scores, and results for this session will be permanently deleted and cannot be recovered.') }}</p>
            <form :action="'{{ url('sessions') }}/' + deleteSessionId + '/force-delete'" method="POST" class="mt-6 flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold uppercase tracking-wider hover:bg-slate-200 transition-all text-xs">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-5 py-3 rounded-xl bg-red-600 text-white font-bold uppercase tracking-wider hover:bg-red-700 transition-all text-xs shadow-md shadow-red-600/20">{{ __('Delete Forever') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
