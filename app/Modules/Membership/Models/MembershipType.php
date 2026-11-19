<?php

namespace App\Modules\Membership\Models;

use App\Modules\Membership\Database\Factories\MembershipTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'max_loans', 'loan_days', 'fine_per_day'];

    protected static function newFactory(): MembershipTypeFactory
    {
        return MembershipTypeFactory::new();
    }

    // One-to-Many
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
