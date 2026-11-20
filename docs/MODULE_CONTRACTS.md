# Kontrak Integrasi Antar Modul (Modular Monolith)

Untuk menjaga independensi antar modul, komunikasi lintas bounded context dilakukan melalui antarmuka (interface) resmi tanpa mengakses tabel modul lain secara langsung.

## 1. BookAvailabilityInterface (Modul Catalog)
- Digunakan oleh modul Circulation sebelum membuat transaksi peminjaman.
- Memastikan stok buku masih tersedia dan mengurangi/mengembalikan jumlah stok secara aman.

## 2. MemberEligibilityInterface (Modul Membership)
- Digunakan oleh modul Circulation untuk memverifikasi status aktif anggota.
- Memeriksa apakah jumlah pinjaman aktif anggota masih di bawah batas maksimal jenis keanggotaannya.
