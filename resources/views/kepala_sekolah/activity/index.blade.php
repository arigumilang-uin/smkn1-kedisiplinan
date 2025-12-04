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
                    <a href="{{ route('audit.activity.export-csv', request()->query()) }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-download mr-1"></i> Export CSV
                    </a>
                    <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary btn-sm">
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
                        @if(isset($summary)) Ringkasan Aktivitas & Status @else Total: {{ $logs->total() }} Log Aktivitas @endif
                    </h6>
                </div>

                <div class="card-body table-responsive p-0">
                    @if(isset($summary))
                        <div class="p-3">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0">{{ $summary['pendingApprovals'] }}</h5>
                                            <small class="text-muted">Menunggu Persetujuan</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0">{{ $summary['openCases'] }}</h5>
                                            <small class="text-muted">Kasus Terbuka</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0">{{ $summary['statusCounts']->sum() }}</h5>
                                            <small class="text-muted">Total Status Tindak Lanjut</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body text-center">
                                            <h5 class="mb-0">{{ $summary['topViolations']->sum('total') }}</h5>
                                            <small class="text-muted">Pelanggaran (90 hari)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header">Top Pelanggaran (90 hari)</div>
                                        <div class="card-body p-2">
                                            @if($summary['topViolations']->isEmpty())
                                                <p class="text-muted p-2">Tidak ada data pelanggaran dalam 90 hari terakhir.</p>
                                            @else
                                                <ul class="list-group list-group-flush">
                                                    @foreach($summary['topViolations'] as $v)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $v->jenis->nama ?? 'Tidak diketahui' }}</strong>
                                                            <div class="text-muted small">ID: {{ $v->jenis_pelanggaran_id }}</div>
                                                        </div>
                                                        <span class="badge badge-primary badge-pill">{{ $v->total }}</span>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header">Persetujuan Terbaru</div>
                                        <div class="card-body p-2">
                                            @if($summary['recentApprovals']->isEmpty())
                                                <p class="text-muted p-2">Belum ada persetujuan yang ditandatangani.</p>
                                            @else
                                                <ul class="list-group list-group-flush">
                                                    @foreach($summary['recentApprovals'] as $a)
                                                    <li class="list-group-item">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong>{{ $a->siswa->nama ?? 'Siswa' }}</strong>
                                                                <div class="text-muted small">Tanggal: {{ optional($a->tanggal_disetujui)->format('d M Y') }}</div>
                                                            </div>
                                                            <div class="text-right">
                                                                <small class="text-muted">Status: {{ $a->status }}</small>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
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
                                            <a href="{{ route('audit.activity.show', $log->id) }}" class="btn btn-primary btn-xs" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endif
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    @if(!isset($summary))
                        {{ $logs->links('pagination::bootstrap-4') }}
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
