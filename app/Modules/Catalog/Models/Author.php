<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Catalog\Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected static function newFactory(): AuthorFactory
    {
        return AuthorFactory::new();
    }

    // Many-to-Many
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'author_book');
    }
}
