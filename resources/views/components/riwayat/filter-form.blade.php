{{-- Filter Form Partial for Riwayat Pelanggaran --}}
{{-- Usage: @include('components.riwayat.filter-form') --}}

<form id="filterForm" action="{{ route('riwayat.index') }}" method="GET">
    <div class="row align-items-end">
        
        {{-- Filter Tanggal (Date Range) --}}
        <div class="col-md-3 mb-2">
            <label class="filter-label">
                <i class="fas fa-calendar-alt mr-1"></i> Rentang Waktu
            </label>
            <div class="input-group input-group-sm">
                <input 
                    type="date" 
                    name="start_date" 
                    data-filter="start_date"
                    value="{{ request('start_date') }}" 
                    class="form-control form-control-clean"
                    title="Tanggal mulai">
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text border-left-0 border-right-0 bg-white">
                        <i class="fas fa-arrow-right text-muted small"></i>
                    </span>
                </div>
                <input 
                    type="date" 
                    name="end_date" 
                    data-filter="end_date"
                    value="{{ request('end_date') }}" 
                    class="form-control form-control-clean"
                    title="Tanggal akhir">
            </div>
        </div>

        {{-- Filter Kelas (Admin Only) --}}
        @if(! Auth::user()->hasRole('Wali Kelas'))
        <div class="col-md-2 mb-2">
            <label class="filter-label">
                <i class="fas fa-layer-group mr-1"></i> Kelas
            </label>
            <select 
                name="kelas_id" 
                data-filter="kelas_id"
                class="form-control form-control-sm form-control-clean"
                title="Pilih kelas">
                <option value="">- Semua -</option>
                @foreach($allKelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kelas }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Filter Jenis Pelanggaran --}}
        <div class="col-md-4 mb-2">
            <label class="filter-label">
                <i class="fas fa-list-check mr-1"></i> Jenis Pelanggaran
            </label>
            <select 
                name="jenis_pelanggaran_id" 
                data-filter="jenis_pelanggaran_id"
                class="form-control form-control-sm form-control-clean"
                title="Pilih jenis pelanggaran">
                <option value="">- Semua Jenis -</option>
                @foreach($allPelanggaran as $jp)
                    <option value="{{ $jp->id }}" {{ request('jenis_pelanggaran_id') == $jp->id ? 'selected' : '' }}>
                        [{{ $jp->kategoriPelanggaran->nama_kategori }}] {{ $jp->nama_pelanggaran }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Live Search (Cari Siswa) --}}
        <div class="col-md-3 mb-2">
            <label class="filter-label">
                <i class="fas fa-search mr-1"></i> Cari Siswa
            </label>
            <div class="input-group input-group-sm">
                <input 
                    type="text" 
                    id="liveSearch" 
                    name="cari_siswa" 
                    data-filter="cari_siswa"
                    class="form-control form-control-clean" 
                    placeholder="Ketik nama..."
                    value="{{ request('cari_siswa') }}"
                    title="Cari nama siswa">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit" id="searchBtn" title="Cari">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- Reset Button (Visible only when filters are active) --}}
    @if(request()->has('cari_siswa') || request()->has('start_date') || request()->has('end_date') || request()->has('jenis_pelanggaran_id') || request()->has('kelas_id'))
    <div class="row mt-1 pt-2 border-top">
        <div class="col-12 text-right">
            <a 
                href="{{ route('riwayat.index') }}" 
                class="btn btn-default btn-xs shadow-sm text-danger font-weight-bold filter-reset-btn"
                title="Hapus semua filter">
                <i class="fas fa-times-circle mr-1"></i> Hapus Filter
            </a>
        </div>
    </div>
    @endif
</form>
