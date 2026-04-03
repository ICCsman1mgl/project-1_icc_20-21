# Panduan Migrasi Implementasi

## Dampak Perubahan

Perubahan ini menambah lapisan keamanan dan mengubah beberapa asumsi deployment:

- Konfigurasi aplikasi sekarang mendukung environment variables.
- Form POST kritis sekarang wajib membawa CSRF token.
- Security headers aktif secara default.
- Redirect HTTPS dapat diaktifkan melalui environment variable.
- Logging aplikasi menulis ke `storage/logs/app.log`.

## Langkah Migrasi

### 1. Siapkan Environment Variables

Atur variabel berikut di server:

- `APP_ENV=production`
- `APP_DEBUG=0`
- `BASE_URL=/LibraryManagement`
- `DB_HOST=<host-db>`
- `DB_USERNAME=<user-non-root>`
- `DB_PASSWORD=<password-kuat>`
- `DB_NAME=perpustakaan`
- `APP_LOG_PATH=<path-log-yang-bisa-ditulis>`
- `ENABLE_HTTPS_REDIRECT=1` setelah TLS siap

### 2. Pastikan Folder Writable

- `proses/uploads/`
- `storage/logs/`

### 3. Verifikasi HTTPS

- Pasang sertifikat TLS di web server / reverse proxy.
- Baru setelah itu aktifkan `ENABLE_HTTPS_REDIRECT=1`.

### 4. Hapus Rahasia yang Tidak Boleh Dideploy

- Pastikan tidak ada file plaintext berisi password/admin credential.
- Simpan kredensial hanya di environment/secret manager.

### 5. Uji Setelah Deploy

Jalankan:

```bash
php tests.php
php tests/integration.php
php tests/http_integration.php
```

## Rollback

Jika perlu rollback cepat:

- Nonaktifkan `ENABLE_HTTPS_REDIRECT`.
- Kembalikan `APP_DEBUG=1` hanya di staging/local.
- Pastikan server tetap bisa menulis log untuk investigasi.

## Catatan Operasional

- Fitur backup dari UI sengaja dibatasi; gunakan snapshot/PITR atau backup terjadwal di level infrastruktur.
- Monitor log aplikasi secara berkala untuk mendeteksi login gagal dan error runtime.
