@extends('layouts.app')

@section('title', 'Manajemen Jurusan')
@section('subtitle', 'Kelola data jurusan/kompetensi keahlian.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('konsentrasi.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Kelola Konsentrasi</span>
    </a>
    <a href="{{ route('jurusan.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        <span>Tambah Jurusan</span>
    </a>
@endsection

@section('content')
<div x-data="{ selectionMode: false, selected: [] }">
    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 mb-4 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">Jurusan Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            {{-- Placeholder Bulk Action --}}
            <button type="button" onclick="alert('Fitur hapus massal untuk Jurusan belum tersedia.')" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Hapus Massal
            </button>
        </div>
    </div>

    <div class="table-container min-h-[300px]">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th class="">Kode</th>
                    <th>Nama Jurusan</th>
                    <th class="">Kaprodi</th>
                    <th class="text-center">Konsentrasi</th>
                    <th class="text-center">Jumlah Kelas</th>
                    <th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="selectionMode = !selectionMode" title="Klik untuk memilih data">
                        <div class="flex items-center justify-center">
                            <template x-if="!selectionMode">
                                <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="9" y1="12" x2="15" y2="12"></line> 
                                    </svg>
                                </div>
                            </template>
                            <template x-if="selectionMode">
                                <div class="flex items-center text-indigo-600 justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </template>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($jurusanList ?? [] as $index => $j)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $j->id }}') }">
                        <td class="text-gray-500">{{ $loop->iteration }}</td>
                        <td class=""><span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded-md">{{ $j->kode_jurusan ?? '-' }}</span></td>
                        <td class="font-medium text-gray-800">{{ $j->nama_jurusan }}</td>
                        <td class="text-gray-500">{{ $j->kaprodi->username ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('konsentrasi.index', ['jurusan_id' => $j->id]) }}" class="badge badge-info hover:bg-blue-200 transition">
                                {{ $j->konsentrasi_count ?? $j->konsentrasi->count() }}
                            </a>
                        </td>
                        <td class="text-center"><span class="badge badge-primary">{{ $j->kelas_count ?? $j->kelas->count() }}</span></td>
                        <td class="text-center relative">
                            {{-- Normal Mode: Kebab Dropdown --}}
                            <div x-show="!selectionMode" 
                                 x-data="{
                                     open: false,
                                     timer: null,
                                     isLongPress: false,
                                     
                                     startPress() {
                                         this.isLongPress = false;
                                         this.timer = setTimeout(() => {
                                             this.isLongPress = true;
                                             // Trigger Selection Mode (Parent Scope)
                                             this.selectionMode = true;
                                             // Add to selected (Parent Scope)
                                             if (!this.selected.includes('{{ $j->id }}')) {
                                                 this.selected.push('{{ $j->id }}');
                                             }
                                             if (navigator.vibrate) navigator.vibrate(50);
                                         }, 500);
                                     },
                                     
                                     endPress() {
                                         clearTimeout(this.timer);
                                     },

                                     toggle() {
                                         if (this.isLongPress) return;
                                         if (this.open) { this.open = false; return; }
                                         this.open = true;
                                         this.$nextTick(() => {
                                             const trigger = this.$refs.trigger.getBoundingClientRect();
                                             const menu = this.$refs.menu;
                                             
                                             // Calculate Right Alignment
                                             let left = trigger.right - menu.offsetWidth;
                                             let top = trigger.bottom + 2; 
                                             
                                             // Check bottom overflow
                                             if (window.innerHeight - trigger.bottom < menu.offsetHeight + 20) {
                                                 top = trigger.top - menu.offsetHeight - 2;
                                             }
                                             
                                             menu.style.top = `${top}px`;
                                             menu.style.left = `${left}px`;
                                         });
                                     }
                                 }" 
                                 @scroll.window="open = false"
                                 @resize.window="open = false"
                                 class="relative inline-block text-left"
                            >
                                <button 
                                    x-ref="trigger" 
                                    @click="toggle()" 
                                    @mousedown="startPress()"
                                    @touchstart="startPress()"
                                    @mouseup="endPress()"
                                    @mouseleave="endPress()"
                                    @touchend="endPress()"
                                    type="button" 
                                    class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 select-none"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </button>
                                
                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-ref="menu"
                                         @click.outside="open = false"
                                         style="position: fixed; z-index: 9999; display: none;"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('jurusan.show', $j->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                Detail
                                            </a>
                                            <a href="{{ route('jurusan.edit', $j->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                Edit
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('jurusan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jurusan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Selection Mode: Checkbox --}}
                            <div x-show="selectionMode" style="display: none;">
                                <input type="checkbox" value="{{ $j->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <h3 class="empty-state-title">Tidak Ada Data</h3>
                                <p class="empty-state-description">Belum ada jurusan yang terdaftar.</p>
                                <a href="{{ route('jurusan.create') }}" class="btn btn-primary">Tambah Jurusan</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
