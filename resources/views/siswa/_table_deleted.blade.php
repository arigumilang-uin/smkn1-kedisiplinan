
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
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
        <p class="text-sm text-gray-500">
            Menampilkan {{ $deletedSiswa->firstItem() }} sampai {{ $deletedSiswa->lastItem() }} dari {{ $deletedSiswa->total() }} data
        </p>
        <div class="pagination">
            {{-- Previous --}}
            @if($deletedSiswa->onFirstPage())
                <span class="pagination-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </span>
            @else
                <a href="{{ $deletedSiswa->previousPageUrl() }}" class="pagination-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </a>
            @endif
            
            {{-- Next --}}
            @if($deletedSiswa->hasMorePages())
                <a href="{{ $deletedSiswa->nextPageUrl() }}" class="pagination-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            @else
                <span class="pagination-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </span>
            @endif
        </div>
    </div>
@endif
