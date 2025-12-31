@extends('layouts.app')

@section('title', 'Log Pelanggaran')
@section('subtitle', 'Riwayat pencatatan pelanggaran siswa.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('riwayat.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/><path d="M12 5v14"/>
        </svg>
        <span>Catat Pelanggaran</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: true }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span class="card-title">Filter Data</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </div>
        
        <div class="card-body" x-show="expanded" x-collapse>
            <form action="{{ route('riwayat.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="form-group">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="end_date" class="form-label">Sampai</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="jurusan_id" class="form-label">Jurusan</label>
                    <select id="jurusan_id" name="jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($allJurusan ?? [] as $j)
                            <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group flex items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-1">Filter</button>
                    <a href="{{ route('riwayat.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Stats --}}
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500">
            Total: <b class="text-blue-600">{{ $riwayat->total() }}</b> data
        </span>
    </div>
    
    {{-- Data Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Siswa</th>
                    <th class="">Kelas</th>
                    <th>Pelanggaran</th>
                    <th class="text-center">Poin</th>
                    <th class="">Dicatat Oleh</th>
                    <th class="text-center">Bukti</th>
                    <th class="w-24 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $r)
                    <tr>
                        {{-- Waktu (Tanggal + Jam) --}}
                        <td class="whitespace-nowrap">
                            <div class="font-medium text-gray-800">{{ $r->tanggal_kejadian->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $r->tanggal_kejadian->format('H:i') }} WIB
                            </div>
                        </td>
                        
                        {{-- Siswa --}}
                        <td>
                            <a href="{{ route('siswa.show', $r->siswa->id ?? 0) }}" class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $r->siswa->nama_siswa ?? '-' }}
                            </a>
                        </td>
                        
                        {{-- Kelas --}}
                        <td class="">
                            <span class="badge badge-primary">{{ $r->siswa->kelas->nama_kelas ?? '-' }}</span>
                        </td>
                        
                        {{-- Pelanggaran --}}
                        <td class="max-w-xs">
                            <p class="font-medium text-gray-800">{{ $r->jenisPelanggaran->nama_pelanggaran ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $r->jenisPelanggaran->kategoriPelanggaran->nama_kategori ?? '' }}</p>
                            @if($r->keterangan)
                                <p class="text-sm text-gray-500 truncate mt-1 italic">"{{ Str::limit($r->keterangan, 40) }}"</p>
                            @endif
                        </td>
                        
                        {{-- Poin --}}
                        <td class="text-center">
                            @php
                                // Gunakan helper untuk kalkulasi poin berdasarkan frequency rules
                                $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($r);
                            @endphp
                            @if($poinInfo['matched'] && $poinInfo['poin'] > 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700" 
                                      title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($r) }}">
                                    +{{ $poinInfo['poin'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-400">
                                    +0
                                </span>
                            @endif
                            @if(!empty($poinInfo['frequency']))
                                <div class="text-[10px] text-gray-400 mt-1">{{ $poinInfo['frequency'] }}× Kejadian</div>
                            @endif
                        </td>
                        
                        {{-- Dicatat Oleh --}}
                        <td class="text-sm">
                            @if($r->guruPencatat)
                                <div class="font-medium text-gray-700">{{ $r->guruPencatat->username }}</div>
                                <div class="text-[10px] text-gray-400 uppercase">{{ $r->guruPencatat->role->nama_role ?? 'Staff' }}</div>
                            @else
                                <span class="text-gray-400 italic text-xs">Sistem</span>
                            @endif
                        </td>
                        
                        {{-- Bukti Foto --}}
                        <td class="text-center">
                            @if($r->bukti_foto_path)
                                <a href="{{ asset('storage/' . $r->bukti_foto_path) }}" target="_blank" 
                                   class="btn btn-icon btn-outline" title="Lihat Bukti">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </a>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        
                        {{-- Aksi --}}
                        <td>
                            {{-- Desktop: Icon buttons --}}
                            <div class="action-buttons-desktop">
                                <a href="{{ route('riwayat.edit', $r->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('riwayat.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus riwayat pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50 hover:border-red-200" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
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
                                    <a href="{{ route('riwayat.edit', $r->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        Edit
                                    </a>
                                    <div class="action-dropdown-divider"></div>
                                    <form action="{{ route('riwayat.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat pelanggaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-dropdown-item action-dropdown-item--delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                                </svg>
                                <h3 class="empty-state-title">Tidak Ada Data</h3>
                                <p class="empty-state-description">Belum ada riwayat pelanggaran yang dicatat.</p>
                                <a href="{{ route('riwayat.create') }}" class="btn btn-primary">Catat Pelanggaran</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($riwayat->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500">
                Menampilkan {{ $riwayat->firstItem() }} - {{ $riwayat->lastItem() }} dari {{ $riwayat->total() }}
            </p>
            {{ $riwayat->links() }}
        </div>
    @endif
</div>
@endsection
