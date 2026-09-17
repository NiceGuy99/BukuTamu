<?php
/**
 * Halaman Lacak Status Kunjungan Tamu
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/models/Kunjungan.php';
require_once __DIR__ . '/helpers/functions.php';

$query = clean($_GET['q'] ?? '');
$hasil = [];

if (!empty($query)) {
    $db = Database::getConnection();
    $stmt = $db->prepare(Kunjungan::getBaseQuery() . " 
        WHERE k.kode_kunjungan = ? OR k.no_hp LIKE ? OR k.nama_tamu LIKE ?
        ORDER BY k.id DESC LIMIT 10
    ");
    $stmt->execute([$query, "%$query%", "%$query%"]);
    $hasil = $stmt->fetchAll();
}

$pageTitle = 'Lacak Kunjungan Tamu';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-2">
                    <i class="bi bi-search fs-2"></i>
                </div>
                <h3 class="fw-bold">Lacak Status Kunjungan</h3>
                <p class="text-muted">Masukkan Nomor Tiket Kunjungan (contoh: <code>TM-<?= date('Ymd') ?>-0001</code>) atau Nomor WhatsApp Tamu</p>
            </div>

            <!-- Form Pencarian -->
            <div class="card-custom p-4 mb-4">
                <form action="<?= BASE_URL ?>lacak.php" method="GET">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-ticket-detailed text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Ketik Kode Tiket / No. HP / Nama Tamu..." value="<?= htmlspecialchars($query) ?>" required autofocus>
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                    </div>
                </form>
            </div>

            <!-- Hasil Pencarian -->
            <?php if (!empty($query)): ?>
                <h5 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                    <span>Hasil Pencarian: "<?= htmlspecialchars($query) ?>"</span>
                    <span class="badge bg-secondary rounded-pill"><?= count($hasil) ?> Ditemukan</span>
                </h5>

                <?php if (empty($hasil)): ?>
                    <div class="card-custom p-5 text-center text-muted">
                        <i class="bi bi-emoji-frown fs-1 text-secondary mb-2"></i>
                        <h6 class="fw-bold">Tidak ada data kunjungan yang sesuai</h6>
                        <p class="small mb-3">Pastikan nomor tiket atau nomor WhatsApp yang Anda masukkan sudah benar.</p>
                        <a href="<?= BASE_URL ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">Isi Formulir Baru</a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($hasil as $row): ?>
                            <div class="col-12">
                                <div class="card-custom p-3 d-flex flex-row align-items-center justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-bold text-primary fs-6"><?= htmlspecialchars($row['kode_kunjungan']) ?></span>
                                            <?= renderStatusBadge($row['status']) ?>
                                        </div>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_tamu']) ?> &bull; <span class="text-muted"><?= htmlspecialchars($row['instansi']) ?></span></div>
                                        <div class="text-muted small">
                                            <i class="bi bi-building me-1"></i> <?= htmlspecialchars($row['nama_ruangan'] ?? 'Umum') ?> (<?= htmlspecialchars($row['nama_pegawai'] ?? '-') ?>) &bull;
                                            <i class="bi bi-clock me-1"></i> Datang: <?= formatJamIndo($row['jam_kedatangan']) ?>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL ?>tiket.php?kode=<?= urlencode($row['kode_kunjungan']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Lihat Tiket <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
