@extends('layouts.app')

@section('title', 'Import Siswa')
@section('subtitle', 'Import data siswa secara massal dari file CSV/Excel atau input manual.')
@section('page-header', true)

@section('actions')
    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
        </svg>
        <span>Kembali</span>
    </a>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Info Banner --}}
    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl">
        <div class="flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600 shrink-0 mt-0.5">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
            <div>
                <p class="font-medium text-blue-800">Format File CSV/Excel</p>
                <p class="text-sm text-blue-700 mt-1">
                    File harus memiliki kolom: <code class="bg-blue-100 px-1 rounded">nisn</code>, 
                    <code class="bg-blue-100 px-1 rounded">nama</code>, 
                    <code class="bg-blue-100 px-1 rounded">nomor_hp</code> (opsional). 
                    NISN harus 10 digit angka dan unik.
                </p>
            </div>
        </div>
    </div>

    {{-- Error Messages --}}
    @if(session('bulk_errors'))
        <div class="p-4 bg-red-50 border border-red-100 rounded-xl">
            <div class="flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600 shrink-0 mt-0.5">
                    <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-red-800">Terjadi Error pada Beberapa Baris:</p>
                    <ul class="text-sm text-red-700 mt-2 space-y-1 list-disc pl-4">
                        @foreach(session('bulk_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Import Siswa</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('siswa.bulk-store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                {{-- Pilih Kelas --}}
                <div class="form-group">
                    <label for="kelas_id" class="form-label form-label-required">Kelas Tujuan</label>
                    <select id="kelas_id" name="kelas_id" class="form-input form-select @error('kelas_id') error @enderror" required>
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas ?? [] as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} {{ $k->nama_jurusan ? '(' . $k->nama_jurusan . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="form-help">Semua siswa yang diimport akan masuk ke kelas ini.</p>
                    @error('kelas_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Tabs for Upload Method --}}
                <div x-data="{ method: 'upload' }" class="space-y-4">
                    <div class="flex gap-2 border-b border-gray-200">
                        <button 
                            type="button" 
                            @click="method = 'upload'" 
                            :class="method === 'upload' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                            class="px-4 py-2 font-medium text-sm"
                        >
                            📁 Upload File
                        </button>
                        <button 
                            type="button" 
                            @click="method = 'manual'" 
                            :class="method === 'manual' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                            class="px-4 py-2 font-medium text-sm"
                        >
                            ✏️ Input Manual
                        </button>
                    </div>
                    
                    {{-- Upload File Tab --}}
                    <div x-show="method === 'upload'" class="space-y-4">
                        <div class="form-group">
                            <label for="bulk_file" class="form-label">Upload File CSV/Excel</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-blue-400 transition-colors">
                                <input type="file" id="bulk_file" name="bulk_file" accept=".csv,.txt,.xlsx" class="hidden">
                                <label for="bulk_file" class="cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto text-gray-300 mb-3">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                                    </svg>
                                    <p class="text-gray-600 font-medium">Klik untuk pilih file atau drag & drop</p>
                                    <p class="text-sm text-gray-400 mt-1">Format: CSV, TXT, XLSX (Maks. 2MB)</p>
                                </label>
                            </div>
                            <p id="file-name" class="form-help mt-2 hidden"></p>
                            @error('bulk_file')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Download Template --}}
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm font-medium text-gray-700 mb-2">Download Template:</p>
                            <div class="flex gap-2">
                                <a href="{{ asset('templates/template_import_siswa.csv') }}" download class="btn btn-sm btn-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    Template CSV
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Manual Input Tab --}}
                    <div x-show="method === 'manual'" class="space-y-4">
                        <div class="form-group">
                            <label for="bulk_data" class="form-label">Data Siswa (Format CSV)</label>
                            <textarea 
                                id="bulk_data" 
                                name="bulk_data" 
                                rows="10" 
                                class="form-input form-textarea font-mono text-sm @error('bulk_data') error @enderror"
                                placeholder="nisn,nama,nomor_hp
1234567890,Ahmad Rizki,081234567890
0987654321,Siti Nurhaliza,081234567891
1122334455,Budi Santoso,"
                            >{{ old('bulk_data') }}</textarea>
                            <p class="form-help">Satu siswa per baris. Format: <code>nisn,nama,nomor_hp</code> (nomor_hp boleh kosong)</p>
                            @error('bulk_data')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Example Data --}}
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm font-medium text-gray-700 mb-2">Contoh format:</p>
                            <pre class="text-xs bg-gray-100 p-3 rounded-lg overflow-x-auto">nisn,nama,nomor_hp
1234567890,Ahmad Rizki,081234567890
0987654321,Siti Nurhaliza,081234567891
1122334455,Budi Santoso,</pre>
                        </div>
                    </div>
                </div>
                
                {{-- Create Wali Option --}}
                <div class="form-group">
                    <label class="flex items-center gap-3 cursor-pointer p-3 bg-blue-50 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                        <input 
                            type="checkbox" 
                            name="create_wali_all" 
                            value="1" 
                            {{ old('create_wali_all') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <div>
                            <span class="text-sm font-medium text-blue-800">Buat akun wali murid untuk semua siswa</span>
                            <p class="text-xs text-blue-600">Sistem akan membuat akun wali murid dengan username berdasarkan NISN.</p>
                        </div>
                    </label>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                        </svg>
                        <span>Import Siswa</span>
                    </button>
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('bulk_file');
    const fileName = document.getElementById('file-name');
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileName.textContent = 'File terpilih: ' + this.files[0].name;
            fileName.classList.remove('hidden');
        } else {
            fileName.classList.add('hidden');
        }
    });
});
</script>
@endpush
