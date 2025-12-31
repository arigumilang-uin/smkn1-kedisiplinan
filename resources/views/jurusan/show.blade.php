@extends('layouts.app')

@section('title', 'Detail Jurusan')
@section('subtitle', $jurusan->nama_jurusan)
@section('page-header', true)

@section('actions')
    <a href="{{ route('jurusan.edit', $jurusan->id) }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
        </svg>
        <span>Edit Jurusan</span>
    </a>
    <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        <span>Kembali</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Info Jurusan --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Jurusan</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-gray-500 uppercase font-medium">Kode Jurusan</label>
                            <p class="font-mono text-lg font-bold text-blue-600">{{ $jurusan->kode_jurusan }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase font-medium">Nama Jurusan</label>
                            <p class="font-medium text-gray-800">{{ $jurusan->nama_jurusan }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase font-medium">Kepala Program (Kaprodi)</label>
                            @if($jurusan->kaprodi)
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr($jurusan->kaprodi->username, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $jurusan->kaprodi->username }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-400 italic">Belum ditentukan</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $jurusan->konsentrasi->count() }}</div>
                            <div class="text-xs text-purple-500 font-medium mt-1">Konsentrasi</div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $jurusan->kelas->count() }}</div>
                            <div class="text-xs text-blue-500 font-medium mt-1">Kelas</div>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $jurusan->siswa->count() }}</div>
                            <div class="text-xs text-green-500 font-medium mt-1">Siswa</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Daftar Konsentrasi --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Konsentrasi Keahlian</h3>
            <a href="{{ route('konsentrasi.create', ['jurusan_id' => $jurusan->id]) }}" class="btn btn-sm btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Tambah
            </a>
        </div>
        <div class="card-body">
            @if($jurusan->konsentrasi->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($jurusan->konsentrasi as $konsentrasi)
                        <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-sm">
                                    {{ $konsentrasi->kode_konsentrasi ?? strtoupper(substr($konsentrasi->nama_konsentrasi, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 truncate">{{ $konsentrasi->nama_konsentrasi }}</p>
                                    <p class="text-xs text-gray-400">{{ $konsentrasi->kelas->count() }} Kelas</p>
                                </div>
                                <a href="{{ route('konsentrasi.edit', $konsentrasi->id) }}" class="text-gray-400 hover:text-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-400 mb-4">Belum ada konsentrasi untuk jurusan ini.</p>
                    <a href="{{ route('konsentrasi.create', ['jurusan_id' => $jurusan->id]) }}" class="btn btn-primary btn-sm">Tambah Konsentrasi</a>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Daftar Kelas --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kelas</h3>
            <a href="{{ route('kelas.create', ['jurusan_id' => $jurusan->id]) }}" class="btn btn-sm btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Tambah
            </a>
        </div>
        <div class="card-body">
            @if($jurusan->kelas->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Konsentrasi</th>
                                <th>Wali Kelas</th>
                                <th class="text-center">Siswa</th>
                                <th class="w-24 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jurusan->kelas->sortBy('nama_kelas') as $kelas)
                                <tr>
                                    <td class="font-medium text-gray-800">{{ $kelas->nama_kelas }}</td>
                                    <td><span class="badge badge-primary">{{ $kelas->tingkat }}</span></td>
                                    <td>
                                        @if($kelas->konsentrasi)
                                            <span class="font-mono text-sm bg-purple-50 text-purple-700 px-2 py-1 rounded-md">
                                                {{ $kelas->konsentrasi->kode_konsentrasi ?? strtoupper(substr($kelas->konsentrasi->nama_konsentrasi, 0, 3)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="text-gray-600">{{ $kelas->waliKelas->username ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $kelas->siswa->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-400 mb-4">Belum ada kelas untuk jurusan ini.</p>
                    <a href="{{ route('kelas.create', ['jurusan_id' => $jurusan->id]) }}" class="btn btn-primary btn-sm">Tambah Kelas</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
