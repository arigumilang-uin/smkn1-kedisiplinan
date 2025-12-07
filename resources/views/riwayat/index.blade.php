@extends('layouts.app')

@section('title', 'Riwayat Pelanggaran')

@section('styles')
    <!-- Panggil CSS Eksternal -->
    <link rel="stylesheet" href="{{ asset('css/pages/riwayat/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/riwayat/filters.css') }}">
@endsection

@section('content')
<div class="container-fluid">

    <!-- HEADER HALAMAN -->
    <div class="row mb-3 pt-2 align-items-center">
        <div class="col-sm-6">
            <h4 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-history text-primary mr-2"></i> Log Riwayat
            </h4>
        </div>
        <div class="col-sm-6 text-right">
             @php
                $role = auth()->user()->effectiveRoleName() ?? auth()->user()->role?->nama_role;
                $backRoute = match($role) {
                    'Wali Kelas' => route('dashboard.walikelas'),
                    'Kaprodi' => route('dashboard.kaprodi'),
                    'Kepala Sekolah' => route('dashboard.kepsek'),
                    default => route('dashboard.admin'),
                };
            @endphp
            <div class="btn-group">
                <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm border rounded mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Dashboard
                </a>
                <span class="btn btn-light btn-sm border rounded disabled text-dark font-weight-bold">
                    Total: {{ $riwayat->total() }} Data
                </span>
            </div>
        </div>
    </div>

    <!-- FILTER SECTION (STICKY) -->
    <!-- ID stickyFilter digunakan oleh JS untuk efek melayang -->
    <div id="stickyFilter" class="card card-outline card-primary shadow-sm border-0">
        
        <div class="card-body bg-white py-3" style="border-radius: 8px;">
            @include('components.riwayat.filter-form')
        </div>
    </div>

    <!-- TABEL DATA (SCROLLABLE) -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table table-hover table-premium w-100">
                    <thead>
                        <tr>
                            <th style="padding-left: 25px;">Waktu</th>
                            <th>Identitas Siswa</th>
                            <th>Detail Pelanggaran</th>
                            <th class="text-center">Poin</th>
                            <th>Dicatat Oleh</th>
                            <th class="text-center">Bukti</th>
                            @if(auth()->user()->hasRole('Operator Sekolah'))
                            <th class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                        <tr>
                            <!-- 1. WAKTU -->
                            <td class="pl-4 align-top pt-3">
                                <div class="font-weight-bold text-dark">
                                    <a href="{{ route('riwayat.index', array_merge(request()->all(), ['start_date' => $r->tanggal_kejadian->format('Y-m-d'), 'end_date' => $r->tanggal_kejadian->format('Y-m-d')])) }}" 
                                       class="smart-link" title="Filter tanggal ini">
                                        {{ $r->tanggal_kejadian->format('d M Y') }}
                                    </a>
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="far fa-clock mr-1"></i> {{ $r->tanggal_kejadian->format('H:i') }} WIB
                                </div>
                            </td>

                            <!-- 2. SISWA -->
                            <td>
                                <div class="student-profile">
                                    @php $initial = strtoupper(substr($r->siswa->nama_siswa, 0, 1)); @endphp
                                    <div class="avatar-circle">{{ $initial }}</div>
                                    
                                    <div>
                                        <a href="{{ route('siswa.show', $r->siswa->id) }}" class="text-primary font-weight-bold smart-link" title="Lihat profil siswa">
                                            {{ $r->siswa->nama_siswa }}
                                        </a>
                                        
                                        <div class="student-meta">
                                            <a href="{{ route('riwayat.index', ['kelas_id' => $r->siswa->kelas_id]) }}" class="badge-class text-decoration-none" title="Filter kelas">
                                                {{ $r->siswa->kelas->nama_kelas }}
                                            </a>
                                            
                                            @php
                                                $totalPoinSiswa = $r->siswa->riwayatPelanggaran->sum(fn($rp) => $rp->jenisPelanggaran->poin);
                                                $bgTotal = $totalPoinSiswa >= 100 ? 'bg-danger' : ($totalPoinSiswa >= 50 ? 'bg-warning' : 'bg-secondary');
                                            @endphp
                                            <span class="badge-poin-total {{ $bgTotal }}" title="Total Akumulasi Poin Siswa Ini">
                                                Total: {{ $totalPoinSiswa }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. PELANGGARAN -->
                            <td style="white-space: normal; min-width: 280px;">
                                <div class="font-weight-bold text-dark">{{ $r->jenisPelanggaran->nama_pelanggaran }}</div>
                                <div class="small text-muted text-uppercase mt-1" style="font-weight: 600; font-size: 0.7rem;">
                                    {{ $r->jenisPelanggaran->kategoriPelanggaran->nama_kategori }}
                                </div>
                                @if($r->keterangan)
                                    <div class="text-muted font-italic small mt-1 pl-2 border-left" style="border-color: #dee2e6;">
                                        "{{ Str::limit($r->keterangan, 50) }}"
                                    </div>
                                @endif
                            </td>

                            <!-- 4. POIN -->
                            <td class="text-center">
                                <span class="badge badge-danger px-3 py-1 shadow-sm" style="font-size: 0.85rem; border-radius: 15px;">
                                    +{{ $r->jenisPelanggaran->poin }}
                                </span>
                            </td>

                            <!-- 5. PELAPOR -->
                            <td>
                                @if($r->guruPencatat)
                                    <a href="{{ route('riwayat.index', ['pencatat_id' => $r->guru_pencatat_user_id]) }}" class="d-flex align-items-center text-dark text-decoration-none group-hover" title="Lihat riwayat pelapor ini">
                                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-2 border" style="width:30px; height:30px;">
                                            <i class="fas fa-user-tie text-secondary small"></i>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-sm">{{ $r->guruPencatat->username }}</div>
                                            <div class="text-xs text-muted">Pelapor</div>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-muted small"><i class="fas fa-robot mr-1"></i> Sistem</span>
                                @endif
                            </td>

                            <!-- 6. BUKTI -->
                            <td class="text-center">
                                    @if($r->bukti_foto_path)
                                        <a href="{{ route('bukti.show', ['path' => $r->bukti_foto_path]) }}" target="_blank" class="btn btn-light btn-sm border rounded-circle shadow-sm" style="width: 35px; height: 35px; padding: 0; line-height: 33px;" title="Lihat Foto">
                                            <i class="fas fa-image text-muted"></i>
                                        </a>
                                    @else
                                        <span class="text-muted text-xs">-</span>
                                    @endif
                            </td>

                            <!-- 7. AKSI (Operator Only) -->
                            @if(auth()->user()->hasRole('Operator Sekolah'))
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('my-riwayat.edit', ['riwayat' => $r->id, 'return_url' => url()->full()]) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('my-riwayat.destroy', $r->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus riwayat ini? Poin siswa akan direcalculate.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="return_url" value="{{ url()->full() }}">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('Operator Sekolah') ? '7' : '6' }}" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-search fa-3x text-gray-200 mb-3"></i>
                                    <h6 class="text-muted font-weight-normal">Data tidak ditemukan.</h6>
                                    <p class="text-muted small">Coba sesuaikan filter pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan <strong>{{ $riwayat->firstItem() ?? 0 }} - {{ $riwayat->lastItem() ?? 0 }}</strong> dari <strong>{{ $riwayat->total() }}</strong> data
                </small>
                <div>
                    {{ $riwayat->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Load Filter Module First -->
    <script src="{{ asset('js/pages/riwayat/filters.js') }}"></script>
    <!-- Load Logic Eksternal -->
    <script src="{{ asset('js/pages/riwayat/index.js') }}"></script>
@endpush