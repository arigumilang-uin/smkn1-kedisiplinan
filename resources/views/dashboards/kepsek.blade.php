@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
<div class="container-fluid">
    
    <!-- 1. STATISTIK RINGKAS (KPI CARDS) -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="text-dark font-weight-bold">
                <i class="fas fa-chart-pie text-primary mr-2"></i> Ringkasan Eksekutif
            </h5>
        </div>
    </div>

    <div class="row">
        <!-- Total Siswa -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSiswa }}</h3>
                    <p>Total Siswa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <a href="{{ route('siswa.index') }}" class="small-box-footer">Lihat Daftar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <!-- Pelanggaran Bulan Ini -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pelanggaranBulanIni }}</h3>
                    <p>Pelanggaran Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('riwayat.index', ['bulan' => now()->month]) }}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Pelanggaran Semester -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $pelanggaranSemesterIni }}</h3>
                    <p>Pelanggaran Tahun Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('riwayat.index') }}" class="small-box-footer">Laporan <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Menunggu Persetujuan (ALERT) -->
        <div class="col-lg-3 col-6">
            <div class="small-box {{ $listPersetujuan->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                <div class="inner">
                    <h3>{{ $listPersetujuan->count() }}</h3>
                    <p>{{ $listPersetujuan->count() > 0 ? 'Menunggu Tanda Tangan' : 'Semua Aman' }}</p>
                </div>
                <div class="icon">
                    <i class="fas {{ $listPersetujuan->count() > 0 ? 'fa-file-signature' : 'fa-check-circle' }}"></i>
                </div>
                @if($listPersetujuan->count() > 0)
                    <a href="#approval-section" class="small-box-footer">Proses <i class="fas fa-arrow-circle-right"></i></a>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. TREN PELANGGARAN (GRAFIK 7 HARI TERAKHIR) -->
    <div class="row mt-3">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-chart-area mr-2"></i> Tren Pelanggaran (7 Hari Terakhir)
                    </h3>
                </div>
                <div class="card-body">
                    <div id="trend-chart" style="height: 300px;"></div>
                </div>
                <div class="card-footer text-muted text-sm">
                    <p>Data pelanggaran per hari untuk memonitor tren positif/negatif</p>
                </div>
            </div>
        </div>

        <!-- 3. TOP JENIS PELANGGARAN -->
        <div class="col-md-4">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-list-ol mr-2"></i> Top Pelanggaran
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($topViolations->isEmpty())
                        <div class="text-center p-3 text-muted">
                            <p>Tidak ada data pelanggaran</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($topViolations as $v)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $v->jenisPelanggaran->nama_pelanggaran ?? 'N/A' }}</span>
                                <span class="badge badge-danger badge-pill">{{ $v->jumlah }}</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4. STATISTIK PER JURUSAN -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-sitemap mr-2"></i> Ringkasan per Jurusan
                    </h3>
                </div>
                <div class="card-body table-responsive">
                    @if($jurusanStats->isEmpty())
                        <p class="text-muted text-center">Tidak ada data jurusan</p>
                    @else
                        <table class="table table-sm table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Jurusan</th>
                                    <th class="text-center">Jumlah Siswa</th>
                                    <th class="text-center">Total Pelanggaran</th>
                                    <th class="text-center">Tindakan Terbuka</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jurusanStats as $j)
                                <tr>
                                    <td class="font-weight-bold">{{ $j->nama }}</td>
                                    <td class="text-center"><span class="badge badge-info">{{ $j->siswa_count }}</span></td>
                                    <td class="text-center"><span class="badge badge-warning">{{ $j->pelanggaran_count }}</span></td>
                                    <td class="text-center"><span class="badge badge-{{ $j->tindakan_terbuka > 0 ? 'danger' : 'success' }}">{{ $j->tindakan_terbuka }}</span></td>
                                    <td class="text-center">
                                        @if($j->pelanggaran_count == 0)
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Baik</span>
                                        @elseif($j->tindakan_terbuka > 0)
                                            <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Perhatian</span>
                                        @else
                                            <span class="text-warning"><i class="fas fa-info-circle"></i> Terkontrol</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 5. DAFTAR TUGAS (APPROVAL - PRIORITY) -->
    <div class="row mt-3">
        <div class="col-12" id="approval-section">
            <div class="card card-outline {{ $listPersetujuan->count() > 0 ? 'card-danger' : 'card-success' }} shadow-sm">
                <div class="card-header bg-{{ $listPersetujuan->count() > 0 ? 'danger' : 'success' }}">
                    <h3 class="card-title font-weight-bold text-white">
                        @if($listPersetujuan->count() > 0)
                            <i class="fas fa-bell mr-2"></i> Perlu Tindakan Segera
                        @else
                            <i class="fas fa-check-double mr-2"></i> Status Approval
                        @endif
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            {{ $listPersetujuan->count() }} Pending
                        </span>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    @if($listPersetujuan->isEmpty())
                        <div class="text-center p-5">
                            <i class="fas fa-clipboard-check fa-4x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">Tidak ada dokumen yang memerlukan persetujuan.</h5>
                            <p class="text-muted small">Semua proses berjalan lancar.</p>
                        </div>
                    @else
                        <table class="table table-hover table-striped projects">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 12%">Tanggal</th>
                                    <th style="width: 18%">Siswa</th>
                                    <th style="width: 20%">Pelanggaran</th>
                                    <th style="width: 20%">Rekomendasi</th>
                                    <th style="width: 15%">Dari</th>
                                    <th style="width: 15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listPersetujuan as $kasus)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $kasus->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $kasus->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 font-weight-bold text-primary">{{ $kasus->siswa->nama_siswa }}</h6>
                                        <small class="text-muted">{{ $kasus->siswa->kelas->nama_kelas ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger mb-1">Kasus</span><br>
                                        <small>{{ Str::limit($kasus->pemicu, 30) }}</small>
                                    </td>
                                    <td>
                                        <div class="p-2 bg-light border-left border-danger rounded text-dark font-weight-bold text-sm">
                                            {{ $kasus->sanksi_deskripsi ?? 'Belum ditentukan' }}
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">Oleh: <strong>{{ $kasus->user->nama ?? 'Operator' }}</strong></small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('kasus.edit', $kasus->id) }}" class="btn btn-primary btn-sm" title="Tinjau dan Setujui">
                                            <i class="fas fa-eye mr-1"></i> Tinjau
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
    </div>

    <!-- 7. SISWA PERLU PEMBINAAN (WIDGET) -->
    @if($siswaPerluPembinaan->count() > 0)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-check text-warning mr-2"></i> Siswa Perlu Pembinaan Internal (Top 5)
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="10%">NIS</th>
                                <th width="25%">Nama Siswa</th>
                                <th width="15%">Kelas</th>
                                <th width="10%" class="text-center">Total Poin</th>
                                <th width="15%">Range</th>
                                <th width="25%">Rekomendasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siswaPerluPembinaan as $item)
                            <tr>
                                <td>{{ $item['siswa']->nis }}</td>
                                <td>
                                    <a href="{{ route('siswa.show', $item['siswa']->id) }}" class="text-primary">
                                        <strong>{{ $item['siswa']->nama_lengkap }}</strong>
                                    </a>
                                </td>
                                <td>{{ $item['siswa']->kelas->nama_kelas ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $item['total_poin'] > 300 ? 'danger' : ($item['total_poin'] > 100 ? 'warning' : 'info') }}">
                                        {{ $item['total_poin'] }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $item['rekomendasi']['range_text'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $item['rekomendasi']['keterangan'] }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        Pembinaan internal adalah rekomendasi konseling, bukan trigger surat otomatis.
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Include Chart.js for trend visualization (optional) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Trend Chart Data
        const trendLabels = {!! $trendData->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toJson() !!};
        const trendValues = {!! $trendData->pluck('total')->toJson() !!};

        if (trendLabels.length > 0) {
            const ctx = document.getElementById('trend-chart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Pelanggaran',
                        data: trendValues,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#ffc107',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection