@extends('layouts.app')

{{-- Pastikan Anda sudah menyertakan script Tailwind CSS di `layouts.app` atau di sini --}}
@section('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    // Skema warna yang lebih premium (menggunakan Primary: Blue 500, Success: Emerald 500)
                    primary: '#3b82f6', // Blue 500
                    danger: '#ef4444', // Red 500
                    warning: '#f59e0b', // Amber 500
                    success: '#10b981', // Emerald 500
                    info: '#06b6d4', // Cyan 500
                    secondary: '#6b7280', // Gray 500
                },
                screens: {
                    'xs': '375px',
                }
            }
        },
        corePlugins: {
            preflight: false,
        }
    }
</script>

<style>
    /* Styling Dasar dan Kerapatan */
    .student-profile-v4 {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .card-v4 {
        /* shadow-lg yang lebih halus */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03); 
    }
    
    /* Ikon Wrapper untuk Field Data */
    .icon-wrapper-v4 {
        width: 1.75rem; /* Ukuran 28px */
        height: 1.75rem;
        border-radius: 0.375rem; /* rounded-md */
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
        flex-shrink: 0;
    }

    /* Style untuk Garis Timeline V4 yang Rapat dan Rapi */
    .timeline-v4 {
        position: relative;
        padding-left: 28px;
    }

    .timeline-v4:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #d1d5db; 
        left: 0;
        border-radius: 1px;
    }

    .timeline-v4 > div {
        position: relative;
        margin-bottom: 18px;
    }

    .timeline-v4 > div > .timeline-item-v4 {
        background-color: #ffffff;
        border-radius: 0.5rem;
        margin-left: 18px;
        padding: 0.85rem 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08);
        border: 1px solid #f3f4f6;
    }

    .timeline-v4 > div > .timeline-icon-v4 {
        position: absolute;
        width: 22px; 
        height: 22px;
        font-size: 11px;
        line-height: 22px;
        text-align: center;
        border-radius: 50%;
        color: #fff;
        left: -11px;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    }
</style>
@endsection

@section('title', 'Profil Siswa - ' . $siswa->nama_siswa)

