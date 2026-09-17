<?php
/**
 * Portal ACC Ruangan / Pejabat Tujuan
 * Alur Utama:
 * 1. Menerima Tamu -> Otomatis mencatat jam_ketemu (Jam saat klik terima)
 * 2. Menunda Tamu -> Pejabat tentukan jam tunda (jam_ketemu otomatis disesuaikan jam yang diset)
 * 3. Menolak Tamu -> Pejabat masukkan alasan penolakan
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Kunjungan.php';
require_once __DIR__ . '/../models/Ruangan.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin atau Ruangan
requireLogin(['admin', 'ruangan']);
$currentUser = currentUser();
$freshUser = User::find($currentUser['id']);

// Ambil Master Ruangan
$listRuangan = Ruangan::getAll();

// Jika role ruangan, kunci filter ke ruangan yang ditugaskan ke user
if ($currentUser['role'] === 'ruangan' && !empty($currentUser['ruangan_id'])) {
    $selectedRuanganId = (int)$currentUser['ruangan_id'];
} else {
    $selectedRuanganId = isset($_GET['ruangan_id']) ? (int)$_GET['ruangan_id'] : ($listRuangan[0]['id'] ?? 0);
}
$selectedRuangan = Ruangan::find($selectedRuanganId);

// Ambil data kunjungan untuk ruangan ini
$kunjunganRuangan = Kunjungan::getHariIni($selectedRuanganId ?: null);

// Pisahkan kategori
$perluRespon = array_filter($kunjunganRuangan, fn($k) => $k['status'] === 'menunggu_acc_ruangan' || $k['status'] === 'ditunda');
$sedangBertemu = array_filter($kunjunganRuangan, fn($k) => $k['status'] === 'sedang_bertemu');
$riwayatRuangan = array_filter($kunjunganRuangan, fn($k) => $k['status'] === 'selesai' || strpos($k['status'], 'ditolak') !== false);

$currentAvail = $freshUser['status_ketersediaan'] ?? 'Ada di Tempat';

$pageTitle = 'Portal ACC Ruangan / Pejabat';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <!-- Header Ruangan & Status Ketersediaan Pegawai -->
    <div class="row g-3 align-items-center justify-content-between mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold"><i class="bi bi-building-check"></i> PORTAL ACC RUANGAN & PEJABAT</span>
                <span class="text-muted small"><i class="bi bi-clock"></i> <span id="liveClock"><?= date('H:i:s') ?> WIB</span></span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Respon Kunjungan Tamu</h3>
        </div>

        <!-- Widget Pengubah Status Ketersediaan Pejabat -->
        <div class="col-md-6 text-md-end">
            <div class="card-custom d-inline-block p-2 px-3 bg-white border shadow-sm text-start">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Status Ketersediaan Anda:</div>
                        <div class="fw-bold fs-6" id="badgeAvailContainer">
                            <?php if ($currentAvail === 'Ada di Tempat'): ?>
                                <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Ada di Tempat</span>
                            <?php elseif ($currentAvail === 'Sedang Rapat'): ?>
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i> Sedang Rapat</span>
                            <?php elseif ($currentAvail === 'Dinas Luar'): ?>
                                <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-airplane-fill me-1"></i> Dinas Luar</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="bi bi-x-circle-fill me-1"></i> <?= htmlspecialchars($currentAvail) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm rounded-pill dropdown-toggle fw-semibold px-3" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-arrow-repeat me-1"></i> Ubah Status
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2">
                            <li><button class="dropdown-item rounded-2 py-2 text-success fw-semibold" onclick="ubahStatusKetersediaan('Ada di Tempat')"><i class="bi bi-check-circle-fill me-2"></i> 🟢 Ada di Tempat</button></li>
                            <li><button class="dropdown-item rounded-2 py-2 text-warning fw-semibold" onclick="ubahStatusKetersediaan('Sedang Rapat')"><i class="bi bi-clock-history me-2"></i> 🟡 Sedang Rapat</button></li>
                            <li><button class="dropdown-item rounded-2 py-2 text-danger fw-semibold" onclick="ubahStatusKetersediaan('Dinas Luar')"><i class="bi bi-airplane-fill me-2"></i> 🔴 Dinas Luar</button></li>
                            <li><button class="dropdown-item rounded-2 py-2 text-secondary fw-semibold" onclick="ubahStatusKetersediaan('Tidak di Tempat')"><i class="bi bi-x-circle-fill me-2"></i> ⚪ Tidak di Tempat</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profil Pejabat Terhubung & Ruangan -->
    <div class="card-custom mb-4 p-3 bg-light border-0">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-white text-primary rounded-circle shadow-sm">
                    <i class="bi bi-person-badge fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($freshUser['nama_pegawai'] ?: $freshUser['nama_lengkap']) ?></h5>
                        <?php if (!empty($freshUser['nip'])): ?>
                            <span class="badge bg-white text-muted border small">NIP: <?= htmlspecialchars($freshUser['nip']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-briefcase me-1"></i> Jabatan: <strong><?= htmlspecialchars($freshUser['jabatan'] ?? 'Pejabat Ruangan') ?></strong> &bull;
                        <i class="bi bi-door-open me-1"></i> Ruangan: <strong><?= htmlspecialchars($selectedRuangan['nama_ruangan'] ?? 'Seksi Penunjang') ?></strong> (<?= htmlspecialchars($selectedRuangan['lokasi_lantai'] ?? 'Gedung Utama') ?>)
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <form action="<?= BASE_URL ?>ruangan/index.php" method="GET" class="d-flex align-items-center gap-2">
                        <select name="ruangan_id" class="form-select form-select-sm form-select-custom shadow-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Ruangan --</option>
                            <?php foreach ($listRuangan as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $selectedRuanganId == $r['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['nama_ruangan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>

                <div class="d-flex gap-2 text-center">
                    <div class="px-3 py-1 bg-white rounded-3 shadow-sm border">
                        <div class="small text-muted" style="font-size: 0.72rem;">Menunggu ACC</div>
                        <div class="fw-bold text-warning"><?= count($perluRespon) ?> Tamu</div>
                    </div>
                    <div class="px-3 py-1 bg-white rounded-3 shadow-sm border">
                        <div class="small text-muted" style="font-size: 0.72rem;">Sedang Bertemu</div>
                        <div class="fw-bold text-success"><?= count($sedangBertemu) ?> Tamu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Nav Tabs Ruangan -->
    <div class="card-custom">
        <div class="card-header bg-white border-bottom p-3">
            <ul class="nav nav-pills gap-2" id="ruanganTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold position-relative px-4" id="tab-respon" data-bs-toggle="pill" data-bs-target="#content-respon" type="button">
                        <i class="bi bi-bell-fill me-1 text-warning"></i> Tamu Menunggu Respon
                        <?php if (count($perluRespon) > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-1"><?= count($perluRespon) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold px-4" id="tab-bertemu" data-bs-toggle="pill" data-bs-target="#content-bertemu" type="button">
                        <i class="bi bi-person-check-fill me-1 text-success"></i> Sedang Bertemu di Ruangan (<?= count($sedangBertemu) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold px-4" id="tab-riwayat-ruangan" data-bs-toggle="pill" data-bs-target="#content-riwayat-ruangan" type="button">
                        <i class="bi bi-journal-text me-1 text-secondary"></i> Riwayat Selesai (<?= count($riwayatRuangan) ?>)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="ruanganTabContent">
                <!-- TAB 1: TAMU MENUNGGU RESPON (ACC / TERIMA / TUNDA / TOLAK) -->
                <div class="tab-pane fade show active p-3" id="content-respon" role="tabpanel">
                    <?php if (empty($perluRespon)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-emoji-smile fs-1 text-success mb-2"></i>
                            <h6 class="fw-bold">Tidak ada tamu yang menunggu respon Anda saat ini</h6>
                            <p class="small mb-0">Semua tamu telah diproses atau belum ada tamu baru yang diverifikasi CS.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($perluRespon as $row): ?>
                                <div class="col-lg-6">
                                    <div class="card-custom p-4 border <?= $row['status'] === 'ditunda' ? 'border-warning bg-warning bg-opacity-10' : 'border-primary' ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <span class="badge bg-primary rounded-pill px-3 py-1"><?= htmlspecialchars($row['kode_kunjungan']) ?></span>
                                                <?= renderStatusBadge($row['status']) ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-clock"></i> Datang: <strong><?= formatJamIndo($row['jam_kedatangan']) ?></strong>
                                            </div>
                                        </div>

                                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($row['nama_tamu']) ?> (<?= $row['jumlah_orang'] ?> Orang)</h5>
                                        <div class="text-primary fw-semibold mb-2">
                                            <i class="bi bi-building me-1"></i> <?= htmlspecialchars($row['instansi']) ?>
                                        </div>

                                        <div class="p-3 bg-white rounded-3 border mb-3 small">
                                            <div class="mb-1"><span class="text-muted">Ditujukan Kepada:</span> <strong><?= htmlspecialchars($row['nama_pegawai'] ?? 'Umum Ruangan') ?> (<?= htmlspecialchars($row['jabatan'] ?? '') ?>)</strong></div>
                                            <div class="mb-1"><span class="text-muted">Status Janji:</span> <?= $row['status_janji'] === 'sudah_janji' ? '<span class="badge bg-success">Sudah Janji (' . htmlspecialchars($row['jam_janji']) . ')</span>' : '<span class="badge bg-secondary">Belum Ada Janji</span>' ?></div>
                                            <div><span class="text-muted">Keperluan:</span> <?= nl2br(htmlspecialchars($row['keperluan'])) ?></div>
                                            
                                            <?php if ($row['status'] === 'ditunda'): ?>
                                                <div class="mt-2 pt-2 border-top text-danger">
                                                    <i class="bi bi-clock-history me-1"></i> <strong>Ditunda s.d Jam: <?= formatJamIndo($row['jam_tunda']) ?></strong>
                                                    <?php if (!empty($row['alasan_tolak_tunda'])): ?>
                                                        <br><span class="text-muted">Alasan: <?= htmlspecialchars($row['alasan_tolak_tunda']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- 3 PILIHAN AKSI PEJABAT: TERIMA (ACC), MENUNDA, MENOLAK -->
                                        <div class="d-flex flex-wrap gap-2 justify-content-between pt-2 border-top">
                                            <!-- 1. Menerima (ACC) -->
                                            <button class="btn btn-success-custom rounded-pill px-3 fw-semibold btn-terima-modal"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama_tamu']) ?>"
                                                    data-instansi="<?= htmlspecialchars($row['instansi']) ?>">
                                                <i class="bi bi-check-circle-fill me-1"></i> 1. Terima Kunjungan
                                            </button>

                                            <!-- 2. Menunda -->
                                            <button class="btn btn-warning text-dark rounded-pill px-3 fw-semibold btn-tunda-modal"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama_tamu']) ?>"
                                                    data-jam="<?= date('H:i', strtotime('+30 minutes')) ?>">
                                                <i class="bi bi-clock-history me-1"></i> 2. Tunda Jam
                                            </button>

                                            <!-- 3. Menolak -->
                                            <button class="btn btn-outline-danger rounded-pill px-3 fw-semibold btn-tolak-modal"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama_tamu']) ?>">
                                                <i class="bi bi-x-circle me-1"></i> 3. Tolak
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 2: SEDANG BERTEMU -->
                <div class="tab-pane fade p-3" id="content-bertemu" role="tabpanel">
                    <?php if (empty($sedangBertemu)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-person-x fs-1 text-secondary mb-2"></i>
                            <h6 class="fw-bold">Tidak ada tamu yang sedang bertemu di ruangan saat ini</h6>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Tiket</th>
                                        <th>Nama Tamu & Instansi</th>
                                        <th>Pejabat yang Ditemui</th>
                                        <th>Waktu Mulai Bertemu (Jam Ketemu)</th>
                                        <th>Durasi Berjalan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sedangBertemu as $row): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($row['kode_kunjungan']) ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_tamu']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($row['instansi']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['nama_pegawai'] ?? 'Umum Ruangan') ?></td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success fs-6 border border-success">
                                                    <i class="bi bi-clock me-1"></i> <?= formatJamIndo($row['jam_ketemu']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted fw-semibold">
                                                    <?= hitungDurasiTeks($row['jam_ketemu'], date('Y-m-d H:i:s')) ?>
                                                </span>
                                            </td>
                                            <td><?= renderStatusBadge($row['status']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 3: RIWAYAT SELESAI RUANGAN -->
                <div class="tab-pane fade p-3" id="content-riwayat-ruangan" role="tabpanel">
                    <?php if (empty($riwayatRuangan)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-1 text-secondary mb-2"></i>
                            <h6 class="fw-bold">Belum ada riwayat kunjungan selesai untuk ruangan ini hari ini</h6>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kode Tiket</th>
                                        <th>Nama Tamu & Instansi</th>
                                        <th>Jam Ketemu</th>
                                        <th>Jam Selesai</th>
                                        <th>Durasi Total</th>
                                        <th>Status Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayatRuangan as $row): ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($row['kode_kunjungan']) ?></td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_tamu']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($row['instansi']) ?></small>
                                            </td>
                                            <td><?= $row['jam_ketemu'] ? formatJamIndo($row['jam_ketemu']) : '-' ?></td>
                                            <td><?= $row['jam_selesai_bertemu'] ? formatJamIndo($row['jam_selesai_bertemu']) : '-' ?></td>
                                            <td><?= $row['durasi_menit'] ? $row['durasi_menit'] . ' Menit' : '-' ?></td>
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
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TERIMA KUNJUNGAN (ACC) -->
<!-- ========================================== -->
<div class="modal fade" id="modalTerima" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>api/aksi_kunjungan.php" method="POST">
                <input type="hidden" name="id" id="terima_id">
                <input type="hidden" name="aksi" value="terima_ruangan">
                <input type="hidden" name="redirect_to" value="<?= BASE_URL ?>ruangan/index.php?ruangan_id=<?= $selectedRuanganId ?>">

                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Terima Kunjungan Tamu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success border-0 rounded-3 mb-3 small">
                        <i class="bi bi-info-circle-fill me-1"></i> Saat Anda klik tombol <strong>Terima</strong>, sistem akan <strong>otomatis mencatat Jam Ketemu</strong> pada detik ini juga!
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="small text-muted">Tamu:</div>
                        <div class="fw-bold fs-5 text-dark" id="terima_tamu">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Catatan / Arahan Ruangan Pertemuan (Opsional)</label>
                        <input type="text" name="catatan_ruangan" class="form-control form-control-custom" placeholder="Contoh: Silakan langsung masuk ke Ruang Rapat Lt. 2">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success-custom rounded-pill px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Terima Tamu Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TUNDA KUNJUNGAN (POSTPONE) -->
<!-- ========================================== -->
<div class="modal fade" id="modalTunda" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>api/aksi_kunjungan.php" method="POST">
                <input type="hidden" name="id" id="tunda_id">
                <input type="hidden" name="aksi" value="tunda_ruangan">
                <input type="hidden" name="redirect_to" value="<?= BASE_URL ?>ruangan/index.php?ruangan_id=<?= $selectedRuanganId ?>">

                <div class="modal-header bg-warning text-dark rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2"></i> Tunda Waktu Kunjungan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 rounded-3 mb-3 small">
                        <i class="bi bi-info-circle-fill me-1"></i> Tentukan jam penundaan. <strong>Jam Ketemu akan otomatis disesuaikan</strong> dengan jam yang diset ini.
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="small text-muted">Tamu:</div>
                        <div class="fw-bold fs-5 text-dark" id="tunda_tamu">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Ditunda Sampai Jam Berapa? <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-alarm"></i></span>
                            <input type="time" name="jam_tunda" id="tunda_jam_input" class="form-control form-control-custom" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Alasan Penundaan <span class="text-danger">*</span></label>
                        <textarea name="alasan_tunda" class="form-control form-control-custom" rows="2" placeholder="Contoh: Menyelesaikan rapat darurat terlebih dahulu..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">
                        <i class="bi bi-clock-fill me-1"></i> Simpan Penundaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TOLAK KUNJUNGAN RUANGAN -->
<!-- ========================================== -->
<div class="modal fade" id="modalTolakRuangan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>api/aksi_kunjungan.php" method="POST">
                <input type="hidden" name="id" id="tolak_r_id">
                <input type="hidden" name="aksi" value="tolak_ruangan">
                <input type="hidden" name="redirect_to" value="<?= BASE_URL ?>ruangan/index.php?ruangan_id=<?= $selectedRuanganId ?>">

                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-x-octagon-fill me-2"></i> Tolak Kunjungan Tamu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted">Apakah Anda yakin ingin menolak kunjungan tamu <strong id="tolak_r_tamu">-</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label-custom">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_tolak" class="form-control form-control-custom" rows="3" placeholder="Contoh: Pejabat sedang dinas luar kota / rapat tertutup..." required></textarea>
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

<?php
$extraScript = "
<script>
    function ubahStatusKetersediaan(statusBaru) {
        const formData = new FormData();
        formData.append('status_ketersediaan', statusBaru);

        fetch(window.APP_BASE_URL + 'api/update_ketersediaan.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Status Diperbarui!',
                    html: res.message,
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message || 'Gagal mengubah status', 'error');
            }
        })
        .catch(err => {
            console.error('Error updating availability:', err);
            Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Modal Terima Ruangan Trigger
        document.querySelectorAll('.btn-terima-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('terima_id').value = this.dataset.id;
                document.getElementById('terima_tamu').textContent = this.dataset.nama + ' (' + this.dataset.instansi + ')';
                new bootstrap.Modal(document.getElementById('modalTerima')).show();
            });
        });

        // Modal Tunda Ruangan Trigger
        document.querySelectorAll('.btn-tunda-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('tunda_id').value = this.dataset.id;
                document.getElementById('tunda_tamu').textContent = this.dataset.nama;
                document.getElementById('tunda_jam_input').value = this.dataset.jam;
                new bootstrap.Modal(document.getElementById('modalTunda')).show();
            });
        });

        // Modal Tolak Ruangan Trigger
        document.querySelectorAll('.btn-tolak-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('tolak_r_id').value = this.dataset.id;
                document.getElementById('tolak_r_tamu').textContent = this.dataset.nama;
                new bootstrap.Modal(document.getElementById('modalTolakRuangan')).show();
            });
        });
    });
</script>
";
require_once __DIR__ . '/../includes/footer.php';
?>
