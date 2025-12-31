@extends('layouts.app')

@section('title', 'Manajemen Konsentrasi Keahlian')
@section('subtitle', 'Kelola data konsentrasi keahlian per jurusan.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('konsentrasi.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        <span>Tambah Konsentrasi</span>
    </a>
@endsection

@section('content')
{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('konsentrasi.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="form-group mb-0 flex-1 min-w-[200px]">
                <label class="form-label text-xs">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nama/Kode konsentrasi...">
            </div>
            <div class="form-group mb-0 w-48">
                <label class="form-label text-xs">Jurusan</label>
                <select name="jurusan_id" class="form-input form-select">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusanList ?? [] as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                            {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('konsentrasi.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th class="w-12">No</th>
                <th>Kode</th>
                <th>Nama Konsentrasi</th>
                <th>Jurusan</th>
                <th class="text-center">Kelas</th>
                <th class="text-center">Status</th>
                <th class="w-32 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($konsentrasiList ?? [] as $index => $k)
                <tr>
                    <td class="text-gray-500">{{ $konsentrasiList->firstItem() + $index }}</td>
                    <td><span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded-md">{{ $k->kode_konsentrasi ?? '-' }}</span></td>
                    <td class="font-medium text-gray-800">{{ $k->nama_konsentrasi }}</td>
                    <td class="text-gray-500">{{ $k->jurusan->nama_jurusan ?? '-' }}</td>
                    <td class="text-center"><span class="badge badge-primary">{{ $k->kelas_count ?? 0 }}</span></td>
                    <td class="text-center">
                        @if($k->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        {{-- Desktop: Icon buttons --}}
                        <div class="action-buttons-desktop">
                            <a href="{{ route('konsentrasi.show', $k->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('konsentrasi.edit', $k->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            </a>
                            <form action="{{ route('konsentrasi.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus konsentrasi ini?')">
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
                                <a href="{{ route('konsentrasi.show', $k->id) }}" class="action-dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Detail
                                </a>
                                <a href="{{ route('konsentrasi.edit', $k->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    Edit
                                </a>
                                <div class="action-dropdown-divider"></div>
                                <form action="{{ route('konsentrasi.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus konsentrasi ini?')">
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
                    <td colspan="7">
                        <div class="empty-state">
                            <h3 class="empty-state-title">Tidak Ada Data</h3>
                            <p class="empty-state-description">Belum ada konsentrasi yang terdaftar.</p>
                            <a href="{{ route('konsentrasi.create') }}" class="btn btn-primary">Tambah Konsentrasi</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($konsentrasiList->hasPages())
    <div class="mt-4">
        {{ $konsentrasiList->links() }}
    </div>
@endif
@endsection
