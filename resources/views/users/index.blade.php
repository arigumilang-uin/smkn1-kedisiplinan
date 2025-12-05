@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/users/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/users/filters.css') }}">
@endsection

@section('content')

    <!-- HEADER & TOMBOL TAMBAH -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="m-0 text-dark"><i class="fas fa-users mr-2"></i> Data Pengguna Sistem</h4>
            <div class="btn-group">
                <!-- Tombol Kembali ke Dashboard (Hanya untuk Operator/Admin) -->
                @if(auth()->user()->hasRole('Operator Sekolah'))
                     <a href="{{ route('dashboard.admin') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Dashboard
                    </a>
                @endif
               
                <a href="{{ route('users.create') }}" class="btn btn-success">
                    <i class="fas fa-user-plus mr-1"></i> Tambah User Baru
                </a>
            </div>
        </div>
    </div>

    <!-- CARD FILTER (PENCARIAN LANJUTAN) -->
    <div id="stickyFilter" class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter & Pencarian</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="background-color: #f4f6f9;">
            @include('components.users.filter-form')
        </div>
    </div>

    <!-- ALERT SUKSES/ERROR -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- CARD TABEL DATA -->
    <div class="card shadow-sm">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title text-muted">Total: <strong>{{ $users->total() }}</strong> Pengguna Terdaftar</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap table-striped">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Kontak</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $key => $u)
                    <tr>
                        <td>{{ $users->firstItem() + $key }}</td>
                        <td>
                            <strong>{{ $u->nama }}</strong>
                        </td>
                        <td><code>{{ $u->username }}</code></td>
                        <td>
                            @php $roleName = $u->role?->nama_role ?? 'N/A'; @endphp
                            @if($roleName == 'Operator Sekolah')
                                <span class="badge badge-primary">Operator</span>
                            @elseif($roleName == 'Waka Kesiswaan')
                                <span class="badge badge-info">Waka</span>
                            @elseif($roleName == 'Kepala Sekolah')
                                <span class="badge badge-danger">Kepsek</span>
                            @elseif($roleName == 'Wali Kelas')
                                <span class="badge badge-warning">Wali Kelas</span>
                            @elseif($roleName == 'Wali Murid')
                                <span class="badge badge-success" style="background-color: #28a745;">Wali Murid</span>
                            @elseif($roleName == 'Guru')
                                <span class="badge badge-secondary">Guru</span>
                            @else
                                <span class="badge badge-light border">{{ $roleName }}</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $u->email }}</td>
                        <td class="text-muted">
                            @php
                                // Untuk Wali Murid, ambil kontak dari data siswa (nomor_hp_wali_murid)
                                if ($u->isWaliMurid()) {
                                    $kontak = $u->anakWali
                                        ->whereNotNull('nomor_hp_wali_murid')
                                        ->pluck('nomor_hp_wali_murid')
                                        ->first();
                                } else {
                                    $kontak = $u->phone;
                                }
                            @endphp
                            {{ $kontak ?? '-' }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.edit', $u->id) }}" class="btn btn-warning" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Cegah menghapus diri sendiri -->
                                @if(Auth::id() != $u->id)
                                <form onsubmit="return confirm('Yakin ingin menghapus user {{ $u->nama }}?');" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-secondary" disabled title="Tidak bisa hapus diri sendiri"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fa-3x mb-3"></i><br>
                            Tidak ada data pengguna yang cocok dengan filter Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- FOOTER PAGINATION -->
        <div class="card-footer clearfix bg-white">
            <div class="float-right">
                <!-- Memaksa pagination menggunakan style Bootstrap 4 agar cocok dengan AdminLTE -->
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
            <div class="float-left pt-2 text-muted text-sm">
                Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} data.
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- Load Filter Module First -->
    <script src="{{ asset('js/pages/users/filters.js') }}"></script>
    <script src="{{ asset('js/pages/users/index.js') }}"></script>
@endpush