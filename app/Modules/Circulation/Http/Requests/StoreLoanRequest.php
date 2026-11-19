<?php

namespace App\Modules\Circulation\Http\Requests;

use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Keberadaan data dicek lewat Contract, bukan "exists:" ke tabel modul lain
            'member_id' => ['required', 'integer', function ($attr, $value, $fail) {
                if (! app(MemberEligibilityInterface::class)->getMembersByIds([$value])) {
                    $fail('Anggota tidak ditemukan.');
                }
            }],
            'book_ids' => ['required', 'array', 'min:1'],
            'book_ids.*' => ['integer', function ($attr, $value, $fail) {
                if (! app(BookAvailabilityInterface::class)->getBooksByIds([$value])) {
                    $fail('Buku dengan ID '.$value.' tidak ditemukan.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Pilih anggota peminjam.',
            'book_ids.required' => 'Pilih minimal satu buku untuk dipinjam.',
            'book_ids.min' => 'Pilih minimal satu buku untuk dipinjam.',
        ];
    }
}
