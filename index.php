<?php
/**
 * Halaman Utama: Formulir Pendaftaran Tamu Digital (Public / Kiosk)
 * Otomatis mencatat jam_kedatangan saat tombol simpan diklik.
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/models/Kunjungan.php';
require_once __DIR__ . '/models/Ruangan.php';
require_once __DIR__ . '/models/Pegawai.php';
require_once __DIR__ . '/helpers/functions.php';

// Ambil data Master Ruangan untuk dropdown
$listRuangan = Ruangan::getAll();

// Proses POST Form Tamu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaTamu = clean($_POST['nama_tamu'] ?? '');
    $instansi = clean($_POST['instansi'] ?? '');
    $noHp = clean($_POST['no_hp'] ?? '');
    $noIdentitas = clean($_POST['no_identitas'] ?? '');
    $jumlahOrang = (int)($_POST['jumlah_orang'] ?? 1);
    $ruanganId = clean($_POST['ruangan_id'] ?? '');
    $pegawaiId = clean($_POST['pegawai_id'] ?? '');
    $keperluan = clean($_POST['keperluan'] ?? '');
    $statusJanji = clean($_POST['status_janji'] ?? 'belum_janji');
    $jamJanji = clean($_POST['jam_janji'] ?? '');

    // Validasi
    $errors = [];
    if (empty($namaTamu)) $errors[] = 'Nama Lengkap Tamu wajib diisi.';
    if (empty($instansi)) $errors[] = 'Nama Instansi / Perusahaan wajib diisi.';
    if (empty($noHp)) $errors[] = 'Nomor WhatsApp / HP wajib diisi.';
    if (empty($ruanganId)) $errors[] = 'Ruangan / Seksi tujuan wajib dipilih.';
    if (empty($keperluan)) $errors[] = 'Keperluan kunjungan wajib diisi.';

    if (empty($errors)) {
        // Simpan data -> jam_kedatangan otomatis tercatat di model
        $baru = Kunjungan::create([
            'nama_tamu' => $namaTamu,
            'instansi' => $instansi,
            'no_hp' => $noHp,
            'no_identitas' => $noIdentitas,
            'jumlah_orang' => $jumlahOrang,
            'ruangan_id' => $ruanganId,
            'pegawai_id' => $pegawaiId,
            'keperluan' => $keperluan,
            'status_janji' => $statusJanji,
            'jam_janji' => ($statusJanji === 'sudah_janji' ? $jamJanji : null)
        ]);

        if ($baru) {
            setFlash('success', "Pendaftaran Kunjungan berhasil! Nomor Tiket Anda: {$baru['kode_kunjungan']}. Jam kedatangan otomatis tercatat.");
            header('Location: ' . BASE_URL . 'tiket.php?kode=' . urlencode($baru['kode_kunjungan']));
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data kunjungan. Silakan coba kembali.';
        }
    }
}

$pageTitle = 'Formulir Kunjungan Tamu';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <!-- Hero Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-custom border-0 p-4" style="background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%); color: white; border-radius: 20px;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="bg-white p-2 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 52px; height: 52px; flex-shrink: 0;">
                                <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo <?= APP_ORG ?>" style="max-height: 42px; max-width: 42px; object-fit: contain;">
                            </div>
                            <div>
                                <div class="d-inline-flex align-items-center gap-2 px-3 py-0.5 rounded-pill bg-white bg-opacity-20 small fw-bold mb-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-shield-check"></i> Layanan Registrasi Pengunjung Digital
                                </div>
                                <h2 class="fw-bold mb-0 fs-3">Selamat Datang di <?= APP_ORG ?></h2>
                            </div>
                        </div>
                        <p class="mb-0 text-white-50 fs-6 mt-2">Silakan isi formulir kunjungan di bawah ini. Jam kedatangan Anda akan dicatat secara otomatis oleh sistem saat formulir disimpan.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="bg-white bg-opacity-10 p-3 rounded-4 d-inline-block text-center text-md-end">
                            <div class="small text-white-50"><i class="bi bi-calendar-event me-1"></i> <?= formatTanggalIndo(date('Y-m-d')) ?></div>
                            <div class="fs-4 fw-bold" id="liveClock"><?= date('H:i:s') ?> WIB</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Mohon lengkapi formulir dengan benar:</div>
            <ul class="mb-0 small ps-3">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Form Utama (Kiri) -->
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-header-gradient d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill"></i> Formulir Pendaftaran Tamu
                    </h5>
                    <span class="badge bg-white text-primary fw-bold rounded-pill px-3 py-2">
                        <i class="bi bi-clock-fill me-1"></i> Jam Kedatangan Auto-Record
                    </span>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASE_URL ?>index.php" method="POST" id="formTamu">
                        <!-- 1. Identitas Tamu -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-person-circle me-1"></i> 1. Identitas Tamu / Pengunjung
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label-custom">Nama Lengkap Tamu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" name="nama_tamu" class="form-control form-control-custom" placeholder="Contoh: A (Budi Pratama)" value="<?= htmlspecialchars($_POST['nama_tamu'] ?? '') ?>" required autofocus>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Instansi / Perusahaan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                    <input type="text" name="instansi" class="form-control form-control-custom" placeholder="Contoh: PT. Batara" value="<?= htmlspecialchars($_POST['instansi'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="tel" name="no_hp" class="form-control form-control-custom" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">No. Identitas (KTP / SIM)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-card-text"></i></span>
                                    <input type="text" name="no_identitas" class="form-control form-control-custom" placeholder="Nomor KTP / SIM" value="<?= htmlspecialchars($_POST['no_identitas'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-custom">Jumlah Tamu</label>
                                <input type="number" name="jumlah_orang" class="form-control form-control-custom" min="1" max="50" value="<?= htmlspecialchars($_POST['jumlah_orang'] ?? '1') ?>">
                            </div>
                        </div>

                        <!-- 2. Tujuan & Pejabat yang Ditemui -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-geo-alt-fill me-1"></i> 2. Ruangan & Pejabat Tujuan
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label-custom">Ruangan / Seksi / Bagian Tujuan <span class="text-danger">*</span></label>
                                <select name="ruangan_id" id="ruangan_id" class="form-select form-select-custom" required>
                                    <option value="">-- Pilih Ruangan / Seksi Tujuan --</option>
                                    <?php foreach ($listRuangan as $r): ?>
                                        <option value="<?= $r['id'] ?>" <?= (isset($_POST['ruangan_id']) && $_POST['ruangan_id'] == $r['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($r['nama_ruangan']) ?> (<?= htmlspecialchars($r['lokasi_lantai'] ?? 'Gedung Utama') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Pejabat / Pegawai yang Dituju</label>
                                <select name="pegawai_id" id="pegawai_id" class="form-select form-select-custom" disabled>
                                    <option value="">-- Pilih Ruangan Terlebih Dahulu --</option>
                                </select>
                                <small class="text-muted" style="font-size: 0.78rem;">Pilih ruangan dahulu untuk memuat daftar pejabat/staf di ruangan tersebut.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Keperluan / Perihal Kunjungan <span class="text-danger">*</span></label>
                                <textarea name="keperluan" class="form-control form-control-custom" rows="3" placeholder="Jelaskan maksud dan tujuan kunjungan secara ringkas dan jelas..." required><?= htmlspecialchars($_POST['keperluan'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- 3. Status Perjanjian Kunjungan -->
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-calendar-check-fill me-1"></i> 3. Status Janji Temu
                        </h6>
                        <div class="row g-3 mb-4 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label-custom d-block">Apakah sudah memiliki janji sebelumnya?</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="status_janji" id="janji_sudah" value="sudah_janji" autocomplete="off" <?= (isset($_POST['status_janji']) && $_POST['status_janji'] === 'sudah_janji') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary py-2 fw-semibold" for="janji_sudah">
                                        <i class="bi bi-check2-circle me-1"></i> Sudah Punya Janji
                                    </label>

                                    <input type="radio" class="btn-check" name="status_janji" id="janji_belum" value="belum_janji" autocomplete="off" <?= (!isset($_POST['status_janji']) || $_POST['status_janji'] === 'belum_janji') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-secondary py-2 fw-semibold" for="janji_belum">
                                        <i class="bi bi-dash-circle me-1"></i> Belum Ada Janji
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 <?= (isset($_POST['status_janji']) && $_POST['status_janji'] === 'sudah_janji') ? '' : 'd-none' ?>" id="jamJanjiContainer">
                                <label class="form-label-custom">Jam Janji Temu</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-alarm"></i></span>
                                    <input type="time" name="jam_janji" class="form-control form-control-custom" value="<?= htmlspecialchars($_POST['jam_janji'] ?? date('H:i')) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i> Data Anda akan diteruskan ke Customer Service untuk verifikasi.
                            </div>
                            <button type="submit" class="btn btn-primary-custom px-4 py-2 fs-6">
                                <i class="bi bi-send-check-fill fs-5"></i> Simpan Pendaftaran Tamu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Informasi & Alur (Kanan) -->
        <div class="col-lg-4">
            <!-- Card Alur Kunjungan -->
            <div class="card-custom mb-4">
                <div class="p-3 bg-light border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-diagram-3-fill text-primary"></i> Alur Proses Kunjungan
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="tracker-container">
                        <!-- Step 1 -->
                        <div class="step-item active">
                            <div class="step-icon"><i class="bi bi-1-circle-fill"></i></div>
                            <div class="fw-bold text-primary">Isi Form Tamu</div>
                            <div class="text-muted small">Tamu mengisi form, sistem otomatis mencatat <strong>Jam Kedatangan</strong>.</div>
                        </div>
                        <!-- Step 2 -->
                        <div class="step-item">
                            <div class="step-icon"><i class="bi bi-2-circle"></i></div>
                            <div class="fw-bold">Verifikasi CS</div>
                            <div class="text-muted small">Customer Service memeriksa identitas & meneruskan ke ruangan.</div>
                        </div>
                        <!-- Step 3 -->
                        <div class="step-item">
                            <div class="step-icon"><i class="bi bi-3-circle"></i></div>
                            <div class="fw-bold">Respon Ruangan / Pejabat</div>
                            <div class="text-muted small">Pejabat menerima (catat <strong>Jam Ketemu</strong>), menunda, atau menolak.</div>
                        </div>
                        <!-- Step 4 -->
                        <div class="step-item">
                            <div class="step-icon"><i class="bi bi-4-circle"></i></div>
                            <div class="fw-bold">Lapor CS & Selesai</div>
                            <div class="text-muted small">Tamu kembali ke CS, CS menyelesaikan & mencatat <strong>Jam Selesai</strong>.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Bantuan & Lacak -->
            <div class="card-custom bg-white p-3 text-center">
                <div class="text-primary fs-3 mb-2"><i class="bi bi-search"></i></div>
                <h6 class="fw-bold mb-1">Sudah Pernah Mendaftar?</h6>
                <p class="text-muted small mb-3">Lacak status kunjungan Anda dengan memasukkan nomor tiket atau nomor WhatsApp.</p>
                <a href="<?= BASE_URL ?>lacak.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-arrow-right-circle me-1"></i> Lacak Status Tiket
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
