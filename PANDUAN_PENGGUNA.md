# 📖 BUKU PANDUAN PENGGUNA (USER MANUAL)
**Aplikasi Buku Tamu Digital Terpadu — RSUD Sidoarjo Barat**

---

## 📑 DAFTAR ISI
1. [Pengenalan Aplikasi](#1-pengenalan-aplikasi)
2. [Hak Akses & Akun Demo Sistem](#2-hak-akses--akun-demo-sistem)
3. [Panduan untuk Tamu Publik / Pengunjung](#3-panduan-untuk-tamu-publik--pengunjung)
   - 3.1 [Pendaftaran Kunjungan (Formulir Tamu)](#31-pendaftaran-kunjungan-formulir-tamu)
   - 3.2 [Melihat & Menyimpan Tiket Kunjungan](#32-melihat--menyimpan-tiket-kunjungan)
   - 3.3 [Melacak Status Kunjungan Mandiri](#33-melacak-status-kunjungan-mandiri)
   - 3.4 [Display Layar Monitor Antrean Ruang Tunggu](#34-display-layar-monitor-antrean-ruang-tunggu)
4. [Panduan untuk Petugas Customer Service (CS Desk)](#4-panduan-untuk-petugas-customer-service-cs-desk)
   - 4.1 [Login & Fitur Captcha Penjumlahan](#41-login--fitur-captcha-penjumlahan)
   - 4.2 [Dashboard CS & Pemantauan Antrean](#42-dashboard-cs--pemantauan-antrean)
   - 4.3 [Verifikasi Tamu & Pemberian Nomor Badge](#43-verifikasi-tamu--pemberian-nomor-badge)
   - 4.4 [Cetak Kartu Badge Tamu](#44-cetak-kartu-badge-tamu)
   - 4.5 [Penyelesaian Kunjungan (Tamu Pulang)](#45-penyelesaian-kunjungan-tamu-pulang)
   - 4.6 [Monitoring Ketersediaan Pejabat](#46-monitoring-ketersediaan-pejabat)
5. [Panduan untuk Pejabat / Pegawai Ruangan](#5-panduan-untuk-pejabat--pegawai-ruangan)
   - 5.1 [Login Ruangan / Pejabat](#51-login-ruangan--pejabat)
   - 5.2 [Mengubah Status Ketersediaan Pejabat](#52-mengubah-status-ketersediaan-pejabat)
   - 5.3 [Menerima, Menunda, atau Menolak Kunjungan](#53-menerima-menunda-atau-menolak-kunjungan)
6. [Panduan untuk Administrator Sistem](#6-panduan-untuk-administrator-sistem)
   - 6.1 [Dashboard Statistik Kunjungan](#61-dashboard-statistik-kunjungan)
   - 6.2 [Filter Laporan & Ekspor Data (Excel/CSV/Print)](#62-filter-laporan--ekspor-data-excelcsvprint)
   - 6.3 [Manajemen Master Ruangan](#63-manajemen-master-ruangan)
   - 6.4 [Manajemen Master Pegawai & Pejabat](#64-manajemen-master-pegawai--pejabat)
   - 6.5 [Manajemen Pengguna (User Management)](#65-manajemen-pengguna-user-management)
7. [Tanya Jawab & Troubleshooting (FAQ)](#7-tanya-jawab--troubleshooting-faq)

---

## 1. 🌟 Pengenalan Aplikasi

**Buku Tamu Digital** adalah platform terintegrasi untuk mencatat, melacak, dan mengelola alur kunjungan tamu di lingkungan RSUD secara tertib, transparan, dan akurat. Sistem ini menghubungkan:
- **Tamu** (Pendaftaran mandiri di layar Kiosk / Ponsel),
- **Customer Service / CS Desk** (Pemeriksaan identitas, pemberian kartu identitas tamu/badge, dan verifikasi awal),
- **Pejabat / Ruangan** (Persetujuan tatap muka, penundaan, atau penolakan kunjungan),
- **Manajemen / Admin** (Laporan durasi kunjungan dan statistik real-time).

---

## 2. 🔑 Hak Akses & Akun Demo Sistem

Aplikasi menyediakan tombol **Quick Fill Akun Demo** di halaman login untuk mempermudah uji coba tanpa perlu mengetik manual.

| Role / Peran | Username | Password | Deskripsi Tugas & Wewenang |
| :--- | :--- | :--- | :--- |
| **Customer Service** | `cs` | `cs123` | Memverifikasi tamu datang, menerbitkan nomor badge, dan menyelesaikan kunjungan saat tamu pulang. |
| **Ruangan (Bpk B)** | `penunjang` | `penunjang123` | Menerima tamu masuk ruangan, menunda jam temu, menolak kunjungan, dan update status hadir. |
| **Administrator** | `admin` | `admin123` | Mengelola data master ruangan, pegawai, akun pengguna, serta mencetak & mengekspor laporan kunjungan. |

---

## 3. 👤 Panduan untuk Tamu Publik / Pengunjung

### 3.1 Pendaftaran Kunjungan (Formulir Tamu)
1. Akses halaman utama website: `http://localhost/buku-tamu/` (atau scan QR di Kiosk Front Office).
2. Isi formulir pendaftaran dengan data yang valid:
   - **Nama Lengkap**: Masukkan nama lengkap Anda sesuai KTP/Identitas.
   - **Instansi / Perusahaan**: Nama lembaga asal (contoh: *PT. Sumber Sehat*, *Dinas Kesehatan*, atau *Pribadi*).
   - **No. WhatsApp / HP**: Nomor aktif untuk konfirmasi.
   - **No. Identitas**: Nomor KTP / SIM / Paspor (opsional namun disarankan).
   - **Jumlah Orang**: Jumlah rombongan tamu yang hadir bersama Anda.
   - **Ruangan / Unit Kerja Tujuan**: Pilih seksi atau ruangan yang ingin dituju (contoh: *Seksi Penunjang*).
   - **Pejabat yang Ditemui**: Pilih nama pejabat yang bersangkutan (pilihan otomatis menyesuaikan ruangan yang dipilih). Anda juga dapat melihat status ketersediaan pejabat secara langsung (misal: *Ada di Tempat*, *Sedang Rapat*, dll).
   - **Status Perjanjian**: Pilih apakah *Sudah Buat Janji* (sertakan jam perjanjian) atau *Belum Ada Janji*.
   - **Keperluan Kunjungan**: Tuliskan maksud dan tujuan kedatangan secara jelas.
3. Klik tombol **"Daftarkan Kunjungan Saya"**. Sistem akan otomatis mencatat jam kedatangan secara presisi.

---

### 3.2 Melihat & Menyimpan Tiket Kunjungan
Setelah formulir berhasil dikirim, layar akan langsung menampilkan **Tiket Digital**:
- **Nomor Registrasi / Kode Kunjungan**: Contoh `TM-20260916-0001`.
- **QR Code Tiket**: Dapat di-scan oleh petugas CS di Front Desk.
- **Ringkasan Kunjungan**: Rincian tujuan ruangan, nama pejabat, dan status saat ini (*Menunggu Verifikasi CS*).
- **Aksi Tersedia**:
  - Klik **"Cetak Bukti Tiket"** untuk mencetak struk atau menyimpan sebagai PDF.
  - Klik **"Lacak Status Kunjungan"** untuk memantau proses secara berkala.

---

### 3.3 Melacak Status Kunjungan Mandiri
Jika Anda sedang menunggu di ruang tunggu dan ingin mengetahui apakah kunjungan sudah diterima oleh pejabat:
1. Klik menu **"Lacak Kunjungan"** pada navigasi atas atau buka `lacak.php`.
2. Masukkan **Kode Kunjungan** Anda (misal: `TM-20260916-0001`) atau **Nomor WhatsApp**.
3. Klik **"Cari Status"**.
4. Sistem akan menampilkan *timeline* perjalanan kunjungan lengkap dengan waktu verifikasi CS, jam mulai pertemuan, atau catatan dari ruangan.

---

### 3.4 Display Layar Monitor Antrean Ruang Tunggu
Pengunjung dapat memantau status antrean tamu yang ditampilkan pada layar TV Informasi di ruang tunggu melalui menu **"Layar Monitor"** (`monitor.php`):
- Menampilkan daftar tamu yang sedang **Menunggu Verifikasi**, **Menunggu Respon Ruangan**, dan **Sedang Bertemu**.
- Layar melakukan pembaruan data otomatis (*live refresh*) setiap 5 detik tanpa perlu me-reload halaman secara manual.

---

## 4. 🎧 Panduan untuk Petugas Customer Service (CS Desk)

### 4.1 Login & Fitur Captcha Penjumlahan
1. Buka menu **"Login Petugas"** (`login.php`).
2. Masukkan Username (`cs`) dan Password (`cs123`).
3. **Verifikasi Captcha Matematika**:
   - Perhatikan kotak soal penjumlahan (contoh: `8 + 4 = ?`).
   - Masukkan jawaban angka yang benar (misal: `12`) ke dalam kolom isian.
   - *(Opsional)* Jika soal kurang jelas, klik tombol **"Ganti Soal"** untuk mendapatkan angka baru secara instan.
4. Klik **"Masuk ke Sistem"**.

---

### 4.2 Dashboard CS & Pemantauan Antrean
Setelah login, Anda akan diarahkan ke **Dashboard Customer Service** (`cs/index.php`):
- **Kartu Statistik**: Menampilkan jumlah total tamu hari ini, tamu menunggu verifikasi, tamu sedang di ruangan, dan kunjungan selesai.
- **Tabel Antrean Masuk**: Menampilkan daftar tamu baru yang baru saja mendaftar.

---

### 4.3 Verifikasi Tamu & Pemberian Nomor Badge
Ketika tamu mendatangi meja Front Desk / CS:
1. Cari data tamu pada tab **"Menunggu Verifikasi CS"**.
2. Klik tombol **"Verifikasi Tamu"** (ikon centang biru).
3. Pada modal verifikasi:
   - Periksa kesesuaian identitas tamu.
   - Masukkan **Nomor Badge / Kartu Tamu** fisik yang diserahkan kepada tamu (contoh: `BADGE-05`).
   - Berikan catatan tambahan bila diperlukan.
4. Klik **"Simpan & Teruskan ke Ruangan"**.
5. Status tamu akan otomatis berpindah menjadi **"Menunggu Respon Ruangan"** dan notifikasi akan muncul di dashboard pejabat ruangan yang dituju.

> **Menolak Tamu**: Jika tamu tidak memenuhi syarat kunjungan, klik tombol **"Tolak"** dan sertakan alasan penolakan.

---

### 4.4 Cetak Kartu Badge Tamu
CS dapat mencetak kartu identitas tamu untuk disematkan di tali gantungan (*lanyard*):
1. Pada data tamu yang telah diverifikasi, klik tombol **"Cetak Kartu"**.
2. Sistem akan membuka jendela cetak kartu identitas standar berukuran saku lengkap dengan foto/nama tamu, instansi, nomor badge, dan QR Code verifikasi.

---

### 4.5 Penyelesaian Kunjungan (Tamu Pulang)
Setelah pertemuan selesai dan tamu kembali ke meja CS untuk mengembalikan kartu badge:
1. Buka tab **"Sedang Bertemu"** pada dashboard CS.
2. Cari nama tamu atau nomor badge yang dikembalikan.
3. Klik tombol **"Selesaikan Kunjungan"** (ikon selesai hijau).
4. Masukkan catatan penutupan (contoh: *Kartu badge telah dikembalikan dalam kondisi baik*).
5. Sistem akan otomatis mencatat jam selesai dan menghitung total durasi pertemuan (menit/jam).

---

### 4.6 Monitoring Ketersediaan Pejabat
Petugas Customer Service dapat memeriksa status kehadiran seluruh pejabat/pegawai sebelum mengarahkan atau mendaftarkan tamu baru:
1. Buka dropdown **"Customer Service"** pada navbar lalu pilih **"Ketersediaan Pejabat"** (`cs/ketersediaan.php`), atau klik tombol hijau **"Cek Ketersediaan Pejabat"** di Dashboard CS.
2. Halaman ini menyajikan:
   - **Ringkasan Metrik**: Total pejabat, jumlah yang *Ada di Tempat*, *Sedang Rapat*, dan *Dinas Luar / Berhalangan*.
   - **Filter Pencarian Cepat**: Filter berdasarkan kata kunci nama/NIP/jabatan, unit kerja/ruangan, atau status ketersediaan.
   - **Kartu Informasi Pejabat**: Menampilkan nama pejabat, jabatan, ruangan, lantai gedung, serta indikator badge ketersediaan (🟢 Ada di Tempat, 🟡 Sedang Rapat, 🔵 Dinas Luar, 🔴 Tidak di Tempat).
   - **Tombol Kontak Cepat WhatsApp**: Klik tombol **"Hubungi WA"** untuk langsung mengirimkan pesan konfirmasi kedatangan tamu via WhatsApp Web/App kepada pejabat terkait.
   - **Tombol Input Tamu**: Klik tombol **"Input Tamu"** untuk langsung mendaftarkan tamu yang ingin menemui pejabat tersebut.

---

## 5. 🏢 Panduan untuk Pejabat / Pegawai Ruangan

### 5.1 Login Ruangan / Pejabat
1. Masuk melalui menu **Login Petugas** menggunakan akun ruangan Anda (contoh akun demo: Username `penunjang`, Password `penunjang123`).
2. Selesaikan soal **Captcha Penjumlahan Matematika**.
3. Klik **"Masuk ke Sistem"**.

---

### 5.2 Mengubah Status Ketersediaan Pejabat
Pejabat dapat memberitahukan status kehadirannya agar tamu dan CS mengetahui kondisi terkini:
1. Pada bagian atas Dashboard Ruangan (`ruangan/index.php`), perhatikan panel **"Status Ketersediaan Pejabat"**.
2. Pilih salah satu status:
   - 🟢 **Ada di Tempat**: Siap menerima kunjungan.
   - 🟡 **Sedang Rapat**: Sedang memimpin / mengikuti rapat internal.
   - 🔵 **Dinas Luar**: Sedang bertugas di luar kantor / rumah sakit.
   - 🔴 **Tidak di Tempat**: Sedang istirahat / berhalangan.
3. Klik **"Simpan Status"**. Status akan langsung diperbarui di formulir pendaftaran tamu dan dashboard CS secara real-time.

---

### 5.3 Menerima, Menunda, atau Menolak Kunjungan
Ketika CS meneruskan tamu ke ruangan Anda, tamu akan muncul di tabel **"Tamu Menunggu Respon Ruangan"**:

- **Menerima Kunjungan (Terima Tamu Masuk)**:
  1. Klik tombol hijau **"Terima & Temui"**.
  2. Berikan arahan ruangan (contoh: *Silakan masuk ke Ruang Kasi Lantai 2*).
  3. Klik **"Konfirmasi Terima"**. Sistem langsung mencatat jam mulai tatap muka.

- **Menunda Kunjungan (Tunda Waktu)**:
  1. Jika Anda sedang menyelesaikan pekerjaan mendesak, klik tombol kuning **"Tunda"**.
  2. Tentukan estimasi **Jam Temu Pengganti** (contoh: `11:00`) dan tuliskan alasannya.
  3. Klik **"Simpan Penundaan"**. Tamu akan mendapatkan informasi penundaan di layar monitor & halaman lacak.

- **Menolak Kunjungan (Tolak)**:
  1. Jika tidak dapat menemui tamu, klik tombol merah **"Tolak"**.
  2. Masukkan alasan penolakan secara jelas dan sopan.
  3. Klik **"Kirim Penolakan"**.

---

## 6. ⚙️ Panduan untuk Administrator Sistem

### 6.1 Dashboard Statistik Kunjungan
Admin memiliki hak akses penuh ke seluruh data dan laporan:
1. Login dengan akun Administrator (`admin` / `admin123`).
2. Masuk ke menu **"Laporan & Rekapitulasi"** (`admin/laporan.php`).
3. Melihat metrik total kunjungan, rerata durasi pertemuan, perbandingan tamu terjadwal vs langsung, serta grafik tren kunjungan harian.

---

### 6.2 Filter Laporan & Ekspor Data (Excel/CSV/Print)
1. Gunakan filter laporan berdasarkan:
   - **Rentang Tanggal** (Tanggal Mulai s/d Tanggal Selesai),
   - **Ruangan / Unit Kerja**,
   - **Status Kunjungan** (*Semua, Selesai, Ditolak, Sedang Bertemu*).
2. Klik **"Filter Laporan"**.
3. Untuk mengunduh dokumen rekapitulasi:
   - Klik **"Ekspor Excel / CSV"** untuk membuka file di Microsoft Excel / Google Sheets.
   - Klik **"Cetak / Print Laporan"** untuk mencetak dokumen fisik dengan kop resmi dan kolom tanda tangan pimpinan.

---

### 6.3 Manajemen Master Ruangan
Buka menu **"Master Ruangan"** (`admin/master_ruangan.php`):
- **Tambah Ruangan Baru**: Klik tombol **"+ Tambah Ruangan"**, isi kode ruangan, nama unit kerja, lokasi lantai, dan nama penanggung jawab.
- **Edit / Nonaktifkan**: Ubah informasi ruangan atau nonaktifkan status jika ruangan sedang tidak aktif.

---

### 6.4 Manajemen Master Pegawai & Pejabat
Buka menu **"Master Pegawai"** (`admin/master_pegawai.php`):
- **Sinkronisasi Otomatis dari Master Ruangan**: Nama pejabat otomatis terhubung dan diselaraskan dengan **Nama Penanggung Jawab** pada Master Ruangan. Anda dapat mengklik tombol **"🔄 Sinkronkan dari Master Ruangan"** kapan saja untuk memperbarui seluruh data pejabat sesuai penanggung jawab ruangan terkini.
- **Nilai Default**: Untuk data yang belum lengkap (seperti NIP, Nomor Telepon/WhatsApp, dan Jabatan), sistem secara otomatis mengisinya dengan tanda strip (`-`).
- **Tambah / Edit Manual**: Anda tetap dapat menambahkan atau mengubah detail pejabat/pegawai secara manual jika dalam satu unit kerja terdapat lebih dari satu pejabat/staf.
- Data pejabat ini yang akan muncul pada dropdown formulir tamu publik dan halaman ketersediaan pejabat CS.

---

### 6.5 Manajemen Pengguna (User Management)
Buka menu **"Master Pengguna"** (`admin/master_user.php`):
- **Tambah Pengguna Baru**: Klik **"+ Tambah Pengguna"**, tentukan username, password, nama lengkap, dan peran (*Admin*, *Customer Service*, atau *Ruangan*).
- **Koneksi Akun ke Master Pegawai**: Untuk akun dengan role *Ruangan*, Anda dapat memilih nama pejabat terhubung agar status ketersediaannya sinkron secara otomatis.
- **Reset Password**: Admin dapat mereset kata sandi akun petugas yang lupa password sewaktu-waktu.

---

## 7. ❓ Tanya Jawab & Troubleshooting (FAQ)

### Q1: Mengapa saat login muncul pesan "Jawaban Captcha penjumlahan salah!"?
> **Jawaban**: Pastikan Anda menghitung hasil penjumlahan angka yang tertera di kotak biru dengan teliti dan mengetikkan hanya angkanya saja. Jika angka terasa sulit, Anda dapat mengklik tombol **"Ganti Soal"** untuk mendapatkan kombinasi angka yang lebih mudah.

### Q2: Apakah aplikasi tetap bisa digunakan jika database MySQL tiba-tiba mati?
> **Jawaban**: **Ya!** Sistem Buku Tamu Digital dilengkapi arsitektur *Auto-Fallback*. Jika server MySQL tidak merespons, sistem secara otomatis mengalihkan penyimpanan ke database lokal SQLite di folder `storage/db_buku_tamu.sqlite` sehingga layanan pendaftaran tamu tidak terhenti.

### Q3: Bagaimana jika tamu salah mengisi nama ruangan saat mendaftar?
> **Jawaban**: Petugas CS dapat memberikan catatan pada saat verifikasi atau mengarahkan tamu untuk mendaftarkan ulang kunjungan dengan memilih ruangan yang tepat.

### Q4: Apakah sistem dapat dibuka melalui ponsel / smartphone?
> **Jawaban**: **Ya.** Seluruh antarmuka aplikasi Buku Tamu Digital dirancang responsif dan kompatibel dengan layar smartphone, tablet, laptop, maupun monitor TV display ruang tunggu.
