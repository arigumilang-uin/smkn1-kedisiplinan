@extends('layouts.app')

@section('title', 'Data Siswa')
@section('subtitle', 'Kelola data seluruh siswa di sekolah Anda.')
@section('page-header', true)

@section('actions')
    @can('create', App\Models\Siswa::class)
        <a href="{{ route('siswa.deleted') }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
            </svg>
            <span>Arsip Siswa</span>
        </a>
        <a href="{{ route('siswa.bulk-create') }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
            </svg>
            <span>Import Excel</span>
        </a>
        <a href="{{ route('siswa.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/><path d="M12 5v14"/>
            </svg>
            <span>Tambah Siswa</span>
        </a>
    @endcan
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: false }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Data</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </div>
        
        <div class="card-body" x-show="expanded" x-collapse>
            <form action="{{ route('siswa.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="form-group md:col-span-2">
                    <label for="search" class="form-label">Cari</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ request('search') }}"
                        class="form-input" 
                        placeholder="Nama atau NISN..."
                    >
                </div>
                
                {{-- Jurusan --}}
                <div class="form-group">
                    <label for="jurusan_id" class="form-label">Jurusan</label>
                    <select id="jurusan_id" name="jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($allJurusan ?? [] as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Kelas --}}
                <div class="form-group">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Actions --}}
                <div class="md:col-span-4 flex gap-3">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <span>Terapkan Filter</span>
                    </button>
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>
                        </svg>
                        <span>Reset</span>
                    </a>
                </div>
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
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('siswa.show', $s->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                @can('update', $s)
                                    <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                @endcan
                                @can('delete', $s)
                                    <button 
                                        type="button" 
                                        class="btn btn-icon btn-outline text-red-500 hover:bg-red-50 hover:border-red-200" 
                                        title="Hapus"
                                        @click="$dispatch('open-delete-modal', { id: {{ $s->id }}, nama: '{{ $s->nama_siswa }}', nisn: '{{ $s->nisn }}' })"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                    </button>
                                @endcan
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
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
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
                    <a href="{{ $siswa->previousPageUrl() }}" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </a>
                @endif
                
                {{-- Page Numbers --}}
                @foreach($siswa->getUrlRange(max(1, $siswa->currentPage() - 2), min($siswa->lastPage(), $siswa->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" class="pagination-btn {{ $page == $siswa->currentPage() ? 'active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach
                
                {{-- Next --}}
                @if($siswa->hasMorePages())
                    <a href="{{ $siswa->nextPageUrl() }}" class="pagination-btn">
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
</div>

{{-- Delete Siswa Modal --}}
<div 
    x-data="{ 
        open: false, 
        siswaId: null, 
        siswaName: '', 
        siswaNisn: '',
        alasanKeluar: '',
        keteranganKeluar: ''
    }"
    @open-delete-modal.window="
        open = true; 
        siswaId = $event.detail.id; 
        siswaName = $event.detail.nama; 
        siswaNisn = $event.detail.nisn;
        alasanKeluar = '';
        keteranganKeluar = '';
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Hapus Data Siswa</h3>
                        <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form :action="'/siswa/' + siswaId" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="p-6 space-y-4">
                    {{-- Siswa Info --}}
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500">Siswa yang akan dihapus:</p>
                        <p class="font-semibold text-gray-800" x-text="siswaName"></p>
                        <p class="text-sm text-gray-500 font-mono" x-text="'NISN: ' + siswaNisn"></p>
                    </div>
                    
                    {{-- Alasan Keluar --}}
                    <div class="form-group">
                        <label for="alasan_keluar" class="form-label form-label-required">Alasan Keluar</label>
                        <select 
                            id="alasan_keluar" 
                            name="alasan_keluar" 
                            x-model="alasanKeluar"
                            class="form-input form-select" 
                            required
                        >
                            <option value="">-- Pilih Alasan --</option>
                            <option value="Alumni">Alumni (Lulus)</option>
                            <option value="Dikeluarkan">Dikeluarkan</option>
                            <option value="Pindah Sekolah">Pindah Sekolah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    {{-- Keterangan --}}
                    <div class="form-group">
                        <label for="keterangan_keluar" class="form-label">Keterangan Tambahan</label>
                        <textarea 
                            id="keterangan_keluar" 
                            name="keterangan_keluar" 
                            x-model="keteranganKeluar"
                            rows="3" 
                            class="form-input form-textarea" 
                            placeholder="Tuliskan keterangan tambahan (opsional)..."
                        ></textarea>
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <p class="text-xs text-amber-700">
                            <strong>Perhatian:</strong> Siswa akan dipindahkan ke data arsip dan dapat di-restore kembali jika diperlukan.
                        </p>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button 
                        type="submit" 
                        class="btn btn-danger"
                        :disabled="!alasanKeluar"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        <span>Hapus Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
