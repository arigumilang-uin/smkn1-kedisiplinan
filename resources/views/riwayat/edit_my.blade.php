@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-3xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-1 pb-1 custom-header-row">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-edit text-indigo-600"></i> Edit Catatan Pelanggaran
                </h1>
                <p class="text-slate-500 text-sm mt-1">Perbarui informasi laporan pelanggaran siswa.</p>
            </div>
            
            <a href="{{ route('my-riwayat.index') }}" class="btn-clean-action no-underline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden animate-in fade-in duration-500">
            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100 shadow-sm">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block leading-none">Siswa Terkait</span>
                    <span class="text-sm font-bold text-slate-700 leading-tight">{{ $r->siswa?->nama }}</span>
                </div>
            </div>

            <div class="p-8">
                <form action="{{ route('my-riwayat.update', $r->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="jenis_pelanggaran_id" class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Jenis Pelanggaran</label>
                        <select name="jenis_pelanggaran_id" id="jenis_pelanggaran_id" class="custom-select-clean w-full">
                            @foreach($jenis as $j)
                                <option value="{{ $j->id }}" {{ $r->jenis_pelanggaran_id == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_pelanggaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_kejadian" class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Tanggal Kejadian</label>
                            <input type="date" name="tanggal_kejadian" id="tanggal_kejadian" 
                                   value="{{ optional($r->tanggal_kejadian)->format('Y-m-d') }}" 
                                   class="custom-input-clean w-full">
                        </div>
                        <div>
                            <label for="jam_kejadian" class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Jam Kejadian</label>
                            <input type="time" name="jam_kejadian" id="jam_kejadian" 
                                   value="{{ optional($r->tanggal_kejadian)->format('H:i') }}" 
                                   class="custom-input-clean w-full">
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="keterangan" rows="4" 
                                  class="custom-input-clean w-full resize-none" 
                                  placeholder="Tuliskan detail kejadian...">{{ old('keterangan', $r->keterangan) }}</textarea>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <label for="bukti_foto" class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Unggah Bukti Foto (Opsional)</label>
                        <input type="file" name="bukti_foto" id="bukti_foto" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        
                        @if($r->bukti_foto_path)
                            <div class="mt-4 flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                <i class="fas fa-image text-indigo-500"></i>
                                <span class="text-xs text-slate-600 font-medium">Bukti saat ini sudah tersedia</span>
                                <a href="{{ route('bukti.show', $r->bukti_foto_path) }}" target="_blank" class="ml-auto text-xs font-bold text-indigo-600 hover:text-indigo-800 no-underline">Lihat Foto</a>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <a href="{{ route('my-riwayat.index') }}" class="btn-filter-secondary no-underline px-6">Batal</a>
                        <button type="submit" class="btn-filter-primary px-8">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* --- CORE STYLING --- */
    .page-wrap-custom { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    .custom-header-row { border-bottom: 1px solid #e2e8f0; }

    /* Form Controls */
    .custom-input-clean, .custom-select-clean {
        border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 1rem;
        font-size: 0.85rem; background: white; outline: none; transition: 0.2s;
    }
    .custom-input-clean:focus, .custom-select-clean:focus { 
        border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); 
    }

    .btn-filter-primary {
        background: #4f46e5; color: white; border: none; border-radius: 0.75rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.75rem 1.5rem; cursor: pointer; transition: 0.2s;
    }
    .btn-filter-primary:hover { background-color: #4338ca; transform: translateY(-1px); }

    .btn-filter-secondary {
        background: #f1f5f9; color: #64748b; border-radius: 0.75rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.75rem 1.5rem; border: none; cursor: pointer; transition: 0.2s;
    }
    .btn-filter-secondary:hover { background-color: #e2e8f0; color: #1e293b; }

    .btn-clean-action {
        padding: 0.65rem 1.2rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; transition: 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid #e2e8f0;
    }
    .btn-clean-action:hover { background-color: #e2e8f0; color: #1e293b; }

    /* Animasi */
    .animate-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection