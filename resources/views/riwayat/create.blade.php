@extends('layouts.app')

@section('title', 'Catat Pelanggaran')
@section('page-header', false)

@section('content')
<div x-data="violationWizard()" class="min-h-screen py-8 px-4" x-cloak>
    
    {{-- Progress Stepper --}}
    <div class="max-w-4xl mx-auto mb-10">
        <div class="relative flex items-center justify-between w-full">
            <div class="absolute inset-0 top-1/2 h-1 bg-gray-200 -z-10 rounded-full"></div>
            <div class="absolute inset-0 top-1/2 h-1 bg-blue-600 -z-10 rounded-full transition-all duration-500 ease-out" :style="'width: ' + ((step - 1) / 2 * 100) + '%'"></div>

            <!-- Step 1 Trigger -->
            <div class="flex flex-col items-center gap-2 cursor-pointer bg-white px-2" @click="step > 1 ? step = 1 : null">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg border-2 transition-colors duration-300"
                     :class="step >= 1 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-white border-gray-300 text-gray-400'">
                    1
                </div>
                <span class="text-xs font-bold uppercase tracking-wider" :class="step >= 1 ? 'text-blue-600' : 'text-gray-400'">Pilih Data</span>
            </div>
            
            <!-- Step 2 Trigger -->
            <div class="flex flex-col items-center gap-2 bg-white px-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg border-2 transition-colors duration-300"
                     :class="step >= 2 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/30' : (step === 1 ? 'border-blue-200 text-blue-200' : 'bg-white border-gray-300 text-gray-400')">
                    2
                </div>
                <span class="text-xs font-bold uppercase tracking-wider" :class="step >= 2 ? 'text-blue-600' : 'text-gray-400'">Detail</span>
            </div>
            
            <!-- Step 3 Trigger -->
            <div class="flex flex-col items-center gap-2 bg-white px-2">
                 <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg border-2 transition-colors duration-300"
                     :class="step >= 3 ? 'bg-green-600 border-green-600 text-white shadow-lg shadow-green-500/30' : 'bg-white border-gray-300 text-gray-400'">
                    3
                </div>
                <span class="text-xs font-bold uppercase tracking-wider" :class="step >= 3 ? 'text-green-600' : 'text-gray-400'">Selesai</span>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden min-h-[500px] flex flex-col relative">
        
        {{-- Loading Overlay --}}
        <div class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center" x-show="isLoading" x-transition>
            <div class="flex flex-col items-center gap-3">
                <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-medium text-blue-600 animate-pulse">Memproses Data...</p>
            </div>
        </div>
        
        <form x-ref="form" action="{{ route('riwayat.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col">
            @csrf
            <input type="hidden" name="guru_pencatat_user_id" value="{{ auth()->id() }}">

            {{-- STEP 1: PILIH DATA --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col h-full">
                <div class="grid grid-cols-1 lg:grid-cols-2 h-full divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                    {{-- KIRI: SISWA --}}
                    <div class="flex flex-col h-[500px] lg:h-auto">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800 mb-1">Pilih Siswa</h3>
                            <p class="text-sm text-gray-500 mb-4">Siapa yang melanggar?</p>
                            <input type="text" x-model="searchSiswa" class="form-input" placeholder="Cari Nama, NISN, atau Kelas...">
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-2">
                             @foreach($daftarSiswa ?? [] as $s)
                                <label class="flex items-center gap-4 p-3 rounded-xl border-2 cursor-pointer transition-all hover:bg-gray-50"
                                       x-show="matchSiswa('{{ strtolower($s->nama_siswa) }}')"
                                       :class="selectedSiswa.includes('{{ $s->id }}') ? 'border-blue-500 bg-blue-50/50' : 'border-transparent bg-white hover:border-gray-200 shadow-sm'">
                                    <input type="checkbox" name="siswa_id[]" value="{{ $s->id }}" x-model="selectedSiswa" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">{{ $s->nama_siswa }}</p>
                                        <p class="text-xs text-gray-500">{{ $s->kelas->nama_kelas ?? '-' }} • {{ $s->nisn }}</p>
                                    </div>
                                    <div class="w-2 h-2 rounded-full bg-blue-500" x-show="selectedSiswa.includes('{{ $s->id }}')"></div>
                                </label>
                            @endforeach
                            <div class="text-center py-10" x-show="searchSiswa && !hasSiswaMatch()">
                                <p class="text-gray-400">Tidak ditemukan.</p>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 border-t border-gray-100 text-center text-sm font-medium text-gray-600">
                            <span x-text="selectedSiswa.length"></span> Siswa Terpilih
                        </div>
                    </div>
                    
                    {{-- KANAN: PELANGGARAN --}}
                    <div class="flex flex-col h-[500px] lg:h-auto">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                             <h3 class="text-lg font-bold text-gray-800 mb-1">Pilih Pelanggaran</h3>
                            <p class="text-sm text-gray-500 mb-4">Apa pelanggarannya?</p>
                            <input type="text" x-model="searchPelanggaran" class="form-input" placeholder="Cari Jenis Pelanggaran...">
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-2">
                             @foreach($daftarPelanggaran ?? [] as $p)
                                <label class="flex items-start gap-4 p-3 rounded-xl border-2 cursor-pointer transition-all hover:bg-gray-50"
                                       x-show="matchPelanggaran('{{ strtolower($p->nama_pelanggaran) }}')"
                                       :class="selectedPelanggaran.includes('{{ $p->id }}') ? 'border-red-500 bg-red-50/30' : 'border-transparent bg-white hover:border-gray-200 shadow-sm'">
                                    <input type="checkbox" name="jenis_pelanggaran_id[]" value="{{ $p->id }}" x-model="selectedPelanggaran" class="w-5 h-5 mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <p class="font-bold text-gray-800">{{ $p->nama_pelanggaran }}</p>
                                            <span class="badge" :class="selectedPelanggaran.includes('{{ $p->id }}') ? 'badge-danger' : 'badge-secondary'">{{ $p->poin }} Poin</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $p->kategori ?? 'Umum' }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="p-4 bg-gray-50 border-t border-gray-100 text-center text-sm font-medium text-gray-600">
                            <span x-text="selectedPelanggaran.length"></span> Jenis Pelanggaran Terpilih
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 2: DETAIL --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col p-8">
                <div class="max-w-3xl mx-auto w-full space-y-8">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-900">Detail Kejadian</h2>
                        <p class="text-gray-500">Lengkapi informasi waktu dan bukti pendukung.</p>
                    </div>

                    <div class="bg-blue-50/50 rounded-2xl p-6 border border-blue-100 flex gap-4 items-start">
                        <div class="bg-white p-2 rounded-lg shadow-sm text-blue-600">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-900 uppercase tracking-wider mb-1">Mencatat Pelanggaran Untuk:</p>
                            <p class="text-blue-800 font-medium" x-text="selectedSiswa.length + ' Siswa'"></p>
                            <p class="text-blue-800 font-medium mt-1" x-text="selectedPelanggaran.length + ' Jenis Pelanggaran'"></p>
                            <p class="text-xs text-blue-600 mt-2 italic">*Total <span x-text="selectedSiswa.length * selectedPelanggaran.length"></span> catatan akan dibuat.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label font-bold">Tanggal Kejadian</label>
                            <input type="date" name="tanggal_kejadian" x-model="formData.tanggal" class="form-input text-lg" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-bold">Waktu</label>
                            <input type="time" name="waktu_kejadian" x-model="formData.waktu" class="form-input text-lg">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label font-bold">Keterangan Tambahan</label>
                        <textarea name="keterangan" x-model="formData.keterangan" rows="4" class="form-input form-textarea" placeholder="Jelaskan detail kejadian..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label font-bold">Bukti Foto (Opsional)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 transition-colors cursor-pointer relative" :class="{'border-blue-500 bg-blue-50': fileName}">
                            <input type="file" name="bukti_foto" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleFileUpload">
                            <div x-show="!fileName" class="flex flex-col items-center gap-2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <span>Klik atau Drop foto di sini</span>
                            </div>
                            <div x-show="fileName" class="flex items-center justify-center gap-2 text-blue-600 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                <span x-text="fileName"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 3: SUCCESS (RESULT PREVIEW) --}}
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-500 transform" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="flex-1 flex flex-col items-center justify-center p-8 text-center h-full">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-4 shadow-xl shadow-green-100 animate-bounce-slow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <p class="text-green-600 font-medium mb-6" x-text="successMessage || 'Data berhasil tersimpan.'"></p>

                {{-- RESULT TABLE --}}
                <div class="w-full max-w-2xl bg-gray-50 rounded-xl border border-gray-200 overflow-hidden mb-8 text-left shadow-sm">
                    <div class="p-3 border-b border-gray-200 bg-gray-100 flex justify-between items-center">
                        <h4 class="font-bold text-gray-700 text-sm flex items-center gap-2">
                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                             Data Tersimpan
                        </h4>
                        <span class="badge badge-success text-xs font-bold px-2" x-text="savedData.length + ' Item'"></span>
                    </div>
                    <div class="max-h-60 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-white text-gray-500 border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                                 <tr>
                                    <th class="px-4 py-2 text-left font-medium w-1/3">Siswa</th>
                                    <th class="px-4 py-2 text-left font-medium w-1/3">Pelanggaran</th>
                                    <th class="px-4 py-2 text-right font-medium">Poin</th>
                                 </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white leading-relaxed">
                                <template x-for="(item, index) in savedData" :key="index">
                                     <tr class="hover:bg-gray-50 transition-colors">
                                         <td class="px-4 py-2 text-gray-800 font-bold" x-text="item.siswa_nama"></td>
                                         <td class="px-4 py-2 text-gray-600" x-text="item.pelanggaran_nama"></td>
                                         <td class="px-4 py-2 text-right text-red-600 font-bold" x-text="'+' + item.poin"></td>
                                     </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary px-6">Ke Dashboard</a>
                    <button type="button" @click="resetWizard()" class="btn btn-primary px-8 shadow-lg shadow-blue-500/30">Catat Pelanggaran Lain</button>
                </div>
            </div>
            
            {{-- Footer Controls --}}
            <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-between items-center" x-show="step < 3">
                
                <!-- Back Button -->
                <button type="button" @click="step--" class="btn btn-secondary px-6" x-show="step > 1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="m15 18-6-6 6-6"/></svg>
                    Kembali
                </button>
                <div x-show="step === 1"></div> <!-- Spacer -->

                <!-- Next/Submit Button -->
                <button type="button" @click="nextStep()" class="btn btn-primary px-8 shadow-lg shadow-blue-500/20 disabled:opacity-50 disabled:cursor-not-allowed text-lg font-bold transition-all transform hover:-translate-y-0.5" 
                        :disabled="(step === 1 && (selectedSiswa.length === 0 || selectedPelanggaran.length === 0))">
                    <span x-text="step === 1 ? 'Lanjut Detail' : 'Simpan Data'"></span>
                    <svg x-show="step === 1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function violationWizard() {
        // Inject Data for Client Side Mapping
        const siswaMap = {!! json_encode($daftarSiswa->pluck('nama_siswa', 'id')) !!};
        const pelanggaranMap = {!! json_encode($daftarPelanggaran->mapWithKeys(fn($i) => [$i->id => ['nama' => $i->nama_pelanggaran, 'poin' => $i->poin]])) !!};

        return {
            step: 1,
            isLoading: false,
            searchSiswa: '',
            searchPelanggaran: '',
            selectedSiswa: [],
            selectedPelanggaran: [],
            savedData: [],
            fileName: '',
            successMessage: '',
            formData: {
                tanggal: '{{ date("Y-m-d") }}',
                waktu: '{{ date("H:i") }}',
                keterangan: ''
            },

            // Helpers
            matchSiswa(nama) { return !this.searchSiswa || nama.includes(this.searchSiswa.toLowerCase()); },
            matchPelanggaran(nama) { return !this.searchPelanggaran || nama.includes(this.searchPelanggaran.toLowerCase()); },
            hasSiswaMatch() { return true; }, 
            
            handleFileUpload(event) {
                const file = event.target.files[0];
                if (file) this.fileName = file.name;
            },

            nextStep() {
                if (this.step === 1) {
                    if (this.selectedSiswa.length > 0 && this.selectedPelanggaran.length > 0) {
                        this.step = 2;
                    }
                } else if (this.step === 2) {
                    this.submitData();
                }
            },

            async submitData() {
                this.isLoading = true;
                const form = this.$refs.form;
                const data = new FormData(form);
                
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: data,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.successMessage = result.message;
                        this.savedData = result.data;
                        this.step = 3; // Go to Result Step
                    } else {
                        alert(result.message || 'Error.');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Server error.');
                } finally {
                    this.isLoading = false;
                }
            },

            resetWizard() {
                this.step = 1;
                this.selectedSiswa = [];
                this.selectedPelanggaran = [];
                this.fileName = '';
                this.successMessage = '';
                this.formData.keterangan = '';
                this.searchSiswa = '';
                this.searchPelanggaran = '';
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    .animate-bounce-slow { animation: bounce 2s infinite; }
</style>
@endsection
