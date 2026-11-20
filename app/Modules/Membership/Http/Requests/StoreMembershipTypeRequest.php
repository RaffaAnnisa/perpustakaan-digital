<?php

namespace App\Modules\Membership\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:membership_types,name'],
            'max_loans' => ['required', 'integer', 'min:1', 'max:20'],
            'loan_days' => ['required', 'integer', 'min:1', 'max:90'],
            'fine_per_day' => ['required', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis keanggotaan wajib diisi.',
            'name.unique' => 'Nama jenis keanggotaan ini sudah ada.',
            'max_loans.required' => 'Batas jumlah buku wajib diisi.',
            'max_loans.min' => 'Batas jumlah buku minimal 1.',
            'max_loans.max' => 'Batas jumlah buku maksimal 20.',
            'loan_days.required' => 'Lama peminjaman wajib diisi.',
            'loan_days.min' => 'Lama peminjaman minimal 1 hari.',
            'loan_days.max' => 'Lama peminjaman maksimal 90 hari.',
            'fine_per_day.required' => 'Tarif denda per hari wajib diisi (isi 0 jika tidak ada denda).',
            'fine_per_day.min' => 'Tarif denda tidak boleh negatif.',
            'fine_per_day.max' => 'Tarif denda maksimal Rp 100.000 per hari.',
            '*.integer' => 'Isian harus berupa angka bulat.',
        ];
    }
}
