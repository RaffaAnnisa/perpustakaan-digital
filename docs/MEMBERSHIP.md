# Modul Membership

Modul **Membership** mengelola anggota perpustakaan dan aturan keanggotaannya. Modul ini berdiri sendiri: modul lain
(Circulation) tidak boleh membaca tabel atau model-nya secara langsung dan harus melalui **Contract**.

## Tabel

| Tabel | Kolom utama | Keterangan |
|---|---|---|
| `membership_types` | `name`, `max_loans`, `loan_days`, `fine_per_day` | Aturan tiap jenis keanggotaan |
| `members` | `membership_type_id`, `member_code`, `name`, `email`, `status` | Data anggota (`active` / `inactive`) |

**Relasi:** `MembershipType` 1 --- N `Member` (One-to-Many).

## Fitur dan hak akses

| Fitur | Route | Hak akses |
|---|---|---|
| CRUD Anggota | `/members` | admin |
| Kartu anggota | `/members/{member}/card` | admin |
| CRUD Jenis Keanggotaan | `/membership-types` | admin |

Jenis keanggotaan yang masih dipakai anggota tidak dapat dihapus (`MembershipTypeInUseException`).

## Komunikasi dengan modul lain

Modul Circulation memakai `MemberEligibilityInterface`:

| Method | Fungsi |
|---|---|
| `getMemberInfo(id)` | Data satu anggota beserta aturan jenisnya |
| `getMembersByIds(ids)` | Data banyak anggota sekaligus (untuk tampilan daftar) |
| `listActive()` | Anggota aktif untuk pilihan di form peminjaman |
| `assertCanBorrow(id, aktif, diminta)` | Memeriksa status aktif dan batas pinjam, mengembalikan aturan peminjaman |

`assertCanBorrow()` melempar `MemberInactiveException` atau `LoanLimitExceededException` bila syarat tidak terpenuhi.
Jumlah buku yang sedang dipinjam dihitung oleh Circulation dan dikirim sebagai parameter, sehingga Membership tidak
perlu membaca tabel `loans`.

## Catatan desain

- Tarif denda dan lama pinjam disalin (snapshot) ke tabel `loans` saat peminjaman dibuat. Mengubah jenis keanggotaan
  tidak mengubah denda peminjaman yang sedang berjalan.
- `loans.member_id` hanya berupa angka tanpa foreign key, agar batas antar modul tetap tegas.
