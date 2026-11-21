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

class FineFilterTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role' => 'pustakawan']);
    }

    /** Pinjam, terlambat $daysLate hari, lalu dikembalikan. @return array{0: Fine, 1: Book, 2: Member} */
    private function lateReturn(User $staff, int $daysLate, int $finePerDay = 1000): array
    {
        $type = MembershipType::factory()->create(['fine_per_day' => $finePerDay]);
        $member = Member::factory()->create(['membership_type_id' => $type->id]);
        $book = Book::factory()->create(['stock' => 5, 'available_stock' => 3]);

        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$book->id]]);
        $loan = Loan::latest('id')->firstOrFail();
        $loan->update(['due_date' => today()->subDays($daysLate)]);
        $this->actingAs($staff)->post("/loans/{$loan->id}/return");

        return [Fine::where('loan_id', $loan->id)->firstOrFail(), $book, $member];
    }

    /** Dua denda: satu tercatat 10 hari lalu (Rp 2.000), satu hari ini (Rp 4.000). */
    private function oldAndNewFine(User $staff): array
    {
        [$old, $oldBook] = $this->lateReturn($staff, 2);
        [, $newBook] = $this->lateReturn($staff, 4);
        Fine::whereKey($old->id)->update(['created_at' => now()->subDays(10)]);

        return [$oldBook, $newBook];
    }

    // ---------- Filter tanggal ----------

    public function test_date_filter_from_hides_older_fines(): void
    {
        $staff = $this->staff();
        [$oldBook, $newBook] = $this->oldAndNewFine($staff);

        $this->actingAs($staff)->get('/fines?dari='.today()->subDays(2)->toDateString())
            ->assertOk()->assertSee($newBook->title)->assertDontSee($oldBook->title);
    }

    public function test_date_filter_until_hides_newer_fines(): void
    {
        $staff = $this->staff();
        [$oldBook, $newBook] = $this->oldAndNewFine($staff);

        $this->actingAs($staff)->get('/fines?sampai='.today()->subDays(5)->toDateString())
            ->assertOk()->assertSee($oldBook->title)->assertDontSee($newBook->title);
    }

    public function test_totals_follow_the_date_range(): void
    {
        $staff = $this->staff();
        $this->oldAndNewFine($staff);

        $this->actingAs($staff)->get('/fines?dari='.today()->subDays(2)->toDateString())
            ->assertViewHas('totals', ['all' => 4000, 'unpaid' => 4000, 'paid' => 0]);
    }

    public function test_invalid_date_is_ignored(): void
    {
        $staff = $this->staff();
        [$oldBook, $newBook] = $this->oldAndNewFine($staff);

        $this->actingAs($staff)->get('/fines?dari=bukan-tanggal&sampai=2026-13-45')
            ->assertOk()
            ->assertViewHas('from', null)
            ->assertViewHas('to', null)
            ->assertSee($oldBook->title)
            ->assertSee($newBook->title);
    }

    // ---------- Unduh CSV ----------

    public function test_csv_export_requires_login(): void
    {
        $this->get('/fines/export')->assertRedirect('/login');
    }

    public function test_csv_export_contains_header_and_fine_rows(): void
    {
        $staff = $this->staff();
        [$fine, $book, $member] = $this->lateReturn($staff, 3, 1000);

        $response = $this->actingAs($staff)->get('/fines/export')->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ID Pinjaman', $csv);
        $this->assertStringContainsString($member->name, $csv);
        $this->assertStringContainsString($book->title, $csv);
        $this->assertStringContainsString('3000', $csv);
        $this->assertStringContainsString('Belum lunas', $csv);
    }

    public function test_csv_export_follows_status_filter(): void
    {
        $staff = $this->staff();
        [$paidFine, $paidBook] = $this->lateReturn($staff, 2);
        [, $unpaidBook] = $this->lateReturn($staff, 4);
        $this->actingAs($staff)->post("/fines/{$paidFine->id}/pay");

        $csv = $this->actingAs($staff)->get('/fines/export?status=unpaid')->streamedContent();

        $this->assertStringContainsString($unpaidBook->title, $csv);
        $this->assertStringNotContainsString($paidBook->title, $csv);
    }

    public function test_csv_export_follows_date_filter(): void
    {
        $staff = $this->staff();
        [$oldBook, $newBook] = $this->oldAndNewFine($staff);

        $csv = $this->actingAs($staff)
            ->get('/fines/export?dari='.today()->subDays(2)->toDateString())
            ->streamedContent();

        $this->assertStringContainsString($newBook->title, $csv);
        $this->assertStringNotContainsString($oldBook->title, $csv);
    }
}
