@extends('layouts.app')

@section('title', 'Pratinjau Laporan')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-dark font-weight-bold">
                    <i class="fas fa-file-pdf mr-2"></i> Pratinjau Laporan
                </h3>
                <div>
                    <a href="{{ route('kepala-sekolah.reports.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali Edit
                    </a>
                    <a href="{{ route('kepala-sekolah.reports.export-csv') }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-file-csv mr-1"></i> Export CSV
                    </a>
                    <a href="{{ route('kepala-sekolah.reports.export-pdf') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Total Data:</strong> {{ count($data) }} record
                @if(!empty($filters['jurusan_id']))
                    | <strong>Jurusan:</strong> {{ $filters['jurusan_id'] }}
                @endif
                @if(!empty($filters['kelas_id']))
                    | <strong>Kelas:</strong> {{ $filters['kelas_id'] }}
                @endif
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header bg-primary">
                    <h3 class="card-title text-white font-weight-bold">
                        Data Laporan {{ $reportType == 'pelanggaran' ? 'Pelanggaran' : ($reportType == 'siswa' ? 'Siswa' : 'Tindakan') }}
                    </h3>
                </div>

                <div class="card-body table-responsive p-0">
                    @if(empty($data))
                        <div class="text-center p-5 text-muted">
                            <i class="fas fa-inbox fa-4x mb-3"></i>
                            <p>Tidak ada data sesuai filter yang dipilih.</p>
                        </div>
                    @else
                        <table class="table table-sm table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>NISN</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th>Tanggal</th>
                                    <th>Dilaporkan Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $idx => $row)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $row->siswa->nisn ?? '-' }}</td>
                                    <td><strong>{{ $row->siswa->nama_siswa ?? '-' }}</strong></td>
                                    <td><small class="badge badge-info">{{ $row->siswa->kelas->nama_kelas ?? '-' }}</small></td>
                                    <td><small class="badge badge-secondary">{{ $row->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</small></td>
                                    <td>{{ $row->jenisPelanggaran->nama ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->tanggal_kejadian)->format('d M Y H:i') }}</td>
                                    <td><small class="text-muted">{{ $row->user->nama ?? '-' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
