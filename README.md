# Library Management System (Perpustakaan Digital)

[![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL%2FMariaDB-5.7%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

Sistem manajemen perpustakaan berbasis web untuk mengelola koleksi buku, anggota, serta transaksi peminjaman dan pengembalian. Aplikasi ini menyediakan panel admin untuk operasional perpustakaan dan panel user untuk katalog, peminjaman, dan riwayat.

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Arsitektur Singkat](#arsitektur-singkat)
- [Prasyarat](#prasyarat)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Penggunaan](#penggunaan)
  - [Akun Default](#akun-default)
  - [Alur Admin](#alur-admin)
  - [Alur User](#alur-user)
  - [Fitur QR / NIS](#fitur-qr--nis)
  - [Import Anggota (CSV)](#import-anggota-csv)
- [Screenshot](#screenshot)
- [Testing](#testing)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)
- [Kontak](#kontak)

## Fitur

**Admin**

- Dashboard statistik (buku, anggota, transaksi, jatuh tempo)
- Manajemen buku: tambah, edit, hapus, cover, stok, lokasi rak
- Manajemen anggota: tambah, edit, hapus, status aktif/nonaktif, foto profil
- Transaksi: peminjaman dan pengembalian (termasuk perhitungan denda)
- Riwayat transaksi
- Laporan (mis. buku populer)
- Scan NIS (QR) untuk percepat pemilihan anggota
- Import anggota dari CSV

**User**

- Katalog buku (pencarian dan browsing)
- Peminjaman buku (dengan validasi aturan)
- Riwayat peminjaman
- Profil user (update data + foto)
- Notifikasi jatuh tempo (mendekati tanggal kembali)

## Teknologi

- Backend: PHP (PDO)
- Database: MySQL / MariaDB
- UI: Bootstrap 5, Bootstrap Icons
- Komponen UI tambahan: jQuery, DataTables, SweetAlert
- QR: `phpqrcode` + `html5-qrcode` (browser camera)

## Arsitektur Singkat

- `config/database.php`: koneksi DB + helper (auth, sanitasi input, util upload)
- `admin/*`: halaman panel admin
- `user/*`: halaman panel user
- `proses/*`: handler POST/GET (login, CRUD, transaksi, endpoint JSON)
- `includes/*`: layout bersama (header/navbar/footer/alerts)
- `assets/*`: CSS/JS dan library QR

## Prasyarat

- PHP 8.0+ (disarankan 8.2+)
- MySQL 5.7+ atau MariaDB 10.4+
- Web server (Apache/Nginx) atau PHP built-in server
- Opsional (direkomendasikan untuk lokal): XAMPP/Laragon/WAMP

## Instalasi

1) Clone repository

```bash
git clone <URL_REPOSITORY_ANDA>
cd project-1_icc_20-21/LibraryManagement
```

2) Letakkan project di document root web server

- XAMPP (Windows): `C:\xampp\htdocs\LibraryManagement`
- Laragon: `C:\laragon\www\LibraryManagement`

3) Buat database dan import schema/data

Pilih salah satu:

- **Schema minimal**: import `database.sql`
- **Schema + sample data**: import `perpustakaan.sql` (disarankan untuk demo)

Contoh via phpMyAdmin:

1. Buat database `perpustakaan`
2. Import file `perpustakaan.sql`

## Konfigurasi

Konfigurasi disarankan menggunakan environment variables (agar kredensial tidak hardcoded):

- `APP_ENV` (default: `production`)
- `APP_DEBUG` (default: `0`)
- `BASE_URL` (default: `/LibraryManagement`)
- `DB_HOST` (default: `localhost`)
- `DB_USERNAME` (default: `root`)
- `DB_PASSWORD` (default: kosong)
- `DB_NAME` (default: `perpustakaan`)
- `APP_LOG_PATH` (default: `storage/logs/app.log`)
- `ENABLE_HTTPS_REDIRECT` (default: `0`) — aktifkan `1` di production jika sudah HTTPS
- `CSRF_TOKEN_TTL` (default: `7200`) — masa berlaku token CSRF (detik)

Catatan keamanan deployment:

- Wajib gunakan user database non-root dan password kuat.
- Pastikan aplikasi berjalan di HTTPS (aktifkan redirect setelah TLS siap).

Pastikan folder upload dapat ditulis oleh server:

- `proses/uploads/`
  - `proses/uploads/covers/`
  - `proses/uploads/profiles/`

Pastikan folder log dapat dibuat/ditulis:

- `storage/logs/`

## Penggunaan

Buka aplikasi:

- `http://localhost/LibraryManagement/`

### Akun Default

Tergantung file SQL yang Anda import:

- Jika menggunakan `database.sql`: akun admin default biasanya `admin` dengan password `password`.
- Jika menggunakan `perpustakaan.sql`: akun admin dan user sudah tersedia di tabel `users` (lihat isi dump untuk detail).

### Alur Admin

1. Login sebagai admin
2. Kelola buku: `Admin → Kelola Buku → Tambah Buku / Daftar Buku`
3. Kelola anggota: `Admin → Kelola Anggota`
4. Proses peminjaman: `Admin → Transaksi → Peminjaman`
5. Proses pengembalian: `Admin → Transaksi → Pengembalian`
6. Laporan: `Admin → Laporan`

### Alur User

1. Login sebagai user
2. Lihat katalog: `User → Katalog Buku`
3. Pinjam buku dari detail/halaman pinjam
4. Cek riwayat: `User → Riwayat Pinjam`
5. Update profil: `User → Profil`

### Fitur QR / NIS

- Halaman scan (admin): `Admin → Scan NIS (QR)`
- QR yang di-scan berisi nilai **NIS** (diimplementasikan sebagai `username` user) untuk lookup anggota.

Endpoint terkait:

- `proses/get_anggota_by_nis.php?nis=<NIS>` → JSON data anggota
- `proses/generate_qr.php?id=<user_id>` → generate QR PNG (khusus admin)

### Import Anggota (CSV)

Buka: `Admin → Kelola Anggota → Import Anggota`

Format CSV (minimal header `nis`):

```csv
nis,nama,email,no_hp,alamat,status
12345,Budi,budi@example.com,08123456789,"Jakarta",aktif
```

Catatan:

- `nis` akan dipakai sebagai `username`.
- Password default mengikuti nilai `nis`.
- Jika `email` kosong, sistem akan mengisi otomatis menjadi `<nis>@import.local`.

## Screenshot

Tambahkan screenshot di folder misalnya `docs/screenshots/` lalu tautkan di sini:

- Dashboard Admin
- Katalog User
- Form Peminjaman/Pengembalian

Contoh (ganti path sesuai file Anda):

```md
![Dashboard Admin](docs/screenshots/admin-dashboard.png)
```

## Testing

Jalankan unit test:

```bash
php tests.php
```

Jalankan integration test (HTTP + cek header keamanan):

```bash
php tests/http_integration.php
```

Project ini belum menggunakan framework testing resmi (mis. PHPUnit). Saat ini tersedia smoke test sederhana:

```bash
php tests.php
```

Untuk pengembangan lebih lanjut, direkomendasikan menambahkan PHPUnit via Composer dan membuat test untuk:

- Validasi aturan peminjaman (maks 3 buku, maks 14 hari)
- Perhitungan denda saat pengembalian
- Upload file (cover/profil) dan pembersihan file lama

## Kontribusi

Kontribusi sangat diterima.

1. Fork repository
2. Buat branch fitur: `git checkout -b feat/nama-fitur`
3. Pastikan perubahan tidak memecahkan alur utama (login, buku, anggota, transaksi)
4. Buat PR dengan deskripsi jelas dan langkah uji/reproduksi

Pedoman teknis:

- Gunakan prepared statement untuk query DB.
- Jangan menampilkan detail error database ke user (hindari leak info sensitif).
- Ikuti pola `requireLogin()` / `requireAdmin()` pada halaman yang butuh proteksi.

```mermaid
flowchart TB
    %% Start & Landing
    START(("Start"))
    LP["Landing Page / Login (index.php)"]
    click LP href "/index.php" "Buka Landing Page / Login"

    CREDS{"Kredensial valid?"}
    ROLE{"Role admin?"}
    ERR_LOGIN["Tampilkan error login"]

    START --> LP --> CREDS
    CREDS -- "Tidak" --> ERR_LOGIN --> LP
    CREDS -- "Ya" --> ROLE

    %% Admin / User fork
    AD["Dashboard Admin"]
    click AD href "/admin/dashboard.php" "Buka Dashboard Admin"

    UD["Dashboard User"]
    click UD href "/user/dashboard.php" "Buka Dashboard User"

    ROLE -- "Admin" --> AD
    ROLE -- "User" --> UD

    %% Admin Navigation
    subgraph "Admin Pages"
        A_Buku_List["Daftar Buku"]
        click A_Buku_List href "/admin/buku/Daftar_Buku.php" "Kelola daftar buku"

        A_Buku_Add["Tambah Buku"]
        click A_Buku_Add href "/admin/buku/tambah.php" "Tambah buku baru"

        A_Buku_Edit["Edit Buku"]
        click A_Buku_Edit href "/admin/buku/edit.php?id=ID" "Edit buku (contoh)"

        A_Anggota_List["Kelola Anggota"]
        click A_Anggota_List href "/admin/anggota/index.php" "Kelola data anggota"

        A_Import["Import Anggota CSV"]
        click A_Import href "/admin/anggota/import.php" "Import anggota dari CSV"

        A_Scan_NIS["Scan NIS (QR)"]
        click A_Scan_NIS href "/admin/transaksi/scan_nis.php" "Scan QR NIS"

        A_Trans_List["Riwayat Transaksi"]
        click A_Trans_List href "/admin/transaksi/index.php" "Lihat riwayat transaksi"

        A_Laporan["Laporan"]
        click A_Laporan href "/admin/laporan/index.php" "Laporan dan statistik"
    end

    AD --> A_Buku_List
    AD --> A_Buku_Add
    AD --> A_Buku_Edit
    AD --> A_Anggota_List
    AD --> A_Import
    AD --> A_Scan_NIS
    AD --> A_Trans_List
    AD --> A_Laporan

    %% Admin - Peminjaman
    A_Pinjam["Transaksi Peminjaman"]
    click A_Pinjam href "/admin/transaksi/pinjam.php" "Form peminjaman"

    AD --> A_Pinjam

    P_USER_ACTIVE{"Anggota aktif?"}
    P_LIMIT{"Sudah pinjam >= 3?"}
    P_ALREADY{"Sudah pinjam buku ini?"}
    P_STOCK{"Stok tersedia?"}
    P_DATE_OK{"Tanggal valid & <= 14 hari?"}
    P_ERR["Tolak: aturan dilanggar"]
    P_OK["Transaksi dibuat • stok -1"]

    A_Pinjam --> P_USER_ACTIVE
    P_USER_ACTIVE -- "Tidak" --> P_ERR --> A_Pinjam
    P_USER_ACTIVE -- "Ya" --> P_LIMIT
    P_LIMIT -- "Ya" --> P_ERR
    P_LIMIT -- "Tidak" --> P_ALREADY
    P_ALREADY -- "Ya" --> P_ERR
    P_ALREADY -- "Tidak" --> P_STOCK
    P_STOCK -- "Tidak" --> P_ERR
    P_STOCK -- "Ya" --> P_DATE_OK
    P_DATE_OK -- "Tidak" --> P_ERR
    P_DATE_OK -- "Ya" --> P_OK

    %% Admin - Pengembalian
    A_Return["Transaksi Pengembalian"]
    click A_Return href "/admin/transaksi/kembali.php" "Form pengembalian"

    AD --> A_Return

    R_DATE_VALID{"Tanggal kembali ≥ tanggal pinjam?"}
    R_FINE["Hitung denda otomatis 1000/hari"]
    R_DONE["Status dikembalikan • stok +1"]
    R_ERR["Tolak: tanggal tidak valid"]

    A_Return --> R_DATE_VALID
    R_DATE_VALID -- "Tidak" --> R_ERR --> A_Return
    R_DATE_VALID -- "Ya" --> R_FINE --> R_DONE

    %% User Navigation
    subgraph "User Pages"
        U_Katalog["Katalog Buku"]
        click U_Katalog href "/user/katalog.php" "Cari dan lihat buku"

        U_Pinjam["Pinjam (User)"]
        click U_Pinjam href "/user/pinjam.php?buku_id=ID" "Form pinjam user"

        U_Riwayat["Riwayat Pinjam"]
        click U_Riwayat href "/user/riwayat.php" "Lihat riwayat"

        U_Profil["Profil Saya"]
        click U_Profil href "/user/profile.php" "Update profil"
    end

    UD --> U_Katalog
    UD --> U_Riwayat
    UD --> U_Profil

    %% User - Peminjaman dari Katalog
    U_Katalog --> U_Pinjam
    U_LIMIT{"Sedang pinjam >= 3?"}
    U_ALREADY{"Sudah pinjam buku ini?"}
    U_STOCK{"Stok tersedia?"}
    U_DATE_OK{"Tanggal valid & <= 14 hari?"}
    U_ERR["Tolak: aturan dilanggar"]
    U_OK["Transaksi dibuat • stok -1"]

    U_Pinjam --> U_LIMIT
    U_LIMIT -- "Ya" --> U_ERR --> U_Pinjam
    U_LIMIT -- "Tidak" --> U_ALREADY
    U_ALREADY -- "Ya" --> U_ERR
    U_ALREADY -- "Tidak" --> U_STOCK
    U_STOCK -- "Tidak" --> U_ERR
    U_STOCK -- "Ya" --> U_DATE_OK
    U_DATE_OK -- "Tidak" --> U_ERR
    U_DATE_OK -- "Ya" --> U_OK --> U_Riwayat

    %% User - Update Profil
    PROF_UPDATE["Update profil & foto"]
    UD --> U_Profil --> PROF_UPDATE --> U_Profil

    %% Notifikasi Jatuh Tempo (Navbar)
    NOTIF["Notifikasi Jatuh Tempo"]
    AD --> NOTIF
    UD --> NOTIF

    %% End Goals (Konversi)
    GOAL_PINJAM((("Konversi: Peminjaman Berhasil")))
    GOAL_KEMBALI((("Konversi: Pengembalian Berhasil")))
    GOAL_PROFIL((("Konversi: Profil Diperbarui")))

    P_OK --> GOAL_PINJAM
    U_OK --> GOAL_PINJAM
    R_DONE --> GOAL_KEMBALI
    PROF_UPDATE --> GOAL_PROFIL

    END(("End"))
    GOAL_PINJAM --> END
    GOAL_KEMBALI --> END
    GOAL_PROFIL --> END
```

## Kontak

- Maintainer: (isi nama/tim)
- Email: (isi email)
- Issue tracker: gunakan tab **Issues** di GitHub untuk bug report dan feature request

