@extends('layouts.app')

@section('title', 'Tambah Siswa')
@section('subtitle', 'Tambahkan data siswa baru ke sistem.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Siswa</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('siswa.store') }}" method="POST" class="space-y-6">
                @csrf
                
                {{-- Data Siswa --}}
                <div class="form-group">
                    <label for="nisn" class="form-label form-label-required">NISN</label>
                    <input 
                        type="text" 
                        id="nisn" 
                        name="nisn" 
                        value="{{ old('nisn') }}"
                        class="form-input @error('nisn') error @enderror" 
                        placeholder="10 digit NISN"
                        maxlength="20"
                        required
                    >
                    <p class="form-help">NISN harus unik untuk setiap siswa.</p>
                    @error('nisn')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="nama_siswa" class="form-label form-label-required">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="nama_siswa" 
                        name="nama_siswa" 
                        value="{{ old('nama_siswa') }}"
                        class="form-input @error('nama_siswa') error @enderror" 
                        placeholder="Nama lengkap siswa"
                        required
                    >
                    @error('nama_siswa')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="kelas_id" class="form-label form-label-required">Kelas</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select @error('kelas_id') error @enderror" required>
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Data Wali Murid --}}
                <div class="border-t border-gray-100 pt-6">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Data Wali Murid
                    </h4>
                    
                    <div class="form-group">
                        <label for="nomor_hp_wali_murid" class="form-label">No. HP Wali Murid</label>
                        <input 
                            type="text" 
                            id="nomor_hp_wali_murid" 
                            name="nomor_hp_wali_murid" 
                            value="{{ old('nomor_hp_wali_murid') }}"
                            class="form-input @error('nomor_hp_wali_murid') error @enderror" 
                            placeholder="Contoh: 08123456789"
                        >
                        <p class="form-help">Digunakan untuk menghubungi wali murid via WhatsApp.</p>
                        @error('nomor_hp_wali_murid')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="wali_murid_id" class="form-label">Hubungkan ke Akun Wali Murid</label>
                        <select id="wali_murid_id" name="wali_murid_id" class="form-input form-select @error('wali_murid_id') error @enderror">
                            <option value="">-- Tidak ada / Buat baru --</option>
                            @foreach($waliMurid ?? [] as $wm)
                                <option value="{{ $wm->id }}" {{ old('wali_murid_id') == $wm->id ? 'selected' : '' }}>
                                    {{ $wm->username }}
                                </option>
                            @endforeach
                        </select>
                        <p class="form-help">Pilih akun wali murid yang sudah ada, atau centang opsi di bawah untuk membuat otomatis.</p>
                        @error('wali_murid_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-blue-50 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                            <input 
                                type="checkbox" 
                                name="create_wali" 
                                value="1" 
                                {{ old('create_wali') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <div>
                                <span class="text-sm font-medium text-blue-800">Buat akun wali murid secara otomatis</span>
                                <p class="text-xs text-blue-600">Sistem akan membuat akun wali murid dengan username berdasarkan NISN.</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                        </svg>
                        <span>Simpan Data Siswa</span>
                    </button>
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
