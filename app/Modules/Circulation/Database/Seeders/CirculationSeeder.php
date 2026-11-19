<?php

namespace App\Modules\Circulation\Database\Seeders;

use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Circulation\Models\Loan;
use App\Modules\Circulation\Services\LoanService;
use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use Illuminate\Database\Seeder;

class CirculationSeeder extends Seeder
{
    public function run(
        LoanService $loans,
        MemberEligibilityInterface $members,
        BookAvailabilityInterface $books,
    ): void {
        // Data contoh dibuat lewat Contract + LoanService, jadi stok ikut konsisten.
        $memberIds = collect($members->listActive())->pluck('id')->values();
        $bookIds = collect($books->listAvailable())->pluck('id')->values();

        // 1) pinjaman normal yang masih berjalan
        $loans->borrow($memberIds[0], [$bookIds[0], $bookIds[1]], null);

        // 2) pinjaman terlambat 4 hari (belum dikembalikan)
        $late = $loans->borrow($memberIds[1], [$bookIds[2]], null);
        Loan::whereKey($late->id)->update(['loan_date' => today()->subDays(11), 'due_date' => today()->subDays(4)]);

        // 3) pinjaman jatuh tempo besok
        $soon = $loans->borrow($memberIds[2], [$bookIds[3]], null);
        Loan::whereKey($soon->id)->update(['due_date' => today()->addDay()]);

        // 4) pinjaman yang dikembalikan terlambat -> menghasilkan denda
        $old = $loans->borrow($memberIds[3], [$bookIds[4]], null);
        Loan::whereKey($old->id)->update(['loan_date' => today()->subDays(15), 'due_date' => today()->subDays(3)]);
        $loans->return($old->id);
    }
}
