<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\StoreBookRequest;
use App\Modules\Catalog\Http\Requests\UpdateBookRequest;
use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');

        $books = Book::with(['category', 'authors'])
            ->when($q !== '', fn ($b) => $b->where(fn ($w) => $w
                ->where('title', 'like', "%{$q}%")
                ->orWhere('isbn', 'like', "%{$q}%")
                ->orWhereHas('authors', fn ($a) => $a->where('name', 'like', "%{$q}%"))))
            ->when($categoryId, fn ($b) => $b->where('category_id', $categoryId))
            ->orderBy('title')
            ->get();

        return view('catalog::books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
            'q' => $q,
            'categoryId' => $categoryId,
        ]);
    }

    public function create()
    {
        return view('catalog::books.create', [
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $book = Book::create([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'isbn' => $data['isbn'],
                'stock' => $data['stock'],
                'available_stock' => $data['stock'],
            ]);
            $book->authors()->sync($data['author_ids']);
        });

        return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function edit(Book $book)
    {
        return view('catalog::books.edit', [
            'book' => $book->load('authors'),
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $book) {
            // selisih total stok ikut menggeser stok tersedia
            $diff = $data['stock'] - $book->stock;
            $book->update([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'isbn' => $data['isbn'],
                'stock' => $data['stock'],
                'available_stock' => max(0, $book->available_stock + $diff),
            ]);
            $book->authors()->sync($data['author_ids']);
        });

        return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus.');
    }
}
