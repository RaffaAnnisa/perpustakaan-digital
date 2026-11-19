<?php

namespace App\Modules\Circulation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Models\Loan;
use App\Modules\Circulation\Models\LoanItem;
use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use Illuminate\Support\Facades\DB;

/**
 * Dasbor lintas modul. Data milik Catalog & Membership hanya diambil lewat Contract.
 */
class DashboardController extends Controller
{
    public function __construct(
        private BookAvailabilityInterface $books,
        private MemberEligibilityInterface $members,
    ) {}

    public function __invoke()
    {
        $today = today()->toDateString();
        $soon = today()->addDays(3)->toDateString();

        $overdue = Loan::with('items')->where('status', 'borrowed')
            ->where('due_date', '<', $today)->orderBy('due_date')->get();

        $dueSoon = Loan::with('items')->where('status', 'borrowed')
            ->whereBetween('due_date', [$today, $soon])->orderBy('due_date')->get();

        $top = LoanItem::select('book_id', DB::raw('count(*) as total'))
            ->groupBy('book_id')->orderByDesc('total')->limit(5)->get();

        $lowStock = $this->books->lowStock(5);

        $memberMap = $this->members->getMembersByIds(
            $overdue->merge($dueSoon)->pluck('member_id')->unique()->all()
        );
        $bookIds = $overdue->merge($dueSoon)->pluck('items')->flatten()->pluck('book_id')
            ->merge($top->pluck('book_id'))->unique()->all();

        return view('circulation::dashboard.index', [
            'summary' => $this->books->summary(),
            'memberCount' => $this->members->countByStatus(),
            'activeLoans' => Loan::where('status', 'borrowed')->count(),
            'unpaidFines' => (int) Fine::whereNull('paid_at')->sum('amount'),
            'runningFines' => $overdue->sum(fn (Loan $l) => $l->runningFine()),
            'overdue' => $overdue,
            'dueSoon' => $dueSoon,
            'top' => $top,
            'lowStock' => $lowStock,
            'memberMap' => $memberMap,
            'bookMap' => $this->books->getBooksByIds($bookIds),
        ]);
    }
}
