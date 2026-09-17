<?php
/**
 * Model Pegawai / Pejabat
 */

require_once __DIR__ . '/Database.php';

class Pegawai {
    public static function getAll($activeOnly = true): array {
        $db = Database::getConnection();
        $sql = "
            SELECT p.*, r.nama_ruangan, r.lokasi_lantai 
            FROM pegawai p
            LEFT JOIN ruangan r ON p.ruangan_id = r.id
        ";
        if ($activeOnly) {
            $sql .= " WHERE p.is_active = 1";
        }
        $sql .= " ORDER BY p.nama_pegawai ASC";
        return $db->query($sql)->fetchAll();
    }

    public static function getByRuangan($ruanganId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM pegawai 
            WHERE ruangan_id = ? AND is_active = 1 
            ORDER BY nama_pegawai ASC
        ");
        $stmt->execute([$ruanganId]);
        return $stmt->fetchAll();
    }

    public static function find($id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, r.nama_ruangan, r.lokasi_lantai 
            FROM pegawai p
            LEFT JOIN ruangan r ON p.ruangan_id = r.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO pegawai (ruangan_id, nama_pegawai, nip, jabatan, no_hp_wa, email, status_ketersediaan, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            $data['ruangan_id'] ?: null,
            $data['nama_pegawai'],
            !empty($data['nip']) ? $data['nip'] : '-',
            !empty($data['jabatan']) ? $data['jabatan'] : '-',
            !empty($data['no_hp_wa']) ? $data['no_hp_wa'] : '-',
            !empty($data['email']) ? $data['email'] : '-',
            $data['status_ketersediaan'] ?? 'Ada di Tempat',
            $data['is_active'] ?? 1
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update($id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE pegawai 
            SET ruangan_id = ?, nama_pegawai = ?, nip = ?, jabatan = ?, no_hp_wa = ?, email = ?, status_ketersediaan = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['ruangan_id'] ?: null,
            $data['nama_pegawai'],
            !empty($data['nip']) ? $data['nip'] : '-',
            !empty($data['jabatan']) ? $data['jabatan'] : '-',
            !empty($data['no_hp_wa']) ? $data['no_hp_wa'] : '-',
            !empty($data['email']) ? $data['email'] : '-',
            $data['status_ketersediaan'] ?? 'Ada di Tempat',
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    public static function updateStatusKetersediaan($id, $status): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE pegawai 
            SET status_ketersediaan = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$status, $id]);
    }

    public static function delete($id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM pegawai WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Sinkronkan Master Pejabat dari Penanggung Jawab Master Ruangan
     */
    public static function syncFromRuangan(): int {
        $db = Database::getConnection();
        $ruanganList = $db->query("SELECT id, nama_ruangan, penanggung_jawab FROM ruangan WHERE is_active = 1")->fetchAll();
        
        $count = 0;
        foreach ($ruanganList as $r) {
            $namaPJ = trim($r['penanggung_jawab'] ?? '');
            if (empty($namaPJ)) {
                $namaPJ = '-';
            }
            
            // Cek apakah sudah ada pegawai untuk ruangan_id ini
            $stmtCek = $db->prepare("SELECT id FROM pegawai WHERE ruangan_id = ? LIMIT 1");
            $stmtCek->execute([$r['id']]);
            $existing = $stmtCek->fetch();
            
            if ($existing) {
                // Update nama pegawai sesuai penanggung jawab ruangan
                $stmtUpd = $db->prepare("
                    UPDATE pegawai 
                    SET nama_pegawai = ?, 
                        nip = COALESCE(NULLIF(nip, ''), '-'), 
                        jabatan = COALESCE(NULLIF(jabatan, ''), '-'), 
                        no_hp_wa = COALESCE(NULLIF(no_hp_wa, ''), '-'),
                        email = COALESCE(NULLIF(email, ''), '-'),
                        is_active = 1
                    WHERE id = ?
                ");
                $stmtUpd->execute([$namaPJ, $existing['id']]);
            } else {
                // Buat record baru
                $stmtIns = $db->prepare("
                    INSERT INTO pegawai (ruangan_id, nama_pegawai, nip, jabatan, no_hp_wa, email, status_ketersediaan, is_active, created_at)
                    VALUES (?, ?, '-', '-', '-', '-', 'Ada di Tempat', 1, CURRENT_TIMESTAMP)
                ");
                $stmtIns->execute([$r['id'], $namaPJ]);
            }
            $count++;
        }

        // Sinkronkan relasi pegawai_id pada users role ruangan
        $stmtUser = $db->query("SELECT id, ruangan_id FROM users WHERE role = 'ruangan'");
        while ($u = $stmtUser->fetch()) {
            $rId = $u['ruangan_id'];
            $stmtP = $db->prepare("SELECT id FROM pegawai WHERE ruangan_id = ? LIMIT 1");
            $stmtP->execute([$rId]);
            $peg = $stmtP->fetch();
            if ($peg) {
                $db->prepare("UPDATE users SET pegawai_id = ? WHERE id = ?")->execute([$peg['id'], $u['id']]);
            }
        }

        return $count;
    }
}

