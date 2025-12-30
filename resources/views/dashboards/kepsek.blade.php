@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')
@section('subtitle', 'Ringkasan eksekutif dan monitoring kedisiplinan siswa.')
@section('page-header', true)

@section('content')
<div class="space-y-6">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Siswa --}}
        <div class="stat-card">
            <div class="stat-card-icon primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Total Siswa</p>
                <p class="stat-card-value">{{ number_format($totalSiswa ?? 0) }}</p>
            </div>
        </div>
        
        {{-- Total Pelanggaran --}}
        <div class="stat-card">
            <div class="stat-card-icon danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Pelanggaran</p>
                <p class="stat-card-value">{{ number_format($totalPelanggaran ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Periode ini</p>
            </div>
        </div>
        
        {{-- Total Kasus --}}
        <div class="stat-card">
            <div class="stat-card-icon warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Kasus Aktif</p>
                <p class="stat-card-value">{{ number_format($totalKasus ?? 0) }}</p>
            </div>
        </div>
        
        {{-- Menunggu Persetujuan --}}
        <a href="{{ route('kepala-sekolah.approvals.index') }}" class="stat-card group">
            <div class="stat-card-icon success">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Persetujuan</p>
                <p class="stat-card-value">{{ number_format($totalKasusMenunggu ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Menunggu</p>
            </div>
        </a>
    </div>
    
    {{-- Filter & Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Trend Chart --}}
        <div class="lg:col-span-2 card">
            <div class="card-header">
                <h3 class="card-title">Tren Pelanggaran 6 Bulan Terakhir</h3>
            </div>
            <div class="card-body">
                <canvas id="chartTrend" height="250"></canvas>
            </div>
        </div>
        
        {{-- Pelanggaran Populer --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Pelanggaran</h3>
            </div>
            <div class="card-body">
                <canvas id="chartPelanggaran" height="250"></canvas>
            </div>
        </div>
    </div>
    
    {{-- Pelanggaran Per Jurusan --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pelanggaran Per Jurusan</h3>
        </div>
        <div class="card-body">
            <canvas id="chartJurusan" height="100"></canvas>
        </div>
    </div>
    
    {{-- Kasus Menunggu Persetujuan --}}
    @if(($kasusMenunggu ?? collect())->count() > 0)
        <div class="card border-amber-200 bg-amber-50/50">
            <div class="card-header">
                <h3 class="card-title text-amber-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Menunggu Persetujuan Anda
                </h3>
                <a href="{{ route('kepala-sekolah.approvals.index') }}" class="btn btn-sm btn-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="table-container !rounded-none !border-0 !bg-transparent">
                <table class="table">
                    <thead class="bg-amber-100/50">
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th class="w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($kasusMenunggu->take(5) as $kasus)
                            <tr>
                                <td class="font-medium">{{ $kasus->siswa->nama_siswa ?? '-' }}</td>
                                <td><span class="badge badge-primary">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }}</span></td>
                                <td class="text-gray-500">{{ $kasus->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="btn btn-sm btn-primary">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart Trend
    const ctxTrend = document.getElementById('chartTrend');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: @json($chartTrendLabels ?? []),
                datasets: [{
                    label: 'Jumlah Pelanggaran',
                    data: @json($chartTrendData ?? []),
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
    
    // Chart Pelanggaran
    const ctxPelanggaran = document.getElementById('chartPelanggaran');
    if (ctxPelanggaran) {
        new Chart(ctxPelanggaran, {
            type: 'doughnut',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [{
                    data: @json($chartData ?? []),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)', 'rgba(139, 92, 246, 0.8)', 'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)', 'rgba(249, 115, 22, 0.8)', 'rgba(99, 102, 241, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } } }
            }
        });
    }
    
    // Chart Jurusan
    const ctxJurusan = document.getElementById('chartJurusan');
    if (ctxJurusan) {
        new Chart(ctxJurusan, {
            type: 'bar',
            data: {
                labels: @json($chartJurusanLabels ?? []),
                datasets: [{
                    label: 'Jumlah Pelanggaran',
                    data: @json($chartJurusanData ?? []),
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
