<!-- Tab: Status Akun -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <input type="hidden" name="tab" value="status">
                    
                    <select name="status" class="form-control form-control-sm mr-2" style="width: 150px;">
                        <option value="">-- Semua Status --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <select name="role_id" class="form-control form-control-sm mr-2" style="width: 150px;">
                        <option value="">-- Semua Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->nama_role }}
                            </option>
                        @endforeach
                    </select>

                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari nama/username/email..." value="{{ request('search') }}" style="width: 250px;">

                    <button type="submit" class="btn btn-primary btn-sm mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>

                    @if(request()->hasAny(['search', 'role_id', 'status']))
                    <a href="{{ route('audit.activity.index', ['tab' => 'status']) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Total: {{ $users->total() }} pengguna</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Nama</th>
                                <th width="15%">Username</th>
                                <th width="15%">Role</th>
                                <th width="20%">Email</th>
                                <th width="15%" class="text-center">Status</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $key => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $key }}</td>
                                <td>{{ $user->nama }}</td>
                                <td><code>{{ $user->username }}</code></td>
                                <td>
                                    <span class="badge badge-info">{{ $user->role->nama_role ?? '-' }}</span>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">
                                    @if($user->is_active)
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-ban"></i> Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(Auth::id() != $user->id)
                                    <form action="{{ route('users.toggle-active', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }}" 
                                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                onclick="return confirm('Yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun {{ $user->nama }}?')">
                                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    @else
                                    <span class="badge badge-info">Akun Anda</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Tidak ada data pengguna.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
