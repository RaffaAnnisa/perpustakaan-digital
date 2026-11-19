<?php

namespace App\Modules\Membership\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class LoanLimitExceededException extends RuntimeException
{
    public function __construct(string $memberName, int $max, int $current)
    {
        parent::__construct(
            "Anggota '{$memberName}' melebihi batas peminjaman: maksimal {$max} buku, sedang meminjam {$current}."
        );
    }

    public function render(Request $request): RedirectResponse
    {
        return back()->withInput()->with('error', $this->getMessage());
    }
}
