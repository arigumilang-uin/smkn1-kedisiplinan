@extends('layouts.app')

@section('title', 'Riwayat Pelanggaran')

@section('content')

<style>
    /* --- 1. STICKY FILTER STYLES --- */
    .content-wrapper { overflow: visible !important; }

    #stickyFilter {
        position: -webkit-sticky; /* Safari */
        position: sticky;
        top: 57px; 
        z-index: 1040;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    #stickyFilter.compact-mode {
        border-radius: 0 0 8px 8px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        margin-left: -7.5px;
        margin-right: -7.5px;
        border-top: none;
    }

    #filterHeader {
        transition: all 0.3s ease;
        overflow: hidden;
        max-height: 100px;
        opacity: 1;
    }

    .header-hidden #filterHeader {
        max-height: 0; opacity: 0; padding: 0 !important; border: none;
    }
    .header-hidden .card-body {
        padding: 10px !important; background-color: #ffffff !important;
    }

    /* --- 2. TABLE STYLES (FIXED HEADER) --- */
    .table-wrapper {
        /* KUNCI AGAR HEADER TETAP DIAM: Berikan tinggi maksimal pada container */
        max-height: 70vh; /* Menggunakan 70% tinggi layar */
        overflow-y: auto; 
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
    }
    
    /* Header Tetap Diam (Sticky) */
    .table-premium thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa; /* Wajib warna solid agar tidak transparan */
        color: #495057;
        z-index: 100; /* Pastikan di atas baris data */
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1); /* Bayangan halus di bawah header */
        border-top: none;
        padding: 15px 20px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Styling Scrollbar */
    .table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
    .table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
    .table-wrapper::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 10px; }
    .table-wrapper::-webkit-scrollbar-thumb:hover { background: #6c757d; }

    .table-premium td {
        padding: 12px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
        color: #343a40;
    }
    .table-premium tbody tr:hover { background-color: #f8f9fa; }

    .smart-link {
        text-decoration: none; color: inherit; border-bottom: 1px dashed #adb5bd; transition: all 0.2s;
    }
    .smart-link:hover { color: #007bff; border-bottom-color: #007bff; }

    .form-control-clean {
        border-radius: 4px; border: 1px solid #ced4da; font-size: 0.9rem;
    }
    .form-control-clean:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.15); }

    .avatar-circle {
        width: 35px; height: 35px; background-color: #e9ecef; color: #6c757d;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 0.9rem; margin-right: 10px;
    }
    .badge-poin-total {
        font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 12px;
        background-color: #343a40; color: #fff; display: inline-block; margin-top: 4px;
    }
</style>

<div class="container-fluid">

    <!-- HEADER -->
    <div class="row mb-3 pt-2 align-items-center">
        <div class="col-sm-6">
            <h4 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-history text-primary mr-2"></i> Log Riwayat
            </h4>
        </div>
        <div class="col-sm-6 text-right">
             @php
                $role = auth()->user()->role->nama_role;
                $backRoute = match($role) {
                    'Wali Kelas' => route('dashboard.walikelas'),
                    'Kaprodi' => route('dashboard.kaprodi'),
                    'Kepala Sekolah' => route('dashboard.kepsek'),
                    default => route('dashboard.admin'),
                };
            @endphp
            <div class="btn-group">
                <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm border rounded mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Dashboard
                </a>
                <span class="btn btn-light btn-sm border rounded disabled text-dark font-weight-bold">
                    Total: {{ $riwayat->total() }} Data
                </span>
            </div>
        </div>
    </div>

    <!-- FILTER SECTION (STICKY & SHRINK) -->
    <div id="stickyFilter" class="card card-outline card-primary shadow-sm">
        
        <div id="filterHeader" class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-primary font-weight-bold" style="font-size: 1rem;">
                    <i class="fas fa-filter mr-1"></i> Filter & Pencarian
                </h3>
                
                @if(request()->has('cari_siswa') || request()->has('start_date') || request()->has('jenis_pelanggaran_id'))
                    <a href="{{ route('riwayat.index') }}" class="btn btn-xs btn-light text-danger border font-weight-bold px-3">
                        <i class="fas fa-times mr-1"></i> Reset
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body bg-light">
            <form id="filterForm" action="{{ route('riwayat.index') }}" method="GET">
                <div class="row">
                    
                    <!-- 1. Filter Tanggal -->
                    <div class="col-md-3 mb-2">
                        <label class="filter-label small font-weight-bold text-muted">Rentang Waktu</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-clean" onchange="this.form.submit()">
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text border-left-0 border-right-0 bg-white"><i class="fas fa-arrow-right text-muted small"></i></span>
                            </div>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-clean" onchange="this.form.submit()">
                        </div>
                    </div>

                    <!-- 2. Filter Kelas (Admin Only) -->
                    @if(Auth::user()->role->nama_role != 'Wali Kelas')
                    <div class="col-md-2 mb-2">
                        <label class="filter-label small font-weight-bold text-muted">Kelas</label>
                        <select name="kelas_id" class="form-control form-control-sm form-control-clean" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($allKelas as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- 3. Filter Jenis Pelanggaran -->
                    <div class="col-md-4 mb-2">
                        <label class="filter-label small font-weight-bold text-muted">Jenis Pelanggaran</label>
                        <select name="jenis_pelanggaran_id" class="form-control form-control-sm form-control-clean" onchange="this.form.submit()">
                            <option value="">Semua Jenis</option>
                            @foreach($allPelanggaran as $jp)
                                <option value="{{ $jp->id }}" {{ request('jenis_pelanggaran_id') == $jp->id ? 'selected' : '' }}>
                                    [{{ $jp->kategoriPelanggaran->nama_kategori }}] {{ $jp->nama_pelanggaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Cari Nama (Live) -->
                    <div class="col-md-3 mb-2">
                        <label class="filter-label small font-weight-bold text-muted">Cari Siswa</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="liveSearch" name="cari_siswa" class="form-control form-control-clean" 
                                   placeholder="Ketik nama..." value="{{ request('cari_siswa') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DATA (SCROLLABLE & FIXED HEADER) -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table table-premium text-nowrap w-100">
                    <thead>
                        <tr>
                            <th style="padding-left: 25px;">Waktu</th>
                            <th>Identitas Siswa</th>
                            <th>Detail Pelanggaran</th>
                            <th class="text-center">Poin</th>
                            <th>Dicatat Oleh</th>
                            <th class="text-center">Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                        <tr>
                            <!-- 1. WAKTU -->
                            <td style="padding-left: 25px;">
                                <div class="font-weight-bold text-dark">
                                    <a href="{{ route('riwayat.index', array_merge(request()->all(), ['start_date' => $r->tanggal_kejadian->format('Y-m-d'), 'end_date' => $r->tanggal_kejadian->format('Y-m-d')])) }}" class="smart-link" title="Filter tanggal ini">
                                        {{ $r->tanggal_kejadian->format('d M Y') }}
                                    </a>
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="far fa-clock mr-1"></i> {{ $r->tanggal_kejadian->format('H:i') }} WIB
                                </div>
                            </td>

                            <!-- 2. SISWA -->
                            <td>
                                <div class="d-flex align-items-center">
                                    @php $initial = strtoupper(substr($r->siswa->nama_siswa, 0, 1)); @endphp
                                    <div class="avatar-circle">{{ $initial }}</div>
                                    
                                    <div>
                                        <a href="{{ route('riwayat.index', ['cari_siswa' => $r->siswa->nama_siswa]) }}" class="text-primary font-weight-bold smart-link" title="Lihat riwayat siswa ini">
                                            {{ $r->siswa->nama_siswa }}
                                        </a>
                                        <div class="mt-1">
                                            <a href="{{ route('riwayat.index', ['kelas_id' => $r->siswa->kelas_id]) }}" class="badge badge-light border text-muted" title="Filter kelas">
                                                {{ $r->siswa->kelas->nama_kelas }}
                                            </a>
                                            
                                            @php
                                                $totalPoinSiswa = $r->siswa->riwayatPelanggaran->sum(fn($rp) => $rp->jenisPelanggaran->poin);
                                                $bgTotal = $totalPoinSiswa >= 100 ? 'bg-danger' : ($totalPoinSiswa >= 50 ? 'bg-warning' : 'bg-secondary');
                                            @endphp
                                            <span class="badge {{ $bgTotal }} ml-1" style="font-weight:normal; font-size:0.7rem;">
                                                Total: {{ $totalPoinSiswa }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. PELANGGARAN -->
                            <td style="white-space: normal; min-width: 280px;">
                                <div class="font-weight-bold text-dark">{{ $r->jenisPelanggaran->nama_pelanggaran }}</div>
                                <div class="small text-muted text-uppercase mt-1" style="font-weight: 600; font-size: 0.7rem;">
                                    {{ $r->jenisPelanggaran->kategoriPelanggaran->nama_kategori }}
                                </div>
                                @if($r->keterangan)
                                    <div class="text-muted font-italic small mt-1 pl-2 border-left" style="border-color: #dee2e6;">
                                        "{{ Str::limit($r->keterangan, 50) }}"
                                    </div>
                                @endif
                            </td>

                            <!-- 4. POIN -->
                            <td class="text-center">
                                <span class="badge badge-danger px-3 py-2 shadow-sm" style="font-size: 0.9rem; border-radius: 20px;">
                                    +{{ $r->jenisPelanggaran->poin }}
                                </span>
                            </td>

                            <!-- 5. PELAPOR -->
                            <td>
                                @if($r->guruPencatat)
                                    <!-- Klik untuk filter berdasarkan ID pencatat -->
                                    <a href="{{ route('riwayat.index', ['pencatat_id' => $r->guru_pencatat_user_id]) }}" class="d-flex align-items-center text-dark text-decoration-none group-hover">
                                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-2 border" style="width:32px; height:32px;">
                                            <i class="fas fa-user-tie text-secondary small"></i>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-sm">{{ $r->guruPencatat->nama }}</div>
                                            <div class="text-xs text-muted">Pelapor</div>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-muted"><i class="fas fa-robot mr-1"></i> Sistem</span>
                                @endif
                            </td>

                            <!-- 6. BUKTI -->
                            <td class="text-center">
                                @if($r->bukti_foto_path)
                                    <a href="{{ asset('storage/' . $r->bukti_foto_path) }}" target="_blank" class="btn btn-light btn-sm border rounded-circle shadow-sm" style="width: 35px; height: 35px; padding: 0; line-height: 33px;" title="Lihat Foto">
                                        <i class="fas fa-image text-info"></i>
                                    </a>
                                @else
                                    <span class="text-muted text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-search fa-3x text-gray-200 mb-3"></i>
                                    <h6 class="text-muted font-weight-normal">Data tidak ditemukan.</h6>
                                    <p class="text-muted small">Coba sesuaikan filter pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan <strong>{{ $riwayat->firstItem() ?? 0 }} - {{ $riwayat->lastItem() ?? 0 }}</strong> dari <strong>{{ $riwayat->total() }}</strong> data
                </small>
                <div>
                    {{ $riwayat->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // LOGIC STICKY & SHRINK FILTER
        const stickyFilter = document.getElementById('stickyFilter');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 20) {
                stickyFilter.classList.add('compact-mode');
                stickyFilter.classList.add('header-hidden'); 
            } else {
                stickyFilter.classList.remove('compact-mode');
                stickyFilter.classList.remove('header-hidden'); 
            }
        });

        // LOGIC LIVE SEARCH
        let timeout = null;
        const searchInput = document.getElementById('liveSearch');
        const form = document.getElementById('filterForm');

        if(searchInput){
            searchInput.addEventListener('keyup', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    form.submit(); 
                }, 800);
            });

            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('cari_siswa')) {
                searchInput.focus();
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        }
    });
</script>
@endpush