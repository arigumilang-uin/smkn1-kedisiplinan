@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card card-primary shadow">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i> Tambah Siswa Baru</h3>
                </div>
                
                <form action="{{ route('siswa.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        
                        <div class="form-group">
                            <label>NISN <span class="text-danger">*</span></label>
                            <input type="number" name="nisn" class="form-control @error('nisn') is-invalid @enderror" placeholder="Nomor Induk Siswa Nasional" value="{{ old('nisn') }}" required>
                            @error('nisn') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_siswa" class="form-control @error('nama_siswa') is-invalid @enderror" placeholder="Nama Siswa sesuai Ijazah" value="{{ old('nama_siswa') }}" required>
                            @error('nama_siswa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                            @error('kelas_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Nomor HP/WA Orang Tua</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                </div>
                                <input type="number" name="nomor_hp_ortu" class="form-control @error('nomor_hp_ortu') is-invalid @enderror" placeholder="08xxxxxxxxxx" value="{{ old('nomor_hp_ortu') }}">
                            </div>
                            <small class="text-muted">Digunakan untuk mengirim notifikasi pelanggaran via WA.</small>
                            @error('nomor_hp_ortu') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Akun Ortu (Optional, bisa diset nanti di User Management) -->
                        <!-- Disembunyikan agar simple, karena biasanya operator buat user dulu baru siswa, atau sebaliknya -->
                        
                    </div>

                    <div class="card-footer d-flex justify-content-between bg-white">
                        <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection