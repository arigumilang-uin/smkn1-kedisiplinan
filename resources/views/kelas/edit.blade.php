@extends('layouts.app')

@section('title', 'Edit Kelas')
@section('subtitle', 'Perbarui data kelas.')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Kelas</h3>
        </div>
        <div class="card-body">
            {{-- Jurusan mapping for auto-generate class name --}}
            @php
                $jurusanMap = [];
                foreach($jurusanList ?? [] as $j) {
                    $jurusanMap[$j->id] = $j->kode_jurusan ?? strtoupper(substr($j->nama_jurusan, 0, 3));
                }
                // Extract rombel from nama_kelas (last number)
                preg_match('/(\d+)$/', $kelas->nama_kelas ?? '', $matches);
                $currentRombel = $matches[1] ?? '1';
            @endphp
            
            <form action="{{ route('kelas.update', $kelas->id) }}" method="POST" class="space-y-6"
                  x-data="{ 
                      tingkat: '{{ old('tingkat', $kelas->tingkat ?? 'X') }}',
                      jurusanId: '{{ old('jurusan_id', $kelas->jurusan_id) }}',
                      rombel: '{{ old('rombel', $currentRombel) }}',
                      jurusanMap: {{ json_encode($jurusanMap) }},
                      
                      getKodeJurusan() {
                          return this.jurusanMap[this.jurusanId] || '';
                      },
                      generateNamaKelas() {
                          if (!this.tingkat || !this.jurusanId) return '';
                          const kode = this.getKodeJurusan();
                          const rombelNum = this.rombel || '1';
                          return this.tingkat + ' ' + kode + ' ' + rombelNum;
                      }
                  }"
            >
                @csrf
                @method('PUT')
                
                {{-- Tingkat Selection --}}
                <div class="form-group">
                    <label class="form-label form-label-required">Tingkat</label>
                    <div class="grid grid-cols-3 gap-3">
                        @php $currentTingkat = old('tingkat', $kelas->tingkat ?? 'X'); @endphp
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="X" x-model="tingkat" class="peer sr-only" {{ $currentTingkat == 'X' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">X</span>
                                <p class="text-sm mt-1">Kelas 10</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="XI" x-model="tingkat" class="peer sr-only" {{ $currentTingkat == 'XI' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">XI</span>
                                <p class="text-sm mt-1">Kelas 11</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="tingkat" value="XII" x-model="tingkat" class="peer sr-only" {{ $currentTingkat == 'XII' ? 'checked' : '' }}>
                            <div class="p-4 text-center border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:bg-gray-50">
                                <span class="text-2xl font-bold">XII</span>
                                <p class="text-sm mt-1">Kelas 12</p>
                            </div>
                        </label>
                    </div>
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
                            <option value="{{ $j->id }}" {{ old('jurusan_id', $kelas->jurusan_id) == $j->id ? 'selected' : '' }}>
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
                            <option value="{{ $i }}" {{ old('rombel', $currentRombel) == $i ? 'selected' : '' }}>{{ $i }}</option>
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
                    <label for="wali_kelas_user_id" class="form-label">Wali Kelas</label>
                    <select id="wali_kelas_user_id" name="wali_kelas_user_id" class="form-input form-select @error('wali_kelas_user_id') error @enderror">
                        <option value="">-- Belum ditentukan --</option>
                        @foreach($waliList ?? [] as $w)
                            <option value="{{ $w->id }}" {{ old('wali_kelas_user_id', $kelas->wali_kelas_user_id) == $w->id ? 'selected' : '' }}>
                                {{ $w->nama ?? $w->username }} ({{ $w->username }})
                            </option>
                        @endforeach
                    </select>
                    <p class="form-help">Guru yang ditugaskan sebagai wali kelas ini.</p>
                    @error('wali_kelas_user_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Kelas Info --}}
                <div class="p-4 bg-gray-50 rounded-xl">
                    <h4 class="font-semibold text-gray-700 mb-3">Info Kelas</h4>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Jumlah Siswa</dt>
                            <dd class="font-semibold text-lg text-blue-600">{{ $kelas->siswa->count() ?? 0 }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Nama Saat Ini</dt>
                            <dd class="font-semibold">{{ $kelas->nama_kelas }}</dd>
                        </div>
                    </dl>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary" :disabled="!tingkat || !jurusanId">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
