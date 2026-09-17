<?php
/**
 * API Ubah Status Ketersediaan Pejabat / Pegawai
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/functions.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' 
       || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

if (!isLoggedIn()) {
    if ($isAjax) {
        jsonResponse('error', 'Silakan login terlebih dahulu.', [], 401);
    }
    setFlash('warning', 'Silakan login terlebih dahulu.');
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$currentUser = currentUser();
$status = clean($_POST['status_ketersediaan'] ?? '');
$redirectUrl = $_POST['redirect_to'] ?? (BASE_URL . 'ruangan/index.php');

$validStatuses = ['Ada di Tempat', 'Sedang Rapat', 'Dinas Luar', 'Tidak di Tempat'];
if (!in_array($status, $validStatuses)) {
    if ($isAjax) {
        jsonResponse('error', 'Status ketersediaan tidak valid.', [], 400);
    }
    setFlash('error', 'Status ketersediaan tidak valid.');
    header("Location: $redirectUrl");
    exit;
}

// Update status ketersediaan
$userId = (int)$currentUser['id'];
$success = User::updateStatusKetersediaan($userId, $status);

if ($success) {
    $pesan = "Status ketersediaan berhasil diubah menjadi: <strong>$status</strong>";
    if ($isAjax) {
        jsonResponse('success', $pesan, [
            'status_ketersediaan' => $status,
            'user' => currentUser()
        ]);
    } else {
        setFlash('success', $pesan);
        header("Location: $redirectUrl");
        exit;
    }
} else {
    $pesan = "Gagal memperbarui status ketersediaan. Pastikan akun telah terhubung dengan data Master Pegawai.";
    if ($isAjax) {
        jsonResponse('error', $pesan, [], 400);
    } else {
        setFlash('error', $pesan);
        header("Location: $redirectUrl");
        exit;
    }
}
