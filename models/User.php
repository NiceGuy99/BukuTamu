<?php
/**
 * Model User untuk Otentikasi & Manajemen Pengguna
 * Terintegrasi dengan Master Ruangan & Master Pejabat/Pegawai
 */

require_once __DIR__ . '/Database.php';

class User {
    public static function getBaseQuery(): string {
        return "
            SELECT u.*, 
                   r.nama_ruangan, r.lokasi_lantai, r.kode_ruangan,
                   p.nama_pegawai, p.jabatan, p.nip, p.no_hp_wa, 
                   COALESCE(p.status_ketersediaan, 'Ada di Tempat') as status_ketersediaan
            FROM users u
            LEFT JOIN ruangan r ON u.ruangan_id = r.id
            LEFT JOIN pegawai p ON u.pegawai_id = p.id
        ";
    }

    public static function authenticate(string $username, string $password): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare(self::getBaseQuery() . " WHERE u.username = ? AND u.is_active = 1");
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Hapus hash password dari session/return
            return $user;
        }

        return null;
    }

    public static function find($id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare(self::getBaseQuery() . " WHERE u.id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            unset($user['password']);
        }
        return $user ?: null;
    }

    public static function getAll(): array {
        $db = Database::getConnection();
        return $db->query(self::getBaseQuery() . " ORDER BY u.id ASC")->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO users (username, password, nama_lengkap, role, ruangan_id, pegawai_id, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['nama_lengkap'],
            $data['role'],
            $data['ruangan_id'] ?: null,
            $data['pegawai_id'] ?: null,
            $data['is_active'] ?? 1
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update($id, array $data): bool {
        $db = Database::getConnection();
        if (!empty($data['password'])) {
            $stmt = $db->prepare("
                UPDATE users 
                SET username = ?, password = ?, nama_lengkap = ?, role = ?, ruangan_id = ?, pegawai_id = ?, is_active = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['nama_lengkap'],
                $data['role'],
                $data['ruangan_id'] ?: null,
                $data['pegawai_id'] ?: null,
                $data['is_active'] ?? 1,
                $id
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE users 
                SET username = ?, nama_lengkap = ?, role = ?, ruangan_id = ?, pegawai_id = ?, is_active = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['username'],
                $data['nama_lengkap'],
                $data['role'],
                $data['ruangan_id'] ?: null,
                $data['pegawai_id'] ?: null,
                $data['is_active'] ?? 1,
                $id
            ]);
        }
    }

    /**
     * Ubah status ketersediaan pejabat yang terhubung dengan akun user
     */
    public static function updateStatusKetersediaan($userId, $status): bool {
        $user = self::find($userId);
        if (!$user) return false;

        $pegawaiId = $user['pegawai_id'] ?? null;
        if (!$pegawaiId) {
            // Jika user ruangan belum punya pegawai_id, coba cari pegawai di ruangan tersebut
            if (!empty($user['ruangan_id'])) {
                $db = Database::getConnection();
                $stmt = $db->prepare("SELECT id FROM pegawai WHERE ruangan_id = ? LIMIT 1");
                $stmt->execute([$user['ruangan_id']]);
                $p = $stmt->fetch();
                if ($p) {
                    $pegawaiId = $p['id'];
                    $db->exec("UPDATE users SET pegawai_id = $pegawaiId WHERE id = $userId");
                }
            }
        }

        if ($pegawaiId) {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE pegawai SET status_ketersediaan = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $res = $stmt->execute([$status, $pegawaiId]);

            // Perbarui session user jika sedang aktif
            if (isset($_SESSION['auth_user']) && $_SESSION['auth_user']['id'] == $userId) {
                $_SESSION['auth_user']['status_ketersediaan'] = $status;
                $_SESSION['auth_user']['pegawai_id'] = $pegawaiId;
            }

            return $res;
        }

        return false;
    }

    public static function delete($id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
