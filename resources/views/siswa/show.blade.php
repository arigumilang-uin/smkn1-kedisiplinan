@extends('layouts.app')

@section('title', 'Profil Siswa - ' . $siswa->nama_siswa)

@section('content')
<div class="container-fluid">
    
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-user-graduate text-primary mr-2"></i> Profil Siswa
            </h4>
        </div>
    </div>

    <!-- Profil Card -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle" 
                             src="https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_siswa) }}&background=random&size=128" 
                             alt="Avatar">
                    </div>

                    <h3 class="profile-username text-center">{{ $siswa->nama_siswa }}</h3>

                    <p class="text-muted text-center">
                        <span class="badge badge-info">{{ $siswa->kelas->nama_kelas }}</span>
                        <span class="badge badge-secondary">{{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>NISN</b> <span class="float-right">{{ $siswa->nisn }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Total Poin Pelanggaran</b> 
                            <span class="float-right">
                                <span class="badge badge-{{ $totalPoin >= 301 ? 'danger' : ($totalPoin >= 100 ? 'warning' : 'success') }}">
                                    {{ $totalPoin }} Poin
                                </span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Total Pelanggaran</b> 
                            <span class="float-right badge badge-secondary">{{ $siswa->riwayatPelanggaran->count() }}</span>
                        </li>
                    </ul>

                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Info Akademik -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-school mr-1"></i> Informasi Akademik
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-door-open mr-1"></i> Kelas</strong>
                            <p class="text-muted">{{ $siswa->kelas->nama_kelas }}</p>

                            <strong><i class="fas fa-graduation-cap mr-1"></i> Jurusan</strong>
                            <p class="text-muted">{{ $siswa->kelas->jurusan->nama_jurusan }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-user-tie mr-1"></i> Wali Kelas</strong>
                            <p class="text-muted">{{ $siswa->kelas->waliKelas->username ?? '-' }}</p>

                            <strong><i class="fas fa-user-cog mr-1"></i> Kepala Program</strong>
                            <p class="text-muted">{{ $siswa->kelas->jurusan->kaprodi->username ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Wali Murid -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-users mr-1"></i> Informasi Wali Murid
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user mr-1"></i> Nama Wali Murid</strong>
                            <p class="text-muted">{{ $siswa->waliMurid->nama ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-phone mr-1"></i> Nomor HP</strong>
                            <p class="text-muted">{{ $siswa->nomor_hp_wali_murid ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pelanggaran -->
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-1"></i> Riwayat Pelanggaran
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger">{{ $siswa->riwayatPelanggaran->count() }} Pelanggaran</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($siswa->riwayatPelanggaran->count() > 0)
                        <div class="timeline">
                            @foreach($siswa->riwayatPelanggaran->sortByDesc('tanggal_kejadian') as $riwayat)
                            <div>
                                <i class="fas fa-exclamation-circle bg-danger"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('d M Y, H:i') }}
                                    </span>
                                    <h3 class="timeline-header">
                                        <strong>{{ $riwayat->jenisPelanggaran->nama_pelanggaran }}</strong>
                                        @php
                                            $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($riwayat);
                                        @endphp
                                        @if($poinInfo['matched'] && $poinInfo['poin'] > 0)
                                            <span class="badge badge-danger ml-2" title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($riwayat) }}">
                                                +{{ $poinInfo['poin'] }} Poin
                                            </span>
                                        @else
                                            <span class="badge badge-secondary ml-2" title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($riwayat) }}">
                                                +0 Poin
                                            </span>
                                        @endif
                                        @if($poinInfo['frequency'])
                                            <small class="text-muted">({{ $poinInfo['frequency'] }}×)</small>
                                        @endif
                                    </h3>
                                    <div class="timeline-body">
                                        <p class="mb-1">
                                            <strong>Kategori:</strong> 
                                            <span class="badge badge-secondary">{{ $riwayat->jenisPelanggaran->kategoriPelanggaran->nama_kategori }}</span>
                                        </p>
                                        @if($riwayat->keterangan)
                                        <p class="mb-1"><strong>Keterangan:</strong> {{ $riwayat->keterangan }}</p>
                                        @endif
                                        <p class="mb-0">
                                            <small class="text-muted">
                                                <i class="fas fa-user mr-1"></i> Dicatat oleh: {{ $riwayat->guruPencatat->nama ?? '-' }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div>
                                <i class="fas fa-check bg-success"></i>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-1"></i> Tidak ada riwayat pelanggaran. Siswa ini memiliki catatan bersih!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #dee2e6;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    position: relative;
    margin-right: 10px;
    margin-bottom: 15px;
}

.timeline > div > .timeline-item {
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #495057;
    margin-left: 60px;
    margin-right: 15px;
    padding: 10px;
    position: relative;
}

.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}

.timeline > div > .timeline-item > .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}

.timeline > div > .timeline-body {
    padding: 10px;
}

.timeline > div > i {
    position: absolute;
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    text-align: center;
    border-radius: 50%;
    color: #fff;
    background: #adb5bd;
    left: 18px;
    top: 0;
}
</style>
@endsection
