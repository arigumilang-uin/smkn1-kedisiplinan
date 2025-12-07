<!-- Tab: Log Aktivitas Sistem -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <input type="hidden" name="tab" value="activity">
                    
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

                    <input type="date" name="dari_tanggal" class="form-control form-control-sm mr-2" 
                           value="{{ request('dari_tanggal') }}" placeholder="Dari Tanggal">
                    <input type="date" name="sampai_tanggal" class="form-control form-control-sm mr-2" 
                           value="{{ request('sampai_tanggal') }}" placeholder="Sampai Tanggal">

                    <button type="submit" class="btn btn-primary btn-sm mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>

                    @if(request()->hasAny(['search', 'type', 'dari_tanggal', 'sampai_tanggal']))
                    <a href="{{ route('audit.activity.index', ['tab' => 'activity']) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Total: {{ $logs->total() }} log</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th width="15%">Tanggal</th>
                                <th width="10%">Jenis</th>
                                <th width="15%">Dilakukan Oleh</th>
                                <th width="40%">Deskripsi</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>
                                    <small>{{ $log->created_at->format('d M Y') }}</small>
                                    <br><small class="text-muted">{{ $log->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $log->log_name }}</span>
                                </td>
                                <td>
                                    {{ $log->causer->nama ?? 'System' }}
                                    <br><small class="text-muted">{{ $log->causer->role->nama_role ?? '-' }}</small>
                                </td>
                                <td>{{ $log->description }}</td>
                                <td class="text-center">
                                    <a href="{{ route('audit.activity.show', $log->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Tidak ada log aktivitas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $logs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
