@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Data Jurusan</h2>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Jurusan</h5>
                    <h2>{{ $jurusanList->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Kelas</h5>
                    <h2>{{ $jurusanList->sum('total_kelas') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Siswa</h5>
                    <h2>{{ $jurusanList->sum('total_siswa') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pelanggaran</h5>
                    <h2>{{ $jurusanList->sum('total_pelanggaran') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Jurusan List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Jurusan</th>
                            <th>Kode</th>
                            <th>Kaprodi</th>
                            <th class="text-center">Total Kelas</th>
                            <th class="text-center">Total Siswa</th>
                            <th class="text-center">Total Pelanggaran</th>
                            <th class="text-center">Pelanggaran Bulan Ini</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurusanList as $item)
                        <tr>
                            <td>{{ $item['jurusan']->nama_jurusan }}</td>
                            <td><span class="badge bg-secondary">{{ $item['jurusan']->kode_jurusan }}</span></td>
                            <td>{{ $item['jurusan']->kaprodi->nama ?? '-' }}</td>
                            <td class="text-center">{{ $item['total_kelas'] }}</td>
                            <td class="text-center">{{ $item['total_siswa'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $item['total_pelanggaran'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $item['pelanggaran_bulan_ini'] }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('data-jurusan.show', $item['jurusan']->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Tidak ada data jurusan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
