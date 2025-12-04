@extends('layouts.app')

@section('title', 'Data Siswa')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/siswa/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/siswa/filters.css') }}">
@endsection

@section('content')

    @php
        $userRole = Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role;
        $isOperator = Auth::user()->hasRole('Operator Sekolah');
        $isWaliKelas = Auth::user()->hasRole('Wali Kelas');
        $isWaka = Auth::user()->hasRole('Waka Kesiswaan');
        $isKaprodi = Auth::user()->hasRole('Kaprodi');
    @endphp

    <div class="container-fluid">
        
        <!-- HEADER -->
        <div class="row mb-3 pt-2 align-items-center">
            <div class="col-sm-6">
                <h4 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    @if($isWaliKelas) Siswa Kelas Anda @else Data Induk Siswa @endif
                </h4>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group">
                    @if($isOperator || $isWaka)
                         <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary btn-sm border rounded mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Dashboard
                        </a>
                    @elseif($isWaliKelas)
                        <a href="{{ route('dashboard.walikelas') }}" class="btn btn-outline-secondary btn-sm border rounded mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Dashboard
                        </a>
                    @endif
                   
                    @if($isOperator)
                    <a href="{{ route('siswa.create') }}" class="btn btn-primary btn-sm shadow-sm mr-2">
                        <i class="fas fa-plus mr-1"></i> Tambah Siswa
                    </a>
                    <a href="{{ route('siswa.bulk.create') }}" class="btn btn-outline-primary btn-sm shadow-sm mr-2">
                        <i class="fas fa-copy mr-1"></i> Tambah Banyak
                    </a>
                    <a href="{{ route('audit.siswa') }}" class="btn btn-danger btn-sm shadow-sm">
                        <i class="fas fa-shield-alt mr-1"></i> Audit & Hapus
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('wali_created'))
            @php $c = session('wali_created'); @endphp
            <div class="alert alert-info">
                Akun Wali Murid otomatis telah dibuat: <strong>{{ $c['username'] }}</strong>
                <br>Password (sampel): <strong>{{ $c['password'] }}</strong>
                <br>Pastikan untuk menyampaikan kredensial ini kepada wali dan menyarankan perubahan password setelah login.
            </div>
        @endif
        @if(session('bulk_wali_created'))
            @php $list = session('bulk_wali_created'); @endphp
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✓ Sukses!</strong> {{ count($list) }} akun Wali Murid telah dibuat. File Excel kredensial otomatis diunduh ke device Anda (format: NISN | Username | Password).
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <!-- FILTER SECTION - EXTRACTED TO PARTIAL -->
        <div id="stickyFilter" class="card card-outline card-primary shadow-sm border-0">
            <div class="card-body bg-white py-3" style="border-radius:8px;">
                @include('components.siswa.filter-form')
            </div>
        </div>

        <!-- TABEL DATA -->
        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped text-nowrap table-valign-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10px">No</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            @if(!$isWaliKelas) <th>Kelas</th> @endif
                            <th>Kontak Wali Murid</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $key => $s)
                        <tr>
                            <td>{{ $siswa->firstItem() + $key }}</td>
                            <td><code>{{ $s->nisn }}</code></td>
                            <td>
                                <span class="font-weight-bold text-dark">{{ $s->nama_siswa }}</span>
                                @if($isWaliKelas && !$s->waliMurid)
                                    <i class="fas fa-exclamation-circle text-danger ml-1" title="Akun Wali Murid belum dihubungkan oleh Operator"></i>
                                @endif
                            </td>
                            @if(!$isWaliKelas)
                                <td><span class="badge badge-light border">{{ $s->kelas->nama_kelas }}</span></td>
                            @endif
                            <td>
                                @if($s->nomor_hp_wali_murid)
                                    <a href="https://wa.me/62{{ ltrim($s->nomor_hp_wali_murid, '0') }}" target="_blank" class="text-success font-weight-bold">
                                        <i class="fab fa-whatsapp"></i> {{ $s->nomor_hp_wali_murid }}
                                    </a>
                                @else
                                    <span class="text-muted text-sm font-italic">Kosong</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @if($isWaliKelas)
                                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-info btn-sm shadow-sm" title="Update Kontak">
                                            <i class="fas fa-edit mr-1"></i> Update Kontak
                                        </a>
                                    @elseif($isOperator)
                                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-warning" title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form onsubmit="return confirm('Yakin ingin menghapus siswa {{ $s->nama_siswa }}?');" action="{{ route('siswa.destroy', $s->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @elseif($isWaka)
                                        <a href="{{ route('riwayat.index', ['cari_siswa' => $s->nama_siswa]) }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                                            <i class="fas fa-history mr-1"></i> Lihat Riwayat
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isWaliKelas ? 5 : 6 }}" class="text-center py-5 text-muted">
                                <i class="fas fa-search-minus fa-3x mb-3 opacity-50"></i><br>
                                Data siswa tidak ditemukan dengan filter ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix bg-white">
                <div class="float-right">
                    {{ $siswa->links('pagination::bootstrap-4') }}
                </div>
                <div class="float-left pt-2 text-muted text-sm">
                    Total: {{ $siswa->total() }} Data
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/pages/siswa/filters.js') }}"></script>
    <script src="{{ asset('js/pages/siswa/index.js') }}"></script>
@endsection