@extends('layouts.app')

@section('title', 'Detail Konsentrasi')
@section('subtitle', $konsentrasi->nama_konsentrasi)
@section('page-header', true)

@section('actions')
    <a href="{{ route('konsentrasi.edit', $konsentrasi->id) }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
        <span>Edit</span>
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Info Card --}}
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Konsentrasi</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Kode</p>
                    <p class="font-mono text-lg font-bold">{{ $konsentrasi->kode_konsentrasi ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Nama Konsentrasi</p>
                    <p class="font-medium text-gray-800">{{ $konsentrasi->nama_konsentrasi }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Jurusan (Program Keahlian)</p>
                    <p class="font-medium text-blue-600">{{ $konsentrasi->jurusan->nama_jurusan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Kaprodi</p>
                    <p class="font-medium text-gray-800">{{ $konsentrasi->jurusan->kaprodi->username ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Status</p>
                    @if($konsentrasi->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-secondary">Nonaktif</span>
                    @endif
                </div>
                @if($konsentrasi->deskripsi)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Deskripsi</p>
                    <p class="text-sm text-gray-600">{{ $konsentrasi->deskripsi }}</p>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Statistics --}}
        <div class="card mt-6">
            <div class="card-body">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-xl">
                        <p class="text-3xl font-bold text-blue-600">{{ $konsentrasi->kelas->count() }}</p>
                        <p class="text-xs text-blue-600 uppercase tracking-wider">Kelas</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-xl">
                        <p class="text-3xl font-bold text-green-600">{{ $konsentrasi->kelas->sum(fn($k) => $k->siswa->count()) }}</p>
                        <p class="text-xs text-green-600 uppercase tracking-wider">Siswa</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Kelas List --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="card-title">Daftar Kelas</h3>
            </div>
            <div class="card-body p-0">
                @if($konsentrasi->kelas->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tingkat</th>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas</th>
                                <th class="text-center">Siswa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($konsentrasi->kelas->sortBy('tingkat') as $kelas)
                                <tr>
                                    <td>
                                        <span class="badge {{ $kelas->tingkat == 'X' ? 'badge-secondary' : ($kelas->tingkat == 'XI' ? 'badge-primary' : 'badge-success') }}">
                                            {{ $kelas->tingkat }}
                                        </span>
                                    </td>
                                    <td class="font-medium text-gray-800">{{ $kelas->nama_kelas }}</td>
                                    <td class="text-gray-500">{{ $kelas->waliKelas->username ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $kelas->siswa->count() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state py-10">
                        <h3 class="empty-state-title">Belum Ada Kelas</h3>
                        <p class="empty-state-description">Konsentrasi ini belum memiliki kelas terdaftar.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('konsentrasi.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
        Kembali
    </a>
</div>
@endsection
