@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Detail Jurusan: {{ $jurusan->nama_jurusan }}</h2>
            <p class="text-muted mb-0">Kode: <span class="badge bg-secondary">{{ $jurusan->kode_jurusan }}</span></p>
        </div>
        <a href="{{ route('data-jurusan.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Kelas</h6>
                    <h3>{{ $stats['total_kelas'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Siswa</h6>
                    <h3>{{ $stats['total_siswa'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Pelanggaran</h6>
                    <h3>{{ $stats['total_pelanggaran'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Pelanggaran Bulan Ini</h6>
                    <h3>{{ $stats['pelanggaran_bulan_ini'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chart: Pelanggaran per Bulan -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Grafik Pelanggaran (6 Bulan Terakhir)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPelanggaranPerBulan" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart: Pelanggaran per Kategori -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pelanggaran per Kategori</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPelanggaranPerKategori"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Siswa -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Top 10 Siswa dengan Pelanggaran Terbanyak</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th class="text-center">Total Pelanggaran</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSiswa as $index => $item)
                        <tr>
                            <td>
                                @if($index === 0)
                                    <span class="badge bg-warning">🥇 #{{ $index + 1 }}</span>
                                @elseif($index === 1)
                                    <span class="badge bg-secondary">🥈 #{{ $index + 1 }}</span>
                                @elseif($index === 2)
                                    <span class="badge bg-danger">🥉 #{{ $index + 1 }}</span>
                                @else
                                    <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>{{ $item['siswa']->nis ?? '-' }}</td>
                            <td>{{ $item['siswa']->nama ?? '-' }}</td>
                            <td>{{ $item['siswa']->kelas->nama_kelas ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $item['total_pelanggaran'] }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('siswa.show', $item['siswa']->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Profil
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data pelanggaran</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart: Pelanggaran per Bulan
const ctxBulan = document.getElementById('chartPelanggaranPerBulan');
const chartDataBulan = @json($chartData);

// Prepare labels and data
const bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const labels = chartDataBulan.map(item => `${bulanNames[item.bulan - 1]} ${item.tahun}`);
const data = chartDataBulan.map(item => item.total);

new Chart(ctxBulan, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Jumlah Pelanggaran',
            data: data,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Chart: Pelanggaran per Kategori
const ctxKategori = document.getElementById('chartPelanggaranPerKategori');
const kategoriData = @json($pelanggaranPerKategori);

new Chart(ctxKategori, {
    type: 'doughnut',
    data: {
        labels: kategoriData.map(item => item.nama_kategori),
        datasets: [{
            data: kategoriData.map(item => item.total),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection
