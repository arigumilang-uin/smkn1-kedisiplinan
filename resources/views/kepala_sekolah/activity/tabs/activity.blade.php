{{-- Tab: Activity Logs --}}
<div class="space-y-6" x-data="activityLogPage()">
    {{-- Filter --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'type', 'dari_tanggal', 'sampai_tanggal']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Parameter</span>
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
                    <label class="form-label">Cari Deskripsi / User</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model.debounce.500ms="filters.search" 
                            class="form-input pr-10 w-full" 
                            placeholder="Kata kunci..."
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isLoading">
                            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="form-group md:col-span-2">
                    <label class="form-label">Jenis Log</label>
                    <select x-model="filters.type" class="form-input form-select w-full">
                        <option value="">Semua Jenis</option>
                        @foreach($activityTypes ?? [] as $type)
                            <option value="{{ $type }}">
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group md:col-span-2">
                    <label class="form-label">Dari</label>
                    <input type="date" x-model="filters.dari_tanggal" class="form-input w-full">
                </div>

                <div class="form-group md:col-span-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" x-model="filters.sampai_tanggal" class="form-input w-full">
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
    <div id="activity-logs-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('kepala_sekolah.activity._table_logs')
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('activityLogPage', () => ({
            isLoading: false,
            filters: {
                search: '{{ request('search') }}',
                type: '{{ request('type') }}',
                dari_tanggal: '{{ request('dari_tanggal') }}',
                sampai_tanggal: '{{ request('sampai_tanggal') }}'
            },

            init() {
                // Watchers
                this.$watch('filters.search', () => this.fetchData());
                this.$watch('filters.type', () => this.fetchData());
                this.$watch('filters.dari_tanggal', () => this.fetchData());
                this.$watch('filters.sampai_tanggal', () => this.fetchData());

                window.addEventListener('popstate', (event) => {
                    this.fetchData(window.location.href, false);
                });

                const container = document.getElementById('activity-logs-table-container');
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
                    params.append('tab', 'activity'); // Ensure tab is preserved
                    if (this.filters.search) params.append('search', this.filters.search);
                    if (this.filters.type) params.append('type', this.filters.type);
                    if (this.filters.dari_tanggal) params.append('dari_tanggal', this.filters.dari_tanggal);
                    if (this.filters.sampai_tanggal) params.append('sampai_tanggal', this.filters.sampai_tanggal);
                    
                    url = `{{ route('audit.activity.index') }}?${params.toString()}`;
                    
                    if (updatePushState) {
                        window.history.pushState({}, '', url);
                    }
                    
                    params.append('render_partial', '1');
                    fetchUrl = `{{ route('audit.activity.index') }}?${params.toString()}`;
                } else {
                    const urlObj = new URL(url);
                    urlObj.searchParams.append('render_partial', '1');
                    // Ensure tab param exists
                    if (!urlObj.searchParams.has('tab')) {
                        urlObj.searchParams.append('tab', 'activity');
                    }
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
                        document.getElementById('activity-logs-table-container').innerHTML = html;
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            resetFilters() {
                this.filters.search = '';
                this.filters.type = '';
                this.filters.dari_tanggal = '';
                this.filters.sampai_tanggal = '';
            }
        }));
    });
</script>
@endpush
