<?php

namespace App\Modules\Circulation\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Domain Event: satu buku sudah dikembalikan.
 * Hanya membawa data primitif; Catalog yang mendengarkan dan menambah stok.
 */
class BookReturned
{
    use Dispatchable;

    public function __construct(
        public readonly int $loanId,
        public readonly int $bookId,
    ) {}
}
