@extends('layouts.app')

@section('title', 'Audit & Log Aktivitas')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-dark font-weight-bold">
                    <i class="fas fa-history mr-2"></i> Audit & Log Aktivitas
                </h3>
                <div>
                    <a href="{{ route('kepala-sekolah.activity.export-csv', request()->query()) }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-download mr-1"></i> Export CSV
                    </a>
                    <a href="{{ route('dashboard.kepsek') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        <i class="fas fa-filter mr-2"></i> Filter Log
                    </h6>
                </div>

                <form method="GET" class="form-inline p-3">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari deskripsi..." value="{{ request('search') }}" style="width: 200px;">

                    <select name="type" class="form-control form-control-sm mr-2" style="width: 150px;">
                        <option value="">-- Semua Jenis --</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>

                    <input type="date" name="dari_tanggal" class="form-control form-control-sm mr-2" value="{{ request('dari_tanggal') }}">
                    <input type="date" name="sampai_tanggal" class="form-control form-control-sm mr-2" value="{{ request('sampai_tanggal') }}">

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header bg-info">
                    <h6 class="card-title text-white font-weight-bold mb-0">
                        Total: {{ $logs->total() }} Log Aktivitas
                    </h6>
                </div>

                <div class="card-body table-responsive p-0">
                    @if($logs->isEmpty())
                        <div class="text-center p-5 text-muted">
                            <i class="fas fa-inbox fa-4x mb-3"></i>
                            <p>Tidak ada log aktivitas ditemukan.</p>
                        </div>
                    @else
                        <table class="table table-sm table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Jenis</th>
                                    <th>Dilakukan Oleh</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <small class="font-weight-bold">{{ $log->created_at->format('d M Y') }}</small><br>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ ucfirst($log->log_name) }}</span>
                                    </td>
                                    <td>
                                        <small class="font-weight-bold">{{ $log->causer->nama ?? 'System' }}</small><br>
                                        <small class="text-muted">{{ $log->causer->username ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $log->description }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('kepala-sekolah.activity.show', $log->id) }}" class="btn btn-primary btn-xs" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $logs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
