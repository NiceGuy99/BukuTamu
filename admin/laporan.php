<?php
/**
 * Laporan & Rekapitulasi Kunjungan Tamu
 * Fitur: Filter tanggal, ruangan, status, statistik rata-rata durasi, Cetak & Export Excel.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Kunjungan.php';
require_once __DIR__ . '/../models/Ruangan.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin
requireLogin(['admin']);

$tglAwal = clean($_GET['tgl_awal'] ?? date('Y-m-d'));
$tglAkhir = clean($_GET['tgl_akhir'] ?? date('Y-m-d'));
$ruanganId = clean($_GET['ruangan_id'] ?? '');
$status = clean($_GET['status'] ?? '');
$search = clean($_GET['search'] ?? '');

$filters = [
    'tgl_awal' => $tglAwal,
    'tgl_akhir' => $tglAkhir,
    'ruangan_id' => $ruanganId,
    'status' => $status,
    'search' => $search
];

// Cek jika aksi export CSV / Excel
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $dataExport = Kunjungan::getLaporan($filters);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Buku_Tamu_' . date('Ymd_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    // BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header CSV
    fputcsv($output, [
        'No', 'Kode Tiket', 'Nama Tamu', 'Instansi', 'No. HP', 'Jumlah Orang',
        'Ruangan Tujuan', 'Pejabat Tujuan', 'Keperluan', 'Status Janji',
        'Jam Datang', 'Jam Verifikasi CS', 'Jam Ketemu', 'Jam Selesai', 'Durasi (Menit)', 'Status', 'Petugas CS'
    ]);
    
    $no = 1;
    foreach ($dataExport as $r) {
        fputcsv($output, [
            $no++,
            $r['kode_kunjungan'],
            $r['nama_tamu'],
            $r['instansi'],
            $r['no_hp'],
            $r['jumlah_orang'],
            $r['nama_ruangan'] ?? '-',
            $r['nama_pegawai'] ?? '-',
            $r['keperluan'],
            $r['status_janji'],
            $r['jam_kedatangan'],
            $r['jam_verifikasi_cs'] ?? '-',
            $r['jam_ketemu'] ?? '-',
            $r['jam_selesai_bertemu'] ?? '-',
            $r['durasi_menit'] ?? 0,
            $r['status'],
            $r['verified_by'] ?? '-'
        ]);
    }
    fclose($output);
    exit;
}

$listRuangan = Ruangan::getAll();
$dataLaporan = Kunjungan::getLaporan($filters);
$stat = Kunjungan::getStatistik($tglAwal, $tglAkhir);

$pageTitle = 'Rekapitulasi Laporan Kunjungan Tamu';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-bold"><i class="bi bi-file-earmark-bar-graph"></i> REKAPITULASI LAPORAN</span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Laporan Kunjungan Tamu</h3>
        </div>

        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
            <a href="<?= BASE_URL ?>admin/laporan.php?<?= http_build_query(array_merge($filters, ['export' => 'excel'])) ?>" class="btn btn-success rounded-pill px-3">
                <i class="bi bi-file-earmark-excel me-1"></i> Export ke Excel (CSV)
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card-custom p-4 mb-4 no-print">
        <form action="<?= BASE_URL ?>admin/laporan.php" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label-custom">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control form-control-custom" value="<?= htmlspecialchars($tglAwal) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control form-control-custom" value="<?= htmlspecialchars($tglAkhir) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom">Ruangan / Seksi</label>
                    <select name="ruangan_id" class="form-select form-select-custom">
                        <option value="">-- Semua Ruangan --</option>
                        <?php foreach ($listRuangan as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $ruanganId == $r['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nama_ruangan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom">Status</label>
                    <select name="status" class="form-select form-select-custom">
                        <option value="">-- Semua Status --</option>
                        <option value="menunggu_verifikasi_cs" <?= $status === 'menunggu_verifikasi_cs' ? 'selected' : '' ?>>Menunggu CS</option>
                        <option value="menunggu_acc_ruangan" <?= $status === 'menunggu_acc_ruangan' ? 'selected' : '' ?>>Menunggu Ruangan</option>
                        <option value="sedang_bertemu" <?= $status === 'sedang_bertemu' ? 'selected' : '' ?>>Sedang Bertemu</option>
                        <option value="ditunda" <?= $status === 'ditunda' ? 'selected' : '' ?>>Ditunda</option>
                        <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="ditolak_cs" <?= $status === 'ditolak_cs' ? 'selected' : '' ?>>Ditolak CS</option>
                        <option value="ditolak_ruangan" <?= $status === 'ditolak_ruangan' ? 'selected' : '' ?>>Ditolak Ruangan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-custom">Pencarian</label>
                    <input type="text" name="search" class="form-control form-control-custom" placeholder="Nama / Instansi / No Tiket..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary-custom w-100 py-2">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-primary border-4">
                <div class="text-muted small fw-semibold">Total Kunjungan</div>
                <div class="fs-3 fw-bold text-primary"><?= $stat['total_kunjungan'] ?? 0 ?> <span class="fs-6 fw-normal text-muted">Tamu</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-success border-4">
                <div class="text-muted small fw-semibold">Kunjungan Selesai</div>
                <div class="fs-3 fw-bold text-success"><?= $stat['selesai'] ?? 0 ?> <span class="fs-6 fw-normal text-muted">Tamu</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-danger border-4">
                <div class="text-muted small fw-semibold">Kunjungan Ditolak</div>
                <div class="fs-3 fw-bold text-danger"><?= $stat['ditolak'] ?? 0 ?> <span class="fs-6 fw-normal text-muted">Tamu</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-info border-4">
                <div class="text-muted small fw-semibold">Rata-rata Durasi Temu</div>
                <div class="fs-3 fw-bold text-info"><?= !empty($stat['rata_durasi_menit']) ? round($stat['rata_durasi_menit']) . ' Menit' : '-' ?></div>
            </div>
        </div>
    </div>

    <!-- Tabel Data Laporan -->
    <div class="card-custom">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-table me-2 text-primary"></i> Data Rekapitulasi Kunjungan (<?= count($dataLaporan) ?> Data)</h5>
            <span class="text-muted small">Periode: <strong><?= formatTanggalIndo($tglAwal) ?></strong> s.d <strong><?= formatTanggalIndo($tglAkhir) ?></strong></span>
        </div>

        <div class="card-body p-0">
            <?php if (empty($dataLaporan)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 text-secondary mb-2"></i>
                    <h6 class="fw-bold">Tidak ada data kunjungan untuk filter yang dipilih</h6>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Kode Tiket</th>
                                <th>Nama Tamu & Instansi</th>
                                <th>Tujuan Ruangan & Pejabat</th>
                                <th>Keperluan</th>
                                <th>Jam Datang</th>
                                <th>Jam Ketemu</th>
                                <th>Jam Selesai</th>
                                <th>Durasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($dataLaporan as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>tiket.php?kode=<?= urlencode($row['kode_kunjungan']) ?>" class="fw-bold text-primary text-decoration-none">
                                            <?= htmlspecialchars($row['kode_kunjungan']) ?>
                                        </a>
                                        <?php if (!empty($row['no_badge_kartu'])): ?>
                                            <div class="badge bg-light text-dark border small"><?= htmlspecialchars($row['no_badge_kartu']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_tamu']) ?> (<?= $row['jumlah_orang'] ?> Org)</div>
                                        <div class="text-muted small"><i class="bi bi-building"></i> <?= htmlspecialchars($row['instansi']) ?></div>
                                        <div class="text-success small"><i class="bi bi-whatsapp"></i> <?= htmlspecialchars($row['no_hp']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($row['nama_pegawai'] ?? '-') ?></div>
                                    </td>
                                    <td style="max-width: 180px;">
                                        <small><?= htmlspecialchars($row['keperluan']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= formatJamIndo($row['jam_kedatangan']) ?></div>
                                        <div class="text-muted" style="font-size: 0.72rem;"><?= date('d/m/Y', strtotime($row['jam_kedatangan'])) ?></div>
                                    </td>
                                    <td><?= $row['jam_ketemu'] ? formatJamIndo($row['jam_ketemu']) : '-' ?></td>
                                    <td><?= $row['jam_selesai_bertemu'] ? formatJamIndo($row['jam_selesai_bertemu']) : '-' ?></td>
                                    <td class="text-center">
                                        <?= $row['durasi_menit'] ? '<span class="badge bg-light text-dark border">' . $row['durasi_menit'] . ' Menit</span>' : '-' ?>
                                    </td>
                                    <td><?= renderStatusBadge($row['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
