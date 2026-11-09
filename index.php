<?php
$page_title = "Daftar Karyawan";
require_once "header.php";

// Menangkap parameter pencarian & filter (Aman dari SQL Injection)
$keyword = isset($_GET['keyword']) ? escape($_GET['keyword']) : '';
$filter_departemen = isset($_GET['id_departemen']) ? (int)$_GET['id_departemen'] : 0;
$filter_status = isset($_GET['status']) ? escape($_GET['status']) : '';

// Query Statistik Ringkasan untuk Card Widget
$q_total = mysql_query("SELECT COUNT(*) AS total FROM karyawan");
$total_karyawan = ($r = mysql_fetch_array($q_total)) ? $r['total'] : 0;

$q_tetap = mysql_query("SELECT COUNT(*) AS total FROM karyawan WHERE status_karyawan = 'Tetap'");
$total_tetap = ($r = mysql_fetch_array($q_tetap)) ? $r['total'] : 0;

$q_kontrak = mysql_query("SELECT COUNT(*) AS total FROM karyawan WHERE status_karyawan = 'Kontrak'");
$total_kontrak = ($r = mysql_fetch_array($q_kontrak)) ? $r['total'] : 0;

$q_dep = mysql_query("SELECT COUNT(*) AS total FROM departemen");
$total_departemen = ($r = mysql_fetch_array($q_dep)) ? $r['total'] : 0;

// Membangun Query Utama dengan JOIN tabel departemen dan jabatan
$where_clauses = array();

