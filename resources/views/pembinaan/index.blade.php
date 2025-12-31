@extends('layouts.app')

@section('title', 'Pembinaan Internal')
@section('subtitle', 'Monitoring dan tracking status pembinaan internal siswa.')
@section('page-header', true)

@section('content')
<div class="space-y-6" x-data="pembinaanPage()">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Total</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Siswa</p>
        </div>

        <div class="card p-5 border-amber-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wide">Perlu Pembinaan</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-amber-600">{{ $stats['perlu_pembinaan'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Menunggu</p>
        </div>

        <div class="card p-5 border-blue-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wide">Sedang Dibina</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-blue-600">{{ $stats['sedang_dibina'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Proses</p>
        </div>

        <div class="card p-5 border-emerald-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wide">Selesai</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-emerald-600">{{ $stats['selesai'] ?? 0 }}</h3>
            <p class="text-[10px] text-gray-400 uppercase font-bold">Tuntas</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span class="card-title">Filter & Export</span>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pembinaan.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input form-select">
                        <option value="">Semua Status</option>
                        <option value="Perlu Pembinaan" {{ ($statusFilter ?? '') == 'Perlu Pembinaan' ? 'selected' : '' }}>🟡 Perlu Pembinaan</option>
                        <option value="Sedang Dibina" {{ ($statusFilter ?? '') == 'Sedang Dibina' ? 'selected' : '' }}>🔵 Sedang Dibina</option>
                        <option value="Selesai" {{ ($statusFilter ?? '') == 'Selesai' ? 'selected' : '' }}>🟢 Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Range Poin</label>
                    <select name="rule_id" class="form-input form-select">
                        <option value="">Semua Range</option>
                        @foreach($rules ?? [] as $rule)
                            <option value="{{ $rule->id }}" {{ ($ruleId ?? '') == $rule->id ? 'selected' : '' }}>{{ $rule->getRangeText() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList ?? [] as $kelas)
                            <option value="{{ $kelas->id }}" {{ ($kelasId ?? '') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Jurusan</label>
                    <select name="jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList ?? [] as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ ($jurusanId ?? '') == $jurusan->id ? 'selected' : '' }}>{{ $jurusan->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group flex items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-1">Filter</button>
                    <a href="{{ route('pembinaan.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Siswa</th>
                    <th class="">Kelas</th>
                    <th class="text-center">Poin</th>
                    <th class="">Keterangan</th>
                    <th class="text-center">Status</th>
                    <th class="">Dibina Oleh</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembinaanList ?? [] as $item)
                    <tr>
                        {{-- Siswa --}}
                        <td>
                            <a href="{{ route('siswa.show', $item->siswa->id ?? 0) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $item->siswa->nama_siswa ?? '-' }}
                            </a>
                            <span class="block text-[10px] text-gray-400 font-mono">{{ $item->siswa->nisn ?? '-' }}</span>
                        </td>
                        
                        {{-- Kelas --}}
                        <td class="">
                            <span class="font-medium text-gray-700">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</span>
                            <span class="block text-[10px] text-gray-400">{{ $item->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                        </td>
                        
                        {{-- Poin --}}
                        <td class="text-center">
                            @php
                                $p = $item->total_poin_saat_trigger ?? 0;
                                $badgeClass = $p > 300 ? 'badge-danger' : ($p > 100 ? 'badge-warning' : 'badge-info');
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $p }} Poin</span>
                        </td>
                        
                        {{-- Keterangan --}}
                        <td class="max-w-xs">
                            <p class="text-xs text-gray-600 italic truncate">"{{ $item->keterangan_pembinaan }}"</p>
                            <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $item->range_text }}</span>
                        </td>
                        
                        {{-- Status --}}
                        <td class="text-center">
                            @php
                                $status = $item->status->value ?? $item->status ?? 'Unknown';
                                $statusClass = match($status) {
                                    'Perlu Pembinaan' => 'badge-warning',
                                    'Sedang Dibina' => 'badge-info',
                                    'Selesai' => 'badge-success',
                                    default => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $status }}</span>
                        </td>
                        
                        {{-- Dibina Oleh --}}
                        <td class="">
                            @if($item->dibinaOleh)
                                <span class="font-medium text-gray-700">{{ $item->dibinaOleh->nama ?? $item->dibinaOleh->username }}</span>
                                <span class="block text-[9px] text-gray-400">{{ $item->dibina_at?->format('d M Y') }}</span>
                            @else
                                <span class="text-gray-400 italic text-xs">-</span>
                            @endif
                        </td>
                        
                        {{-- Aksi --}}
                        <td class="text-center">
                            @php $statusValue = $item->status->value ?? $item->status; @endphp
                            @if($statusValue === 'Perlu Pembinaan')
                                <form action="{{ route('pembinaan.mulai', $item->id) }}" method="POST" 
                                      onsubmit="return confirm('Mulai pembinaan untuk ' + {{ json_encode($item->siswa->nama_siswa ?? 'siswa ini') }} + '?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-primary text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                                        Mulai
                                    </button>
                                </form>
                            @elseif($statusValue === 'Sedang Dibina')
                                <button type="button" 
                                        @click="openModal({{ $item->id }}, {{ json_encode($item->siswa->nama_siswa ?? '') }})" 
                                        class="btn btn-success text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                                    Selesai
                                </button>
                            @else
                                <span class="text-emerald-600 font-bold text-xs flex items-center justify-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                                    Tuntas
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                                </svg>
                                <h3 class="empty-state-title">Tidak Ada Data</h3>
                                <p class="empty-state-description">Belum ada siswa yang perlu pembinaan atau semua sudah selesai.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Info Section --}}
    <div class="p-6 bg-blue-50 rounded-xl border border-blue-100">
        <h6 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            Informasi Penting
        </h6>
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-xs text-blue-700/80 ml-4 list-disc">
            <li><strong>Perlu Pembinaan</strong> = Siswa yang mencapai threshold poin dan belum ditangani.</li>
            <li><strong>Sedang Dibina</strong> = Proses pembinaan sedang berlangsung oleh pembina.</li>
            <li><strong>Selesai</strong> = Pembinaan telah selesai dengan hasil yang tercatat.</li>
            <li>Klik nama siswa untuk melihat <strong>riwayat lengkap</strong> pelanggaran.</li>
        </ul>
    </div>

    {{-- Modal Selesaikan --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form :action="'/pembinaan/' + selectedId + '/selesaikan'" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 border-b border-gray-100 bg-emerald-50">
                        <h3 class="text-lg font-bold text-emerald-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                            Selesaikan Pembinaan
                        </h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600">
                            Selesaikan pembinaan untuk: <strong x-text="selectedName" class="text-gray-800"></strong>
                        </p>
                        
                        <div class="form-group">
                            <label class="form-label">Hasil Pembinaan</label>
                            <textarea name="hasil_pembinaan" rows="4" class="form-input form-textarea" 
                                      placeholder="Tuliskan hasil/catatan pembinaan..."></textarea>
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                            Selesaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pembinaanPage() {
    return {
        showModal: false,
        selectedId: null,
        selectedName: '',
        
        openModal(id, name) {
            this.selectedId = id;
            this.selectedName = name;
            this.showModal = true;
        }
    }
}
</script>
@endpush
@endsection
