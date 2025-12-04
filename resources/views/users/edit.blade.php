@extends('layouts.app')

@section('title', 'Edit User')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/users/edit.css') }}">
@endsection

@push('scripts')
    <script>
        (function(){
            const roleSelect = document.getElementById('roleSelect');
            const kaprodiSection = document.getElementById('kaprodiSection');
            const jurusanSelect = document.getElementById('jurusanSelect');
            const waliSection = document.getElementById('waliSection');
            const kelasSelect = document.getElementById('kelasSelect');
            const currentUserId = '{{ $user->id }}';

            function toggleSections() {
                const opt = roleSelect.options[roleSelect.selectedIndex];
                const roleName = opt ? opt.dataset.roleName : '';
                // Kaprodi atau Developer
                if (roleName === 'Kaprodi' || roleName === 'Developer') {
                    kaprodiSection.style.display = '';
                } else {
                    kaprodiSection.style.display = 'none';
                    if (jurusanSelect) jurusanSelect.value = '';
                }

                // Wali Kelas atau Developer
                if (roleName === 'Wali Kelas' || roleName === 'Developer') {
                    waliSection.style.display = '';
                } else {
                    waliSection.style.display = 'none';
                    if (kelasSelect) kelasSelect.value = '';
                }

                // Wali Murid atau Developer
                const siswaSection = document.getElementById('siswaSection');
                if (roleName === 'Wali Murid' || roleName === 'Developer') {
                    siswaSection.style.display = '';
                } else {
                    siswaSection.style.display = 'none';
                }
            }

            function disableAssignedJurusan() {
                if (!jurusanSelect) return;
                for (let i = 0; i < jurusanSelect.options.length; i++) {
                    const opt = jurusanSelect.options[i];
                    const kaprodiId = opt.dataset.kaprodiId || '';
                    if (kaprodiId && kaprodiId !== '' && kaprodiId !== currentUserId) {
                        opt.disabled = true;
                    }
                }
            }

            function disableAssignedKelas() {
                if (!kelasSelect) return;
                for (let i = 0; i < kelasSelect.options.length; i++) {
                    const opt = kelasSelect.options[i];
                    const waliId = opt.dataset.waliId || '';
                    if (waliId && waliId !== '' && waliId !== currentUserId) {
                        opt.disabled = true;
                    }
                }
            }

            roleSelect.addEventListener('change', toggleSections);
            document.addEventListener('DOMContentLoaded', function(){ toggleSections(); disableAssignedJurusan(); disableAssignedKelas(); });
        })();
    </script>
@endpush

@section('content')

