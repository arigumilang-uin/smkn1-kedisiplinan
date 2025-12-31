@extends('layouts.app')

@section('title', 'Kelola Aturan Pelanggaran')
@section('subtitle', 'Atur jenis pelanggaran, poin, sanksi, dan frequency rules.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('jenis-pelanggaran.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        <span>Tambah Jenis Pelanggaran</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="frequencyRulesPage()">
    {{-- Filter --}}
    <div class="card" x-data="{ expanded: {{ request()->has('kategori_id') ? 'true' : 'false' }} }">
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
        
        <div class="card-body" x-show="expanded" x-collapse>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group md:col-span-2">
                    <label for="kategori_id" class="form-label">Filter Kategori</label>
                    <div class="relative">
                        <select 
                            id="kategori_id" 
                            x-model="filters.kategori_id" 
                            class="form-input form-select w-full"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris ?? [] as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-8 flex items-center pointer-events-none" x-show="isLoading">
                             <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
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

    {{-- Table --}}
    <div id="frequency-rules-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('frequency-rules._table')
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('frequencyRulesPage', () => ({
            isLoading: false,
            filters: {
                kategori_id: '{{ $kategoriId ?? '' }}'
            },

            init() {
                this.$watch('filters.kategori_id', () => this.fetchData());

                window.addEventListener('popstate', (event) => {
                    this.fetchData(window.location.href, false);
                });
            },

            async fetchData(url = null, updatePushState = true) {
                this.isLoading = true;
                
                // Get fresh value from DOM because x-model might be slighty behind in some edge cases or just to be safe
                // Actually x-model is fine normally.
                
                if (!url) {
                    const params = new URLSearchParams();
                    if (this.filters.kategori_id) params.append('kategori_id', this.filters.kategori_id);
                    
                    url = `{{ route('frequency-rules.index') }}?${params.toString()}`;
                    
                    if (updatePushState) {
                        window.history.pushState({}, '', url);
                    }
                    
                    params.append('render_partial', '1');
                    fetchUrl = `{{ route('frequency-rules.index') }}?${params.toString()}`;
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
                        document.getElementById('frequency-rules-table-container').innerHTML = html;
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            resetFilters() {
                this.filters.kategori_id = '';
            }
        }));
    });
</script>
@endpush
@endsection
