<?php

namespace App\Modules\Circulation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $fillable = ['loan_id', 'days_late', 'amount', 'paid_at'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereNull('paid_at');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->whereNotNull('paid_at');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
