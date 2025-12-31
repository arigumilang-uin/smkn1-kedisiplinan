    {{-- Kasus Terbaru --}}
    <div class="card h-full flex flex-col">
        <div class="card-header border-b border-gray-100">
            <h3 class="card-title">Kasus Perlu Ditangani</h3>
            <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body p-0 flex-1">
            @forelse($kasusBaru ?? [] as $kasus)
                <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 group transition-colors">
                    <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate group-hover:text-amber-600 transition-colors">{{ $kasus->siswa->nama_siswa ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $kasus->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="badge badge-{{ $kasus->status === 'Baru' ? 'info' : 'warning' }}">{{ $kasus->status }}</span>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-12 px-4 text-center h-full">
                    <div class="w-12 h-12 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                    </div>
                    <p class="text-sm text-gray-500">Bagus! Tidak ada kasus yang perlu ditangani saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
