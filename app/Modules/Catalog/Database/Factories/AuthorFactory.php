<?php

namespace App\Modules\Catalog\Database\Factories;

use App\Modules\Catalog\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return ['name' => fake()->name()];
    }
}
