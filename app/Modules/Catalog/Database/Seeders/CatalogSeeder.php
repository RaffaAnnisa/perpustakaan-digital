<?php

namespace App\Modules\Catalog\Database\Seeders;

use App\Modules\Catalog\Models\Author;
use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // [judul, ISBN, kategori, stok, [penulis...]]
        $books = [
            ['Laskar Pelangi', '9789793062792', 'Novel', 4, ['Andrea Hirata']],
            ['Bumi Manusia', '9789799731234', 'Novel', 3, ['Pramoedya Ananta Toer']],
            ['Cantik Itu Luka', '9786020312583', 'Novel', 2, ['Eka Kurniawan']],
            ['Laut Bercerita', '9786024246945', 'Novel', 3, ['Leila S. Chudori']],
            ['Pulang', '9786024246212', 'Novel', 2, ['Leila S. Chudori']],
            ['Negeri 5 Menara', '9789792248616', 'Novel', 5, ['Ahmad Fuadi']],
            ['Sapiens', '9786020333755', 'Sejarah', 3, ['Yuval Noah Harari']],
            ['Sejarah Indonesia Modern', '9789790660372', 'Sejarah', 2, ['M.C. Ricklefs']],
            ['Max Havelaar', '9789794618011', 'Sejarah', 1, ['Multatuli']],
            ['Clean Code', '9780132350884', 'Teknologi', 3, ['Robert C. Martin']],
            ['The Pragmatic Programmer', '9780135957059', 'Teknologi', 2, ['Andrew Hunt', 'David Thomas']],
            ['Designing Data-Intensive Applications', '9781449373320', 'Teknologi', 2, ['Martin Kleppmann']],
            ['Laravel: Up & Running', '9781492041214', 'Teknologi', 3, ['Matt Stauffer']],
            ['Refactoring', '9780134757599', 'Teknologi', 1, ['Martin Fowler']],
            ['Kosmos', '9786020324074', 'Sains', 2, ['Carl Sagan']],
            ['Riwayat Singkat Waktu', '9786020331706', 'Sains', 3, ['Stephen Hawking']],
            ['Seni Berpikir Jernih', '9786020628745', 'Sains', 2, ['Rolf Dobelli']],
            ['Atomic Habits', '9786020633176', 'Bisnis', 4, ['James Clear']],
            ['Zero to One', '9780804139298', 'Bisnis', 2, ['Peter Thiel', 'Blake Masters']],
            ['Filosofi Teras', '9786232420762', 'Bisnis', 3, ['Henry Manampiring']],
        ];

        $categories = collect(['Teknologi', 'Sains', 'Novel', 'Sejarah', 'Bisnis'])
            ->mapWithKeys(fn ($name) => [$name => Category::firstOrCreate(['name' => $name])]);

        foreach ($books as [$title, $isbn, $category, $stock, $names]) {
            $book = Book::create([
                'category_id' => $categories[$category]->id,
                'title' => $title,
                'isbn' => $isbn,
                'stock' => $stock,
                'available_stock' => $stock,
            ]);

            // Many-to-Many lewat pivot author_book
            $book->authors()->attach(
                collect($names)->map(fn ($n) => Author::firstOrCreate(['name' => $n])->id)->all()
            );
        }
    }
}
