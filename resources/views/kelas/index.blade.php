@extends('layouts.app')

@section('title', 'Manajemen Kelas')
@section('subtitle', 'Kelola data rombongan belajar sekolah.')
@section('page-header', true)

@section('actions')
    @can('create', App\Models\Kelas::class)
    <a href="{{ route('kelas.create') }}" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        <span>Tambah Kelas</span>
    </a>
    @endcan
@endsection

@section('content')
<div class="space-y-6">
    {{-- Wali Created Info --}}
    @if(session('wali_created'))
        @php $w = session('wali_created'); @endphp
        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
            <h4 class="text-indigo-700 font-bold flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Akun Wali Kelas Baru Dibuat
            </h4>
            <div class="flex flex-wrap gap-4">
                <div class="bg-white p-3 rounded-lg border border-indigo-100 flex-1 min-w-[150px]">
                    <span class="text-xs text-indigo-400 uppercase font-bold">Username</span>
                    <div class="font-mono text-indigo-900 font-bold text-lg">{{ $w['username'] }}</div>
                </div>
                <div class="bg-white p-3 rounded-lg border border-indigo-100 flex-1 min-w-[150px]">
                    <span class="text-xs text-indigo-400 uppercase font-bold">Password</span>
                    <div class="font-mono text-rose-600 font-bold text-lg">{{ $w['password'] }}</div>
                </div>
            </div>
            <p class="text-xs text-indigo-500 mt-3 italic">* Harap simpan kredensial ini.</p>
        </div>
    @endif

    {{-- Stats --}}
    <div class="flex justify-between items-center">
        <span class="text-sm text-gray-500">
            Total: <b class="text-blue-600">{{ method_exists($kelasList, 'total') ? $kelasList->total() : $kelasList->count() }}</b> kelas
        </span>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>Nama Kelas</th>
                    <th class="">Jurusan</th>
                    <th class="">Wali Kelas</th>
                    <th class="text-center">Jumlah Siswa</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelasList ?? [] as $index => $k)
                    <tr>
                        <td class="text-gray-500">{{ $loop->iteration }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-100 to-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm shadow-sm border border-orange-100">
                                    {{ substr($k->nama_kelas, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">{{ $k->nama_kelas }}</div>
                                    <div class="text-xs text-gray-400 font-mono">ID: {{ $k->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class=""><span class="badge badge-primary">{{ $k->jurusan->nama_jurusan ?? '-' }}</span></td>
                        <td class="">
                            @if($k->waliKelas)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold border border-blue-100">
                                        {{ strtoupper(substr($k->waliKelas->username, 0, 1)) }}
                                    </div>
                                    <span class="text-gray-600">{{ $k->waliKelas->username }}</span>
                                </div>
                            @else
                                <span class="text-gray-300 italic text-sm">Belum ditentukan</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $k->siswa_count ?? $k->siswa()->count() }}</span>
                        </td>
                        <td>
                            {{-- Desktop: Icon buttons --}}
                            <div class="action-buttons-desktop">
                                <a href="{{ route('kelas.show', $k->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @can('update', $k)
                                <a href="{{ route('kelas.edit', $k->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </a>
                                @endcan
                                @can('delete', $k)
                                <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                            
                            {{-- Mobile: Dropdown --}}
                            <div class="action-dropdown-mobile" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="action-dropdown-trigger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="action-dropdown-menu">
                                    <a href="{{ route('kelas.show', $k->id) }}" class="action-dropdown-item action-dropdown-item--view">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Detail
                                    </a>
                                    @can('update', $k)
                                    <a href="{{ route('kelas.edit', $k->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        Edit
                                    </a>
                                    @endcan
                                    @can('delete', $k)
                                    <div class="action-dropdown-divider"></div>
                                    <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus kelas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-dropdown-item action-dropdown-item--delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <h3 class="empty-state-title">Tidak Ada Data Kelas</h3>
                                <p class="empty-state-description">Belum ada kelas yang terdaftar.</p>
                                @can('create', App\Models\Kelas::class)
                                <a href="{{ route('kelas.create') }}" class="btn btn-primary">Tambah Kelas</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if(method_exists($kelasList, 'links'))
        <div class="flex justify-center">
            {{ $kelasList->links() }}
        </div>
    @endif
</div>
@endsection

