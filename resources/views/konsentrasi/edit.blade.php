@extends('layouts.app')

@section('title', 'Edit Konsentrasi')
@section('subtitle', 'Perbarui data konsentrasi keahlian.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Konsentrasi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('konsentrasi.update', $konsentrasi->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="jurusan_id" class="form-label form-label-required">Jurusan (Program Keahlian)</label>
                    <select id="jurusan_id" name="jurusan_id" class="form-input form-select @error('jurusan_id') error @enderror" required>
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusanList ?? [] as $j)
                            <option value="{{ $j->id }}" {{ old('jurusan_id', $konsentrasi->jurusan_id) == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    @error('jurusan_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="kode_konsentrasi" class="form-label">Kode Konsentrasi</label>
                    <input type="text" id="kode_konsentrasi" name="kode_konsentrasi" 
                           value="{{ old('kode_konsentrasi', $konsentrasi->kode_konsentrasi) }}"
                           class="form-input @error('kode_konsentrasi') error @enderror" 
                           placeholder="Contoh: TPB" maxlength="20">
                    @error('kode_konsentrasi')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="nama_konsentrasi" class="form-label form-label-required">Nama Konsentrasi</label>
                    <input type="text" id="nama_konsentrasi" name="nama_konsentrasi" 
                           value="{{ old('nama_konsentrasi', $konsentrasi->nama_konsentrasi) }}"
                           class="form-input @error('nama_konsentrasi') error @enderror" 
                           placeholder="Contoh: Teknik Pembangkit Biomassa" required>
                    @error('nama_konsentrasi')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3"
                              class="form-input form-textarea @error('deskripsi') error @enderror" 
                              placeholder="Deskripsi singkat tentang konsentrasi ini (opsional)">{{ old('deskripsi', $konsentrasi->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" 
                               class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               {{ old('is_active', $konsentrasi->is_active) ? 'checked' : '' }}>
                        <span class="text-gray-700">Konsentrasi Aktif</span>
                    </label>
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('konsentrasi.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
