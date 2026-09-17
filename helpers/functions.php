<?php
/**
 * Helper Functions Aplikasi Buku Tamu Digital
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Format Tanggal Indonesia
 */
function formatTanggalIndo($datetime) {
    if (!$datetime || $datetime === '0000-00-00 00:00:00') return '-';
    $timestamp = strtotime($datetime);
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $namaHari = $hari[date('w', $timestamp)];
    $tgl = date('d', $timestamp);
    $namaBulan = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    
    return "$namaHari, $tgl $namaBulan $thn";
}

/**
 * Format Jam Indonesia (contoh: 14:30 WIB)
 */
function formatJamIndo($datetime, $withSuffix = true) {
    if (!$datetime || $datetime === '0000-00-00 00:00:00' || $datetime === '00:00:00') return '-';
    $timestamp = strtotime($datetime);
    return date('H:i', $timestamp) . ($withSuffix ? ' WIB' : '');
}

/**
 * Format Tanggal dan Jam
 */
function formatDatetimeIndo($datetime) {
    if (!$datetime || $datetime === '0000-00-00 00:00:00') return '-';
    return formatTanggalIndo($datetime) . ' - ' . formatJamIndo($datetime);
}

/**
 * Hitung Durasi Antara Dua Timestamp (dalam menit dan format teks)
 */
function hitungDurasiTeks($mulai, $selesai) {
    if (!$mulai || !$selesai) return '-';
    $start = strtotime($mulai);
    $end = strtotime($selesai);
    
    if ($end <= $start) return '0 Menit';
    
    $diffMinutes = round(($end - $start) / 60);
    
    if ($diffMinutes < 60) {
        return "$diffMinutes Menit";
    }
    
    $hours = floor($diffMinutes / 60);
    $mins = $diffMinutes % 60;
    return $mins > 0 ? "{$hours} Jam {$mins} Menit" : "{$hours} Jam";
}

/**
 * Render Badge Status dengan Warna dan Ikon Menarik
 */
function renderStatusBadge($status, $extra = '') {
    $map = [
        'menunggu_verifikasi_cs' => [
            'label' => 'Menunggu CS',
            'desc' => 'Menunggu Verifikasi Customer Service',
            'class' => 'badge-status-warning',
            'icon' => 'bi-hourglass-split',
            'color' => '#f59e0b'
        ],
        'menunggu_acc_ruangan' => [
            'label' => 'Menunggu Ruangan',
            'desc' => 'Diverifikasi CS, Menunggu Respon Ruangan',
            'class' => 'badge-status-info',
            'icon' => 'bi-building-check',
            'color' => '#0284c7'
        ],
        'sedang_bertemu' => [
            'label' => 'Sedang Bertemu',
            'desc' => 'Kunjungan Diterima & Sedang Bertemu',
            'class' => 'badge-status-success',
            'icon' => 'bi-person-check-fill',
            'color' => '#10b981'
        ],
        'ditunda' => [
            'label' => 'Ditunda',
            'desc' => 'Kunjungan Ditunda oleh Ruangan',
            'class' => 'badge-status-orange',
            'icon' => 'bi-clock-history',
            'color' => '#ea580c'
        ],
        'ditolak_cs' => [
            'label' => 'Ditolak CS',
            'desc' => 'Kunjungan Ditolak oleh CS',
            'class' => 'badge-status-danger',
            'icon' => 'bi-x-circle-fill',
            'color' => '#ef4444'
        ],
        'ditolak_ruangan' => [
            'label' => 'Ditolak Ruangan',
            'desc' => 'Kunjungan Ditolak oleh Ruangan/Pejabat',
            'class' => 'badge-status-danger',
            'icon' => 'bi-x-octagon-fill',
            'color' => '#ef4444'
        ],
        'selesai' => [
            'label' => 'Selesai',
            'desc' => 'Kunjungan Telah Selesai',
            'class' => 'badge-status-secondary',
            'icon' => 'bi-check-all',
            'color' => '#64748b'
        ]
    ];
    
    $item = $map[$status] ?? [
        'label' => ucfirst(str_replace('_', ' ', $status)),
        'desc' => '-',
        'class' => 'badge-status-secondary',
        'icon' => 'bi-info-circle',
        'color' => '#64748b'
    ];
    
    $extraText = $extra ? " <small>($extra)</small>" : '';
    return "<span class=\"badge-status {$item['class']}\" title=\"{$item['desc']}\"><i class=\"bi {$item['icon']} me-1\"></i> {$item['label']}{$extraText}</span>";
}

/**
 * Generate Kode Kunjungan Unik (Format: TM-YYYYMMDD-XXXX)
 */
function generateKodeKunjungan($db) {
    $today = date('Ymd');
    $prefix = "TM-{$today}-";
    
    $stmt = $db->prepare("SELECT kode_kunjungan FROM kunjungan WHERE kode_kunjungan LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(["$prefix%"]);
    $last = $stmt->fetch();
    
    if ($last) {
        $lastNumber = (int)substr($last['kode_kunjungan'], -4);
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '0001';
    }
    
    return $prefix . $nextNumber;
}

/**
 * Flash Message Handler
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitize input data
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Helper JSON Response
 */
function jsonResponse($status, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// =========================================================================
// AUTHENTICATION & ACCESS CONTROL HELPERS
// =========================================================================

/**
 * Cek apakah user sedang login
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['auth_user']);
}

/**
 * Dapatkan data user yang sedang login
 */
function currentUser(): ?array {
    return $_SESSION['auth_user'] ?? null;
}

/**
 * Set session login
 */
function loginUser(array $user): void {
    $_SESSION['auth_user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'nama_lengkap' => $user['nama_lengkap'],
        'role' => $user['role'], // 'admin', 'cs', 'ruangan'
        'ruangan_id' => $user['ruangan_id'] ?? null,
        'nama_ruangan' => $user['nama_ruangan'] ?? null,
        'pegawai_id' => $user['pegawai_id'] ?? null,
        'nama_pegawai' => $user['nama_pegawai'] ?? null,
        'jabatan' => $user['jabatan'] ?? null,
        'status_ketersediaan' => $user['status_ketersediaan'] ?? 'Ada di Tempat'
    ];
}

/**
 * Hapus session login (Logout)
 */
function logoutUser(): void {
    unset($_SESSION['auth_user']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Middleware Guard: Wajib Login & Role Access
 */
function requireLogin(array $allowedRoles = []): void {
    if (!isLoggedIn()) {
        $currentUrl = $_SERVER['REQUEST_URI'] ?? BASE_URL;
        $_SESSION['redirect_after_login'] = $currentUrl;
        setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    if (!empty($allowedRoles)) {
        $user = currentUser();
        $userRole = $user['role'] ?? '';
        
        // Role 'admin' memiliki akses ke semua halaman
        if ($userRole !== 'admin' && !in_array($userRole, $allowedRoles)) {
            setFlash('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            
            // Redirect sesuai role
            if ($userRole === 'cs') {
                header('Location: ' . BASE_URL . 'cs/index.php');
            } elseif ($userRole === 'ruangan') {
                header('Location: ' . BASE_URL . 'ruangan/index.php');
            } else {
                header('Location: ' . BASE_URL);
            }
            exit;
        }
    }
}
