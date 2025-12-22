@extends('layouts.app')

@section('title', 'Data Siswa Terhapus')

@section('content')
<div class="container-fluid py-4" id="deletedSiswaApp">
    <div class="row">
        <div class="col-12">
            
            {{-- Header --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">
                                <i class="fas fa-trash-restore text-warning"></i>
                                Data Siswa Terhapus
                            </h3>
                            <p class="text-muted mb-0">Daftar siswa yang sudah dihapus. Anda dapat restore atau permanent delete.</p>
                        </div>
                        <a href="{{ route('siswa.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            {{-- Filters --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('siswa.deleted') }}" class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="small font-weight-bold">Cari Siswa</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                                   class="form-control" placeholder="Nama atau NISN...">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="small font-weight-bold">Alasan Keluar</label>
                            <select name="alasan_keluar" class="form-control">
                                <option value="">Semua Alasan</option>
                                @foreach($alasanOptions as $option)
                                    <option value="{{ $option }}" {{ ($filters['alasan_keluar'] ?? '') == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="small font-weight-bold">Kelas</label>
                            <select name="kelas_id" class="form-control">
                                <option value="">Semua Kelas</option>
                                @foreach($allKelas as $k)
                                    <option value="{{ $k->id }}" {{ ($filters['kelas_id'] ?? '') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('siswa.deleted') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="card border-0 shadow-sm">
                @if($deletedSiswa->count() > 0)
                    {{-- Bulk Action Bar --}}
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox d-inline-block mr-3">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="selectAll"
                                           onchange="toggleSelectAll(this)">
                                    <label class="custom-control-label" for="selectAll">Pilih Semua</label>
                                </div>
                                <span class="badge badge-primary" id="selectedCount">0 dipilih</span>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" 
                                        onclick="bulkRestore()" 
                                        id="btnBulkRestore"
                                        disabled
                                        class="btn btn-sm btn-success">
                                    <i class="fas fa-undo"></i> Restore Terpilih
                                </button>
                                <button type="button" 
                                        onclick="bulkPermanentDelete()" 
                                        id="btnBulkPermanentDelete"
                                        disabled
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-times-circle"></i> Delete Permanent
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-items-center mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0" style="width: 40px;"></th>
                                        <th class="border-0">Siswa</th>
                                        <th class="border-0">Kelas</th>
                                        <th class="border-0">Alasan Keluar</th>
                                        <th class="border-0">Keterangan</th>
                                        <th class="border-0">Dihapus Pada</th>
                                        <th class="border-0 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deletedSiswa as $siswa)
                                        <tr>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" 
                                                           class="custom-control-input siswa-checkbox" 
                                                           id="check{{ $siswa->id }}"
                                                           value="{{ $siswa->id }}"
                                                           onchange="updateSelection()">
                                                    <label class="custom-control-label" for="check{{ $siswa->id }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0">{{ $siswa->nama_siswa }}</h6>
                                                    <small class="text-muted">NISN: {{ $siswa->nisn }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match($siswa->alasan_keluar) {
                                                        'Alumni' => 'badge-success',
                                                        'Dikeluarkan' => 'badge-danger',
                                                        'Pindah Sekolah' => 'badge-warning',
                                                        default => 'badge-secondary'
                                                    };
                                                    $icon = match($siswa->alasan_keluar) {
                                                        'Alumni' => '🎓',
                                                        'Dikeluarkan' => '🚪',
                                                        'Pindah Sekolah' => '🏫',
                                                        default => '❓'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ $icon }} {{ $siswa->alasan_keluar }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $siswa->keterangan_keluar ? Str::limit($siswa->keterangan_keluar, 40) : '-' }}</small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $siswa->deleted_at->format('d/m/Y H:i') }}<br>
                                                    <span class="text-muted">{{ $siswa->deleted_at->diffForHumans() }}</span>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <form action="{{ route('siswa.restore', $siswa->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success" title="Restore"
                                                                onclick="return confirm('Restore siswa {{ addslashes($siswa->nama_siswa) }}?')">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" class="btn btn-danger" 
                                                            onclick="showPermanentDeleteModal({{ $siswa->id }}, '{{ addslashes($siswa->nama_siswa) }}')"
                                                            title="Delete Permanent">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    <div class="card-footer bg-light">
                        {{ $deletedSiswa->links('pagination::bootstrap-4') }}
                    </div>
                @else
                    <div class="card-body">
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5>Tidak ada data siswa terhapus</h5>
                            <p class="text-muted">Semua siswa masih aktif, atau gunakan filter untuk mencari data tertentu.</p>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Permanent Delete Modal --}}
<div class="modal fade" id="permanentDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="permanentDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        PERMANENT DELETE
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>⚠️ PERINGATAN KERAS!</strong>
                        <p class="mb-2">Anda akan PERMANENT DELETE siswa: <strong id="permanentDeleteName"></strong></p>
                        <p class="mb-0">Data akan <strong>HILANG SELAMANYA</strong> dari database dan <strong>TIDAK BISA</strong> di-restore!</p>
                    </div>
                    
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="confirm_permanent" value="1" 
                               class="custom-control-input" id="confirmPermanent" required>
                        <label class="custom-control-label text-danger font-weight-bold" for="confirmPermanent">
                            Saya memahami data akan HILANG PERMANENT
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> DELETE PERMANENT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Pure Vanilla JavaScript - No dependencies
let selectedIds = [];

// Toggle select all
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.siswa-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelection();
}

// Update selection
function updateSelection() {
    const checkboxes = document.querySelectorAll('.siswa-checkbox:checked');
    selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    // Update counter
    document.getElementById('selectedCount').textContent = selectedIds.length + ' dipilih';
    
    // Enable/disable buttons
    const btnRestore = document.getElementById('btnBulkRestore');
    const btnDelete = document.getElementById('btnBulkPermanentDelete');
    const shouldEnable = selectedIds.length > 0;
    
    btnRestore.disabled = !shouldEnable;
    btnDelete.disabled = !shouldEnable;
    
    // Update select all checkbox
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.siswa-checkbox');
    if (allCheckboxes.length > 0) {
        selectAll.checked = checkboxes.length === allCheckboxes.length;
    }
}

// Bulk restore
function bulkRestore() {
    if (selectedIds.length === 0) return;
    
    if (confirm(`Restore ${selectedIds.length} siswa?\n\nSemua data terkait akan di-restore.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/siswa/${selectedIds[0]}/restore`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk permanent delete
function bulkPermanentDelete() {
    if (selectedIds.length === 0) return;
    
    const confirmation = prompt(
        `⚠️ PERMANENT DELETE ${selectedIds.length} siswa?\n\n` +
        `Data akan HILANG SELAMANYA!\n\n` +
        `Ketik "HAPUS PERMANENT" untuk confirm:`,
        ''
    );
    
    if (confirmation === 'HAPUS PERMANENT') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("siswa.bulk-force-delete") }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        const confirm = document.createElement('input');
        confirm.type = 'hidden';
        confirm.name = 'confirm_permanent';
        confirm.value = '1';
        form.appendChild(confirm);
        
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'siswa_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    } else {
        alert('Permanent delete dibatalkan.');
    }
}

// Show permanent delete modal
function showPermanentDeleteModal(id, name) {
    document.getElementById('permanentDeleteName').textContent = name;
    document.getElementById('permanentDeleteForm').action = `/siswa/${id}/force-delete`;
    document.getElementById('confirmPermanent').checked = false;
    $('#permanentDeleteModal').modal('show');
}
</script>
@endpush
@endsection
