@extends('layouts.app')

@section('title', 'Edit Aturan')
@section('subtitle', $jenisPelanggaran->nama_pelanggaran ?? 'Edit Jenis Pelanggaran')
@section('page-header', true)

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Jenis Pelanggaran</h3>
        </div>
        <div class="card-body">
            {{-- Info Box --}}
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 mb-6">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500 shrink-0 mt-0.5">
                        <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                    </svg>
                    <div class="text-sm text-blue-700">
                        <strong>Catatan:</strong> Untuk mengatur <strong>Poin, Sanksi, Trigger Surat, dan Pembina</strong>, silakan gunakan halaman <strong>"Kelola Rules"</strong>.
                        <a href="{{ route('frequency-rules.show', $jenisPelanggaran->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold underline block mt-2">
                            → Lanjut ke Halaman Kelola Rules
                        </a>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('jenis-pelanggaran.update', $jenisPelanggaran->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                {{-- Nama Pelanggaran --}}
                <div class="form-group">
                    <label for="nama_pelanggaran" class="form-label form-label-required">Nama Pelanggaran</label>
                    <input type="text" id="nama_pelanggaran" name="nama_pelanggaran" 
                           value="{{ old('nama_pelanggaran', $jenisPelanggaran->nama_pelanggaran) }}"
                           class="form-input @error('nama_pelanggaran') error @enderror" 
                           required autofocus>
                    @error('nama_pelanggaran')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Kategori Pelanggaran --}}
                <div class="form-group">
                    <label for="kategori_id" class="form-label form-label-required">Kategori Pelanggaran</label>
                    <select id="kategori_id" name="kategori_id" 
                            class="form-input form-select @error('kategori_id') error @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategori ?? [] as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id', $jenisPelanggaran->kategori_id) == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Filter Category --}}
                <div class="form-group">
                    <label for="filter_category" class="form-label">Filter Kategori <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <select id="filter_category" name="filter_category" 
                            class="form-input form-select @error('filter_category') error @enderror">
                        <option value="">-- Tidak ada filter --</option>
                        <option value="atribut" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'atribut' ? 'selected' : '' }}>Atribut/Seragam</option>
                        <option value="absensi" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'absensi' ? 'selected' : '' }}>Absensi/Kehadiran</option>
                        <option value="kerapian" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'kerapian' ? 'selected' : '' }}>Kerapian/Kebersihan</option>
                        <option value="ibadah" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'ibadah' ? 'selected' : '' }}>Ibadah/Agama</option>
                        <option value="berat" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'berat' ? 'selected' : '' }}>Berat/Kejahatan</option>
                    </select>
                    <p class="form-help">Filter untuk memudahkan pencarian saat catat pelanggaran.</p>
                    @error('filter_category')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Keywords --}}
                <div class="form-group">
                    <label for="keywords" class="form-label">Alias / Keywords <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea id="keywords" name="keywords" rows="3"
                              class="form-input form-textarea @error('keywords') error @enderror" 
                              placeholder="Contoh: Rambut panjang, Rambut gondrong, Rambut dicat">{{ old('keywords', $jenisPelanggaran->keywords) }}</textarea>
                    <p class="form-help">Kata kunci alternatif untuk memudahkan pencarian.</p>
                    @error('keywords')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Current Status --}}
                <div class="p-4 bg-gray-50 rounded-xl">
                    <h4 class="font-semibold text-gray-700 mb-3">Status Saat Ini</h4>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Poin Saat Ini</dt>
                            <dd class="font-bold text-lg text-amber-600">{{ $jenisPelanggaran->poin ?? 0 }} Poin</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd>
                                @if($jenisPelanggaran->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-neutral">Nonaktif</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('frequency-rules.show', $jenisPelanggaran->id) }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Kelola Rules</span>
                    </a>
                    <a href="{{ route('frequency-rules.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
