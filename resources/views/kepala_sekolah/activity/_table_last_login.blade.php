    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Daftar Sesi Login</span>
            <span class="badge badge-primary">Total: {{ $users->total() ?? 0 }} User</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16 text-center">#</th>
                        <th class="w-72">Identitas Pengguna</th>
                        <th class="w-40 text-center">Role / Jabatan</th>
                        <th>Kontak Email</th>
                        <th class="text-right">Aktivitas Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $key => $user)
                        <tr>
                            <td class="text-center text-gray-400">{{ $users->firstItem() + $key }}</td>
                            <td>
                                <div class="font-medium text-gray-700">{{ $user->nama }}</div>
                                <div class="text-[10px] text-indigo-500 font-mono uppercase">{{ $user->username }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-neutral">{{ $user->role->nama_role ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <span class="italic truncate">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                @if($user->last_login_at)
                                    <div class="font-medium text-gray-700">{{ $user->last_login_at->diffForHumans() }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $user->last_login_at->format('d M Y, H:i:s') }}</div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum pernah login</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="11" x2="22" y2="11"/>
                                    </svg>
                                    <h3 class="empty-state-title">Tidak Ada Data</h3>
                                    <p class="empty-state-description">Tidak ada data pengguna ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($users) && $users->hasPages())
        <div class="card-body border-t border-gray-100">
            {{ $users->appends(request()->except('render_partial'))->links() }}
        </div>
        @endif
    </div>
