@extends('layouts.app')

@section('title', 'Developer Dashboard')

@section('styles')
    <style>
        .dev-hero { background: linear-gradient(90deg, #0f172a, #0b1220); color: #fff; padding: 48px 24px; border-radius: 12px; }
        .dev-hero h1 { font-weight: 800; letter-spacing: 1px; font-size: 2.2rem; }
        .dev-hero p { opacity: 0.85; margin-top: 6px; }
        .dev-card { border-radius: 10px; box-shadow: 0 8px 30px rgba(2,6,23,0.45); overflow: hidden; }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-md-10">
            <div class="dev-card">
                <div class="dev-hero text-center">
                    <h1>gasss developer ganteng</h1>
                    <p class="lead">Selamat datang, Anda masuk sebagai <strong>Developer</strong>. Gunakan menu <em>Impersonate Role</em> untuk berpindah peran dan menguji fitur.</p>
                </div>
                <div class="bg-white p-4 text-center">
                    <p class="mb-0 text-muted">Akses cepat: <a href="{{ route('developer.status') }}">Status Impersonation</a> · <a href="{{ route('developer.impersonate.clear') }}">Clear Impersonation</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
