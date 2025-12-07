<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JurusanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $jurusan = Jurusan::withCount(['kelas'])->withCount(['siswa'])->orderBy('nama_jurusan')->get();
        return view('jurusan.index', ['jurusanList' => $jurusan]);
    }

    public function create()
    {
        return view('jurusan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_jurusan' => 'required|string|max:191',
            'kode_jurusan' => 'nullable|string|max:20|unique:jurusan,kode_jurusan',
            'kaprodi_user_id' => 'nullable|exists:users,id',
        ]);

        if (empty($data['kode_jurusan'])) {
            $data['kode_jurusan'] = $this->generateKode($data['nama_jurusan']);

            // ensure unique by appending number if necessary
            $base = $data['kode_jurusan'];
            $i = 1;
            while (Jurusan::where('kode_jurusan', $data['kode_jurusan'])->exists()) {
                $i++;
                $data['kode_jurusan'] = $base . $i;
            }
        }

        // Simpan jurusan terlebih dahulu
        $jurusan = Jurusan::create($data);

        // Jika operator meminta pembuatan akun Kaprodi otomatis saat create, buat user baru
        if ($request->has('create_kaprodi') && $request->boolean('create_kaprodi')) {
            $kode = $jurusan->kode_jurusan ?? $this->generateKode($jurusan->nama_jurusan);
            $cleanKode = preg_replace('/[^a-z0-9]+/i', '', (string) $kode);
            $cleanKode = Str::lower($cleanKode);
            if ($cleanKode === '') {
                $cleanKode = Str::lower($this->generateKode($jurusan->nama_jurusan));
            }
            $baseUsername = 'kaprodi.' . $cleanKode;
            $username = $baseUsername;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $i++;
                $username = $baseUsername . $i;
            }

            $password = Str::random(10);

            $role = Role::findByName('Kaprodi');
            $user = User::create([
                'role_id' => $role?->id,
                'nama' => 'Kaprodi ' . $jurusan->nama_jurusan,
                'username' => $username,
                'email' => $username . '@no-reply.local',
                'password' => $password,
            ]);

            // Hubungkan kaprodi ke jurusan
            $jurusan->kaprodi_user_id = $user->id;
            $jurusan->save();

            // Flash kredensial supaya operator melihatnya setelah redirect
            session()->flash('kaprodi_created', ['username' => $username, 'password' => $password]);
        }

        return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil dibuat.');
    }

    public function show(Jurusan $jurusan)
    {
        $jurusan->load(['kaprodi', 'kelas.siswa']);
        return view('jurusan.show', ['jurusan' => $jurusan]);
    }

    public function edit(Jurusan $jurusan)
    {
        return view('jurusan.edit', ['jurusan' => $jurusan]);
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $data = $request->validate([
            'nama_jurusan' => 'required|string|max:191',
            'kode_jurusan' => 'nullable|string|max:20|unique:jurusan,kode_jurusan,' . $jurusan->id,
            'kaprodi_user_id' => 'nullable|exists:users,id',
        ]);

        // Jika kode dikosongkan pada form, coba generate dari nama
        if (empty($data['kode_jurusan'])) {
            $data['kode_jurusan'] = $this->generateKode($data['nama_jurusan']);

            // pastikan unik
            $base = $data['kode_jurusan'];
            $i = 1;
            while (Jurusan::where('kode_jurusan', $data['kode_jurusan'])->where('id', '!=', $jurusan->id)->exists()) {
                $i++;
                $data['kode_jurusan'] = $base . $i;
            }
        }

        DB::transaction(function () use ($jurusan, $data, $request) {
            $oldKode = $jurusan->kode_jurusan;
            $jurusan->fill($data);
            $jurusan->save();

            // Jika kode berubah, propagasi ke kelas terkait
            $newKode = $jurusan->kode_jurusan;
            if ($newKode !== $oldKode) {
                $kelasByTingkat = $jurusan->kelas()->orderBy('id')->get()->groupBy('tingkat');
                foreach ($kelasByTingkat as $tingkat => $kelasGroup) {
                    $seq = 0;
                    foreach ($kelasGroup as $kelas) {
                        $seq++;
                        $kelas->nama_kelas = trim($kelas->tingkat . ' ' . $newKode . ' ' . $seq);
                        $kelas->save();

                        // Jika kelas memiliki wali, perbarui akun wali agar username/nama mengikuti format baru
                        if ($kelas->wali_kelas_user_id) {
                            $wali = User::find($kelas->wali_kelas_user_id);
                            if ($wali) {
                                $tingkatShort = Str::lower($kelas->tingkat);
                                $kodeSafe = preg_replace('/[^a-z0-9]+/i', '', (string) $newKode);
                                $kodeSafe = Str::lower($kodeSafe);
                                if ($kodeSafe === '') {
                                    $kodeSafe = Str::lower($this->generateKode($jurusan->nama_jurusan));
                                }
                                $baseWaliUsername = "walikelas.{$tingkatShort}.{$kodeSafe}{$seq}";
                                $newWaliUsername = $baseWaliUsername;
                                $j = 1;
                                while (User::where('username', $newWaliUsername)->where('id', '!=', $wali->id)->exists()) {
                                    $j++;
                                    $newWaliUsername = $baseWaliUsername . $j;
                                }
                                $wali->username = $newWaliUsername;
                                $wali->nama = 'Wali Kelas ' . $kelas->nama_kelas;
                                $wali->save();
                            }
                        }
                    }
                }
            }

            // Jika Kaprodi sudah ada, dan kode berubah, perbarui username Kaprodi supaya mengikuti kode baru
            if ($jurusan->kaprodi_user_id) {
                $kaprodi = User::find($jurusan->kaprodi_user_id);
                if ($kaprodi) {
                    $rawKode = $jurusan->kode_jurusan ?? $this->generateKode($jurusan->nama_jurusan);
                    $cleanKode = preg_replace('/[^a-z0-9]+/i', '', (string) $rawKode);
                    $cleanKode = Str::lower($cleanKode);
                    if ($cleanKode === '') {
                        $cleanKode = Str::lower($this->generateKode($jurusan->nama_jurusan));
                    }
                    $desiredBase = 'kaprodi.' . $cleanKode;
                    $newUsername = $desiredBase;
                    $i = 1;
                    while (User::where('username', $newUsername)->where('id', '!=', $kaprodi->id)->exists()) {
                        $i++;
                        $newUsername = $desiredBase . $i;
                    }
                    // update username & display name
                    $kaprodi->username = $newUsername;
                    $kaprodi->nama = 'Kaprodi ' . $jurusan->nama_jurusan;
                    $kaprodi->save();
                }
            } else {
                // Jika Kaprodi belum ada tapi operator meminta pembuatan akun saat edit
                if ($request->has('create_kaprodi') && $request->boolean('create_kaprodi')) {
                    $kode = $jurusan->kode_jurusan ?? $this->generateKode($jurusan->nama_jurusan);
                    $cleanKode = preg_replace('/[^a-z0-9]+/i', '', (string) $kode);
                    $cleanKode = Str::lower($cleanKode);
                    if ($cleanKode === '') {
                        $cleanKode = Str::lower($this->generateKode($jurusan->nama_jurusan));
                    }
                    $baseUsername = 'kaprodi.' . $cleanKode;
                    $username = $baseUsername;
                    $i = 1;
                    while (User::where('username', $username)->exists()) {
                        $i++;
                        $username = $baseUsername . $i;
                    }

                    // Password standardized: smkn1.kaprodi.{kode_jurusan}
                    $password = 'smkn1.kaprodi.' . $cleanKode;
                    $role = Role::findByName('Kaprodi');
                    $user = User::create([
                        'role_id' => $role?->id,
                        'nama' => 'Kaprodi ' . $jurusan->nama_jurusan,
                        'username' => $username,
                        'email' => $username . '@no-reply.local',
                        'password' => $password,
                    ]);

                    $jurusan->kaprodi_user_id = $user->id;
                    $jurusan->save();
                    session()->flash('kaprodi_created', ['username' => $username, 'password' => $password]);
                }
            }
        });

        return redirect()->route('jurusan.show', $jurusan)->with('success', 'Jurusan diperbarui. Perubahan nama kode telah dipropagasi ke kelas terkait.');
    }

    public function destroy(Jurusan $jurusan)
    {
        // Prevent deletion if there are kelas or siswa
        $kelasCount = $jurusan->kelas()->count();
        $siswaCount = $jurusan->siswa()->count();
        if ($kelasCount > 0 || $siswaCount > 0) {
            return redirect()->route('jurusan.index')->with('error', 'Tidak dapat menghapus jurusan yang memiliki kelas atau siswa.');
        }

        $jurusan->delete();
        return redirect()->route('jurusan.index')->with('success', 'Jurusan dihapus.');
    }

    /**
     * Generate kode_jurusan from a name by taking initials (up to 3 chars)
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
}

