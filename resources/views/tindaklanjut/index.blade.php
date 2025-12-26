@extends('layouts.app')

@section('content')

{{-- Tailwind Config --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    accent: '#3b82f6',
                    success: '#10b981',
                    info: '#3b82f6',
                    warning: '#f59e0b',
                    danger: '#f43f5e',
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

@php
    use App\Enums\StatusTindakLanjut;
    
    // Get current filter
    $currentStatus = request('status');
    $isArsip = in_array($currentStatus, ['Selesai', 'Ditolak']);
    
    // Build base query sesuai role
    $baseQuery = \App\Models\TindakLanjut::query();
    
    $user = auth()->user();
    if ($role === 'Wali Kelas') {
        $kelasBinaan = $user->kelasDiampu;
        if ($kelasBinaan) {
            $baseQuery->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasBinaan->id));
        }
    } elseif ($role === 'Kaprodi') {
        // Support multiple jurusan via Program Keahlian
        $jurusanIds = $user->getJurusanIdsForKaprodi();
        if (!empty($jurusanIds)) {
            $baseQuery->whereHas('siswa.kelas', fn($q) => $q->whereIn('jurusan_id', $jurusanIds));
        }
    }
    
    // Statistics sesuai role
    $stats = [
        'total' => $tindakLanjut->total(),
        'aktif' => (clone $baseQuery)->whereIn('status', StatusTindakLanjut::activeStatuses())->count(),
        'selesai' => (clone $baseQuery)->where('status', StatusTindakLanjut::SELESAI)->count(),
        'ditolak' => (clone $baseQuery)->where('status', StatusTindakLanjut::DITOLAK)->count(),
    ];
    
    // Status badge colors
    $statusColors = [
        'Baru' => 'bg-blue-100 text-blue-700 border-blue-200',
        'Menunggu Persetujuan' => 'bg-amber-100 text-amber-700 border-amber-200',
        'Disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Ditolak' => 'bg-rose-100 text-rose-700 border-rose-200',
        'Ditangani' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
        'Selesai' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];
    
    // Check if user has full access
    $hasFullAccess = in_array($role, ['Waka Kesiswaan', 'Kepala Sekolah', 'Operator Sekolah', 'Developer']);
