@extends('layouts.app')

@section('title', 'Tambah Kelas')
@section('subtitle', 'Buat rombongan belajar baru.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Kelas</h3>
        </div>
        <div class="card-body">
            {{-- Jurusan mapping for auto-generate class name --}}
            @php
                $jurusanMap = [];
                foreach($jurusanList ?? [] as $j) {
                    $jurusanMap[$j->id] = $j->kode_jurusan ?? strtoupper(substr($j->nama_jurusan, 0, 3));
                }
            @endphp
            
            <form action="{{ route('kelas.store') }}" method="POST" class="space-y-6"
                  x-data="{ 
                      tingkat: '{{ old('tingkat', '') }}',
                      jurusanId: '{{ old('jurusan_id', '') }}',
                      rombel: '{{ old('rombel', '1') }}',
                      createWali: false,
                      jurusanMap: {{ json_encode($jurusanMap) }},
                      
                      getKodeJurusan() {
                          return this.jurusanMap[this.jurusanId] || '';
                      },
                      generateNamaKelas() {
                          if (!this.tingkat || !this.jurusanId) return '';
                          const kode = this.getKodeJurusan();
                          const rombelNum = this.rombel || '1';
                          return this.tingkat + ' ' + kode + ' ' + rombelNum;
                      },
                      generateWaliUsername() {
                          if (!this.tingkat || !this.jurusanId) return '...';
                          const kode = this.getKodeJurusan().toLowerCase();
                          const tingkat = this.tingkat.toLowerCase();
                          const rombel = this.rombel || '1';
                          return kode + '_' + tingkat + '_' + rombel + '_wali';
                      }
                  }"
            >
                @csrf
                
                {{-- Tingkat Selection --}}
                <div class="form-group">
                    <label class="form-label form-label-required">Tingkat</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="X" x-model="tingkat" class="peer sr-only" {{ old('tingkat') == 'X' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">X</span>
                                <p class="text-sm mt-1">Kelas 10</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="XI" x-model="tingkat" class="peer sr-only" {{ old('tingkat') == 'XI' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">XI</span>
                                <p class="text-sm mt-1">Kelas 11</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="XII" x-model="tingkat" class="peer sr-only" {{ old('tingkat') == 'XII' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">XII</span>
                                <p class="text-sm mt-1">Kelas 12</p>
                            </div>
                        </label>
                    </div>
                    <p class="form-help">Nama kelas akan digenerate otomatis.</p>
                    @error('tingkat')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Jurusan Selection --}}
                <div class="form-group">
                    <label for="jurusan_id" class="form-label form-label-required">Jurusan / Kompetensi</label>
                    <select id="jurusan_id" name="jurusan_id" x-model="jurusanId"
                            class="form-input form-select @error('jurusan_id') error @enderror" required>
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach($jurusanList ?? [] as $j)
                            <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }} ({{ $j->kode_jurusan ?? strtoupper(substr($j->nama_jurusan, 0, 3)) }})
                            </option>
                        @endforeach
                    </select>
                    @error('jurusan_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Rombel Number --}}
                <div class="form-group">
                    <label for="rombel" class="form-label form-label-required">Nomor Rombel</label>
                    <select id="rombel" name="rombel" x-model="rombel"
                            class="form-input form-select @error('rombel') error @enderror" required>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('rombel', '1') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <p class="form-help">Nomor urut rombongan belajar (1, 2, 3...)</p>
                    @error('rombel')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Preview Nama Kelas --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100"
                     x-show="tingkat && jurusanId"
                     x-transition>
                    <label class="form-label text-blue-700">Nama Kelas (Otomatis)</label>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 p-3 bg-white rounded-lg border border-blue-200">
                            <span class="text-xl font-bold text-blue-800" x-text="generateNamaKelas()"></span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                        </svg>
                    </div>
                    <input type="hidden" name="nama_kelas" :value="generateNamaKelas()">
                </div>

                <hr class="border-gray-100">
                
                {{-- Wali Kelas Selection --}}
                <div class="form-group">
                    <label for="wali_kelas_user_id" class="form-label">Wali Kelas (Opsional)</label>
                    <select id="wali_kelas_user_id" name="wali_kelas_user_id" class="form-input form-select @error('wali_kelas_user_id') error @enderror">
                        <option value="">-- Pilih dari Guru yang Ada --</option>
                        @foreach($waliList ?? [] as $w)
                            <option value="{{ $w->id }}" {{ old('wali_kelas_user_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->nama ?? $w->username }} ({{ $w->username }})
                            </option>
                        @endforeach
                    </select>
                    @error('wali_kelas_user_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Create Wali Option --}}
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="create_wali" value="1" x-model="createWali"
                               class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-bold text-gray-700">Buat akun Wali Kelas baru secara otomatis</span>
                            <p class="text-xs text-gray-500">Centang ini jika guru belum terdaftar di sistem. Akun akan dibuatkan oleh sistem.</p>
                        </div>
                    </label>
                    
                    {{-- Preview Wali Account --}}
                    <div x-show="createWali && tingkat && jurusanId" x-transition class="mt-4">
                        <div class="p-4 bg-white rounded-lg border border-indigo-100">
                            <h5 class="text-xs font-bold text-indigo-500 uppercase tracking-wide mb-3">Preview Akun Baru</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-xs text-gray-400">Username</span>
                                    <div class="flex items-center gap-2 bg-gray-50 p-2 rounded border mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <span class="font-mono font-bold text-gray-700 text-sm" x-text="generateWaliUsername()"></span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400">Password Awal</span>
                                    <div class="flex items-center gap-2 bg-gray-50 p-2 rounded border mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        <span class="font-mono font-bold text-rose-500 text-sm">(Auto-generated)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary" :disabled="!tingkat || !jurusanId">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan Data</span>
                    </button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
