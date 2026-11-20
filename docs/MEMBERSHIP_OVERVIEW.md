# Ringkasan Modul Membership

## Bounded Context
Modul Membership bertanggung jawab mengelola data anggota perpustakaan, klasifikasi jenis keanggotaan, serta pengecekan status dan kelayakan peminjaman buku.

## Relasi Data (ERD)
- MembershipTypes (1) -> (N) Members
- Members (1) -> (N) Loans (via modul Circulation)

## Fitur Utama
1. Pengelolaan data jenis keanggotaan (Pelajar, Reguler, Premium) beserta batas maksimal peminjaman buku.
2. Pendaftaran, pembaruan data, dan manajemen status aktif/nonaktif anggota perpustakaan.
3. Validasi kelayakan anggota saat transaksi peminjaman melalui MemberEligibilityInterface.
