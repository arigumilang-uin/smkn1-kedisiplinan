@extends('layouts.app')

@section('title', 'Log Pelanggaran')
@section('subtitle', 'Riwayat pencatatan pelanggaran siswa.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('riwayat.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/><path d="M12 5v14"/>
        </svg>
        <span>Catat Pelanggaran</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="riwayatPage()">
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
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
                    <div class="form-group md:col-span-4">
                        <label for="search" class="form-label">Cari</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="search" 
                                x-model.debounce.500ms="filters.search" 
                                class="form-input pr-10 w-full" 
                                placeholder="Cari nama siswa, NISN, jenis pelanggaran, atau pencatat..."
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isLoading">
                                <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Dari Tanggal --}}
                    <div class="form-group">
                        <label for="start_date" class="form-label">Dari Tanggal</label>
                        <input 
                            type="date" 
                            id="start_date" 
                            x-model="filters.start_date"
                            @change="fetchData()"
                            class="form-input w-full"
                        >
                    </div>
                    
                    {{-- Sampai Tanggal --}}
                    <div class="form-group">
                        <label for="end_date" class="form-label">Sampai Tanggal</label>
                        <input 
                            type="date" 
                            id="end_date" 
                            x-model="filters.end_date"
                            @change="fetchData()"
                            class="form-input w-full"
                        >
                    </div>
                    
                    {{-- Jurusan Dropdown --}}
                    <div class="form-group">
                        <label for="jurusan_id" class="form-label">Jurusan</label>
                        <select id="jurusan_id" x-model="filters.jurusan_id" @change="onJurusanChange()" class="form-input form-select w-full">
                            <option value="">Semua Jurusan</option>
                            @foreach($allJurusan ?? [] as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Kelas Dropdown (Dynamic) --}}
                    <div class="form-group">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select id="kelas_id" x-model="filters.kelas_id" class="form-input form-select w-full" :disabled="loadingKelas">
                            <option value="">Semua Kelas</option>
                            <template x-for="kelas in kelasList" :key="kelas.id">
                                <option :value="kelas.id" x-text="kelas.nama_kelas"></option>
                            </template>
                        </select>
                        <p class="text-xs text-blue-500 mt-1" x-show="loadingKelas">Memuat kelas...</p>
                    </div>
                    
                    {{-- Reset Button --}}
                    <div class="md:col-span-4 flex justify-end">
                        <button type="button" @click="resetFilters()" class="btn btn-secondary">
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
    
    {{-- Table Container --}}
    <div id="riwayat-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('riwayat._table')
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('riwayatPage', () => ({
            isLoading: false,
            loadingKelas: false,
            filters: {
                search: '{{ request('search') }}',
                jurusan_id: '{{ request('jurusan_id') }}',
                kelas_id: '{{ request('kelas_id') }}',
                start_date: '{{ request('start_date') }}',
                end_date: '{{ request('end_date') }}'
            },
            kelasList: @json($allKelas ?? []),
            allKelasOriginal: @json($allKelas ?? []),
            
            // Selection State
            selectionMode: false,
            selected: [],
            selectAll: false,

            toggleSelectionMode() {
                this.selectionMode = !this.selectionMode;
                if (!this.selectionMode) {
                    this.selected = [];
                    this.selectAll = false;
                }
            },
            
            // Toggle Select All logic will be handled within the table component or updated here
            // We'll trust the child component to push/splice 'selected' array.

            init() {
                this.$watch('filters.search', () => this.fetchData());
                this.$watch('filters.kelas_id', () => this.fetchData());

                // Handle browser back/forward
                window.addEventListener('popstate', (event) => {
                    this.fetchData(window.location.href, false);
                });

                // Handle pagination clicks
                const container = document.getElementById('riwayat-table-container');
                if (container) {
                    container.addEventListener('click', (e) => {
                        const link = e.target.closest('.pagination a');
                        if (link) {
                            e.preventDefault();
                            this.fetchData(link.href);
                        }
                    });
                }

                // Load filtered kelas if jurusan already selected
                if (this.filters.jurusan_id) {
                    this.loadKelasByJurusan(this.filters.jurusan_id);
                }
            },

            onJurusanChange() {
                this.filters.kelas_id = ''; // Reset kelas selection
                if (this.filters.jurusan_id) {
                    this.loadKelasByJurusan(this.filters.jurusan_id);
                } else {
                    this.kelasList = this.allKelasOriginal;
                }
                this.fetchData();
            },

            async loadKelasByJurusan(jurusanId) {
                this.loadingKelas = true;
                try {
                    const response = await fetch(`/api/kelas-by-jurusan?jurusan_id=${jurusanId}`);
                    if (response.ok) {
                        this.kelasList = await response.json();
                    }
                } catch (error) {
                    console.error('Error loading kelas:', error);
                    this.kelasList = this.allKelasOriginal;
                } finally {
                    this.loadingKelas = false;
                }
            },

            async fetchData(url = null, updatePushState = true) {
                this.isLoading = true;
                let fetchUrl;
                
                if (!url) {
                    const params = new URLSearchParams();
                    if (this.filters.search) params.append('search', this.filters.search);
                    if (this.filters.jurusan_id) params.append('jurusan_id', this.filters.jurusan_id);
                    if (this.filters.kelas_id) params.append('kelas_id', this.filters.kelas_id);
                    if (this.filters.start_date) params.append('start_date', this.filters.start_date);
                    if (this.filters.end_date) params.append('end_date', this.filters.end_date);
                    
                    url = `{{ route('riwayat.index') }}?${params.toString()}`;
                    
                    if (updatePushState) {
                        window.history.pushState({}, '', url);
                    }
                    
                    params.append('render_partial', '1');
                    fetchUrl = `{{ route('riwayat.index') }}?${params.toString()}`;
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
                        document.getElementById('riwayat-table-container').innerHTML = html;
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
                this.filters.start_date = '';
                this.filters.end_date = '';
                this.kelasList = this.allKelasOriginal;
            }
        }));
    });
</script>
@endpush
