{{-- Sidebar Component --}}
@php
    $user = Auth::user();
    $role = $user?->effectiveRoleName() ?? $user?->role?->nama_role ?? 'Guest';
    $isDeveloper = $user?->isDeveloper() ?? false;
    $override = session('developer_role_override');
@endphp

<!-- Brand -->
<div class="sidebar-brand">
    <div class="sidebar-brand-logo">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
            <path d="m9 12 2 2 4-4"/>
        </svg>
    </div>
    <div class="sidebar-brand-text">
        SIMDIS <span>SMK</span>
    </div>
</div>

<!-- User Info -->
@auth
<div class="sidebar-user">
    <div class="sidebar-user-avatar">
        {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
    </div>
    <div class="sidebar-user-info">
        <div class="sidebar-user-name">{{ Str::limit($user->username ?? 'User', 18) }}</div>
        <div class="sidebar-user-role">{{ $role }}</div>
    </div>
</div>
@endauth

<!-- Navigation -->
<nav class="sidebar-nav">
    
    {{-- Developer Switch Role (if applicable) --}}
    @if($isDeveloper)
        <div class="sidebar-section">Developer</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('dashboard.developer') }}" class="sidebar-menu-link {{ Request::routeIs('dashboard.developer') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                    </svg>
                    <span>Console</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Dashboard --}}
    @unless($isDeveloper && !$override)
        <div class="sidebar-section">Menu Utama</div>
        <ul class="sidebar-menu">
            @php
                $dashRoute = match(true) {
                    $isDeveloper && !$override => route('dashboard.developer'),
                    in_array($role, ['Operator Sekolah', 'Waka Kesiswaan']) => route('dashboard.admin'),
                    $role === 'Kepala Sekolah' => route('dashboard.kepsek'),
                    $role === 'Wali Kelas' => route('dashboard.walikelas'),
                    $role === 'Kaprodi' => route('dashboard.kaprodi'),
                    $role === 'Wali Murid' => route('dashboard.wali_murid'),
                    $role === 'Waka Sarana' => route('dashboard.waka-sarana'),
                    default => route('dashboard.admin')
                };
            @endphp
            <li class="sidebar-menu-item">
                <a href="{{ $dashRoute }}" class="sidebar-menu-link {{ Request::is('dashboard*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>
    @endunless
    
    {{-- Operational Menu (Guru, Wali Kelas, Waka, Kaprodi, Waka Sarana) --}}
    @if(in_array($role, ['Guru', 'Wali Kelas', 'Waka Kesiswaan', 'Kaprodi', 'Waka Sarana']) || $isDeveloper)
        <div class="sidebar-section">Operasional</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('riwayat.create') }}" class="sidebar-menu-link {{ Request::routeIs('riwayat.create', 'pelanggaran.create') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"/>
                    </svg>
                    <span>Catat Pelanggaran</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('my-riwayat.index') }}" class="sidebar-menu-link {{ Request::routeIs('my-riwayat.*', 'riwayat.my') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>
                    </svg>
                    <span>Riwayat Saya</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Monitoring Menu (Operator, Waka, Wali Kelas, Kaprodi, Kepala Sekolah) --}}
    @if(in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi', 'Kepala Sekolah']) || $isDeveloper)
        <div class="sidebar-section">Monitoring</div>
        <ul class="sidebar-menu">
            @if(in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('siswa.index') }}" class="sidebar-menu-link {{ Request::routeIs('siswa.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>{{ $role === 'Wali Kelas' ? 'Siswa Kelas' : 'Data Siswa' }}</span>
                    </a>
                </li>
            @endif
            
            <li class="sidebar-menu-item">
                <a href="{{ route('riwayat.index') }}" class="sidebar-menu-link {{ Request::routeIs('riwayat.index', 'riwayat.show', 'riwayat.edit') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>
                    </svg>
                    <span>Log Pelanggaran</span>
                </a>
            </li>
            
            @if(in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah', 'Operator Sekolah']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('tindak-lanjut.index') }}" class="sidebar-menu-link {{ Request::routeIs('tindak-lanjut.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 17 2 2 4-4"/><path d="m3 7 2 2 4-4"/><path d="M13 6h8"/><path d="M13 12h8"/><path d="M13 18h8"/>
                        </svg>
                        <span>Daftar Kasus</span>
                    </a>
                </li>
            @endif
            
            @if(in_array($role, ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ route('pembinaan.index') }}" class="sidebar-menu-link {{ Request::routeIs('pembinaan.*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>
                        </svg>
                        <span>Siswa Pembinaan</span>
                    </a>
                </li>
            @endif
            
            @if(in_array($role, ['Waka Kesiswaan', 'Kepala Sekolah']) || $isDeveloper)
                <li class="sidebar-menu-item">
                    <a href="{{ in_array($role, ['Kepala Sekolah', 'Waka Kesiswaan']) ? route('kepala-sekolah.data.jurusan') : route('jurusan.index') }}" class="sidebar-menu-link {{ Request::is('*jurusan*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
                        </svg>
                        <span>Data Jurusan</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ in_array($role, ['Kepala Sekolah', 'Waka Kesiswaan']) ? route('kepala-sekolah.data.kelas') : route('kelas.index') }}" class="sidebar-menu-link {{ Request::is('*kelas*') ? 'active' : '' }}">
                        <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/>
                        </svg>
                        <span>Data Kelas</span>
                    </a>
                </li>
            @endif
        </ul>
    @endif
    
    {{-- Administration Menu (Operator Sekolah) --}}
    @if($role === 'Operator Sekolah' || $isDeveloper)
        <div class="sidebar-section">Administrasi</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('users.index') }}" class="sidebar-menu-link {{ Request::routeIs('users.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/><path d="M13.765 2.152C13.398 2 12.932 2 12 2c-.932 0-1.398 0-1.765.152a2 2 0 0 0-1.083 1.083c-.092.223-.129.484-.143.863a1.617 1.617 0 0 1-.79 1.353 1.617 1.617 0 0 1-1.567.008c-.336-.178-.579-.276-.82-.308a2 2 0 0 0-1.478.396C4.04 5.79 3.806 6.193 3.34 7c-.466.807-.7 1.21-.751 1.605a2 2 0 0 0 .396 1.478c.148.192.355.353.676.555.473.297.777.803.777 1.362 0 .56-.304 1.065-.777 1.362-.321.202-.529.363-.676.555a2 2 0 0 0-.396 1.478c.052.394.285.798.75 1.605.467.807.7 1.21 1.015 1.453a2 2 0 0 0 1.479.396c.24-.032.483-.13.819-.308a1.617 1.617 0 0 1 1.567.008c.483.28.77.795.79 1.353.014.38.05.64.143.863a2 2 0 0 0 1.083 1.083C10.602 22 11.068 22 12 22c.932 0 1.398 0 1.765-.152a2 2 0 0 0 1.083-1.083c.092-.223.129-.483.143-.863.02-.558.307-1.074.79-1.353a1.617 1.617 0 0 1 1.567-.008c.336.178.579.276.819.308a2 2 0 0 0 1.479-.396c.315-.242.548-.646 1.014-1.453.466-.807.7-1.21.751-1.605a2 2 0 0 0-.396-1.478c-.148-.192-.355-.353-.676-.555a1.617 1.617 0 0 1-.777-1.362c0-.56.304-1.065.777-1.362.321-.202.528-.363.676-.555a2 2 0 0 0 .396-1.478c-.052-.394-.285-.798-.75-1.605-.467-.807-.7-1.21-1.015-1.453a2 2 0 0 0-1.479-.396c-.24.032-.483.13-.82.308a1.617 1.617 0 0 1-1.566-.008 1.617 1.617 0 0 1-.79-1.353c-.014-.38-.05-.64-.143-.863a2 2 0 0 0-1.083-1.083Z"/>
                    </svg>
                    <span>Manajemen User</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('frequency-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('frequency-rules.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21 15-9-9-9 9"/><path d="M3 21h18"/>
                    </svg>
                    <span>Aturan & Poin</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('pembinaan-internal-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('pembinaan-internal-rules.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 14h2"/><path d="M12 11v6"/><path d="M3.5 5.5 5 7"/><path d="m6 12-1.5 1.5"/><path d="M8 5.5 6.5 4"/><path d="m19 15.5 1.5 1.5"/><path d="M18 18.5 16.5 20"/><path d="m21 12 1.5-1.5"/><path d="m12 3 .5 2"/><path d="M12 22v-2"/><path d="M12 12a6 6 0 0 0-6 6c0 1.2.5 2 2 3l4 1 4-1c1.5-1 2-1.8 2-3a6 6 0 0 0-6-6Z"/>
                    </svg>
                    <span>Pembinaan Internal</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('audit.activity.index') }}" class="sidebar-menu-link {{ Request::routeIs('audit.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M12 7a5 5 0 1 0 5 5"/><path d="M13 3.055A9 9 0 1 0 20.941 11"/><path d="M22 6H16V0"/>
                    </svg>
                    <span>Audit Log</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('jurusan.index') }}" class="sidebar-menu-link {{ Request::is('jurusan*') && !Request::is('*data*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
                    </svg>
                    <span>Master Jurusan</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('kelas.index') }}" class="sidebar-menu-link {{ Request::is('kelas*') && !Request::is('*data*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    <span>Master Kelas</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Kesiswaan Menu (Waka Kesiswaan) --}}
    @if($role === 'Waka Kesiswaan' || $isDeveloper)
        <div class="sidebar-section">Kesiswaan</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('frequency-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('frequency-rules.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/>
                    </svg>
                    <span>Aturan Tata Tertib</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('pembinaan-internal-rules.index') }}" class="sidebar-menu-link {{ Request::routeIs('pembinaan-internal-rules.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span>Pembinaan Internal</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Kepala Sekolah Menu --}}
    @if($role === 'Kepala Sekolah' || $isDeveloper)
        @php
            $pendingCount = \App\Models\TindakLanjut::where('status', 'Menunggu Persetujuan')->count();
        @endphp
        <div class="sidebar-section">Kepala Sekolah</div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('kepala-sekolah.approvals.index') }}" class="sidebar-menu-link {{ Request::routeIs('kepala-sekolah.approvals.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span>Persetujuan</span>
                    @if($pendingCount > 0)
                        <span class="sidebar-menu-badge">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('kepala-sekolah.reports.index') }}" class="sidebar-menu-link {{ Request::routeIs('kepala-sekolah.reports.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>
                    </svg>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="sidebar-menu-link {{ Request::routeIs('kepala-sekolah.siswa-perlu-pembinaan.*') ? 'active' : '' }}">
                    <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>
                    </svg>
                    <span>Siswa Pembinaan</span>
                </a>
            </li>
        </ul>
    @endif
    
    {{-- Settings (All Users) --}}
    <div class="sidebar-section">Pengaturan</div>
    <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
            <a href="{{ route('account.edit') }}" class="sidebar-menu-link {{ Request::routeIs('account.*', 'profile.*') ? 'active' : '' }}">
                <svg class="sidebar-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>
                </svg>
                <span>Profil Saya</span>
            </a>
        </li>
    </ul>
    
</nav>
