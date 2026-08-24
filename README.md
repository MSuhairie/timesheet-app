# Timesheet App (PHP Native)

Aplikasi pencatat aktivitas kerja harian pengganti Excel manual — dibuat dengan
**PHP native (tanpa framework)** + MySQL + Bootstrap 5.

Versi ini adalah **MVP** dengan fitur:
- Dashboard (statistik bulan berjalan + grafik jam kerja 7 hari)
- Profil Karyawan (termasuk data kop surat: nama perusahaan, alamat, telp)
- Daily Activity (tambah/edit/hapus aktivitas, update status cepat, salin task)
- Check In / Check Out (dengan penghitungan jam kerja otomatis)
- Riwayat Aktivitas (filter bulan, tempat kerja, status, pencarian task, salin task)
- **Cetak Laporan** — laporan dengan kop surat per bulan, bisa disimpan sebagai
  PDF (lewat dialog print browser) atau diunduh sebagai file Word (.doc)

Aplikasi ini **tanpa login** — dibuat single user untuk pemakaian pribadi.
Semua data otomatis terikat ke satu profil (id tetap, lihat `CURRENT_USER_ID`
di `config/database.php`). Fitur Export Excel/PDF, Approval, Reminder,
Calendar, dan Attachment belum dibuat, menyusul di tahap berikutnya.

> ⚠️ Karena tanpa login, jangan deploy aplikasi ini ke server publik yang bisa
> diakses siapa saja — cocoknya untuk dipakai di localhost atau jaringan
> pribadi/internal saja.

## 1. Struktur Folder

```
timesheet-app/
├── api/                  # Endpoint AJAX (check-in, check-out, update status)
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── config/database.php   # Konfigurasi koneksi DB + CURRENT_USER_ID
├── database/schema.sql   # Skema tabel + seed profil
├── includes/             # helper umum, header, sidebar, footer (layout)
├── pages/                # dashboard, activity, history, profile, report, report_word
├── index.php              # redirect langsung ke dashboard
└── README.md
```

## 2. Cara Instalasi (XAMPP / Laragon / server PHP lokal)

1. Copy folder `timesheet-app` ke folder web server kamu, misalnya:
   `C:\xampp\htdocs\timesheet-app` (XAMPP) atau `C:\laragon\www\timesheet-app` (Laragon).

2. Buat database dengan mengimpor `database/schema.sql`, contoh via phpMyAdmin
   atau CLI:
   ```
   mysql -u root -p < database/schema.sql
   ```

3. Sesuaikan koneksi database di `config/database.php` jika perlu (user/password
   MySQL berbeda dari default XAMPP `root` tanpa password).

4. Buka di browser:
   ```
   http://localhost/timesheet-app/
   ```
   Langsung masuk ke Dashboard tanpa login.

## 3. Kebutuhan Server

- PHP 8.x dengan ekstensi `pdo_mysql`
- MySQL / MariaDB
- Web server (Apache/Nginx) — bisa juga pakai PHP built-in server untuk testing:
  ```
  php -S localhost:8000
  ```
  lalu buka `http://localhost:8000/`

## 4. Catatan Keamanan

- Semua query menggunakan PDO prepared statement (aman dari SQL Injection).
- Semua output ke HTML di-escape lewat helper `e()` (aman dari XSS).
- Session PHP native hanya dipakai untuk flash message (notifikasi sukses/gagal
  antar halaman), bukan untuk autentikasi — karena memang tidak ada login.
- Karena tanpa login, pastikan aplikasi ini hanya diakses di localhost atau
  jaringan pribadi, bukan server publik.

## 5. Cetak Laporan (PDF & Word)

Menu **Cetak Laporan** di sidebar menampilkan laporan dengan kop surat
(nama perusahaan, alamat, telp — diambil dari halaman Profil Karyawan),
data karyawan, dan tabel aktivitas untuk bulan yang dipilih.

- **PDF**: klik tombol "Cetak / Simpan PDF" → akan membuka dialog print
  browser. Pilih **Save as PDF** / **Simpan sebagai PDF** sebagai printer
  tujuan. Layout sudah diatur supaya navbar/sidebar otomatis disembunyikan
  saat print (`report.php`).
- **Word**: klik tombol "Export Word" → file `.doc` otomatis terunduh dan
  bisa langsung dibuka/diedit di Microsoft Word (`report_word.php`).

Kalau database sudah pernah dibuat sebelum fitur ini ditambahkan, jalankan
dulu 2 baris migrasi di bagian bawah `database/schema.sql` supaya kolom
alamat & telp perusahaan tersedia:
```sql
ALTER TABLE users ADD COLUMN perusahaan_alamat VARCHAR(255) DEFAULT NULL AFTER perusahaan;
ALTER TABLE users ADD COLUMN perusahaan_telp   VARCHAR(100) DEFAULT NULL AFTER perusahaan_alamat;
```

### Logo di Kop Surat (opsional)

Taruh file logo di folder `assets/img/` dengan nama:
- `Logo1.png` → logo sebelah kiri
- `Logo2.png` → elemen/logo sebelah kanan

Ekstensi `.png`, `.jpg`, `.jpeg`, `.webp`, atau `.svg` semua didukung — sistem
akan otomatis mendeteksi file mana yang ada. Kalau file tidak ditemukan,
kop surat tetap tampil tanpa logo (tanpa error).

Di halaman **Cetak Laporan** (`report.php`), logo dimuat lewat path biasa
karena halamannya dibuka langsung di browser (server tetap aktif). Tapi di
**Export Word** (`report_word.php`), logo di-**embed langsung sebagai base64**
ke dalam file — bukan path biasa — karena file `.doc` didownload dan dibuka
lepas dari server, jadi path relatif/URL tidak akan ter-load oleh Word.
Margin halaman Word juga sudah diatur **Narrow (0.5 inch)** di semua sisi.

## 6. Roadmap Selanjutnya (belum dibuat)

- Task Management dengan sub-checklist per task + attachment
- Calendar view
- Reminder check-out / submit laporan
- Approval workflow (Employee → Supervisor)
- Multi-user (Admin / Employee / Supervisor) + role & permission
