<?php
/**
 * Display TV Monitor Antrian Ruang Tunggu (Lobby Queue Display)
 * Tampilan Fullscreen Elegan dengan Realtime Auto-Polling
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/helpers/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Monitor Antrian Tamu - <?= APP_ORG ?></title>
    
    <!-- Favicon / Logo Tab Bar -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/img/logo.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= time() ?>">
</head>
<body class="display-tv-body p-3 p-lg-4">

<div class="container-fluid h-100 d-flex flex-column justify-content-between">
    <!-- Header TV -->
    <header class="tv-card p-3 px-4 mb-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 rounded-4 bg-white shadow d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo <?= APP_ORG ?>" style="max-height: 48px; max-width: 48px; object-fit: contain;">
            </div>
            <div>
                <h3 class="fw-bold mb-0 text-white tracking-wide"><?= APP_ORG ?></h3>
                <div class="text-info small fw-semibold" style="letter-spacing: 1.5px;"><?= APP_SUBTITLE ?> &bull; INFORMASI ANTRIAN TAMU</div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <div class="text-end">
                <div class="text-white-50 small" id="tvDate"><?= formatTanggalIndo(date('Y-m-d')) ?></div>
                <div class="tv-clock" id="tvClock"><?= date('H:i:s') ?> WIB</div>
            </div>
            <button onclick="toggleFullscreen()" class="btn btn-outline-light btn-sm rounded-circle p-2" title="Fullscreen">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
        </div>
    </header>

    <!-- Ringkasan Counter Antrian -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="tv-card p-3 text-center border-start border-warning border-4">
                <div class="text-white-50 small fw-bold text-uppercase">1. Menunggu CS</div>
                <div class="fs-2 fw-bold text-warning" id="countMenungguCS">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tv-card p-3 text-center border-start border-info border-4">
                <div class="text-white-50 small fw-bold text-uppercase">2. Menunggu Ruangan</div>
                <div class="fs-2 fw-bold text-info" id="countMenungguRuangan">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tv-card p-3 text-center border-start border-success border-4">
                <div class="text-white-50 small fw-bold text-uppercase">3. Sedang Bertemu</div>
                <div class="fs-2 fw-bold text-success" id="countSedangBertemu">0</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tv-card p-3 text-center border-start border-secondary border-4">
                <div class="text-white-50 small fw-bold text-uppercase">4. Selesai Hari Ini</div>
                <div class="fs-2 fw-bold text-secondary" id="countSelesai">0</div>
            </div>
        </div>
    </div>

    <!-- Tabel Live Antrian -->
    <div class="tv-card flex-grow-1 p-4 mb-3 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                <span class="spinner-grow spinner-grow-sm text-danger" role="status"></span>
                <span>DAFTAR KUNJUNGAN AKTIF RUANG TUNGGU</span>
            </h5>
            <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3 py-2 small">
                <i class="bi bi-broadcast me-1 text-success"></i> Auto-Update Realtime
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-white-50 border-bottom border-secondary" style="font-size: 0.95rem;">
                        <th>KODE TIKET</th>
                        <th>NAMA TAMU / INSTANSI</th>
                        <th>RUANGAN & PEJABAT TUJUAN</th>
                        <th>JAM DATANG</th>
                        <th>STATUS KUNJUNGAN</th>
                        <th>KETERANGAN WAKTU</th>
                    </tr>
                </thead>
                <tbody id="queueTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data antrian...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Running Text Footer -->
    <footer class="tv-card p-2 px-4 d-flex align-items-center">
        <div class="badge bg-danger rounded-pill px-3 py-2 me-3 fw-bold text-uppercase">Info</div>
        <marquee class="text-white fw-semibold small" scrollamount="6">
            Selamat datang di <?= APP_ORG ?>. Demi kenyamanan dan ketertiban bersama, tamu dimohon untuk mengisi formulir buku tamu digital dan mengenakan kartu identitas tamu selama berada di lingkungan gedung. Terima kasih atas kerja sama Anda.
        </marquee>
    </footer>
</div>

<script src="<?= BASE_URL ?>assets/js/app.js?v=<?= time() ?>"></script>
<script>
    let lastDataCount = -1;

    function fetchAntrian() {
        fetch('<?= BASE_URL ?>api/get_antrian.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // Update Clock & Date
                    document.getElementById('tvClock').textContent = res.server_time + ' WIB';
                    document.getElementById('tvDate').textContent = res.server_date;

                    // Update Counters
                    const stat = res.statistik || {};
                    document.getElementById('countMenungguCS').textContent = stat.menunggu_cs || 0;
                    document.getElementById('countMenungguRuangan').textContent = stat.menunggu_ruangan || 0;
                    document.getElementById('countSedangBertemu').textContent = stat.sedang_bertemu || 0;
                    document.getElementById('countSelesai').textContent = stat.selesai || 0;

                    // Update Table
                    const tbody = document.getElementById('queueTableBody');
                    if (!res.data || res.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center py-5 text-white-50">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Tidak ada antrian tamu aktif saat ini.
                                </td>
                            </tr>
                        `;
                    } else {
                        let html = '';
                        res.data.forEach(item => {
                            let waktuKet = '-';
                            if (item.status === 'sedang_bertemu') {
                                waktuKet = `<span class="text-success fw-bold"><i class="bi bi-clock-fill me-1"></i> Ketemu: ${item.jam_ketemu}</span>`;
                            } else if (item.status === 'ditunda') {
                                waktuKet = `<span class="text-warning fw-bold"><i class="bi bi-clock-history me-1"></i> Ditunda s.d ${item.jam_tunda}</span>`;
                            } else if (item.status === 'menunggu_acc_ruangan') {
                                waktuKet = `<span class="text-info"><i class="bi bi-hourglass-split me-1"></i> Menunggu Respon</span>`;
                            } else {
                                waktuKet = `<span class="text-muted">Menunggu Verifikasi CS</span>`;
                            }

                            html += `
                                <tr class="border-bottom border-secondary border-opacity-25">
                                    <td>
                                        <div class="fw-bold text-info fs-5">${item.kode_kunjungan}</div>
                                        ${item.no_badge_kartu && item.no_badge_kartu !== '-' ? `<span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 small">${item.no_badge_kartu}</span>` : ''}
                                    </td>
                                    <td>
                                        <div class="fw-bold fs-6 text-white">${item.nama_tamu}</div>
                                        <div class="text-white-50 small"><i class="bi bi-building me-1"></i> ${item.instansi}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-light">${item.nama_ruangan}</div>
                                        <div class="text-white-50 small">${item.nama_pegawai}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary fs-6">${item.jam_kedatangan}</span>
                                    </td>
                                    <td>
                                        ${item.badge_html}
                                    </td>
                                    <td>
                                        ${waktuKet}
                                    </td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    }

                    // Play sound chime if new queue arrived
                    if (lastDataCount !== -1 && res.data.length > lastDataCount) {
                        playChime();
                    }
                    lastDataCount = res.data.length;
                }
            })
            .catch(err => console.error('Error fetching antrian:', err));
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchAntrian();
        setInterval(fetchAntrian, 4000); // Polling setiap 4 detik
    });
</script>

</body>
</html>
