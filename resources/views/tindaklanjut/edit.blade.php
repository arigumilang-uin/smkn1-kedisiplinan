@extends('layouts.app')

@section('title', 'Kelola Kasus')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/tindaklanjut/edit.css') }}">
@endsection

@section('content')
<div class="kasus-container">
    <div class="kasus-header">
        <h1>Kelola Kasus: {{ $kasus->siswa->nama_siswa }}</h1>
        <small>Kelas: {{ $kasus->siswa->kelas->nama_kelas }} | NISN: {{ $kasus->siswa->nisn }}</small>
    </div>

    <div class="info-box">
        <div class="info-row">
            <div class="info-label">Pemicu Kasus:</div>
            <div class="info-val">{{ $kasus->pemicu }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sanksi Sistem:</div>
            <div class="info-val text-danger">{{ $kasus->sanksi_deskripsi }}</div>
        </div>
        @if($kasus->suratPanggilan)
        <div class="info-row">
            <div class="info-label">Rekomendasi:</div>
            <div class="info-val">
                Cetak <strong>{{ $kasus->suratPanggilan->tipe_surat }}</strong>
            </div>
        </div>
        @endif
    </div>

    <div style="margin-bottom: 25px;">
        @if($kasus->suratPanggilan)
            
            @if($kasus->status == 'Selesai')
                <div class="archive-box">
                    📂 <strong>Arsip Surat:</strong> <a href="{{ route('kasus.cetak', $kasus->id) }}" target="_blank">Download Copy Surat</a>
                </div>
            
            @elseif($kasus->status == 'Menunggu Persetujuan')
                <button class="btn btn-secondary" disabled>
                    ⏳ Menunggu Persetujuan Kepsek (Belum Bisa Cetak)
                </button>
            
            @else
                <a href="{{ route('kasus.cetak', $kasus->id) }}" target="_blank" class="btn btn-print">
                    🖨️ Cetak {{ $kasus->suratPanggilan->tipe_surat }}
                </a>
                <p class="text-muted">
                    *Mencetak surat akan otomatis mengubah status kasus menjadi <strong>"Sedang Ditangani"</strong>.
                </p>
            @endif

        @endif
    </div>

    <hr>

    <form action="{{ route('kasus.update', $kasus->id) }}" method="POST">
        @csrf
        @method('PUT')

        <h3>Hasil Penanganan / Tindak Lanjut</h3>

        @if($kasus->status == 'Selesai')
            
            <div class="alert alert-success">
                ✅ <strong>KASUS DITUTUP</strong><br>
                Kasus ini dinyatakan selesai pada tanggal: <strong>{{ \Carbon\Carbon::parse($kasus->tanggal_tindak_lanjut)->format('d F Y') }}</strong>.
                Data tidak dapat diubah lagi.
            </div>

            <label>Tanggal Penanganan:</label>
            <input type="text" value="{{ $kasus->tanggal_tindak_lanjut }}" disabled>

            <label>Denda / Catatan:</label>
            <textarea rows="3" disabled>{{ $kasus->denda_deskripsi }}</textarea>
            
            <label>Status:</label>
            <input type="text" value="Selesai" disabled style="font-weight: bold; color: green;">

            <br><br>
            <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>

        @else
            <label for="tanggal_tindak_lanjut">Tanggal Penanganan:</label>
            <input type="date" name="tanggal_tindak_lanjut" value="{{ $kasus->tanggal_tindak_lanjut ? \Carbon\Carbon::parse($kasus->tanggal_tindak_lanjut)->format('Y-m-d') : date('Y-m-d') }}" required>
    
            <label for="denda_deskripsi">Denda / Catatan Tambahan (Opsional):</label>
            <textarea name="denda_deskripsi" rows="3" placeholder="Contoh: Siswa diminta membawa 2 pot bunga.">{{ $kasus->denda_deskripsi }}</textarea>
    
            @if(Auth::user()->hasRole('Kepala Sekolah'))
                <div class="approval-area">
                    <p>Area Persetujuan Kepala Sekolah</p>
                    <label>
                        <input type="checkbox" name="status" value="Disetujui" required> 
                        Saya telah meninjau kasus ini dan menyetujui sanksi yang diberikan.
                    </label>
                </div>
            @else
                <label for="status">Status Kasus:</label>
                
                @if($kasus->status == 'Menunggu Persetujuan')
                    <div class="alert alert-warning">
                        🔒 <strong>Terkunci:</strong> Menunggu persetujuan Kepala Sekolah.
                    </div>
                    <input type="hidden" name="status" value="Menunggu Persetujuan">
                @else
                    <select name="status" required>
                        @if($kasus->status == 'Baru')
                            <option value="Baru" selected>Baru (Belum Selesai)</option>
                        @endif
                        
                        @if($kasus->status == 'Disetujui' || $kasus->status == 'Ditangani')
                            <option value="Disetujui" disabled>✅ Sudah Disetujui (Terkunci)</option>
                            @if($kasus->status == 'Disetujui') <option value="Disetujui" hidden selected>Disetujui</option> @endif
                        @endif
                        
                        <option value="Ditangani" {{ $kasus->status == 'Ditangani' ? 'selected' : '' }}>Sedang Ditangani</option>
                        <option value="Selesai">Selesai (Kasus Ditutup)</option>
                    </select>
                @endif
            @endif
    
            <br><br>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>

        @endif
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/tindaklanjut/edit.js') }}"></script>
@endpush
