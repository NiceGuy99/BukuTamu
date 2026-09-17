<?php
/**
 * Cetak Kartu Tamu / Visitor Pass Badge
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Kunjungan.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin atau CS
requireLogin(['admin', 'cs']);

$id = (int)($_GET['id'] ?? 0);
$kunjungan = Kunjungan::find($id);

if (!$kunjungan) {
    echo "Data tamu tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Tamu - <?= htmlspecialchars($kunjungan['kode_kunjungan']) ?></title>
    <!-- Favicon / Logo Tab Bar -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/img/logo.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .badge-card {
            width: 380px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 2px solid #0284c7;
            text-align: center;
        }

        .badge-header {
            background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
            color: white;
            padding: 16px 15px;
        }

        .badge-body {
            padding: 24px;
        }

        .badge-id {
            background: #e0f2fe;
            color: #0284c7;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 12px;
        }

        .badge-footer {
            background: #f8fafc;
            border-top: 1px dashed #cbd5e1;
            padding: 12px;
            font-size: 0.75rem;
            color: #64748b;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .badge-card {
                box-shadow: none;
                border: 2px solid #000;
                margin: auto;
            }
        }
    </style>
</head>
<body>

<div class="text-center">
    <div class="badge-card mb-3">
        <div class="badge-header d-flex align-items-center justify-content-center gap-3">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo <?= APP_ORG ?>" style="height: 46px; width: auto; background: white; padding: 3px; border-radius: 8px;">
            <div class="text-start">
                <div class="fw-bold small text-uppercase text-white-50" style="letter-spacing: 1px; font-size: 0.75rem;"><?= APP_ORG ?></div>
                <h6 class="fw-bold mb-0 text-white" style="font-size: 0.95rem;">VISITOR PASS / KARTU TAMU</h6>
            </div>
        </div>
        <div class="badge-body">
            <div class="badge-id"><?= htmlspecialchars($kunjungan['kode_kunjungan']) ?></div>
            <?php if (!empty($kunjungan['no_badge_kartu'])): ?>
                <div class="fs-4 fw-bold text-dark mb-2"><?= htmlspecialchars($kunjungan['no_badge_kartu']) ?></div>
            <?php endif; ?>
            
            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($kunjungan['nama_tamu']) ?></h4>
            <div class="text-primary fw-semibold mb-3"><?= htmlspecialchars($kunjungan['instansi']) ?></div>

            <div id="qrcode" class="d-inline-block p-2 bg-white border rounded-3 mb-3"></div>

            <div class="text-start bg-light p-3 rounded-3 small">
                <div class="mb-1"><span class="text-muted">Tujuan:</span> <strong><?= htmlspecialchars($kunjungan['nama_ruangan'] ?? '-') ?></strong></div>
                <div class="mb-1"><span class="text-muted">Pejabat:</span> <strong><?= htmlspecialchars($kunjungan['nama_pegawai'] ?? '-') ?></strong></div>
                <div><span class="text-muted">Tanggal:</span> <?= date('d/m/Y H:i', strtotime($kunjungan['jam_kedatangan'])) ?> WIB</div>
            </div>
        </div>
        <div class="badge-footer">
            Harap selalu mengenakan kartu tamu ini selama berada di area gedung & serahkan kembali ke Customer Service saat pulang.
        </div>
    </div>

    <div class="no-print d-flex gap-2 justify-content-center">
        <button onclick="window.print()" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-printer me-1"></i> Cetak Kartu Tamu
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary rounded-pill px-3">
            Tutup
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new QRCode(document.getElementById('qrcode'), {
            text: '<?= $kunjungan['kode_kunjungan'] ?>',
            width: 100,
            height: 100,
            colorDark : '#0f172a',
            colorLight : '#ffffff',
            correctLevel : QRCode.CorrectLevel.M
        });
    });
</script>

</body>
</html>
