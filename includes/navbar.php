<?php
/**
 * Template Navbar Buku Tamu Digital (Role-Based Dynamic Navbar)
 */

$currentScript = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
$user = currentUser();
?>
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none py-0" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo <?= APP_ORG ?>" class="navbar-logo">
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark lh-1" style="font-size: 1.05rem; letter-spacing: -0.2px;"><?= APP_ORG ?></span>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700;">BUKU TAMU</span>
                </div>
                <div class="text-muted small d-none d-sm-block lh-sm" style="font-size: 0.73rem; margin-top: 2px; font-weight: 500;"><?= APP_SUBTITLE ?></div>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1 mt-3 mt-lg-0">
                <!-- 1. Form Tamu (Selalu bisa diakses tanpa login) -->
                <li class="nav-item">
                    <a class="nav-link nav-custom-link <?= ($currentScript === 'index.php' && $currentDir !== 'cs' && $currentDir !== 'ruangan' && $currentDir !== 'admin') ? 'active' : '' ?>" href="<?= BASE_URL ?>">
                        <i class="bi bi-pencil-square text-primary"></i>
                        <span>Form Tamu</span>
                    </a>
                </li>

                <!-- 2. Lacak Tamu (Publik) -->
                <li class="nav-item">
                    <a class="nav-link nav-custom-link <?= ($currentScript === 'lacak.php' || $currentScript === 'tiket.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>lacak.php">
                        <i class="bi bi-search text-info"></i>
                        <span>Lacak Tiket</span>
                    </a>
                </li>

                <!-- 3. Display TV Monitor (Publik / TV) -->
                <li class="nav-item">
                    <a class="nav-link nav-custom-link" href="<?= BASE_URL ?>monitor.php" target="_blank">
                        <i class="bi bi-tv text-purple"></i>
                        <span>Display TV <i class="bi bi-box-arrow-up-right small"></i></span>
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <!-- MENU UNTUK USER LOGIN: -->
                    
                    <!-- CS Desk: Admin atau CS -->
                    <?php if ($user['role'] === 'admin' || $user['role'] === 'cs'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-custom-link dropdown-toggle <?= ($currentDir === 'cs') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-headset text-warning"></i>
                                <span>Customer Service</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2">
                                <li><a class="dropdown-item rounded-2 py-2 <?= ($currentScript === 'index.php' && $currentDir === 'cs') ? 'active' : '' ?>" href="<?= BASE_URL ?>cs/index.php"><i class="bi bi-list-check me-2 text-primary"></i> Antrian Kunjungan CS</a></li>
                                <li><a class="dropdown-item rounded-2 py-2 <?= ($currentScript === 'ketersediaan.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>cs/ketersediaan.php"><i class="bi bi-person-lines-fill me-2 text-success"></i> Ketersediaan Pejabat</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Portal Ruangan: Admin atau Ruangan -->
                    <?php if ($user['role'] === 'admin' || $user['role'] === 'ruangan'): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-custom-link <?= ($currentDir === 'ruangan') ? 'active' : '' ?>" href="<?= BASE_URL ?>ruangan/index.php">
                                <i class="bi bi-building-check text-success"></i>
                                <span>ACC Ruangan</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Menu Admin: Hanya Admin -->
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-custom-link dropdown-toggle <?= ($currentDir === 'admin') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear-fill text-secondary"></i>
                                <span>Admin & Laporan</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2">
                                <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>admin/laporan.php"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i> Rekapitulasi Laporan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>admin/master_ruangan.php"><i class="bi bi-door-open me-2 text-info"></i> Master Ruangan / Seksi</a></li>
                                <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>admin/master_pegawai.php"><i class="bi bi-people me-2 text-success"></i> Master Pejabat / Pegawai</a></li>
                                <li><a class="dropdown-item rounded-2 py-2" href="<?= BASE_URL ?>admin/master_user.php"><i class="bi bi-person-badge me-2 text-warning"></i> Manajemen User & Akun</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- User Profile & Logout Dropdown -->
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="btn btn-light rounded-pill px-3 py-1 dropdown-toggle d-flex align-items-center gap-2 border" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="p-1 rounded-circle bg-primary text-white small" style="width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="text-start d-none d-md-block">
                                <div class="fw-bold text-dark small lh-1"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'cs' ? 'warning text-dark' : 'success') ?> p-1" style="font-size: 0.65rem;">
                                        <?= strtoupper($user['role']) ?>
                                    </span>
                                    <?php if ($user['role'] === 'ruangan'): ?>
                                        <span class="badge bg-<?= ($user['status_ketersediaan'] ?? '') === 'Ada di Tempat' ? 'success' : (($user['status_ketersediaan'] ?? '') === 'Sedang Rapat' ? 'warning text-dark' : 'danger') ?> p-1" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($user['status_ketersediaan'] ?? 'Ada di Tempat') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2" style="min-width: 240px;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                                <div class="text-muted small">@<?= htmlspecialchars($user['username']) ?> &bull; <?= ucfirst($user['role']) ?></div>
                                <?php if (!empty($user['nama_ruangan'])): ?>
                                    <div class="text-primary small mt-1"><i class="bi bi-building me-1"></i> <?= htmlspecialchars($user['nama_ruangan']) ?></div>
                                <?php endif; ?>
                            </li>

                            <?php if ($user['role'] === 'ruangan'): ?>
                                <li class="px-3 py-2 border-bottom bg-light rounded-2 my-1">
                                    <div class="small fw-bold text-muted mb-1 text-uppercase" style="font-size: 0.7rem;">Status Ketersediaan:</div>
                                    <form action="<?= BASE_URL ?>api/update_ketersediaan.php" method="POST" class="d-flex flex-column gap-1">
                                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? BASE_URL) ?>">
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <button type="submit" name="status_ketersediaan" value="Ada di Tempat" class="btn btn-outline-success <?= ($user['status_ketersediaan'] ?? '') === 'Ada di Tempat' ? 'active fw-bold' : '' ?>" title="Ada di Tempat">🟢 Ada</button>
                                            <button type="submit" name="status_ketersediaan" value="Sedang Rapat" class="btn btn-outline-warning text-dark <?= ($user['status_ketersediaan'] ?? '') === 'Sedang Rapat' ? 'active fw-bold' : '' ?>" title="Sedang Rapat">🟡 Rapat</button>
                                            <button type="submit" name="status_ketersediaan" value="Dinas Luar" class="btn btn-outline-danger <?= ($user['status_ketersediaan'] ?? '') === 'Dinas Luar' ? 'active fw-bold' : '' ?>" title="Dinas Luar">🔴 Dinas</button>
                                        </div>
                                    </form>
                                </li>
                            <?php endif; ?>

                            <li><a class="dropdown-item text-danger rounded-2 py-2 mt-1" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout / Keluar</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <!-- Tombol Login Petugas jika Belum Login -->
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary-custom rounded-pill px-4 py-2" href="<?= BASE_URL ?>login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login Petugas
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Container Pesan Flash Alert -->
<div class="container-fluid px-lg-4 pt-3">
    <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill text-success' : ($flash['type'] === 'error' ? 'exclamation-octagon-fill text-danger' : 'info-circle-fill text-primary') ?> fs-5 me-2"></i>
                <div><?= $flash['message'] ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>
