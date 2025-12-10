<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update User Request
 * 
 * Validation for updating existing user.
 * Authorization: Only Operator Sekolah and Kepala Sekolah.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['Operator Sekolah', 'Kepala Sekolah']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user'); // From route parameter

        return [
            'role_id' => ['sometimes', 'exists:roles,id'],
            'nama' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:20'],
            'nuptk' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            
            // Role-specific assignments
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'jurusan_id' => ['nullable', 'exists:jurusan,id'],
            
            // Siswa linking for Wali Murid/Developer roles
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'role_id' => 'Role',
            'nama' => 'Nama Lengkap',
            'username' => 'Username',
            'email' => 'Email',
            'phone' => 'Nomor HP',
            'nip' => 'NIP',
            'nuptk' => 'NUPTK',
            'is_active' => 'Status Aktif',
        ];
    }
}
