@extends('layouts.app')

@section('title', 'Riwayat Saya')
@section('subtitle', 'Pelanggaran yang dicatat oleh Anda.')
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
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('my-riwayat.edit', $r->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/>
                                </svg>
                                <h3 class="empty-state-title">Belum Ada Catatan</h3>
                                <p class="empty-state-description">Anda belum mencatat pelanggaran apapun.</p>
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
