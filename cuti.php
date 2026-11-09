<?php
$page_title = "Daftar Pengajuan Cuti Karyawan";
require_once "header.php";

// Menangkap parameter filter rentang tanggal dan status (Aman dari SQL Injection)
$tgl_mulai   = isset($_GET['tgl_mulai']) ? escape($_GET['tgl_mulai']) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? escape($_GET['tgl_selesai']) : '';
$filter_status = isset($_GET['status']) ? escape($_GET['status']) : '';
$keyword     = isset($_GET['keyword']) ? escape($_GET['keyword']) : '';

// Membangun kondisi WHERE untuk filter
$where = array();

if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    // Filter rentang tanggal pengajuan cuti
    $where[] = "(c.tanggal_mulai BETWEEN '$tgl_mulai' AND '$tgl_selesai' OR c.tanggal_pengajuan BETWEEN '$tgl_mulai' AND '$tgl_selesai')";
} elseif (!empty($tgl_mulai)) {
    $where[] = "c.tanggal_mulai >= '$tgl_mulai'";
} elseif (!empty($tgl_selesai)) {
    $where[] = "c.tanggal_selesai <= '$tgl_selesai'";
}

if (!empty($filter_status)) {
    $where[] = "c.status = '$filter_status'";
}

if (!empty($keyword)) {
    $where[] = "(c.nomor_pengajuan LIKE '%$keyword%' OR k.nama_lengkap LIKE '%$keyword%' OR k.nik LIKE '%$keyword%')";
}

$where_clause = "";
if (count($where) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where);
}

// 1. Query Utama dengan JOIN minimal 2 tabel (pengajuan_cuti JOIN karyawan JOIN departemen JOIN jabatan)
$sql = "SELECT c.*, k.nik, k.nama_lengkap, d.nama_departemen, j.nama_jabatan
        FROM pengajuan_cuti c
        JOIN karyawan k ON c.id_karyawan = k.id_karyawan
        JOIN departemen d ON k.id_departemen = d.id_departemen
        JOIN jabatan j ON k.id_jabatan = j.id_jabatan
        $where_clause
        ORDER BY c.tanggal_pengajuan DESC, c.id_cuti DESC";

$result = mysql_query($sql);
if (!$result) {
    die("<div class='alert alert-danger'>Query Error: " . mysql_error() . "</div>");
}

// 2. Query Rekapitulasi & Total Nilai Transaksi (Sesuai Syarat Spesifikasi)
// Total transaksi keseluruhan dan berdasarkan filter
$sql_rekap = "SELECT 
                COUNT(*) AS total_transaksi,
                COALESCE(SUM(c.jumlah_hari), 0) AS total_hari_cuti,
                COUNT(CASE WHEN c.status = 'Disetujui' THEN 1 END) AS total_disetujui,
                COALESCE(SUM(CASE WHEN c.status = 'Disetujui' THEN c.jumlah_hari ELSE 0 END), 0) AS hari_disetujui,
                COUNT(CASE WHEN c.status = 'Menunggu' THEN 1 END) AS total_menunggu,
                COALESCE(SUM(CASE WHEN c.status = 'Menunggu' THEN c.jumlah_hari ELSE 0 END), 0) AS hari_menunggu,
                COUNT(CASE WHEN c.status = 'Ditolak' THEN 1 END) AS total_ditolak
              FROM pengajuan_cuti c
              JOIN karyawan k ON c.id_karyawan = k.id_karyawan
              $where_clause";

$q_rekap = mysql_query($sql_rekap);
$rekap = mysql_fetch_array($q_rekap);
?>

<!-- Header Transaksi Cuti -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h3 fw-bold mb-1">Transaksi Pengajuan Cuti</h2>
        <p class="text-muted mb-0">Kelola dan pantau permohonan cuti seluruh karyawan perusahaan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-people me-1"></i> Data Karyawan
        </a>
        <a href="tambah_cuti.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Ajukan Cuti Baru
        </a>
    </div>
</div>

