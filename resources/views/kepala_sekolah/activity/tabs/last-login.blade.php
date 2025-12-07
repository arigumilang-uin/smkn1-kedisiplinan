<!-- Tab: Last Login Users -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <input type="hidden" name="tab" value="last-login">
                    
                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari nama/username/email..." value="{{ request('search') }}" style="width: 250px;">

                    <select name="role_id" class="form-control form-control-sm mr-2" style="width: 150px;">
                        <option value="">-- Semua Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->nama_role }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>

                    @if(request()->hasAny(['search', 'role_id']))
                    <a href="{{ route('audit.activity.index', ['tab' => 'last-login']) }}" class="btn btn-secondary btn-sm">
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
                                <th width="25%">Last Login</th>
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
                                <td>
                                    @if($user->last_login_at)
                                        <strong>{{ formatRelative($user->last_login_at) }}</strong>
                                        <br><small class="text-muted">{{ formatDateTime($user->last_login_at, 'd M Y, H:i:s') }}</small>
                                    @else
                                        <span class="text-muted">Belum pernah login</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
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
