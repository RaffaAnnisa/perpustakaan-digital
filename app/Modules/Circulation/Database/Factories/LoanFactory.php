<?php

namespace App\Modules\Circulation\Database\Factories;

use App\Modules\Circulation\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        return [
            'member_id' => 1,
            'processed_by' => null,
            'loan_date' => today(),
            'due_date' => today()->addDays(7),
            'fine_per_day' => 1000,
            'status' => 'borrowed',
        ];
    }
}
