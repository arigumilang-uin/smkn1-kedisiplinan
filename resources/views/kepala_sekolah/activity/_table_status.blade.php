    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Manajemen Akses Akun</span>
            <span class="badge badge-primary">Total: {{ $users->total() ?? 0 }} Pengguna</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16 text-center">#</th>
                        <th class="w-72">Identitas & Jabatan</th>
                        <th>Kontak Email</th>
                        <th class="w-32 text-center">Status</th>
                        <th class="w-48 text-center">Kontrol Akses</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $key => $user)
                        <tr>
                            <td class="text-center text-gray-400">{{ $users->firstItem() + $key }}</td>
                            <td>
                                <div class="font-medium text-gray-700">{{ $user->nama }}</div>
                                <div class="text-[10px] text-indigo-500 font-mono uppercase">{{ $user->username }}</div>
                                <div class="text-[9px] text-gray-400 font-bold uppercase">{{ $user->role->nama_role ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                    <span class="italic">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[9px] font-bold uppercase border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-gray-100 text-gray-400 text-[9px] font-bold uppercase border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Off
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(Auth::id() != $user->id)
                                    <form action="{{ route('users.toggle-active', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-outline text-xs w-full {{ $user->is_active ? 'hover:bg-red-50 hover:text-red-600 hover:border-red-200' : 'hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200' }}"
                                                onclick="return confirm('Yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun {{ $user->nama }}?')">
                                            @if($user->is_active)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="8" x2="22" y2="13"/><line x1="22" y1="8" x2="17" y2="13"/></svg>
                                                Suspend
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
                                                Activate
                                            @endif
                                        </button>
                                    </form>
                                @else
                                    <div class="px-3 py-2 rounded-xl bg-indigo-50 text-indigo-400 text-[10px] font-bold uppercase">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                        My Account
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M12 17v4"/><path d="M8 21h8"/>
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
