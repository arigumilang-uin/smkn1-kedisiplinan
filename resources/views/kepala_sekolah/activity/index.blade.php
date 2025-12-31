@extends('layouts.app')

@section('title', 'Audit & Log Sistem')
@section('subtitle', 'Pantau integritas data dan riwayat aktivitas pengguna sistem.')
@section('page-header', true)

@section('actions')
    @if(!isset($tab) || $tab === 'activity')
    <a href="{{ route('audit.activity.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-success">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <span>Export CSV</span>
    </a>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    {{-- Tabs --}}
    <div class="card">
        <div class="p-4 flex flex-wrap gap-2">
            <a href="{{ route('audit.activity.index', ['tab' => 'activity']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition-all {{ (!isset($tab) || $tab === 'activity') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                Log Aktivitas
            </a>
            
            <a href="{{ route('audit.activity.index', ['tab' => 'last-login']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition-all {{ (isset($tab) && $tab === 'last-login') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Last Login
            </a>
            
            <a href="{{ route('audit.activity.index', ['tab' => 'status']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition-all {{ (isset($tab) && $tab === 'status') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
                Status Akun
            </a>
        </div>
    </div>

    {{-- Tab Content --}}
    @if(!isset($tab) || $tab === 'activity')
        @include('kepala_sekolah.activity.tabs.activity')
    @elseif($tab === 'last-login')
        @include('kepala_sekolah.activity.tabs.last-login')
    @elseif($tab === 'status')
        @include('kepala_sekolah.activity.tabs.status')
    @endif

    {{-- Info Box --}}
    <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
            </div>
            <p class="text-xs text-indigo-700/80">
                Log sistem mencatat setiap perubahan data penting untuk keamanan. Gunakan data ini untuk meninjau validasi aktivitas atau mendeteksi akses yang tidak sah secara berkala.
            </p>
        </div>
    </div>
</div>
@endsection
