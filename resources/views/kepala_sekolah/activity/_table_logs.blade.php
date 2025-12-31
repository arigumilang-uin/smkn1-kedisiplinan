
    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Aktivitas Terbaru</span>
            <span class="badge badge-primary">Total: {{ $logs->total() ?? 0 }} Record</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-44">Waktu & Tanggal</th>
                        <th class="w-32 text-center">Jenis</th>
                        <th class="w-64">Pelaku (User)</th>
                        <th>Keterangan Aktivitas</th>
                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs ?? [] as $log)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-700">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $log->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $log->log_name }}</span>
                            </td>
                            <td>
                                <div class="font-medium text-gray-700">{{ $log->causer->nama ?? 'System' }}</div>
                                <div class="text-[10px] text-gray-400 uppercase">{{ $log->causer->role->nama_role ?? '-' }}</div>
                            </td>
                            <td class="max-w-md">
                                <p class="text-xs text-gray-600 italic truncate">"{{ $log->description }}"</p>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('audit.activity.show', $log->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/>
                                    </svg>
                                    <h3 class="empty-state-title">Data Log Tidak Ditemukan</h3>
                                    <p class="empty-state-description">Belum ada log aktivitas yang tercatat di sistem.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($logs) && $logs->hasPages())
        <div class="card-body border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
             <p class="text-sm text-gray-500">Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }}</p>
             <div class="pagination">
                {{-- Previous --}}
                @if($logs->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                @endif
                
                {{-- Next --}}
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @else
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
