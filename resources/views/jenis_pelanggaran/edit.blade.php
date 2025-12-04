@extends('layouts.app')

@section('title', 'Edit Jenis Pelanggaran')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/jenis_pelanggaran/edit.css') }}">
@endsection

@section('content')
<div class="form-wrapper">
    <div class="form-section">
        <h5><i class="fas fa-edit mr-2 text-warning"></i> Edit Jenis Pelanggaran</h5>
        
        <form action="{{ route('jenis-pelanggaran.update', $jenisPelanggaran->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group required">
                <label for="nama_pelanggaran">Nama Pelanggaran</label>
                <input type="text" id="nama_pelanggaran" name="nama_pelanggaran" class="form-control @error('nama_pelanggaran') is-invalid @enderror" 
                       placeholder="Misal: Tidur di kelas" required value="{{ old('nama_pelanggaran', $jenisPelanggaran->nama_pelanggaran) }}">
                @error('nama_pelanggaran') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group required">
                <label for="kategori_id">Kategori Pelanggaran</label>
                <select id="kategori_id" name="kategori_id" class="form-control @error('kategori_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->id }}" {{ old('kategori_id', $jenisPelanggaran->kategori_id) == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                @error('kategori_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group required">
                <label for="poin">Bobot Poin</label>
                <input type="number" id="poin" name="poin" class="form-control @error('poin') is-invalid @enderror" 
                       placeholder="Misal: 5" min="0" required value="{{ old('poin', $jenisPelanggaran->poin) }}">
                <small class="text-muted">Semakin tinggi angka = semakin berat pelanggaran</small>
                @error('poin') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="filter_category">Filter Kategori (Opsional)</label>
                <select id="filter_category" name="filter_category" class="form-control @error('filter_category') is-invalid @enderror">
                    <option value="">-- Tidak ada filter --</option>
                    <option value="atribut" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'atribut' ? 'selected' : '' }}>Atribut/Seragam</option>
                    <option value="absensi" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'absensi' ? 'selected' : '' }}>Absensi/Kehadiran</option>
                    <option value="kerapian" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'kerapian' ? 'selected' : '' }}>Kerapian/Kebersihan</option>
                    <option value="ibadah" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'ibadah' ? 'selected' : '' }}>Ibadah/Agama</option>
                    <option value="berat" {{ old('filter_category', $jenisPelanggaran->filter_category) == 'berat' ? 'selected' : '' }}>Berat/Kejahatan</option>
                </select>
                <small class="text-muted d-block mt-1">Pilih kategori filter untuk memudahkan pencarian saat catat pelanggaran. Kosongkan jika tidak perlu filter khusus.</small>
                @error('filter_category') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Keywords/Alias (Opsional)</label>
                <small class="text-muted d-block mb-2">Tambahkan keyword alternatif untuk memudahkan pencarian. Contoh: jika nama "Mencuri", tambahkan keyword "Maling" atau "Nyuri".</small>
                
                <div id="keywordsContainer">
                    @php
                        $currentKeywords = old('keywords', $jenisPelanggaran->getKeywordsArray());
                    @endphp
                    
                    @if(count($currentKeywords) > 0)
                        @foreach($currentKeywords as $index => $kw)
                        <div class="input-group mb-2 keyword-input-group">
                            <input type="text" name="keywords[]" class="form-control" placeholder="Keyword {{$index+1}}" value="{{ $kw }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-danger btn-remove-keyword" type="button"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="input-group mb-2 keyword-input-group">
                            <input type="text" name="keywords[]" class="form-control" placeholder="Keyword 1">
                            <div class="input-group-append">
                                <button class="btn btn-outline-danger btn-remove-keyword" type="button"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    @endif
                </div>
                
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="btnAddKeyword">
                    <i class="fas fa-plus mr-1"></i> Tambah Keyword Lain
                </button>
                @error('keywords') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-actions">
                <a href="{{ route('jenis-pelanggaran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save mr-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/jenis_pelanggaran/edit.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnAddKeyword = document.getElementById('btnAddKeyword');
            const keywordsContainer = document.getElementById('keywordsContainer');
            let keywordCount = keywordsContainer.querySelectorAll('.keyword-input-group').length;

            function updateRemoveButtons() {
                const groups = keywordsContainer.querySelectorAll('.keyword-input-group');
                groups.forEach((group, index) => {
                    const btn = group.querySelector('.btn-remove-keyword');
                    btn.style.display = groups.length > 1 ? 'block' : 'none';
                });
            }

            btnAddKeyword.addEventListener('click', function() {
                keywordCount++;
                const newGroup = document.createElement('div');
                newGroup.className = 'input-group mb-2 keyword-input-group';
                newGroup.innerHTML = `
                    <input type="text" name="keywords[]" class="form-control" placeholder="Keyword ${keywordCount}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-danger btn-remove-keyword" type="button"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                keywordsContainer.appendChild(newGroup);
                
                newGroup.querySelector('.btn-remove-keyword').addEventListener('click', function() {
                    newGroup.remove();
                    updateRemoveButtons();
                });
                
                updateRemoveButtons();
            });

            // Initial setup untuk remove buttons
            document.querySelectorAll('.btn-remove-keyword').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.keyword-input-group').remove();
                    updateRemoveButtons();
                });
            });
            
            updateRemoveButtons();
        });
    </script>
@endpush
