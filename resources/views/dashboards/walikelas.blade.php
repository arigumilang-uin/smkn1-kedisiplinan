@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas - ' . $kelas->nama_kelas)

@section('content')
<div class="container-fluid">

    <!-- 1. WELCOME & INFO KELAS -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="callout callout-success shadow-sm border-left-success">
                <h5><i class="fas fa-chalkboard-teacher mr-2 text-success"></i> Kelas Binaan: <strong>{{ $kelas->nama_kelas }}</strong></h5>
                <p class="text-muted mb-0">Selamat datang, Bapak/Ibu Wali Kelas. Berikut adalah ringkasan kedisiplinan siswa Anda.</p>
            </div>
        </div>
    </div>

    <!-- 2. STATISTIK RINGKAS (SMALL BOX) -->
    <div class="row">
        <!-- Total Siswa -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $kelas->siswa->count() }}</h3>
                    <p>Total Siswa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
        
        <!-- Kasus Perlu Ditangani -->
        <div class="col-lg-4 col-6">
            <div class="small-box {{ $kasusBaru->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                <div class="inner">
                    <h3>{{ $kasusBaru->count() }}</h3>
                    <p>{{ $kasusBaru->count() > 0 ? 'Kasus Perlu Ditangani' : 'Kelas Aman' }}</p>
                </div>
                <div class="icon">
                    <i class="fas {{ $kasusBaru->count() > 0 ? 'fa-exclamation-circle' : 'fa-check-circle' }}"></i>
                </div>
            </div>
        </div>

        <!-- Total Riwayat (Bulan Ini - Opsional, hitung dari koleksi) -->
        <div class="col-lg-4 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $riwayatTerbaru->count() }}</h3>
                    <p>Riwayat Terbaru</p>
                </div>
                <div class="icon">
                    <i class="fas fa-history"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <!-- 3. KOLOM KIRI: KASUS PRIORITAS (ACTIONABLE) -->
        <div class="col-lg-5 col-md-12 mb-4">
            <div class="card card-outline {{ $kasusBaru->count() > 0 ? 'card-danger' : 'card-success' }} h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        @if($kasusBaru->count() > 0)
                            <i class="fas fa-bell text-danger mr-1"></i> Perlu Tindakan Anda
                        @else
                            <i class="fas fa-check-double text-success mr-1"></i> Status Kelas
                        @endif
                    </h3>
                    <div class="card-tools">
                        <span class="badge {{ $kasusBaru->count() > 0 ? 'badge-danger' : 'badge-success' }}">
                            {{ $kasusBaru->count() }} Kasus
                        </span>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    @if($kasusBaru->isEmpty())
                        <div class="text-center p-5">
                            <i class="fas fa-shield-alt fa-4x text-success mb-3" style="opacity: 0.5;"></i>
                            <h5>Tidak ada kasus aktif.</h5>
                            <p class="text-muted small">Terima kasih telah membimbing siswa dengan baik.</p>
                        </div>
                    @else
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kasusBaru as $kasus)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold text-dark">{{ $kasus->siswa->nama_siswa }}</span><br>
                                        <small class="text-muted">{{ Str::limit($kasus->pemicu, 25) }}</small>
                                    </td>
                                    <td>
                                        @if($kasus->status == 'Menunggu Persetujuan')
                                            <span class="badge badge-warning">Tunggu ACC</span>
                                        @elseif($kasus->status == 'Baru')
                                            <span class="badge badge-danger">Baru</span>
                                        @else
                                            <span class="badge badge-info">{{ $kasus->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('kasus.edit', $kasus->id) }}" class="btn btn-primary btn-sm btn-sm" title="Kelola Kasus">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. KOLOM KANAN: RIWAYAT PELANGGARAN (LOG) -->
        <div class="col-lg-7 col-md-12">
            
            <!-- Filter Tanggal -->
            <div class="card card-outline card-primary collapsed-card mb-3 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Riwayat</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body bg-light pt-2 pb-2">
                    <form action="{{ route('dashboard.walikelas') }}" method="GET" class="form-inline justify-content-end">
                        <label class="mr-2 small text-muted">Periode:</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm mr-2 mb-2">
                        <span class="mr-2 mb-2">-</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm mr-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm mb-2"><i class="fas fa-search"></i></button>
                        <a href="{{ route('dashboard.walikelas') }}" class="btn btn-default btn-sm mb-2 ml-1"><i class="fas fa-undo"></i></a>
                    </form>
                </div>
            </div>

            <!-- Tabel Riwayat -->
            <div class="card h-100 shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold">📜 Riwayat Pelanggaran Terbaru</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Pelanggaran</th>
                                <th class="text-center">Poin</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatTerbaru as $r)
                            <tr>
                                <td class="text-muted small">{{ $r->tanggal_kejadian->format('d/m/y') }}</td>
                                <td><span class="font-weight-bold">{{ $r->siswa->nama_siswa }}</span></td>
                                <td>
                                    <span class="d-block text-sm">{{ $r->jenisPelanggaran->nama_pelanggaran }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger">+{{ $r->jenisPelanggaran->poin }}</span>
                                </td>
                                <td>
                                        @if($r->bukti_foto_path)
                                            <a href="{{ route('bukti.show', ['path' => $r->bukti_foto_path]) }}" target="_blank" class="text-primary" title="Lihat Bukti">
                                                <i class="fas fa-image"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i><br>
                                    Belum ada data pelanggaran pada periode ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection