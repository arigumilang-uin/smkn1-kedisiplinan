{{-- Tab: Last Login --}}
<div class="space-y-6" x-data="activityLastLoginPage()">
    {{-- Filter --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'role_id']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Pengguna</span>
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
                {{-- Search --}}
                <div class="form-group md:col-span-2">
                    <label class="form-label">Cari Pengguna</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model.debounce.500ms="filters.search" 
                            class="form-input pr-10 w-full" 
                            placeholder="Nama, Username, atau Email..."
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isLoading">
                            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                {{-- Role --}}
                <div class="form-group md:col-span-2">
                    <label class="form-label">Role / Jabatan</label>
                    <select x-model="filters.role_id" class="form-input form-select w-full">
                        <option value="">Semua Role</option>
                        @foreach($roles ?? [] as $role)
                            <option value="{{ $role->id }}">{{ $role->nama_role }}</option>
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

    {{-- Table --}}
    <div id="last-login-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('kepala_sekolah.activity._table_last_login')
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('activityLastLoginPage', () => ({
            isLoading: false,
            filters: {
                search: '{{ request('search') }}',
                role_id: '{{ request('role_id') }}',
            },

            init() {
                // Watchers
                this.$watch('filters.search', () => this.fetchData());
                this.$watch('filters.role_id', () => this.fetchData());

                window.addEventListener('popstate', (event) => {
                    this.fetchData(window.location.href, false);
                });

                const container = document.getElementById('last-login-table-container');
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
                    params.append('tab', 'last-login'); // Force correct tab
                    
                    if (this.filters.search) params.append('search', this.filters.search);
                    if (this.filters.role_id) params.append('role_id', this.filters.role_id);
                    
                    url = `{{ route('audit.activity.index') }}?${params.toString()}`;
                    
                    if (updatePushState) {
                        window.history.pushState({}, '', url);
                    }
                    
                    params.append('render_partial', '1');
                    fetchUrl = `{{ route('audit.activity.index') }}?${params.toString()}`;
                } else {
                    const urlObj = new URL(url);
                    urlObj.searchParams.append('render_partial', '1');
                    if (!urlObj.searchParams.has('tab')) {
                        urlObj.searchParams.append('tab', 'last-login');
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
                        document.getElementById('last-login-table-container').innerHTML = html;
                    }
                } catch (error) {
                    console.error('Error fetching data:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            resetFilters() {
                this.filters.search = '';
                this.filters.role_id = '';
            }
        }));
    });
</script>
@endpush
