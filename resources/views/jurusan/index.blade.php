@extends('layouts.app')

@section('title', 'Manajemen Jurusan')
@section('subtitle', 'Kelola data jurusan/kompetensi keahlian.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('jurusan.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        <span>Tambah Jurusan</span>
    </a>
@endsection

@section('content')
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th class="w-12">No</th>
                <th class="">Kode</th>
                <th>Nama Jurusan</th>
                <th class="">Kaprodi</th>
                <th class="text-center">Jumlah Kelas</th>
                <th class="w-32 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurusanList ?? [] as $index => $j)
                <tr>
                    <td class="text-gray-500">{{ $loop->iteration }}</td>
                    <td class=""><span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded-md">{{ $j->kode_jurusan ?? '-' }}</span></td>
                    <td class="font-medium text-gray-800">{{ $j->nama_jurusan }}</td>
                    <td class="text-gray-500">{{ $j->kaprodi->username ?? '-' }}</td>
                    <td class="text-center"><span class="badge badge-primary">{{ $j->kelas_count ?? $j->kelas->count() }}</span></td>
                    <td>
                        {{-- Desktop: Icon buttons --}}
                        <div class="action-buttons-desktop">
                            <a href="{{ route('jurusan.edit', $j->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            </a>
                            <form action="{{ route('jurusan.destroy', $j->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus jurusan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                        
                        {{-- Mobile: Dropdown --}}
                        <div class="action-dropdown-mobile" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="action-dropdown-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="action-dropdown-menu">
                                <a href="{{ route('jurusan.edit', $j->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    Edit
                                </a>
                                <div class="action-dropdown-divider"></div>
                                <form action="{{ route('jurusan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jurusan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-dropdown-item action-dropdown-item--delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <h3 class="empty-state-title">Tidak Ada Data</h3>
                            <p class="empty-state-description">Belum ada jurusan yang terdaftar.</p>
                            <a href="{{ route('jurusan.create') }}" class="btn btn-primary">Tambah Jurusan</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
