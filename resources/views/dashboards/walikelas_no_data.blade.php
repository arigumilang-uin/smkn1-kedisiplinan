@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-header', true)

@section('content')
<div class="flex items-center justify-center min-h-[400px]">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 mx-auto bg-amber-100 rounded-full flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Kelas Belum Ditetapkan</h2>
        <p class="text-gray-500 mb-6">
            Anda belum ditetapkan sebagai wali kelas untuk kelas manapun. 
            Silakan hubungi Operator Sekolah untuk mengatur kelas yang Anda ampu.
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
