@extends('layouts.app')

@section('title', 'Dashboard Operator')

@section('content')

<div class="container-fluid">
    
    <!-- WELCOME MESSAGE -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="callout callout-info shadow-sm">
                <h5><i class="fas fa-user-cog mr-2"></i> Selamat Datang, {{ Auth::user()->nama }}!</h5>
                <p>Anda berada di Panel Operator Sekolah. Silakan kelola data induk sistem di bawah ini.</p>
            </div>
        </div>
    </div>

    <!-- BAGIAN 1: MANAJEMEN DATA INDUK -->
    <h5 class="mb-3 text-dark font-weight-bold"><i class="fas fa-database text-primary mr-2"></i> Manajemen Data Induk</h5>
    
    <div class="row">
        <!-- CARD USER -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow">
                <div class="inner">
                    <h3>{{ $totalUser }}</h3>
                    <p>Data Pengguna</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <a href="{{ route('users.index') }}" class="small-box-footer">
                    Kelola User <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- CARD SISWA -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow">
                <div class="inner">
                    <h3>{{ $totalSiswa }}</h3>
                    <p>Data Siswa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <a href="{{ route('siswa.index') }}" class="small-box-footer">
                    Kelola Siswa <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- CARD ATURAN -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow">
                <div class="inner">
                    <h3>{{ $totalAturan }}</h3>
                    <p>Aturan & Poin</p>
                </div>
                <div class="icon">
                    <i class="fas fa-gavel"></i>
                </div>
                <a href="{{ route('jenis-pelanggaran.index') }}" class="small-box-footer">
                    Kelola Aturan <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- CARD KELAS (DISABLED / COMING SOON) -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary shadow">
                <div class="overlay dark">
                    <i class="fas fa-lock fa-3x"></i>
                </div>
                <div class="inner">
                    <h3>{{ $totalKelas }}</h3>
                    <p>Data Kelas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-school"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Fitur Terkunci <i class="fas fa-ban"></i>
                </a>
            </div>
        </div>
    </div>

    <hr>

    <!-- BAGIAN 2: SHORTCUT CEPAT -->
    <h5 class="mb-3 text-dark font-weight-bold"><i class="fas fa-rocket text-warning mr-2"></i> Aksi Cepat</h5>
    
    <div class="row">
        <div class="col-md-4 col-sm-6 mb-3">
            <a href="{{ route('users.create') }}" class="btn btn-app bg-white shadow-sm btn-block text-left pl-3 border">
                <span class="badge bg-purple">Baru</span>
                <i class="fas fa-user-plus text-purple" style="font-size: 2rem; float: right;"></i>
                <strong>Tambah User</strong><br>
                Daftarkan Guru/Ortu baru
            </a>
        </div>
        
        <!-- Contoh Shortcut Lain jika ada -->
        <div class="col-md-4 col-sm-6 mb-3">
            <a href="{{ route('siswa.index') }}" class="btn btn-app bg-white shadow-sm btn-block text-left pl-3 border">
                <i class="fas fa-id-card text-success" style="font-size: 2rem; float: right;"></i>
                <strong>Cari Siswa</strong><br>
                Lihat profil siswa
            </a>
        </div>
    </div>

</div>
@endsection