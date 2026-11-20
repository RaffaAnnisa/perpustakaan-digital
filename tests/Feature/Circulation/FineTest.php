<?php

namespace Tests\Feature\Circulation;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Models\Loan;
use App\Modules\Membership\Models\Member;
use App\Modules\Membership\Models\MembershipType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FineTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role = 'pustakawan'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function member(array $typeOverrides = []): Member
    {
        $type = MembershipType::factory()->create($typeOverrides);

        return Member::factory()->create(['membership_type_id' => $type->id]);
    }

    private function book(): Book
    {
        return Book::factory()->create(['stock' => 5, 'available_stock' => 3]);
    }

    /** Pinjam lewat HTTP, lalu majukan jatuh tempo ke masa lalu. */
    private function borrow(User $staff, Member $member, Book $book, int $daysUntilDue): Loan
    {
        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$book->id]]);

        $loan = Loan::latest('id')->firstOrFail();
        $loan->update(['due_date' => today()->addDays($daysUntilDue)]);

        return $loan;
    }

    /** Pinjam, terlambat $daysLate hari, lalu dikembalikan. @return array{0: Fine, 1: Book, 2: Member} */
    private function lateReturn(User $staff, int $daysLate, int $finePerDay = 1000): array
    {
        $member = $this->member(['fine_per_day' => $finePerDay]);
        $book = $this->book();
        $loan = $this->borrow($staff, $member, $book, -$daysLate);

        $this->actingAs($staff)->post("/loans/{$loan->id}/return");

        return [Fine::where('loan_id', $loan->id)->firstOrFail(), $book, $member];
    }

    // ---------- Perhitungan denda ----------

    public function test_returning_on_due_date_creates_no_fine(): void
    {
        $staff = $this->staff();
        $loan = $this->borrow($staff, $this->member(), $this->book(), 0);

        $this->actingAs($staff)->post("/loans/{$loan->id}/return");

        $this->assertSame(0, Fine::count());
    }

    public function test_returning_one_day_late_charges_one_day(): void
    {
        [$fine] = $this->lateReturn($this->staff(), 1, 1500);

        $this->assertSame(1, $fine->days_late);
        $this->assertSame(1500, $fine->amount);
    }

    public function test_running_fine_is_days_late_times_rate_and_stops_after_return(): void
    {
        $late = Loan::factory()->make(['due_date' => today()->subDays(2), 'fine_per_day' => 700]);

        $this->assertTrue($late->isOverdue());
        $this->assertSame(2, $late->daysLate());
        $this->assertSame(1400, $late->runningFine());

        $returned = Loan::factory()->make([
            'due_date' => today()->subDays(2), 'fine_per_day' => 700, 'status' => 'returned',
        ]);

        $this->assertFalse($returned->isOverdue());
        $this->assertSame(0, $returned->daysLate());
        $this->assertSame(0, $returned->runningFine());
    }

    public function test_loan_due_today_is_not_overdue(): void
    {
        $loan = Loan::factory()->make(['due_date' => today()]);

        $this->assertFalse($loan->isOverdue());
        $this->assertSame(0, $loan->daysLate());
        $this->assertSame(0, $loan->daysUntilDue());
    }

    // ---------- Halaman riwayat denda ----------

    public function test_fine_history_requires_login(): void
    {
        $this->get('/fines')->assertRedirect('/login');
    }

    public function test_fine_history_lists_fine_with_member_book_and_amount(): void
    {
        $staff = $this->staff();
        [$fine, $book, $member] = $this->lateReturn($staff, 3, 1000);

        $this->actingAs($staff)->get('/fines')
            ->assertOk()
            ->assertSee($member->name)
            ->assertSee($book->title)
            ->assertSee('3 hari')
            ->assertSee('Rp 3.000');
    }

    public function test_fine_history_can_be_filtered_by_payment_status(): void
    {
        $staff = $this->staff();
        [$paidFine, $paidBook] = $this->lateReturn($staff, 2);
        [, $unpaidBook] = $this->lateReturn($staff, 4);
        $this->actingAs($staff)->post("/fines/{$paidFine->id}/pay");

        $this->actingAs($staff)->get('/fines?status=unpaid')
            ->assertOk()->assertSee($unpaidBook->title)->assertDontSee($paidBook->title);

        $this->actingAs($staff)->get('/fines?status=paid')
            ->assertOk()->assertSee($paidBook->title)->assertDontSee($unpaidBook->title);
    }

    public function test_fine_history_totals_split_paid_and_unpaid(): void
    {
        $staff = $this->staff();
        [$paidFine] = $this->lateReturn($staff, 2); // Rp 2.000
        $this->lateReturn($staff, 4);               // Rp 4.000
        $this->actingAs($staff)->post("/fines/{$paidFine->id}/pay");

        $this->actingAs($staff)->get('/fines')
            ->assertOk()
            ->assertViewHas('totals', ['all' => 6000, 'unpaid' => 4000, 'paid' => 2000]);
    }

    public function test_marking_fine_paid_from_history_returns_to_history(): void
    {
        $staff = $this->staff();
        [$fine] = $this->lateReturn($staff, 2);

        $this->actingAs($staff)->from('/fines')->post("/fines/{$fine->id}/pay")
            ->assertRedirect('/fines');

        $this->assertTrue($fine->fresh()->isPaid());
    }

    // ---------- Status terlambat di daftar peminjaman ----------

    public function test_loan_list_shows_overdue_status_and_running_fine(): void
    {
        $staff = $this->staff();
        $member = $this->member(['fine_per_day' => 500, 'max_loans' => 5]);
        $this->borrow($staff, $member, $this->book(), -3);

        $this->actingAs($staff)->get('/loans')
            ->assertOk()
            ->assertSee('1 peminjaman terlambat')
            ->assertSee('Terlambat 3 hari')
            ->assertSee('Rp 1.500');
    }

    public function test_loan_list_flags_loans_due_today_and_tomorrow(): void
    {
        $staff = $this->staff();
        $member = $this->member(['max_loans' => 5]);
        $this->borrow($staff, $member, $this->book(), 0);
        $this->borrow($staff, $member, $this->book(), 1);

        $this->actingAs($staff)->get('/loans')
            ->assertOk()
            ->assertSee('Jatuh tempo hari ini')
            ->assertSee('Jatuh tempo besok')
            ->assertDontSee('peminjaman terlambat');
    }
}
