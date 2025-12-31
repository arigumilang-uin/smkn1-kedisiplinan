
    {{-- Data Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Kontak Wali</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswa as $index => $s)
                    <tr>
                        <td class="text-gray-500">{{ $siswa->firstItem() + $index }}</td>
                        <td>
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-md">
                                {{ $s->nisn }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('siswa.show', $s->id) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $s->nama_siswa }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-primary">
                                {{ $s->kelas->nama_kelas ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @if($s->nomor_hp_wali_murid)
                                <a href="https://wa.me/62{{ ltrim($s->nomor_hp_wali_murid, '0') }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    {{ $s->nomor_hp_wali_murid }}
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td>
                            {{-- Desktop: Icon buttons --}}
                            <div class="action-buttons-desktop">
                                <a href="{{ route('siswa.show', $s->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                @can('update', $s)
                                    <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                @endcan
                                @can('delete', $s)
                                    <button type="button" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50 hover:border-red-200" title="Hapus"
                                        @click="$dispatch('open-delete-modal', { id: {{ $s->id }}, nama: '{{ $s->nama_siswa }}', nisn: '{{ $s->nisn }}' })">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                    </button>
                                @endcan
                            </div>
                            
                            {{-- Mobile: Dropdown --}}
                            <div class="action-dropdown-mobile" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="action-dropdown-trigger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="action-dropdown-menu">
                                    <a href="{{ route('siswa.show', $s->id) }}" class="action-dropdown-item action-dropdown-item--view">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Detail
                                    </a>
                                    @can('update', $s)
                                    <a href="{{ route('siswa.edit', $s->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        Edit
                                    </a>
                                    @endcan
                                    @can('delete', $s)
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item action-dropdown-item--delete"
                                            @click="$dispatch('open-delete-modal', { id: {{ $s->id }}, nama: '{{ $s->nama_siswa }}', nisn: '{{ $s->nisn }}' })">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        Hapus
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                </svg>
                                <h3 class="empty-state-title">Data Tidak Ditemukan</h3>
                                <p class="empty-state-description">Tidak ada data siswa yang sesuai dengan filter Anda.</p>
                                @can('create', App\Models\Siswa::class)
                                    <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14"/><path d="M12 5v14"/>
                                        </svg>
                                        <span>Tambah Siswa Baru</span>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($siswa->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <p class="text-sm text-gray-500">
                Menampilkan {{ $siswa->firstItem() }} sampai {{ $siswa->lastItem() }} dari {{ $siswa->total() }} data
            </p>
            <div class="pagination">
                {{-- Previous --}}
                @if($siswa->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </span>
                @else
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->previousPageUrl() }}" class="pagination-btn" data-page="prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </a>
                @endif
                
                {{-- Next --}}
                @if($siswa->hasMorePages())
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->nextPageUrl() }}" class="pagination-btn" data-page="next">
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
