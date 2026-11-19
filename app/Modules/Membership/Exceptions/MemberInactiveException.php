<?php

namespace App\Modules\Membership\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MemberInactiveException extends RuntimeException
{
    public function __construct(public readonly string $memberName)
    {
        parent::__construct("Anggota '{$memberName}' berstatus nonaktif dan tidak dapat meminjam buku.");
    }

    public function render(Request $request): RedirectResponse
    {
        return back()->withInput()->with('error', $this->getMessage());
    }
}
