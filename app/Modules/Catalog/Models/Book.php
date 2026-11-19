<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Catalog\Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'title', 'isbn', 'stock', 'available_stock'];

    protected static function newFactory(): BookFactory
    {
        return BookFactory::new();
    }

    // One-to-Many (sisi "many")
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Many-to-Many dengan pivot author_book
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_book');
    }
}
