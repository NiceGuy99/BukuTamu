<?php
/**
 * Halaman Monitoring Ketersediaan Pejabat / Pegawai untuk Customer Service (CS Desk)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../models/Ruangan.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin atau Customer Service
requireLogin(['admin', 'cs']);
$currentUser = currentUser();

// Ambil data ruangan dan seluruh pegawai
$listRuangan = Ruangan::getAll();
$listPegawai = Pegawai::getAll();

// Hitung statistik ketersediaan
$totalPegawai = count($listPegawai);
$adaDiTempat = count(array_filter($listPegawai, fn($p) => ($p['status_ketersediaan'] ?? '') === 'Ada di Tempat'));
$sedangRapat = count(array_filter($listPegawai, fn($p) => ($p['status_ketersediaan'] ?? '') === 'Sedang Rapat'));
$dinasLuar = count(array_filter($listPegawai, fn($p) => ($p['status_ketersediaan'] ?? '') === 'Dinas Luar'));
$tidakDiTempat = count(array_filter($listPegawai, fn($p) => ($p['status_ketersediaan'] ?? '') === 'Tidak di Tempat'));

$pageTitle = 'Ketersediaan Pejabat - Customer Service Desk';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-headset"></i> CS DESK
                </span>
                <span class="text-muted small">
                    <i class="bi bi-clock"></i> <span id="liveClock"><?= date('H:i:s') ?> WIB</span>
                </span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Status Ketersediaan Pejabat & Pegawai</h3>
            <p class="text-muted small mb-0">Pantau kehadiran pejabat di seluruh ruangan sebelum mengarahkan atau mendaftarkan tamu.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>cs/index.php" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Antrean CS
            </a>
            <button class="btn btn-outline-primary rounded-pill px-3" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Status
            </button>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Total Pejabat Terdata</div>
                        <div class="fs-3 fw-bold text-primary"><?= $totalPegawai ?> <span class="fs-6 fw-normal text-muted">Orang</span></div>
                    </div>
                    <div class="p-3 bg-primary-light text-primary rounded-circle">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Ada di Tempat (Siap)</div>
                        <div class="fs-3 fw-bold text-success"><?= $adaDiTempat ?> <span class="fs-6 fw-normal text-muted">Orang</span></div>
                    </div>
                    <div class="p-3 bg-success-subtle text-success rounded-circle">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Sedang Rapat</div>
                        <div class="fs-3 fw-bold text-warning"><?= $sedangRapat ?> <span class="fs-6 fw-normal text-muted">Orang</span></div>
                    </div>
                    <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                        <i class="bi bi-easel-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 border-start border-danger border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Dinas Luar / Berhalangan</div>
                        <div class="fs-3 fw-bold text-danger"><?= ($dinasLuar + $tidakDiTempat) ?> <span class="fs-6 fw-normal text-muted">Orang</span></div>
                    </div>
                    <div class="p-3 bg-danger-subtle text-danger rounded-circle">
                        <i class="bi bi-briefcase-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card-custom p-3 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari nama pejabat, NIP, jabatan, atau ruangan..." onkeyup="filterPegawai()">
                </div>
            </div>
            <div class="col-md-4">
                <select id="filterRuangan" class="form-select" onchange="filterPegawai()">
                    <option value="">-- Semua Ruangan / Unit Kerja --</option>
                    <?php foreach ($listRuangan as $r): ?>
                        <option value="<?= htmlspecialchars($r['nama_ruangan']) ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select" onchange="filterPegawai()">
                    <option value="">-- Semua Status Ketersediaan --</option>
                    <option value="Ada di Tempat">🟢 Ada di Tempat</option>
                    <option value="Sedang Rapat">🟡 Sedang Rapat</option>
                    <option value="Dinas Luar">🔴 Dinas Luar</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Daftar Pejabat Grid -->
    <div class="row g-3" id="pegawaiGrid">
        <?php foreach ($listPegawai as $p): 
            $status = $p['status_ketersediaan'] ?? 'Ada di Tempat';
            $badgeClass = 'bg-success';
            $borderClass = 'border-success';
            $statusIcon = 'bi-check-circle-fill';
            
            if ($status === 'Sedang Rapat') {
                $badgeClass = 'bg-warning text-dark';
                $borderClass = 'border-warning';
                $statusIcon = 'bi-easel-fill';
            } elseif ($status === 'Dinas Luar') {
                $badgeClass = 'bg-danger';
                $borderClass = 'border-info';
                $statusIcon = 'bi-car-front-fill';
            } elseif ($status === 'Tidak di Tempat') {
                $badgeClass = 'bg-danger';
                $borderClass = 'border-danger';
                $statusIcon = 'bi-x-circle-fill';
            }
        ?>
            <div class="col-md-6 col-xl-4 pegawai-item" 
                 data-nama="<?= strtolower(htmlspecialchars($p['nama_pegawai'] . ' ' . ($p['nip'] ?? '') . ' ' . $p['jabatan'])) ?>"
                 data-ruangan="<?= htmlspecialchars($p['nama_ruangan'] ?? '') ?>"
                 data-status="<?= htmlspecialchars($status) ?>">
                <div class="card-custom h-100 p-4 border-top <?= $borderClass ?> border-3 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold shadow-sm" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <?= strtoupper(substr(trim($p['nama_pegawai']), 0, 1)) ?>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($p['nama_pegawai']) ?></h6>
                                <span class="text-muted small"><?= htmlspecialchars($p['jabatan']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi <?= $statusIcon ?> me-1"></i> <?= htmlspecialchars($status) ?>
                        </span>
                    </div>

                    <div class="small text-muted mb-3 bg-light p-3 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-building text-primary"></i>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($p['nama_ruangan'] ?? 'Tanpa Ruangan') ?></span>
                        </div>
                        <?php if (!empty($p['lokasi_lantai'])): ?>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-geo-alt text-danger"></i>
                                <span><?= htmlspecialchars($p['lokasi_lantai']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($p['nip'])): ?>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-credit-card-2-front text-secondary"></i>
                                <span>NIP: <?= htmlspecialchars($p['nip']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 mt-auto pt-2 border-top">
                        <?php if (!empty($p['no_hp_wa'])): 
                            $cleanPhone = preg_replace('/[^0-9]/', '', $p['no_hp_wa']);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                        ?>
                            <a href="https://wa.me/<?= $cleanPhone ?>?text=Halo%20<?= urlencode($p['nama_pegawai']) ?>,%20ada%20tamu%20yang%20ingin%20bertemu%20di%20CS%20Front%20Desk." 
                               target="_blank" class="btn btn-sm btn-outline-success flex-fill rounded-pill">
                                <i class="bi bi-whatsapp me-1"></i> Hubungi WA
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-light flex-fill rounded-pill text-muted" disabled>
                                <i class="bi bi-telephone-x me-1"></i> Tanpa WA
                            </button>
                        <?php endif; ?>

                        <a href="<?= BASE_URL ?>cs/index.php" class="btn btn-sm btn-primary-custom flex-fill rounded-pill">
                            <i class="bi bi-pencil-square me-1"></i> Input Tamu
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Alert jika hasil pencarian kosong -->
    <div id="noResultsAlert" class="alert alert-warning text-center rounded-3 p-4 d-none mt-3">
        <i class="bi bi-search fs-3 text-warning mb-2 d-block"></i>
        <h6 class="fw-bold">Tidak ada data pejabat yang sesuai dengan filter!</h6>
        <p class="small text-muted mb-0">Coba ubah kata kunci pencarian, filter ruangan, atau status ketersediaan.</p>
    </div>
</div>

<script>
    // Live Clock
    setInterval(() => {
        const now = new Date();
        const str = now.toTimeString().split(' ')[0] + ' WIB';
        const el = document.getElementById('liveClock');
        if (el) el.textContent = str;
    }, 1000);

    // Filter Pegawai Dinamis
    function filterPegawai() {
        const keyword = document.getElementById('searchInput').value.toLowerCase();
        const selectedRuangan = document.getElementById('filterRuangan').value;
        const selectedStatus = document.getElementById('filterStatus').value;

        const items = document.querySelectorAll('.pegawai-item');
        let visibleCount = 0;

        items.forEach(item => {
            const nama = item.getAttribute('data-nama');
            const ruangan = item.getAttribute('data-ruangan');
            const status = item.getAttribute('data-status');

            const matchKeyword = nama.includes(keyword) || ruangan.toLowerCase().includes(keyword);
            const matchRuangan = !selectedRuangan || ruangan === selectedRuangan;
            const matchStatus = !selectedStatus || status === selectedStatus;

            if (matchKeyword && matchRuangan && matchStatus) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        const alertEl = document.getElementById('noResultsAlert');
        if (visibleCount === 0) {
            alertEl.classList.remove('d-none');
        } else {
            alertEl.classList.add('d-none');
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
