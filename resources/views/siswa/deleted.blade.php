@extends('layouts.app')

@section('title', 'Arsip Siswa')
@section('subtitle', 'Siswa yang telah dihapus (soft-delete). Dapat di-restore atau dihapus permanen.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
        </svg>
        <span>Kembali ke Data Siswa</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Info Banner --}}
    <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl">
        <div class="flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600 shrink-0 mt-0.5">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
            <div>
                <p class="font-medium text-amber-800">Catatan Penting</p>
                <p class="text-sm text-amber-700 mt-1">
                    Halaman ini menampilkan siswa yang telah dihapus. Anda dapat me-<strong>restore</strong> siswa kembali ke daftar aktif, 
                    atau menghapus secara <strong>permanen</strong> dari database (tidak dapat dikembalikan).
                </p>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('siswa.deleted') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-group flex-1 min-w-[150px]">
                    <label for="search" class="form-label">Cari</label>
                    <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-input" placeholder="Nama atau NISN...">
                </div>
                <div class="form-group min-w-[150px]">
                    <label for="alasan_keluar" class="form-label">Alasan Keluar</label>
                    <select id="alasan_keluar" name="alasan_keluar" class="form-input form-select">
                        <option value="">Semua Alasan</option>
                        @foreach($alasanOptions ?? [] as $alasan)
                            <option value="{{ $alasan }}" {{ ($filters['alasan_keluar'] ?? '') == $alasan ? 'selected' : '' }}>{{ $alasan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group min-w-[150px]">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ ($filters['kelas_id'] ?? '') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('siswa.deleted') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas Terakhir</th>
                    <th>Alasan Keluar</th>
                    <th>Tanggal Dihapus</th>
                    <th class="w-40 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deletedSiswa ?? [] as $index => $s)
                    <tr>
                        <td class="text-gray-500">{{ ($deletedSiswa->currentPage() - 1) * $deletedSiswa->perPage() + $index + 1 }}</td>
                        <td>
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-md">{{ $s->nisn }}</span>
                        </td>
                        <td class="font-medium text-gray-800">{{ $s->nama_siswa }}</td>
                        <td>
                            <span class="badge badge-neutral">{{ $s->kelas->nama_kelas ?? '-' }}</span>
                        </td>
                        <td>
                            @php
                                $alasanColors = [
                                    'Alumni' => 'badge-success',
                                    'Dikeluarkan' => 'badge-danger',
                                    'Pindah Sekolah' => 'badge-warning',
                                    'Lainnya' => 'badge-neutral',
                                ];
                            @endphp
                            <span class="badge {{ $alasanColors[$s->alasan_keluar] ?? 'badge-neutral' }}">{{ $s->alasan_keluar ?? '-' }}</span>
                            @if($s->keterangan_keluar)
                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($s->keterangan_keluar, 30) }}</p>
                            @endif
                        </td>
                        <td class="text-gray-500 text-sm">{{ $s->deleted_at ? $s->deleted_at->format('d M Y H:i') : '-' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                {{-- Restore Button --}}
                                <form action="{{ route('siswa.restore', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Restore siswa ini ke daftar aktif?')">
                                    @csrf
                                    <button type="submit" class="btn btn-icon btn-outline text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200" title="Restore">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>
                                        </svg>
                                    </button>
                                </form>
                                
                                {{-- Permanent Delete Button --}}
                                <button 
                                    type="button" 
                                    class="btn btn-icon btn-outline text-red-600 hover:bg-red-50 hover:border-red-200" 
                                    title="Hapus Permanen"
                                    @click="$dispatch('open-permanent-delete-modal', { id: {{ $s->id }}, nama: '{{ addslashes($s->nama_siswa) }}', nisn: '{{ $s->nisn }}' })"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                                </svg>
                                <h3 class="empty-state-title">Tidak Ada Data Arsip</h3>
                                <p class="empty-state-description">Tidak ada siswa yang telah dihapus.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(method_exists($deletedSiswa ?? [], 'hasPages') && $deletedSiswa->hasPages())
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500">Menampilkan {{ $deletedSiswa->firstItem() }} - {{ $deletedSiswa->lastItem() }} dari {{ $deletedSiswa->total() }}</p>
            {{ $deletedSiswa->links() }}
        </div>
    @endif
</div>

{{-- Permanent Delete Modal --}}
<div 
    x-data="{ 
        open: false, 
        siswaId: null, 
        siswaName: '', 
        siswaNisn: '',
        confirmed: false
    }"
    @open-permanent-delete-modal.window="
        open = true; 
        siswaId = $event.detail.id; 
        siswaName = $event.detail.nama; 
        siswaNisn = $event.detail.nisn;
        confirmed = false;
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    {{-- Backdrop --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
        @click="open = false"
    ></div>
    
    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
            @click.stop
        >
            {{-- Header --}}
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-600">⚠️ Hapus Permanen</h3>
                        <p class="text-sm text-gray-500">Data tidak dapat dikembalikan!</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form :action="'/siswa/' + siswaId + '/force-delete'" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="p-6 space-y-4">
                    {{-- Siswa Info --}}
                    <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                        <p class="text-sm text-red-600">Siswa yang akan dihapus permanen:</p>
                        <p class="font-semibold text-red-800" x-text="siswaName"></p>
                        <p class="text-sm text-red-600 font-mono" x-text="'NISN: ' + siswaNisn"></p>
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-gray-800">⚠️ Tindakan ini akan menghapus:</p>
                        <ul class="text-sm text-gray-600 space-y-1 pl-4">
                            <li>• Data siswa secara permanen</li>
                            <li>• Semua riwayat pelanggaran terkait</li>
                            <li>• Semua kasus tindak lanjut terkait</li>
                        </ul>
                    </div>
                    
                    {{-- Confirmation Checkbox --}}
                    <label class="flex items-start gap-3 cursor-pointer p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <input 
                            type="checkbox" 
                            name="confirm_permanent" 
                            value="1" 
                            x-model="confirmed"
                            class="w-4 h-4 mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500"
                        >
                        <span class="text-sm text-amber-800">
                            Saya mengerti bahwa tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong> dan semua data akan dihapus permanen.
                        </span>
                    </label>
                </div>
                
                {{-- Footer --}}
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button 
                        type="submit" 
                        class="btn btn-danger"
                        :disabled="!confirmed"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        <span>Hapus Permanen</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
