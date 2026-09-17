<?php
/**
 * API Handler Aksi Kunjungan
 * Mengelola perubahan status alur:
 * 1. verifikasi_cs -> teruskan ke ruangan
 * 2. tolak_cs -> tolak tamu di CS
 * 3. terima_ruangan -> catat jam_ketemu saat itu juga
 * 4. tolak_ruangan -> catat alasan tolak
 * 5. tunda_ruangan -> set jam tunda, jam_ketemu disesuaikan
 * 6. selesai_cs -> catat jam_selesai_bertemu & hitung durasi
 */

require_once __DIR__ . '/../models/Kunjungan.php';
require_once __DIR__ . '/../helpers/functions.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' 
       || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

// Cek apakah user sedang login
if (!isLoggedIn()) {
    if ($isAjax) {
        jsonResponse('error', 'Sesi login telah berakhir. Silakan login kembali.', [], 401);
    }
    setFlash('warning', 'Silakan login terlebih dahulu.');
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        jsonResponse('error', 'Metode HTTP tidak diizinkan', [], 405);
    }
    header('Location: ' . BASE_URL);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$aksi = clean($_POST['aksi'] ?? '');
$redirectUrl = $_POST['redirect_to'] ?? BASE_URL;

if ($id <= 0 || empty($aksi)) {
    if ($isAjax) {
        jsonResponse('error', 'Parameter ID atau Aksi tidak valid', [], 400);
    }
    setFlash('error', 'Parameter tidak valid.');
    header("Location: $redirectUrl");
    exit;
}

$success = false;
$pesan = '';

switch ($aksi) {
    case 'verifikasi_cs':
        $verifiedBy = clean($_POST['verified_by'] ?? 'Customer Service');
        $noBadge = clean($_POST['no_badge_kartu'] ?? '');
        $catatan = clean($_POST['catatan_cs'] ?? '');
        $success = Kunjungan::verifikasiCS($id, $verifiedBy, $noBadge, $catatan);
        $pesan = $success ? 'Tamu berhasil diverifikasi dan diteruskan ke Pejabat/Ruangan tujuan!' : 'Gagal memverifikasi tamu.';
        break;

    case 'tolak_cs':
        $alasan = clean($_POST['alasan_tolak'] ?? 'Tidak memenuhi syarat kunjungan');
        $aktor = clean($_POST['verified_by'] ?? 'Customer Service');
        $success = Kunjungan::tolakCS($id, $alasan, $aktor);
        $pesan = $success ? 'Kunjungan berhasil ditolak oleh CS.' : 'Gagal menolak kunjungan.';
        break;

    case 'terima_ruangan':
        $catatan = clean($_POST['catatan_ruangan'] ?? 'Silakan masuk ke ruangan.');
        $aktor = clean($_POST['nama_pejabat'] ?? 'Pejabat/Ruangan');
        $success = Kunjungan::terimaRuangan($id, $catatan, $aktor);
        $pesan = $success ? 'Kunjungan DITERIMA! Jam mulai bertemu otomatis tercatat saat ini.' : 'Gagal menerima kunjungan.';
        break;

    case 'tolak_ruangan':
        $alasan = clean($_POST['alasan_tolak'] ?? 'Sedang rapat / tidak di tempat');
        $aktor = clean($_POST['nama_pejabat'] ?? 'Pejabat/Ruangan');
        $success = Kunjungan::tolakRuangan($id, $alasan, $aktor);
        $pesan = $success ? 'Kunjungan DITOLAK oleh Ruangan/Pejabat.' : 'Gagal memproses penolakan.';
        break;

    case 'tunda_ruangan':
        $jamTunda = clean($_POST['jam_tunda'] ?? '');
        $alasan = clean($_POST['alasan_tunda'] ?? 'Sedang ada tugas mendesak');
        $aktor = clean($_POST['nama_pejabat'] ?? 'Pejabat/Ruangan');
        
        if (empty($jamTunda)) {
            $pesan = 'Mohon tentukan jam penundaan!';
            $success = false;
        } else {
            $success = Kunjungan::tundaRuangan($id, $jamTunda, $alasan, $aktor);
            $pesan = $success ? "Kunjungan DITUNDA sampai jam $jamTunda. Jam temu disesuaikan!" : 'Gagal menunda kunjungan.';
        }
        break;

    case 'selesai_cs':
        $catatan = clean($_POST['catatan_cs'] ?? '');
        $aktor = clean($_POST['cs_nama'] ?? 'Customer Service');
        $success = Kunjungan::selesaiCS($id, $catatan, $aktor);
        $pesan = $success ? 'Kunjungan telah SELESAI! Jam selesai otomatis tercatat & durasi telah dihitung.' : 'Gagal menyelesaikan kunjungan.';
        break;

    default:
        $pesan = 'Aksi tidak dikenali.';
        $success = false;
        break;
}

if ($isAjax) {
    jsonResponse($success ? 'success' : 'error', $pesan, [
        'kunjungan' => Kunjungan::find($id)
    ]);
} else {
    setFlash($success ? 'success' : 'error', $pesan);
    header("Location: $redirectUrl");
    exit;
}
