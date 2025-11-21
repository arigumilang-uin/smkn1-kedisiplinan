@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
<div class="container-fluid">
    
    <!-- 1. STATISTIK RINGKAS (SMALL BOX) -->
    <div class="row mb-2">
        <div class="col-12">
            <h5 class="text-dark font-weight-bold">
                <i class="fas fa-chart-pie text-primary mr-2"></i> Ringkasan Eksekutif
            </h5>
        </div>
    </div>

    <div class="row">
        <!-- Total Siswa -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSiswa }}</h3>
                    <p>Total Siswa Aktif</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
        
        <!-- Pelanggaran Bulan Ini -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pelanggaranBulanIni }}</h3>
                    <p>Pelanggaran Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <!-- Menunggu Persetujuan -->
        <div class="col-lg-4 col-12">
            <div class="small-box {{ $listPersetujuan->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                <div class="inner">
                    <h3>{{ $listPersetujuan->count() }}</h3>
                    <p>{{ $listPersetujuan->count() > 0 ? 'Menunggu Tanda Tangan' : 'Semua Berkas Aman' }}</p>
                </div>
                <div class="icon">
                    <i class="fas {{ $listPersetujuan->count() > 0 ? 'fa-file-signature' : 'fa-check-circle' }}"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. DAFTAR TUGAS (APPROVAL) -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-outline {{ $listPersetujuan->count() > 0 ? 'card-danger' : 'card-success' }} shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        @if($listPersetujuan->count() > 0)
                            <i class="fas fa-bell text-danger mr-1"></i> Perlu Tindakan Segera
                        @else
                            <i class="fas fa-check-double text-success mr-1"></i> Status Approval
                        @endif
                    </h3>
                    <div class="card-tools">
                        <span class="badge {{ $listPersetujuan->count() > 0 ? 'badge-danger' : 'badge-success' }}">
                            {{ $listPersetujuan->count() }} Pending
                        </span>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    @if($listPersetujuan->isEmpty())
                        <div class="text-center p-5">
                            <i class="fas fa-clipboard-check fa-4x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">Tidak ada dokumen yang memerlukan persetujuan saat ini.</h5>
                        </div>
                    @else
                        <table class="table table-hover table-striped projects">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 15%">Tanggal</th>
                                    <th style="width: 20%">Identitas Siswa</th>
                                    <th style="width: 25%">Pelanggaran Berat</th>
                                    <th style="width: 25%">Rekomendasi Sanksi</th>
                                    <th style="width: 15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listPersetujuan as $kasus)
                                <tr>
                                    <td>
                                        <div class="text-muted font-weight-bold">{{ $kasus->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $kasus->created_at->format('H:i') }} WIB</small>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 font-weight-bold text-primary">{{ $kasus->siswa->nama_siswa }}</h6>
                                        <small class="text-muted">{{ $kasus->siswa->kelas->nama_kelas }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger mb-1">Kasus Berat</span><br>
                                        {{ $kasus->pemicu }}
                                    </td>
                                    <td>
                                        <div class="p-2 bg-light border rounded text-danger font-weight-bold text-sm">
                                            {{ $kasus->sanksi_deskripsi }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('kasus.edit', $kasus->id) }}" class="btn btn-primary btn-sm btn-block shadow-sm">
                                            <i class="fas fa-pen-alt mr-1"></i> Tinjau
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

</div>
@endsection