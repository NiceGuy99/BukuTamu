<?php
/**
 * Master Data Pegawai / Pejabat Tujuan
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../models/Ruangan.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin
requireLogin(['admin']);

// Handle POST Add/Edit/Delete/Sync
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = clean($_POST['aksi'] ?? '');
    
    if ($aksi === 'sinkron_ruangan') {
        $syncedCount = Pegawai::syncFromRuangan();
        setFlash('success', "Sinkronisasi berhasil! Sebanyak <strong>{$syncedCount}</strong> data pejabat berhasil disinkronkan dari Penanggung Jawab Master Ruangan.");
    } elseif ($aksi === 'tambah') {
        $nama = clean($_POST['nama_pegawai'] ?? '');
        $ruanganId = (int)($_POST['ruangan_id'] ?? 0);
        $jabatan = clean($_POST['jabatan'] ?? '') ?: '-';
        $nip = clean($_POST['nip'] ?? '') ?: '-';
        $noHp = clean($_POST['no_hp_wa'] ?? '') ?: '-';
        $email = clean($_POST['email'] ?? '') ?: '-';
        $statusKetersediaan = clean($_POST['status_ketersediaan'] ?? 'Ada di Tempat');
        
        if (!empty($nama)) {
            Pegawai::create([
                'ruangan_id' => $ruanganId ?: null,
                'nama_pegawai' => $nama,
                'nip' => $nip,
                'jabatan' => $jabatan,
                'no_hp_wa' => $noHp,
                'email' => $email,
                'status_ketersediaan' => $statusKetersediaan,
                'is_active' => 1
            ]);
            setFlash('success', 'Pegawai / Pejabat baru berhasil ditambahkan!');
        } else {
            setFlash('error', 'Nama Pegawai / Pejabat wajib diisi.');
        }
    } elseif ($aksi === 'edit') {
        $id = (int)$_POST['id'];
        Pegawai::update($id, [
            'ruangan_id' => (int)($_POST['ruangan_id'] ?? 0) ?: null,
            'nama_pegawai' => clean($_POST['nama_pegawai'] ?? ''),
            'nip' => clean($_POST['nip'] ?? '') ?: '-',
            'jabatan' => clean($_POST['jabatan'] ?? '') ?: '-',
            'no_hp_wa' => clean($_POST['no_hp_wa'] ?? '') ?: '-',
            'email' => clean($_POST['email'] ?? '') ?: '-',
            'status_ketersediaan' => clean($_POST['status_ketersediaan'] ?? 'Ada di Tempat'),
            'is_active' => (int)($_POST['is_active'] ?? 1)
        ]);
        setFlash('success', 'Data pegawai berhasil diperbarui!');
    }
    
    header('Location: ' . BASE_URL . 'admin/master_pegawai.php');
    exit;
}

$pegawaiList = Pegawai::getAll(false);
$listRuangan = Ruangan::getAll();

$pageTitle = 'Master Pegawai / Pejabat Tujuan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold"><i class="bi bi-people"></i> MASTER DATA</span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Master Pejabat & Pegawai Tujuan</h3>
            <p class="text-muted small mb-0">Data pejabat tujuan kunjungan yang tersinkronisasi dengan Master Ruangan & Penanggung Jawab.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <form action="<?= BASE_URL ?>admin/master_pegawai.php" method="POST" onsubmit="return confirm('Sinkronkan seluruh nama pejabat dari Penanggung Jawab Master Ruangan?')">
                <input type="hidden" name="aksi" value="sinkron_ruangan">
                <button type="submit" class="btn btn-outline-success rounded-pill px-3">
                    <i class="bi bi-arrow-repeat me-1"></i> 🔄 Sinkronkan dari Master Ruangan
                </button>
            </form>
            <button class="btn btn-primary-custom rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahPegawai">
                <i class="bi bi-person-plus-fill me-1"></i> + Tambah Pegawai Baru
            </button>
        </div>
    </div>

    <div class="card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Pegawai / Pejabat</th>
                            <th>Jabatan</th>
                            <th>Ruangan / Seksi</th>
                            <th>Kontak WhatsApp</th>
                            <th>Ketersediaan</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($pegawaiList as $p): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['nama_pegawai']) ?></div>
                                    <small class="text-muted">NIP: <?= htmlspecialchars($p['nip'] ?? '-') ?></small>
                                </td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($p['jabatan']) ?></td>
                                <td><?= htmlspecialchars($p['nama_ruangan'] ?? 'Umum / Direksi') ?></td>
                                <td>
                                    <?php if (!empty($p['no_hp_wa'])): ?>
                                        <span class="text-success"><i class="bi bi-whatsapp me-1"></i> <?= htmlspecialchars($p['no_hp_wa']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $p['status_ketersediaan'] === 'Ada di Tempat' ? 'success' : ($p['status_ketersediaan'] === 'Sedang Rapat' ? 'warning text-dark' : 'secondary') ?>">
                                        <?= htmlspecialchars($p['status_ketersediaan'] ?? 'Ada di Tempat') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-circle btn-edit"
                                            data-id="<?= $p['id'] ?>"
                                            data-nama="<?= htmlspecialchars($p['nama_pegawai']) ?>"
                                            data-nip="<?= htmlspecialchars($p['nip'] ?? '') ?>"
                                            data-jabatan="<?= htmlspecialchars($p['jabatan']) ?>"
                                            data-ruangan="<?= $p['ruangan_id'] ?>"
                                            data-wa="<?= htmlspecialchars($p['no_hp_wa'] ?? '') ?>"
                                            data-email="<?= htmlspecialchars($p['email'] ?? '') ?>"
                                            data-avail="<?= htmlspecialchars($p['status_ketersediaan'] ?? 'Ada di Tempat') ?>"
                                            data-status="<?= $p['is_active'] ?>">
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

<!-- Modal Tambah Pegawai -->
<div class="modal fade" id="modalTambahPegawai" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>admin/master_pegawai.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header card-header-gradient text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Tambah Pejabat / Pegawai Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pegawai" class="form-control form-control-custom" placeholder="Nama pejabat / penanggung jawab..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Ruangan / Seksi <span class="text-danger">*</span></label>
                            <select name="ruangan_id" class="form-select form-select-custom" required>
                                <option value="">-- Pilih Ruangan / Seksi --</option>
                                <?php foreach ($listRuangan as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Jabatan (Opsional)</label>
                            <input type="text" name="jabatan" class="form-control form-control-custom" placeholder="Default: -" value="-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">NIP (Opsional)</label>
                            <input type="text" name="nip" class="form-control form-control-custom" placeholder="Default: -" value="-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Nomor WhatsApp / HP (Opsional)</label>
                            <input type="text" name="no_hp_wa" class="form-control form-control-custom" placeholder="Default: -" value="-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Status Ketersediaan</label>
                            <select name="status_ketersediaan" class="form-select form-select-custom">
                                <option value="Ada di Tempat">Ada di Tempat</option>
                                <option value="Sedang Rapat">Sedang Rapat</option>
                                <option value="Dinas Luar">Dinas Luar</option>
                                <option value="Tidak di Tempat">Tidak di Tempat</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Simpan Pegawai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pegawai -->
<div class="modal fade" id="modalEditPegawai" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>admin/master_pegawai.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit_p_id">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i> Edit Pejabat / Pegawai</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Nama Lengkap & Gelar</label>
                            <input type="text" name="nama_pegawai" id="edit_p_nama" class="form-control form-control-custom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Ruangan / Seksi</label>
                            <select name="ruangan_id" id="edit_p_ruangan" class="form-select form-select-custom">
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($listRuangan as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Jabatan</label>
                            <input type="text" name="jabatan" id="edit_p_jabatan" class="form-control form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">NIP</label>
                            <input type="text" name="nip" id="edit_p_nip" class="form-control form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">No. WhatsApp / HP</label>
                            <input type="text" name="no_hp_wa" id="edit_p_wa" class="form-control form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Status Ketersediaan</label>
                            <select name="status_ketersediaan" id="edit_p_avail" class="form-select form-select-custom">
                                <option value="Ada di Tempat">Ada di Tempat</option>
                                <option value="Sedang Rapat">Sedang Rapat</option>
                                <option value="Dinas Luar">Dinas Luar</option>
                                <option value="Tidak di Tempat">Tidak di Tempat</option>
                            </select>
                        </div>
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
            document.getElementById('edit_p_id').value = this.dataset.id;
            document.getElementById('edit_p_nama').value = this.dataset.nama;
            document.getElementById('edit_p_nip').value = this.dataset.nip;
            document.getElementById('edit_p_jabatan').value = this.dataset.jabatan;
            document.getElementById('edit_p_ruangan').value = this.dataset.ruangan || '';
            document.getElementById('edit_p_wa').value = this.dataset.wa;
            document.getElementById('edit_p_avail').value = this.dataset.avail;
            new bootstrap.Modal(document.getElementById('modalEditPegawai')).show();
        });
    });
</script>
";
require_once __DIR__ . '/../includes/footer.php';
?>
