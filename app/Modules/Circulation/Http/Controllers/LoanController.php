<?php

namespace App\Modules\Circulation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Circulation\Http\Requests\StoreLoanRequest;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Models\Loan;
use App\Modules\Circulation\Models\LoanItem;
use App\Modules\Circulation\Services\LoanService;
use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        private LoanService $loans,
        private BookAvailabilityInterface $books,
        private MemberEligibilityInterface $members,
    ) {}

    public function index(Request $request)
    {
        $filter = in_array($request->query('status'), ['borrowed', 'overdue', 'returned'], true)
            ? $request->query('status') : 'all';
        $today = today()->toDateString();

        $counts = [
            'all' => Loan::count(),
            'borrowed' => Loan::where('status', 'borrowed')->count(),
            'overdue' => Loan::where('status', 'borrowed')->where('due_date', '<', $today)->count(),
            'returned' => Loan::where('status', 'returned')->count(),
        ];

        // ringkasan keterlambatan untuk banner di atas tabel (tidak terpengaruh filter)
        $overdueLoans = Loan::where('status', 'borrowed')->where('due_date', '<', $today)->get();
        $overdueTotal = (int) $overdueLoans->sum(fn (Loan $l) => $l->runningFine());

        $query = Loan::with(['items', 'fine'])->latest('id');
        if ($filter === 'borrowed') {
            $query->where('status', 'borrowed');
        } elseif ($filter === 'overdue') {
            $query->where('status', 'borrowed')->where('due_date', '<', $today);
        } elseif ($filter === 'returned') {
            $query->where('status', 'returned');
        }
        $loans = $query->get();

        // Nama anggota & judul buku diambil lewat Contract
        $memberMap = $this->members->getMembersByIds($loans->pluck('member_id')->unique()->all());
        $bookMap = $this->books->getBooksByIds($loans->pluck('items')->flatten()->pluck('book_id')->unique()->all());

        return view('circulation::loans.index', compact('loans', 'memberMap', 'bookMap', 'filter', 'counts', 'overdueTotal'));
    }

    public function create()
    {
        // jumlah buku yang sedang dipinjam tiap anggota (tabel milik Circulation sendiri)
        $activeCounts = LoanItem::whereNull('loan_items.returned_at')
            ->join('loans', 'loans.id', '=', 'loan_items.loan_id')
            ->selectRaw('loans.member_id as member_id, count(*) as total')
            ->groupBy('loans.member_id')
            ->pluck('total', 'member_id')->all();

        return view('circulation::loans.create', [
            'members' => $this->members->listActive(),
            'books' => $this->books->listAvailable(),
            'activeCounts' => $activeCounts,
        ]);
    }

    public function store(StoreLoanRequest $request)
    {
        $loan = $this->loans->borrow(
            (int) $request->validated('member_id'),
            $request->validated('book_ids'),
            $request->user()->id,
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', "Peminjaman #{$loan->id} berhasil. Jatuh tempo {$loan->due_date->format('d M Y')}.");
    }

    public function show(Loan $loan)
    {
        $loan->load(['items', 'fine']);

        return view('circulation::loans.show', [
            'loan' => $loan,
            'member' => $this->members->getMembersByIds([$loan->member_id])[$loan->member_id] ?? null,
            'bookMap' => $this->books->getBooksByIds($loan->items->pluck('book_id')->all()),
            'staff' => $loan->processed_by ? User::find($loan->processed_by) : null,
        ]);
    }

    public function returnLoan(Loan $loan)
    {
        $loan = $this->loans->return($loan->id);

        $msg = "Peminjaman #{$loan->id} telah dikembalikan.";
        if ($loan->fine) {
            $msg .= ' Denda keterlambatan '.$loan->fine->days_late.' hari: Rp '.number_format($loan->fine->amount, 0, ',', '.').'.';
        }

        return redirect()->route('loans.index')->with('success', $msg);
    }

    public function payFine(Fine $fine)
    {
        $this->loans->payFine($fine->id);

        return back()->with('success', 'Denda ditandai lunas.');
    }
}
