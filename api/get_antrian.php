<?php
/**
 * API Get Antrian Realtime (Untuk Display Monitor TV & Live Refresh)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../models/Kunjungan.php';
require_once __DIR__ . '/../helpers/functions.php';

$ruanganId = !empty($_GET['ruangan_id']) ? (int)$_GET['ruangan_id'] : null;

$antrian = Kunjungan::getAntrianAktif($ruanganId);
$statistik = Kunjungan::getStatistik(date('Y-m-d'), date('Y-m-d'));

$formatted = [];
foreach ($antrian as $item) {
    $formatted[] = [
        'id' => $item['id'],
        'kode_kunjungan' => $item['kode_kunjungan'],
        'nama_tamu' => $item['nama_tamu'],
        'instansi' => $item['instansi'],
        'nama_ruangan' => $item['nama_ruangan'] ?? 'Umum',
        'nama_pegawai' => $item['nama_pegawai'] ?? '-',
        'keperluan' => $item['keperluan'],
        'status' => $item['status'],
        'badge_html' => renderStatusBadge($item['status']),
        'jam_kedatangan' => formatJamIndo($item['jam_kedatangan']),
        'jam_ketemu' => $item['jam_ketemu'] ? formatJamIndo($item['jam_ketemu']) : '-',
        'jam_tunda' => $item['jam_tunda'] ? formatJamIndo($item['jam_tunda']) : null,
        'alasan_tolak_tunda' => $item['alasan_tolak_tunda'],
        'no_badge_kartu' => $item['no_badge_kartu'] ?? '-'
    ];
}

echo json_encode([
    'status' => 'success',
    'total_antrian' => count($formatted),
    'statistik' => $statistik,
    'data' => $formatted,
    'server_time' => date('H:i:s'),
    'server_date' => formatTanggalIndo(date('Y-m-d'))
]);
