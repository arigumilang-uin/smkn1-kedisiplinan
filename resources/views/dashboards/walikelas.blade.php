@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')
@section('subtitle', 'Monitoring siswa kelas {{ $kelas->nama_kelas ?? "" }}')
@section('page-header', true)

@section('content')
<div class="space-y-6">
    {{-- Class Info Banner --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-6 overflow-hidden text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-20 -mt-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <p class="text-emerald-100 text-sm mb-1">Kelas yang Diampu</p>
                <h2 class="text-2xl font-bold">{{ $kelas->nama_kelas ?? 'Belum ada kelas' }}</h2>
                <p class="text-emerald-100 text-sm mt-1">{{ $kelas->jurusan->nama_jurusan ?? '' }}</p>
            </div>
            
            <a href="{{ route('siswa.index') }}" class="btn bg-white/20 backdrop-blur-sm text-white border border-white/20 hover:bg-white/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
                <span>Lihat Data Siswa</span>
            </a>
        </div>
    </div>
    
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="stat-card">
            <div class="stat-card-icon primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Siswa di Kelas</p>
                <p class="stat-card-value">{{ number_format($totalSiswa ?? 0) }}</p>
            </div>
        </div>
        
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
        
        <div class="stat-card">
            <div class="stat-card-icon warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Kasus Aktif</p>
                <p class="stat-card-value">{{ number_format($totalKasus ?? 0) }}</p>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pelanggaran Chart --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top 10 Pelanggaran di Kelas</h3>
            </div>
            <div class="card-body">
                @if(($chartData ?? collect())->count() > 0)
                    <canvas id="chartPelanggaran" height="300"></canvas>
                @else
                    <div class="empty-state py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                        </svg>
                        <h3 class="empty-state-title">Tidak Ada Pelanggaran</h3>
                        <p class="empty-state-description">Belum ada data pelanggaran pada periode ini.</p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Kasus Terbaru --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kasus Perlu Ditangani</h3>
                <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($kasusBaru ?? [] as $kasus)
                    <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $kasus->siswa->nama_siswa ?? '-' }}</p>
                            <p class="text-sm text-gray-500">{{ $kasus->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="badge badge-{{ $kasus->status === 'Baru' ? 'info' : 'warning' }}">{{ $kasus->status }}</span>
                    </a>
                @empty
                    <div class="empty-state py-8">
                        <p class="text-gray-500">Tidak ada kasus yang perlu ditangani.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('riwayat.create') }}" class="card flex items-center gap-4 p-4 hover:border-blue-200 hover:shadow-lg transition-all group">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 group-hover:text-blue-600">Catat Pelanggaran</h4>
                <p class="text-sm text-gray-500">Catat pelanggaran siswa baru</p>
            </div>
        </a>
        
        <a href="{{ route('pembinaan.index') }}" class="card flex items-center gap-4 p-4 hover:border-emerald-200 hover:shadow-lg transition-all group">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 group-hover:text-emerald-600">Siswa Pembinaan</h4>
                <p class="text-sm text-gray-500">Lihat siswa perlu pembinaan</p>
            </div>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartPelanggaran');
    if (ctx && @json($chartData ?? [])->length > 0) {
        new Chart(ctx, {
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
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } }
            }
        });
    }
});
</script>
@endpush
