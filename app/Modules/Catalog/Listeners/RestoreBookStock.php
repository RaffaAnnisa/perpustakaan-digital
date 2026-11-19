<?php

namespace App\Modules\Catalog\Listeners;

use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Circulation\Events\BookReturned;

class RestoreBookStock
{
    public function __construct(private BookAvailabilityInterface $books) {}

    public function handle(BookReturned $event): void
    {
        $this->books->restoreStock($event->bookId);
    }
}
