@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Selamat datang kembali! Kelola data sekolah Anda.')
@section('page-header', true)

@section('content')
<div class="space-y-6">
    {{-- Welcome Banner --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 overflow-hidden text-white">
        {{-- Decorative elements --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium text-blue-200 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                    System Active
                </div>
                <h2 class="text-xl md:text-2xl font-bold">
                    Halo, {{ auth()->user()->username ?? 'Operator' }}! 👋
                </h2>
                <p class="text-blue-100 text-sm opacity-80 mt-1">
                    Selamat bekerja, data hari ini siap dikelola.
                </p>
            </div>
            
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner">
                <div class="bg-blue-500/20 p-2 rounded-lg text-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                    <span class="block text-xs uppercase tracking-wider text-blue-200">{{ date('F Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Users --}}
        <a href="{{ route('users.index') }}" class="stat-card group">
            <div class="stat-card-icon primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Total User</p>
                <p class="stat-card-value">{{ $totalUser ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Akun Terdaftar</p>
            </div>
        </a>
        
        {{-- Total Siswa --}}
        <a href="{{ route('siswa.index') }}" class="stat-card group">
            <div class="stat-card-icon success">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Total Siswa</p>
                <p class="stat-card-value">{{ $totalSiswa ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Data Pokok</p>
            </div>
        </a>
        
        {{-- Total Pelanggaran --}}
        <a href="{{ route('riwayat.index') }}" class="stat-card group">
            <div class="stat-card-icon danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Pelanggaran</p>
                <p class="stat-card-value">{{ $totalAturan ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Jenis Poin</p>
            </div>
        </a>
        
        {{-- Total Kelas --}}
        <a href="{{ route('kelas.index') }}" class="stat-card group">
            <div class="stat-card-icon warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M17 21v-8.33A2 2 0 0 0 15 10.67H9a2 2 0 0 0-2 2V21"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Rombel</p>
                <p class="stat-card-value">{{ $totalKelas ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Kelas Aktif</p>
            </div>
        </a>
    </div>
    
    {{-- Quick Actions --}}
    <div>
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Aksi Cepat</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Add User --}}
            <a href="{{ route('users.create') }}" class="card flex items-center gap-4 p-4 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="20" x2="20" y1="8" y2="14"/><line x1="23" x2="17" y1="11" y2="11"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Tambah User Baru</h4>
                    <p class="text-sm text-gray-500">Guru, Staff, atau Wali Murid</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-all">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
            
            {{-- Add Siswa --}}
            <a href="{{ route('siswa.create') }}" class="card flex items-center gap-4 p-4 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/><line x1="12" x2="12" y1="22" y2="17"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-emerald-600 transition-colors">Tambah Data Siswa</h4>
                    <p class="text-sm text-gray-500">Input data siswa baru</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
            
            {{-- Search Siswa --}}
            <a href="{{ route('siswa.index') }}" class="card flex items-center gap-4 p-4 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-violet-600 transition-colors">Cari Data Siswa</h4>
                    <p class="text-sm text-gray-500">Lihat pelanggaran & profil</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-violet-500 group-hover:translate-x-1 transition-all">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
            
            {{-- Bulk Import --}}
            <a href="{{ route('siswa.bulk-create') }}" class="card flex items-center gap-4 p-4 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-amber-600 transition-colors">Import Siswa</h4>
                    <p class="text-sm text-gray-500">Upload data Excel/CSV</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-amber-500 group-hover:translate-x-1 transition-all">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
            
            {{-- Log Pelanggaran --}}
            <a href="{{ route('riwayat.index') }}" class="card flex items-center gap-4 p-4 hover:border-rose-200 hover:shadow-lg hover:shadow-rose-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-rose-600 transition-colors">Log Pelanggaran</h4>
                    <p class="text-sm text-gray-500">Lihat riwayat pelanggaran</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-rose-500 group-hover:translate-x-1 transition-all">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
            
            {{-- Audit Log --}}
            <a href="{{ route('audit.activity.index') }}" class="card flex items-center gap-4 p-4 hover:border-slate-200 hover:shadow-lg hover:shadow-slate-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M12 7a5 5 0 1 0 5 5"/><path d="M13 3.055A9 9 0 1 0 20.941 11"/><path d="M22 6H16V0"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-slate-700 transition-colors">Audit Log</h4>
                    <p class="text-sm text-gray-500">Aktivitas sistem</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection
