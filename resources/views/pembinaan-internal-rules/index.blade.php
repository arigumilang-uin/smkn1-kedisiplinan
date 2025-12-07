@extends('layouts.app')

@section('title', 'Kelola Pembinaan Internal')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4><i class="fas fa-user-check mr-2"></i> Kelola Aturan Pembinaan Internal</h4>
            <p class="text-muted">
                Atur threshold pembinaan internal berdasarkan <strong>akumulasi poin</strong> siswa.
                <br><strong>Catatan Penting:</strong> Pembinaan internal adalah <strong>rekomendasi konseling</strong>, TIDAK trigger surat pemanggilan otomatis.
                <br><strong>Surat pemanggilan</strong> hanya trigger dari pelanggaran dengan sanksi "Panggilan orang tua" (diatur di Frequency Rules).
            </p>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Button Tambah Rule -->
    <div class="row mb-3">
        <div class="col-12 text-right">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalTambahRule">
                <i class="fas fa-plus-circle"></i> Tambah Aturan Baru
            </button>
        </div>
    </div>

    <!-- Table Rules -->
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">Range Poin</th>
                        <th width="30%">Pembina yang Terlibat</th>
                        <th width="35%">Keterangan</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr>
                        <td>{{ $rule->display_order }}</td>
                        <td>
                            <span class="badge badge-primary badge-lg">
                                {{ $rule->getRangeText() }}
                            </span>
                        </td>
                        <td>
                            @foreach($rule->pembina_roles as $role)
                                <span class="badge badge-info">{{ $role }}</span>
                            @endforeach
                        </td>
                        <td>{{ $rule->keterangan }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning" 
                                    data-toggle="modal" 
                                    data-target="#modalEditRule{{ $rule->id }}"
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('pembinaan-internal-rules.destroy', $rule->id) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus aturan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit Rule -->
                    <div class="modal fade" id="modalEditRule{{ $rule->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <form action="{{ route('pembinaan-internal-rules.update', $rule->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Aturan Pembinaan Internal</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        @include('pembinaan-internal-rules.partials.form', ['rule' => $rule])
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> Belum ada aturan pembinaan internal. Klik "Tambah Aturan Baru" untuk memulai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Box -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle"></i> Cara Kerja Pembinaan Internal
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Sistem menghitung <strong>total poin akumulasi</strong> dari semua pelanggaran siswa</li>
                        <li>Berdasarkan total poin, sistem memberikan <strong>rekomendasi pembina</strong> yang perlu terlibat</li>
                        <li>Pembinaan dilakukan secara <strong>internal</strong> (konseling, monitoring, evaluasi)</li>
                        <li><strong>TIDAK</strong> trigger surat pemanggilan otomatis ke orang tua</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-exclamation-triangle"></i> Perbedaan dengan Frequency Rules
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="50%"><strong>Frequency Rules:</strong></td>
                            <td width="50%"><strong>Pembinaan Internal:</strong></td>
                        </tr>
                        <tr>
                            <td>Berdasarkan <em>frekuensi</em> pelanggaran</td>
                            <td>Berdasarkan <em>akumulasi poin</em></td>
                        </tr>
                        <tr>
                            <td>Trigger surat pemanggilan</td>
                            <td>Rekomendasi konseling</td>
                        </tr>
                        <tr>
                            <td>Melibatkan orang tua</td>
                            <td>Internal sekolah</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Rule -->
<div class="modal fade" id="modalTambahRule" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('pembinaan-internal-rules.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Aturan Pembinaan Internal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('pembinaan-internal-rules.partials.form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alert after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
@endsection
