@extends('layouts.app')

@section('title', 'Data Siswa')
@section('subtitle', 'Kelola data seluruh siswa di sekolah Anda.')
@section('page-header', true)

@section('actions')
    @can('create', App\Models\Siswa::class)
        <a href="{{ route('siswa.deleted') }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
            </svg>
            <span>Arsip Siswa</span>
        </a>
        <a href="{{ route('siswa.bulk-create') }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
            </svg>
            <span>Import Excel</span>
        </a>
        <a href="{{ route('siswa.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/><path d="M12 5v14"/>
            </svg>
            <span>Tambah Siswa</span>
        </a>
    @endcan
@endsection

@section('content')
<div class="space-y-6" x-data="siswaPage()">
    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'jurusan_id', 'kelas_id']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Data</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat...</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>
        </div>
        
        <div x-show="expanded" x-collapse.duration.300ms x-cloak>
            <div class="card-body border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
                    <div class="form-group md:col-span-2">
                        <label for="search" class="form-label">Cari</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="search" 
                                x-model.debounce.500ms="filters.search"
                                class="form-input pr-10" 
                                placeholder="Nama atau NISN..."
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isLoading">
                                <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Jurusan --}}
                    <div class="form-group">
                        <label for="jurusan_id" class="form-label">Jurusan</label>
                        <select id="jurusan_id" x-model="filters.jurusan_id" class="form-input form-select">
                            <option value="">Semua Jurusan</option>
                            @foreach($allJurusan ?? [] as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Kelas --}}
                    <div class="form-group">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select id="kelas_id" x-model="filters.kelas_id" class="form-input form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($allKelas ?? [] as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Actions --}}
                    <div class="md:col-span-4 flex justify-end">
                        <button type="button" @click="resetFilters()" class="btn btn-secondary text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>
                            </svg>
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">Siswa Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button 
                type="button" 
                @click="$dispatch('open-bulk-delete-modal', { ids: selected })" 
                class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Hapus Massal
            </button>
        </div>
    </div>
    
    {{-- Data Table Container --}}
    <div id="siswa-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('siswa._table')
    </div>
</div>

