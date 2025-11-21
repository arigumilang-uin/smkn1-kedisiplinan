@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<style>
    /* Styling untuk area Filter */
    .filter-box {
        background-color: #fff3cd; /* Warna kuning tipis (khas Edit) */
        border: 1px solid #ffeeba;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
    }
    /* Styling untuk Daftar Siswa (Scrollable) */
    .student-list-container {
        max-height: 350px; /* Sedikit lebih tinggi */
        overflow-y: auto;
        border: 1px solid #ced4da;
        background: #fff;
        border-radius: 4px;
    }
    .student-item {
        padding: 10px 12px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    .student-item:hover {
        background-color: #f8f9fa;
    }
    .student-item:last-child {
        border-bottom: none;
    }
    
    /* Styling khusus untuk siswa yang SUDAH terhubung */
    .student-item.connected {
        background-color: #d4edda; /* Warna Hijau Muda */
        border-left: 4px solid #28a745;
    }
    .student-item.connected:hover {
        background-color: #c3e6cb;
    }

    .student-item label {
        cursor: pointer;
        font-weight: normal !important;
        margin-bottom: 0;
        width: 100%;
        display: flex;
        align-items: center;
    }
    /* Checkbox custom size */
    .student-checkbox {
        transform: scale(1.2);
        margin-right: 15px;
    }
</style>

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
                                    <option value="{{ $role->id }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }}>
                                        {{ $role->nama_role }}
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

                <!-- BAGIAN 2: HUBUNGKAN SISWA (KHUSUS ORANG TUA) -->
                <div id="siswaSection" style="display: none;">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark py-2">
                            <h3 class="card-title" style="font-size: 1rem;"><i class="fas fa-child mr-1"></i> Hubungkan Orang Tua dengan Siswa</h3>
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
                <!-- END AREA ORANG TUA -->

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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // 1. Logika Tampilkan/Sembunyikan Section Orang Tua
        $('#roleSelect').on('change', function() {
            var text = $(this).find("option:selected").text();
            if(text.includes('Orang Tua')) {
                $('#siswaSection').slideDown();
            } else {
                $('#siswaSection').slideUp();
            }
        }).trigger('change');

        // 2. LOGIKA FILTERING REAL-TIME
        function filterList() {
            var fTingkat = $('#filterTingkat').val();
            var fJurusan = $('#filterJurusan').val();
            var fKelas = $('#filterKelas').val();
            var fSearch = $('#searchSiswa').val().toLowerCase();

            var visibleCount = 0;

            $('.student-item').each(function() {
                var item = $(this);
                var sTingkat = item.data('tingkat');
                var sJurusan = item.data('jurusan');
                var sKelas = item.data('kelas');
                var sSearch = item.data('search');

                var match = true;

                if(fTingkat && sTingkat != fTingkat) match = false;
                if(fJurusan && sJurusan != fJurusan) match = false;
                if(fKelas && sKelas != fKelas) match = false;
                if(fSearch && !sSearch.includes(fSearch)) match = false;

                if(match) {
                    item.show();
                    visibleCount++;
                } else {
                    item.hide();
                }
            });

            if(visibleCount === 0) {
                $('#noResultMsg').show();
            } else {
                $('#noResultMsg').hide();
            }
        }

        $('#filterTingkat, #filterJurusan, #filterKelas').on('change', filterList);
        $('#searchSiswa').on('keyup', filterList);

        // 3. Helper: Filter Dropdown Kelas berdasarkan Jurusan
        $('#filterJurusan').on('change', function() {
            var jurId = $(this).val();
            $('#filterKelas option').each(function() {
                var kJur = $(this).data('jurusan');
                if($(this).val() == "" || !jurId || kJur == jurId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#filterKelas').val('');
            filterList();
        });
    });

    function resetFilters() {
        $('#filterTingkat').val('');
        $('#filterJurusan').val('');
        $('#filterKelas').val('');
        $('#searchSiswa').val('');
        $('#filterJurusan').trigger('change');
    }
</script>
@endpush