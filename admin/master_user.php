<?php
/**
 * Master Data User & Akun Petugas
 * Terintegrasi dengan Master Ruangan & Master Pejabat / Pegawai
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ruangan.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/functions.php';

// Wajib Login sebagai Admin
requireLogin(['admin']);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = clean($_POST['aksi'] ?? '');

    if ($aksi === 'tambah') {
        $username = clean($_POST['username'] ?? '');
        $password = clean($_POST['password'] ?? '');
        $nama = clean($_POST['nama_lengkap'] ?? '');
        $role = clean($_POST['role'] ?? '');
        $ruanganId = (int)($_POST['ruangan_id'] ?? 0);
        $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);

        // Jika pegawai_id dipilih dan ruangan_id kosong, ambil ruangan dari pegawai
        if ($pegawaiId > 0 && empty($ruanganId)) {
            $p = Pegawai::find($pegawaiId);
            if ($p) {
                $ruanganId = $p['ruangan_id'];
            }
        }

        if (!empty($username) && !empty($password) && !empty($nama) && !empty($role)) {
            try {
                User::create([
                    'username' => $username,
                    'password' => $password,
                    'nama_lengkap' => $nama,
                    'role' => $role,
                    'ruangan_id' => ($role === 'ruangan' ? $ruanganId : null),
                    'pegawai_id' => ($role === 'ruangan' ? $pegawaiId : null),
                    'is_active' => 1
                ]);
                setFlash('success', "Akun user <strong>$username</strong> berhasil dibuat!");
            } catch (Exception $e) {
                setFlash('error', "Gagal menambah user (mungkin username sudah digunakan): " . $e->getMessage());
            }
        } else {
            setFlash('error', 'Semua field wajib diisi.');
        }
    } elseif ($aksi === 'edit') {
        $id = (int)$_POST['id'];
        $username = clean($_POST['username'] ?? '');
        $password = clean($_POST['password'] ?? '');
        $nama = clean($_POST['nama_lengkap'] ?? '');
        $role = clean($_POST['role'] ?? '');
        $ruanganId = (int)($_POST['ruangan_id'] ?? 0);
        $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 1);

        if ($pegawaiId > 0 && empty($ruanganId)) {
            $p = Pegawai::find($pegawaiId);
            if ($p) {
                $ruanganId = $p['ruangan_id'];
            }
        }

        try {
            User::update($id, [
                'username' => $username,
                'password' => $password, // kosong jika tidak diubah
                'nama_lengkap' => $nama,
                'role' => $role,
                'ruangan_id' => ($role === 'ruangan' ? $ruanganId : null),
                'pegawai_id' => ($role === 'ruangan' ? $pegawaiId : null),
                'is_active' => $isActive
            ]);
            setFlash('success', "Data user <strong>$username</strong> berhasil diperbarui!");
        } catch (Exception $e) {
            setFlash('error', "Gagal memperbarui user: " . $e->getMessage());
        }
    } elseif ($aksi === 'hapus') {
        $id = (int)$_POST['id'];
        if ($id === 1) {
            setFlash('error', 'Akun Administrator utama tidak boleh dihapus.');
        } else {
            User::delete($id);
            setFlash('success', 'Akun user berhasil dihapus.');
        }
    }

    header('Location: ' . BASE_URL . 'admin/master_user.php');
    exit;
}

$userList = User::getAll();
$listRuangan = Ruangan::getAll();
$listPegawai = Pegawai::getAll();

$pageTitle = 'Manajemen User & Akun Petugas';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold"><i class="bi bi-person-badge"></i> HAK AKSES</span>
            </div>
            <h3 class="fw-bold text-dark mt-1 mb-0">Manajemen Akun Petugas & Ruangan</h3>
        </div>

        <button class="btn btn-primary-custom rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="bi bi-person-plus-fill me-1"></i> + Tambah Akun User Baru
        </button>
    </div>

    <div class="card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Username</th>
                            <th>Nama Akun</th>
                            <th>Role / Peran</th>
                            <th>Penugasan Ruangan</th>
                            <th>Pejabat Terhubung</th>
                            <th>Status Ketersediaan</th>
                            <th>Status Akun</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($userList as $u): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="fw-bold text-dark">@<?= htmlspecialchars($u['username']) ?></span>
                                </td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : ($u['role'] === 'cs' ? 'warning text-dark' : 'success') ?> rounded-pill px-3 py-1">
                                        <?= strtoupper($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($u['nama_ruangan']) ? htmlspecialchars($u['nama_ruangan']) : '<span class="text-muted fst-italic">-</span>' ?>
                                </td>
                                <td>
                                    <?php if (!empty($u['nama_pegawai'])): ?>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($u['nama_pegawai']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($u['jabatan'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'ruangan'): ?>
                                        <span class="badge bg-<?= ($u['status_ketersediaan'] ?? '') === 'Ada di Tempat' ? 'success' : (($u['status_ketersediaan'] ?? '') === 'Sedang Rapat' ? 'warning text-dark' : 'danger') ?>">
                                            <?= htmlspecialchars($u['status_ketersediaan'] ?? 'Ada di Tempat') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $u['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle btn-edit-user"
                                                data-id="<?= $u['id'] ?>"
                                                data-username="<?= htmlspecialchars($u['username']) ?>"
                                                data-nama="<?= htmlspecialchars($u['nama_lengkap']) ?>"
                                                data-role="<?= htmlspecialchars($u['role']) ?>"
                                                data-ruangan="<?= $u['ruangan_id'] ?? '' ?>"
                                                data-pegawai="<?= $u['pegawai_id'] ?? '' ?>"
                                                data-status="<?= $u['is_active'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($u['id'] > 1): ?>
                                            <form action="<?= BASE_URL ?>admin/master_user.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun ini?');" class="d-inline">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>admin/master_user.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header card-header-gradient text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Tambah Akun User Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Nama Lengkap Akun <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control form-control-custom" placeholder="Contoh: Bapak B (Kepala Seksi Penunjang)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control form-control-custom" placeholder="Contoh: penunjang" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control form-control-custom" placeholder="Password akun..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Role / Peran Akses <span class="text-danger">*</span></label>
                        <select name="role" id="tambah_role" class="form-select form-select-custom" required>
                            <option value="cs">Customer Service (CS Desk)</option>
                            <option value="ruangan" selected>Ruangan / Pejabat Tujuan</option>
                            <option value="admin">Administrator (Akses Penuh)</option>
                        </select>
                    </div>

                    <!-- Pilihan Pejabat Terhubung & Ruangan -->
                    <div id="tambah_ruangan_container">
                        <div class="mb-3">
                            <label class="form-label-custom">Hubungkan ke Master Pejabat / Pegawai</label>
                            <select name="pegawai_id" id="tambah_pegawai_id" class="form-select form-select-custom">
                                <option value="">-- Pilih Pejabat / Pegawai (Opsional) --</option>
                                <?php foreach ($listPegawai as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-ruangan="<?= $p['ruangan_id'] ?>">
                                        <?= htmlspecialchars($p['nama_pegawai']) ?> - <?= htmlspecialchars($p['jabatan']) ?> (<?= htmlspecialchars($p['nama_ruangan'] ?? 'Umum') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Menghubungkan akun ini agar pejabat dapat mengubah status ketersediaannya.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Tugaskan ke Ruangan / Seksi</label>
                            <select name="ruangan_id" id="tambah_ruangan_id" class="form-select form-select-custom">
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($listRuangan as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= BASE_URL ?>admin/master_user.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit_u_id">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i> Edit Akun User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_u_nama" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Username</label>
                        <input type="text" name="username" id="edit_u_username" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Ganti Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="form-control form-control-custom" placeholder="Biarkan kosong jika tidak ganti...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Role / Peran Akses</label>
                        <select name="role" id="edit_u_role" class="form-select form-select-custom" required>
                            <option value="cs">Customer Service (CS Desk)</option>
                            <option value="ruangan">Ruangan / Pejabat Tujuan</option>
                            <option value="admin">Administrator (Akses Penuh)</option>
                        </select>
                    </div>

                    <div id="edit_u_ruangan_container">
                        <div class="mb-3">
                            <label class="form-label-custom">Hubungkan ke Master Pejabat / Pegawai</label>
                            <select name="pegawai_id" id="edit_u_pegawai" class="form-select form-select-custom">
                                <option value="">-- Pilih Pejabat / Pegawai (Opsional) --</option>
                                <?php foreach ($listPegawai as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-ruangan="<?= $p['ruangan_id'] ?>">
                                        <?= htmlspecialchars($p['nama_pegawai']) ?> - <?= htmlspecialchars($p['jabatan']) ?> (<?= htmlspecialchars($p['nama_ruangan'] ?? 'Umum') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Tugaskan ke Ruangan / Seksi</label>
                            <select name="ruangan_id" id="edit_u_ruangan" class="form-select form-select-custom">
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($listRuangan as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Status Akun</label>
                        <select name="is_active" id="edit_u_status" class="form-select form-select-custom">
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
    document.addEventListener('DOMContentLoaded', function() {
        const tambahRole = document.getElementById('tambah_role');
        const tambahRuanganContainer = document.getElementById('tambah_ruangan_container');
        if (tambahRole && tambahRuanganContainer) {
            tambahRole.addEventListener('change', function() {
                if (this.value === 'ruangan') {
                    tambahRuanganContainer.classList.remove('d-none');
                } else {
                    tambahRuanganContainer.classList.add('d-none');
                }
            });
        }

        const editRole = document.getElementById('edit_u_role');
        const editRuanganContainer = document.getElementById('edit_u_ruangan_container');
        if (editRole && editRuanganContainer) {
            editRole.addEventListener('change', function() {
                if (this.value === 'ruangan') {
                    editRuanganContainer.classList.remove('d-none');
                } else {
                    editRuanganContainer.classList.add('d-none');
                }
            });
        }

        document.querySelectorAll('.btn-edit-user').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_u_id').value = this.dataset.id;
                document.getElementById('edit_u_nama').value = this.dataset.nama;
                document.getElementById('edit_u_username').value = this.dataset.username;
                document.getElementById('edit_u_role').value = this.dataset.role;
                document.getElementById('edit_u_ruangan').value = this.dataset.ruangan || '';
                document.getElementById('edit_u_pegawai').value = this.dataset.pegawai || '';
                document.getElementById('edit_u_status').value = this.dataset.status;

                if (this.dataset.role === 'ruangan') {
                    editRuanganContainer.classList.remove('d-none');
                } else {
                    editRuanganContainer.classList.add('d-none');
                }

                new bootstrap.Modal(document.getElementById('modalEditUser')).show();
            });
        });
    });
</script>
";
require_once __DIR__ . '/../includes/footer.php';
?>
