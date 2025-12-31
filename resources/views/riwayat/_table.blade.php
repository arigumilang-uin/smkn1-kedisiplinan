{{-- Riwayat Table Partial --}}
{{-- Stats --}}
<div class="flex justify-between items-center mb-4">
    <span class="text-sm text-gray-500">
        Total: <b class="text-blue-600">{{ $riwayat->total() }}</b> data
    </span>
</div>

{{-- Data Table --}}
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Siswa</th>
                <th class="">Kelas</th>
                <th>Pelanggaran</th>
                <th class="text-center">Poin</th>
                <th class="">Dicatat Oleh</th>
                <th class="text-center">Bukti</th>
                <th class="w-24 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="toggleSelectionMode()" title="Klik untuk memilih data">
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
                            <div class="flex items-center justify-center">
                                <input type="checkbox" x-model="selectAll" 
                                    @change="selectAll ? selected = ['{{ $riwayat->pluck('id')->implode("','") }}'] : selected = []" 
                                    @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                            </div>
                        </template>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $r)
                <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $r->id }}') }">
                    {{-- Waktu (Tanggal + Jam) --}}
                    <td class="whitespace-nowrap">
                        <div class="font-medium text-gray-800">{{ $r->tanggal_kejadian->format('d M Y') }}</div>
                        <div class="text-xs text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $r->tanggal_kejadian->format('H:i') }} WIB
                        </div>
                    </td>
                    
                    {{-- Siswa --}}
                    <td>
                        <a href="{{ route('siswa.show', $r->siswa->id ?? 0) }}" class="font-medium text-gray-800 hover:text-blue-600">
                            {{ $r->siswa->nama_siswa ?? '-' }}
                        </a>
                        <div class="text-xs text-gray-400 font-mono">{{ $r->siswa->nisn ?? '-' }}</div>
                    </td>
                    
                    {{-- Kelas --}}
                    <td class="">
                        <span class="badge badge-primary">{{ $r->siswa->kelas->nama_kelas ?? '-' }}</span>
                    </td>
                    
                    {{-- Pelanggaran --}}
                    <td class="max-w-xs">
                        <p class="font-medium text-gray-800">{{ $r->jenisPelanggaran->nama_pelanggaran ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $r->jenisPelanggaran->kategoriPelanggaran->nama_kategori ?? '' }}</p>
                        @if($r->keterangan)
                            <p class="text-sm text-gray-500 truncate mt-1 italic">"{{ Str::limit($r->keterangan, 40) }}"</p>
                        @endif
                    </td>
                    
                    {{-- Poin --}}
                    <td class="text-center">
                        @php
                            $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($r);
                        @endphp
                        @if($poinInfo['matched'] && $poinInfo['poin'] > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700" 
                                  title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($r) }}">
                                +{{ $poinInfo['poin'] }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-400">
                                +0
                            </span>
                        @endif
                        @if(!empty($poinInfo['frequency']))
                            <div class="text-[10px] text-gray-400 mt-1">{{ $poinInfo['frequency'] }}× Kejadian</div>
                        @endif
                    </td>
                    
                    {{-- Dicatat Oleh --}}
                    <td class="text-sm">
                        @if($r->guruPencatat)
                            <div class="font-medium text-gray-700">{{ $r->guruPencatat->username }}</div>
                            <div class="text-[10px] text-gray-400">{{ $r->guruPencatat->nama ?? '-' }}</div>
                        @else
                            <span class="text-gray-400 italic text-xs">Sistem</span>
                        @endif
                    </td>
                    
                    {{-- Bukti Foto --}}
                    <td class="text-center">
                        @if($r->bukti_foto_path)
                            <a href="{{ asset('storage/' . $r->bukti_foto_path) }}" target="_blank" 
                               class="btn btn-icon btn-outline" title="Lihat Bukti">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </a>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    
                    {{-- Aksi --}}
                    <td class="text-center relative">
                        {{-- Selection Mode --}}
                        <div x-show="selectionMode" style="display: none;">
                            <input type="checkbox" value="{{ $r->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                        </div>

                        {{-- Normal Mode --}}
                        <div x-show="!selectionMode">
                            {{-- Desktop Buttons --}}
                            <div class="hidden md:flex justify-center gap-1">
                                <a href="{{ route('riwayat.edit', $r->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('riwayat.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus riwayat pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50 hover:border-red-200" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            
                            {{-- Mobile Kebab Dropdown --}}
                            <div class="md:hidden relative inline-block text-left"
                                 x-data="{
                                     open: false,
                                     timer: null,
                                     isLongPress: false,
                                     
                                     startPress() {
                                         this.isLongPress = false;
                                         this.timer = setTimeout(() => {
                                             this.isLongPress = true;
                                             this.toggleSelectionMode();
                                             if (!this.selected.includes('{{ $r->id }}')) {
                                                 this.selected.push('{{ $r->id }}');
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
                                             let left = trigger.right - menu.offsetWidth;
                                             let top = trigger.bottom + 2; 
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
                            >
                                <button 
                                    x-ref="trigger" 
                                    @click="toggle()" 
                                    @mousedown="startPress()"
                                    @touchstart="startPress()"
                                    @mouseup="endPress()"
                                    @mouseleave="endPress()"
                                    @touchend="endPress()"
                                    class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                    </svg>
                                </button>

                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-ref="menu"
                                         @click.outside="open = false"
                                         style="position: fixed; z-index: 9999; display: none;"
                                         x-transition:enter="transition ease-out duration-100"
                                         class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('riwayat.edit', $r->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                Edit
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('riwayat.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat pelanggaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                            </svg>
                            <h3 class="empty-state-title">Tidak Ada Data</h3>
                            <p class="empty-state-description">Belum ada riwayat pelanggaran yang dicatat.</p>
                            <a href="{{ route('riwayat.create') }}" class="btn btn-primary">Catat Pelanggaran</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($riwayat->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
        <p class="text-sm text-gray-500">
            Menampilkan {{ $riwayat->firstItem() }} - {{ $riwayat->lastItem() }} dari {{ $riwayat->total() }}
        </p>
        {{ $riwayat->links() }}
    </div>
@endif
