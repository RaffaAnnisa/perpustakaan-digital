<?php

namespace App\Modules\Catalog\Database\Factories;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $stock = fake()->numberBetween(1, 10);

        return [
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'isbn' => fake()->unique()->isbn13(),
            'stock' => $stock,
            'available_stock' => $stock,
        ];
    }
}
