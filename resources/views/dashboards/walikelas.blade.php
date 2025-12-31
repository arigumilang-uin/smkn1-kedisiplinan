@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')
@section('subtitle', 'Monitoring siswa kelas {{ $kelas->nama_kelas ?? "" }}')
@section('page-header', false)

@section('content')
<div class="space-y-6" x-data="walikelasDashboard()">
    {{-- Class Info Banner --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 p-6 overflow-hidden text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-emerald-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium text-emerald-100 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                    Wali Kelas Panel
                </div>
                <h2 class="text-2xl font-bold">{{ $kelas->nama_kelas ?? 'Belum ada kelas' }}</h2>
                <p class="text-emerald-100 text-sm mt-1">{{ $kelas->jurusan->nama_jurusan ?? '' }}</p>
            </div>
            
            <a href="{{ route('siswa.index') }}" class="btn bg-white/10 backdrop-blur-md text-white border border-white/20 hover:bg-white/20 shadow-lg group">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
                <span>Lihat Data Siswa</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 group-hover:translate-x-1 transition-transform">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>
    </div>
    
    {{-- Statistics Cards --}}
    <div id="stats-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._walikelas_stats')
    </div>
    
    {{-- Filter Data (Date Only) --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['start_date', 'end_date']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer select-none" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <h3 class="card-title">Filter Periode</h3>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat Data...</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>
        </div>
        <div class="card-body" x-show="expanded" x-collapse>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-group">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" x-model="filters.start_date" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" x-model="filters.end_date" class="form-input">
                </div>
                <div class="form-group">
                    <button type="button" @click="resetFilters()" class="btn btn-secondary w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        {{-- Pelanggaran Chart --}}
        <div class="card h-full flex flex-col">
            <div class="card-header border-b border-gray-100">
                <h3 class="card-title">Statistik Pelanggaran</h3>
                <span class="text-xs text-gray-500 font-normal">Top 10 Jenis</span>
            </div>
            <div class="card-body flex-1 min-h-[300px] relative flex items-center justify-center">
                 <canvas id="chartPelanggaran"></canvas>
            </div>
        </div>
        
        {{-- Kasus Terbaru --}}
        <div id="table-container" class="h-full">
            @include('dashboards._walikelas_table')
        </div>
    </div>
    
    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('riwayat.create') }}" class="card flex items-center gap-4 p-4 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-100/50 transition-all group">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Catat Pelanggaran</h4>
                <p class="text-sm text-gray-500">Input pelanggaran siswa baru</p>
            </div>
        </a>
        
        <a href="{{ route('pembinaan.index') }}" class="card flex items-center gap-4 p-4 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-100/50 transition-all group">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-gray-800 group-hover:text-emerald-600 transition-colors">Siswa Pembinaan</h4>
                <p class="text-sm text-gray-500">Lihat milestone pembinaan</p>
            </div>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartInstances = {};

    document.addEventListener('alpine:init', () => {
        Alpine.data('walikelasDashboard', () => ({
            isLoading: false,
            filters: {
                start_date: '{{ $startDate }}',
                end_date: '{{ $endDate }}',
            },

            init() {
                this.initCharts();
                
                this.$watch('filters.start_date', () => this.fetchData());
                this.$watch('filters.end_date', () => this.fetchData());
            },

            async fetchData() {
                this.isLoading = true;
                const params = new URLSearchParams(this.filters);
                const url = `{{ route('dashboard.walikelas') }}?${params.toString()}`;
                window.history.pushState({}, '', url);

                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (response.ok) {
                        const data = await response.json();
                        
                        document.getElementById('stats-container').innerHTML = data.stats;
                        document.getElementById('table-container').innerHTML = data.table;
                        
                        this.updateCharts(data.charts);
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    this.isLoading = false;
                }
            },
            
            resetFilters() {
                this.filters.start_date = '{{ date("Y-m-01") }}';
                this.filters.end_date = '{{ date("Y-m-d") }}';
            },

            initCharts() {
                const ctxPelanggaran = document.getElementById('chartPelanggaran');
                if (ctxPelanggaran) {
                    chartInstances.pelanggaran = new Chart(ctxPelanggaran, {
                        type: 'doughnut',
                        data: {
                            labels: @json($chartLabels ?? []),
                            datasets: [{
                                data: @json($chartData ?? []),
                                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#22c55e'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 20, font: { size: 11 } } } },
                            cutout: '70%'
                        }
                    });
                }
            },
            
            updateCharts(data) {
                if (chartInstances.pelanggaran && data.pelanggaran) {
                    chartInstances.pelanggaran.data.labels = data.pelanggaran.labels;
                    chartInstances.pelanggaran.data.datasets[0].data = data.pelanggaran.data;
                    chartInstances.pelanggaran.update();
                }
            }
        }));
    });
</script>
@endpush
