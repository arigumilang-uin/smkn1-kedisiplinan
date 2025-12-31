
    {{-- Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Login Terakhir</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                    <tr>
                        <td class="font-medium text-gray-800">{{ $user->username }}</td>
                        <td class="text-gray-500">{{ $user->email ?? '-' }}</td>
                        <td>
                            <span class="badge badge-primary">{{ $user->role->nama_role ?? '-' }}</span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-gray-500 text-sm">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '-' }}</td>
                        <td>
                            {{-- Desktop: Icon buttons --}}
                            <div class="action-buttons-desktop">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </a>
                                <form action="{{ route('users.toggle-active', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-icon btn-outline {{ $user->is_active ? 'text-amber-500' : 'text-green-500' }}" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($user->is_active)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                            
                            {{-- Mobile: Dropdown --}}
                            <div class="action-dropdown-mobile" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="action-dropdown-trigger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="action-dropdown-menu">
                                    <a href="{{ route('users.edit', $user->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        Edit
                                    </a>
                                    <div class="action-dropdown-divider"></div>
                                    <form action="{{ route('users.toggle-active', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="action-dropdown-item {{ $user->is_active ? 'action-dropdown-item--delete' : 'action-dropdown-item--view' }}">
                                            @if($user->is_active)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                                                Nonaktifkan
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                                Aktifkan
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                <h3 class="empty-state-title">Tidak Ada User</h3>
                                <p class="empty-state-description">Belum ada user yang terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(method_exists($users, 'hasPages') && $users->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <p class="text-sm text-gray-500">Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }}</p>
            <div class="pagination">
                {{-- Previous --}}
                @if($users->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </a>
                @endif
                
                {{-- Next --}}
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                @else
                    <span class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    @endif
