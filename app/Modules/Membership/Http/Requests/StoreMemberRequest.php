<?php

namespace App\Modules\Membership\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'member_code' => ['required', 'string', 'max:30', 'unique:members,member_code'],
            'email' => ['nullable', 'email', 'max:255'],
            'membership_type_id' => ['required', 'exists:membership_types,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama anggota wajib diisi.',
            'member_code.required' => 'Kode anggota wajib diisi.',
            'member_code.unique' => 'Kode anggota ini sudah dipakai.',
            'email.email' => 'Format email tidak valid.',
            'membership_type_id.required' => 'Pilih jenis keanggotaan.',
            'status.in' => 'Status harus active atau inactive.',
        ];
    }
}
