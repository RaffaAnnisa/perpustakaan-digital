<?php

namespace App\Modules\Catalog\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class BookOutOfStockException extends RuntimeException
{
    public function __construct(public readonly string $title, public readonly int $available)
    {
        parent::__construct("Stok buku '{$title}' habis (tersisa {$available}).");
    }

    // Laravel memanggil render() otomatis saat exception ini lolos ke handler
    public function render(Request $request): RedirectResponse
    {
        return back()->withInput()->with('error', $this->getMessage());
    }
}
