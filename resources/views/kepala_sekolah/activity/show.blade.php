@extends('layouts.app')

@section('title', 'Detail Log Aktivitas')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-dark font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i> Detail Log Aktivitas
                </h3>
                <a href="{{ route('kepala-sekolah.activity.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        <i class="fas fa-clipboard-list mr-2"></i> Informasi Log
                    </h6>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Tanggal & Waktu</label>
                        <p>{{ $log->created_at->format('d M Y H:i:s') }}</p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Jenis Aktivitas</label>
                        <p><span class="badge badge-secondary p-2">{{ ucfirst($log->log_name) }}</span></p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Deskripsi</label>
                        <p class="text-lg font-weight-bold text-primary">{{ $log->description }}</p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Dilakukan Oleh</label>
                        <p>
                            <strong>{{ $log->causer->nama ?? 'System' }}</strong> 
                            <small class="text-muted">({{ $log->causer->username ?? '-' }})</small>
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Subject Type</label>
                        <p><code>{{ $log->subject_type ?? 'N/A' }}</code></p>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Subject ID</label>
                        <p><code>{{ $log->subject_id ?? 'N/A' }}</code></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-info">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        <i class="fas fa-database mr-2"></i> Data Perubahan (Properties)
                    </h6>
                </div>

                <div class="card-body">
                    @if(empty($log->properties))
                        <p class="text-muted text-center">Tidak ada data perubahan</p>
                    @else
                        <pre class="text-sm" style="height: 400px; overflow-y: auto;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
