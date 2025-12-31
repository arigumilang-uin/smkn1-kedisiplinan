
    {{-- Data Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Kontak Wali</th>
                    <th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="toggleSelectionMode()" title="Klik untuk memilih data">
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
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                                </div>
                            </template>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswa as $index => $s)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $s->id }}') }">
                        <td class="text-gray-500">{{ $siswa->firstItem() + $index }}</td>
                        <td>
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-md">
                                {{ $s->nisn }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('siswa.show', $s->id) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $s->nama_siswa }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-primary">
                                {{ $s->kelas->nama_kelas ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @if($s->nomor_hp_wali_murid)
                                <a href="https://wa.me/62{{ ltrim($s->nomor_hp_wali_murid, '0') }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    {{ $s->nomor_hp_wali_murid }}
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="text-center relative">
                            {{-- Selection Mode: Checkbox --}}
                            <div x-show="selectionMode" style="display: none;">
                                <input type="checkbox" value="{{ $s->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </div>

                            {{-- Normal Mode: Kebab Dropdown --}}
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
                                             // Trigger Selection Mode
                                             this.toggleSelectionMode();
                                             // Add this row to selected
                                             if (!this.selected.includes('{{ $s->id }}')) {
                                                 this.selected.push('{{ $s->id }}');
                                             }
                                             // Haptic feedback
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
                                            <a href="{{ route('siswa.show', $s->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                Detail
                                            </a>
                                            @can('update', $s)
                                            <a href="{{ route('siswa.edit', $s->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                Edit
                                            </a>
                                            @endcan
                                            @can('delete', $s)
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <button type="button" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors"
                                                    @click="open = false; $dispatch('open-delete-modal', { id: {{ $s->id }}, nama: '{{ $s->nama_siswa }}', nisn: '{{ $s->nisn }}' })">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                Hapus
                                            </button>
                                            @endcan
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                </svg>
                                <h3 class="empty-state-title">Data Tidak Ditemukan</h3>
                                <p class="empty-state-description">Tidak ada data siswa yang sesuai dengan filter Anda.</p>
                                @can('create', App\Models\Siswa::class)
                                    <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14"/><path d="M12 5v14"/>
                                        </svg>
                                        <span>Tambah Siswa Baru</span>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($siswa->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <p class="text-sm text-gray-500">
                Menampilkan {{ $siswa->firstItem() }} sampai {{ $siswa->lastItem() }} dari {{ $siswa->total() }} data
            </p>
            <div class="pagination">
                {{-- Previous --}}
                @if($siswa->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </span>
                @else
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->previousPageUrl() }}" class="pagination-btn" data-page="prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </a>
                @endif
                
                {{-- Next --}}
                @if($siswa->hasMorePages())
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->nextPageUrl() }}" class="pagination-btn" data-page="next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                @else
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    @endif
