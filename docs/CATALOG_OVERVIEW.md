# Ringkasan Modul Catalog

## Bounded Context
Modul Catalog mengelola data master koleksi perpustakaan (Buku, Penulis, dan Kategori) secara terpisah dari modul Membership dan Circulation.

## Relasi Data (ERD)
- Authors (1) -> (N) Books
- Books (N) <-> (N) Categories

## Fitur Utama
1. Pencarian buku berdasarkan judul, ISBN, dan penulis.
2. Pengelolaan data penulis (Authors) dan kategori (Categories) oleh Admin.
3. Pengecekan ketersediaan stok buku melalui BookAvailabilityInterface.
