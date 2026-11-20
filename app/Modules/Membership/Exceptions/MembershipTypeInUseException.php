<?php

namespace App\Modules\Membership\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MembershipTypeInUseException extends RuntimeException
{
    public function __construct(string $name, int $count)
    {
        parent::__construct("Jenis keanggotaan '{$name}' tidak dapat dihapus karena masih dipakai oleh {$count} anggota.");
    }

    public function render(Request $request): RedirectResponse
    {
        return back()->with('error', $this->getMessage());
    }
}
