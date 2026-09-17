<?php
/**
 * Model Kunjungan Tamu
 * Mengelola seluruh transaksi kunjungan dan tracking waktu otomatis:
 * - jam_kedatangan (saat tamu submit form)
 * - jam_verifikasi_cs (saat CS verifikasi)
 * - jam_ketemu (saat ruangan terima / jam tunda yang diset)
 * - jam_tunda (jam penundaan yang diset ruangan)
 * - jam_selesai_bertemu (saat CS selesaikan kunjungan)
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

class Kunjungan {

    /**
     * Ambil data kunjungan dengan join master ruangan dan pegawai
     */
    public static function getBaseQuery(): string {
        return "
            SELECT k.*, 
                   r.nama_ruangan, r.lokasi_lantai, r.kode_ruangan,
                   p.nama_pegawai, p.jabatan, p.nip, p.no_hp_wa as no_hp_pegawai
            FROM kunjungan k
            LEFT JOIN ruangan r ON k.ruangan_id = r.id
            LEFT JOIN pegawai p ON k.pegawai_id = p.id
        ";
    }

    /**
     * Ambil kunjungan berdasarkan ID
     */
    public static function find($id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare(self::getBaseQuery() . " WHERE k.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ambil kunjungan berdasarkan Kode Kunjungan
     */
    public static function findByKode($kode): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare(self::getBaseQuery() . " WHERE k.kode_kunjungan = ?");
        $stmt->execute([trim($kode)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ambil semua kunjungan hari ini
     */
    public static function getHariIni($ruanganId = null, $status = null): array {
        $db = Database::getConnection();
        $today = date('Y-m-d');
        
        $sql = self::getBaseQuery() . " WHERE DATE(k.jam_kedatangan) = ?";
        $params = [$today];

        if ($ruanganId) {
            $sql .= " AND k.ruangan_id = ?";
            $params[] = $ruanganId;
        }

        if ($status) {
            $sql .= " AND k.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY k.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ambil daftar kunjungan aktif (belum selesai & tidak ditolak)
     */
    public static function getAntrianAktif($ruanganId = null): array {
        $db = Database::getConnection();
        $today = date('Y-m-d');
        
        $sql = self::getBaseQuery() . " 
            WHERE DATE(k.jam_kedatangan) = ? 
            AND k.status IN ('menunggu_verifikasi_cs', 'menunggu_acc_ruangan', 'sedang_bertemu', 'ditunda')
        ";
        $params = [$today];

        if ($ruanganId) {
            $sql .= " AND k.ruangan_id = ?";
            $params[] = $ruanganId;
        }

        $sql .= " ORDER BY 
            CASE k.status
                WHEN 'menunggu_verifikasi_cs' THEN 1
                WHEN 'menunggu_acc_ruangan' THEN 2
                WHEN 'ditunda' THEN 3
                WHEN 'sedang_bertemu' THEN 4
                ELSE 5
            END, k.id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 1. TAMU SIMPAN FORM -> Otomatis mencatat jam_kedatangan
     */
    public static function create(array $data): ?array {
        $db = Database::getConnection();
        $kodeKunjungan = generateKodeKunjungan($db);
        $jamKedatangan = date('Y-m-d H:i:s'); // Jam saat simpan

        $stmt = $db->prepare("
            INSERT INTO kunjungan (
                kode_kunjungan, nama_tamu, instansi, no_hp, no_identitas, jumlah_orang,
                ruangan_id, pegawai_id, keperluan, status_janji, jam_janji,
                jam_kedatangan, status, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, 'menunggu_verifikasi_cs', CURRENT_TIMESTAMP
            )
        ");

        $stmt->execute([
            $kodeKunjungan,
            $data['nama_tamu'],
            $data['instansi'],
            $data['no_hp'],
            $data['no_identitas'] ?? null,
            (int)($data['jumlah_orang'] ?? 1),
            $data['ruangan_id'] ?: null,
            $data['pegawai_id'] ?: null,
            $data['keperluan'],
            $data['status_janji'] ?? 'belum_janji',
            $data['jam_janji'] ?? null,
            $jamKedatangan
        ]);

        $kunjunganId = (int)$db->lastInsertId();

        // Tambah Audit Log
        self::addLog($kunjunganId, null, 'menunggu_verifikasi_cs', 'Tamu mengisi formulir pendaftaran. Jam kedatangan tercatat otomatis.', $data['nama_tamu']);

        return self::find($kunjunganId);
    }

    /**
     * 2. CS VERIFIKASI -> Otomatis mencatat jam_verifikasi_cs & teruskan ke Ruangan
     */
    public static function verifikasiCS($id, $verifiedBy = 'Customer Service', $noBadge = null, $catatan = null): bool {
        $db = Database::getConnection();
        $jamVerif = date('Y-m-d H:i:s');

        $curr = self::find($id);
        if (!$curr) return false;

        $stmt = $db->prepare("
            UPDATE kunjungan 
            SET status = 'menunggu_acc_ruangan',
                jam_verifikasi_cs = ?,
                verified_by = ?,
                no_badge_kartu = COALESCE(?, no_badge_kartu),
                catatan_cs = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $result = $stmt->execute([$jamVerif, $verifiedBy, $noBadge, $catatan, $id]);

        if ($result) {
            self::addLog($id, $curr['status'], 'menunggu_acc_ruangan', "Diverifikasi oleh CS ($verifiedBy). Diteruskan ke Pejabat/Ruangan tujuan.", $verifiedBy);
        }

        return $result;
    }

    /**
     * CS TOLAK KUNJUNGAN
     */
    public static function tolakCS($id, $alasan = '', $aktor = 'Customer Service'): bool {
        $db = Database::getConnection();
        $curr = self::find($id);
        if (!$curr) return false;

        $stmt = $db->prepare("
            UPDATE kunjungan 
            SET status = 'ditolak_cs',
                alasan_tolak_tunda = ?,
                verified_by = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $result = $stmt->execute([$alasan, $aktor, $id]);

        if ($result) {
            self::addLog($id, $curr['status'], 'ditolak_cs', "Kunjungan ditolak oleh CS. Alasan: $alasan", $aktor);
        }

        return $result;
    }

    /**
     * 3A. RUANGAN / PEJABAT MENERIMA (ACC) -> Otomatis mencatat jam_ketemu = saat menerima
     */
    public static function terimaRuangan($id, $catatanRuangan = null, $aktor = 'Pejabat/Ruangan'): bool {
        $db = Database::getConnection();
        $jamKetemu = date('Y-m-d H:i:s'); // Jam saat menerima

        $curr = self::find($id);
        if (!$curr) return false;

        $stmt = $db->prepare("
            UPDATE kunjungan 
            SET status = 'sedang_bertemu',
                jam_ketemu = ?,
                catatan_ruangan = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $result = $stmt->execute([$jamKetemu, $catatanRuangan, $id]);

        if ($result) {
            self::addLog($id, $curr['status'], 'sedang_bertemu', "Kunjungan DITERIMA oleh Ruangan/Pejabat. Jam ketemu tercatat otomatis: " . date('H:i:s', strtotime($jamKetemu)), $aktor);
        }

        return $result;
    }

    /**
     * 3B. RUANGAN / PEJABAT MENOLAK
     */
    public static function tolakRuangan($id, $alasan = '', $aktor = 'Pejabat/Ruangan'): bool {
        $db = Database::getConnection();
        $curr = self::find($id);
        if (!$curr) return false;

        $stmt = $db->prepare("
            UPDATE kunjungan 
            SET status = 'ditolak_ruangan',
                alasan_tolak_tunda = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $result = $stmt->execute([$alasan, $id]);

        if ($result) {
            self::addLog($id, $curr['status'], 'ditolak_ruangan', "Kunjungan DITOLAK oleh Ruangan/Pejabat. Alasan: $alasan", $aktor);
        }

        return $result;
    }

    /**
     * 3C. RUANGAN / PEJABAT MENUNDA -> Set jam tunda, jam_ketemu menyesuaikan jam yang diset
     */
    public static function tundaRuangan($id, $jamTundaInput, $alasan = '', $aktor = 'Pejabat/Ruangan'): bool {
        $db = Database::getConnection();
        $curr = self::find($id);
        if (!$curr) return false;

        // Buat format datetime lengkap untuk jam tunda (hari ini + jam tunda)
        $tglHariIni = date('Y-m-d');
        if (strlen($jamTundaInput) <= 5) {
            $jamTundaDatetime = "$tglHariIni $jamTundaInput:00";
        } else {
            $jamTundaDatetime = $jamTundaInput;
        }

        // Jam ketemu menyesuaikan jam yang diset
        $stmt = $db->prepare("
            UPDATE kunjungan 
            SET status = 'ditunda',
                jam_tunda = ?,
                jam_ketemu = ?,
                alasan_tolak_tunda = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $result = $stmt->execute([$jamTundaDatetime, $jamTundaDatetime, $alasan, $id]);

        if ($result) {
            $jamText = date('H:i', strtotime($jamTundaDatetime)) . ' WIB';
            self::addLog($id, $curr['status'], 'ditunda', "Kunjungan DITUNDA sampai jam $jamText. Jam temu disesuaikan. Alasan: $alasan", $aktor);
        }

        return $result;
    }

    /**
     * 4. TAMU LAPOR KEMBALI KE CS & CS SELESAIKAN -> Otomatis mencatat jam_selesai_bertemu & hitung durasi
     */
    public static function selesaiCS($id, $catatan = null, $aktor = 'Customer Service'): bool {
        $db = Database::getConnection();
        $jamSelesai = date('Y-m-d H:i:s'); // Jam selesai bertemu

        $curr = self::find($id);
        if (!$curr) return false;

        // Hitung durasi dalam menit
        $jamMulai = $curr['jam_ketemu'] ?: ($curr['jam_verifikasi_cs'] ?: $curr['jam_kedatangan']);
        $start = strtotime($jamMulai);
        $end = strtotime($jamSelesai);
        $durasiMenit = max(1, (int)round(($end - $start) / 60));

        $stmt = $db->prepare("
            UPDATE kunjungan 
            SET status = 'selesai',
                jam_selesai_bertemu = ?,
                durasi_menit = ?,
                catatan_cs = CASE WHEN ? IS NOT NULL THEN ? ELSE catatan_cs END,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $result = $stmt->execute([$jamSelesai, $durasiMenit, $catatan, $catatan, $id]);

        if ($result) {
            $durasiTeks = hitungDurasiTeks($jamMulai, $jamSelesai);
            self::addLog($id, $curr['status'], 'selesai', "Kunjungan DISELESAIKAN oleh CS ($aktor). Jam selesai otomatis dicatat: " . date('H:i:s', strtotime($jamSelesai)) . " (Total Durasi: $durasiTeks).", $aktor);
        }

        return $result;
    }

    /**
     * Tambah Audit Log
     */
    public static function addLog($kunjunganId, $statusSebelumnya, $statusBaru, $keterangan, $aktor = null): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO kunjungan_log (kunjungan_id, status_sebelumnya, status_baru, keterangan, aktor, created_at)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$kunjunganId, $statusSebelumnya, $statusBaru, $keterangan, $aktor]);
    }

    /**
     * Ambil Riwayat Log Kunjungan
     */
    public static function getLogs($kunjunganId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM kunjungan_log WHERE kunjungan_id = ? ORDER BY id ASC");
        $stmt->execute([$kunjunganId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil Rekapitulasi Laporan dengan Filter
     */
    public static function getLaporan(array $filters = []): array {
        $db = Database::getConnection();
        $sql = self::getBaseQuery() . " WHERE 1=1";
        $params = [];

        if (!empty($filters['tgl_awal'])) {
            $sql .= " AND DATE(k.jam_kedatangan) >= ?";
            $params[] = $filters['tgl_awal'];
        }

        if (!empty($filters['tgl_akhir'])) {
            $sql .= " AND DATE(k.jam_kedatangan) <= ?";
            $params[] = $filters['tgl_akhir'];
        }

        if (!empty($filters['ruangan_id'])) {
            $sql .= " AND k.ruangan_id = ?";
            $params[] = $filters['ruangan_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND k.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (k.nama_tamu LIKE ? OR k.instansi LIKE ? OR k.kode_kunjungan LIKE ? OR k.no_hp LIKE ?)";
            $keyword = '%' . $filters['search'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $sql .= " ORDER BY k.jam_kedatangan DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ambil Statistik Ringkas Kunjungan
     */
    public static function getStatistik($tglAwal = null, $tglAkhir = null): array {
        $db = Database::getConnection();
        $tglAwal = $tglAwal ?: date('Y-m-d');
        $tglAkhir = $tglAkhir ?: date('Y-m-d');

        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_kunjungan,
                SUM(CASE WHEN status = 'menunggu_verifikasi_cs' THEN 1 ELSE 0 END) as menunggu_cs,
                SUM(CASE WHEN status = 'menunggu_acc_ruangan' THEN 1 ELSE 0 END) as menunggu_ruangan,
                SUM(CASE WHEN status = 'sedang_bertemu' THEN 1 ELSE 0 END) as sedang_bertemu,
                SUM(CASE WHEN status = 'ditunda' THEN 1 ELSE 0 END) as ditunda,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status LIKE 'ditolak%' THEN 1 ELSE 0 END) as ditolak,
                AVG(CASE WHEN status = 'selesai' AND durasi_menit > 0 THEN durasi_menit ELSE NULL END) as rata_durasi_menit
            FROM kunjungan
            WHERE DATE(jam_kedatangan) BETWEEN ? AND ?
        ");
        $stmt->execute([$tglAwal, $tglAkhir]);
        return $stmt->fetch() ?: [];
    }
}
