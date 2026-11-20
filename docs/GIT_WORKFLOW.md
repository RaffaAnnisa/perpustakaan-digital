# Panduan Alur Kerja Git & Konvensi Commit

## Struktur Branch
- `main`: Branch utama yang selalu berada dalam kondisi stabil dan siap dijalankan.
- `feat/<nama-modul>`: Branch pengembangan fitur baru berdasarkan pembagian modul.
- `docs/<topik>`: Branch khusus penambahan dan pembaruan dokumentasi.

## Konvensi Pesan Commit (Conventional Commits)
- `feat(<modul>):` Penambahan fitur fungsional baru (contoh: `feat(catalog): add author crud`).
- `fix(<modul>):` Perbaikan bug atau tampilan antarmuka.
- `test(<modul>):` Penambahan pengujian otomatis (Feature/Unit Test).
- `docs(<modul>):` Pembaruan dokumentasi teknis dan panduan pengguna.
