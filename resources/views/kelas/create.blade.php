@extends('layouts.app')

@section('title', 'Tambah Kelas')
@section('subtitle', 'Tambahkan kelas baru.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Kelas</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('kelas.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="form-group">
                    <label for="nama_kelas" class="form-label form-label-required">Nama Kelas</label>
                    <input type="text" id="nama_kelas" name="nama_kelas" 
                           value="{{ old('nama_kelas') }}"
                           class="form-input @error('nama_kelas') error @enderror" 
                           placeholder="Contoh: X TKJ 1" required>
                    @error('nama_kelas')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="jurusan_id" class="form-label form-label-required">Jurusan</label>
                    <select id="jurusan_id" name="jurusan_id" class="form-input form-select @error('jurusan_id') error @enderror" required>
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusanList ?? [] as $j)
                            <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    @error('jurusan_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="wali_kelas_user_id" class="form-label">Wali Kelas</label>
                    <select id="wali_kelas_user_id" name="wali_kelas_user_id" class="form-input form-select @error('wali_kelas_user_id') error @enderror">
                        <option value="">-- Pilih Wali Kelas --</option>
                        @foreach($waliKelasList ?? [] as $w)
                            <option value="{{ $w->id }}" {{ old('wali_kelas_user_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->username }}
                            </option>
                        @endforeach
                    </select>
                    @error('wali_kelas_user_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
