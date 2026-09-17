<?php
/**
 * Dashboard Customer Service (CS Desk)
 * Fungsi:
 * 1. Verifikasi Tamu Masuk (Catat jam_verifikasi_cs & Teruskan ke Ruangan)
 * 2. Tolak Kunjungan
 * 3. Selesaikan Kunjungan saat tamu lapor kembali (Catat jam_selesai_bertemu & hitung durasi)
 * 4. Input Tamu Walk-in langsung dari meja CS
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Kunjungan.php';
require_once __DIR__ . '/../models/Ruangan.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin atau Customer Service
requireLogin(['admin', 'cs']);
$currentUser = currentUser();

// Ambil semua data kunjungan hari ini
$kunjunganHariIni = Kunjungan::getHariIni();
$listRuangan = Ruangan::getAll();

// Kelompokkan berdasarkan status
$menungguVerif = array_filter($kunjunganHariIni, fn($k) => $k['status'] === 'menunggu_verifikasi_cs');
$menungguRuangan = array_filter($kunjunganHariIni, fn($k) => $k['status'] === 'menunggu_acc_ruangan');
$sedangBertemu = array_filter($kunjunganHariIni, fn($k) => $k['status'] === 'sedang_bertemu');
$ditunda = array_filter($kunjunganHariIni, fn($k) => $k['status'] === 'ditunda');
$selesai = array_filter($kunjunganHariIni, fn($k) => $k['status'] === 'selesai' || strpos($k['status'], 'ditolak') !== false);

// Statistik Hari Ini
$stat = Kunjungan::getStatistik(date('Y-m-d'), date('Y-m-d'));

$pageTitle = 'Dashboard Customer Service (CS Desk)';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <!-- Header CS -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold"><i class="bi bi-headset"></i> LOKET CS DESK</span>
                <span class="text-muted small"><i class="bi bi-clock"></i> <span id="liveClock"><?= date('H:i:s') ?> WIB</span></span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Manajemen Antrian Kunjungan CS</h3>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>cs/ketersediaan.php" class="btn btn-outline-success rounded-pill px-3">
                <i class="bi bi-person-lines-fill me-1"></i> Cek Ketersediaan Pejabat
            </a>
            <button class="btn btn-outline-primary rounded-pill px-3" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
            </button>
            <button class="btn btn-primary-custom rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahWalkin">
                <i class="bi bi-plus-circle-fill me-1"></i> + Input Tamu Walk-in
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-warning border-4">
                <div class="text-muted small fw-semibold">Menunggu Verifikasi CS</div>
                <div class="fs-3 fw-bold text-warning"><?= count($menungguVerif) ?> <span class="fs-6 fw-normal text-muted">Tamu</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-info border-4">
                <div class="text-muted small fw-semibold">Menunggu ACC Ruangan</div>
                <div class="fs-3 fw-bold text-info"><?= count($menungguRuangan) ?> <span class="fs-6 fw-normal text-muted">Tamu</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-success border-4">
                <div class="text-muted small fw-semibold">Sedang Bertemu (Aktif)</div>
                <div class="fs-3 fw-bold text-success"><?= count($sedangBertemu) ?> <span class="fs-6 fw-normal text-muted">Tamu</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-secondary border-4">
                <div class="text-muted small fw-semibold">Selesai Hari Ini</div>
                <div class="fs-3 fw-bold text-secondary"><?= $stat['selesai'] ?? 0 ?> <span class="fs-6 fw-normal text-muted">Tamu</span></div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs Antrian CS -->
    <div class="card-custom">
        <div class="card-header bg-white border-bottom p-3">
            <ul class="nav nav-pills gap-2" id="csTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold position-relative px-4" id="tab-verif" data-bs-toggle="pill" data-bs-target="#content-verif" type="button">
                        <i class="bi bi-hourglass-split me-1 text-warning"></i> Perlu Verifikasi CS
                        <?php if (count($menungguVerif) > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-1"><?= count($menungguVerif) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold px-4" id="tab-aktif" data-bs-toggle="pill" data-bs-target="#content-aktif" type="button">
                        <i class="bi bi-person-check-fill me-1 text-success"></i> Sedang Bertemu & Respon (<?= count($sedangBertemu) + count($menungguRuangan) + count($ditunda) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold px-4" id="tab-selesai" data-bs-toggle="pill" data-bs-target="#content-selesai" type="button">
                        <i class="bi bi-check-all me-1 text-secondary"></i> Selesai / Riwayat Hari Ini (<?= count($selesai) ?>)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="csTabContent">
                <!-- TAB 1: PERLU VERIFIKASI CS -->
                <div class="tab-pane fade show active p-3" id="content-verif" role="tabpanel">
                    <?php if (empty($menungguVerif)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                            <h6 class="fw-bold">Tidak ada tamu yang menunggu verifikasi saat ini</h6>
                            <p class="small mb-0">Semua pendaftaran tamu baru telah diproses.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Tiket</th>
                                        <th>Nama Tamu & Instansi</th>
                                        <th>Ruangan & Pejabat Tujuan</th>
                                        <th>Keperluan</th>
                                        <th>Jam Datang</th>
                                        <th>Status Janji</th>
                                        <th class="text-center">Aksi CS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menungguVerif as $row): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary"><?= htmlspecialchars($row['kode_kunjungan']) ?></span>
                                                <div class="small text-muted"><?= $row['jumlah_orang'] ?> Orang</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_tamu']) ?></div>
                                                <div class="small text-muted"><i class="bi bi-building"></i> <?= htmlspecialchars($row['instansi']) ?></div>
                                                <div class="small text-success"><i class="bi bi-whatsapp"></i> <?= htmlspecialchars($row['no_hp']) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-primary"><?= htmlspecialchars($row['nama_ruangan'] ?? 'Umum') ?></div>
                                                <div class="small text-muted"><i class="bi bi-person"></i> <?= htmlspecialchars($row['nama_pegawai'] ?? 'Umum Ruangan') ?></div>
                                            </td>
                                            <td style="max-width: 200px;">
                                                <small><?= nl2br(htmlspecialchars($row['keperluan'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="badge bg-light text-dark border fs-6">
                                                    <i class="bi bi-clock me-1 text-primary"></i> <?= formatJamIndo($row['jam_kedatangan']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?= $row['status_janji'] === 'sudah_janji' ? '<span class="badge bg-success-subtle text-success border border-success">Sudah Janji (' . htmlspecialchars($row['jam_janji']) . ')</span>' : '<span class="badge bg-secondary-subtle text-secondary">Belum Ada Janji</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="btn btn-sm btn-success rounded-pill px-3 fw-semibold btn-modal-verif" 
                                                            data-id="<?= $row['id'] ?>"
                                                            data-kode="<?= htmlspecialchars($row['kode_kunjungan']) ?>"
                                                            data-nama="<?= htmlspecialchars($row['nama_tamu']) ?>"
                                                            data-instansi="<?= htmlspecialchars($row['instansi']) ?>"
                                                            data-tujuan="<?= htmlspecialchars(($row['nama_ruangan'] ?? '') . ' - ' . ($row['nama_pegawai'] ?? '')) ?>">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Verifikasi
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-modal-tolak"
                                                            data-id="<?= $row['id'] ?>"
                                                            data-kode="<?= htmlspecialchars($row['kode_kunjungan']) ?>"
                                                            data-nama="<?= htmlspecialchars($row['nama_tamu']) ?>">
                                                        <i class="bi bi-x-circle"></i> Tolak
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 2: SEDANG BERTEMU & RESPON RUANGAN -->
                <div class="tab-pane fade p-3" id="content-aktif" role="tabpanel">
                    <?php 
                    $tamuAktif = array_merge($menungguRuangan, $ditunda, $sedangBertemu);
                    if (empty($tamuAktif)): 
                    ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 text-secondary mb-2"></i>
                            <h6 class="fw-bold">Tidak ada tamu yang sedang aktif bertemu atau menunggu respon ruangan</h6>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Tiket / Badge</th>
                                        <th>Nama Tamu & Instansi</th>
                                        <th>Ruangan / Pejabat Tujuan</th>
                                        <th>Status Kunjungan</th>
                                        <th>Waktu Bertemu</th>
                                        <th class="text-center">Aksi CS (Selesai Bertemu)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tamuAktif as $row): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-primary"><?= htmlspecialchars($row['kode_kunjungan']) ?></div>
                                                <?php if (!empty($row['no_badge_kartu'])): ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary"><i class="bi bi-credit-card me-1"></i> <?= htmlspecialchars($row['no_badge_kartu']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_tamu']) ?></div>
                                                <div class="small text-muted"><i class="bi bi-building"></i> <?= htmlspecialchars($row['instansi']) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_ruangan'] ?? 'Umum') ?></div>
                                                <div class="small text-muted"><i class="bi bi-person"></i> <?= htmlspecialchars($row['nama_pegawai'] ?? '-') ?></div>
                                            </td>
                                            <td>
                                                <?= renderStatusBadge($row['status']) ?>
                                                <?php if ($row['status'] === 'ditunda'): ?>
                                                    <div class="small text-danger mt-1">Ditunda s.d: <strong><?= formatJamIndo($row['jam_tunda']) ?></strong></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><span class="text-muted">Datang:</span> <?= formatJamIndo($row['jam_kedatangan']) ?></div>
                                                    <?php if ($row['jam_ketemu']): ?>
                                                        <div class="fw-semibold text-success"><span class="text-muted">Ketemu:</span> <?= formatJamIndo($row['jam_ketemu']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <!-- Tombol Selesaikan Kunjungan saat tamu lapor kembali ke CS -->
                                                    <button class="btn btn-sm btn-primary-custom rounded-pill px-3 btn-modal-selesai"
                                                            data-id="<?= $row['id'] ?>"
                                                            data-kode="<?= htmlspecialchars($row['kode_kunjungan']) ?>"
                                                            data-nama="<?= htmlspecialchars($row['nama_tamu']) ?>"
                                                            data-instansi="<?= htmlspecialchars($row['instansi']) ?>"
                                                            data-ketemu="<?= $row['jam_ketemu'] ? formatJamIndo($row['jam_ketemu']) : formatJamIndo($row['jam_kedatangan']) ?>">
                                                        <i class="bi bi-check2-all me-1"></i> Selesaikan Kunjungan
                                                    </button>
                                                    <a href="<?= BASE_URL ?>cs/cetak_kartu.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill" title="Cetak Kartu Tamu">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 3: SELESAI / RIWAYAT HARI INI -->
                <div class="tab-pane fade p-3" id="content-selesai" role="tabpanel">
                    <?php if (empty($selesai)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-1 text-secondary mb-2"></i>
                            <h6 class="fw-bold">Belum ada kunjungan yang diselesaikan hari ini</h6>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Tiket</th>
                                        <th>Nama Tamu & Instansi</th>
                                        <th>Tujuan Ruangan</th>
                                        <th>Status</th>
                                        <th>Jam Datang</th>
                                        <th>Jam Selesai</th>
                                        <th>Total Durasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selesai as $row): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($row['kode_kunjungan']) ?></td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_tamu']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($row['instansi']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?></td>
                                            <td><?= renderStatusBadge($row['status']) ?></td>
                                            <td><?= formatJamIndo($row['jam_kedatangan']) ?></td>
                                            <td><?= $row['jam_selesai_bertemu'] ? formatJamIndo($row['jam_selesai_bertemu']) : '-' ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= $row['durasi_menit'] ? $row['durasi_menit'] . ' Menit' : '-' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>tiket.php?kode=<?= urlencode($row['kode_kunjungan']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL VERIFIKASI CS -->
<!-- ========================================== -->
<div class="modal fade" id="modalVerifikasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>api/aksi_kunjungan.php" method="POST">
                <input type="hidden" name="id" id="verif_id">
                <input type="hidden" name="aksi" value="verifikasi_cs">
                <input type="hidden" name="redirect_to" value="<?= BASE_URL ?>cs/index.php">

                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2"></i> Verifikasi Kunjungan Tamu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="small text-muted">Kode Tiket:</div>
                        <div class="fw-bold fs-5 text-primary" id="verif_kode">-</div>
                        <div class="fw-semibold text-dark" id="verif_tamu">-</div>
                        <div class="small text-muted" id="verif_tujuan">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Nomor Badge / Kartu Tamu yang Diberikan</label>
                        <input type="text" name="no_badge_kartu" class="form-control form-control-custom" placeholder="Contoh: Badge #01 / Kartu Tamu A1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Nama Petugas CS</label>
                        <input type="text" name="verified_by" class="form-control form-control-custom" value="<?= htmlspecialchars($currentUser['nama_lengkap'] ?? 'Petugas CS') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Catatan Tambahan CS (Opsional)</label>
                        <textarea name="catatan_cs" class="form-control form-control-custom" rows="2" placeholder="Contoh: Identitas KTP terverifikasi."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="bi bi-send-check-fill me-1"></i> Verifikasi & Teruskan ke Ruangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TOLAK CS -->
<!-- ========================================== -->
<div class="modal fade" id="modalTolak" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>api/aksi_kunjungan.php" method="POST">
                <input type="hidden" name="id" id="tolak_id">
                <input type="hidden" name="aksi" value="tolak_cs">
                <input type="hidden" name="redirect_to" value="<?= BASE_URL ?>cs/index.php">

                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2"></i> Tolak Kunjungan Tamu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted">Apakah Anda yakin ingin menolak kunjungan tamu <strong id="tolak_nama">-</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label-custom">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_tolak" class="form-control form-control-custom" rows="3" placeholder="Sebutkan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">
                        Tolak Kunjungan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL SELESAI CS (CHECK-OUT) -->
<!-- ========================================== -->
<div class="modal fade" id="modalSelesai" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>api/aksi_kunjungan.php" method="POST">
                <input type="hidden" name="id" id="selesai_id">
                <input type="hidden" name="aksi" value="selesai_cs">
                <input type="hidden" name="redirect_to" value="<?= BASE_URL ?>cs/index.php">

                <div class="modal-header card-header-gradient text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-check2-all me-2"></i> Selesaikan Kunjungan Tamu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 mb-3 small">
                        <i class="bi bi-info-circle-fill me-1"></i> Tamu telah selesai bertemu dan melapor kembali ke Customer Service. Saat Anda klik Selesaikan, sistem akan <strong>otomatis mencatat Jam Selesai</strong> saat ini dan menghitung total durasi pertemuan.
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="small text-muted">Tamu:</div>
                        <div class="fw-bold fs-5 text-dark" id="selesai_tamu">-</div>
                        <div class="text-muted small">Waktu Mulai: <strong id="selesai_ketemu">-</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Catatan Akhir CS / Pengembalian Kartu Tamu</label>
                        <input type="text" name="catatan_cs" class="form-control form-control-custom" placeholder="Contoh: Kartu Tamu #01 telah dikembalikan lengkap.">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i> Selesaikan Kunjungan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL INPUT TAMU WALKIN DI CS -->
<!-- ========================================== -->
<div class="modal fade" id="modalTambahWalkin" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>index.php" method="POST">
                <div class="modal-header card-header-gradient text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Input Tamu Walk-in (Loket CS)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Nama Lengkap Tamu <span class="text-danger">*</span></label>
                            <input type="text" name="nama_tamu" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Instansi / Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="instansi" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                            <input type="tel" name="no_hp" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Ruangan / Seksi Tujuan <span class="text-danger">*</span></label>
                            <select name="ruangan_id" id="ruangan_id_modal" class="form-select form-select-custom" required>
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($listRuangan as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Pejabat / Pegawai Tujuan</label>
                            <select name="pegawai_id" id="pegawai_id_modal" class="form-select form-select-custom" disabled>
                                <option value="">-- Pilih Ruangan Terlebih Dahulu --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Status Janji</label>
                            <select name="status_janji" class="form-select form-select-custom">
                                <option value="belum_janji">Belum Ada Janji</option>
                                <option value="sudah_janji">Sudah Punya Janji</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Keperluan Kunjungan <span class="text-danger">*</span></label>
                            <textarea name="keperluan" class="form-control form-control-custom" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pendaftaran Tamu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScript = "
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal Verifikasi CS Trigger
        document.querySelectorAll('.btn-modal-verif').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('verif_id').value = this.dataset.id;
                document.getElementById('verif_kode').textContent = this.dataset.kode;
                document.getElementById('verif_tamu').textContent = this.dataset.nama + ' (' + this.dataset.instansi + ')';
                document.getElementById('verif_tujuan').textContent = 'Tujuan: ' + this.dataset.tujuan;
                new bootstrap.Modal(document.getElementById('modalVerifikasi')).show();
            });
        });

        // Modal Tolak CS Trigger
        document.querySelectorAll('.btn-modal-tolak').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('tolak_id').value = this.dataset.id;
                document.getElementById('tolak_nama').textContent = this.dataset.nama + ' (' + this.dataset.kode + ')';
                new bootstrap.Modal(document.getElementById('modalTolak')).show();
            });
        });

        // Modal Selesai CS Trigger
        document.querySelectorAll('.btn-modal-selesai').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('selesai_id').value = this.dataset.id;
                document.getElementById('selesai_tamu').textContent = this.dataset.nama + ' - ' + this.dataset.instansi + ' (' + this.dataset.kode + ')';
                document.getElementById('selesai_ketemu').textContent = this.dataset.ketemu;
                new bootstrap.Modal(document.getElementById('modalSelesai')).show();
            });
        });

        // Cascading Dropdown Modal Walk-in
        const modalRuangan = document.getElementById('ruangan_id_modal');
        const modalPegawai = document.getElementById('pegawai_id_modal');
        if (modalRuangan && modalPegawai) {
            modalRuangan.addEventListener('change', function() {
                const rId = this.value;
                modalPegawai.innerHTML = '<option value=\"\">-- Memuat data staf... --</option>';
                modalPegawai.disabled = true;
                if (!rId) return;

                fetch(window.APP_BASE_URL + 'api/get_pegawai.php?ruangan_id=' + rId)
                    .then(r => r.json())
                    .then(data => {
                        let html = '<option value=\"\">-- Pilih Pejabat / Staf Tujuan --</option>';
                        data.forEach(p => {
                            html += `<option value=\"\${p.id}\">\${p.nama_pegawai} - \${p.jabatan}</option>`;
                        });
                        modalPegawai.innerHTML = html;
                        modalPegawai.disabled = false;
                    });
            });
        }
    });
</script>
";
require_once __DIR__ . '/../includes/footer.php';
?>