<!-- Rekapitulasi / Total Transaksi (Wajib di Spesifikasi) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary me-3">
                        <i class="bi bi-journal-text fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Pengajuan</div>
                        <div class="fs-4 fw-bold"><?php echo (int)$rekap['total_transaksi']; ?> <small class="fs-6 fw-normal text-muted">transaksi</small></div>
                    </div>
                </div>
                <div class="mt-2 text-muted small border-top pt-2">
                    Total durasi: <strong><?php echo (int)$rekap['total_hari_cuti']; ?> hari</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success me-3">
                        <i class="bi bi-check2-circle fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cuti Disetujui</div>
                        <div class="fs-4 fw-bold text-success"><?php echo (int)$rekap['total_disetujui']; ?></div>
                    </div>
                </div>
                <div class="mt-2 text-muted small border-top pt-2">
                    Durasi: <strong><?php echo (int)$rekap['hari_disetujui']; ?> hari</strong> terpakai
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning me-3">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Menunggu Review</div>
                        <div class="fs-4 fw-bold text-warning"><?php echo (int)$rekap['total_menunggu']; ?></div>
                    </div>
                </div>
                <div class="mt-2 text-muted small border-top pt-2">
                    Durasi: <strong><?php echo (int)$rekap['hari_menunggu']; ?> hari</strong> pending
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3 text-danger me-3">
                        <i class="bi bi-x-circle fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cuti Ditolak</div>
                        <div class="fs-4 fw-bold text-danger"><?php echo (int)$rekap['total_ditolak']; ?></div>
                    </div>
                </div>
                <div class="mt-2 text-muted small border-top pt-2">
                    Permohonan tidak disetujui
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Filter Rentang Tanggal (Wajib di Spesifikasi) -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="cuti.php" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal (Mulai):</label>
                <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?php echo clean($tgl_mulai); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal (Selesai):</label>
                <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?php echo clean($tgl_selesai); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status Cuti:</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="Menunggu" <?php echo ($filter_status == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Disetujui" <?php echo ($filter_status == 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="Ditolak" <?php echo ($filter_status == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Kata Kunci:</label>
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="No. Cuti / Karyawan" value="<?php echo clean($keyword); ?>">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <?php if (!empty($tgl_mulai) || !empty($tgl_selesai) || !empty($filter_status) || !empty($keyword)): ?>
                    <a href="cuti.php" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Daftar Transaksi Cuti -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold text-secondary">
            <i class="bi bi-calendar2-range me-2 text-primary"></i>Daftar Pengajuan Cuti
        </h5>
        <span class="badge bg-light text-dark border">
            Ditemukan <?php echo mysql_num_rows($result); ?> Transaksi
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>No. Pengajuan</th>
                    <th>Tgl Pengajuan</th>
                    <th>Nama Karyawan</th>
                    <th>Departemen / Jabatan</th>
                    <th>Jenis Cuti</th>
                    <th>Periode Tanggal</th>
                    <th class="text-center">Durasi</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $subtotal_hari = 0;
                if (mysql_num_rows($result) > 0):
                    while ($row = mysql_fetch_array($result)): 
                        $subtotal_hari += (int)$row['jumlah_hari'];

                        // Status Badge
                        $badge_class = 'bg-secondary';
                        $icon_status = 'bi-question-circle';
                        if ($row['status'] == 'Disetujui') {
                            $badge_class = 'bg-success';
                            $icon_status = 'bi-check-circle';
                        } elseif ($row['status'] == 'Menunggu') {
                            $badge_class = 'bg-warning text-dark';
                            $icon_status = 'bi-clock-history';
                        } elseif ($row['status'] == 'Ditolak') {
                            $badge_class = 'bg-danger';
                            $icon_status = 'bi-x-circle';
                        }
                ?>
                <tr>
                    <td class="text-center text-muted"><?php echo $no++; ?></td>
                    <td>
                        <span class="badge bg-light text-dark border font-monospace"><?php echo clean($row['nomor_pengajuan']); ?></span>
                    </td>
                    <td class="text-nowrap small text-muted"><?php echo format_tanggal($row['tanggal_pengajuan']); ?></td>
                    <td>
                        <div class="fw-bold"><?php echo clean($row['nama_lengkap']); ?></div>
                        <small class="text-muted font-monospace"><?php echo clean($row['nik']); ?></small>
                    </td>
                    <td>
                        <div class="small fw-semibold"><?php echo clean($row['nama_departemen']); ?></div>
                        <small class="text-muted"><?php echo clean($row['nama_jabatan']); ?></small>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?php echo clean($row['jenis_cuti']); ?>
                        </span>
                    </td>
                    <td class="text-nowrap small">
                        <div><strong>Mulai:</strong> <?php echo format_tanggal($row['tanggal_mulai']); ?></div>
                        <div><strong>Sampai:</strong> <?php echo format_tanggal($row['tanggal_selesai']); ?></div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-dark bg-opacity-75 fs-6"><?php echo (int)$row['jumlah_hari']; ?> Hari</span>
                    </td>
                    <td>
                        <small class="text-secondary"><?php echo clean($row['keterangan']); ?></small>
                    </td>
                    <td>
                        <span class="badge <?php echo $badge_class; ?>">
                            <i class="bi <?php echo $icon_status; ?> me-1"></i><?php echo clean($row['status']); ?>
                        </span>
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="edit_cuti.php?id=<?php echo (int)$row['id_cuti']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Ubah Pengajuan / Status">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="hapus_cuti.php?id=<?php echo (int)$row['id_cuti']; ?>" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi pengajuan cuti <?php echo clean($row['nomor_pengajuan']); ?> atas nama <?php echo clean($row['nama_lengkap']); ?>?');"
                           class="btn btn-sm btn-outline-danger" 
                           title="Hapus Pengajuan">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            <p class="mb-2">Tidak ada data transaksi cuti yang sesuai dengan kriteria.</p>
                            <a href="tambah_cuti.php" class="btn btn-sm btn-primary">Ajukan Cuti Baru</a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
            <!-- Baris Rekapitulasi di Bawah Tabel (Wajib di Spesifikasi) -->
            <tfoot class="table-light border-top">
                <tr>
                    <th colspan="7" class="text-end fw-bold">TOTAL REKAPITULASI PADA TABEL INI:</th>
                    <th class="text-center fw-bold text-primary font-monospace fs-6">
                        <?php echo $subtotal_hari; ?> Hari
                    </th>
                    <th colspan="3" class="text-muted small">
                        (Total <?php echo ($no - 1); ?> transaksi pengajuan cuti)
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require_once "footer.php"; ?>
