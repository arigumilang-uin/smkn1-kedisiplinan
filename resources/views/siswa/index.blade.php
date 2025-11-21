@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')

    @php
        $userRole = Auth::user()->role->nama_role;
        $isOperator = ($userRole == 'Operator Sekolah');
        $isWaliKelas = ($userRole == 'Wali Kelas');
        $isWaka = ($userRole == 'Waka Kesiswaan'); // Definisi variabel Waka
    @endphp

    <div class="container-fluid">
        <!-- HEADER -->
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="m-0 text-dark font-weight-bold">
                        <i class="fas fa-user-graduate text-primary mr-2"></i>
                        @if($isWaliKelas) Siswa Kelas Anda @else Data Induk Siswa @endif
                    </h4>
                    <p class="text-muted small mb-0">
                        @if($isWaka)
                            Klik tombol aksi untuk melihat riwayat pelanggaran siswa.
                        @else
                            Manajemen data siswa aktif.
                        @endif
                    </p>
                </div>
                <div class="btn-group">
                    @if($isOperator)
                        <a href="{{ route('siswa.create') }}" class="btn btn-primary shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Siswa
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- FILTER CARD (CONSISTENT STYLE) -->
        <div class="card card-outline {{ $isWaliKelas ? 'card-info' : 'card-primary' }} collapsed-card shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Pencarian</h3>
                <div class="card-tools">
                    @if(!$isWaliKelas)
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    @endif
                </div>
            </div>
            <div class="card-body bg-light">
                <form action="{{ route('siswa.index') }}" method="GET">
                    <div class="row">
                        @if(!$isWaliKelas)
                        <div class="col-md-3 mb-2">
                            <select name="tingkat" class="form-control form-control-sm">
                                <option value="">- Semua Tingkat -</option>
                                <option value="X" {{ request('tingkat') == 'X' ? 'selected' : '' }}>Kelas X</option>
                                <option value="XI" {{ request('tingkat') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                <option value="XII" {{ request('tingkat') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select name="jurusan_id" class="form-control form-control-sm">
                                <option value="">- Semua Jurusan -</option>
                                @foreach($allJurusan as $j)
                                    <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select name="kelas_id" class="form-control form-control-sm">
                                <option value="">- Semua Kelas -</option>
                                @foreach($allKelas as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="{{ $isWaliKelas ? 'col-md-10' : 'col-md-3' }} mb-2">
                            <div class="input-group input-group-sm">
                                <input type="text" name="cari" class="form-control" placeholder="Cari Nama / NISN..." value="{{ request('cari') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn {{ $isWaliKelas ? 'btn-info' : 'btn-primary' }}"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        
                        @if($isWaliKelas)
                        <div class="col-md-2 mb-2 text-right">
                             <a href="{{ route('siswa.index') }}" class="btn btn-default btn-sm btn-block"><i class="fas fa-undo"></i> Reset</a>
                        </div>
                        @endif
                    </div>
                    
                    @if(!$isWaliKelas)
                    <div class="text-right mt-2">
                        <a href="{{ route('siswa.index') }}" class="btn btn-default btn-sm mr-1"><i class="fas fa-undo"></i> Reset</a>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Terapkan Filter</button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- TABEL DATA -->
        <div class="card shadow-sm">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped text-nowrap table-valign-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10px">No</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            @if(!$isWaliKelas) <th>Kelas</th> @endif
                            <th>Kontak Ortu</th>
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
                                @if($isWaliKelas && !$s->orangTua)
                                    <i class="fas fa-exclamation-circle text-danger ml-1" title="Akun Orang Tua belum dihubungkan oleh Operator"></i>
                                @endif
                            </td>
                            @if(!$isWaliKelas)
                                <td><span class="badge badge-light border">{{ $s->kelas->nama_kelas }}</span></td>
                            @endif
                            <td>
                                @if($s->nomor_hp_ortu)
                                    <a href="https://wa.me/62{{ ltrim($s->nomor_hp_ortu, '0') }}" target="_blank" class="text-success font-weight-bold">
                                        <i class="fab fa-whatsapp"></i> {{ $s->nomor_hp_ortu }}
                                    </a>
                                @else
                                    <span class="text-muted text-sm font-italic">Kosong</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    
                                    {{-- LOGIKA TOMBOL AKSI PER ROLE --}}

                                    @if($isWaliKelas)
                                        <!-- WALI KELAS: Update Kontak -->
                                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-info btn-sm shadow-sm" title="Update Kontak">
                                            <i class="fas fa-edit mr-1"></i> Update Kontak
                                        </a>
                                    
                                    @elseif($isOperator)
                                        <!-- OPERATOR: Edit & Hapus -->
                                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-warning" title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form onsubmit="return confirm('Yakin ingin menghapus?');" action="{{ route('siswa.destroy', $s->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </form>

                                    @elseif($isWaka)
                                        <!-- WAKA: Lihat Riwayat (Shortcut Cerdas) -->
                                        <!-- Mengarah ke halaman Riwayat dengan filter nama siswa -->
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
                                <i class="fas fa-search-minus fa-3x mb-3 opacity-50"></i><br>Data siswa tidak ditemukan.
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