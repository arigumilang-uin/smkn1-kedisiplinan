@extends('layouts.app')

@section('title', 'Log Pelanggaran')
@section('subtitle', 'Riwayat pencatatan pelanggaran siswa.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('riwayat.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/><path d="M12 5v14"/>
        </svg>
        <span>Catat Pelanggaran</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: true }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Data</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </div>
        
        <div class="card-body" x-show="expanded" x-collapse>
            <form action="{{ route('riwayat.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="form-group">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="end_date" class="form-label">Sampai</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="jurusan_id" class="form-label">Jurusan</label>
                    <select id="jurusan_id" name="jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($allJurusan ?? [] as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group flex items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-1">Filter</button>
                    <a href="{{ route('riwayat.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Data Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Kelas</th>
                    <th>Pelanggaran</th>
                    <th class="text-center">Poin</th>
                    <th>Dicatat Oleh</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $r)
                    <tr>
                        <td class="text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->tanggal_kejadian)->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('siswa.show', $r->siswa->id ?? 0) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $r->siswa->nama_siswa ?? '-' }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $r->siswa->kelas->nama_kelas ?? '-' }}</span>
                        </td>
                        <td class="max-w-xs">
                            <p class="font-medium text-gray-800">{{ $r->jenisPelanggaran->nama_pelanggaran ?? '-' }}</p>
                            @if($r->keterangan)
                                <p class="text-sm text-gray-500 truncate">{{ $r->keterangan }}</p>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-danger">{{ $r->poin ?? 0 }}</span>
                        </td>
                        <td class="text-gray-500 text-sm">{{ $r->pencatat->username ?? '-' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('riwayat.edit', $r->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('riwayat.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus riwayat pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50 hover:border-red-200" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                                </svg>
                                <h3 class="empty-state-title">Tidak Ada Data</h3>
                                <p class="empty-state-description">Belum ada riwayat pelanggaran yang dicatat.</p>
                                <a href="{{ route('riwayat.create') }}" class="btn btn-primary">Catat Pelanggaran</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($riwayat->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500">
                Menampilkan {{ $riwayat->firstItem() }} - {{ $riwayat->lastItem() }} dari {{ $riwayat->total() }}
            </p>
            {{ $riwayat->links() }}
        </div>
    @endif
</div>
@endsection
