<!-- PDF Report Template -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; margin-bottom: 5px; }
        .meta { text-align: center; margin-bottom: 20px; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .footer { text-align: right; margin-top: 30px; font-size: 12px; }
    </style>
</head>
<body>
    <h1>LAPORAN PELANGGARAN SISWA</h1>
    <div class="meta">
        <p>Tanggal Laporan: {{ now()->format('d M Y H:i') }}</p>
        @if(!empty($filters['periode_mulai']) && !empty($filters['periode_akhir']))
            <p>Periode: {{ \Carbon\Carbon::parse($filters['periode_mulai'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($filters['periode_akhir'])->format('d M Y') }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 10%">NISN</th>
                <th style="width: 20%">Nama Siswa</th>
                <th style="width: 15%">Kelas</th>
                <th style="width: 15%">Jurusan</th>
                <th style="width: 20%">Jenis Pelanggaran</th>
                <th style="width: 15%">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $idx => $row)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $row->siswa->nisn ?? '-' }}</td>
                <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $row->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</td>
                <td>{{ $row->jenisPelanggaran->nama ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal_kejadian)->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total: {{ count($data) }} record</p>
        <p>&copy; {{ now()->year }} - Sistem Kedisiplinan SMKN 1 Cirebon</p>
    </div>
</body>
</html>
