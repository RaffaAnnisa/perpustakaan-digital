<?php

namespace App\Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi ditangani middleware role pada route
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:20', 'unique:books,isbn'],
            'category_id' => ['required', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'author_ids' => ['required', 'array', 'min:1'],
            'author_ids.*' => ['exists:authors,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku wajib diisi.',
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.unique' => 'ISBN ini sudah terdaftar pada buku lain.',
            'category_id.required' => 'Pilih kategori buku.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'author_ids.required' => 'Pilih minimal satu penulis.',
        ];
    }
}
