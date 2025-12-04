@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('css/pages/siswa/edit.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- HEADER -->
    <div class="row mb-3 pt-2">
        <div class="col-sm-6">
            <h4 class="m-0 text-dark font-weight-bold">
                @if(Auth::user()->hasRole('Wali Kelas'))
                    <i class="fas fa-phone-alt text-info mr-2"></i> Update Kontak
                @else
                    <i class="fas fa-user-edit text-warning mr-2"></i> Edit Data Siswa
                @endif
            </h4>
        </div>
        <div class="col-sm-6 text-right">
            <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm border rounded">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Data Siswa
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            @php 
                $isWaliKelas = Auth::user()->hasRole('Wali Kelas');
                // Warna Card: Info (Biru Muda) untuk Wali, Warning (Kuning) untuk Admin
                $cardClass = $isWaliKelas ? 'card-info' : 'card-warning'; 
                $readOnlyAttr = $isWaliKelas ? 'readonly' : '';
            @endphp

            <div class="card {{ $cardClass }} card-outline shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title font-weight-bold text-dark">Formulir Perubahan Data</h3>
                </div>
                
                <form action="{{ route('siswa.update', $siswa->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body bg-light">
                        
                        <!-- ALERT KHUSUS WALI KELAS -->
                        @if($isWaliKelas)
                        <div class="alert alert-light border-info text-info shadow-sm mb-4">
                            <div class="d-flex">
                                <div class="mr-3"><i class="fas fa-info-circle fa-2x"></i></div>
                                <div>
                                    <strong>Info Akses:</strong><br>
                                    Sebagai Wali Kelas, Anda hanya diizinkan mengubah <u>Nomor HP Wali Murid</u>. 
                                    Untuk perbaikan Nama atau NISN, silakan hubungi Operator Sekolah.
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <!-- Kolom Kiri -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-muted">NISN</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fas fa-id-card text-secondary"></i></span>
                                        </div>
                                        <input type="text" name="nisn" class="form-control form-control-clean" 
                                               value="{{ $siswa->nisn }}" {{ $readOnlyAttr }} required>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-muted">Nama Lengkap</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fas fa-user text-secondary"></i></span>
                                        </div>
                                        <input type="text" name="nama_siswa" class="form-control form-control-clean" 
                                               value="{{ $siswa->nama_siswa }}" {{ $readOnlyAttr }} required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-muted">Kelas</label>
                                    @if($isWaliKelas)
                                        <!-- Tampilan Readonly untuk Wali Kelas -->
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fas fa-chalkboard text-secondary"></i></span>
                                            </div>
                                            <input type="text" class="form-control form-control-clean" 
                                                   value="{{ $siswa->kelas->nama_kelas }}" readonly>
                                        </div>
                                        <input type="hidden" name="kelas_id" value="{{ $siswa->kelas_id }}">
                                    @else
                                        <!-- Dropdown Select2 untuk Operator -->
                                        <select name="kelas_id" class="form-control select2" required data-placeholder="-- Pilih Kelas --">
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                                                    {{ $k->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-primary">Nomor HP Wali Murid (WA)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-primary text-success"><i class="fab fa-whatsapp"></i></span>
                                        </div>
                                             <input type="text" name="nomor_hp_wali_murid" class="form-control form-control-clean border-primary" 
                                                 value="{{ $siswa->nomor_hp_wali_murid }}" placeholder="Contoh: 081234567890">
                                    </div>
                                    <small class="text-muted font-italic">Pastikan nomor aktif (WhatsApp).</small>
                                </div>
                            </div>
                        </div>

                        <!-- AKUN WALI MURID (KHUSUS OPERATOR) -->
                        @if(!$isWaliKelas)
                        <div class="form-group mt-3">
                            <label class="form-label text-muted">Akun Login Wali Murid</label>
                            <select name="wali_murid_user_id" class="form-control select2" data-placeholder="-- Pilih Akun --">
                                <option value=""></option>
                                @foreach($waliMurid as $wali)
                                    <option value="{{ $wali->id }}" {{ $siswa->wali_murid_user_id == $wali->id ? 'selected' : '' }}>
                                        {{ $wali->nama }} ({{ $wali->username }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted font-italic">Hubungkan siswa dengan akun login aplikasi.</small>
                        </div>
                        @endif

                    </div>

                    <div class="card-footer bg-white d-flex justify-content-end py-3">
                        <a href="{{ route('siswa.index') }}" class="btn btn-default mr-2">Batal</a>
                        <button type="submit" class="btn {{ $isWaliKelas ? 'btn-info' : 'btn-warning' }} px-4 font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/pages/siswa/edit.js') }}"></script>
@endpush