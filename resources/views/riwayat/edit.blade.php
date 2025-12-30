@extends('layouts.app')

@section('title', 'Edit Pelanggaran')
@section('subtitle', 'Perbarui data pelanggaran.')
@section('page-header', true)

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Pelanggaran</h3>
        </div>
        <div class="card-body">
            {{-- Info Siswa --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                <p class="text-sm text-gray-500">Siswa</p>
                <p class="font-semibold text-gray-800">{{ $riwayat->siswa->nama_siswa ?? '-' }}</p>
                <p class="text-sm text-gray-500">{{ $riwayat->siswa->nisn ?? '' }} • {{ $riwayat->siswa->kelas->nama_kelas ?? '-' }}</p>
            </div>
            
            <form action="{{ route('riwayat.update', $riwayat->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                {{-- Jenis Pelanggaran --}}
                <div class="form-group">
                    <label for="jenis_pelanggaran_id" class="form-label form-label-required">Jenis Pelanggaran</label>
                    <select id="jenis_pelanggaran_id" name="jenis_pelanggaran_id" class="form-input form-select @error('jenis_pelanggaran_id') error @enderror" required>
                        @foreach($jenisPelanggaran ?? [] as $p)
                            <option value="{{ $p->id }}" {{ old('jenis_pelanggaran_id', $riwayat->jenis_pelanggaran_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_pelanggaran }} ({{ $p->poin }} poin)
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_pelanggaran_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Tanggal & Waktu --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="tanggal_kejadian" class="form-label form-label-required">Tanggal Kejadian</label>
                        <input type="date" id="tanggal_kejadian" name="tanggal_kejadian" 
                               value="{{ old('tanggal_kejadian', \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('Y-m-d')) }}"
                               class="form-input @error('tanggal_kejadian') error @enderror" required>
                        @error('tanggal_kejadian')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="waktu_kejadian" class="form-label">Waktu Kejadian</label>
                        <input type="time" id="waktu_kejadian" name="waktu_kejadian" 
                               value="{{ old('waktu_kejadian', \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('H:i')) }}"
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
                              class="form-input form-textarea @error('keterangan') error @enderror">{{ old('keterangan', $riwayat->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Bukti Foto --}}
                <div class="form-group">
                    <label for="bukti_foto" class="form-label">Bukti Foto</label>
                    @if($riwayat->bukti_foto_path)
                        <div class="mb-3">
                            <img src="{{ Storage::url($riwayat->bukti_foto_path) }}" alt="Bukti" class="w-32 h-32 object-cover rounded-lg border">
                            <p class="text-sm text-gray-500 mt-1">Foto saat ini</p>
                        </div>
                    @endif
                    <input type="file" id="bukti_foto" name="bukti_foto" accept="image/*"
                           class="form-input @error('bukti_foto') error @enderror">
                    <p class="form-help">Biarkan kosong jika tidak ingin mengubah foto.</p>
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
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('riwayat.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
