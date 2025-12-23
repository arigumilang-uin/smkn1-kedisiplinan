@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    rose: { 50: '#fff1f2', 100: '#ffe4e6', 600: '#e11d48', 700: '#be123c' },
                    emerald: { 50: '#ecfdf5', 100: '#d1fae5', 600: '#059669', 700: '#047857' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6 bg-slate-50">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 pb-1 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-indigo-600 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Persetujuan Kasus</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-file-signature text-indigo-600"></i> Tinjau & Setujui Kasus
                </h1>
            </div>
            
            <a href="{{ route('tindak-lanjut.pending-approval') }}" class="btn-clean-action no-underline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0">Identitas Siswa</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-black shadow-indigo-200 shadow-lg">
                                {{ substr($kasus->siswa->nama_siswa, 0, 1) }}
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-slate-800 tracking-tight leading-none mb-2">{{ $kasus->siswa->nama_siswa }}</h2>
                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] font-mono text-slate-400">NISN: {{ $kasus->siswa->nisn }}</span>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold border border-slate-200 uppercase">{{ $kasus->siswa->kelas->nama_kelas }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0">Deskripsi Kasus & Sanksi</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Pemicu / Kejadian</span>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 italic text-sm text-slate-600 leading-relaxed">
                                "{{ $kasus->pemicu }}"
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">
                                <span class="block text-[9px] font-bold text-rose-400 uppercase tracking-widest mb-1">Rekomendasi Sanksi</span>
                                <span class="text-sm font-black text-rose-700">{{ $kasus->sanksi_deskripsi ?? 'Belum ditentukan' }}</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                                <span class="block text-[9px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Dilaporkan Oleh</span>
                                <span class="text-sm font-black text-indigo-700">{{ $kasus->user->nama ?? 'Sistem' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden sticky top-6">
                    <div class="p-6 bg-slate-900 text-white">
                        <h3 class="text-lg font-black tracking-tight m-0 flex items-center gap-2">
                            <i class="fas fa-check-circle text-emerald-400"></i> Keputusan Akhir
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-1">Silakan tinjau dan berikan validasi anda.</p>
                    </div>

                    <form id="approvalForm" method="POST" class="p-6 space-y-6">
                        @csrf
                        
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 block">Pilih Tindakan</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="action_type" value="approve" checked onclick="updateAction('approve')" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-100 bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all">
                                        <i class="fas fa-check text-slate-300 peer-checked:text-emerald-600 mb-1"></i>
                                        <span class="block text-[10px] font-black uppercase text-slate-400 peer-checked:text-emerald-700">Setujui</span>
                                    </div>
                                </label>
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="action_type" value="reject" onclick="updateAction('reject')" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-100 bg-slate-50 peer-checked:border-rose-500 peer-checked:bg-rose-50 transition-all">
                                        <i class="fas fa-times text-slate-300 peer-checked:text-rose-600 mb-1"></i>
                                        <span class="block text-[10px] font-black uppercase text-slate-400 peer-checked:text-rose-700">Tolak</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="reason" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 block">Catatan / Alasan</label>
                            <textarea name="reason" id="reason" rows="4" 
                                class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm transition-all"
                                placeholder="Tuliskan catatan untuk guru/wali murid..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-1">
                            Kirim Keputusan <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function updateAction(action) {
        const form = document.getElementById('approvalForm');
        const id = "{{ $kasus->id }}"; // Mengambil ID dari variabel $kasus
        
        // Kita susun URL-nya secara manual di JS supaya tidak error parameter
        if (action === 'approve') {
            form.action = "/tindak-lanjut/" + id + "/approve";
        } else {
            form.action = "/tindak-lanjut/" + id + "/reject";
        }
    }

    // Inisialisasi action saat pertama kali load
    window.onload = function() {
        updateAction('approve');
    };
</script>

<style>
    .page-wrap-custom { font-family: 'Inter', sans-serif; }
    .btn-clean-action {
        padding: 0.5rem 1rem; border-radius: 0.75rem; background: #fff; color: #475569; 
        font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; transition: 0.2s;
    }
    .btn-clean-action:hover { background: #f1f5f9; color: #0f172a; }
</style>
@endsection