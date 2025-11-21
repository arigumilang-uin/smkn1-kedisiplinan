@extends('layouts.app')

@section('title', 'Dashboard Kaprodi')

@section('content')
<div class="container-fluid">

    <!-- WELCOME CALLOUT -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="callout callout-info shadow-sm border-left-primary">
                <h5><i class="fas fa-chalkboard-teacher mr-2 text-primary"></i> Program Studi: <strong>{{ $jurusan->nama_jurusan }}</strong></h5>
                <p class="text-muted mb-0">Berikut adalah laporan kedisiplinan siswa di bawah naungan jurusan Anda.</p>
            </div>
        </div>
    </div>

    <!-- 1. STATISTIK (SMALL BOX) -->
    <div class="row">
        <!-- Total Siswa -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSiswa }}</h3>
                    <p>Total Siswa Jurusan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <!-- Pelanggaran Bulan Ini -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $pelanggaranBulanIni }}</h3>
                    <p>Pelanggaran Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
            </div>
        </div>
        
        <!-- Kasus Aktif -->
        <div class="col-lg-4 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $kasusAktif }}</h3>
                    <p>Kasus Belum Selesai</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. AREA UTAMA (FILTER, TABEL, GRAFIK) -->
    <div class="row">
        
        <!-- KOLOM KIRI: TABEL RIWAYAT -->
        <div class="col-lg-8 col-md-12">
            
            <!-- Filter Collapsed -->
            <div class="card card-outline card-primary collapsed-card mb-3 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Data Lanjutan</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <form action="{{ route('dashboard.kaprodi') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small text-muted">Dari Tanggal</label>
                                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small text-muted">Sampai Tanggal</label>
                                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small text-muted">Kelas</label>
                                    <select name="kelas_id" class="form-control form-control-sm">
                                        <option value="">Semua Kelas</option>
                                        @foreach($kelasJurusan as $k)
                                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                             <a href="{{ route('dashboard.kaprodi') }}" class="btn btn-default btn-sm mr-1"><i class="fas fa-undo"></i> Reset</a>
                             <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i> Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="card h-100 shadow-sm card-outline card-secondary">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold">📝 Riwayat Pelanggaran Terbaru</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ count($riwayatTerbaru) }} Data Terakhir</span>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Pelanggaran</th>
                                <th class="text-center">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatTerbaru as $r)
                            <tr>
                                <td class="text-muted small">{{ $r->tanggal_kejadian->format('d/m/y') }}</td>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ $r->siswa->nama_siswa }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light border">{{ $r->siswa->kelas->nama_kelas }}</span>
                                </td>
                                <td>
                                    <span class="d-block text-sm">{{ $r->jenisPelanggaran->nama_pelanggaran }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger">+{{ $r->jenisPelanggaran->poin }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i><br>
                                    Belum ada data pelanggaran untuk periode ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: GRAFIK STATISTIK -->
        <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">📊 Statistik per Kelas</h3>
                </div>
                <div class="card-body">
                    <div class="chart-responsive">
                        <canvas id="kelasChart" height="250"></canvas>
                    </div>
                    <p class="text-center text-muted small mt-3">
                        Grafik menampilkan jumlah total pelanggaran yang tercatat berdasarkan filter tanggal.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Script Chart.js -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('kelasChart');
        
        if (ctx) {
            // Data dari Controller
            const labels = {!! json_encode($chartLabels) !!};
            const data = {!! json_encode($chartData) !!};

            // Pesan jika data kosong
            if(labels.length === 0) {
                // Opsional: Handle UI empty chart
            }

            new Chart(ctx, {
                type: 'bar', // Bar chart lebih cocok untuk perbandingan kelas
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Pelanggaran',
                        data: data,
                        backgroundColor: '#17a2b8', // Warna Info/Cyan AdminLTE
                        borderColor: '#117a8b',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { display: false }, // Sembunyikan legenda karena cuma 1 dataset
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' Kasus';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1 } 
                        },
                        x: {
                            ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection