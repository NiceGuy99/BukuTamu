<?php
/**
 * Halaman Tiket Digital Tamu & Live Status Tracker
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/models/Kunjungan.php';
require_once __DIR__ . '/helpers/functions.php';

$kode = clean($_GET['kode'] ?? '');
if (empty($kode)) {
    setFlash('error', 'Kode tiket tidak ditemukan.');
    header('Location: ' . BASE_URL . 'lacak.php');
    exit;
}

$kunjungan = Kunjungan::findByKode($kode);
if (!$kunjungan) {
    setFlash('error', "Tiket dengan kode $kode tidak ditemukan.");
    header('Location: ' . BASE_URL . 'lacak.php');
    exit;
}

$logs = Kunjungan::getLogs($kunjungan['id']);

$pageTitle = 'Tiket Kunjungan ' . $kunjungan['kode_kunjungan'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Kartu Tiket -->
            <div class="card-custom mb-4">
                <div class="card-header-gradient p-4 text-center">
                    <div class="bg-white p-2 rounded-4 shadow-sm d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo <?= APP_ORG ?>" style="max-height: 50px; max-width: 50px; object-fit: contain;">
                    </div>
                    <div>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold mb-2">
                            <i class="bi bi-ticket-perforated-fill me-1"></i> TIKET KUNJUNGAN RESMI
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1"><?= htmlspecialchars($kunjungan['kode_kunjungan']) ?></h3>
                    <p class="mb-0 text-white-50 small"><?= APP_ORG ?> &bull; <?= formatTanggalIndo($kunjungan['jam_kedatangan']) ?></p>
                </div>

                <div class="card-body p-4">
                    <!-- Status Banner -->
                    <div class="p-3 rounded-3 mb-4 text-center bg-light border">
                        <div class="small text-muted mb-1">Status Kunjungan Saat Ini:</div>
                        <div class="fs-5 fw-bold"><?= renderStatusBadge($kunjungan['status']) ?></div>
                        
                        <?php if ($kunjungan['status'] === 'ditunda'): ?>
                            <div class="alert alert-warning border-0 mt-2 mb-0 py-2 small">
                                <i class="bi bi-clock-history me-1"></i> Ditunda sampai pukul: <strong><?= formatJamIndo($kunjungan['jam_tunda']) ?></strong>
                                <?php if (!empty($kunjungan['alasan_tolak_tunda'])): ?>
                                    <br><span class="text-muted">Alasan: <?= htmlspecialchars($kunjungan['alasan_tolak_tunda']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($kunjungan['status'] === 'sedang_bertemu'): ?>
                            <div class="alert alert-success border-0 mt-2 mb-0 py-2 small">
                                <i class="bi bi-check-circle-fill me-1"></i> Diterima! Jam mulai bertemu: <strong><?= formatJamIndo($kunjungan['jam_ketemu']) ?></strong>
                                <?php if (!empty($kunjungan['catatan_ruangan'])): ?>
                                    <br><span class="text-muted">Catatan Ruangan: <?= htmlspecialchars($kunjungan['catatan_ruangan']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($kunjungan['status'] === 'selesai'): ?>
                            <div class="alert alert-secondary border-0 mt-2 mb-0 py-2 small">
                                <i class="bi bi-check-all me-1"></i> Kunjungan Selesai pada pukul: <strong><?= formatJamIndo($kunjungan['jam_selesai_bertemu']) ?></strong>
                                (Durasi Pertemuan: <strong><?= hitungDurasiTeks($kunjungan['jam_ketemu'], $kunjungan['jam_selesai_bertemu']) ?></strong>)
                            </div>
                        <?php elseif (strpos($kunjungan['status'], 'ditolak') !== false): ?>
                            <div class="alert alert-danger border-0 mt-2 mb-0 py-2 small">
                                <i class="bi bi-x-circle me-1"></i> Kunjungan Ditolak. Alasan: <strong><?= htmlspecialchars($kunjungan['alasan_tolak_tunda'] ?? '-') ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Ringkasan Informasi Tamu & QR Code -->
                    <div class="row g-4 mb-4 align-items-center">
                        <div class="col-md-8">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 140px;">Nama Tamu</td>
                                    <td class="fw-bold">: <?= htmlspecialchars($kunjungan['nama_tamu']) ?> (<?= $kunjungan['jumlah_orang'] ?> Orang)</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Instansi</td>
                                    <td class="fw-semibold">: <?= htmlspecialchars($kunjungan['instansi']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. WhatsApp</td>
                                    <td>: <?= htmlspecialchars($kunjungan['no_hp']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ruangan Tujuan</td>
                                    <td class="fw-semibold text-primary">: <?= htmlspecialchars($kunjungan['nama_ruangan'] ?? '-') ?> (<?= htmlspecialchars($kunjungan['lokasi_lantai'] ?? '') ?>)</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Pejabat Tujuan</td>
                                    <td class="fw-semibold">: <?= htmlspecialchars($kunjungan['nama_pegawai'] ?? 'Umum Ruangan') ?> (<?= htmlspecialchars($kunjungan['jabatan'] ?? '') ?>)</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Keperluan</td>
                                    <td>: <?= nl2br(htmlspecialchars($kunjungan['keperluan'])) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status Janji</td>
                                    <td>: <?= $kunjungan['status_janji'] === 'sudah_janji' ? '<span class="badge bg-success">Sudah Janji (' . htmlspecialchars($kunjungan['jam_janji']) . ')</span>' : '<span class="badge bg-secondary">Belum Ada Janji</span>' ?></td>
                                </tr>
                                <?php if (!empty($kunjungan['no_badge_kartu'])): ?>
                                <tr>
                                    <td class="text-muted">No. Kartu Tamu</td>
                                    <td>: <span class="badge bg-primary fs-6"><?= htmlspecialchars($kunjungan['no_badge_kartu']) ?></span></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-4 text-center border-start">
                            <div id="qrcode" class="d-inline-block p-2 bg-white border rounded-3 mb-2"></div>
                            <div class="small text-muted">Tunjukkan QR Code ini ke petugas Customer Service</div>
                        </div>
                    </div>

                    <!-- Jejak Waktu Otomatis (Timestamp Stepper) -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-clock-history text-primary me-1"></i> Jejak Waktu Kunjungan (Timestamp Otomatis)
                    </h6>
                    <div class="row g-2 mb-4">
                        <!-- 1. Jam Kedatangan -->
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded-3 bg-light text-center h-100 border">
                                <div class="text-muted small mb-1"><i class="bi bi-box-arrow-in-right text-primary"></i> Jam Datang</div>
                                <div class="fw-bold fs-6 text-dark"><?= formatJamIndo($kunjungan['jam_kedatangan']) ?></div>
                                <small class="text-muted" style="font-size: 0.72rem;">Tercatat saat daftar</small>
                            </div>
                        </div>
                        <!-- 2. Jam Verifikasi CS -->
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded-3 bg-light text-center h-100 border">
                                <div class="text-muted small mb-1"><i class="bi bi-shield-check text-info"></i> Verifikasi CS</div>
                                <div class="fw-bold fs-6 text-dark"><?= $kunjungan['jam_verifikasi_cs'] ? formatJamIndo($kunjungan['jam_verifikasi_cs']) : '<span class="text-muted fst-italic">Menunggu</span>' ?></div>
                                <small class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($kunjungan['verified_by'] ?? '-') ?></small>
                            </div>
                        </div>
                        <!-- 3. Jam Ketemu -->
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded-3 bg-light text-center h-100 border">
                                <div class="text-muted small mb-1"><i class="bi bi-person-check text-success"></i> Jam Ketemu</div>
                                <div class="fw-bold fs-6 text-dark"><?= $kunjungan['jam_ketemu'] ? formatJamIndo($kunjungan['jam_ketemu']) : '<span class="text-muted fst-italic">-</span>' ?></div>
                                <small class="text-muted" style="font-size: 0.72rem;"><?= $kunjungan['status'] === 'ditunda' ? 'Jam Disesuaikan' : 'Jam Diterima' ?></small>
                            </div>
                        </div>
                        <!-- 4. Jam Selesai -->
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 rounded-3 bg-light text-center h-100 border">
                                <div class="text-muted small mb-1"><i class="bi bi-check2-all text-secondary"></i> Jam Selesai</div>
                                <div class="fw-bold fs-6 text-dark"><?= $kunjungan['jam_selesai_bertemu'] ? formatJamIndo($kunjungan['jam_selesai_bertemu']) : '<span class="text-muted fst-italic">Belum</span>' ?></div>
                                <small class="text-muted" style="font-size: 0.72rem;"><?= $kunjungan['durasi_menit'] ? $kunjungan['durasi_menit'] . ' Menit' : '-' ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Audit Log Perubahan Status -->
                    <?php if (!empty($logs)): ?>
                        <div class="accordion accordion-flush mb-4" id="accordionLogs">
                            <div class="accordion-item border rounded-3 overflow-hidden">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-2 small fw-semibold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogs">
                                        <i class="bi bi-list-check me-2"></i> Lihat Riwayat Log Aktivitas (<?= count($logs) ?> Entri)
                                    </button>
                                </h2>
                                <div id="collapseLogs" class="accordion-collapse collapse" data-bs-parent="#accordionLogs">
                                    <div class="accordion-body p-0">
                                        <ul class="list-group list-group-flush small">
                                            <?php foreach ($logs as $l): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-start py-2">
                                                    <div>
                                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($l['keterangan']) ?></div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">Oleh: <?= htmlspecialchars($l['aktor'] ?? 'Sistem') ?></div>
                                                    </div>
                                                    <span class="badge bg-light text-dark border"><?= date('H:i:s', strtotime($l['created_at'])) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-2 justify-content-between pt-3 border-top no-print">
                        <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-arrow-left me-1"></i> Form Tamu Baru
                        </a>
                        <div class="d-flex gap-2">
                            <button onclick="window.print()" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="bi bi-printer me-1"></i> Cetak Tiket
                            </button>
                            <button onclick="location.reload()" class="btn btn-primary-custom btn-sm rounded-pill px-3">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extraScript = "
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Generate QR Code
        const qrEl = document.getElementById('qrcode');
        if (qrEl) {
            new QRCode(qrEl, {
                text: '{$kunjungan['kode_kunjungan']}',
                width: 110,
                height: 110,
                colorDark : '#0f172a',
                colorLight : '#ffffff',
                correctLevel : QRCode.CorrectLevel.M
            });
        }

        // Auto Refresh status every 10 seconds if not finished/rejected
        const currentStatus = '{$kunjungan['status']}';
        if (currentStatus !== 'selesai' && !currentStatus.includes('ditolak')) {
            setTimeout(() => {
                location.reload();
            }, 10000);
        }
    });
</script>
";
require_once __DIR__ . '/includes/footer.php'; 
?>
