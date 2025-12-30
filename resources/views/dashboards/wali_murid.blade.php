@extends('layouts.app')

@section('title', 'Dashboard Wali Murid')
@section('subtitle', 'Pantau perkembangan kedisiplinan anak Anda.')
@section('page-header', true)

@section('content')
<div class="space-y-6">
    {{-- Child Selector (if multiple children) --}}
    @if($semuaAnak->count() > 1)
        <div class="card">
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-gray-600">Pilih Anak:</span>
                    @foreach($semuaAnak as $anak)
                        <a href="{{ route('dashboard.wali_murid', ['siswa_id' => $anak->id]) }}" 
                           class="btn {{ $siswa->id === $anak->id ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $anak->nama_siswa }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    {{-- Student Info Card --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 p-6 overflow-hidden text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-20 -mt-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center gap-6">
            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-bold border border-white/20">
                {{ strtoupper(substr($siswa->nama_siswa ?? 'S', 0, 1)) }}
            </div>
            
            <div class="flex-1">
                <h2 class="text-2xl font-bold">{{ $siswa->nama_siswa }}</h2>
                <p class="text-blue-100 mt-1">NISN: {{ $siswa->nisn }} • {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
            </div>
            
            <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                <p class="text-4xl font-bold">{{ $totalPoin ?? 0 }}</p>
                <p class="text-sm text-blue-100">Poin Kumulatif</p>
            </div>
        </div>
    </div>
    
    {{-- Pembinaan Alert (if active) --}}
    @if($pembinaanAktif)
        <div class="alert alert-warning">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
            <div class="alert-content">
                <p class="alert-title">Status Pembinaan Aktif</p>
                <p class="alert-message">
                    Anak Anda saat ini dalam masa pembinaan: <strong>{{ $pembinaanAktif->rule->nama_rule ?? 'Pembinaan Internal' }}</strong>
                    @if($pembinaanAktif->dibinaOleh)
                        dibina oleh {{ $pembinaanAktif->dibinaOleh->username }}
                    @endif
                </p>
            </div>
        </div>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Riwayat Pelanggaran --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pelanggaran</h3>
            </div>
            <div class="card-body p-0">
                @forelse($riwayat as $r)
                    <div class="flex items-start gap-4 p-4 border-b border-gray-100 last:border-b-0">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <span class="font-bold text-sm">{{ $r->poin ?? 0 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800">{{ $r->jenisPelanggaran->nama_pelanggaran ?? 'Tidak diketahui' }}</p>
                            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($r->tanggal_kejadian)->format('d M Y') }}</p>
                            @if($r->catatan)
                                <p class="text-sm text-gray-600 mt-1">{{ $r->catatan }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                        </svg>
                        <h3 class="empty-state-title">Tidak Ada Pelanggaran</h3>
                        <p class="empty-state-description">Anak Anda belum memiliki catatan pelanggaran.</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        {{-- Kasus / Tindak Lanjut --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tindak Lanjut & Surat</h3>
            </div>
            <div class="card-body p-0">
                @forelse($kasus as $k)
                    <div class="flex items-start gap-4 p-4 border-b border-gray-100 last:border-b-0">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-800">{{ $k->jenis_tindak_lanjut ?? 'Tindak Lanjut' }}</p>
                                @php
                                    $statusColors = [
                                        'Baru' => 'badge-info',
                                        'Menunggu Persetujuan' => 'badge-warning',
                                        'Disetujui' => 'badge-success',
                                        'Ditangani' => 'badge-primary',
                                        'Selesai' => 'badge-neutral',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$k->status] ?? 'badge-neutral' }}">{{ $k->status }}</span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $k->created_at->format('d M Y') }}</p>
                            @if($k->catatan)
                                <p class="text-sm text-gray-600 mt-1">{{ $k->catatan }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <h3 class="empty-state-title">Tidak Ada Tindak Lanjut</h3>
                        <p class="empty-state-description">Belum ada surat panggilan atau tindak lanjut.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    {{-- Info Contact --}}
    <div class="card bg-blue-50 border-blue-100">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-800">Butuh Bantuan?</h3>
                    <p class="text-blue-700 text-sm mt-1">
                        Jika Anda memiliki pertanyaan mengenai kedisiplinan anak Anda, silakan hubungi Wali Kelas atau bagian Kesiswaan sekolah.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
