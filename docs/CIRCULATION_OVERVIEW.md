# Ringkasan Modul Circulation

## Bounded Context
Modul Circulation menangani seluruh siklus transaksi peminjaman buku, pengembalian, perpanjangan, serta perhitungan otomatis keterlambatan dan denda.

## Relasi Data (ERD)
- Members (1) -> (N) Loans
- Books (1) -> (N) Loans
- Loans (1) -> (0..1) Fines

## Fitur Utama
1. Pencatatan transaksi peminjaman buku baru beserta tanggal jatuh tempo pengembalian.
2. Pemrosesan pengembalian buku dan pembaruan status ketersediaan stok.
3. Perhitungan denda keterlambatan secara otomatis dan pencatatan status pelunasan denda.
