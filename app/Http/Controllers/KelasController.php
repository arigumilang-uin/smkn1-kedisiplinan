<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('jurusan','waliKelas')->orderBy('nama_kelas')->get();
        return view('kelas.index', ['kelasList' => $kelas]);
    }

    /**
     * Generate kode from nama (same logic as in JurusanController)
     */
    protected function generateKode(string $nama): string
    {
        $words = preg_split('/\s+/', trim($nama));
        $letters = '';
        foreach ($words as $w) {
            if ($w === '') continue;
            $letters .= strtoupper(mb_substr($w, 0, 1));
            if (mb_strlen($letters) >= 3) break;
        }
        if ($letters === '') {
            $letters = 'JRS';
        }
        return $letters;
    }

    public function create()
    {
        $jurusan = Jurusan::orderBy('nama_jurusan')->get();
        $wali = User::whereHas('role', function($q){ $q->where('nama_role','Wali Kelas'); })->get();
        return view('kelas.create', ['jurusanList' => $jurusan, 'waliList' => $wali]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tingkat' => 'required|string|in:X,XI,XII',
            'jurusan_id' => 'required|integer',
            'wali_kelas_user_id' => 'nullable|integer',
        ]);

        // Determine jurusan code. Prefer explicit kode_jurusan column if present, else compute abbreviation.
        $jurusan = Jurusan::findOrFail($data['jurusan_id']);
        $kode = null;
        if (array_key_exists('kode_jurusan', $jurusan->getAttributes()) && $jurusan->kode_jurusan) {
            $kode = $jurusan->kode_jurusan;
        } else {
            // fallback: build abbreviation from nama_jurusan (take first letters of words, up to 3 chars)
            $words = preg_split('/\s+/', trim($jurusan->nama_jurusan));
            $abbr = '';
            foreach ($words as $w) {
                if ($w === '') continue;
                $abbr .= mb_strtoupper(mb_substr($w, 0, 1));
                if (mb_strlen($abbr) >= 3) break;
            }
            $kode = $abbr ?: strtoupper(substr(preg_replace('/[^A-Z]/', '', $jurusan->nama_jurusan), 0, 3));
        }

        $base = $data['tingkat'] . ' ' . $kode;

        // Find existing kelas with same base and extract numeric suffixes
        $existing = Kelas::where('jurusan_id', $jurusan->id)
            ->where('nama_kelas', 'like', $base . '%')
            ->pluck('nama_kelas')
            ->toArray();

        $max = 0;
        foreach ($existing as $name) {
            if (preg_match('/\s+(\d+)$/', $name, $m)) {
                $num = intval($m[1]);
                if ($num > $max) $max = $num;
            }
        }
        $next = $max + 1;
        $data['nama_kelas'] = $base . ' ' . $next;

        // Simpan kelas terlebih dahulu
        $kelas = Kelas::create($data);

        // Jika diminta, buat akun Wali Kelas otomatis
        if ($request->has('create_wali') && $request->boolean('create_wali')) {
            // username format: walikelas.{tingkat}.{kode}{nomor}  (contoh: walikelas.x.aphp1)
            $tingkat = Str::lower($data['tingkat']);
            $kodeSafe = preg_replace('/[^a-z0-9]+/i', '', (string) $kode);
            $kodeSafe = Str::lower($kodeSafe);
            if ($kodeSafe === '') {
                $kodeSafe = Str::lower($this->generateKode($jurusan->nama_jurusan));
            }
            $nomor = $next; // seq yang sudah dihitung
            $baseUsername = "walikelas.{$tingkat}.{$kodeSafe}{$nomor}";
            $username = $baseUsername;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $i++;
                $username = $baseUsername . $i;
            }

            // Password standardized: smkn1.walikelas.{tingkat}{kode}{nomor}
            $password = 'smkn1.walikelas.' . $tingkat . $kodeSafe . $nomor;
            $role = Role::findByName('Wali Kelas');
            $user = User::create([
                'role_id' => $role?->id,
                'nama' => 'Wali Kelas ' . $kelas->nama_kelas,
                'username' => $username,
                'email' => $username . '@no-reply.local',
                'password' => $password,
            ]);

            // hubungkan user sebagai wali_kelas pada kelas
            $kelas->wali_kelas_user_id = $user->id;
            $kelas->save();

            session()->flash('wali_created', ['username' => $username, 'password' => $password]);
        }

        return redirect()->route('kelas.index')->with('success','Kelas berhasil dibuat: ' . $data['nama_kelas']);
    }

    public function edit(Kelas $kelas)
    {
        $jurusan = Jurusan::orderBy('nama_jurusan')->get();
        $wali = User::whereHas('role', function($q){ $q->where('nama_role','Wali Kelas'); })->get();
        return view('kelas.edit', ['kelas' => $kelas, 'jurusanList' => $jurusan, 'waliList' => $wali]);
    }

    public function show(Kelas $kelas)
    {
        $kelas->load(['jurusan', 'waliKelas', 'siswa']);
        return view('kelas.show', ['kelas' => $kelas]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'tingkat' => 'required|string|in:X,XI,XII',
            'jurusan_id' => 'required|integer',
            'wali_kelas_user_id' => 'nullable|integer',
        ]);

        // Simpan perubahan kelas
        $oldNama = $kelas->nama_kelas;
        $oldTingkat = $kelas->tingkat;
        $oldJurusanId = $kelas->jurusan_id;

        $kelas->update($data);

        // Jika nama kelas / tingkat / jurusan berubah dan kelas memiliki wali, perbarui akun wali supaya konsisten
        if (($kelas->nama_kelas !== $oldNama) || ($kelas->tingkat !== $oldTingkat) || ($kelas->jurusan_id !== $oldJurusanId)) {
            if ($kelas->wali_kelas_user_id) {
                $wali = User::find($kelas->wali_kelas_user_id);
                if ($wali) {
                    // rebuild username format: walikelas.{tingkat}.{kode}{nomor}
                    $jurusan = $kelas->jurusan()->first();
                    $kode = $jurusan?->kode_jurusan ?? '';

                    // Extract nomor suffix from nama_kelas (last number)
                    $nomor = 1;
                    if (preg_match('/\s(\d+)$/', $kelas->nama_kelas, $m)) {
                        $nomor = intval($m[1]);
                    }

                    $tingkat = Str::lower($kelas->tingkat);
                    $kodeSafe = preg_replace('/[^a-z0-9]+/i', '', (string) $kode);
                    $kodeSafe = Str::lower($kodeSafe);
                    if ($kodeSafe === '') {
                        $kodeSafe = Str::lower($this->generateKode($jurusan->nama_jurusan ?? ''));
                    }
                    $baseUsername = "walikelas.{$tingkat}.{$kodeSafe}{$nomor}";
                    $newUsername = $baseUsername;
                    $i = 1;
                    while (User::where('username', $newUsername)->where('id', '!=', $wali->id)->exists()) {
                        $i++;
                        $newUsername = $baseUsername . $i;
                    }

                    $wali->username = $newUsername;
                    $wali->nama = 'Wali Kelas ' . $kelas->nama_kelas;
                    $wali->save();
                }
            }
        }

        return redirect()->route('kelas.index')->with('success','Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('kelas.index')->with('success','Kelas dihapus.');
    }
}
