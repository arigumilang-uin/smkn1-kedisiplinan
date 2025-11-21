@extends('layouts.app')

@section('title', 'Dashboard Kesiswaan')

@section('content')
<div class="container-fluid">

    <!-- 1. WELCOME & STATS -->
    <div class="row mb-2">
        <div class="col-12">
            <h5 class="text-dark font-weight-bold">
                <i class="fas fa-tachometer-alt text-primary mr-2"></i> Ringkasan Statistik
            </h5>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $kasusAktif }}</h3>
                    <p>Kasus Aktif</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $butuhPersetujuan }}</h3>
                    <p>Menunggu ACC</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pelanggaranFiltered }}</h3>
                    <p>Total Pelanggaran (Filter)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalSiswa }}</h3>
                    <p>Total Siswa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 2. FILTER DATA (KIRI) -->
        <div class="col-md-12">
            <div class="card card-outline card-primary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Data Lanjutan</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <form action="{{ route('dashboard.admin') }}" method="GET">
                        <input type="hidden" name="chart_type" value="{{ $chartType }}">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="small text-muted">Dari Tanggal</label>
                                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-muted">Sampai Tanggal</label>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted">Tingkat</label>
                                <select name="angkatan" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="X" {{ request('angkatan') == 'X' ? 'selected' : '' }}>Kelas X</option>
                                    <option value="XI" {{ request('angkatan') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                    <option value="XII" {{ request('angkatan') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted">Jurusan</label>
                                <select name="jurusan_id" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    @foreach($allJurusan as $j)
                                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted">Kelas</label>
                                <select name="kelas_id" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    @foreach($allKelas as $k)
                                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <a href="{{ route('dashboard.admin') }}" class="btn btn-default btn-sm mr-1"><i class="fas fa-undo"></i> Reset</a>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 3. TABEL AKTIVITAS TERBARU (KIRI - LEBAR) -->
        <div class="col-lg-8">
            <div class="card card-outline card-secondary h-100">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-dark">🚨 Aktivitas Kasus Terbaru</h3>
                    <div class="card-tools">
                        <a href="{{ route('riwayat.index') }}" class="btn btn-tool btn-sm">
                            <i class="fas fa-bars"></i> Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kasus</th>
                            <th class="text-center">Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($daftarKasus as $kasus)
                        <tr>
                            <td class="text-muted small">{{ $kasus->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="font-weight-bold text-dark">{{ $kasus->siswa->nama_siswa }}</span><br>
                                <small class="text-muted">{{ $kasus->siswa->kelas->nama_kelas }}</small>
                            </td>
                            <td>
                                <span class="text-muted small d-block">{{ Str::limit($kasus->pemicu, 30) }}</span>
                                @if($kasus->suratPanggilan)
                                    <span class="badge badge-light border">{{ $kasus->suratPanggilan->tipe_surat }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($kasus->status == 'Baru')
                                    <span class="badge badge-warning px-2">Baru</span>
                                @elseif($kasus->status == 'Menunggu Persetujuan')
                                    <span class="badge badge-danger px-2">Butuh ACC</span>
                                @elseif($kasus->status == 'Disetujui')
                                    <span class="badge badge-primary px-2">Disetujui</span>
                                @elseif($kasus->status == 'Selesai')
                                    <span class="badge badge-success px-2">Selesai</span>
                                @else
                                    <span class="badge badge-secondary">{{ $kasus->status }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('kasus.edit', $kasus->id) }}" class="btn btn-sm btn-default">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Tidak ada data kasus terbaru.</td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. GRAFIK (KANAN - SEMPIT) -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Tren Pelanggaran</h3>
                    <div class="card-tools">
                        <select class="custom-select custom-select-sm" onchange="changeChartType(this.value)" style="width: auto;">
                            <option value="doughnut" {{ $chartType == 'doughnut' ? 'selected' : '' }}>Donut</option>
                            <option value="bar" {{ $chartType == 'bar' ? 'selected' : '' }}>Bar</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-responsive">
                        <canvas id="wakaChart" height="220"></canvas>
                    </div>
                </div>
                <div class="card-footer bg-white p-0">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item border-bottom">
                            <a href="#" class="nav-link text-muted">
                                Grafik menampilkan top 5 kategori pelanggaran tertinggi berdasarkan filter yang aktif.
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT CHART -->
<script>
    function changeChartType(type) {
        let url = new URL(window.location.href);
        url.searchParams.set('chart_type', type);
        window.location.href = url.toString();
    }

    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('wakaChart');
        if (ctx) {
            const labels = {!! json_encode($chartLabels) !!};
            const data = {!! json_encode($chartData) !!};
            const type = "{{ $chartType }}";

            if (labels.length === 0) {
                // Handle empty data visualization if needed
            }

            new Chart(ctx, {
                type: type, 
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah',
                        data: data,
                        backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc'],
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });
</script>
@endsection