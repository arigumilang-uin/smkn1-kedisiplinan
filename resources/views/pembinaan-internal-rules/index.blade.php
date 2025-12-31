@extends('layouts.app')

@section('title', 'Aturan Pembinaan Internal')
@section('subtitle', 'Threshold pembinaan berdasarkan akumulasi poin siswa.')
@section('page-header', true)

@section('content')
<div class="space-y-6" x-data="pembinaanRulesPage()">
    {{-- Action Button (inside x-data scope) --}}
    <div class="flex justify-end">
        <button type="button" @click="showAddModal = true" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            <span>Tambah Aturan Baru</span>
        </button>
    </div>

    {{-- Info Banner --}}
    <div class="p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-r-xl">
        <div class="flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-500 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <p class="text-sm text-indigo-800">
                <strong>Penting:</strong> Pembinaan internal adalah rekomendasi konseling, <strong>TIDAK</strong> memicu surat pemanggilan otomatis. Surat panggilan hanya dipicu oleh aturan frekuensi pelanggaran.
            </p>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Daftar Aturan Aktif</span>
            <span class="badge badge-primary">Total: {{ $rules->count() }} Aturan</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16">#</th>
                        <th>Range Poin</th>
                        <th>Pembina Terlibat</th>
                        <th>Keterangan</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td class="font-bold text-gray-400">{{ $rule->display_order }}</td>
                            <td>
                                <span class="badge badge-info font-bold">{{ $rule->getRangeText() }}</span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($rule->pembina_roles ?? [] as $role)
                                        <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded font-bold border border-blue-100">{{ $role }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="max-w-md">
                                <p class="text-xs text-gray-600 italic">{{ $rule->keterangan }}</p>
                            </td>
                            <td>
                                {{-- Desktop: Icon buttons --}}
                                <div class="action-buttons-desktop">
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode([
                                                'id' => $rule->id,
                                                'poin_min' => $rule->poin_min,
                                                'poin_max' => $rule->poin_max,
                                                'pembina_roles' => $rule->pembina_roles ?? [],
                                                'keterangan' => $rule->keterangan,
                                                'display_order' => $rule->display_order,
                                            ]) }})" 
                                            class="btn btn-icon btn-outline" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    </button>
                                    <form action="{{ route('pembinaan-internal-rules.destroy', $rule->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus aturan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                                
                                {{-- Mobile: Dropdown --}}
                                <div class="action-dropdown-mobile" x-data="{ open: false }">
                                    <button @click="open = !open" @click.away="open = false" class="action-dropdown-trigger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition class="action-dropdown-menu">
                                        <button type="button" 
                                                @click="openEditModal({{ json_encode([
                                                    'id' => $rule->id,
                                                    'poin_min' => $rule->poin_min,
                                                    'poin_max' => $rule->poin_max,
                                                    'pembina_roles' => $rule->pembina_roles ?? [],
                                                    'keterangan' => $rule->keterangan,
                                                    'display_order' => $rule->display_order,
                                                ]) }}); open = false" 
                                                class="action-dropdown-item action-dropdown-item--edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                            Edit
                                        </button>
                                        <div class="action-dropdown-divider"></div>
                                        <form action="{{ route('pembinaan-internal-rules.destroy', $rule->id) }}" method="POST" onsubmit="return confirm('Hapus aturan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-dropdown-item action-dropdown-item--delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>
                                    </svg>
                                    <h3 class="empty-state-title">Belum Ada Aturan</h3>
                                    <p class="empty-state-description">Klik tombol "Tambah Aturan Baru" untuk membuat threshold pembinaan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- Modal Tambah --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showAddModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form action="{{ route('pembinaan-internal-rules.store') }}" method="POST">
                    @csrf
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Tambah Aturan Pembinaan</h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Poin Min</label>
                                <input type="number" name="poin_min" class="form-input" value="{{ $suggestedPoinMin ?? 0 }}" min="0" required>
                                <p class="form-help">Rekomendasi: {{ $suggestedPoinMin ?? 0 }}</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Poin Max</label>
                                <input type="number" name="poin_max" class="form-input" min="0">
                                <p class="form-help">Kosongkan untuk ∞</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Pembina yang Terlibat</label>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach(['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'] as $role)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" class="rounded border-gray-300 text-indigo-600">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="form-input form-textarea" placeholder="Contoh: Pembinaan sedang, monitoring ketat..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="display_order" class="form-input" value="{{ $suggestedDisplayOrder ?? 1 }}" min="1">
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showEditModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form :action="'/pembinaan-internal-rules/' + editRule.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Edit Aturan Pembinaan</h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Poin Min</label>
                                <input type="number" name="poin_min" class="form-input" x-model="editRule.poin_min" min="0" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Poin Max</label>
                                <input type="number" name="poin_max" class="form-input" x-model="editRule.poin_max" min="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Pembina yang Terlibat</label>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach(['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'] as $role)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" 
                                               class="rounded border-gray-300 text-indigo-600"
                                               :checked="editRule.pembina_roles && editRule.pembina_roles.includes('{{ $role }}')">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="form-input form-textarea" x-model="editRule.keterangan" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="display_order" class="form-input" x-model="editRule.display_order" min="1">
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pembinaanRulesPage() {
    return {
        showAddModal: false,
        showEditModal: false,
        editRule: {
            id: null,
            poin_min: 0,
            poin_max: null,
            pembina_roles: [],
            keterangan: '',
            display_order: 1
        },
        
        openEditModal(rule) {
            this.editRule = { ...rule };
            this.showEditModal = true;
        }
    }
}
</script>
@endpush
@endsection
