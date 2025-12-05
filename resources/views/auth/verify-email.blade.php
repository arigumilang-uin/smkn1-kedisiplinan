@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-envelope mr-1"></i> Verifikasi Alamat Email</h3>
            </div>
            <div class="card-body">
                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">
                        Link verifikasi baru telah dikirim ke email Anda.
                    </div>
                @endif

                <p>
                    Untuk memastikan email Anda aktif dan tidak salah ketik,
                    kami telah mengirimkan link verifikasi ke alamat:
                    <strong>{{ auth()->user()->email }}</strong>.
                </p>
                <p class="mb-0">
                    Jika Anda belum menerima email tersebut, Anda dapat meminta
                    pengiriman ulang dengan menekan tombol di bawah.
                </p>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Ulang Email Verifikasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




