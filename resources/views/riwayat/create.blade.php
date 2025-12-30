@extends('layouts.app')

@section('title', 'Catat Pelanggaran')
@section('subtitle', 'Catat pelanggaran baru untuk siswa.')
@section('page-header', true)

@section('content')
<div class="max-w-4xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Pencatatan Pelanggaran</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('riwayat.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="guru_pencatat_user_id" value="{{ auth()->id() }}">
                
                {{-- Pilih Siswa --}}
                <div class="form-group">
                    <label class="form-label form-label-required">Pilih Siswa</label>
                    <p class="text-sm text-gray-500 mb-3">Pilih satu atau lebih siswa yang melakukan pelanggaran.</p>
                    
                    {{-- Search Box --}}
                    <div class="mb-3">
                        <input type="text" id="searchSiswa" class="form-input" placeholder="Cari nama atau NISN...">
                    </div>
                    
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-2" id="siswaList">
                        @foreach($daftarSiswa ?? [] as $s)
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer siswa-item" data-nama="{{ strtolower($s->nama_siswa) }}" data-nisn="{{ $s->nisn }}">
                                <input type="checkbox" name="siswa_id[]" value="{{ $s->id }}" 
                                       {{ in_array($s->id, old('siswa_id', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800">{{ $s->nama_siswa }}</p>
                                    <p class="text-sm text-gray-500">{{ $s->nisn }} • {{ $s->kelas->nama_kelas ?? '-' }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('siswa_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Pilih Jenis Pelanggaran --}}
                <div class="form-group">
                    <label class="form-label form-label-required">Jenis Pelanggaran</label>
                    <p class="text-sm text-gray-500 mb-3">Pilih satu atau lebih jenis pelanggaran.</p>
                    
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-2">
                        @foreach($daftarPelanggaran ?? [] as $p)
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                <input type="checkbox" name="jenis_pelanggaran_id[]" value="{{ $p->id }}" 
                                       {{ in_array($p->id, old('jenis_pelanggaran_id', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800">{{ $p->nama_pelanggaran }}</p>
                                </div>
                                <span class="badge badge-danger">{{ $p->poin }} poin</span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_pelanggaran_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Tanggal & Waktu --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="tanggal_kejadian" class="form-label form-label-required">Tanggal Kejadian</label>
                        <input type="date" id="tanggal_kejadian" name="tanggal_kejadian" 
                               value="{{ old('tanggal_kejadian', date('Y-m-d')) }}"
                               class="form-input @error('tanggal_kejadian') error @enderror" required>
                        @error('tanggal_kejadian')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="waktu_kejadian" class="form-label">Waktu Kejadian</label>
                        <input type="time" id="waktu_kejadian" name="waktu_kejadian" 
                               value="{{ old('waktu_kejadian', date('H:i')) }}"
                               class="form-input @error('waktu_kejadian') error @enderror">
                        @error('waktu_kejadian')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                {{-- Keterangan --}}
                <div class="form-group">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" 
                              class="form-input form-textarea @error('keterangan') error @enderror"
                              placeholder="Deskripsi singkat kejadian...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Bukti Foto --}}
                <div class="form-group">
                    <label for="bukti_foto" class="form-label">Bukti Foto (Opsional)</label>
                    <input type="file" id="bukti_foto" name="bukti_foto" accept="image/*"
                           class="form-input @error('bukti_foto') error @enderror">
                    <p class="form-help">Format: JPG, PNG. Maksimal 2MB.</p>
                    @error('bukti_foto')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                        </svg>
                        <span>Simpan Pelanggaran</span>
                    </button>
                    <a href="{{ route('riwayat.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchSiswa');
    const siswaItems = document.querySelectorAll('.siswa-item');
    
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        
        siswaItems.forEach(function(item) {
            const nama = item.dataset.nama;
            const nisn = item.dataset.nisn;
            
            if (nama.includes(query) || nisn.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
@endpush
