<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Catalog\Exceptions\BookOutOfStockException;
use App\Modules\Catalog\Models\Book;

class BookAvailabilityService implements BookAvailabilityInterface
{
    public function getBookInfo(int $bookId): array
    {
        return $this->toArray(Book::findOrFail($bookId));
    }

    public function getBooksByIds(array $bookIds): array
    {
        return Book::whereIn('id', $bookIds)->get()
            ->mapWithKeys(fn (Book $b) => [$b->id => $this->toArray($b)])
            ->all();
    }

    public function listAvailable(): array
    {
        return Book::where('available_stock', '>', 0)->orderBy('title')->get()
            ->map(fn (Book $b) => $this->toArray($b))
            ->all();
    }

    public function reduceStock(int $bookId, int $quantity = 1): void
    {
        // lockForUpdate mencegah dua peminjaman bersamaan mengambil stok terakhir
        $book = Book::lockForUpdate()->findOrFail($bookId);

        if ($book->available_stock < $quantity) {
            throw new BookOutOfStockException($book->title, $book->available_stock);
        }

        $book->decrement('available_stock', $quantity);
    }

    public function restoreStock(int $bookId, int $quantity = 1): void
    {
        $book = Book::lockForUpdate()->findOrFail($bookId);

        // jangan melebihi total stok
        $book->available_stock = min($book->stock, $book->available_stock + $quantity);
        $book->save();
    }

    public function summary(): array
    {
        return [
            'titles' => Book::count(),
            'copies' => (int) Book::sum('stock'),
            'available' => (int) Book::sum('available_stock'),
        ];
    }

    public function lowStock(int $limit = 5): array
    {
        return Book::orderBy('available_stock')->orderBy('title')->limit($limit)->get()
            ->map(fn (Book $b) => $this->toArray($b) + ['stock' => $b->stock])
            ->all();
    }

    private function toArray(Book $book): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'available_stock' => $book->available_stock,
        ];
    }
}