{{-- Delete Siswa Modal (Single & Bulk) --}}
<div 
    x-data="{ 
        open: false, 
        siswaId: null, 
        siswaName: '', 
        siswaNisn: '',
        alasanKeluar: '',
        keteranganKeluar: '',
        bulkMode: false,
        bulkIdsString: '',
        selectedCount: 0
    }"
    @open-delete-modal.window="
        open = true; 
        bulkMode = false;
        siswaId = $event.detail.id; 
        siswaName = $event.detail.nama; 
        siswaNisn = $event.detail.nisn;
        alasanKeluar = '';
        keteranganKeluar = '';
    "
    @open-bulk-delete-modal.window="
        open = true;
        bulkMode = true;
        bulkIdsString = $event.detail.ids.join(',');
        selectedCount = $event.detail.ids.length;
        alasanKeluar = '';
        keteranganKeluar = '';
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    {{-- Backdrop --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
        @click="open = false"
    ></div>
    
    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
            @click.stop
        >
            {{-- Header --}}
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800" x-text="bulkMode ? 'Hapus Data Massal' : 'Hapus Data Siswa'"></h3>
                        <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form :action="bulkMode ? '{{ route('siswa.bulk-delete-selection') }}' : '/siswa/' + siswaId" method="POST">
                @csrf
                <template x-if="!bulkMode">
                    @method('DELETE')
                </template>
                <input type="hidden" name="ids" x-model="bulkIdsString" x-if="bulkMode">
                
                <div class="p-6 space-y-4">
                    {{-- Siswa Info / Bulk Info --}}
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <template x-if="!bulkMode">
                            <div>
                                <p class="text-sm text-gray-500">Siswa yang akan dihapus:</p>
                                <p class="font-semibold text-gray-800" x-text="siswaName"></p>
                                <p class="text-sm text-gray-500 font-mono" x-text="'NISN: ' + siswaNisn"></p>
                            </div>
                        </template>
                        <template x-if="bulkMode">
                            <div>
                                <p class="text-sm text-gray-500">Jumlah siswa yang akan dihapus:</p>
                                <p class="font-semibold text-gray-800 text-lg" x-text="selectedCount + ' Siswa'"></p>
                                <p class="text-xs text-gray-400 mt-1">Siswa terpilih akan dipindahkan ke arsip.</p>
                            </div>
                        </template>
                    </div>
                    
                    {{-- Alasan Keluar --}}
                    <div class="form-group">
                        <label for="alasan_keluar" class="form-label form-label-required">Alasan Keluar</label>
                        <select 
                            id="alasan_keluar" 
                            name="alasan_keluar" 
                            x-model="alasanKeluar"
                            class="form-input form-select" 
                            required
                        >
                            <option value="">-- Pilih Alasan --</option>
                            <option value="Alumni">Alumni (Lulus)</option>
                            <option value="Dikeluarkan">Dikeluarkan</option>
                            <option value="Pindah Sekolah">Pindah Sekolah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    {{-- Keterangan --}}
                    <div class="form-group">
                        <label for="keterangan_keluar" class="form-label">Keterangan Tambahan</label>
                        <textarea 
                            id="keterangan_keluar" 
                            name="keterangan_keluar" 
                            x-model="keteranganKeluar"
                            rows="3" 
                            class="form-input form-textarea" 
                            placeholder="Tuliskan keterangan tambahan (opsional)..."
                        ></textarea>
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <p class="text-xs text-amber-700">
                            <strong>Perhatian:</strong> Siswa akan dipindahkan ke data arsip dan dapat di-restore kembali jika diperlukan.
                        </p>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button 
                        type="submit" 
                        class="btn btn-danger"
                        :disabled="!alasanKeluar"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        <span x-text="bulkMode ? 'Hapus Massal' : 'Hapus Siswa'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('siswaPage', () => ({
            isLoading: false,
            selectionMode: false,
            selectAll: false,
            selected: [],
            filters: {
                search: '{{ request('search') }}',
                jurusan_id: '{{ request('jurusan_id') }}',
                kelas_id: '{{ request('kelas_id') }}'
            },

            init() {
                // Watchers for cleaner filtering logic
                this.$watch('filters.search', () => this.fetchData());
                this.$watch('filters.jurusan_id', () => this.fetchData());
                this.$watch('filters.kelas_id', () => this.fetchData());
                
                // Watch selection mode change
                this.$watch('selectionMode', (value) => {
                    if (!value) {
                         this.selected = []; // Clear selection when exiting mode
                         this.selectAll = false;
                    }
                });

                // Initialize popstate listener for back/forward button support
                window.addEventListener('popstate', (event) => {
                    this.fetchData(window.location.href, false);
                });

                // Handle pagination clicks within the table container using event delegation
                const container = document.getElementById('siswa-table-container');
                if (container) {
                     container.addEventListener('click', (e) => {
                        const link = e.target.closest('.pagination a');
                        if (link) {
                            e.preventDefault();
                            this.fetchData(link.href);
                        }
                    });
                }
            },

            toggleSelectionMode() {
                this.selectionMode = !this.selectionMode;
            },
            
            toggleSelectAll() {
                // Get all checkboxes in the table
                const checkboxes = document.querySelectorAll('#siswa-table-container input[type="checkbox"][value]');
                const ids = Array.from(checkboxes).map(cb => cb.value);
                
                if (this.selectAll) {
                    // Select all
                    this.selected = ids;
                } else {
                    // Deselect all
                    this.selected = [];
                }
            },

            async fetchData(url = null, updatePushState = true) {
                this.isLoading = true;
                
                // Build URL if not provided
                if (!url) {
                    const params = new URLSearchParams();
                    if (this.filters.search) params.append('search', this.filters.search);
                    if (this.filters.jurusan_id) params.append('jurusan_id', this.filters.jurusan_id);
                    if (this.filters.kelas_id) params.append('kelas_id', this.filters.kelas_id);
                    
                    // Add render_partial param only for the fetch, but we want clean URL in browser
                    // So we construct two URLs
                    url = `{{ route('siswa.index') }}?${params.toString()}`;
                
                    // Update browser URL without reload (clean URL)
                    if (updatePushState) {
                        window.history.pushState({}, '', url);
                    }
                    
                    // Add partial param for fetching
                    params.append('render_partial', '1');
                    fetchUrl = `{{ route('siswa.index') }}?${params.toString()}`;
                } else {
                    // If url is provided (pagination), append render_partial
                    const urlObj = new URL(url);
                    urlObj.searchParams.append('render_partial', '1');
                    fetchUrl = urlObj.toString();
                    
                    if (updatePushState) {
                         window.history.pushState({}, '', url);
                    }
                }

                try {
                    // Add X-Requested-With header to request partial view
                    const response = await fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    });
                    
                    if (response.ok) {
                        const html = await response.text();
                        const container = document.getElementById('siswa-table-container');
                        container.innerHTML = html;
                        // Clear selection
                        this.selected = [];
                        this.selectAll = false;
                    } else {
                        console.error('Failed to fetch data:', response.status);
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            resetFilters() {
                this.filters.search = '';
                this.filters.jurusan_id = '';
                this.filters.kelas_id = '';
            }
        }));
    });
</script>
@endpush
