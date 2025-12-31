    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Siswa --}}
        <div class="stat-card group cursor-default">
            <div class="stat-card-icon primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Total Siswa</p>
                <p class="stat-card-value">{{ number_format($totalSiswa ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Siswa Aktif</p>
            </div>
        </div>
        
        {{-- Total Pelanggaran --}}
        <div class="stat-card group cursor-default">
            <div class="stat-card-icon danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Pelanggaran</p>
                <p class="stat-card-value">{{ number_format($totalPelanggaran ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Periode Ini</p>
            </div>
        </div>
        
        {{-- Total Kasus --}}
        <div class="stat-card group cursor-default">
            <div class="stat-card-icon warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Kasus Aktif</p>
                <p class="stat-card-value">{{ number_format($totalKasus ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Perlu Penanganan</p>
            </div>
        </div>
        
        {{-- Menunggu Persetujuan --}}
        <a href="{{ route('kepala-sekolah.approvals.index') }}" class="stat-card group">
            <div class="stat-card-icon success">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Persetujuan</p>
                <p class="stat-card-value">{{ number_format($totalKasusMenunggu ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Menunggu Acc</p>
            </div>
        </a>
    </div>
