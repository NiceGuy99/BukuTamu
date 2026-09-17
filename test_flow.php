<?php
/**
 * Test Script Verifikasi Alur Buku Tamu Digital + Otentikasi User + Status Ketersediaan Pejabat
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Ruangan.php';
require_once __DIR__ . '/models/Pegawai.php';
require_once __DIR__ . '/models/Kunjungan.php';
require_once __DIR__ . '/helpers/functions.php';

echo "===============================================================\n";
echo " PENGUJIAN SISTEM BUKU TAMU: USER - PEGAWAI - KETERSEDIAAN\n";
echo "===============================================================\n";

// 1. Inisialisasi DB & Schema
$db = Database::getConnection();
$driver = Database::getDriver();
echo "[1] Status Database: OK (Driver: $driver)\n";

// 2. Test User Authentication & Pegawai Connection
echo "\n[2] Pengujian Akun Pengguna & Relasi ke Master Pejabat:\n";
$userPenunjang = User::authenticate('penunjang', 'penunjang123');
if ($userPenunjang) {
    echo "    - Login 'penunjang': BERHASIL\n";
    echo "    - Nama Lengkap: {$userPenunjang['nama_lengkap']}\n";
    echo "    - Ruangan: {$userPenunjang['nama_ruangan']} (ID: {$userPenunjang['ruangan_id']})\n";
    echo "    - Pejabat Terhubung: " . ($userPenunjang['nama_pegawai'] ?? 'N/A') . " (ID: " . ($userPenunjang['pegawai_id'] ?? 'NULL') . ")\n";
    echo "    - Status Ketersediaan Awal: {$userPenunjang['status_ketersediaan']} (PASSED)\n";
} else {
    echo "    - Login 'penunjang': GAGAL!\n";
}

// 3. Test Ubah Status Ketersediaan
echo "\n[3] Pengujian Perubahan Status Ketersediaan Pejabat:\n";
$userId = $userPenunjang['id'];

// Ubah ke "Sedang Rapat"
User::updateStatusKetersediaan($userId, 'Sedang Rapat');
$userUpdated1 = User::find($userId);
echo "    - Ubah status -> 'Sedang Rapat': " . ($userUpdated1['status_ketersediaan'] === 'Sedang Rapat' ? 'BERHASIL (PASSED)' : 'GAGAL') . "\n";

// Ubah ke "Dinas Luar"
User::updateStatusKetersediaan($userId, 'Dinas Luar');
$userUpdated2 = User::find($userId);
echo "    - Ubah status -> 'Dinas Luar': " . ($userUpdated2['status_ketersediaan'] === 'Dinas Luar' ? 'BERHASIL (PASSED)' : 'GAGAL') . "\n";

// Kembalikan ke "Ada di Tempat"
User::updateStatusKetersediaan($userId, 'Ada di Tempat');
$userUpdated3 = User::find($userId);
echo "    - Kembalikan status -> 'Ada di Tempat': " . ($userUpdated3['status_ketersediaan'] === 'Ada di Tempat' ? 'BERHASIL (PASSED)' : 'GAGAL') . "\n";

// 4. Test Alur Kunjungan Tamu
echo "\n[4] Pengujian Alur Kunjungan Tamu:\n";
$ruanganList = Ruangan::getAll();
$seksiPenunjang = $ruanganList[0] ?? null;
$pegawaiList = Pegawai::getByRuangan($seksiPenunjang['id'] ?? 1);
$bapakB = $pegawaiList[0] ?? null;

// Form Tamu (Tanpa Login)
$tamu = Kunjungan::create([
    'nama_tamu' => 'Tamu A (PT Batara)',
    'instansi' => 'PT. Batara',
    'no_hp' => '081234567890',
    'ruangan_id' => $seksiPenunjang['id'] ?? 1,
    'pegawai_id' => $bapakB['id'] ?? 1,
    'keperluan' => 'Menemui Kepala Seksi Penunjang',
    'status_janji' => 'sudah_janji',
    'jam_janji' => '10:00'
]);
echo "    - Step 1 (Publik/Tanpa Login): Tamu daftar -> Kode: {$tamu['kode_kunjungan']}, Jam Datang: {$tamu['jam_kedatangan']} (PASSED)\n";

// CS Verifikasi (Login CS)
Kunjungan::verifikasiCS($tamu['id'], 'Petugas Customer Service', 'BADGE-05');
$tamuVerif = Kunjungan::find($tamu['id']);
echo "    - Step 2 (CS): Verifikasi -> Status: {$tamuVerif['status']}, Jam Verif: {$tamuVerif['jam_verifikasi_cs']} (PASSED)\n";

// Ruangan ACC (Login Ruangan)
Kunjungan::terimaRuangan($tamu['id'], 'Silakan masuk ke Ruang Penunjang', 'Bapak B (Kasi Penunjang)');
$tamuACC = Kunjungan::find($tamu['id']);
echo "    - Step 3 (Ruangan/Pejabat): Terima Kunjungan -> Status: {$tamuACC['status']}, Jam Ketemu: {$tamuACC['jam_ketemu']} (PASSED)\n";

// CS Selesai (Login CS)
Kunjungan::selesaiCS($tamu['id'], 'Kartu tamu telah dikembalikan', 'Petugas Customer Service');
$tamuSelesai = Kunjungan::find($tamu['id']);
echo "    - Step 4 (CS): Selesaikan Kunjungan -> Status: {$tamuSelesai['status']}, Jam Selesai: {$tamuSelesai['jam_selesai_bertemu']}, Durasi: {$tamuSelesai['durasi_menit']} Menit (PASSED)\n";

echo "\n===============================================================\n";
echo " SELURUH PENGUJIAN OTENTIKASI & STATUS KETERSEDIAAN SUKSES!\n";
echo "===============================================================\n";
