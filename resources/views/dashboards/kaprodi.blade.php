@extends('layouts.app')

@section('title', 'Dashboard Kaprodi')
@section('subtitle', 'Monitoring siswa jurusan {{ $jurusan->nama_jurusan ?? "" }}')
@section('page-header', true)

@section('content')
<div class="space-y-6">
    {{-- Jurusan Info Banner --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-violet-600 to-purple-600 p-6 overflow-hidden text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-20 -mt-20"></div>
        
        <div class="relative z-10">
            <p class="text-violet-100 text-sm mb-1">Jurusan yang Diampu</p>
            <h2 class="text-2xl font-bold">{{ $jurusan->nama_jurusan ?? 'Belum ada jurusan' }}</h2>
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
                <p class="stat-card-label">Siswa di Jurusan</p>
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
    
    {{-- Filter --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('dashboard.kaprodi') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-group flex-1 min-w-[150px]">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-input">
                </div>
                <div class="form-group flex-1 min-w-[150px]">
                    <label for="end_date" class="form-label">Sampai</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-input">
                </div>
                <div class="form-group flex-1 min-w-[150px]">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasJurusan ?? [] as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top 10 Pelanggaran di Jurusan</h3>
            </div>
            <div class="card-body">
                @if(($chartData ?? collect())->count() > 0)
                    <canvas id="chartPelanggaran" height="300"></canvas>
                @else
                    <div class="empty-state py-12">
                        <p class="text-gray-500">Tidak ada data pelanggaran.</p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Kasus Terbaru --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kasus Perlu Ditangani</h3>
            </div>
            <div class="card-body p-0">
                @forelse($kasusBaru ?? [] as $kasus)
                    <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $kasus->siswa->nama_siswa ?? '-' }}</p>
                            <p class="text-sm text-gray-500">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }} • {{ $kasus->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="badge badge-{{ $kasus->status === 'Baru' ? 'info' : 'warning' }}">{{ $kasus->status }}</span>
                    </a>
                @empty
                    <div class="empty-state py-8"><p class="text-gray-500">Tidak ada kasus yang perlu ditangani.</p></div>
                @endforelse
            </div>
        </div>
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
                    backgroundColor: ['rgba(59,130,246,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(239,68,68,0.8)','rgba(139,92,246,0.8)','rgba(236,72,153,0.8)','rgba(20,184,166,0.8)','rgba(249,115,22,0.8)','rgba(99,102,241,0.8)','rgba(34,197,94,0.8)'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
@endpush