@endphp

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-indigo-600 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Manajemen Kasus</span>
                    @if(!$hasFullAccess)
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-amber-50 text-amber-700 px-2 py-0.5 rounded border border-amber-200">
                        @if($role === 'Wali Kelas')
                            <i class="fas fa-filter mr-1"></i>{{ $user->kelasDiampu->nama_kelas ?? 'Kelas Saya' }}
                        @elseif($role === 'Kaprodi')
                            @php $jurusanDiampu = $user->jurusanDiampu; @endphp
                            <i class="fas fa-filter mr-1"></i>{{ $jurusanDiampu?->programKeahlian?->nama_program ?? $jurusanDiampu?->nama_jurusan ?? 'Program Saya' }}
                        @endif
                    </span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-folder-open text-indigo-600"></i> Daftar Tindak Lanjut
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    @if($role === 'Wali Kelas')
                        Kasus siswa di kelas binaan Anda.
                    @elseif($role === 'Kaprodi')
                        Kasus siswa di jurusan binaan Anda.
                    @else
                        Kelola semua kasus pelanggaran siswa termasuk arsip.
                    @endif
                </p>
            </div>
            
            <div class="flex gap-2 mt-4 md:mt-0">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        {{-- STATISTICS CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('tindak-lanjut.index') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-indigo-300 transition-all no-underline">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <i class="fas fa-folder"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-700">{{ $stats['aktif'] + $stats['selesai'] + $stats['ditolak'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Semua Kasus</p>
            </a>

            <a href="{{ route('tindak-lanjut.index', ['active_only' => 1]) }}" class="bg-white rounded-2xl p-5 border {{ !$isArsip && !$currentStatus ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-indigo-200' }} shadow-sm hover:border-indigo-300 transition-all no-underline">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-wider">Aktif</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-indigo-600">{{ $stats['aktif'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Dalam Proses</p>
            </a>

            <a href="{{ route('tindak-lanjut.index', ['status' => 'Selesai']) }}" class="bg-white rounded-2xl p-5 border {{ $currentStatus === 'Selesai' ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-emerald-200' }} shadow-sm hover:border-emerald-300 transition-all no-underline">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Selesai</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-emerald-600">{{ $stats['selesai'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Tuntas</p>
            </a>

            <a href="{{ route('tindak-lanjut.index', ['status' => 'Ditolak']) }}" class="bg-white rounded-2xl p-5 border {{ $currentStatus === 'Ditolak' ? 'border-rose-400 ring-2 ring-rose-100' : 'border-rose-200' }} shadow-sm hover:border-rose-300 transition-all no-underline">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-rose-500 uppercase tracking-wider">Ditolak</span>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-rose-600">{{ $stats['ditolak'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Tidak Disetujui</p>
            </a>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h5 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                    <i class="fas fa-filter text-slate-400"></i> Filter Kasus
                </h5>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('tindak-lanjut.index') }}" class="grid grid-cols-1 md:grid-cols-{{ $hasFullAccess ? '5' : '3' }} gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Status</label>
                        <select name="status" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                            <option value="">Semua Status</option>
                            @foreach(StatusTindakLanjut::cases() as $status)
                                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    @if($hasFullAccess || $role === 'Kaprodi')
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Kelas</label>
                        <select name="kelas_id" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none" {{ $role === 'Wali Kelas' ? 'disabled' : '' }}>
                            <option value="">Semua Kelas</option>
                            @php
                                // Kaprodi: hanya tampilkan kelas di jurusannya
                                $kelasList = \App\Models\Kelas::orderBy('nama_kelas');
                                if ($role === 'Kaprodi' && $user->jurusanDiampu) {
                                    $kelasList = $kelasList->where('jurusan_id', $user->jurusanDiampu->id);
                                }
                                $kelasList = $kelasList->get();
                            @endphp
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    @if($hasFullAccess)
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Jurusan</label>
                        <select name="jurusan_id" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                            <option value="">Semua Jurusan</option>
                            @foreach(\App\Models\Jurusan::orderBy('nama_jurusan')->get() as $jurusan)
                                <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>{{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Per Halaman</label>
                        <select name="perPage" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                            <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('perPage', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-indigo-700 transition-all">
                            Filter
                        </button>
                        <a href="{{ route('tindak-lanjut.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-slate-200 transition-all no-underline">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h5 class="text-sm font-bold text-slate-700 m-0">
                    @if($currentStatus)
                        Kasus dengan Status: <span class="text-indigo-600">{{ $currentStatus }}</span>
                    @elseif(request('active_only'))
                        Kasus Aktif
                    @else
                        Semua Kasus
                    @endif
                </h5>
                <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200">
                    Menampilkan: <b class="text-indigo-600">{{ $tindakLanjut->count() }}</b> dari <b>{{ $tindakLanjut->total() }}</b>
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="px-6 py-4">Siswa</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4">Sanksi</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($tindakLanjut as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center font-black text-sm">
                                        {{ strtoupper(substr($item->siswa->nama_siswa ?? 'X', 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('siswa.show', $item->siswa_id) }}" class="text-sm font-bold text-slate-700 hover:text-indigo-600 no-underline">
                                            {{ $item->siswa->nama_siswa ?? '-' }}
                                        </a>
                                        <span class="block text-[10px] font-mono text-slate-400">{{ $item->siswa->nisn ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-700">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $item->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-[11px] text-slate-600 leading-relaxed m-0 line-clamp-2">{{ $item->sanksi_deskripsi ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $statusColors[$item->status->value] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $item->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-700">{{ $item->tanggal_tindak_lanjut ? \Carbon\Carbon::parse($item->tanggal_tindak_lanjut)->format('d M Y') : '-' }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $item->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('tindak-lanjut.show', $item->id) }}" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-bold uppercase hover:bg-slate-200 transition-all no-underline" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($item->status->isActive())
                                    <a href="{{ route('tindak-lanjut.edit', $item->id) }}" class="px-3 py-2 rounded-lg bg-indigo-100 text-indigo-600 text-[10px] font-bold uppercase hover:bg-indigo-200 transition-all no-underline" title="Kelola">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-16">
                                <div class="flex flex-col items-center opacity-60">
                                    <i class="fas fa-folder-open text-4xl text-slate-300 mb-3"></i>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Tidak Ada Kasus</p>
                                    <p class="text-xs text-slate-400">Belum ada kasus yang sesuai dengan filter.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($tindakLanjut->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $tindakLanjut->appends(request()->query())->links() }}
            </div>
            @endif
        </div>

        {{-- INFO SECTION --}}
        <div class="mt-8 p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100">
            <h6 class="text-sm font-bold text-indigo-800 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Informasi Status Kasus
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-indigo-700/80">
                <div>
                    <span class="font-bold">Aktif:</span>
                    <ul class="pl-4 mt-1 space-y-1 mb-0">
                        <li><span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 text-[9px] font-bold">Baru</span> - Kasus baru dibuat</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[9px] font-bold">Menunggu Persetujuan</span> - Menunggu Kepsek</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[9px] font-bold">Disetujui</span> - Siap ditangani</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 text-[9px] font-bold">Ditangani</span> - Sedang diproses</li>
                    </ul>
                </div>
                <div>
                    <span class="font-bold">Arsip:</span>
                    <ul class="pl-4 mt-1 space-y-1 mb-0">
                        <li><span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[9px] font-bold">Selesai</span> - Kasus tuntas</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-[9px] font-bold">Ditolak</span> - Tidak disetujui Kepsek</li>
                    </ul>
                </div>
                <div>
                    <span class="font-bold">Tips:</span>
                    <ul class="pl-4 mt-1 space-y-1 mb-0">
                        <li>Klik card statistik untuk filter cepat</li>
                        <li>Kasus selesai/ditolak tetap tersimpan sebagai arsip</li>
                        <li>Klik nama siswa untuk melihat profil lengkap</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-wrap-custom { background: #f8fafc; font-family: 'Inter', sans-serif; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
