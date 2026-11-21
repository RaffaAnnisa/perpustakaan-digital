<?php

namespace App\Modules\Circulation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        return view('circulation::fines.index', $this->load($request));
    }

    /** Unduh riwayat denda (mengikuti filter status & tanggal yang sedang aktif) sebagai CSV. */
    public function export(Request $request): StreamedResponse
    {
        $data = $this->load($request);

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar
            fwrite($out, "sep=,\r\n");    // beri tahu Excel bahwa pemisah kolom adalah koma
            
            fputcsv($out, ['ID Pinjaman', 'Anggota', 'Buku', 'Tanggal Denda', 'Hari Terlambat', 'Denda (Rp)', 'Status', 'Tanggal Lunas'], ',', '"', '');

            foreach ($data['fines'] as $fine) {
                $loan = $fine->loan;
                $titles = $loan
                    ? $loan->items->map(fn ($item) => $data['bookMap'][$item->book_id]['title'] ?? '(buku dihapus)')->implode('; ')
                    : '';

                fputcsv($out, [
                    $loan?->id,
                    $this->csvSafe($loan ? ($data['memberMap'][$loan->member_id]['name'] ?? '(anggota dihapus)') : ''),
                    $this->csvSafe($titles),
                    $fine->created_at->format('Y-m-d'),
                    $fine->days_late,
                    $fine->amount,
                    $fine->isPaid() ? 'Lunas' : 'Belum lunas',
                    $fine->paid_at?->format('Y-m-d'),
                ], ',', '"', '');
            }

            fclose($out);
        }, 'riwayat-denda-'.today()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Data bersama untuk halaman riwayat dan ekspor CSV. */
    private function load(Request $request): array
    {
        $filter = in_array($request->query('status'), ['unpaid', 'paid'], true)
            ? $request->query('status') : 'all';
        $from = $this->dateParam($request, 'dari');
        $to = $this->dateParam($request, 'sampai');

        // basis query: hanya dibatasi rentang tanggal denda (belum difilter status)
        $base = fn () => Fine::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $counts = [
            'all' => $base()->count(),
            'unpaid' => $base()->unpaid()->count(),
            'paid' => $base()->paid()->count(),
        ];

        $totals = [
            'all' => (int) $base()->sum('amount'),
            'unpaid' => (int) $base()->unpaid()->sum('amount'),
            'paid' => (int) $base()->paid()->sum('amount'),
        ];

        $query = $base()->with('loan.items')->latest('id');
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

        return compact('fines', 'memberMap', 'bookMap', 'filter', 'from', 'to', 'counts', 'totals');
    }

    /** Ambil tanggal valid (Y-m-d) dari query string; selain itu diabaikan. */
    private function dateParam(Request $request, string $key): ?string
    {
        $value = $request->query($key);
        if (! is_string($value)) {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    /** Cegah CSV injection: sel yang diawali = + - @ dianggap rumus oleh Excel. */
    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
