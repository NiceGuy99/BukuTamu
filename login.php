<?php
/**
 * Halaman Login Petugas / Pegawai
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/helpers/functions.php';

// Jika sudah login, langsung redirect ke dashboard yang sesuai
if (isLoggedIn()) {
    $user = currentUser();
    if ($user['role'] === 'cs') {
        header('Location: ' . BASE_URL . 'cs/index.php');
    } elseif ($user['role'] === 'ruangan') {
        header('Location: ' . BASE_URL . 'ruangan/index.php');
    } else {
        header('Location: ' . BASE_URL . 'admin/laporan.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = clean($_POST['password'] ?? '');
    $captcha = trim($_POST['captcha'] ?? '');
    $expectedCaptcha = $_SESSION['login_captcha_sum'] ?? null;

    if (empty($username) || empty($password)) {
        $error = 'Username dan Password wajib diisi.';
    } elseif ($captcha === '' || !is_numeric($captcha) || (int)$captcha !== (int)$expectedCaptcha) {
        $error = 'Jawaban Captcha penjumlahan salah! Silakan coba lagi.';
    } else {
        $user = User::authenticate($username, $password);
        if ($user) {
            unset($_SESSION['login_captcha_sum']);
            loginUser($user);
            setFlash('success', "Selamat datang kembali, <strong>{$user['nama_lengkap']}</strong>!");

            // Redirect ke halaman yang diminta sebelumnya atau default role dashboard
            $redirectTo = $_SESSION['redirect_after_login'] ?? '';
            unset($_SESSION['redirect_after_login']);

            if (!empty($redirectTo)) {
                header("Location: $redirectTo");
            } elseif ($user['role'] === 'cs') {
                header('Location: ' . BASE_URL . 'cs/index.php');
            } elseif ($user['role'] === 'ruangan') {
                header('Location: ' . BASE_URL . 'ruangan/index.php');
            } else {
                header('Location: ' . BASE_URL . 'admin/laporan.php');
            }
            exit;
        } else {
            $error = 'Username atau Password salah, atau akun Anda dinonaktifkan.';
        }
    }
}

// Generate angka baru untuk captcha penjumlahan
$num1 = rand(1, 15);
$num2 = rand(1, 15);
$_SESSION['login_captcha_sum'] = $num1 + $num2;

$pageTitle = 'Login Petugas - Buku Tamu Digital';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card-custom shadow-lg">
                <div class="card-header-gradient p-4 text-center">
                    <div class="p-2 d-inline-flex rounded-circle bg-white shadow-sm mb-2 align-items-center justify-content-center" style="width: 72px; height: 72px;">
                        <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo <?= APP_ORG ?>" style="max-height: 54px; max-width: 54px; object-fit: contain;">
                    </div>
                    <h4 class="fw-bold mb-1">Login Petugas</h4>
                    <p class="text-white-50 small mb-0"><?= APP_ORG ?> &bull; CS, Ruangan & Admin</p>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>login.php" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label class="form-label-custom">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" id="inputUsername" class="form-control form-control-custom" placeholder="Masukkan username..." value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" id="inputPassword" class="form-control form-control-custom" placeholder="Masukkan password..." required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Math Addition Captcha -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label-custom mb-0">
                                    <i class="bi bi-shield-check text-primary me-1"></i> Keamanan (Captcha)
                                </label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" onclick="refreshCaptcha()" title="Ganti Pertanyaan Captcha" style="font-size: 0.8rem;">
                                    <i class="bi bi-arrow-clockwise me-1" id="refreshIcon"></i>Ganti Soal
                                </button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text fw-bold text-primary px-3 user-select-none" id="captchaBadge" style="letter-spacing: 1px; font-size: 1.05rem; background: linear-gradient(135deg, #e0f2fe 0%, #f0fdf4 100%); border-color: #bae6fd;">
                                    <span id="captchaQuestion"><?= $num1 ?> + <?= $num2 ?> = ?</span>
                                </span>
                                <input type="number" name="captcha" id="inputCaptcha" class="form-control form-control-custom" placeholder="Hasil penjumlahan..." required autocomplete="off">
                            </div>
                            <div class="form-text small text-muted">Hitung dan masukkan hasil penjumlahan di samping untuk verifikasi.</div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-2 fs-6 mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
                        </button>
                    </form>

                    <!-- Quick Demo Fill Helpers -->
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="small fw-bold text-muted mb-2 text-center text-uppercase" style="letter-spacing: 0.5px;">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Fill Akun Demo:
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary text-start" onclick="fillCredentials('cs', 'cs123')">
                                <i class="bi bi-headset me-1 text-warning"></i> <strong>Customer Service:</strong> <code>cs</code> / <code>cs123</code>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success text-start" onclick="fillCredentials('penunjang', 'penunjang123')">
                                <i class="bi bi-building me-1 text-success"></i> <strong>Ruangan (Bpk B - Kasi Penunjang):</strong> <code>penunjang</code> / <code>penunjang123</code>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-dark text-start" onclick="fillCredentials('admin', 'admin123')">
                                <i class="bi bi-gear-fill me-1 text-primary"></i> <strong>Administrator:</strong> <code>admin</code> / <code>admin123</code>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top text-center py-3 text-muted small">
                    <a href="<?= BASE_URL ?>" class="text-decoration-none text-muted">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Formulir Tamu Publik
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const pass = document.getElementById('inputPassword');
        const icon = document.getElementById('toggleIcon');
        if (pass.type === 'password') {
            pass.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            pass.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    function fillCredentials(user, pass) {
        document.getElementById('inputUsername').value = user;
        document.getElementById('inputPassword').value = pass;
        const captchaInput = document.getElementById('inputCaptcha');
        if (captchaInput) {
            captchaInput.focus();
        }
    }

    function refreshCaptcha() {
        const questionEl = document.getElementById('captchaQuestion');
        const iconEl = document.getElementById('refreshIcon');
        const inputCaptcha = document.getElementById('inputCaptcha');
        
        if (iconEl) iconEl.classList.add('spin-animation');
        
        fetch('<?= BASE_URL ?>api/captcha.php')
            .then(res => res.json())
            .then(data => {
                if (data && data.question) {
                    if (questionEl) questionEl.textContent = data.question;
                    if (inputCaptcha) {
                        inputCaptcha.value = '';
                        inputCaptcha.focus();
                    }
                }
            })
            .catch(err => {
                console.error('Gagal memperbarui captcha:', err);
            })
            .finally(() => {
                if (iconEl) iconEl.classList.remove('spin-animation');
            });
    }
</script>

<style>
    .spin-animation {
        display: inline-block;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin {
        100% { transform: rotate(360deg); }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
