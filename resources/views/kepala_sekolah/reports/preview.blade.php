@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG - Samakan persis dengan sebelumnya --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
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
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Pratinjau Laporan</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-file-invoice text-indigo-600"></i> Hasil Filter Laporan
                </h1>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('kepala-sekolah.reports.index') }}" class="btn-clean-action no-underline bg-white">
                    <i class="fas fa-arrow-left"></i> Kembali Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-between">
                <div>
                    <span class="block text-[9px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Jenis Laporan</span>
                    <span class="text-sm font-black text-indigo-700">{{ $reportType }}</span>
                </div>
                <div class="text-2xl text-indigo-200"><i class="fas fa-layer-group"></i></div>
            </div>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
                <div>
                    <span class="block text-[9px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Total Data Ditemukan</span>
                    <span class="text-sm font-black text-emerald-700">{{ $data->count() }} Records</span>
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 bg-white border border-emerald-200 rounded-lg text-[10px] font-bold text-emerald-600">CSV</button>
                    <button class="px-3 py-1 bg-white border border-emerald-200 rounded-lg text-[10px] font-bold text-emerald-600">PDF</button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0">Daftar Siswa & Tindakan</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">Siswa</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">Kelas</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">Keterangan</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">Waktu</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($data as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm font-black shadow-md">
                                        {{ substr($row->siswa->nama_siswa, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-800 leading-none mb-1">{{ $row->siswa->nama_siswa }}</div>
                                        <div class="text-[10px] font-mono text-slate-400 tracking-tight">NISN: {{ $row->siswa->nisn }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold border border-slate-200 uppercase">
                                    {{ $row->siswa->kelas->nama_kelas }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] text-slate-600 leading-relaxed italic m-0">
                                    "{{ $row->sanksi_deskripsi ?? $row->pemicu }}"
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[10px] font-bold text-slate-500">{{ $row->created_at->format('d M Y') }}</div>
                                <div class="text-[9px] text-slate-400 uppercase tracking-tighter">{{ $row->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $isDisetujui = $row->status == 'Disetujui';
                                    $badgeColor = $isDisetujui ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100';
                                @endphp
                                <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border {{ $badgeColor }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="text-slate-300 text-4xl mb-4"><i class="fas fa-inbox"></i></div>
                                <h4 class="text-slate-800 font-bold m-0">Data Kosong</h4>
                                <p class="text-xs text-slate-400 mt-1">Tidak ada data yang sesuai dengan filter ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    .page-wrap-custom { font-family: 'Inter', sans-serif; }
    .btn-clean-action {
        padding: 0.5rem 1rem; border-radius: 0.75rem; color: #475569; 
        font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; transition: 0.2s;
    }
    .btn-clean-action:hover { background: #f1f5f9; color: #0f172a; }
</style>
@endsection