@extends('layouts.app')

@section('title', 'Dashboard Wali Murid')
@section('page-header', true)

@section('content')
<div class="flex items-center justify-center min-h-[400px]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-blue-600">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Data Anak Belum Terhubung</h2>
        <p class="text-gray-500 mb-6">
            Akun Anda belum terhubung dengan data siswa manapun. 
            Silakan hubungi Operator Sekolah untuk menghubungkan akun Anda dengan data anak Anda.
        </p>
        
        <a href="{{ route('account.edit') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>
            </svg>
            <span>Lihat Profil Saya</span>
        </a>
    </div>
</div>
@endsection
