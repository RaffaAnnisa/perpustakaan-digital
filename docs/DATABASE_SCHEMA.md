# Dokumentasi Skema Database (SQLite)

Struktur tabel utama pada sistem Perpustakaan Rak Digital dikelompokkan berdasarkan masing-masing modul:

## 1. Modul Catalog
- `authors`: Menyimpan daftar penulis (`id`, `name`, `bio`, `timestamps`).
- `categories`: Menyimpan klasifikasi kategori buku (`id`, `name` unik, `timestamps`).
- `books`: Menyimpan data koleksi buku (`id`, `isbn` unik, `title`, `author_id`, `stock`, `timestamps`).
- `book_category`: Tabel pivot relasi *many-to-many* antara `books` dan `categories`.

## 2. Modul Membership
- `membership_types`: Menyimpan kategori keanggotaan (`id`, `name`, `max_books`, `loan_days`, `timestamps`).
- `members`: Menyimpan profil anggota (`id`, `member_code`, `name`, `email`, `membership_type_id`, `is_active`, `timestamps`).

## 3. Modul Circulation
- `loans`: Menyimpan catatan peminjaman buku (`id`, `member_id`, `book_id`, `borrowed_at`, `due_at`, `returned_at`, `timestamps`).
- `fines`: Menyimpan rincian denda keterlambatan (`id`, `loan_id`, `amount`, `paid_at`, `timestamps`).
