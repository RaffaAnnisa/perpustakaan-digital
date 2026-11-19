<?php

namespace App\Modules\Membership\Models;

use App\Modules\Membership\Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    protected $fillable = ['membership_type_id', 'member_code', 'name', 'email', 'status'];

    protected static function newFactory(): MemberFactory
    {
        return MemberFactory::new();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
