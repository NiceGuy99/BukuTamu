<?php
/**
 * Database Handler & Auto Schema Installer
 */

class Database {
    private static ?PDO $pdo = null;
    private static string $driver = 'mysql';

    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/../config/database.php';
        
        // 1. Coba koneksi MySQL
        try {
            $my = $config['mysql'];
            $dsnServer = "mysql:host={$my['host']};port={$my['port']};charset={$my['charset']}";
            $tempPdo = new PDO($dsnServer, $my['username'], $my['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 3
            ]);
            
            // Auto create database db_buku_tamu jika belum ada
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$my['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Sambung ke database db_buku_tamu
            $dsnDb = "mysql:host={$my['host']};port={$my['port']};dbname={$my['database']};charset={$my['charset']}";
            self::$pdo = new PDO($dsnDb, $my['username'], $my['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            self::$driver = 'mysql';
        } catch (Exception $e) {
            // 2. Fallback otomatis ke SQLite jika MySQL tidak aktif
            $sqlitePath = $config['sqlite']['database'];
            $sqliteDir = dirname($sqlitePath);
            if (!is_dir($sqliteDir)) {
                mkdir($sqliteDir, 0777, true);
            }
            self::$pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            self::$driver = 'sqlite';
        }

        // Jalankan auto migration jika tabel belum ada
        self::initSchema();

        return self::$pdo;
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    private static function initSchema(): void {
        $db = self::$pdo;
        $isSqlite = (self::$driver === 'sqlite');

        if ($isSqlite) {
            $db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    nama_lengkap VARCHAR(150) NOT NULL,
                    role VARCHAR(30) NOT NULL, -- 'admin', 'cs', 'ruangan'
                    ruangan_id INTEGER,
                    pegawai_id INTEGER,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS ruangan (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    kode_ruangan VARCHAR(50) UNIQUE NOT NULL,
                    nama_ruangan VARCHAR(150) NOT NULL,
                    lokasi_lantai VARCHAR(100),
                    penanggung_jawab VARCHAR(150),
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS pegawai (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ruangan_id INTEGER,
                    nama_pegawai VARCHAR(150) NOT NULL,
                    nip VARCHAR(50),
                    jabatan VARCHAR(150) NOT NULL,
                    no_hp_wa VARCHAR(30),
                    email VARCHAR(100),
                    status_ketersediaan VARCHAR(50) DEFAULT 'Ada di Tempat',
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS kunjungan (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    kode_kunjungan VARCHAR(50) UNIQUE NOT NULL,
                    nama_tamu VARCHAR(150) NOT NULL,
                    instansi VARCHAR(150) NOT NULL,
                    no_hp VARCHAR(30) NOT NULL,
                    no_identitas VARCHAR(50),
                    jumlah_orang INTEGER DEFAULT 1,
                    ruangan_id INTEGER,
                    pegawai_id INTEGER,
                    keperluan TEXT NOT NULL,
                    status_janji VARCHAR(30) DEFAULT 'belum_janji',
                    jam_janji VARCHAR(20),
                    jam_kedatangan DATETIME NOT NULL,
                    jam_verifikasi_cs DATETIME,
                    jam_ketemu DATETIME,
                    jam_tunda DATETIME,
                    jam_selesai_bertemu DATETIME,
                    durasi_menit INTEGER DEFAULT 0,
                    status VARCHAR(50) DEFAULT 'menunggu_verifikasi_cs',
                    no_badge_kartu VARCHAR(50),
                    catatan_cs TEXT,
                    catatan_ruangan TEXT,
                    alasan_tolak_tunda TEXT,
                    verified_by VARCHAR(100),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE SET NULL,
                    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE SET NULL
                );

                CREATE TABLE IF NOT EXISTS kunjungan_log (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    kunjungan_id INTEGER NOT NULL,
                    status_sebelumnya VARCHAR(50),
                    status_baru VARCHAR(50) NOT NULL,
                    keterangan TEXT,
                    aktor VARCHAR(100),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (kunjungan_id) REFERENCES kunjungan(id) ON DELETE CASCADE
                );
            ");
        } else {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(50) UNIQUE NOT NULL,
                    `password` VARCHAR(255) NOT NULL,
                    `nama_lengkap` VARCHAR(150) NOT NULL,
                    `role` VARCHAR(30) NOT NULL,
                    `ruangan_id` INT NULL,
                    `pegawai_id` INT NULL,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `ruangan` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `kode_ruangan` VARCHAR(50) UNIQUE NOT NULL,
                    `nama_ruangan` VARCHAR(150) NOT NULL,
                    `lokasi_lantai` VARCHAR(100) NULL,
                    `penanggung_jawab` VARCHAR(150) NULL,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `pegawai` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `ruangan_id` INT NULL,
                    `nama_pegawai` VARCHAR(150) NOT NULL,
                    `nip` VARCHAR(50) NULL,
                    `jabatan` VARCHAR(150) NOT NULL,
                    `no_hp_wa` VARCHAR(30) NULL,
                    `email` VARCHAR(100) NULL,
                    `status_ketersediaan` VARCHAR(50) DEFAULT 'Ada di Tempat',
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan`(`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `kunjungan` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `kode_kunjungan` VARCHAR(50) UNIQUE NOT NULL,
                    `nama_tamu` VARCHAR(150) NOT NULL,
                    `instansi` VARCHAR(150) NOT NULL,
                    `no_hp` VARCHAR(30) NOT NULL,
                    `no_identitas` VARCHAR(50) NULL,
                    `jumlah_orang` INT DEFAULT 1,
                    `ruangan_id` INT NULL,
                    `pegawai_id` INT NULL,
                    `keperluan` TEXT NOT NULL,
                    `status_janji` VARCHAR(30) DEFAULT 'belum_janji',
                    `jam_janji` VARCHAR(20) NULL,
                    `jam_kedatangan` DATETIME NOT NULL,
                    `jam_verifikasi_cs` DATETIME NULL,
                    `jam_ketemu` DATETIME NULL,
                    `jam_tunda` DATETIME NULL,
                    `jam_selesai_bertemu` DATETIME NULL,
                    `durasi_menit` INT DEFAULT 0,
                    `status` VARCHAR(50) DEFAULT 'menunggu_verifikasi_cs',
                    `no_badge_kartu` VARCHAR(50) NULL,
                    `catatan_cs` TEXT NULL,
                    `catatan_ruangan` TEXT NULL,
                    `alasan_tolak_tunda` TEXT NULL,
                    `verified_by` VARCHAR(100) NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_status` (`status`),
                    INDEX `idx_tgl_kedatangan` (`jam_kedatangan`),
                    FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan`(`id`) ON DELETE SET NULL,
                    FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS `kunjungan_log` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `kunjungan_id` INT NOT NULL,
                    `status_sebelumnya` VARCHAR(50) NULL,
                    `status_baru` VARCHAR(50) NOT NULL,
                    `keterangan` TEXT NULL,
                    `aktor` VARCHAR(100) NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`kunjungan_id`) REFERENCES `kunjungan`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // Pastikan kolom pegawai_id ada di tabel users
        try {
            if ($isSqlite) {
                $db->exec("ALTER TABLE users ADD COLUMN pegawai_id INTEGER");
            } else {
                $db->exec("ALTER TABLE `users` ADD COLUMN `pegawai_id` INT NULL AFTER `ruangan_id`");
            }
        } catch (Exception $e) {
            // Kolom sudah ada
        }

        // Seed data master awal & users jika masih kosong
        self::seedMasterData();
        self::seedUsers();
    }

    private static function seedMasterData(): void {
        $db = self::$pdo;
        
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM ruangan");
        $row = $stmt->fetch();
        if ($row && $row['cnt'] > 0) {
            return;
        }

        // 1. Seed Master Ruangan / Seksi
        $ruanganData = [
            ['RUG-PNJ', 'Seksi Penunjang', 'Gedung Utama - Lantai 2', 'Bapak Budi Santoso'],
            ['RUG-YMD', 'Seksi Pelayanan Medis (Yanmed)', 'Gedung Utama - Lantai 2', 'dr. Rina Handayani, Sp.A'],
            ['RUG-KPR', 'Seksi Keperawatan', 'Gedung Utama - Lantai 2', 'Ns. Siti Aminah, S.Kep'],
            ['RUG-TU',  'Bagian Tata Usaha & Kepegawaian', 'Gedung Administrasi - Lantai 1', 'H. Ahmad Fauzi, S.E.'],
            ['RUG-DIR', 'Ruang Direktur & Manajemen', 'Gedung Utama - Lantai 3', 'dr. H. Hendra Wijaya, Sp.B'],
            ['RUG-SPR', 'Seksi Sarana & Prasarana (Sarpras)', 'Gedung Penunjang - Lantai 1', 'Ir. Doni Prasetyo'],
            ['RUG-KEU', 'Bagian Keuangan & Akuntansi', 'Gedung Administrasi - Lantai 1', 'Dra. Endang Lestari, M.M.']
        ];

        $stmtRuangan = $db->prepare("INSERT INTO ruangan (kode_ruangan, nama_ruangan, lokasi_lantai, penanggung_jawab, is_active, created_at) VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP)");
        foreach ($ruanganData as $r) {
            $stmtRuangan->execute($r);
        }

        $ruanganMap = [];
        $stmtAllR = $db->query("SELECT id, kode_ruangan FROM ruangan");
        while ($r = $stmtAllR->fetch()) {
            $ruanganMap[$r['kode_ruangan']] = $r['id'];
        }

        // 2. Seed Master Pegawai / Pejabat dari Penanggung Jawab Ruangan
        $stmtAllR = $db->query("SELECT id, penanggung_jawab FROM ruangan ORDER BY id ASC");
        $stmtPegawai = $db->prepare("INSERT INTO pegawai (ruangan_id, nama_pegawai, nip, jabatan, no_hp_wa, email, status_ketersediaan, is_active, created_at) VALUES (?, ?, '-', '-', '-', '-', 'Ada di Tempat', 1, CURRENT_TIMESTAMP)");
        while ($r = $stmtAllR->fetch()) {
            $namaPJ = trim($r['penanggung_jawab'] ?? '') ?: '-';
            $stmtPegawai->execute([$r['id'], $namaPJ]);
        }
    }

    private static function seedUsers(): void {
        $db = self::$pdo;
        
        // Ambil ID Ruangan & Pegawai Bapak B
        $stmtR = $db->query("SELECT id FROM ruangan WHERE nama_ruangan LIKE '%Penunjang%' LIMIT 1");
        $rPenunjang = $stmtR->fetch();
        $ruanganPenunjangId = $rPenunjang['id'] ?? 1;

        $stmtP = $db->query("SELECT id FROM pegawai WHERE nama_pegawai LIKE '%Bapak B%' OR jabatan LIKE '%Penunjang%' LIMIT 1");
        $pBapakB = $stmtP->fetch();
        $pegawaiBId = $pBapakB['id'] ?? 1;

        $stmt = $db->query("SELECT COUNT(*) as cnt FROM users");
        $row = $stmt->fetch();
        if ($row && $row['cnt'] > 0) {
            // Pastikan user penunjang terhubung ke pegawai Bapak B
            $db->exec("UPDATE users SET pegawai_id = $pegawaiBId WHERE username = 'penunjang' AND (pegawai_id IS NULL OR pegawai_id = 0)");
            return;
        }

        // Default Users:
        // 1. Admin: admin / admin123
        // 2. CS: cs / cs123
        // 3. Ruangan Penunjang (Bpk B): penunjang / penunjang123 (konek ke pegawai Bapak B)
        $users = [
            ['admin', password_hash('admin123', PASSWORD_BCRYPT), 'Administrator Sistem', 'admin', null, null],
            ['cs', password_hash('cs123', PASSWORD_BCRYPT), 'Petugas Customer Service (CS Desk)', 'cs', null, null],
            ['penunjang', password_hash('penunjang123', PASSWORD_BCRYPT), 'Bapak B (Kepala Seksi Penunjang)', 'ruangan', $ruanganPenunjangId, $pegawaiBId]
        ];

        $stmtUser = $db->prepare("INSERT INTO users (username, password, nama_lengkap, role, ruangan_id, pegawai_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)");
        foreach ($users as $u) {
            $stmtUser->execute($u);
        }
    }
}