<div class="container-fluid">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-edit mr-1"></i> Edit User: <strong>{{ $user->nama }}</strong></h3>
        </div>
        
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                
                <!-- BAGIAN 1: DATA AKUN -->
                <h5 class="text-muted mb-3"><i class="fas fa-id-card mr-1"></i> Data Akun</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                                   value="{{ old('nama', $user->nama) }}" required>
                            @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Role (Jabatan) <span class="text-danger">*</span></label>
                            <select name="role_id" id="roleSelect" class="form-control @error('role_id') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    @php
                                        $roleName = $role->nama_role ?? 'N/A';
                                        $isKepsek = $roleName === 'Kepala Sekolah';
                                        $disabled = ($isKepsek && isset($kepsekExists) && $kepsekExists && (!isset($kepsekId) || $kepsekId != $user->id)) ? 'disabled' : '';
                                    @endphp
                                    <option value="{{ $role->id }}" data-role-name="{{ $roleName }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }} {{ $disabled }}>
                                        {{ $roleName }}@if($isKepsek && isset($kepsekExists) && $kepsekExists && (!isset($kepsekId) || $kepsekId != $user->id)) — (dipegang oleh: {{ $kepsekUsername ?? '—' }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" 
                                   value="{{ old('username', $user->username) }}" required>
                            @error('username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak ganti">
                            <small class="text-muted">Isi hanya jika ingin mengubah password.</small>
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <!-- BAGIAN 2: HUBUNGKAN SISWA (KHUSUS WALI MURID) -->
                <!-- BAGIAN KAPRODI/DEVELOPER: pilih jurusan jika user adalah Kaprodi atau Developer -->
                <div id="kaprodiSection" style="display:none; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label>Jurusan yang diampu (Kaprodi/Developer)</label>
                        <select name="jurusan_id" id="jurusanSelect" class="form-control @error('jurusan_id') is-invalid @enderror">
                            <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusan as $j)
                                <option value="{{ $j->id }}" data-kaprodi-id="{{ $j->kaprodi_user_id ?? '' }}" data-kaprodi-name="{{ optional($j->kaprodi)->nama ?? '' }}" {{ (old('jurusan_id', $user->jurusanDiampu->id ?? '') == $j->id) ? 'selected' : '' }}>{{ $j->nama_jurusan }}@if($j->kaprodi_user_id && $j->kaprodi_user_id != $user->id) — (dipegang oleh: {{ optional($j->kaprodi)->username ?? '—' }})@endif</option>
                            @endforeach
                        </select>
                        @error('jurusan_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        <small class="text-muted d-block mt-1">Pilih jurusan jika akun ini adalah Kaprodi atau Developer. Jurusan yang sudah mempunyai Kaprodi lain dinonaktifkan.</small>
                    </div>
                </div>

                <!-- AREA KHUSUS WALI KELAS/DEVELOPER -->
                <div id="waliSection" style="display:none; margin-top: 1rem;">
                    <div class="form-group">
                        <label>Kelas yang diampu (Wali Kelas/Developer)</label>
                        <select name="kelas_id" id="kelasSelect" class="form-control @error('kelas_id') is-invalid @enderror">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" data-wali-id="{{ $k->wali_kelas_user_id ?? '' }}" data-wali-name="{{ optional($k->waliKelas)->nama ?? '' }}" {{ (old('kelas_id', $user->kelasDiampu->id ?? '') == $k->id) ? 'selected' : '' }}>{{ $k->nama_kelas }}@if($k->wali_kelas_user_id && $k->wali_kelas_user_id != $user->id) — (dipegang oleh: {{ optional($k->waliKelas)->username ?? '—' }})@endif</option>
                            @endforeach
                        </select>
                        @error('kelas_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        <small class="text-muted d-block mt-1">Pilih kelas jika akun ini adalah Wali Kelas atau Developer. Kelas yang sudah mempunyai wali lain dinonaktifkan.</small>
                    </div>
                </div>

                <div id="siswaSection" style="display: none;">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark py-2">
                            <h3 class="card-title" style="font-size: 1rem;"><i class="fas fa-child mr-1"></i> Hubungkan dengan Siswa (Wali Murid/Developer)</h3>
                        </div>
                        <div class="card-body bg-light">
                            
                            <!-- PANEL FILTER -->
                            <div class="filter-box">
                                <label class="mb-2 text-dark"><i class="fas fa-filter"></i> Filter Pencarian Siswa:</label>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <select id="filterTingkat" class="form-control form-control-sm">
                                            <option value="">- Semua Tingkat -</option>
                                            <option value="X">Kelas X</option>
                                            <option value="XI">Kelas XI</option>
                                            <option value="XII">Kelas XII</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <select id="filterJurusan" class="form-control form-control-sm">
                                            <option value="">- Semua Jurusan -</option>
                                            @foreach($jurusan as $j)
                                                <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <select id="filterKelas" class="form-control form-control-sm">
                                            <option value="">- Semua Kelas -</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}">{{ $k->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="text" id="searchSiswa" class="form-control form-control-sm" placeholder="Cari Nama/NISN...">
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-xs btn-secondary" onclick="resetFilters()">
                                        <i class="fas fa-undo"></i> Reset Filter
                                    </button>
                                </div>
                            </div>

                            <!-- DAFTAR SISWA -->
                            <div class="form-group">
                                <label>Pilih Siswa (Centang yang sesuai):</label>
                                <div class="student-list-container">
                                    
                                    @php
                                        // LOGIKA SORTING DI BLADE
                                        // Kita ingin siswa yang SUDAH TERHUBUNG muncul paling atas
                                        // agar operator langsung melihatnya.
                                        
                                        $connectedIds = $connectedSiswaIds ?? [];
                                        
                                        // Urutkan collection: True (terhubung) di atas False
                                        $sortedSiswa = $siswa->sortByDesc(function($s) use ($connectedIds) {
                                            return in_array($s->id, $connectedIds);
                                        });
                                    @endphp

                                    @foreach($sortedSiswa as $s)
                                        @php
                                            // Data atribut untuk filtering JS
                                            $tingkat = explode(' ', $s->kelas->nama_kelas ?? '')[0];
                                            $jurusanId = $s->kelas->jurusan_id ?? '';
                                            $kelasId = $s->kelas_id;
                                            $searchText = strtolower($s->nama_siswa . ' ' . $s->nisn);
                                            
                                            // Status koneksi
                                            $isChecked = in_array($s->id, $connectedIds);
                                        @endphp
                                        
                                        <div class="student-item {{ $isChecked ? 'connected' : '' }}" 
                                             data-tingkat="{{ $tingkat }}"
                                             data-jurusan="{{ $jurusanId }}"
                                             data-kelas="{{ $kelasId }}"
                                             data-search="{{ $searchText }}">
                                            
                                            <label>
                                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="student-checkbox" 
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                
                                                <div class="d-flex justify-content-between w-100 align-items-center pr-2">
                                                    <div>
                                                        <span class="font-weight-bold text-dark">{{ $s->nama_siswa }}</span>
                                                        <small class="d-block text-muted">
                                                            {{ $s->kelas->nama_kelas ?? 'No Kelas' }} | NISN: {{ $s->nisn }}
                                                        </small>
                                                    </div>
                                                    
                                                    <!-- Badge Penanda Visual -->
                                                    @if($isChecked)
                                                        <span class="badge badge-success"><i class="fas fa-check"></i> Anak Saat Ini</span>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                    
                                    <div id="noResultMsg" style="display:none; padding: 20px; text-align: center; color: #888;">
                                        <i class="fas fa-search"></i> Tidak ada siswa yang cocok dengan filter.
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">* Daftar otomatis diurutkan: Siswa yang terhubung ada di paling atas.</small>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- END AREA WALI MURID -->

            

            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('users.index') }}" class="btn btn-default">Batal</a>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // Filtering functions untuk daftar siswa
        function resetFilters() {
            document.getElementById('filterTingkat').value = '';
            document.getElementById('filterJurusan').value = '';
            document.getElementById('filterKelas').value = '';
            document.getElementById('searchSiswa').value = '';
            filterStudents();
        }

        function filterStudents() {
            const tingkat = document.getElementById('filterTingkat')?.value || '';
            const jurusan = document.getElementById('filterJurusan')?.value || '';
            const kelas = document.getElementById('filterKelas')?.value || '';
            const search = document.getElementById('searchSiswa')?.value?.toLowerCase() || '';

            const items = document.querySelectorAll('.student-item');
            let visibleCount = 0;

            items.forEach(item => {
                let show = true;
                if (tingkat && item.dataset.tingkat !== tingkat) show = false;
                if (jurusan && item.dataset.jurusan !== jurusan) show = false;
                if (kelas && item.dataset.kelas !== kelas) show = false;
                if (search && !item.dataset.search.includes(search)) show = false;

                item.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            const noResultMsg = document.getElementById('noResultMsg');
            if (noResultMsg) {
                noResultMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        // Event listeners untuk filter
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterTingkat')?.addEventListener('change', filterStudents);
            document.getElementById('filterJurusan')?.addEventListener('change', filterStudents);
            document.getElementById('filterKelas')?.addEventListener('change', filterStudents);
            document.getElementById('searchSiswa')?.addEventListener('keyup', filterStudents);
        });
    </script>
@endpush