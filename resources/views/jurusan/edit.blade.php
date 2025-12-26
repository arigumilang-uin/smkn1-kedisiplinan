@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Konfigurasi warna dasar agar seragam
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a', // Slate 900
                    accent: '#3b82f6',  // Blue 500
                    amber: { 500: '#f59e0b' },
                    indigo: { 600: '#4f46e5' }
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap bg-gray-50 min-h-screen p-3 sm:p-6">
    
    <div class="max-w-4xl mx-auto">
        
        <div class="flex justify-between items-center mb-3 pb-1 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Jurusan</h1>
                <p class="text-sm text-gray-500 mt-1">Perbarui detail untuk Jurusan: <span class="font-semibold text-slate-800">{{ $jurusan->nama_jurusan }}</span></p>
            </div>
        </div>

        <form action="{{ route('jurusan.update', $jurusan) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                     <h3 class="text-base font-bold text-slate-700 m-0 flex items-center gap-2">
                         <i class="fas fa-edit text-amber-500"></i> Detail Program Studi
                     </h3>
                </div>
                
                <div class="p-6 space-y-6">
                    
                    {{-- Nama Jurusan --}}
                    <div>
                        <label class="form-label-custom">Nama Jurusan</label>
                        <input type="text" name="nama_jurusan" class="form-input-custom" value="{{ old('nama_jurusan', $jurusan->nama_jurusan) }}" required>
                    </div>

                    {{-- Kode Jurusan --}}
                    <div>
                        <label class="form-label-custom">Kode Jurusan (akan digunakan untuk Kaprodi)</label>
                        <input type="text" name="kode_jurusan" class="form-input-custom" value="{{ old('kode_jurusan', $jurusan->kode_jurusan) }}" placeholder="Contoh: APHP">
                    </div>

                    {{-- HIDDEN: Preserve kaprodi_user_id --}}
                    <input type="hidden" name="kaprodi_user_id" value="{{ $jurusan->kaprodi_user_id }}">

                    {{-- Kaprodi Saat Ini --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <label class="form-label-custom mb-1">Kepala Program Studi (Saat Ini)</label>
                        @if($jurusan->kaprodi)
                            <div class="flex items-center gap-3 mt-2">
                                <span class="px-3 py-1 rounded-full text-sm font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                    {{ $jurusan->kaprodi->nama }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    Username: {{ $jurusan->kaprodi->username }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-3">
                                Jika Anda mengubah kode jurusan, username Kaprodi akan diperbarui otomatis. Untuk ganti Kaprodi, lakukan di halaman Edit User.
                            </p>
                        @else
                            <p class="text-sm text-slate-600">
                                <i class="fas fa-info-circle mr-1 text-amber-500"></i> Belum ada Kaprodi yang ditetapkan.
                            </p>
                        @endif
                    </div>
                    
                    {{-- OPSI BUAT KAPRODI OTOMATIS (Jika belum ada) --}}
                    @if(!$jurusan->kaprodi)
                    <div class="border border-indigo-200 bg-indigo-50 rounded-xl p-4">
                        <div class="flex items-start">
                            <input type="checkbox" class="form-checkbox-custom mt-1" id="create_kaprodi" name="create_kaprodi" value="1">
                            <label class="form-label-custom text-indigo-700 ml-3 cursor-pointer" for="create_kaprodi">
                                Buat akun Kaprodi otomatis sekarang
                            </label>
                        </div>
                        
                        <div id="kaprodi_preview" class="p-3 mt-3 border border-indigo-300 rounded-lg bg-white" style="display:none;">
                            <strong class="text-sm text-indigo-800">Preview Akun Baru:</strong>
                            <div class="grid grid-cols-2 gap-4 mt-2 text-sm">
                                <div><span class="text-slate-500">Username:</span> <span id="kaprodi_username_preview" class="font-bold text-slate-800"></span></div>
                                <div><span class="text-slate-500">Password (Sampel):</span> <span id="kaprodi_password_preview" class="font-bold text-slate-800"></span></div>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2">Password akan di-generate ulang saat disimpan.</p>
                        </div>
                    </div>
                    @endif

                    {{-- ============================================================= --}}
                    {{-- SECTION BARU: PROGRAM KEAHLIAN (Hierarki Parent) --}}
                    {{-- ============================================================= --}}
                    <div class="border-t border-slate-200 pt-6 mt-6">
                        <label class="form-label-custom mb-3 flex items-center gap-2">
                            <i class="fas fa-sitemap text-indigo-500"></i> Program Keahlian (Hierarki)
                        </label>
                        
                        <div class="space-y-4">
                            {{-- Dropdown Program Keahlian --}}
                            <div>
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">Pilih Program Keahlian Induk</label>
                                <select name="program_keahlian_id" class="form-input-custom">
                                    <option value="">-- Tidak Ada (Jurusan Mandiri) --</option>
                                    @foreach($programKeahlianList ?? [] as $pk)
                                        <option value="{{ $pk->id }}" {{ old('program_keahlian_id', $jurusan->program_keahlian_id) == $pk->id ? 'selected' : '' }}>
                                            {{ $pk->nama_program }} {{ $pk->kode_program ? '('.$pk->kode_program.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Jika jurusan ini merupakan bagian dari Program Keahlian, pilih program induknya di sini.
                                </p>
                            </div>
                            
                            {{-- Tingkat Kelas --}}
                            <div>
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">Tingkat Kelas (Opsional)</label>
                                <select name="tingkat" class="form-input-custom">
                                    <option value="">-- Semua Tingkat --</option>
                                    <option value="X" {{ old('tingkat', $jurusan->tingkat) == 'X' ? 'selected' : '' }}>Kelas X</option>
                                    <option value="XI" {{ old('tingkat', $jurusan->tingkat) == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                    <option value="XII" {{ old('tingkat', $jurusan->tingkat) == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                                </select>
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Jika jurusan ini spesifik untuk tingkat tertentu (misal: konsentrasi berbeda per tingkat).
                                </p>
                            </div>
                            
                            {{-- Info Program Keahlian saat ini --}}
                            @if($jurusan->programKeahlian)
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mt-4">
                                <div class="flex items-center gap-2 text-emerald-700 text-sm font-bold mb-2">
                                    <i class="fas fa-link"></i> Tergabung dalam Program Keahlian
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-emerald-100 text-emerald-700">
                                        {{ $jurusan->programKeahlian->nama_program }}
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        Kaprodi jurusan Anda mengelola semua konsentrasi dalam program ini.
                                    </span>
                                </div>
                                @php
                                    $siblingJurusan = $jurusan->programKeahlian->jurusan()->where('id', '!=', $jurusan->id)->get();
                                @endphp
                                @if($siblingJurusan->count() > 0)
                                <div class="mt-3 text-xs text-slate-500">
                                    <span class="font-semibold">Jurusan lain dalam program ini:</span>
                                    @foreach($siblingJurusan as $sib)
                                        <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-600 ml-1">{{ $sib->nama_jurusan }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- ============================================================= --}}
                    {{-- SECTION: BUAT PROGRAM KEAHLIAN BARU --}}
                    {{-- ============================================================= --}}
                    <div class="border border-dashed border-indigo-300 bg-indigo-50/50 rounded-xl p-4 mt-4">
                        <div class="flex items-start">
                            <input type="checkbox" class="form-checkbox-custom mt-1" id="create_program" name="create_program" value="1">
                            <label class="form-label-custom text-indigo-700 ml-3 cursor-pointer" for="create_program">
                                Buat Program Keahlian baru dengan jurusan ini sebagai anggota pertama
                            </label>
                        </div>
                        
                        <div id="program_form" class="mt-4 space-y-3" style="display:none;">
                            <div>
                                <label class="text-xs font-semibold text-indigo-600 mb-1 block">Nama Program Keahlian</label>
                                <input type="text" name="new_program_nama" class="form-input-custom" placeholder="Contoh: Teknik Energi">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-indigo-600 mb-1 block">Kode Program (Opsional)</label>
                                <input type="text" name="new_program_kode" class="form-input-custom" placeholder="Contoh: TE">
                            </div>
                        </div>
                    </div>

                </div>
                
                {{-- FOOTER ACTION --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-slate-200 flex justify-end space-x-3">
                    <a href="{{ route('jurusan.show', $jurusan) }}" class="px-5 py-2 text-sm font-bold text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-100 transition no-underline">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // =====================================================================
    // KAPRODI AUTO-CREATE TOGGLE
    // =====================================================================
    const chk = document.getElementById('create_kaprodi');
    if(chk) {
        const namaInput = document.querySelector('input[name="nama_jurusan"]');
        const kodeInput = document.querySelector('input[name="kode_jurusan"]');
        const previewBox = document.getElementById('kaprodi_preview');
        const userPreview = document.getElementById('kaprodi_username_preview');
        const passPreview = document.getElementById('kaprodi_password_preview');

        function generateKodeFromNama(nama){
            const parts = nama.trim().split(/\s+/).filter(Boolean);
            let letters = '';
            for(let p of parts){ letters += p[0].toUpperCase(); if(letters.length>=3) break; }
            return letters || 'JRS';
        }

        function normalizeKaprodiUsernameFromKode(kode){
            return 'kaprodi.' + kode.toLowerCase().replace(/[^a-z0-9]+/g, '');
        }

        function randomPassword(len=8){
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            let out = '';
            for(let i=0;i<len;i++) out += chars.charAt(Math.floor(Math.random()*chars.length));
            return out;
        }

        function updatePreview(){
            const nama = namaInput.value || '';
            let kode = kodeInput.value || '';
            if (!kode) kode = generateKodeFromNama(nama);
            
            if (kode.length > 5) kode = kode.substring(0, 5); 
            
            const username = normalizeKaprodiUsernameFromKode(kode);
            
            if (passPreview.textContent === '' || previewBox.dataset.generated !== 'true') {
                passPreview.textContent = randomPassword(8);
                previewBox.dataset.generated = 'true';
            }
            
            userPreview.textContent = username;
        }

        chk.addEventListener('change', function(){
            if(chk.checked){ 
                previewBox.style.display = 'block'; 
                updatePreview(); 
            }
            else previewBox.style.display = 'none';
        });

        namaInput.addEventListener('input', function(){ if(chk.checked) updatePreview(); });
        kodeInput.addEventListener('input', function(){ if(chk.checked) updatePreview(); });
        
        if (chk.checked) {
            previewBox.style.display = 'block';
            updatePreview();
        }
    }
    
    // =====================================================================
    // PROGRAM KEAHLIAN CREATE TOGGLE
    // =====================================================================
    const createProgramChk = document.getElementById('create_program');
    const programForm = document.getElementById('program_form');
    const programSelect = document.querySelector('select[name="program_keahlian_id"]');
    
    if(createProgramChk && programForm) {
        createProgramChk.addEventListener('change', function(){
            if(createProgramChk.checked) {
                programForm.style.display = 'block';
                // Disable existing program dropdown
                if(programSelect) {
                    programSelect.disabled = true;
                    programSelect.value = '';
                }
            } else {
                programForm.style.display = 'none';
                if(programSelect) {
                    programSelect.disabled = false;
                }
            }
        });
    }
});
</script>
@endpush

@section('styles')
<style>
    .page-wrap { font-family: 'Inter', sans-serif; }
    
    .form-label-custom {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569; /* Slate 600 */
        margin-bottom: 0.5rem;
    }
    .form-input-custom {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        color: #1e293b;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-input-custom:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .form-checkbox-custom {
        /* Custom styling for checkbox */
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 0.25rem;
        border: 1px solid #94a3b8;
        background-color: white;
        transition: all 0.2s;
        cursor: pointer;
    }
    .form-checkbox-custom:checked {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
</style>
@endsection