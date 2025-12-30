<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'SIMDIS') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body x-data="sidebar" class="antialiased">
    
    <div class="app-layout">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" 
             :class="{ 'active': open }" 
             @click="close()"
             x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:leave="transition ease-in duration-200"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'open': open }">
            @include('components.sidebar')
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navbar -->
            <header class="navbar">
                @include('components.navbar')
            </header>
            
            <!-- Page Content -->
            <div class="page-content">
                <!-- Flash Messages -->
                @include('components.alerts')
                
                <!-- Page Header -->
                @hasSection('page-header')
                    <div class="page-header flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 class="page-title">@yield('title')</h1>
                            @hasSection('subtitle')
                                <p class="page-subtitle">@yield('subtitle')</p>
                            @endif
                        </div>
                        @hasSection('actions')
                            <div class="page-actions">
                                @yield('actions')
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Main Content -->
                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer class="px-6 py-4 text-center text-sm text-gray-500 border-t border-gray-100">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'SIMDIS') }}. Sistem Informasi Manajemen Disiplin Siswa.</p>
            </footer>
        </main>
    </div>
    
    <!-- Global Confirmation Modal -->
    <div x-data="confirm" x-cloak>
        <div class="modal-backdrop" :class="{ 'active': open }" @keydown.escape.window="hide()">
            <div class="modal" @click.away="hide()">
                <div class="modal-header">
                    <h3 class="modal-title" x-text="title"></h3>
                    <button type="button" class="modal-close" @click="hide()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-gray-600" x-text="message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="hide()" x-text="cancelText"></button>
                    <button type="button" 
                            class="btn"
                            :class="{
                                'btn-danger': variant === 'danger',
                                'btn-warning': variant === 'warning',
                                'btn-primary': variant === 'info'
                            }"
                            @click="confirm()" 
                            x-text="confirmText"></button>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
