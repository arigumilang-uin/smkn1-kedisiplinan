<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Jurusan Request
 * 
 * Purpose: Validation for updating existing jurusan
 * Pattern: FormRequest (Laravel)
 * Responsibility: Validation ONLY
 */
class UpdateJurusanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled by middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * EXACT rules from original controller (lines 106-110)
     */
    public function rules(): array
    {
        $jurusanId = $this->route('jurusan')?->id;
        
        return [
            'nama_jurusan' => ['required', 'string', 'max:191'],
            'kode_jurusan' => ['nullable', 'string', 'max:20', 'unique:jurusan,kode_jurusan,' . $jurusanId],
            'kaprodi_user_id' => ['nullable', 'exists:users,id'],
            'create_kaprodi' => ['nullable', 'boolean'], // For creating kaprodi during update
            
            // Program Keahlian fields
            'program_keahlian_id' => ['nullable', 'exists:program_keahlian,id'],
            'tingkat' => ['nullable', 'string', 'in:X,XI,XII'],
            'create_program' => ['nullable', 'boolean'],
            'new_program_nama' => ['nullable', 'string', 'max:191', 'required_if:create_program,1,true'],
            'new_program_kode' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nama_jurusan' => 'Nama Jurusan',
            'kode_jurusan' => 'Kode Jurusan',
            'kaprodi_user_id' => 'Kaprodi',
        ];
    }
}