if (!empty($keyword)) {
    $where_clauses[] = "(k.nik LIKE '%$keyword%' OR k.nama_lengkap LIKE '%$keyword%' OR k.email LIKE '%$keyword%')";
}
if ($filter_departemen > 0) {
    $where_clauses[] = "k.id_departemen = $filter_departemen";
}
if (!empty($filter_status)) {
    $where_clauses[] = "k.status_karyawan = '$filter_status'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Eksekusi query JOIN data karyawan
$sql = "SELECT k.*, d.nama_departemen, d.kode_departemen, j.nama_jabatan, j.gaji_pokok 
        FROM karyawan k
        JOIN departemen d ON k.id_departemen = d.id_departemen
        JOIN jabatan j ON k.id_jabatan = j.id_jabatan
        $where_sql
        ORDER BY k.id_karyawan DESC";

$result = mysql_query($sql);
if (!$result) {
    die("<div class='alert alert-danger'>Query Error: " . mysql_error() . "</div>");
}

// Data Departemen untuk Dropdown Filter
$q_dropdown_dep = mysql_query("SELECT * FROM departemen ORDER BY nama_departemen ASC");
?>

<!-- Header Halaman & Aksi Cepat -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h3 fw-bold mb-1">Manajemen Data Karyawan</h2>
        <p class="text-muted mb-0">Kelola informasi data karyawan, departemen, jabatan, dan riwayat cuti.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="cuti.php" class="btn btn-outline-primary">
            <i class="bi bi-calendar-event me-1"></i> Data Cuti
        </a>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Karyawan Baru
        </a>
    </div>
</div>

<!-- Ringkasan Metrik (Added Value) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Karyawan</div>
                    <div class="fs-4 fw-bold"><?php echo $total_karyawan; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-person-check-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Karyawan Tetap</div>
                    <div class="fs-4 fw-bold"><?php echo $total_tetap; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning me-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Karyawan Kontrak</div>
                    <div class="fs-4 fw-bold"><?php echo $total_kontrak; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-building fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small">Departemen</div>
                    <div class="fs-4 fw-bold"><?php echo $total_departemen; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Pencarian & Filter (Added Value) -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari NIK, Nama, atau Email..." value="<?php echo clean($keyword); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="id_departemen" class="form-select">
                    <option value="">-- Semua Departemen --</option>
                    <?php while ($d = mysql_fetch_array($q_dropdown_dep)): ?>
                        <option value="<?php echo $d['id_departemen']; ?>" <?php echo ($filter_departemen == $d['id_departemen']) ? 'selected' : ''; ?>>
                            <?php echo clean($d['nama_departemen']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Tetap" <?php echo ($filter_status == 'Tetap') ? 'selected' : ''; ?>>Tetap</option>
                    <option value="Kontrak" <?php echo ($filter_status == 'Kontrak') ? 'selected' : ''; ?>>Kontrak</option>
                    <option value="Magang" <?php echo ($filter_status == 'Magang') ? 'selected' : ''; ?>>Magang</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <?php if (!empty($keyword) || $filter_departemen > 0 || !empty($filter_status)): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Data Karyawan -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold text-secondary">
            <i class="bi bi-table me-2 text-primary"></i>Daftar Karyawan Aktif
        </h5>
        <span class="badge bg-light text-dark border">
            Menampilkan <?php echo mysql_num_rows($result); ?> Data
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>NIK</th>
                    <th>Nama & Email</th>
                    <th>L/P</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th>Gaji Pokok</th>
                    <th>No. Telepon</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysql_num_rows($result) > 0):
                    while ($row = mysql_fetch_array($result)): 
                        // Badge status
                        $badge_status = 'bg-secondary';
                        if ($row['status_karyawan'] == 'Tetap') $badge_status = 'bg-success';
                        elseif ($row['status_karyawan'] == 'Kontrak') $badge_status = 'bg-warning text-dark';
                        elseif ($row['status_karyawan'] == 'Magang') $badge_status = 'bg-info text-dark';
                ?>
                <tr>
                    <td class="text-center text-muted"><?php echo $no++; ?></td>
                    <td>
                        <span class="badge bg-light text-primary border font-monospace"><?php echo clean($row['nik']); ?></span>
                    </td>
                    <td>
                        <a href="detail_karyawan.php?id=<?php echo (int)$row['id_karyawan']; ?>" class="fw-bold text-decoration-none text-dark hover-primary" title="Lihat Profil">
                            <?php echo clean($row['nama_lengkap']); ?> <i class="bi bi-box-arrow-up-right small text-muted"></i>
                        </a>
                        <div class="small text-muted"><?php echo clean($row['email']); ?></div>
                    </td>
                    <td>
                        <?php if ($row['jenis_kelamin'] == 'L'): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="Laki-laki">L</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Perempuan">P</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-semibold"><?php echo clean($row['nama_departemen']); ?></div>
                        <small class="text-muted"><?php echo clean($row['kode_departemen']); ?></small>
                    </td>
                    <td><?php echo clean($row['nama_jabatan']); ?></td>
                    <td class="font-monospace text-nowrap"><?php echo format_rupiah($row['gaji_pokok']); ?></td>
                    <td><?php echo clean($row['no_telepon']); ?></td>
                    <td>
                        <span class="badge <?php echo $badge_status; ?>"><?php echo clean($row['status_karyawan']); ?></span>
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="edit.php?id=<?php echo (int)$row['id_karyawan']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Edit Data">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <!-- Konfirmasi JavaScript confirm() -->
                        <a href="hapus.php?id=<?php echo (int)$row['id_karyawan']; ?>" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus data karyawan: \n<?php echo clean($row['nama_lengkap']); ?> (NIK: <?php echo clean($row['nik']); ?>)?\n\nSemua transaksi pengajuan cuti yang terkait juga akan dihapus.');" 
                           class="btn btn-sm btn-outline-danger" 
                           title="Hapus Data">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <p class="mb-2">Tidak ada data karyawan yang ditemukan.</p>
                            <?php if (!empty($keyword) || $filter_departemen > 0 || !empty($filter_status)): ?>
                                <a href="index.php" class="btn btn-sm btn-outline-primary">Reset Filter Pencarian</a>
                            <?php else: ?>
                                <a href="tambah.php" class="btn btn-sm btn-primary">Tambah Data Karyawan Sekarang</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "footer.php"; ?>
