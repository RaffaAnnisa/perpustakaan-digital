# Panduan Menjalankan Pengujian (PHPUnit / Artisan Test)

Pengujian otomatis digunakan untuk memastikan setiap modul berjalan sesuai aturan bisnis sebelum digabungkan ke branch utama.

## Perintah Pengujian
1. Menjalankan seluruh rangkaian pengujian:
   `php artisan test`
2. Menjalankan pengujian khusus satu modul (contoh: Catalog):
   `php artisan test --filter=Catalog`
3. Menjalankan ulang migrasi pada database pengujian:
   `php artisan migrate:fresh --env=testing`

## Cakupan Pengujian Utama
- Autentikasi dan pengalihan pengguna yang belum masuk ke halaman `/login`.
- Pembatasan akses menu administrasi bagi peran Pustakawan.
- Validasi ketersediaan stok buku dan batas pinjaman anggota saat transaksi.
