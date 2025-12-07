@extends('layouts.app')

@section('title', 'Audit & Log')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-dark font-weight-bold">
                    <i class="fas fa-history mr-2"></i> Audit & Log
                </h3>
                <div>
                    @if(!isset($tab) || $tab === 'activity')
                    <a href="{{ route('audit.activity.export-csv', request()->query()) }}" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-download mr-1"></i> Export CSV
                    </a>
                    @endif
                    <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ (!isset($tab) || $tab === 'activity') ? 'active' : '' }}" 
                       href="{{ route('audit.activity.index', ['tab' => 'activity']) }}">
                        <i class="fas fa-list mr-1"></i> Log Aktivitas Sistem
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ (isset($tab) && $tab === 'last-login') ? 'active' : '' }}" 
                       href="{{ route('audit.activity.index', ['tab' => 'last-login']) }}">
                        <i class="fas fa-sign-in-alt mr-1"></i> Last Login Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ (isset($tab) && $tab === 'status') ? 'active' : '' }}" 
                       href="{{ route('audit.activity.index', ['tab' => 'status']) }}">
                        <i class="fas fa-user-check mr-1"></i> Status Akun
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    @if(!isset($tab) || $tab === 'activity')
        @include('kepala_sekolah.activity.tabs.activity')
    @elseif($tab === 'last-login')
        @include('kepala_sekolah.activity.tabs.last-login')
    @elseif($tab === 'status')
        @include('kepala_sekolah.activity.tabs.status')
    @endif

</div>
@endsection
