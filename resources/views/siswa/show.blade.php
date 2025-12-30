@extends('layouts.app')

@section('title', 'Detail Siswa')
@section('subtitle', $siswa->nama_siswa)
@section('page-header', true)

@section('actions')
    @can('update', $siswa)
    <a href="{{ route('siswa.edit', $siswa->id) }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
        </svg>
        <span>Edit</span>
    </a>
    @endcan
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-blue-500/30">
                    {{ strtoupper(substr($siswa->nama_siswa, 0, 1)) }}
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 mt-4">{{ $siswa->nama_siswa }}</h3>
                <p class="text-gray-500 font-mono">NISN: {{ $siswa->nisn }}</p>
                
                <div class="mt-4">
                    <span class="badge badge-primary text-sm">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                    @if($siswa->kelas?->jurusan)
                        <span class="badge badge-neutral text-sm ml-1">{{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                    @endif
                </div>
                
                {{-- Poin Display --}}
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="text-center">
                        @php
                            $poinClass = $totalPoin > 50 ? 'text-red-500' : ($totalPoin > 20 ? 'text-amber-500' : 'text-emerald-500');
                        @endphp
                        <p class="text-4xl font-bold {{ $poinClass }}">{{ $totalPoin ?? 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">Poin Kumulatif</p>
                    </div>
                </div>
                
                {{-- Pembinaan Status --}}
                @if($pembinaanAktif ?? false)
                    <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="flex items-center gap-2 justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            </svg>
                            <p class="text-sm font-semibold text-amber-800">Dalam Pembinaan</p>
                        </div>
                        <p class="text-xs text-amber-600 mt-1">{{ $pembinaanAktif->rule->nama_rule ?? 'Pembinaan Internal' }}</p>
                    </div>
                @elseif(!empty($pembinaanRekomendasi) && is_array($pembinaanRekomendasi) && !empty($pembinaanRekomendasi['nama_rule'] ?? $pembinaanRekomendasi['pembina_roles'] ?? null))
                    <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
                        <p class="text-sm font-semibold text-red-800">Rekomendasi Pembinaan</p>
                        <p class="text-xs text-red-600 mt-1">
                            {{ $pembinaanRekomendasi['nama_rule'] ?? 'Pembinaan diperlukan' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Contact Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kontak Wali Murid</h3>
            </div>
            <div class="card-body space-y-4">
                @if($siswa->nomor_hp_wali_murid)
                    <a href="https://wa.me/62{{ ltrim($siswa->nomor_hp_wali_murid, '0') }}" target="_blank" 
                       class="flex items-center gap-3 p-3 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-emerald-800">{{ $siswa->nomor_hp_wali_murid }}</p>
                            <p class="text-xs text-emerald-600">Klik untuk WhatsApp</p>
                        </div>
                    </a>
                @else
                    <div class="text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto text-gray-300 mb-2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <p class="text-gray-400 text-sm">Tidak ada kontak wali</p>
                    </div>
                @endif
                
                @if($siswa->waliMurid)
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-xs text-blue-600 mb-1">Akun Wali Murid</p>
                        <p class="font-medium text-blue-800">{{ $siswa->waliMurid->username }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Detail Info & History --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Siswa</h3>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">NISN</dt>
                        <dd class="mt-1 font-semibold text-gray-800 font-mono text-lg">{{ $siswa->nisn }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Kelas</dt>
                        <dd class="mt-1 font-semibold text-gray-800 text-lg">{{ $siswa->kelas->nama_kelas ?? '-' }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jurusan</dt>
                        <dd class="mt-1 font-semibold text-gray-800">{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Wali Kelas</dt>
                        <dd class="mt-1 font-semibold text-gray-800">{{ $siswa->kelas->waliKelas->username ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        {{-- Riwayat Pelanggaran --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pelanggaran</h3>
                @can('create', App\Models\RiwayatPelanggaran::class)
                <a href="{{ route('riwayat.create', ['siswa_id' => $siswa->id]) }}" class="btn btn-sm btn-primary">
                    + Catat Pelanggaran
                </a>
                @endcan
            </div>
            <div class="table-container !rounded-none !border-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pelanggaran</th>
                            <th class="text-center">Poin</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa->riwayatPelanggaran ?? [] as $riwayat)
                            <tr>
                                <td class="text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('d M Y') }}</td>
                                <td>
                                    <p class="font-medium text-gray-800">{{ $riwayat->jenisPelanggaran->nama_pelanggaran ?? '-' }}</p>
                                    @if($riwayat->keterangan)
                                        <p class="text-sm text-gray-500">{{ Str::limit($riwayat->keterangan, 50) }}</p>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger">{{ $riwayat->jenisPelanggaran->poin ?? 0 }}</span>
                                </td>
                                <td class="text-gray-500 text-sm">{{ $riwayat->pencatat->username ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto text-emerald-300 mb-2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                    </svg>
                                    <p class="text-gray-400">Tidak ada riwayat pelanggaran 👍</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Kasus Tindak Lanjut --}}
        @if($siswa->tindakLanjut->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kasus Tindak Lanjut</h3>
            </div>
            <div class="card-body p-0">
                @foreach($siswa->tindakLanjut as $kasus)
                    <div class="flex items-center gap-4 p-4 border-b border-gray-100 last:border-b-0">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800">{{ $kasus->jenis_tindak_lanjut ?? 'Tindak Lanjut' }}</p>
                            <p class="text-sm text-gray-500">{{ $kasus->created_at->format('d M Y') }}</p>
                        </div>
                        @php
                            $statusColors = [
                                'Baru' => 'badge-info',
                                'Menunggu Persetujuan' => 'badge-warning',
                                'Disetujui' => 'badge-success',
                                'Ditangani' => 'badge-primary',
                                'Selesai' => 'badge-neutral',
                            ];
                        @endphp
                        <span class="badge {{ $statusColors[$kasus->status] ?? 'badge-neutral' }}">{{ $kasus->status }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
