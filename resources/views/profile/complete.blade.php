@extends('layouts.app')

@section('title', 'Lengkapi Profil Akun')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Lengkapi Profil Akun</h3>
            </div>
            <form method="POST" action="{{ route('profile.complete.store') }}">
                @csrf
                <div class="card-body">
                    <p class="text-muted">
                        Untuk keamanan dan kemudahan reset password / notifikasi ke depan,
                        silakan lengkapi email dan kontak di bawah ini. Data ini bisa dilihat
                        oleh pihak sekolah yang berwenang.
                    </p>

                    <div class="form-group">
                        <label for="email">Email Aktif <span class="text-danger">*</span></label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}"
                            required
                        >
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Gunakan email yang benar-benar aktif. Email ini akan digunakan
                            untuk reset password dan notifikasi penting.
                        </small>
                    </div>

                    @if (! $isWaliMurid)
                        <div class="form-group">
                            <label for="phone">Nomor HP / WA (Opsional)</label>
                            <input
                                type="text"
                                name="phone"
                                id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="Contoh: 0812xxxxxxx"
                            >
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">
                                Opsional, namun disarankan. Nomor ini disimpan hanya sebagai
                                kontak yang dapat dilihat pihak sekolah (tidak digunakan untuk
                                broadcast WA otomatis saat ini).
                            </small>
                        </div>
                    @else
                        <div class="form-group">
                            <label>Nomor HP / WA Wali Murid (Dari Data Siswa)</label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $waliMuridContact ?? 'Belum diisi pada data siswa' }}"
                                disabled
                            >
                            <small class="form-text text-muted">
                                Kontak utama wali murid diambil dari data siswa
                                (kolom <code>nomor_hp_wali_murid</code>). Jika ada perubahan,
                                silakan hubungi wali kelas atau operator sekolah.
                            </small>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        Simpan &amp; Lanjut
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection




