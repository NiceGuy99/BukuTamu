# 🏥 Buku Tamu Digital - RSUD Sidoarjo Barat

Sistem Informasi Manajemen Kunjungan Tamu Terpadu Berbasis Web (PHP Native, Bootstrap 5, MySQL / SQLite Auto-Fallback, Real-time TV Queue Display & QR Code Visitor Pass).

---

## 🌟 Fitur Utama

1. **Formulir Registrasi Tamu Mandiri**:
   - Pendaftaran kunjungan tamu secara online & mandiri.
   - Pilihan tujuan ruangan, seksi/bidang, dan pejabat/pegawai yang dituju.
   - Jam kedatangan otomatis tersimpan presisi secara realtime.
   - Notifikasi SweetAlert2 dan nomor registrasi tiket kunjungan.

2. **Lacak Tiket & QR Code Visitor Pass**:
   - Pelacakan status kunjungan secara realtime (Menunggu CS, Menunggu ACC Ruangan, Bertemu, Selesai, Ditunda, Ditolak).
   - Tiket resmi dengan QR Code dan nomor badge tamu.

3. **Customer Service (CS) Desk**:
   - Dashboard pengelolaan antrian tamu masuk.
   - Pemanggilan nomor antrian via Web Speech Synthesis Audio (Suara CS).
   - Cetak kartu identitas tamu (Visitor Pass Badge) dengan QR Code.
   - Monitoring ketersediaan pejabat/pegawai.

4. **Portal ACC Ruangan / Seksi**:
   - Ruangan tujuan dapat langsung merespon tamu: **Terima (Mulai Bertemu)**, **Tunda (Disertai estimasi jam)**, **Tolak (Disertai alasan)**, atau **Selesaikan Kunjungan**.
   - Sound notification notifikasi tamu baru.

5. **Display TV Monitor Lobby (Queue Display)**:
   - Tampilan layar penuh (Fullscreen) untuk TV lobby / ruang tunggu.
   - Real-time auto-polling & running text informasi rumah sakit.
   - Jam digital dan rekap antrian hari ini.

6. **Admin & Rekapitulasi Laporan**:
   - Master Ruangan / Seksi.
   - Master Pejabat & Pegawai (termasuk status ketersediaan: Ada di Tempat, Rapat, Dinas Luar, Cuti, dll).
   - Master User & Hak Akses Multi-Role (Admin, CS, Ruangan).
   - Export Rekap Laporan Kunjungan ke **Excel (.xls)** & **Cetak PDF**.

---

## 🚀 Persyaratan Sistem & Instalasi

### Kebutuhan:
- PHP >= 7.4 / 8.x
- Apache (XAMPP / Laragon)
- MySQL / MariaDB (Opsional - Sistem memiliki auto-fallback ke SQLite)
- Ekstensi PHP: `pdo`, `pdo_mysql`, `pdo_sqlite`

### Langkah Instalasi:
1. Clone repositori ini ke folder `htdocs` (untuk XAMPP):
   ```bash
   git clone https://github.com/NiceGuy99/BukuTamu.git buku-tamu
   ```
2. Buka browser dan akses:
   ```
   http://localhost/buku-tamu/
   ```
3. Database otomatis dibuat (*zero configuration*):
   - Jika MySQL aktif: database `db_buku_tamu` dan seluruh tabel serta data awal otomatis terbuat.
   - Jika MySQL tidak aktif: sistem otomatis beralih ke SQLite di folder `storage/`.

---

## 🔐 Akun Default untuk Login Petugas

Akses login melalui URL: `http://localhost/buku-tamu/login.php`

| Role | Username | Password |
|---|---|---|
| **Administrator** | `admin` | `admin123` |
| **Customer Service** | `cs` | `cs123` |
| **Ruangan Direktur** | `ruangan_dir` | `ruangan123` |
| **Ruangan Tata Usaha** | `ruangan_tu` | `ruangan123` |
| **Ruangan Keuangan** | `ruangan_keu` | `ruangan123` |

---

## 📄 Dokumentasi Lengkap
- [Panduan Pengguna (User Guide)](PANDUAN_PENGGUNA.md)
- [Dokumentasi Teknis & Arsitektur](DOKUMENTASI_TEKNIS.md)
