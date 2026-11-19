<?php

namespace App\Modules\Circulation\Models;

use App\Modules\Circulation\Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'processed_by', 'loan_date', 'due_date',
        'returned_at', 'fine_per_day', 'status',
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    protected static function newFactory(): LoanFactory
    {
        return LoanFactory::new();
    }

    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' && $this->due_date->lt(today());
    }

    public function daysLate(): int
    {
        if ($this->status !== 'borrowed') {
            return 0;
        }

        return (int) max(0, $this->due_date->diffInDays(today(), false));
    }

    /** Denda yang sedang berjalan (belum dikembalikan) berdasarkan tarif snapshot. */
    public function runningFine(): int
    {
        return $this->daysLate() * $this->fine_per_day;
    }

    // One-to-Many
    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class);
    }
}
