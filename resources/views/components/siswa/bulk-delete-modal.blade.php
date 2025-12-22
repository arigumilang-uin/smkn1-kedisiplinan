{{-- Bulk Delete Siswa Modal Component --}}
{{-- Clean Architecture: Reusable UI component --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('siswa.bulk-delete') }}" method="POST" 
                  onsubmit="return confirm('⚠️ YAKIN menghapus SEMUA siswa di kelas ini?\n\nTindakan ini TIDAK BISA dibatalkan!')">
                @csrf
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Hapus Siswa Per Kelas
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    {{-- Warning Alert --}}
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> INFORMASI PENTING!</strong>
                        <p class="mb-2">Fitur ini akan <strong>SOFT DELETE</strong> semua siswa dalam satu kelas beserta:</p>
                        <ul class="mb-2">
                            <li>Semua riwayat pelanggaran siswa</li>
                            <li>Semua tindak lanjut & surat panggilan</li>
                        </ul>
                        <p class="mb-0 text-success"><strong><i class="fas fa-check-circle"></i> Data masih bisa di-RESTORE</strong> dari menu "Data Terhapus"</p>
                        <small class="text-muted">Delete permanent hanya bisa dilakukan dari halaman Data Terhapus</small>
                    </div>
                    
                    {{-- Kelas Selection --}}
                    <div class="form-group">
                        <label for="kelasSelect"><strong>Pilih Kelas:</strong></label>
                        <select name="kelas_id" id="kelasSelect" class="form-control" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($allKelas as $k)
                                @php
                                    $siswaCount = \App\Models\Siswa::where('kelas_id', $k->id)->count();
                                @endphp
                                <option value="{{ $k->id }}" {{ $siswaCount == 0 ? 'disabled' : '' }}>
                                    {{ $k->nama_kelas }} ({{ $siswaCount }} siswa)
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kelas tanpa siswa tidak dapat dipilih</small>
                    </div>
                    
                    {{-- Alasan Keluar --}}
                    <div class="form-group">
                        <label for="alasanKeluarBulk"><strong>Alasan Keluar:</strong> <span class="text-danger">*</span></label>
                        <select name="alasan_keluar" id="alasanKeluarBulk" class="form-control" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="Alumni">🎓 Alumni (Lulus)</option>
                            <option value="Dikeluarkan">🚪 Dikeluarkan (Drop Out)</option>
                            <option value="Pindah Sekolah">🏫 Pindah Sekolah</option>
                            <option value="Lainnya">❓ Lainnya</option>
                        </select>
                    </div>
                    
                    {{-- Keterangan Keluar (Optional) --}}
                    <div class="form-group">
                        <label for="keteranganKeluarBulk">Keterangan (Opsional):</label>
                        <textarea name="keterangan_keluar" id="keteranganKeluarBulk" 
                                  class="form-control" rows="2" 
                                  placeholder="Contoh: Lulus tahun ajaran 2024/2025, Pindah ke SMA Negeri 2"></textarea>
                        <small class="form-text text-muted">Maksimal 500 karakter</small>
                    </div>
                    
                    {{-- Delete Orphaned Wali Option --}}
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" name="delete_orphaned_wali" value="1" 
                               class="custom-control-input" id="deleteOrphanedWaliCheck">
                        <label class="custom-control-label" for="deleteOrphanedWaliCheck">
                            Hapus juga akun Wali Murid yang orphaned
                            <small class="text-muted d-block">
                                (Wali Murid yang tidak memiliki siswa lain setelah penghapusan ini)
                            </small>
                        </label>
                    </div>
                    
                    {{-- Confirmation Checkbox --}}
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="confirm" value="1" 
                               class="custom-control-input" id="confirmCheck" required>
                        <label class="custom-control-label" for="confirmCheck">
                            <strong class="text-danger">
                                Saya memahami bahwa tindakan ini TIDAK DAPAT DIBATALKAN
                            </strong>
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Hapus Semua Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
