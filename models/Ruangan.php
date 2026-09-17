<?php
/**
 * Model Ruangan
 */

require_once __DIR__ . '/Database.php';

class Ruangan {
    public static function getAll($activeOnly = true): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM ruangan";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY nama_ruangan ASC";
        return $db->query($sql)->fetchAll();
    }

    public static function find($id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ruangan WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO ruangan (kode_ruangan, nama_ruangan, lokasi_lantai, penanggung_jawab, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            $data['kode_ruangan'],
            $data['nama_ruangan'],
            $data['lokasi_lantai'] ?? null,
            $data['penanggung_jawab'] ?? null,
            $data['is_active'] ?? 1
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update($id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE ruangan 
            SET kode_ruangan = ?, nama_ruangan = ?, lokasi_lantai = ?, penanggung_jawab = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['kode_ruangan'],
            $data['nama_ruangan'],
            $data['lokasi_lantai'] ?? null,
            $data['penanggung_jawab'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    public static function delete($id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM ruangan WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
