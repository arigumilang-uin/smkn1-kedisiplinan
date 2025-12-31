@extends('layouts.app')

@section('title', 'Arsip Siswa')
@section('subtitle', 'Siswa yang telah dihapus (soft-delete). Dapat di-restore atau dihapus permanen.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
        </svg>
        <span>Kembali ke Data Siswa</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="siswaArsipPage()">
    {{-- Info Banner --}}
    <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl">
        <div class="flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600 shrink-0 mt-0.5">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
            <div>
                <p class="font-medium text-amber-800">Catatan Penting</p>
                <p class="text-sm text-amber-700 mt-1">
                    Halaman ini menampilkan siswa yang telah dihapus. Anda dapat me-<strong>restore</strong> siswa kembali ke daftar aktif, 
                    atau menghapus secara <strong>permanen</strong> dari database (tidak dapat dikembalikan).
                </p>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'kelas_id', 'alasan_keluar']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Arsip</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat...</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>
        </div>

        <div class="card-body" x-show="expanded" x-collapse>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group md:col-span-2">
                    <label for="search" class="form-label">Cari</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="search" 
                            x-model.debounce.500ms="filters.search" 
                            class="form-input pr-10 w-full" 
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
                
                <div class="form-group">
                    <label for="alasan_keluar" class="form-label">Alasan Keluar</label>
                    <select id="alasan_keluar" x-model="filters.alasan_keluar" class="form-input form-select w-full">
                        <option value="">Semua Alasan</option>
                        @foreach($alasanOptions ?? [] as $alasan)
                            <option value="{{ $alasan }}">{{ $alasan }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" x-model="filters.kelas_id" class="form-input form-select w-full">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
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

    {{-- Data Table Container --}}
    <div id="siswa-deleted-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('siswa._table_deleted')
    </div>
</div>

{{-- Permanent Delete Modal --}}
<div 
    x-data="{ 
        open: false, 
        siswaId: null, 
        siswaName: '', 
        siswaNisn: '',
        confirmed: false
    }"
    @open-permanent-delete-modal.window="
        open = true; 
        siswaId = $event.detail.id; 
        siswaName = $event.detail.nama; 
        siswaNisn = $event.detail.nisn;
        confirmed = false;
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-600">⚠️ Hapus Permanen</h3>
                        <p class="text-sm text-gray-500">Data tidak dapat dikembalikan!</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form :action="'/siswa/' + siswaId + '/force-delete'" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="p-6 space-y-4">
                    {{-- Siswa Info --}}
                    <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                        <p class="text-sm text-red-600">Siswa yang akan dihapus permanen:</p>
                        <p class="font-semibold text-red-800" x-text="siswaName"></p>
                        <p class="text-sm text-red-600 font-mono" x-text="'NISN: ' + siswaNisn"></p>
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-gray-800">⚠️ Tindakan ini akan menghapus:</p>
                        <ul class="text-sm text-gray-600 space-y-1 pl-4">
                            <li>• Data siswa secara permanen</li>
                            <li>• Semua riwayat pelanggaran terkait</li>
                            <li>• Semua kasus tindak lanjut terkait</li>
                        </ul>
                    </div>
                    
                    {{-- Confirmation Checkbox --}}
                    <label class="flex items-start gap-3 cursor-pointer p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <input 
                            type="checkbox" 
                            name="confirm_permanent" 
                            value="1" 
                            x-model="confirmed"
                            class="w-4 h-4 mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500"
                        >
                        <span class="text-sm text-amber-800">
                            Saya mengerti bahwa tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong> dan semua data akan dihapus permanen.
                        </span>
                    </label>
                </div>
                
                {{-- Footer --}}
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button 
                        type="submit" 
                        class="btn btn-danger"
                        :disabled="!confirmed"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        <span>Hapus Permanen</span>
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
        Alpine.data('siswaArsipPage', () => ({
            isLoading: false,
            filters: {
                search: '{{ request('search') }}',
                kelas_id: '{{ request('kelas_id') }}',
                alasan_keluar: '{{ request('alasan_keluar') }}'
            },

            init() {
                // Watcher Pattern
                this.$watch('filters.search', () => this.fetchData());
                this.$watch('filters.kelas_id', () => this.fetchData());
                this.$watch('filters.alasan_keluar', () => this.fetchData());

                window.addEventListener('popstate', (event) => {
                    this.fetchData(window.location.href, false);
                });

                const container = document.getElementById('siswa-deleted-table-container');
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

            async fetchData(url = null, updatePushState = true) {
                this.isLoading = true;
                
                if (!url) {
                    const params = new URLSearchParams();
                    if (this.filters.search) params.append('search', this.filters.search);
                    if (this.filters.kelas_id) params.append('kelas_id', this.filters.kelas_id);
                    if (this.filters.alasan_keluar) params.append('alasan_keluar', this.filters.alasan_keluar);
                    
                    url = `{{ route('siswa.deleted') }}?${params.toString()}`;
                    
                    if (updatePushState) {
                        window.history.pushState({}, '', url);
                    }
                    
                    params.append('render_partial', '1');
                    fetchUrl = `{{ route('siswa.deleted') }}?${params.toString()}`;
                } else {
                    const urlObj = new URL(url);
                    urlObj.searchParams.append('render_partial', '1');
                    fetchUrl = urlObj.toString();
                    
                    if (updatePushState) {
                         window.history.pushState({}, '', url);
                    }
                }

                try {
                    const response = await fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    });
                    
                    if (response.ok) {
                        const html = await response.text();
                        document.getElementById('siswa-deleted-table-container').innerHTML = html;
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            resetFilters() {
                this.filters.search = '';
                this.filters.kelas_id = '';
                this.filters.alasan_keluar = '';
            }
        }));
    });
</script>
@endpush
