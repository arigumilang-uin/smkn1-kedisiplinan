<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create User Request
 * 
 * Validation for creating new user.
 * Authorization: Only Operator Sekolah and Kepala Sekolah.
 */
class CreateUserRequest extends FormRequest
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
        return [
            'role_id' => ['required', 'exists:roles,id'],
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:20'],
            'nuptk' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
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
            'password' => 'Password',
            'is_active' => 'Status Aktif',
        ];
    }
}
