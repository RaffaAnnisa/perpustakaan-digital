# Alur Git untuk tim

Riwayat commit harus memperlihatkan kontribusi tiap anggota. Pembagian yang disarankan: satu orang atau pasangan per modul.

## Pembagian branch (contoh)

| Branch | Isi | Pemilik |
|---|---|---|
| `feat/catalog` | Catalog: migration, model, CRUD buku, pencarian | Anggota 1 |
| `feat/membership` | Membership: jenis keanggotaan, CRUD anggota, kartu | Anggota 2 |
| `feat/circulation` | Peminjaman, denda, event, dasbor | Anggota 3 |
| `feat/auth-rbac` | Sanctum, login/register, middleware role | Anggota 4 |

## Aturan commit

- Pesan deskriptif, bentuk perintah: `Tambah Contract BookAvailabilityInterface`, `Perbaiki stok saat buku dikembalikan dua kali`.
- Satu perubahan logis per commit; commit sering, jangan satu commit besar di akhir.
- Awali dengan jenis bila perlu: `feat:`, `fix:`, `test:`, `docs:`, `refactor:`.
- Setiap anggota commit dari akunnya sendiri (`git config user.name` / `user.email` sesuai akun GitHub), lalu buka Pull Request ke `main` dan minta review satu anggota lain.

## Contoh urutan commit

```
feat: tambah migration dan model Category, Author, Book (pivot author_book)
feat: tambah BookAvailabilityInterface dan implementasinya
feat: CRUD buku dengan Form Request dan transaksi
feat: tambah BookOutOfStockException dengan pesan di view
test: uji stok habis membatalkan seluruh peminjaman
```
