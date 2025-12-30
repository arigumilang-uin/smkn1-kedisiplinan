@extends('layouts.app')

@section('title', 'Manajemen Kelas')
@section('subtitle', 'Kelola data kelas.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('kelas.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        <span>Tambah Kelas</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('kelas.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-group min-w-[200px]">
                    <label for="jurusan_id" class="form-label">Jurusan</label>
                    <select id="jurusan_id" name="jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList ?? [] as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>Nama Kelas</th>
                    <th>Jurusan</th>
                    <th>Wali Kelas</th>
                    <th class="text-center">Jumlah Siswa</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelas ?? [] as $index => $k)
                    <tr>
                        <td class="text-gray-500">{{ $loop->iteration }}</td>
                        <td class="font-medium text-gray-800">{{ $k->nama_kelas }}</td>
                        <td><span class="badge badge-primary">{{ $k->jurusan->nama_jurusan ?? '-' }}</span></td>
                        <td class="text-gray-500">{{ $k->waliKelas->username ?? '-' }}</td>
                        <td class="text-center"><span class="badge badge-neutral">{{ $k->siswa_count ?? $k->siswa->count() }}</span></td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('kelas.edit', $k->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </a>
                                <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <h3 class="empty-state-title">Tidak Ada Data</h3>
                                <p class="empty-state-description">Belum ada kelas yang terdaftar.</p>
                                <a href="{{ route('kelas.create') }}" class="btn btn-primary">Tambah Kelas</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
