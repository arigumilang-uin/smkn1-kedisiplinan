@extends('layouts.app')

@section('title', 'Riwayat Saya')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title">Riwayat Pelanggaran Saya</h3>
          <div>
            <form class="form-inline" method="GET" action="{{ route('my-riwayat.index') }}">
              <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
              <input type="date" name="end_date" class="form-control form-control-sm ml-2" value="{{ request('end_date') }}">
              <button class="btn btn-sm btn-primary ml-2">Filter</button>
            </form>
          </div>
        </div>

        <div class="card-body table-responsive p-0">
          <table class="table table-hover text-nowrap">
            <thead>
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Siswa</th>
                <th>Jenis Pelanggaran</th>
                <th>Keterangan</th>
                <th>Bukti</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($riwayat as $r)
              <tr>
                <td>{{ $loop->iteration + ($riwayat->currentPage()-1)*$riwayat->perPage() }}</td>
                <td>{{ optional($r->tanggal_kejadian)->format('Y-m-d H:i') }}</td>
                <td>{{ $r->siswa?->nama }} <small class="text-muted">({{ $r->siswa?->kelas?->nama_kelas }})</small></td>
                <td>{{ $r->jenisPelanggaran?->nama_pelanggaran }}</td>
                <td>{{ Str::limit($r->keterangan, 80) }}</td>
                <td>
                  @if($r->bukti_foto_path)
                    <a href="{{ route('bukti.show', $r->bukti_foto_path) }}" target="_blank">Lihat</a>
                  @else
                    -
                  @endif
                </td>
                <td>
                  <a href="{{ route('my-riwayat.edit', $r->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                  <form action="{{ route('my-riwayat.destroy', $r->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin ingin menghapus catatan ini?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="card-footer clearfix">
          {{ $riwayat->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
