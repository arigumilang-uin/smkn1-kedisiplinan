@extends('layouts.app')

@section('title', 'Catat Pelanggaran')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/pelanggaran/create.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="row mb-2">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

    <!-- HEADER & BREADCRUMB -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-edit text-primary mr-2"></i> Input Pelanggaran
            </h4>
            @php
                $role = auth()->user()->effectiveRoleName() ?? auth()->user()->role?->nama_role;
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
                                    <input type="checkbox" name="siswa_id[]" value="{{ $siswa->id }}" class="siswa-checkbox">
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
                                    <input type="checkbox" name="jenis_pelanggaran_id[]" value="{{ $jp->id }}" class="pelanggaran-checkbox">
                                </div>
                            @endforeach
                            
                            <div id="noViolationMsg" class="text-center py-5" style="display:none;">
                                <p class="text-muted small mb-0">Pelanggaran tidak ditemukan.</p>
                            </div>
                        </div>
                        @error('jenis_pelanggaran_id') <div class="text-danger small mb-2"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div> @enderror

                        <!-- Form Input Tambahan -->
                        <div class="bg-white p-3 border rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Tanggal Kejadian</label>
                                           <div class="form-row">
                                               <div class="col">
                                                   <input type="date" name="tanggal_kejadian" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                               </div>
                                            <div class="col" style="max-width:120px;">
                                                <input type="time" id="jamKejadian" name="jam_kejadian" class="form-control form-control-sm" value="{{ old('jam_kejadian', date('H:i')) }}" data-has-old="{{ old('jam_kejadian') ? '1' : '0' }}">
                                            </div>
                                           </div>
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
                            <div class="form-group mb-3 mt-2">
                                <label class="small font-weight-bold">Keterangan / Kronologi</label>
                                <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Opsional..."></textarea>
                            </div>

                                                        <button type="submit" id="btnSubmitPreview" class="btn btn-primary btn-block font-weight-bold shadow-sm">
                                                            <i class="fas fa-save mr-1"></i> SIMPAN DATA
                                                        </button>
                        
                                                <!-- Konfirmasi Modal -->
                                                <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Pencatatan Pelanggaran</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div>
                                                                        <p><strong>Siswa terpilih:</strong></p>
                                                                        <ul id="confirmStudents"></ul>
                                                                        <p><strong>Pelanggaran terpilih:</strong></p>
                                                                        <ul id="confirmViolations"></ul>
                                                                        <p><strong>Waktu kejadian:</strong> <span id="confirmTime"></span></p>
                                                                        <p><strong>Keterangan:</strong> <span id="confirmKeterangan"></span></p>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                <button type="button" id="btnConfirmSubmit" class="btn btn-primary">Konfirmasi & Simpan</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
    <script src="{{ asset('js/pages/pelanggaran/create.js') }}"></script>
@endpush