<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            // One-to-Many: Category -> Book (masih dalam satu modul)
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title');
            $table->string('isbn')->unique();
            $table->unsignedInteger('stock')->default(0);            // total eksemplar
            $table->unsignedInteger('available_stock')->default(0);  // yang tersedia
            $table->timestamps();
        });

        // Many-to-Many: Book <-> Author lewat pivot
        Schema::create('author_book', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->primary(['book_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_book');
        Schema::dropIfExists('books');
    }
};
