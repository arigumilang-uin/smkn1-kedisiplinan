@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Data Kelas</h2>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Kelas</h5>
                    <h2>{{ $kelasList->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Siswa</h5>
                    <h2>{{ $kelasList->sum('total_siswa') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pelanggaran</h5>
                    <h2>{{ $kelasList->sum('total_pelanggaran') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Kelas List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Wali Kelas</th>
                            <th class="text-center">Total Siswa</th>
                            <th class="text-center">Total Pelanggaran</th>
                            <th class="text-center">Pelanggaran Bulan Ini</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelasList as $item)
                        <tr>
                            <td><strong>{{ $item['kelas']->nama_kelas }}</strong></td>
                            <td><span class="badge bg-secondary">{{ $item['kelas']->tingkat }}</span></td>
                            <td>{{ $item['kelas']->jurusan->nama_jurusan ?? '-' }}</td>
                            <td>{{ $item['kelas']->waliKelas->nama ?? '-' }}</td>
                            <td class="text-center">{{ $item['total_siswa'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $item['total_pelanggaran'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $item['pelanggaran_bulan_ini'] }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('data-kelas.show', $item['kelas']->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Tidak ada data kelas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
