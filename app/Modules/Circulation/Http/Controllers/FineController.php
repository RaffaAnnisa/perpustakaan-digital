<?php

namespace App\Modules\Circulation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use Illuminate\Http\Request;

/**
 * Riwayat denda. Nama anggota & judul buku diambil lewat Contract,
 * bukan lewat model modul lain.
 */
class FineController extends Controller
{
    public function __construct(
        private BookAvailabilityInterface $books,
        private MemberEligibilityInterface $members,
    ) {}

    public function index(Request $request)
    {
        $filter = in_array($request->query('status'), ['unpaid', 'paid'], true)
            ? $request->query('status') : 'all';

        $counts = [
            'all' => Fine::count(),
            'unpaid' => Fine::unpaid()->count(),
            'paid' => Fine::paid()->count(),
        ];

        $totals = [
            'all' => (int) Fine::sum('amount'),
            'unpaid' => (int) Fine::unpaid()->sum('amount'),
            'paid' => (int) Fine::paid()->sum('amount'),
        ];

        $query = Fine::with('loan.items')->latest('id');
        if ($filter === 'unpaid') {
            $query->unpaid();
        } elseif ($filter === 'paid') {
            $query->paid();
        }
        $fines = $query->get();

        $loans = $fines->pluck('loan')->filter();
        $memberMap = $this->members->getMembersByIds($loans->pluck('member_id')->unique()->all());
        $bookMap = $this->books->getBooksByIds(
            $loans->pluck('items')->flatten()->pluck('book_id')->unique()->all()
        );

        return view('circulation::fines.index', compact('fines', 'memberMap', 'bookMap', 'filter', 'counts', 'totals'));
    }
}
