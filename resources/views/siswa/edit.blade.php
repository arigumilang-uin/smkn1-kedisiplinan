@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <!-- TAMPILAN BEDA WARNA UNTUK WALI KELAS -->
            @php 
                $isWaliKelas = (Auth::user()->role->nama_role == 'Wali Kelas');
                $cardClass = $isWaliKelas ? 'card-info' : 'card-primary';
                // Style background abu-abu muda untuk input readonly agar terlihat jelas terkunci
                $readOnlyAttr = $isWaliKelas ? 'readonly style=background-color:#e9ecef;' : '';
            @endphp

            <div class="card {{ $cardClass }} shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        @if($isWaliKelas)
                            <i class="fas fa-phone-alt mr-2"></i> Update Kontak Siswa
                        @else
                            <i class="fas fa-user-edit mr-2"></i> Edit Data Siswa
                        @endif
                    </h3>
                </div>
                
                <form action="{{ route('siswa.update', $siswa->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        
                        @if($isWaliKelas)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> Sebagai Wali Kelas, Anda hanya diizinkan mengubah <strong>Nomor HP Orang Tua</strong>. Jika ada kesalahan Nama/NISN, silakan hubungi Operator Sekolah.
                        </div>
                        @endif

                        <div class="form-group">
                            <label>NISN</label>
                            <input type="text" name="nisn" class="form-control" value="{{ $siswa->nisn }}" {{ $readOnlyAttr }} required>
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_siswa" class="form-control" value="{{ $siswa->nama_siswa }}" {{ $readOnlyAttr }} required>
                        </div>

                        <div class="form-group">
                            <label>Kelas</label>
                            @if($isWaliKelas)
                                <!-- Input Dummy untuk Tampilan -->
                                <input type="text" class="form-control" value="{{ $siswa->kelas->nama_kelas }}" readonly style="background-color:#e9ecef;">
                                <!-- Input Hidden untuk Value Asli (Agar tidak error validasi required di backend) -->
                                <input type="hidden" name="kelas_id" value="{{ $siswa->kelas_id }}">
                            @else
                                <select name="kelas_id" class="form-control">
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="text-primary font-weight-bold">Nomor HP/WA Orang Tua</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                </div>
                                <input type="number" name="nomor_hp_ortu" class="form-control border-primary" value="{{ $siswa->nomor_hp_ortu }}" placeholder="08xxxxxxxx">
                            </div>
                            <small class="text-muted">Pastikan nomor aktif dan terhubung ke WhatsApp.</small>
                        </div>

                        @if(!$isWaliKelas)
                        <div class="form-group">
                            <label>Akun Login Orang Tua</label>
                            <select name="orang_tua_user_id" class="form-control">
                                <option value="">-- Belum Terhubung --</option>
                                @foreach($orangTua as $ortu)
                                    <option value="{{ $ortu->id }}" {{ $siswa->orang_tua_user_id == $ortu->id ? 'selected' : '' }}>
                                        {{ $ortu->nama }} ({{ $ortu->username }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hubungkan siswa ini dengan akun login orang tua yang sudah dibuat di menu "Manajemen User".</small>
                        </div>
                        @endif

                    </div>
                    <div class="card-footer d-flex justify-content-between bg-white">
                        <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Batal
                        </a>
                        <button type="submit" class="btn {{ $isWaliKelas ? 'btn-info' : 'btn-primary' }} px-4">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection