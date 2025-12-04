@extends('layouts.app')

@section('title', 'Monitoring Siswa')

@section('content')

<div class="row">
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                         src="https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_siswa) }}&background=random"
                         alt="User profile picture">
                </div>
                <h3 class="profile-username text-center">{{ $siswa->nama_siswa }}</h3>
                <p class="text-muted text-center">{{ $siswa->kelas->nama_kelas }} | {{ $siswa->nisn }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Total Poin</b> <a class="float-right badge badge-danger" style="font-size: 1rem;">{{ $totalPoin }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Total Pelanggaran</b> <a class="float-right">{{ $riwayat->count() }} Kali</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        
        @if($kasus->isNotEmpty())
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="icon fas fa-exclamation-triangle"></i> Catatan Kasus / Sanksi</h3>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <tbody>
                        @foreach($kasus as $k)
                        <tr>
                            <td>{{ $k->created_at->format('d M Y') }}</td>
                            <td>{{ $k->sanksi_deskripsi }}</td>
                            <td><span class="badge badge-info">{{ $k->status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pelanggaran</h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    @forelse($riwayat as $r)
                        <div class="time-label">
                            <span class="bg-secondary">{{ $r->tanggal_kejadian->format('d M Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-exclamation bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $r->tanggal_kejadian->format('H:i') }}</span>
                                <h3 class="timeline-header"><a href="#">{{ $r->jenisPelanggaran->nama_pelanggaran }}</a></h3>
                                <div class="timeline-body">
                                    Poin: <span class="text-danger text-bold">+{{ $r->jenisPelanggaran->poin }}</span><br>
                                    Catatan: {{ $r->keterangan ?? '-' }}
                                </div>
                                @if($r->bukti_foto_path)
                                <div class="timeline-footer">
                                    <a href="{{ route('bukti.show', ['path' => $r->bukti_foto_path]) }}" target="_blank" class="btn btn-sm btn-primary">Lihat Foto Bukti</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-center text-success">Tidak ada data pelanggaran.</div>
                    @endforelse
                    
                    @if($riwayat->isNotEmpty())
                    <div>
                        <i class="far fa-clock bg-gray"></i>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
