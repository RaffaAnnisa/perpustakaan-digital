<?php

namespace App\Modules\Catalog\Contracts;

/**
 * Contract publik modul Catalog.
 * Modul lain TIDAK boleh memakai model Book secara langsung.
 */
interface BookAvailabilityInterface
{
    /** @return array{id:int,title:string,isbn:string,available_stock:int} */
    public function getBookInfo(int $bookId): array;

    /** @return array<int, array{id:int,title:string,isbn:string,available_stock:int}> dikunci berdasarkan id */
    public function getBooksByIds(array $bookIds): array;

    /** Buku yang masih punya stok tersedia. */
    public function listAvailable(): array;

    /** @throws \App\Modules\Catalog\Exceptions\BookOutOfStockException */
    public function reduceStock(int $bookId, int $quantity = 1): void;

    public function restoreStock(int $bookId, int $quantity = 1): void;

    /** @return array{titles:int,copies:int,available:int} ringkasan koleksi untuk dasbor */
    public function summary(): array;

    /** Buku dengan stok tersedia paling sedikit (termasuk yang habis). */
    public function lowStock(int $limit = 5): array;
}