@section('content')
<div class="student-profile-v4 p-4 md:p-6 bg-gray-50 min-h-screen">
    
    <div class="flex items-center justify-between mb-5 border-b pb-3">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-graduate text-primary mr-3"></i> Profil Siswa
        </h1>
        <a href="{{ url()->previous() }}" class="text-sm font-medium text-gray-600 hover:text-white transition duration-200 flex items-center px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-primary hover:border-primary shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-5 card-v4 lg:sticky lg:top-6">
                <div class="flex flex-col items-center mb-5">
                    {{-- Avatar --}}
                    <img class="w-24 h-24 rounded-full object-cover mb-4 ring-2 ring-primary/40 p-0.5" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_siswa) }}&background=3b82f6&color=ffffff&size=100" 
                         alt="Avatar Siswa">
                    
                    <h2 class="text-xl font-bold text-gray-900 mb-1 text-center">{{ $siswa->nama_siswa }}</h2>

                    {{-- Badge Kelas & Jurusan --}}
                    <div class="flex flex-wrap justify-center gap-2 text-center">
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-info/10 text-info-700 border border-info/20">{{ $siswa->kelas->nama_kelas }}</span>
                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-secondary/10 text-secondary-700 border border-secondary/20">{{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                    </div>
                </div>

                <div class="space-y-3 pt-4 border-t border-gray-100">
                    {{-- NISN --}}
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-500">NISN</span>
                        <span class="font-semibold text-gray-800">{{ $siswa->nisn }}</span>
                    </div>
                    
                    {{-- Total Poin Pelanggaran (Logika warna disesuaikan) --}}
                    @php
                        $poinClass = 'success';
                        if ($totalPoin >= 301) { $poinClass = 'danger'; } 
                        elseif ($totalPoin >= 100) { $poinClass = 'warning'; }
                        
                        $badgeBg = $poinClass . '-100';
                        $badgeText = $poinClass . '-700';
                        if ($poinClass == 'danger') { $badgeBg = 'danger'; $badgeText = 'white'; }
                        if ($poinClass == 'warning') { $badgeBg = 'warning/20'; $badgeText = 'warning-800'; }
                        if ($poinClass == 'success') { $badgeBg = 'success/20'; $badgeText = 'success-700'; }
                    @endphp
                    
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-500">Total Poin Pelanggaran</span>
                        <span class="px-3 py-0.5 text-xs font-bold rounded-full bg-{{ $badgeBg }} text-{{ $badgeText }}">
                            {{ $totalPoin }} Poin
                        </span>
                    </div>
                    
                    {{-- Total Pelanggaran --}}
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-500">Jumlah Pelanggaran</span>
                        <span class="px-3 py-0.5 text-xs font-bold rounded-full bg-secondary/10 text-secondary-700">
                            {{ $siswa->riwayatPelanggaran->count() }} Kali
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 space-y-6">
            
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 card-v4 p-0">
                <div class="p-4 bg-primary/5 rounded-t-xl border-b border-primary/20 flex items-center">
                    <h3 class="text-lg font-bold text-primary flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i> Informasi Akademik
                    </h3>
                </div>
                
                <div class="p-4 md:p-6 grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8 text-sm">
                    @php $fields = [
                        'Kelas' => ['icon' => 'fas fa-chalkboard', 'value' => $siswa->kelas->nama_kelas],
                        'Jurusan' => ['icon' => 'fas fa-book-reader', 'value' => $siswa->kelas->jurusan->nama_jurusan],
                        'Wali Kelas' => ['icon' => 'fas fa-user-tie', 'value' => $siswa->kelas->waliKelas->username ?? '-'],
                        'Kepala Program' => ['icon' => 'fas fa-user-cog', 'value' => $siswa->kelas->jurusan->kaprodi->username ?? '-']
                    ]; @endphp
                    
                    @foreach($fields as $label => $data)
                    <div class="flex items-start">
                        {{-- Icon Wrapper Premium (Warna Primary) --}}
                        <div class="icon-wrapper-v4 bg-primary/10 text-primary">
                            <i class="{{ $data['icon'] }} text-base"></i>
                        </div>
                        <div>
                            <strong class="text-gray-500 block text-xs uppercase tracking-wider">{{ $label }}</strong>
                            <p class="text-gray-800 font-semibold leading-tight mt-0.5">{{ $data['value'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 card-v4 p-0">
                <div class="p-4 bg-success/5 rounded-t-xl border-b border-success/20 flex items-center">
                    <h3 class="text-lg font-bold text-success flex items-center">
                        <i class="fas fa-user-friends mr-2"></i> Informasi Wali Murid
                    </h3>
                </div>
                
                <div class="p-4 md:p-6 grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8 text-sm">
                    @php $fields = [
                        'Nama Wali Murid' => ['icon' => 'fas fa-user-shield', 'value' => $siswa->waliMurid->nama ?? '-'],
                        'Nomor HP' => ['icon' => 'fas fa-mobile-alt', 'value' => $siswa->nomor_hp_wali_murid ?? '-'],
                    ]; @endphp
                    
                    @foreach($fields as $label => $data)
                    <div class="flex items-start">
                        {{-- Icon Wrapper Premium (Warna Success) --}}
                        <div class="icon-wrapper-v4 bg-success/10 text-success">
                            <i class="{{ $data['icon'] }} text-base"></i>
                        </div>
                        <div>
                            <strong class="text-gray-500 block text-xs uppercase tracking-wider">{{ $label }}</strong>
                            <p class="text-gray-800 font-semibold leading-tight mt-0.5">{{ $data['value'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-0 card-v4">
                <div class="p-4 bg-gray-50 rounded-t-xl border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-danger flex items-center">
                        <i class="fas fa-history mr-2"></i> Riwayat Pelanggaran
                    </h3>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-danger text-white shadow-md">
                        {{ $siswa->riwayatPelanggaran->count() }} Pelanggaran
                    </span>
                </div>
                
                <div class="p-5">
                    @if($siswa->riwayatPelanggaran->count() > 0)
                        <div class="timeline-v4">
                            @foreach($siswa->riwayatPelanggaran->sortByDesc('tanggal_kejadian') as $riwayat)
                            <div>
                                {{-- Icon Timeline (Merah) --}}
                                <div class="timeline-icon-v4 bg-danger-500 shadow-md">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                
                                <div class="timeline-item-v4">
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 pb-1 border-b border-gray-100">
                                        {{-- Header Pelanggaran --}}
                                        <h4 class="text-sm font-bold text-gray-800 leading-tight mb-0.5 sm:mb-0">
                                            {{ $riwayat->jenisPelanggaran->nama_pelanggaran }}
                                            
                                            {{-- Logika Poin --}}
                                            @php
                                                $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($riwayat);
                                                $poinValue = $poinInfo['matched'] && $poinInfo['poin'] > 0 ? $poinInfo['poin'] : 0;
                                                $poinBadgeClass = $poinValue > 0 ? 'bg-danger text-white' : 'bg-secondary/10 text-secondary-700';
                                            @endphp
                                            
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded {{ $poinBadgeClass }} whitespace-nowrap"
                                                  title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($riwayat) }}">
                                                +{{ $poinValue }} Poin
                                            </span>
                                        </h4>
                                        
                                        {{-- Waktu Kejadian --}}
                                        <span class="text-xs text-gray-500 flex-shrink-0">
                                            <i class="fas fa-clock mr-1"></i> 
                                            {{ \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-1 text-xs">
                                        <p>
                                            <strong class="text-gray-600">Kategori:</strong> 
                                            <span class="px-2 py-0.5 font-medium rounded bg-gray-100 text-gray-700">
                                                {{ $riwayat->jenisPelanggaran->kategoriPelanggaran->nama_kategori }}
                                            </span>
                                        </p>
                                        @if($riwayat->keterangan)
                                        <p>
                                            <strong class="text-gray-600">Keterangan:</strong> 
                                            <span class="text-gray-700">{{ $riwayat->keterangan }}</span>
                                        </p>
                                        @endif
                                        <p class="pt-1.5 text-xs text-gray-400 border-t mt-1.5">
                                            <i class="fas fa-user mr-1"></i> Dicatat oleh: {{ $riwayat->guruPencatat->nama ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            
                            {{-- Titik Akhir Timeline (Hijau) --}}
                            <div>
                                <div class="timeline-icon-v4 bg-success-500 shadow-md">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 text-center bg-success-50 border border-success-200 rounded-lg text-success-700 text-sm">
                            <i class="fas fa-check-circle mr-1"></i> Tidak ada riwayat pelanggaran. Catatan bersih!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
