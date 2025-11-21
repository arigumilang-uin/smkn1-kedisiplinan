@extends('layouts.app')

@section('title', 'Tambah User Baru')

@section('content')
<style>
    /* Styling untuk area Filter */
    .filter-box {
        background-color: #f4f6f9;
        border: 1px solid #ced4da;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
    }
    /* Styling untuk Daftar Siswa (Scrollable) */
    .student-list-container {
        max-height: 300px; /* Tinggi maksimal list */
        overflow-y: auto;  /* Scroll jika data banyak */
        border: 1px solid #ced4da;
        background: #fff;
        border-radius: 4px;
    }
    .student-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    .student-item:hover {
        background-color: #e8f4ff;
    }
    .student-item:last-child {
        border-bottom: none;
    }
    .student-item label {
        cursor: pointer;
        font-weight: normal !important;
        margin-bottom: 0;
        width: 100%;
    }
    /* Checkbox custom size */
    .student-checkbox {
        transform: scale(1.2);
        margin-right: 10px;
    }
</style>

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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Masukkan nama lengkap" value="{{ old('nama') }}" required>
                                    @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role (Jabatan) <span class="text-danger">*</span></label>
                                    <select name="role_id" id="roleSelect" class="form-control @error('role_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Role --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->nama_role }}</option>
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
                                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Username login" value="{{ old('username') }}" required>
                                    @error('username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Alamat Email" value="{{ old('email') }}" required>
                                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Password <span class="text-danger">*</span></label>
                                    <input type="text" name="password" class="form-control" value="123456" required>
                                    <small class="text-muted">Default: 123456</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- ========================================== -->
                        <!-- AREA KHUSUS ORANG TUA (KONSEP BARU)        -->
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
                        <!-- END AREA ORANG TUA -->

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
    $(document).ready(function() {
        // 1. Logika Tampilkan/Sembunyikan Section Orang Tua
        $('#roleSelect').on('change', function() {
            var text = $(this).find("option:selected").text();
            if(text.includes('Orang Tua')) {
                $('#siswaSection').slideDown();
            } else {
                $('#siswaSection').slideUp();
                // Opsional: Uncheck semua jika bukan orang tua
                // $('.student-checkbox').prop('checked', false);
            }
        }).trigger('change');

        // 2. LOGIKA FILTERING REAL-TIME
        function filterList() {
            var fTingkat = $('#filterTingkat').val();
            var fJurusan = $('#filterJurusan').val();
            var fKelas = $('#filterKelas').val();
            var fSearch = $('#searchSiswa').val().toLowerCase();

            var visibleCount = 0;

            // Loop setiap item siswa
            $('.student-item').each(function() {
                var item = $(this);
                var sTingkat = item.data('tingkat');
                var sJurusan = item.data('jurusan');
                var sKelas = item.data('kelas');
                var sSearch = item.data('search');

                var match = true;

                // Cek Filter Dropdown
                if(fTingkat && sTingkat != fTingkat) match = false;
                if(fJurusan && sJurusan != fJurusan) match = false;
                if(fKelas && sKelas != fKelas) match = false;

                // Cek Pencarian Teks (Nama/NISN)
                if(fSearch && !sSearch.includes(fSearch)) match = false;

                // Tampilkan/Sembunyikan
                if(match) {
                    item.show();
                    visibleCount++;
                } else {
                    item.hide();
                }
            });

            // Tampilkan pesan jika kosong
            if(visibleCount === 0) {
                $('#noResultMsg').show();
            } else {
                $('#noResultMsg').hide();
            }
        }

        // Pasang Event Listener ke semua input filter
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
            $('#filterKelas').val(''); // Reset kelas saat jurusan berubah
            filterList(); // Jalankan filter ulang
        });
    });

    // Fungsi Reset
    function resetFilters() {
        $('#filterTingkat').val('');
        $('#filterJurusan').val('');
        $('#filterKelas').val('');
        $('#searchSiswa').val('');
        // Trigger change agar list ter-refresh
        $('#filterJurusan').trigger('change');
    }
</script>
@endpush