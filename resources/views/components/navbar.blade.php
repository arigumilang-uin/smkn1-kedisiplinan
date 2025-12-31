{{-- Navbar Component --}}
<div class="navbar-left">
    <!-- Mobile Menu Toggle -->
    <button type="button" class="navbar-toggle" @click="toggle()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
        </svg>
    </button>
    
    <!-- Breadcrumb / School Year -->
    <div class="navbar-title hidden sm:block">
        <span class="text-gray-400">Tahun Ajaran:</span>
        <span class="font-medium text-gray-700">{{ school_year() ?? date('Y') . '/' . (date('Y') + 1) }}</span>
    </div>
</div>

<div class="navbar-right">
    {{-- Notifications (Kepala Sekolah Only) --}}
    @if(Auth::check() && Auth::user()->hasRole('Kepala Sekolah'))
        @php
            $unreadCount = Auth::user()->unreadNotifications()->count();
            $notifications = Auth::user()->unreadNotifications()->limit(5)->get();
        @endphp
        <div x-data="dropdown" class="dropdown relative">
            <button type="button" class="navbar-btn" @click="toggle()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="navbar-btn-badge"></span>
                @endif
            </button>
            
            <div class="dropdown-menu" style="width: 320px;" @click.away="close()" x-show="open" x-transition x-cloak>
                <div class="px-4 py-3 border-b border-gray-100">
                    <h4 class="font-semibold text-gray-800">Notifikasi</h4>
                    <p class="text-xs text-gray-500">{{ $unreadCount }} belum dibaca</p>
                </div>
                
                <div class="max-h-64 overflow-y-auto">
                    @forelse($notifications as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="dropdown-item !py-3">
                            <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Z"/><polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 truncate">{{ $notification->data['siswa_nama'] ?? 'Notifikasi Baru' }}</p>
                                <p class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="py-8 text-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 opacity-50">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><line x1="1" x2="23" y1="1" y2="23"/>
                            </svg>
                            <p class="text-sm">Tidak ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
                
                @if($unreadCount > 0)
                    <div class="p-2 border-t border-gray-100">
                        <a href="{{ route('kepala-sekolah.approvals.index') }}" class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium py-2 rounded-lg hover:bg-gray-50">
                            Lihat Semua
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- User Dropdown --}}
    <div x-data="dropdown" class="dropdown relative">
        <button type="button" class="navbar-user" @click="toggle()">
            <div class="navbar-user-avatar">
                {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
            </div>
            <span class="navbar-user-name hidden sm:block">{{ Str::limit(Auth::user()->username ?? 'User', 12) }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 hidden sm:block">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </button>
        
        <div class="dropdown-menu" @click.away="close()" x-show="open" x-transition x-cloak>
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="font-medium text-gray-800">{{ Auth::user()->username ?? 'User' }}</p>
                <p class="text-xs text-gray-500">{{ Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role ?? 'User' }}</p>
            </div>
            
            <a href="{{ route('account.edit') }}" class="dropdown-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>
                </svg>
                <span>Profil Saya</span>
            </a>
            
            <div class="dropdown-divider"></div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item danger w-full" onclick="return confirm('Keluar dari sistem?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
