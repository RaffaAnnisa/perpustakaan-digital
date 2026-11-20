<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\Loan;
use App\Modules\Membership\Models\Member;
use App\Modules\Membership\Models\MembershipType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => 'pustakawan']);
    }

    private function validType(array $override = []): array
    {
        return array_merge([
            'name' => 'Gold',
            'max_loans' => 4,
            'loan_days' => 10,
            'fine_per_day' => 750,
        ], $override);
    }

    // ---------- RBAC ----------

    public function test_guest_is_redirected_from_membership_types(): void
    {
        $this->get('/membership-types')->assertRedirect('/login');
    }

    public function test_only_admin_can_manage_membership_types(): void
    {
        $this->actingAs($this->staff())->get('/membership-types')->assertForbidden();
        $this->actingAs($this->admin())->get('/membership-types')->assertOk();
    }

    public function test_pustakawan_cannot_open_member_list(): void
    {
        $this->actingAs($this->staff())->get('/members')->assertForbidden();
    }

    // ---------- Validasi ----------

    public function test_membership_type_validation_fails_on_empty_form(): void
    {
        $this->actingAs($this->admin())->post('/membership-types', [])
            ->assertSessionHasErrors(['name', 'max_loans', 'loan_days', 'fine_per_day']);
    }

    public function test_membership_type_rejects_out_of_range_values(): void
    {
        $this->actingAs($this->admin())->post('/membership-types', $this->validType([
            'max_loans' => 0, 'loan_days' => 365, 'fine_per_day' => -5,
        ]))->assertSessionHasErrors(['max_loans', 'loan_days', 'fine_per_day']);
    }

    public function test_membership_type_name_must_be_unique(): void
    {
        MembershipType::factory()->create(['name' => 'Gold']);

        $this->actingAs($this->admin())->post('/membership-types', $this->validType())
            ->assertSessionHasErrors('name');
    }

    // ---------- CRUD ----------

    public function test_admin_can_create_membership_type(): void
    {
        $this->actingAs($this->admin())->post('/membership-types', $this->validType())
            ->assertRedirect(route('membership-types.index'));

        $this->assertDatabaseHas('membership_types', ['name' => 'Gold', 'max_loans' => 4]);
    }

    public function test_admin_can_update_membership_type_and_keep_same_name(): void
    {
        $type = MembershipType::factory()->create(['name' => 'Silver', 'loan_days' => 7]);

        $this->actingAs($this->admin())->put("/membership-types/{$type->id}", $this->validType([
            'name' => 'Silver', 'loan_days' => 14,
        ]))->assertRedirect(route('membership-types.index'));

        $this->assertSame(14, $type->fresh()->loan_days);
    }

    public function test_membership_type_in_use_cannot_be_deleted(): void
    {
        $type = MembershipType::factory()->create();
        Member::factory()->create(['membership_type_id' => $type->id]);

        $this->actingAs($this->admin())->delete("/membership-types/{$type->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('membership_types', ['id' => $type->id]);
    }

    public function test_unused_membership_type_can_be_deleted(): void
    {
        $type = MembershipType::factory()->create();

        $this->actingAs($this->admin())->delete("/membership-types/{$type->id}")
            ->assertRedirect(route('membership-types.index'));

        $this->assertDatabaseMissing('membership_types', ['id' => $type->id]);
    }

    // ---------- Aturan anggota pada peminjaman (lewat Contract) ----------

    public function test_inactive_member_cannot_borrow_books(): void
    {
        $member = Member::factory()->create(['status' => 'inactive']);
        $book = Book::factory()->create(['stock' => 3, 'available_stock' => 3]);

        $this->actingAs($this->staff())->post('/loans', [
            'member_id' => $member->id,
            'book_ids' => [$book->id],
        ])->assertSessionHas('error');

        $this->assertSame(0, Loan::count());
        $this->assertSame(3, $book->fresh()->available_stock);
    }

    public function test_loan_limit_follows_membership_type_setting(): void
    {
        $type = MembershipType::factory()->create(['max_loans' => 1]);
        $member = Member::factory()->create(['membership_type_id' => $type->id]);
        $books = Book::factory(2)->create(['stock' => 3, 'available_stock' => 3]);

        $this->actingAs($this->staff())->post('/loans', [
            'member_id' => $member->id,
            'book_ids' => $books->pluck('id')->all(),
        ])->assertSessionHas('error');

        $this->assertSame(0, Loan::count());
    }
}
