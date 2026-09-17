<?php
/**
 * Master Data Ruangan / Seksi
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Ruangan.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin
requireLogin(['admin']);

// Handle POST Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = clean($_POST['aksi'] ?? '');
    
    if ($aksi === 'tambah') {
        $kode = clean($_POST['kode_ruangan'] ?? '');
        $nama = clean($_POST['nama_ruangan'] ?? '');
        $lokasi = clean($_POST['lokasi_lantai'] ?? '');
        $pj = clean($_POST['penanggung_jawab'] ?? '');
        
        if (!empty($kode) && !empty($nama)) {
            $newId = Ruangan::create([
                'kode_ruangan' => $kode,
                'nama_ruangan' => $nama,
                'lokasi_lantai' => $lokasi,
                'penanggung_jawab' => $pj,
                'is_active' => 1
            ]);
            if ($newId) {
                Pegawai::syncFromRuangan();
            }
            setFlash('success', 'Ruangan baru berhasil ditambahkan dan data pejabat disinkronkan!');
        } else {
            setFlash('error', 'Kode dan Nama Ruangan wajib diisi.');
        }
    } elseif ($aksi === 'edit') {
        $id = (int)$_POST['id'];
        $kode = clean($_POST['kode_ruangan'] ?? '');
        $nama = clean($_POST['nama_ruangan'] ?? '');
        $lokasi = clean($_POST['lokasi_lantai'] ?? '');
        $pj = clean($_POST['penanggung_jawab'] ?? '');
        
        Ruangan::update($id, [
            'kode_ruangan' => $kode,
            'nama_ruangan' => $nama,
            'lokasi_lantai' => $lokasi,
            'penanggung_jawab' => $pj,
            'is_active' => (int)($_POST['is_active'] ?? 1)
        ]);
        Pegawai::syncFromRuangan();
        setFlash('success', 'Data ruangan dan data pejabat berhasil diperbarui!');
    } elseif ($aksi === 'hapus') {
        $id = (int)$_POST['id'];
        Ruangan::delete($id);
        setFlash('success', 'Ruangan berhasil dihapus.');
    }
    
    header('Location: ' . BASE_URL . 'admin/master_ruangan.php');
    exit;
}

$ruanganList = Ruangan::getAll(false);

$pageTitle = 'Master Ruangan / Seksi';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info text-white px-3 py-2 rounded-pill fw-bold"><i class="bi bi-door-open"></i> MASTER DATA</span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Master Ruangan & Seksi</h3>
        </div>

        <button class="btn btn-primary-custom rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahRuangan">
            <i class="bi bi-plus-circle me-1"></i> + Tambah Ruangan Baru
        </button>
    </div>

    <div class="card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kode Ruangan</th>
                            <th>Nama Ruangan / Seksi</th>
                            <th>Lokasi / Lantai</th>
                            <th>Penanggung Jawab</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($ruanganList as $r): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-primary-subtle text-primary border border-primary"><?= htmlspecialchars($r['kode_ruangan']) ?></span></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($r['nama_ruangan']) ?></td>
                                <td><?= htmlspecialchars($r['lokasi_lantai'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['penanggung_jawab'] ?? '-') ?></td>
                                <td>
                                    <?= $r['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-circle btn-edit"
                                            data-id="<?= $r['id'] ?>"
                                            data-kode="<?= htmlspecialchars($r['kode_ruangan']) ?>"
                                            data-nama="<?= htmlspecialchars($r['nama_ruangan']) ?>"
                                            data-lokasi="<?= htmlspecialchars($r['lokasi_lantai'] ?? '') ?>"
                                            data-pj="<?= htmlspecialchars($r['penanggung_jawab'] ?? '') ?>"
                                            data-status="<?= $r['is_active'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Ruangan -->
<div class="modal fade" id="modalTambahRuangan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>admin/master_ruangan.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header card-header-gradient text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Tambah Ruangan / Seksi Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Kode Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="kode_ruangan" class="form-control form-control-custom" placeholder="Contoh: RUG-PNJ" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Nama Ruangan / Seksi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ruangan" class="form-control form-control-custom" placeholder="Contoh: Seksi Penunjang" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Lokasi / Lantai</label>
                        <input type="text" name="lokasi_lantai" class="form-control form-control-custom" placeholder="Contoh: Gedung Utama Lantai 2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Nama Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" class="form-control form-control-custom" placeholder="Contoh: Bapak Budi Santoso">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Simpan Ruangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Ruangan -->
<div class="modal fade" id="modalEditRuangan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>admin/master_ruangan.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i> Edit Ruangan / Seksi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Kode Ruangan</label>
                        <input type="text" name="kode_ruangan" id="edit_kode" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Nama Ruangan / Seksi</label>
                        <input type="text" name="nama_ruangan" id="edit_nama" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Lokasi / Lantai</label>
                        <input type="text" name="lokasi_lantai" id="edit_lokasi" class="form-control form-control-custom">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" id="edit_pj" class="form-control form-control-custom">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Status</label>
                        <select name="is_active" id="edit_status" class="form-select form-select-custom">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScript = "
<script>
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_kode').value = this.dataset.kode;
            document.getElementById('edit_nama').value = this.dataset.nama;
            document.getElementById('edit_lokasi').value = this.dataset.lokasi;
            document.getElementById('edit_pj').value = this.dataset.pj;
            document.getElementById('edit_status').value = this.dataset.status;
            new bootstrap.Modal(document.getElementById('modalEditRuangan')).show();
        });
    });
</script>
";
require_once __DIR__ . '/../includes/footer.php';
?>
