@extends('layouts.app')

@section('title', 'Tambah User')
@section('subtitle', 'Buat akun pengguna baru.')
@section('page-header', true)

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah User</h3>
        </div>
        <div class="card-body">
            {{-- Role mapping from PHP to JS --}}
            @php
                $roleMap = [];
                foreach($roles ?? [] as $role) {
                    $roleMap[$role->id] = strtolower($role->nama_role);
                }
            @endphp
            
            <form action="{{ route('users.store') }}" method="POST" class="space-y-6"
                  x-data="{ 
                      roleId: '{{ old('role_id', '') }}',
                      roleMap: {{ json_encode($roleMap) }},
                      
                      getRoleName() {
                          return this.roleMap[this.roleId] || '';
                      },
                      needsNipNuptk() {
                          const roles = ['guru', 'waka kesiswaan', 'waka sarana', 'operator sekolah', 'wali kelas', 'kaprodi', 'kepala sekolah'];
                          const name = this.getRoleName();
                          return roles.some(r => name.includes(r));
                      },
                      isWaliKelas() {
                          return this.getRoleName().includes('wali kelas');
                      },
                      isKaprodi() {
                          return this.getRoleName().includes('kaprodi');
                      },
                      isWaliMurid() {
                          return this.getRoleName().includes('wali murid');
                      },
                      isDeveloper() {
                          return this.getRoleName().includes('developer');
                      }
                  }"
            >
                @csrf
                
                {{-- Basic Information --}}
                <div class="p-4 bg-gray-50 rounded-xl space-y-4">
                    <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                        Informasi Dasar
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="username" class="form-label form-label-required">Username</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}"
                                   class="form-input @error('username') error @enderror" required>
                            <p class="form-help">Username untuk login</p>
                            @error('username')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="form-input @error('email') error @enderror">
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group" x-data="{ show: false }">
                            <label for="password" class="form-label form-label-required">Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="password" name="password"
                                       class="form-input !pr-10 @error('password') error @enderror" required>
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group" x-data="{ show: false }">
                            <label for="password_confirmation" class="form-label form-label-required">Konfirmasi Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                                       class="form-input !pr-10" required>
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Role Selection --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="form-group mb-0">
                        <label for="role_id" class="form-label form-label-required">Role Pengguna</label>
                        <select id="role_id" name="role_id" x-model="roleId"
                                class="form-input form-select @error('role_id') error @enderror" required>
                            <option value="">Pilih Role</option>
                            @foreach($roles ?? [] as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->nama_role }}</option>
                            @endforeach
                        </select>
                        <p class="form-help">Role menentukan hak akses dan fitur yang tersedia.</p>
                        @error('role_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                {{-- NIP & NUPTK (untuk Guru, Waka, Kepala Sekolah, etc) --}}
                <div x-show="needsNipNuptk() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-amber-50 rounded-xl border border-amber-100 space-y-4">
                    <h4 class="font-semibold text-amber-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                        Data Kepegawaian
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" id="nip" name="nip" value="{{ old('nip') }}"
                                   class="form-input @error('nip') error @enderror" placeholder="18 digit NIP">
                            @error('nip')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="nuptk" class="form-label">NUPTK</label>
                            <input type="text" id="nuptk" name="nuptk" value="{{ old('nuptk') }}"
                                   class="form-input @error('nuptk') error @enderror" placeholder="16 digit NUPTK">
                            @error('nuptk')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                {{-- Kelas (untuk Wali Kelas) --}}
                <div x-show="isWaliKelas() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="form-group mb-0">
                        <label for="kelas_id" class="form-label">
                            Kelas yang Diampu
                            <span x-show="isWaliKelas()" class="text-red-500">*</span>
                        </label>
                        <select id="kelas_id" name="kelas_id" class="form-input form-select @error('kelas_id') error @enderror">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas ?? [] as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} {{ isset($k->jurusan) ? '(' . $k->jurusan->nama_jurusan . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="form-help">Pilih kelas yang akan menjadi tanggung jawab wali kelas ini.</p>
                        @error('kelas_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                {{-- Jurusan (untuk Kaprodi) --}}
                <div x-show="isKaprodi() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                    <div class="form-group mb-0">
                        <label for="jurusan_id" class="form-label">
                            Jurusan yang Diampu
                            <span x-show="isKaprodi()" class="text-red-500">*</span>
                        </label>
                        <select id="jurusan_id" name="jurusan_id" class="form-input form-select @error('jurusan_id') error @enderror">
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach($jurusan ?? [] as $j)
                                <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>
                        <p class="form-help">Pilih jurusan yang akan menjadi tanggung jawab Kaprodi ini.</p>
                        @error('jurusan_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                {{-- Siswa/Anak (untuk Wali Murid) --}}
                <div x-show="isWaliMurid() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-rose-50 rounded-xl border border-rose-100">
                    <div class="form-group mb-0">
                        <label for="siswa_ids" class="form-label">
                            Siswa/Anak yang Diasuh
                            <span x-show="isWaliMurid()" class="text-red-500">*</span>
                        </label>
                        <p class="text-sm text-gray-500 mb-3">Pilih siswa yang menjadi anak dari wali murid ini.</p>
                        <div class="max-h-48 overflow-y-auto border border-rose-200 rounded-lg p-3 bg-white space-y-2">
                            @forelse($siswa ?? [] as $s)
                                <label class="flex items-center gap-3 p-2 hover:bg-rose-50 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" 
                                           {{ in_array($s->id, old('siswa_ids', [])) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-800">{{ $s->nama_siswa }}</p>
                                        <p class="text-sm text-gray-500">{{ $s->nisn }} • {{ $s->kelas->nama_kelas ?? '-' }}</p>
                                    </div>
                                </label>
                            @empty
                                <p class="text-gray-400 text-sm text-center py-4">Tidak ada data siswa</p>
                            @endforelse
                        </div>
                        @error('siswa_ids')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                {{-- Phone & Status --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="form-input @error('phone') error @enderror" placeholder="08xxxxxxxxxx">
                        @error('phone')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="form-group flex items-end">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-gray-50 rounded-lg w-full">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Aktifkan akun langsung</span>
                        </label>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan User</span>
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
