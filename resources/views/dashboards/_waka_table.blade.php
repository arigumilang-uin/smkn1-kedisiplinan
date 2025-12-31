    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kasus Terbaru</h3>
            <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-sm btn-secondary">
                Lihat Semua
            </a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="w-20 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarKasus ?? [] as $kasus)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-800">{{ $kasus->siswa->nama_siswa ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $kasus->siswa->nisn ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-neutral">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'Baru' => 'badge-info',
                                        'Menunggu Persetujuan' => 'badge-warning',
                                        'Disetujui' => 'badge-success',
                                        'Ditangani' => 'badge-primary',
                                        'Selesai' => 'badge-neutral',
                                        'Ditolak' => 'badge-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$kasus->status] ?? 'badge-neutral' }}">
                                    {{ $kasus->status }}
                                </span>
                            </td>
                            <td class="text-gray-600 text-sm">{{ $kasus->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="btn btn-icon btn-outline" title="Detail Kasus">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/>
                                    </svg>
                                    <p class="empty-state-description">Tidak ada kasus dalam periode yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
