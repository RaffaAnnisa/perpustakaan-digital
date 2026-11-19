<?php

namespace App\Modules\Circulation\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class LoanAlreadyReturnedException extends RuntimeException
{
    public function __construct(int $loanId)
    {
        parent::__construct("Peminjaman #{$loanId} sudah dikembalikan sebelumnya.");
    }

    public function render(Request $request): RedirectResponse
    {
        return back()->with('error', $this->getMessage());
    }
}
