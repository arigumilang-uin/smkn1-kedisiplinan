<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Sistem Kedisiplinan') | SMKN 1 Siak</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Theme style (AdminLTE) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  @yield('styles')

  <style>
      /* Perbaikan Kosmetik Kecil */
      .main-sidebar { min-height: 100vh; }
      .brand-link .brand-image { float: left; line-height: .8; margin-left: .8rem; margin-right: .5rem; margin-top: -3px; max-height: 33px; width: auto; }
      .nav-sidebar .nav-header { padding: 0.5rem 1rem; font-size: 0.8rem; color: #adb5bd; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
      
      /* Perbaikan warna teks user di sidebar agar terlihat jelas */
      .user-panel .info { overflow: visible; }
      .user-panel .info a { white-space: normal; }
  </style>
</head>

<!-- 
  PERUBAHAN PENTING DI BODY CLASS:
  1. layout-fixed: Sidebar diam saat discroll.
  2. layout-navbar-fixed: Navbar atas diam saat discroll.
  3. layout-footer-fixed: Footer diam di bawah layar.
-->
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link font-weight-bold text-secondary">Tahun Ajaran 2025/2026</span>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <!-- Tombol Logout di Navbar Kanan -->
      <li class="nav-item">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah anda yakin ingin keluar?')">
                <i class="fas fa-sign-out-alt mr-1"></i> Logout
            </button>
        </form>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    
    <!-- BRAND LOGO -->
    <a href="#" class="brand-link">
      <!-- Ganti src dengan URL logo sekolah Anda nanti. Sementara pakai Logo AdminLTE -->
      <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="SMK Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">SIMDIS <strong>SMK</strong></span>
    </a>

    <div class="sidebar">
      
      <!-- USER PANEL (PROFIL SINGKAT) -->
      @auth
      <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center border-bottom-0">
        <div class="image">
          <!-- Avatar Generator berdasarkan Inisial Nama -->
          <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama) }}&background=random&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block font-weight-bold text-white">{{ Str::limit(Auth::user()->nama, 18) }}</a>
          <!-- PERBAIKAN: Menggunakan badge-info agar teks terlihat jelas di background gelap -->
          <span class="badge badge-info mt-1">{{ Auth::user()->role->nama_role }}</span>
        </div>
      </div>
      @endauth

      <!-- SIDEBAR MENU -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
            @php $role = Auth::user()->role->nama_role; @endphp

            <!-- ================================= -->
            <!-- MENU DASHBOARD -->
            <!-- ================================= -->
            <li class="nav-item">
                @php
                    $dashboardRoute = '#';
                    if($role == 'Operator Sekolah' || $role == 'Waka Kesiswaan') $dashboardRoute = route('dashboard.admin');
                    elseif($role == 'Kepala Sekolah') $dashboardRoute = route('dashboard.kepsek');
                    elseif($role == 'Wali Kelas') $dashboardRoute = route('dashboard.walikelas');
                    elseif($role == 'Kaprodi') $dashboardRoute = route('dashboard.kaprodi');
                    elseif($role == 'Orang Tua') $dashboardRoute = route('dashboard.ortu');
                @endphp
                <a href="{{ $dashboardRoute }}" class="nav-link {{ Request::is('dashboard*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            <!-- ================================= -->
            <!-- MENU OPERASIONAL (Guru/Waka/Wali) -->
            <!-- ================================= -->
            @if(in_array($role, ['Guru', 'Wali Kelas', 'Waka Kesiswaan', 'Kaprodi']))
            <li class="nav-header">OPERASIONAL</li>
            <li class="nav-item">
                <a href="{{ route('pelanggaran.create') }}" class="nav-link {{ Request::is('pelanggaran*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-signature text-warning"></i>
                    <p>Catat Pelanggaran</p>
                </a>
            </li>
            @endif

            <!-- ================================= -->
            <!-- MENU DATA (Waka/Operator/Wali/Kaprodi) -->
            <!-- ================================= -->
            @if(in_array($role, ['Operator Sekolah', 'Waka Kesiswaan', 'Wali Kelas', 'Kaprodi']))
            <li class="nav-header">MONITORING DATA</li>
            
            <li class="nav-item">
                <a href="{{ route('siswa.index') }}" class="nav-link {{ Request::is('siswa*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-graduate"></i>
                    <p>
                        @if($role == 'Wali Kelas') Data Siswa Kelas @else Data Siswa @endif
                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('riwayat.index') }}" class="nav-link {{ Request::is('riwayat-pelanggaran*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-history"></i>
                    <p>Riwayat Pelanggaran</p>
                </a>
            </li>
            @endif

            <!-- ================================= -->
            <!-- MENU ADMIN (Operator Only) -->
            <!-- ================================= -->
            @if($role == 'Operator Sekolah')
            <li class="nav-header">ADMINISTRASI</li>
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users-cog"></i>
                    <p>Manajemen User</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('jenis-pelanggaran.index') }}" class="nav-link {{ Request::is('jenis-pelanggaran*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-gavel"></i>
                    <p>Aturan & Poin</p>
                </a>
            </li>
            @endif

            <!-- ================================= -->
            <!-- MENU PENGATURAN (Semua User) -->
            <!-- ================================= -->
            <li class="nav-header">PENGATURAN</li>
            <li class="nav-item">
                <!-- Link ini bisa diarahkan ke fitur ganti password nanti -->
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-cog"></i>
                    <p>Akun Saya</p>
                </a>
            </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark" style="font-weight: 700;">@yield('title')</h1>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <!-- Ini tempat Alert Global jika ada -->
        
        @yield('content')
      </div>
    </div>
  </div>

  <!-- Main Footer (FIXED BOTTOM) -->
  <footer class="main-footer text-sm">
    <div class="float-right d-none d-sm-inline">
      <b>Sistem Informasi Kedisiplinan</b>
    </div>
    <strong>Copyright &copy; 2025 <a href="#">SMK Negeri 1 Lubuk Dalam</a>.</strong>
  </footer>

</div>
<!-- ./wrapper -->

<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@stack('scripts')

</body>
</html>