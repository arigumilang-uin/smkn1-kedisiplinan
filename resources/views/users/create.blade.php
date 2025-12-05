@extends('layouts.app')

@section('title', 'Tambah User Baru')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/users/create.css') }}">
@endsection

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Form Tambah Pengguna</h3>
                </div>
                
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        
                        <!-- DATA AKUN -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Informasi:</strong> Nama, Username, dan Password akan di-generate otomatis oleh sistem berdasarkan role dan konfigurasi yang Anda pilih.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role (Jabatan) <span class="text-danger">*</span></label>
                                    <select name="role_id" id="roleSelect" class="form-control @error('role_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Role --</option>
                                        @foreach($roles as $role)
                                            @php
                                                $roleName = $role->nama_role ?? 'N/A';
                                                $isKepsek = $roleName === 'Kepala Sekolah';
                                                $disabled = ($isKepsek && isset($kepsekExists) && $kepsekExists) ? 'disabled' : '';
                                            @endphp
                                            <option value="{{ $role->id }}" data-role-name="{{ $roleName }}" {{ old('role_id') == $role->id ? 'selected' : '' }} {{ $disabled }}>{{ $roleName }}@if($isKepsek && isset($kepsekExists) && $kepsekExists) — (dipegang oleh: {{ $kepsekUsername ?? '—' }})@endif</option>
                                        @endforeach
                                    </select>
                                    @error('role_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Alamat Email" value="{{ old('email') }}" required>
                                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted d-block">
                                        Email ini dapat diubah oleh user nantinya di halaman "Akun Saya".
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor HP / Kontak (Opsional)</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Contoh: 0812xxxxxxx" value="{{ old('phone') }}">
                                    @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted d-block">
                                        Untuk Wali Murid, kontak utama tetap diambil dari data siswa (nomor HP wali murid).
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Password <span class="text-danger">*</span></label>
                                    <input type="text" name="password" class="form-control" value="123456" required>
                                    <small class="text-muted">Default: 123456</small>
                                </div>
                            </div>
                        </div>

                        <!-- AREA KHUSUS WALI KELAS -->
                        <div id="waliSection" style="display:none; margin-top: 1rem;">
                            <div class="form-group">
                                <label>Kelas yang diampu (Wali Kelas)</label>
                                <select name="kelas_id" id="kelasSelect" class="form-control @error('kelas_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" data-wali-id="{{ $k->wali_kelas_user_id ?? '' }}" data-wali-name="{{ optional($k->waliKelas)->nama ?? '' }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}@if($k->wali_kelas_user_id) — (dipegang oleh: {{ optional($k->waliKelas)->username ?? '—' }})@endif</option>
                                    @endforeach
                                </select>
                                @error('kelas_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                <small class="text-muted d-block mt-1">Pilih kelas jika akun ini adalah Wali Kelas. Kelas yang sudah mempunyai wali dinonaktifkan.</small>
                            </div>
                        </div>

                        <!-- AREA KHUSUS KAPRODI -->
                        <div id="kaprodiSection" style="display: none; margin-top: 1rem;">
                            <div class="form-group">
                                <label>Jurusan yang diampu (Kaprodi)</label>
                                <select name="jurusan_id" id="jurusanSelect" class="form-control @error('jurusan_id') is-invalid @enderror">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach($jurusan as $j)
                                        <option value="{{ $j->id }}" data-kaprodi-id="{{ $j->kaprodi_user_id ?? '' }}" data-kaprodi-name="{{ optional($j->kaprodi)->nama ?? '' }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}@if($j->kaprodi_user_id) — (dipegang oleh: {{ optional($j->kaprodi)->username ?? '—' }})@endif</option>
                                    @endforeach
                                </select>
                                @error('jurusan_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                <small class="text-muted d-block mt-1">Pilih jurusan jika akun ini adalah Kaprodi. Jurusan yang sudah mempunyai Kaprodi dinonaktifkan.</small>
                            </div>
                        </div>

                        <hr>

                        <!-- ========================================== -->
                        <!-- AREA KHUSUS WALI MURID (KONSEP BARU)        -->
                        <!-- ========================================== -->
                        <div id="siswaSection" style="display: none;">
                            <div class="card border-primary">
                                <div class="card-header bg-primary py-2">
                                    <h3 class="card-title" style="font-size: 1rem;"><i class="fas fa-child mr-1"></i> Pilih Siswa (Anak)</h3>
                                </div>
                                <div class="card-body">
                                    
                                    <!-- 1. PANEL FILTER & PENCARIAN -->
                                    <div class="filter-box">
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label class="small text-muted">Tingkat</label>
                                                <select id="filterTingkat" class="form-control form-control-sm">
                                                    <option value="">- Semua -</option>
                                                    <option value="X">Kelas X</option>
                                                    <option value="XI">Kelas XI</option>
                                                    <option value="XII">Kelas XII</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="small text-muted">Jurusan</label>
                                                <select id="filterJurusan" class="form-control form-control-sm">
                                                    <option value="">- Semua -</option>
                                                    @foreach($jurusan as $j)
                                                        <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="small text-muted">Kelas</label>
                                                <select id="filterKelas" class="form-control form-control-sm">
                                                    <option value="">- Semua -</option>
                                                    @foreach($kelas as $k)
                                                        <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}">{{ $k->nama_kelas }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="small text-muted">Cari Nama / NISN</label>
                                                <input type="text" id="searchSiswa" class="form-control form-control-sm" placeholder="Ketik nama...">
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="button" class="btn btn-xs btn-secondary" onclick="resetFilters()">
                                                <i class="fas fa-undo"></i> Reset Filter
                                            </button>
                                        </div>
                                    </div>

                                    <!-- 2. DAFTAR SISWA (CHECKBOX LIST) -->
                                    <div class="form-group">
                                        <label>Daftar Siswa (Centang yang sesuai):</label>
                                        <div class="student-list-container">
                                            <!-- Loop semua siswa, tapi kita kontrol tampilannya via JS -->
                                            @foreach($siswa as $s)
                                                @php
                                                    // Persiapan data attribut untuk filtering
                                                    $tingkat = explode(' ', $s->kelas->nama_kelas ?? '')[0];
                                                    $jurusanId = $s->kelas->jurusan_id ?? '';
                                                    $kelasId = $s->kelas_id;
                                                    $searchText = strtolower($s->nama_siswa . ' ' . $s->nisn);
                                                @endphp
                                                
                                                <div class="student-item" 
                                                     data-tingkat="{{ $tingkat }}"
                                                     data-jurusan="{{ $jurusanId }}"
                                                     data-kelas="{{ $kelasId }}"
                                                     data-search="{{ $searchText }}">
                                                    
                                                    <label class="d-flex align-items-center">
                                                        <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="student-checkbox">
                                                        <div>
                                                            <span class="font-weight-bold text-primary">{{ $s->nama_siswa }}</span>
                                                            <small class="d-block text-muted">
                                                                {{ $s->kelas->nama_kelas ?? 'No Kelas' }} | NISN: {{ $s->nisn }}
                                                            </small>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                            
                                            <!-- Pesan jika tidak ada hasil -->
                                            <div id="noResultMsg" style="display:none; padding: 20px; text-align: center; color: #888;">
                                                <i class="fas fa-search"></i> Tidak ada siswa yang cocok dengan filter.
                                            </div>
                                        </div>
                                        <small class="text-muted mt-1 d-block">* Daftar di atas otomatis diperbarui saat Anda mengubah filter.</small>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- END AREA WALI MURID -->

                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function(){
            const roleSelect = document.getElementById('roleSelect');
            const kaprodiSection = document.getElementById('kaprodiSection');
            const jurusanSelect = document.getElementById('jurusanSelect');
            const waliSection = document.getElementById('waliSection');
            const kelasSelect = document.getElementById('kelasSelect');

            function toggleSections() {
                const opt = roleSelect.options[roleSelect.selectedIndex];
                const roleName = opt ? opt.dataset.roleName : '';
                // Kaprodi
                if (roleName === 'Kaprodi') {
                    kaprodiSection.style.display = '';
                } else {
                    kaprodiSection.style.display = 'none';
                    if (jurusanSelect) jurusanSelect.value = '';
                }

                // Wali Kelas
                if (roleName === 'Wali Kelas') {
                    waliSection.style.display = '';
                } else {
                    waliSection.style.display = 'none';
                    if (kelasSelect) kelasSelect.value = '';
                }
            }

            function disableAssignedJurusan() {
                if (!jurusanSelect) return;
                for (let i = 0; i < jurusanSelect.options.length; i++) {
                    const opt = jurusanSelect.options[i];
                    const kaprodiId = opt.dataset.kaprodiId || '';
                    if (kaprodiId && kaprodiId !== '') {
                        opt.disabled = true;
                    }
                }
            }

            function disableAssignedKelas() {
                if (!kelasSelect) return;
                for (let i = 0; i < kelasSelect.options.length; i++) {
                    const opt = kelasSelect.options[i];
                    const waliId = opt.dataset.waliId || '';
                    if (waliId && waliId !== '') {
                        opt.disabled = true;
                    }
                }
            }

            roleSelect.addEventListener('change', toggleSections);
            document.addEventListener('DOMContentLoaded', function(){ toggleSections(); disableAssignedJurusan(); disableAssignedKelas(); });
        })();
    </script>
@endpush