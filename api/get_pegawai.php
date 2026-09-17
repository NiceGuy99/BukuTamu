<?php
/**
 * API Get Pegawai Berdasarkan Ruangan
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../models/Pegawai.php';

$ruanganId = (int)($_GET['ruangan_id'] ?? 0);

if ($ruanganId <= 0) {
    echo json_encode([]);
    exit;
}

$pegawaiList = Pegawai::getByRuangan($ruanganId);
echo json_encode($pegawaiList);
