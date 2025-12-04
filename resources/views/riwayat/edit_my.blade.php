@extends('layouts.app')

@section('title', 'Edit Riwayat Pelanggaran')

@section('content')
<div class="container-fluid">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Edit Riwayat Pelanggaran</div>
        <div class="card-body">
          <form action="{{ route('my-riwayat.update', $r->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
              <label for="jenis_pelanggaran_id">Jenis Pelanggaran</label>
              <select name="jenis_pelanggaran_id" id="jenis_pelanggaran_id" class="form-control">
                @foreach($jenis as $j)
                  <option value="{{ $j->id }}" {{ $r->jenis_pelanggaran_id == $j->id ? 'selected' : '' }}>{{ $j->nama_pelanggaran }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="tanggal_kejadian">Tanggal</label>
                <input type="date" name="tanggal_kejadian" id="tanggal_kejadian" value="{{ optional($r->tanggal_kejadian)->format('Y-m-d') }}" class="form-control">
              </div>
              <div class="form-group col-md-6">
                <label for="jam_kejadian">Jam</label>
                <input type="time" name="jam_kejadian" id="jam_kejadian" value="{{ optional($r->tanggal_kejadian)->format('H:i') }}" class="form-control">
              </div>
            </div>

            <div class="form-group">
              <label for="keterangan">Keterangan</label>
              <textarea name="keterangan" id="keterangan" rows="4" class="form-control">{{ old('keterangan', $r->keterangan) }}</textarea>
            </div>

            <div class="form-group">
              <label for="bukti_foto">Bukti Foto (opsional, unggah untuk mengganti)</label>
              <input type="file" name="bukti_foto" id="bukti_foto" class="form-control-file">
              @if($r->bukti_foto_path)
                <p class="mt-2">Bukti saat ini: <a href="{{ route('bukti.show', $r->bukti_foto_path) }}" target="_blank">Lihat</a></p>
              @endif
            </div>

            <div class="form-group text-right">
              <a href="{{ route('my-riwayat.index') }}" class="btn btn-secondary">Batal</a>
              <button class="btn btn-primary">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
