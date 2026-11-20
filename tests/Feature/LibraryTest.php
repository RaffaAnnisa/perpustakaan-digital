<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use App\Modules\Circulation\Models\Fine;
use App\Modules\Circulation\Models\Loan;
use App\Modules\Membership\Models\Member;
use App\Modules\Membership\Models\MembershipType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role = 'pustakawan'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function member(array $typeOverrides = [], array $memberOverrides = []): Member
    {
        $type = MembershipType::factory()->create($typeOverrides);

        return Member::factory()->create(['membership_type_id' => $type->id] + $memberOverrides);
    }

    private function book(int $available = 3): Book
    {
        return Book::factory()->create(['stock' => 5, 'available_stock' => $available]);
    }

    // ---------- Auth & RBAC ----------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/books')->assertRedirect('/login');
    }

    public function test_pustakawan_cannot_open_member_pages_but_admin_can(): void
    {
        $this->actingAs($this->staff('pustakawan'))->get('/members')->assertForbidden();
        $this->actingAs($this->staff('admin'))->get('/members')->assertOk();
    }

    public function test_pustakawan_cannot_delete_book(): void
    {
        $book = $this->book();
        $this->actingAs($this->staff('pustakawan'))->delete("/books/{$book->id}")->assertForbidden();
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_register_validation_fails_then_succeeds(): void
    {
        $this->post('/register', ['name' => '', 'email' => 'salah', 'password' => '123'])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'budi@test.id',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'budi@test.id', 'role' => 'pustakawan']);
    }

    // ---------- Catalog ----------

    public function test_book_validation_and_creation_with_authors(): void
    {
        $admin = $this->staff('admin');
        $category = Category::factory()->create();
        $authors = Author::factory(2)->create();

        $this->actingAs($admin)->post('/books', ['title' => ''])
            ->assertSessionHasErrors(['title', 'isbn', 'category_id', 'stock', 'author_ids']);

        $this->actingAs($admin)->post('/books', [
            'title' => 'Laravel Dasar', 'isbn' => '9781234567890', 'category_id' => $category->id,
            'stock' => 4, 'author_ids' => $authors->pluck('id')->all(),
        ])->assertRedirect(route('books.index'));

        $book = Book::firstWhere('isbn', '9781234567890');
        $this->assertSame(4, $book->available_stock);
        $this->assertCount(2, $book->authors); // Many-to-Many
    }

    // ---------- Circulation ----------

    public function test_borrow_success_reduces_stock(): void
    {
        $member = $this->member();
        $book = $this->book(3);

        $this->actingAs($this->staff())->post('/loans', [
            'member_id' => $member->id, 'book_ids' => [$book->id],
        ])->assertRedirectContains('/loans');

        $this->assertSame(2, $book->fresh()->available_stock);
        $this->assertDatabaseHas('loan_items', ['book_id' => $book->id]);
    }

    public function test_borrow_rolls_back_when_one_book_is_out_of_stock(): void
    {
        $member = $this->member();
        $ok = $this->book(1);
        $empty = $this->book(0);

        $this->actingAs($this->staff())->post('/loans', [
            'member_id' => $member->id, 'book_ids' => [$ok->id, $empty->id],
        ])->assertSessionHas('error');

        // stok buku pertama tidak boleh berkurang & tidak ada loan tersimpan
        $this->assertSame(1, $ok->fresh()->available_stock);
        $this->assertSame(0, Loan::count());
    }

    public function test_inactive_member_cannot_borrow(): void
    {
        $member = $this->member([], ['status' => 'inactive']);
        $book = $this->book();

        $this->actingAs($this->staff())->post('/loans', [
            'member_id' => $member->id, 'book_ids' => [$book->id],
        ])->assertSessionHas('error');

        $this->assertSame(0, Loan::count());
    }

    public function test_loan_limit_is_enforced(): void
    {
        $member = $this->member(['max_loans' => 1]);
        $a = $this->book();
        $b = $this->book();

        $this->actingAs($this->staff())->post('/loans', [
            'member_id' => $member->id, 'book_ids' => [$a->id, $b->id],
        ])->assertSessionHas('error');

        $this->assertSame(0, Loan::count());
    }

    public function test_return_on_time_restores_stock_without_fine(): void
    {
        $member = $this->member();
        $book = $this->book(3);
        $staff = $this->staff();

        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$book->id]]);
        $loan = Loan::first();

        $this->actingAs($staff)->post("/loans/{$loan->id}/return")->assertRedirectContains('/loans');

        $this->assertSame(3, $book->fresh()->available_stock); // dipulihkan via Domain Event
        $this->assertSame(0, Fine::count());
        $this->assertSame('returned', $loan->fresh()->status);
    }

    public function test_late_return_creates_fine(): void
    {
        $member = $this->member(['fine_per_day' => 1000]);
        $book = $this->book(3);
        $staff = $this->staff();

        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$book->id]]);
        $loan = Loan::first();
        $loan->update(['due_date' => today()->subDays(3)]);

        $this->actingAs($staff)->post("/loans/{$loan->id}/return");

        $fine = Fine::first();
        $this->assertSame(3, $fine->days_late);
        $this->assertSame(3000, $fine->amount);
    }

    public function test_returning_twice_is_rejected(): void
    {
        $member = $this->member();
        $book = $this->book(3);
        $staff = $this->staff();

        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$book->id]]);
        $loan = Loan::first();

        $this->actingAs($staff)->post("/loans/{$loan->id}/return");
        $this->actingAs($staff)->post("/loans/{$loan->id}/return")->assertSessionHas('error');

        $this->assertSame(3, $book->fresh()->available_stock); // stok tidak bertambah dua kali
    }

    // ---------- Fitur tambahan ----------

    public function test_dashboard_shows_overdue_with_running_fine(): void
    {
        $member = $this->member(['fine_per_day' => 500]);
        $book = $this->book(3);
        $staff = $this->staff();

        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$book->id]]);
        Loan::first()->update(['due_date' => today()->subDays(2)]);

        $this->actingAs($staff)->get('/')->assertOk()->assertSee($member->name)->assertSee('Terlambat');
        $this->assertSame(1000, Loan::first()->runningFine());
    }

    public function test_loan_list_can_be_filtered_by_overdue(): void
    {
        $member = $this->member(['max_loans' => 5]);
        $a = $this->book();
        $b = $this->book();
        $staff = $this->staff();

        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$a->id]]);
        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$b->id]]);
        Loan::first()->update(['due_date' => today()->subDay()]);

        $this->actingAs($staff)->get('/loans?status=overdue')
            ->assertOk()->assertSee($a->title)->assertDontSee($b->title);
    }

    public function test_book_search_filters_by_title(): void
    {
        $category = Category::factory()->create();
        Book::factory()->create(['title' => 'Kancil dan Buaya', 'category_id' => $category->id]);
        Book::factory()->create(['title' => 'Ensiklopedia Planet', 'category_id' => $category->id]);

        $this->actingAs($this->staff())->get('/books?q=kancil')
            ->assertOk()->assertSee('Kancil dan Buaya')->assertDontSee('Ensiklopedia Planet');
    }

    public function test_fine_can_be_marked_paid_once(): void
    {
        $member = $this->member(['fine_per_day' => 1000]);
        $book = $this->book();
        $staff = $this->staff();

        $this->actingAs($staff)->post('/loans', ['member_id' => $member->id, 'book_ids' => [$book->id]]);
        $loan = Loan::first();
        $loan->update(['due_date' => today()->subDays(2)]);
        $this->actingAs($staff)->post("/loans/{$loan->id}/return");

        $fine = Fine::first();
        $this->actingAs($staff)->post("/fines/{$fine->id}/pay")->assertSessionHas('success');
        $paidAt = $fine->fresh()->paid_at;
        $this->assertNotNull($paidAt);

        $this->actingAs($staff)->post("/fines/{$fine->id}/pay");
        $this->assertEquals($paidAt, $fine->fresh()->paid_at); // idempotent
    }

    public function test_member_card_is_admin_only(): void
    {
        $member = $this->member();

        $this->actingAs($this->staff('pustakawan'))->get("/members/{$member->id}/card")->assertForbidden();
        $this->actingAs($this->staff('admin'))->get("/members/{$member->id}/card")->assertOk()->assertSee($member->member_code);
    }

    // ---------- Batas modul ----------

    public function test_modules_do_not_import_models_of_other_modules(): void
    {
        $modules = ['Catalog', 'Membership', 'Circulation'];
        $violations = [];

        foreach ($modules as $module) {
            $others = array_diff($modules, [$module]);
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path("Modules/{$module}")));

            foreach ($it as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $code = file_get_contents($file->getPathname());
                foreach ($others as $other) {
                    if (str_contains($code, "App\\Modules\\{$other}\\Models")) {
                        $violations[] = $file->getPathname()." -> {$other}\\Models";
                    }
                }
            }
        }

        $this->assertSame([], $violations, "Pelanggaran batas modul:\n".implode("\n", $violations));
    }
}
