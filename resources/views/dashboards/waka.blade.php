@extends('layouts.app')

@section('title', 'Dashboard Waka Kesiswaan')
@section('subtitle', 'Monitoring disiplin siswa dan kasus pembinaan.')
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
        
        {{-- Pelanggaran Periode --}}
        <div class="stat-card">
            <div class="stat-card-icon danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Pelanggaran</p>
                <p class="stat-card-value">{{ number_format($pelanggaranFiltered ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Periode ini</p>
            </div>
        </div>
        
        {{-- Kasus Aktif --}}
        <div class="stat-card">
            <div class="stat-card-icon warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Kasus Aktif</p>
                <p class="stat-card-value">{{ number_format($kasusAktif ?? 0) }}</p>
            </div>
        </div>
        
        {{-- Butuh Persetujuan --}}
        <a href="{{ route('kepala-sekolah.approvals.index') }}" class="stat-card group">
            <div class="stat-card-icon success">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Persetujuan</p>
                <p class="stat-card-value">{{ number_format($butuhPersetujuan ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Menunggu</p>
            </div>
        </a>
    </div>
    
    {{-- Filter Section --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Filter Data
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.admin') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="form-group">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-input">
                </div>
                <div class="form-group">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-input">
                </div>
                <div class="form-group">
                    <label for="jurusan_id" class="form-label">Jurusan</label>
                    <select id="jurusan_id" name="jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($allJurusan ?? [] as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group flex items-end">
                    <button type="submit" class="btn btn-primary w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pelanggaran Populer --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top 10 Pelanggaran</h3>
            </div>
            <div class="card-body">
                <canvas id="chartPelanggaran" height="300"></canvas>
            </div>
        </div>
        
        {{-- Kelas Ternakal --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top 10 Kelas</h3>
            </div>
            <div class="card-body">
                <canvas id="chartKelas" height="300"></canvas>
            </div>
        </div>
    </div>
    
    {{-- Daftar Kasus Terbaru --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kasus Terbaru</h3>
            <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-sm btn-secondary">
                Lihat Semua
            </a>
        </div>
        <div class="table-container !rounded-none !border-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarKasus ?? [] as $kasus)
                        <tr>
                            <td class="font-medium">{{ $kasus->siswa->nama_siswa ?? '-' }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'Baru' => 'badge-info',
                                        'Menunggu Persetujuan' => 'badge-warning',
                                        'Disetujui' => 'badge-success',
                                        'Ditangani' => 'badge-primary',
                                        'Selesai' => 'badge-neutral',
                                        'Ditolak' => 'badge-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$kasus->status] ?? 'badge-neutral' }}">
                                    {{ $kasus->status }}
                                </span>
                            </td>
                            <td class="text-gray-500 text-sm">{{ $kasus->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="btn btn-icon btn-outline">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                Tidak ada kasus dalam periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart Pelanggaran
    const ctxPelanggaran = document.getElementById('chartPelanggaran');
    if (ctxPelanggaran) {
        new Chart(ctxPelanggaran, {
            type: '{{ $chartType ?? "doughnut" }}',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [{
                    data: @json($chartData ?? []),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    }
                }
            }
        });
    }
    
    // Chart Kelas
    const ctxKelas = document.getElementById('chartKelas');
    if (ctxKelas) {
        new Chart(ctxKelas, {
            type: 'bar',
            data: {
                labels: @json($chartKelasLabels ?? []),
                datasets: [{
                    label: 'Jumlah Pelanggaran',
                    data: @json($chartKelasData ?? []),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
