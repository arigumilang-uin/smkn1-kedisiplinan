@extends('layouts.app')

@section('title', 'Tambah Aturan Baru')
@section('subtitle', 'Buat aturan kedisiplinan baru.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Jenis Pelanggaran</h3>
        </div>
        <div class="card-body">
            {{-- Workflow Info --}}
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mb-6">
                <h4 class="font-semibold text-blue-800 mb-2">Workflow:</h4>
                <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                    <li>Isi form ini untuk membuat jenis pelanggaran baru</li>
                    <li>Setelah disimpan, Anda akan diarahkan ke halaman <strong>Kelola Rules</strong></li>
                    <li>Di halaman Kelola Rules, atur: <strong>Frekuensi, Poin, Sanksi, Trigger Surat, Pembina</strong></li>
                </ol>
            </div>
            
            <form action="{{ route('jenis-pelanggaran.store') }}" method="POST" class="space-y-6">
                @csrf
                
                {{-- Nama Pelanggaran --}}
                <div class="form-group">
                    <label for="nama_pelanggaran" class="form-label form-label-required">Nama Pelanggaran</label>
                    <input type="text" id="nama_pelanggaran" name="nama_pelanggaran" 
                           value="{{ old('nama_pelanggaran') }}"
                           class="form-input @error('nama_pelanggaran') error @enderror" 
                           placeholder="Contoh: Rambut tidak sesuai (3-2-1, diwarnai, crop)"
                           required autofocus>
                    @error('nama_pelanggaran')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Kategori Pelanggaran --}}
                <div class="form-group">
                    <label for="kategori_id" class="form-label form-label-required">Kategori Pelanggaran</label>
                    <select id="kategori_id" name="kategori_id" 
                            class="form-input form-select @error('kategori_id') error @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategori ?? [] as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Filter Category --}}
                <div class="form-group">
                    <label for="filter_category" class="form-label">Filter Kategori <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <select id="filter_category" name="filter_category" 
                            class="form-input form-select @error('filter_category') error @enderror">
                        <option value="">-- Tidak ada filter --</option>
                        <option value="atribut" {{ old('filter_category') == 'atribut' ? 'selected' : '' }}>Atribut/Seragam</option>
                        <option value="absensi" {{ old('filter_category') == 'absensi' ? 'selected' : '' }}>Absensi/Kehadiran</option>
                        <option value="kerapian" {{ old('filter_category') == 'kerapian' ? 'selected' : '' }}>Kerapian/Kebersihan</option>
                        <option value="ibadah" {{ old('filter_category') == 'ibadah' ? 'selected' : '' }}>Ibadah/Agama</option>
                        <option value="berat" {{ old('filter_category') == 'berat' ? 'selected' : '' }}>Berat/Kejahatan</option>
                    </select>
                    <p class="form-help">Filter untuk memudahkan pencarian saat catat pelanggaran.</p>
                    @error('filter_category')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Keywords --}}
                <div class="form-group">
                    <label for="keywords" class="form-label">Alias / Keywords <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea id="keywords" name="keywords" rows="3"
                              class="form-input form-textarea @error('keywords') error @enderror" 
                              placeholder="Contoh: Rambut panjang, Rambut gondrong, Rambut dicat">{{ old('keywords') }}</textarea>
                    <p class="form-help">Kata kunci alternatif untuk memudahkan pencarian. Pisahkan dengan koma atau enter.</p>
                    @error('keywords')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan & Lanjut ke Kelola Rules</span>
                    </button>
                    <a href="{{ route('frequency-rules.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
