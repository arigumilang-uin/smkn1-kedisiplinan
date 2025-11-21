@extends('layouts.app')

@section('title', 'Catat Pelanggaran')

@section('content')

<style>
    /* --- ELEGANT UI CUSTOMIZATION --- */
    
    /* Scrollable Area dengan Scrollbar Halus */
    .scroll-area {
        height: 500px;
        overflow-y: auto;
        padding-right: 5px;
    }
    .scroll-area::-webkit-scrollbar { width: 5px; }
    .scroll-area::-webkit-scrollbar-track { background: #f1f1f1; }
    .scroll-area::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .scroll-area::-webkit-scrollbar-thumb:hover { background: #aaa; }

    /* 1. KARTU PILIHAN (Siswa & Pelanggaran) */
    .selection-card {
        position: relative;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: flex;
        align-items: center;
    }

    .selection-card:hover {
        border-color: #adb5bd;
        background-color: #f8f9fa;
        transform: translateX(3px);
    }

    /* State Selected: Siswa (Biru) */
    .student-item.selected {
        background-color: #e7f1ff;
        border: 1px solid #007bff;
        box-shadow: 0 2px 4px rgba(0,123,255,0.1);
    }
    /* State Selected: Pelanggaran (Merah) */
    .violation-item.selected {
        background-color: #fdf2f3;
        border: 1px solid #dc3545;
        box-shadow: 0 2px 4px rgba(220,53,69,0.1);
    }

    /* Avatar Circle */
    .avatar-box {
        width: 42px; height: 42px;
        background-color: #e9ecef;
        color: #495057;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 1.1rem;
        margin-right: 12px; flex-shrink: 0;
    }
    .student-item.selected .avatar-box { background-color: #007bff; color: #fff; }

    /* Badge Poin */
    .point-badge {
        background-color: #f1f3f5; color: #495057;
        font-weight: 700; font-size: 0.8rem;
        padding: 4px 10px; border-radius: 20px;
        min-width: 50px; text-align: center;
    }
    .violation-item.selected .point-badge { background-color: #dc3545; color: #fff; }

    /* Radio hidden */
    input[type="radio"] { display: none; }

    /* Filter Tabs (Pills) */
    .filter-pills .btn {
        border-radius: 50px;
        font-size: 0.85rem;
        padding: 5px 15px;
        margin-right: 5px;
        margin-bottom: 5px;
        border: 1px solid #dee2e6;
        background-color: #fff;
        color: #6c757d;
    }
    .filter-pills .btn:hover { background-color: #f8f9fa; }
    .filter-pills .btn.active {
        background-color: #343a40;
        color: #fff;
        border-color: #343a40;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }
</style>

<div class="container-fluid">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-edit text-primary mr-2"></i> Input Pelanggaran
            </h4>
            @php
                $role = auth()->user()->role->nama_role;
                $backRoute = match($role) {
                    'Wali Kelas' => route('dashboard.walikelas'),
                    'Kaprodi' => route('dashboard.kaprodi'),
                    'Kepala Sekolah' => route('dashboard.kepsek'),
                    default => route('dashboard.admin'),
                };
            @endphp
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <form action="{{ route('pelanggaran.store') }}" method="POST" enctype="multipart/form-data" id="formPelanggaran">
        @csrf
        
        <div class="row">
            
            <!-- ========================================== -->
            <!-- KOLOM KIRI: DATA SISWA (STEP 1)            -->
            <!-- ========================================== -->
            <div class="col-lg-5 col-md-12 mb-4">
                <div class="card card-primary card-outline h-100 shadow-sm">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold text-dark">1. Pilih Siswa</h3>
                        <div class="card-tools">
                            <span class="badge badge-light border" id="countSiswa">{{ count($daftarSiswa) }} Siswa</span>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        
                        <!-- Filter Bar -->
                        <div class="row px-1 mb-2">
                            <div class="col-4 px-1">
                                <select id="filterTingkat" class="form-control form-control-sm shadow-none border-secondary">
                                    <option value="">Tingkat...</option>
                                    <option value="X">Kelas X</option>
                                    <option value="XI">Kelas XI</option>
                                    <option value="XII">Kelas XII</option>
                                </select>
                            </div>
                            <div class="col-4 px-1">
                                <select id="filterJurusan" class="form-control form-control-sm shadow-none border-secondary">
                                    <option value="">Jurusan...</option>
                                    @foreach($jurusan as $j) <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-4 px-1">
                                <select id="filterKelas" class="form-control form-control-sm shadow-none border-secondary">
                                    <option value="">Kelas...</option>
                                    @foreach($kelas as $k) <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan_id }}">{{ $k->nama_kelas }}</option> @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Search Input -->
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" id="searchSiswa" class="form-control border-right-0" placeholder="Ketik Nama Siswa atau NISN...">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default" onclick="resetFilters()"><i class="fas fa-sync-alt"></i></button>
                            </div>
                        </div>

                        <!-- LIST SISWA -->
                        <div class="scroll-area bg-white border rounded p-2" id="studentListContainer">
                            @foreach($daftarSiswa as $siswa)
                                @php
                                    $tingkat = explode(' ', $siswa->kelas->nama_kelas ?? '')[0];
                                    $jurusanId = $siswa->kelas->jurusan_id ?? '';
                                    $searchText = strtolower($siswa->nama_siswa . ' ' . $siswa->nisn);
                                    $initial = strtoupper(substr($siswa->nama_siswa, 0, 1));
                                @endphp
                                
                                <div class="selection-card student-item" 
                                     data-tingkat="{{ $tingkat }}"
                                     data-jurusan="{{ $jurusanId }}"
                                     data-kelas="{{ $siswa->kelas_id }}"
                                     data-search="{{ $searchText }}"
                                     onclick="selectStudent(this)">
                                    
                                    <div class="avatar-box">{{ $initial }}</div>
                                    <div style="line-height: 1.2; width: 100%;">
                                        <div class="font-weight-bold text-dark">{{ $siswa->nama_siswa }}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <small class="text-muted"><i class="fas fa-users mr-1"></i> {{ $siswa->kelas->nama_kelas ?? '-' }}</small>
                                            <small class="text-muted badge badge-light border">{{ $siswa->nisn }}</small>
                                        </div>
                                    </div>
                                    <input type="radio" name="siswa_id" value="{{ $siswa->id }}" required>
                                </div>
                            @endforeach
                            
                            <div id="noResultMsg" class="text-center py-5" style="display:none;">
                                <p class="text-muted small">Siswa tidak ditemukan.</p>
                            </div>
                        </div>
                        @error('siswa_id') <div class="alert alert-danger mt-2 py-1 px-2 text-sm"><i class="fas fa-info-circle"></i> {{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- KOLOM KANAN: DATA PELANGGARAN (STEP 2)     -->
            <!-- ========================================== -->
            <div class="col-lg-7 col-md-12">
                <div class="card card-danger card-outline h-100 shadow-sm">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold text-dark">2. Data Pelanggaran</h3>
                    </div>
                    <div class="card-body bg-light">
                        
                        <!-- Filter Topik (Pills) -->
                        <div class="filter-pills btn-group-toggle mb-2" data-toggle="buttons">
                            <label class="btn active" onclick="setFilterTopic('all', this)">
                                <input type="radio" checked> Semua
                            </label>
                            <label class="btn" onclick="setFilterTopic('atribut', this)">
                                <input type="radio"> 👔 Atribut
                            </label>
                            <label class="btn" onclick="setFilterTopic('kehadiran', this)">
                                <input type="radio"> ⏰ Absensi
                            </label>
                            <label class="btn" onclick="setFilterTopic('kerapian', this)">
                                <input type="radio"> 💇‍♂️ Kerapian
                            </label>
                            <label class="btn" onclick="setFilterTopic('ibadah', this)">
                                <input type="radio"> 🕌 Ibadah
                            </label>
                            <!-- Topik Berat Merah -->
                            <label class="btn text-danger border-danger font-weight-bold" onclick="setFilterTopic('berat', this)">
                                <input type="radio"> ⚠️ BERAT
                            </label>
                        </div>

                        <!-- Search Pelanggaran -->
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" id="searchPelanggaran" class="form-control border-right-0" placeholder="Cari Masalah (Contoh: 'Bolos', 'Rokok', 'Telat')...">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-danger"></i></span>
                            </div>
                        </div>

                        <!-- LIST PELANGGARAN -->
                        <div class="scroll-area bg-white border rounded p-2 mb-3" style="height: 300px;">
                            @foreach($daftarPelanggaran as $jp)
                                @php
                                    $kategoriLower = strtolower($jp->kategoriPelanggaran->nama_kategori);
                                    $namaLower = strtolower($jp->nama_pelanggaran);
                                @endphp

                                <div class="selection-card violation-item" 
                                     data-nama="{{ $namaLower }}"
                                     data-kategori="{{ $kategoriLower }}"
                                     onclick="selectViolation(this)">
                                    
                                    <div style="flex-grow: 1;">
                                        <div class="font-weight-bold text-dark">{{ $jp->nama_pelanggaran }}</div>
                                        <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                            {{ $jp->kategoriPelanggaran->nama_kategori }}
                                        </small>
                                    </div>
                                    
                                    <span class="point-badge">{{ $jp->poin }} Poin</span>
                                    <input type="radio" name="jenis_pelanggaran_id" value="{{ $jp->id }}" required>
                                </div>
                            @endforeach
                            
                            <div id="noViolationMsg" class="text-center py-5" style="display:none;">
                                <p class="text-muted small">Pelanggaran tidak ditemukan.<br>Gunakan kata kunci lain.</p>
                            </div>
                        </div>
                        @error('jenis_pelanggaran_id') <div class="text-danger small mb-2"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div> @enderror

                        <!-- Form Input Tambahan -->
                        <div class="bg-white p-3 border rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Tanggal Kejadian</label>
                                        <input type="date" name="tanggal_kejadian" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Bukti Foto (Wajib)</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="bukti_foto" id="customFile" accept="image/*" required>
                                            <label class="custom-file-label col-form-label-sm" for="customFile">Pilih file...</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0 mt-2">
                                <label class="small font-weight-bold">Kronologi / Keterangan</label>
                                <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Opsional..."></textarea>
                            </div>
                        </div>

                        <div class="mt-3 text-right">
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> SIMPAN DATA
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

<script>
    var activeTopic = 'all';

    // 1. VISUAL SELECTION (JQUERY)
    function selectStudent(el) {
        $('.student-item').removeClass('selected');
        $(el).addClass('selected');
        $(el).find('input').prop('checked', true);
    }

    function selectViolation(el) {
        $('.violation-item').removeClass('selected');
        $(el).addClass('selected');
        $(el).find('input').prop('checked', true);
    }

    $(document).ready(function() {
        bsCustomFileInput.init();

        // ==================================
        // 2. LOGIKA FILTER SISWA
        // ==================================
        function filterStudents() {
            var fTingkat = $('#filterTingkat').val();
            var fJurusan = $('#filterJurusan').val();
            var fKelas = $('#filterKelas').val();
            var fSearch = $('#searchSiswa').val().toLowerCase();
            var visible = 0;

            $('.student-item').each(function() {
                var el = $(this);
                var match = true;
                if(fTingkat && el.data('tingkat') != fTingkat) match = false;
                if(fJurusan && el.data('jurusan') != fJurusan) match = false;
                if(fKelas && el.data('kelas') != fKelas) match = false;
                if(fSearch && !el.data('search').includes(fSearch)) match = false;
                
                if(match) { el.show(); visible++; } else { el.hide(); }
            });
            
            $('#countSiswa').text(visible + ' Siswa');
            if(visible===0) $('#noResultMsg').show(); else $('#noResultMsg').hide();
        }

        $('#filterTingkat, #filterJurusan, #filterKelas').on('change', filterStudents);
        $('#searchSiswa').on('keyup', filterStudents);
        $('#filterJurusan').on('change', function() {
            var jurId = $(this).val();
            $('#filterKelas option').each(function() {
                var kJur = $(this).data('jurusan');
                if(!jurId || !kJur || kJur == jurId) $(this).show(); else $(this).hide();
            });
            $('#filterKelas').val('');
            filterStudents();
        });
        window.resetFilters = function() {
            $('#filterTingkat, #filterJurusan, #filterKelas, #searchSiswa').val('').trigger('change');
            $('.student-item').removeClass('selected');
            $('input[name="siswa_id"]').prop('checked', false);
        };

        // ==================================
        // 3. FILTER PELANGGARAN (SMART SEARCH)
        // ==================================
        const topics = {
            'atribut': ['dasi', 'topi', 'kaos', 'baju', 'seragam', 'ikat', 'sabuk', 'sepatu', 'logo', 'atribut', 'pakaian'],
            'kehadiran': ['lambat', 'telat', 'bolos', 'cabut', 'alfa', 'absen', 'keluar', 'pulang'],
            'kerapian': ['rambut', 'kuku', 'panjang', 'cat', 'warna', 'gondrong', 'make up'],
            'ibadah': ['sholat', 'doa', 'jumat', 'mengaji', 'ibadah', 'musholla'],
            'berat': ['rokok', 'vape', 'hantam', 'pukul', 'kelahi', 'tajam', 'curi', 'maling', 'porno', 'bokep', 'narkoba', 'miras', 'bully', 'ancam', 'palak', 'rusak', 'sangat']
        };
        
        const aliasMap = {
            'rokok': ['sebat', 'asap', 'bakar', 'surya', 'udud', 'vape'],
            'bolos': ['alfamart', 'warnet', 'kantin', 'wc', 'minggat'],
            'terlambat': ['telat', 'kesiangan'],
            'berkelahi': ['gelut', 'ribut', 'tawuran', 'tumbuk'],
            'atribut': ['topi', 'dasi', 'sabuk'],
            'pornografi': ['bokep', 'blue', 'video', '18+'],
            'sajam': ['pisau', 'clurit', 'cutter']
        };

        function filterViolations() {
            var fSearch = $('#searchPelanggaran').val().toLowerCase();
            var visible = 0;

            $('.violation-item').each(function() {
                var el = $(this);
                var nama = el.data('nama');
                var kategoriDB = el.data('kategori');
                var match = true;

                if(activeTopic !== 'all') {
                    var topicMatch = false;
                    // Logic Berat: Cek DB Category ATAU Keyword
                    if(activeTopic === 'berat') {
                        if(kategoriDB.includes('berat') || kategoriDB.includes('sangat')) topicMatch = true;
                    }
                    if(!topicMatch && topics[activeTopic]) {
                        if(topics[activeTopic].some(w => nama.includes(w))) topicMatch = true;
                    }
                    if(!topicMatch) match = false;
                }

                if(match && fSearch) {
                    var textMatch = false;
                    if(nama.includes(fSearch)) textMatch = true;
                    else {
                        Object.keys(aliasMap).forEach(function(key) {
                            if(nama.includes(key)) {
                                if(aliasMap[key].some(a => a.includes(fSearch))) textMatch = true;
                            }
                        });
                    }
                    if(!textMatch) match = false;
                }

                if(match) { el.show(); visible++; } else { el.hide(); }
            });
            if(visible===0) $('#noViolationMsg').show(); else $('#noViolationMsg').hide();
        }

        window.setFilterTopic = function(topic, btn) {
            activeTopic = topic;
            $('.filter-pills .btn').removeClass('active');
            $(btn).addClass('active');
            filterViolations();
        }

        $('#searchPelanggaran').on('keyup', function() { filterViolations(); });
    });
</script>
@endpush