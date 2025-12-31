@extends('layouts.app')

@section('title', 'Detail Kelas')
@section('subtitle', $kelas->nama_kelas ?? 'Detail Kelas')
@section('page-header', true)

@section('actions')
    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
        <span>Kembali</span>
    </a>
    @can('update', $kelas)
    <a href="{{ route('kelas.edit', $kelas->id) }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
        <span>Edit Kelas</span>
    </a>
    @endcan
@endsection

@section('content')
<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Jurusan --}}
        <div class="card p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                    <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
                    <path d="m22 11.7-9.17 4.16a2 2 0 0 1-1.66 0L2 11.7"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase">Jurusan</p>
                <p class="text-lg font-semibold text-gray-800">{{ $kelas->jurusan->nama_jurusan ?? '-' }}</p>
            </div>
        </div>
        
        {{-- Wali Kelas --}}
        <div class="card p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase">Wali Kelas</p>
                <p class="text-lg font-semibold text-gray-800">{{ $kelas->waliKelas->nama ?? $kelas->waliKelas->username ?? 'Belum ditentukan' }}</p>
            </div>
        </div>
        
        {{-- Total Siswa --}}
        <div class="card p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase">Total Siswa</p>
                <p class="text-lg font-semibold text-gray-800">{{ $kelas->siswa->count() }} Siswa</p>
            </div>
        </div>
    </div>
    
    {{-- Daftar Siswa --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Siswa</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Siswa</th>
                        <th>NISN</th>
                        <th>Wali Murid</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas->siswa as $index => $s)
                        <tr>
                            <td class="text-gray-500">{{ $index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($s->nama_siswa ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $s->nama_siswa }}</span>
                                </div>
                            </td>
                            <td class="font-mono text-gray-600">{{ $s->nisn ?? '-' }}</td>
                            <td class="text-gray-500">{{ $s->waliMurid->nama ?? $s->waliMurid->username ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('siswa.show', $s->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                    <h3 class="empty-state-title">Belum Ada Siswa</h3>
                                    <p class="empty-state-description">Kelas ini belum memiliki siswa terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
