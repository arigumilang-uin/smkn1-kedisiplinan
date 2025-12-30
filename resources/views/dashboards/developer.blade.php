@extends('layouts.app')

@section('title', 'Developer Console')
@section('subtitle', 'Development tools and debugging.')
@section('page-header', true)

@section('content')
<div class="space-y-6">
    {{-- Warning Banner --}}
    @if($isProduction)
        <div class="alert alert-danger">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
            </svg>
            <div class="alert-content">
                <p class="alert-title">⚠️ Production Environment</p>
                <p class="alert-message">You are accessing the developer console in a production environment. Be careful!</p>
            </div>
        </div>
    @endif
    
    {{-- System Info --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="stat-card">
            <div class="stat-card-icon primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Laravel Version</p>
                <p class="stat-card-value text-xl">{{ app()->version() }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card-icon warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2"/><path d="M19 11h2m-1 -1v2"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">PHP Version</p>
                <p class="stat-card-value text-xl">{{ phpversion() }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card-icon {{ $isProduction ? 'danger' : 'success' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Environment</p>
                <p class="stat-card-value text-xl">{{ app()->environment() }}</p>
            </div>
        </div>
    </div>
    
    {{-- Role Switcher --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Role Switcher
            </h3>
        </div>
        <div class="card-body">
            <p class="text-gray-600 mb-4">Switch between roles to test different dashboard views.</p>
            
            @php
                $roles = \App\Models\Role::all();
                $currentOverride = session('developer_role_override');
            @endphp
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($roles as $role)
                    <form action="{{ route('developer.switch-role') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role_id" value="{{ $role->id }}">
                        <button type="submit" class="w-full btn {{ $currentOverride == $role->id ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $role->nama_role }}
                        </button>
                    </form>
                @endforeach
            </div>
            
            @if($currentOverride)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form action="{{ route('developer.reset-role') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>
                            </svg>
                            <span>Reset to Developer</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Quick Links --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Tools</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('audit.activity.index') }}" class="card flex items-center gap-4 p-4 hover:border-blue-200 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M12 7a5 5 0 1 0 5 5"/><path d="M13 3.055A9 9 0 1 0 20.941 11"/></svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Audit Log</h4>
                        <p class="text-sm text-gray-500">View system activity</p>
                    </div>
                </a>
                
                <a href="{{ route('users.index') }}" class="card flex items-center gap-4 p-4 hover:border-emerald-200 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Manage Users</h4>
                        <p class="text-sm text-gray-500">User administration</p>
                    </div>
                </a>
                
                <a href="{{ route('frequency-rules.index') }}" class="card flex items-center gap-4 p-4 hover:border-violet-200 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 15-9-9-9 9"/><path d="M3 21h18"/></svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Rules Engine</h4>
                        <p class="text-sm text-gray-500">Configure rules</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
