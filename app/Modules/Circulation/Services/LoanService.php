<?php

namespace App\Modules\Circulation\Services;

use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Circulation\Events\BookReturned;
use App\Modules\Circulation\Exceptions\LoanAlreadyReturnedException;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Models\Loan;
use App\Modules\Circulation\Models\LoanItem;
use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private BookAvailabilityInterface $books,
        private MemberEligibilityInterface $members,
    ) {}

    /**
     * Peminjaman. Jika salah satu langkah gagal (anggota nonaktif, batas pinjam,
     * stok habis), semua perubahan di-rollback.
     */
    public function borrow(int $memberId, array $bookIds, ?int $processedBy): Loan
    {
        $bookIds = array_values(array_unique($bookIds));

        return DB::transaction(function () use ($memberId, $bookIds, $processedBy) {
            $activeItems = LoanItem::whereNull('returned_at')
                ->whereHas('loan', fn ($q) => $q->where('member_id', $memberId))
                ->count();

            // Contract Membership: lempar exception bila tidak boleh meminjam
            $rules = $this->members->assertCanBorrow($memberId, $activeItems, count($bookIds));

            $loan = Loan::create([
                'member_id' => $memberId,
                'processed_by' => $processedBy,
                'loan_date' => today(),
                'due_date' => today()->addDays($rules['loan_days']),
                'fine_per_day' => $rules['fine_per_day'], // snapshot tarif
                'status' => 'borrowed',
            ]);

            foreach ($bookIds as $bookId) {
                // Contract Catalog: lempar BookOutOfStockException bila stok habis
                $this->books->reduceStock($bookId, 1);
                $loan->items()->create(['book_id' => $bookId]);
            }

            return $loan;
        });
    }

    /**
     * Pengembalian: hitung denda, tandai kembali, lalu umumkan event
     * SETELAH transaksi commit agar listener tidak bereaksi pada data yang di-rollback.
     */
    public function return(int $loanId): Loan
    {
        $returnedBookIds = [];

        $loan = DB::transaction(function () use ($loanId, &$returnedBookIds) {
            $loan = Loan::with('items')->lockForUpdate()->findOrFail($loanId);

            if ($loan->status === 'returned') {
                throw new LoanAlreadyReturnedException($loan->id);
            }

            $daysLate = (int) max(0, $loan->due_date->diffInDays(today(), false));

            if ($daysLate > 0) {
                Fine::create([
                    'loan_id' => $loan->id,
                    'days_late' => $daysLate,
                    'amount' => $daysLate * $loan->fine_per_day,
                ]);
            }

            $loan->items()->whereNull('returned_at')->update(['returned_at' => now()]);
            $loan->update(['status' => 'returned', 'returned_at' => now()]);

            $returnedBookIds = $loan->items->pluck('book_id')->all();

            return $loan;
        });

        foreach ($returnedBookIds as $bookId) {
            BookReturned::dispatch($loan->id, $bookId);
        }

        return $loan->load('fine');
    }

    /** Tandai denda lunas. Idempotent: memanggil dua kali tidak mengubah waktu pelunasan. */
    public function payFine(int $fineId): Fine
    {
        return DB::transaction(function () use ($fineId) {
            $fine = Fine::lockForUpdate()->findOrFail($fineId);

            if ($fine->paid_at === null) {
                $fine->update(['paid_at' => now()]);
            }

            return $fine;
        });
    }
}
