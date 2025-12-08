@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-edit mr-2"></i>
                        Edit Profile
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="nama" 
                                           id="nama"
                                           class="form-control @error('nama') is-invalid @enderror" 
                                           value="{{ old('nama', $user->nama) }}" 
                                           required>
                                    @error('nama')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           name="email" 
                                           id="email"
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" 
                                           required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Nomor Telepon</label>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone"
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="Contoh: 081234567890">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $user->username }}" 
                                           disabled>
                                    <small class="text-muted">Username tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                            <a href="{{ route('profile.change-password.form') }}" class="btn btn-warning float-right">
                                <i class="fas fa-key mr-1"></i> Ganti Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
