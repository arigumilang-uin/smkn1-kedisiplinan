@extends('layouts.app')

@section('title', 'Dashboard Waka Kesiswaan')
@section('subtitle', 'Monitoring disiplin siswa dan kasus pembinaan.')
@section('page-header', false)

@section('content')
<div class="space-y-6" x-data="wakaDashboard()">
    {{-- Welcome Banner --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 overflow-hidden text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium text-blue-200 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Waka Kesiswaan Panel
                </div>
                <h2 class="text-xl md:text-2xl font-bold">
                    Halo, {{ auth()->user()->username ?? 'Waka Kesiswaan' }}! 👋
                </h2>
                <p class="text-blue-100 text-sm opacity-80 mt-1">
                    Panel monitoring kedisiplinan dan pembinaan karakter siswa.
                </p>
            </div>
            
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner">
                <div class="bg-blue-500/20 p-2 rounded-lg text-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                    <span class="block text-xs uppercase tracking-wider text-blue-200">{{ date('F Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div id="stats-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._waka_stats')
    </div>

    {{-- Filter Section (Collapsible) --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['start_date', 'end_date', 'jurusan_id', 'kelas_id']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Analisis Data</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat Data...</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>
        </div>
        <div class="card-body" x-show="expanded" x-collapse>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div class="form-group">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" x-model="filters.start_date" class="form-input">
                </div>
                <div class="form-group">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" x-model="filters.end_date" class="form-input">
                </div>
                <div class="form-group">
                    <label for="jurusan_id" class="form-label">Jurusan</label>
                    <select x-model="filters.jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($allJurusan ?? [] as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select x-model="filters.kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group flex justify-end">
                    <button type="button" @click="resetFilters()" class="btn btn-secondary w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>
                        </svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        {{-- Pelanggaran Populer --}}
        <div class="card h-full flex flex-col">
            <div class="card-header border-b border-gray-100">
                <h3 class="card-title">Statistik Pelanggaran</h3>
                <span class="text-xs text-gray-500 font-normal">Top 10 Jenis</span>
            </div>
            <div class="card-body flex-1 min-h-[300px] relative">
                <canvas id="chartPelanggaran"></canvas>
            </div>
        </div>
        
        {{-- Kelas Ternakal --}}
        <div class="card h-full flex flex-col">
            <div class="card-header border-b border-gray-100">
                <h3 class="card-title">Statistik Kelas</h3>
                <span class="text-xs text-gray-500 font-normal">Top 10 Kelas Pelanggaran</span>
            </div>
            <div class="card-body flex-1 min-h-[300px] relative">
                <canvas id="chartKelas"></canvas>
            </div>
        </div>
    </div>
    
    {{-- Kasus Terbaru Table --}}
    <div id="table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._waka_table')
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Global Chart Instances
    let chartInstances = {};

    document.addEventListener('alpine:init', () => {
        Alpine.data('wakaDashboard', () => ({
            isLoading: false,
            filters: {
                start_date: '{{ $startDate }}',
                end_date: '{{ $endDate }}',
                jurusan_id: '{{ request('jurusan_id') }}',
                kelas_id: '{{ request('kelas_id') }}',
            },

            init() {
                this.initCharts();

                // Watchers for Live Filtering
                this.$watch('filters.start_date', () => this.fetchData());
                this.$watch('filters.end_date', () => this.fetchData());
                this.$watch('filters.jurusan_id', () => this.fetchData());
                this.$watch('filters.kelas_id', () => this.fetchData());
            },

            async fetchData() {
                this.isLoading = true;
                
                const params = new URLSearchParams(this.filters);
                const url = `{{ route('dashboard.admin') }}?${params.toString()}`;
                
                // Update URL without reload
                window.history.pushState({}, '', url);

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });
                    
                    if (response.ok) {
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            const data = await response.json();
                            
                            // Update Stats & Table HTML
                            document.getElementById('stats-container').innerHTML = data.stats;
                            document.getElementById('table-container').innerHTML = data.table;
                            
                            // Update Charts
                            this.updateCharts(data.charts);
                        } else {
                            // Fallback if not JSON (redirect or reload)
                             window.location.reload();
                        }
                    }
                } catch (error) {
                    console.error('Error fetching dashboard data:', error);
                } finally {
                    this.isLoading = false;
                }
            },
            
            resetFilters() {
                this.filters.start_date = '{{ date("Y-m-01") }}';
                this.filters.end_date = '{{ date("Y-m-d") }}';
                this.filters.jurusan_id = '';
                this.filters.kelas_id = '';
            },

            initCharts() {
                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 20,
                                font: { size: 11 }
                            }
                        }
                    }
                };

                // Chart Pelanggaran
                const ctxPelanggaran = document.getElementById('chartPelanggaran');
                if (ctxPelanggaran) {
                    chartInstances.pelanggaran = new Chart(ctxPelanggaran, {
                        type: 'doughnut',
                        data: {
                            labels: @json($chartLabels ?? []),
                            datasets: [{
                                data: @json($chartData ?? []),
                                backgroundColor: [
                                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                                    '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', 
                                    '#6366f1', '#22c55e'
                                ],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            ...commonOptions,
                            cutout: '70%',
                        }
                    });
                }
                
                // Chart Kelas
                const ctxKelas = document.getElementById('chartKelas');
                if (ctxKelas) {
                    chartInstances.kelas = new Chart(ctxKelas, {
                        type: 'bar',
                        data: {
                            labels: @json($chartKelasLabels ?? []),
                            datasets: [{
                                label: 'Jumlah Pelanggaran',
                                data: @json($chartKelasData ?? []),
                                backgroundColor: '#ef4444',
                                borderRadius: 6,
                                barThickness: 20
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grid: { display: false }
                                },
                                y: {
                                    grid: { borderDash: [2, 2] },
                                    ticks: { font: { size: 11 } }
                                }
                            }
                        }
                    });
                }
            },

            updateCharts(data) {
                // Update Pelanggaran Chart
                if (chartInstances.pelanggaran && data.pelanggaran) {
                    chartInstances.pelanggaran.data.labels = data.pelanggaran.labels;
                    chartInstances.pelanggaran.data.datasets[0].data = data.pelanggaran.data;
                    chartInstances.pelanggaran.update();
                }

                // Update Kelas Chart
                if (chartInstances.kelas && data.kelas) {
                    chartInstances.kelas.data.labels = data.kelas.labels;
                    chartInstances.kelas.data.datasets[0].data = data.kelas.data;
                    chartInstances.kelas.update();
                }
            }
        }));
    });
</script>
@endpush
