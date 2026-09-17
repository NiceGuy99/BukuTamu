<?php
/**
 * Konfigurasi Utama Aplikasi Buku Tamu Digital
 */

// Set Timezone default ke Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Start Session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Aplikasi
define('APP_NAME', 'Buku Tamu Digital');
define('APP_ORG', 'RSUD Sidoarjo Barat');
define('APP_SUBTITLE', 'Sistem Manajemen Kunjungan Tamu Terpadu');
define('APP_VERSION', '1.0.0');

// Base URL otomatis mendeteksi path
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Sesuaikan base URL jika diakses dari subfolder
$rootPath = preg_replace('/(\/cs|\/ruangan|\/admin|\/api|\/includes|\/helpers|\/models|\/config).*$/', '', $scriptDir);
define('BASE_URL', rtrim($protocol . $host . $rootPath, '/') . '/');

// Direktori Aplikasi
define('BASE_PATH', realpath(__DIR__ . '/..') . '/');
define('STORAGE_PATH', BASE_PATH . 'storage/');

// Pastikan folder storage ada
if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0777, true);
}
