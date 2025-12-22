{{-- Single Delete Siswa Modal --}}
<div class="modal fade" id="deleteSingleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="deleteSingleForm" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-trash-alt"></i>
                        Hapus Siswa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                
                <div class="modal-body">
                    <p class="mb-3">Anda akan menghapus siswa: <strong id="deleteSiswaName"></strong></p>
                    
                    <div class="form-group">
                        <label>Alasan Keluar: <span class="text-danger">*</span></label>
                        <select name="alasan_keluar" class="form-control" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="Alumni">🎓 Alumni (Lulus)</option>
                            <option value="Dikeluarkan">🚪 Dikeluarkan (Drop Out)</option>
                            <option value="Pindah Sekolah">🏫 Pindah Sekolah</option>
                            <option value="Lainnya">❓ Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan (Opsional):</label>
                        <textarea name="keterangan_keluar" class="form-control" rows="2" 
                                  placeholder="Contoh: Lulus tahun 2024"></textarea>
                        <small class="form-text text-muted">Maksimal 500 karakter</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(siswaId, namaSiswa) {
    document.getElementById('deleteSiswaName').textContent = namaSiswa;
    document.getElementById('deleteSingleForm').action = `/siswa/${siswaId}`;
    $('#deleteSingleModal').modal('show');
}
</script>
