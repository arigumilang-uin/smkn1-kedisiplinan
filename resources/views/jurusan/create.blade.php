@extends('layouts.app')

@section('title', isset($jurusan) ? 'Edit Jurusan' : 'Tambah Jurusan')
@section('subtitle', isset($jurusan) ? 'Perbarui data jurusan.' : 'Tambahkan jurusan baru.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form {{ isset($jurusan) ? 'Edit' : 'Tambah' }} Jurusan</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($jurusan) ? route('jurusan.update', $jurusan->id) : route('jurusan.store') }}" method="POST" class="space-y-6">
                @csrf
                @if(isset($jurusan))
                    @method('PUT')
                @endif
                
                <div class="form-group">
                    <label for="kode_jurusan" class="form-label form-label-required">Kode Jurusan</label>
                    <input type="text" id="kode_jurusan" name="kode_jurusan" 
                           value="{{ old('kode_jurusan', $jurusan->kode_jurusan ?? '') }}"
                           class="form-input @error('kode_jurusan') error @enderror" 
                           placeholder="Contoh: TKJ" required>
                    @error('kode_jurusan')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="nama_jurusan" class="form-label form-label-required">Nama Jurusan</label>
                    <input type="text" id="nama_jurusan" name="nama_jurusan" 
                           value="{{ old('nama_jurusan', $jurusan->nama_jurusan ?? '') }}"
                           class="form-input @error('nama_jurusan') error @enderror" 
                           placeholder="Contoh: Teknik Komputer dan Jaringan" required>
                    @error('nama_jurusan')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="kaprodi_user_id" class="form-label">Kaprodi</label>
                    <select id="kaprodi_user_id" name="kaprodi_user_id" class="form-input form-select @error('kaprodi_user_id') error @enderror">
                        <option value="">-- Pilih Kaprodi --</option>
                        @foreach($kaprodiList ?? [] as $k)
                            <option value="{{ $k->id }}" {{ old('kaprodi_user_id', $jurusan->kaprodi_user_id ?? '') == $k->id ? 'selected' : '' }}>
                                {{ $k->username }}
                            </option>
                        @endforeach
                    </select>
                    @error('kaprodi_user_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan</span>
                    </button>
                    <a href="{{ route('jurusan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
