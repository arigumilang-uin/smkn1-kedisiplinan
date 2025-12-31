
    {{-- Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Nama Pelanggaran</th>
                    <th class="w-[40%]">Rules (Frekuensi, Poin & Sanksi)</th>
                    <th class="text-center">Status</th>
                    <th class="w-32 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jenisPelanggaran ?? [] as $jp)
                    <tr>
                        {{-- Kategori --}}
                        <td>
                            @php
                                $kategoriNama = strtolower($jp->kategoriPelanggaran->nama_kategori ?? '');
                                $badgeClass = 'badge-neutral';
                                if (str_contains($kategoriNama, 'ringan')) $badgeClass = 'badge-info';
                                elseif (str_contains($kategoriNama, 'sedang')) $badgeClass = 'badge-warning';
                                elseif (str_contains($kategoriNama, 'berat')) $badgeClass = 'badge-danger';
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ $jp->kategoriPelanggaran->nama_kategori ?? '-' }}
                            </span>
                        </td>

                        {{-- Nama --}}
                        <td>
                            <div class="font-medium text-gray-800">{{ $jp->nama_pelanggaran }}</div>
                            <div class="text-xs text-gray-400 font-mono">ID: {{ $jp->id }}</div>
                        </td>

                        {{-- Rules List --}}
                        <td>
                            @if($jp->frequencyRules->count() > 0)
                                <div class="space-y-2">
                                    @foreach($jp->frequencyRules as $rule)
                                    <div class="p-2 rounded-lg border border-gray-100 bg-gray-50 text-xs">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded bg-gray-800 text-white font-bold text-[10px]">
                                                @if($rule->frequency_min == 1 && !$rule->frequency_max) 
                                                    Setiap 
                                                @elseif($rule->frequency_max) 
                                                    {{$rule->frequency_min}}-{{$rule->frequency_max}}x 
                                                @else 
                                                    {{$rule->frequency_min}}+x 
                                                @endif
                                            </span>
                                            <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 font-bold">{{ $rule->poin }} Poin</span>
                                            @if($rule->trigger_surat)
                                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700 font-bold flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                    SURAT
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-gray-600">
                                            <span class="text-gray-400 font-bold">Sanksi:</span> {{ $rule->sanksi_description }}
                                        </div>
                                        @if($rule->pembina_roles && count($rule->pembina_roles) > 0)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($rule->pembina_roles as $role)
                                                <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold">{{ $role }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 rounded-lg border border-dashed border-gray-200 bg-gray-50 text-center">
                                    <span class="text-xs text-gray-400">Default: {{ $jp->poin }} Poin (Setiap Kejadian)</span>
                                </div>
                            @endif
                        </td>

                        {{-- Status Toggle --}}
                        <td class="text-center">
                            @if($jp->frequencyRules->count() > 0)
                                <span class="badge {{ $jp->is_active ? 'badge-success' : 'badge-neutral' }}">
                                    {{ $jp->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            @else
                                <span class="text-xs text-red-400 italic">No Rules</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            {{-- Desktop: Icon buttons --}}
                            <div class="action-buttons-desktop">
                                <a href="{{ route('frequency-rules.show', $jp->id) }}" class="btn btn-icon btn-outline" title="Kelola Rules">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('jenis-pelanggaran.edit', $jp->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </a>
                                <form action="{{ route('jenis-pelanggaran.destroy', $jp->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus jenis pelanggaran ini?')">
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
                                    <a href="{{ route('frequency-rules.show', $jp->id) }}" class="action-dropdown-item action-dropdown-item--view">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Kelola Rules
                                    </a>
                                    <a href="{{ route('jenis-pelanggaran.edit', $jp->id) }}" class="action-dropdown-item action-dropdown-item--edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        Edit
                                    </a>
                                    <div class="action-dropdown-divider"></div>
                                    <form action="{{ route('jenis-pelanggaran.destroy', $jp->id) }}" method="POST" onsubmit="return confirm('Hapus jenis pelanggaran ini?')">
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
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <h3 class="empty-state-title">Belum Ada Data</h3>
                                <p class="empty-state-description">Tidak ada jenis pelanggaran yang terdaftar.</p>
                                <a href="{{ route('jenis-pelanggaran.create') }}" class="btn btn-primary">Tambah Jenis Pelanggaran</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
