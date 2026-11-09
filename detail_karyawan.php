<?php
$page_title = "Detail Profil Karyawan";
require_once "header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('danger', "ID Karyawan tidak valid!");
    header("Location: index.php");
    exit;
}

// Query detail karyawan dengan JOIN departemen dan jabatan
$sql_karyawan = "SELECT k.*, d.nama_departemen, d.kode_departemen, d.lokasi, j.nama_jabatan, j.gaji_pokok 
                 FROM karyawan k
                 JOIN departemen d ON k.id_departemen = d.id_departemen
                 JOIN jabatan j ON k.id_jabatan = j.id_jabatan
                 WHERE k.id_karyawan = $id";

$q_karyawan = mysql_query($sql_karyawan);
if (!$q_karyawan || mysql_num_rows($q_karyawan) == 0) {
    set_flash('danger', "Data karyawan tidak ditemukan.");
    header("Location: index.php");
    exit;
}

$k = mysql_fetch_array($q_karyawan);

// Query riwayat cuti karyawan ini
$sql_cuti = "SELECT * FROM pengajuan_cuti WHERE id_karyawan = $id ORDER BY tanggal_pengajuan DESC";
$q_cuti = mysql_query($sql_cuti);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">Profil Detail Karyawan</h2>
        <p class="text-muted mb-0">Informasi lengkap biodata dan riwayat pengajuan cuti.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning text-dark">
            <i class="bi bi-pencil me-1"></i> Edit Data
        </a>
        <a href="tambah_cuti.php?id_karyawan=<?php echo $id; ?>" class="btn btn-primary">
            <i class="bi bi-calendar-plus me-1"></i> Ajukan Cuti
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    <?php echo strtoupper(substr($k['nama_lengkap'], 0, 1)); ?>
                </div>
            </div>
            <h4 class="fw-bold mb-1"><?php echo clean($k['nama_lengkap']); ?></h4>
            <div class="text-muted font-monospace mb-2"><?php echo clean($k['nik']); ?></div>
            <span class="badge bg-primary px-3 py-2 mb-3"><?php echo clean($k['nama_jabatan']); ?></span>
            
            <div class="border-top pt-3 text-start small">
                <div class="mb-2"><strong>Departemen:</strong> <?php echo clean($k['nama_departemen']); ?> (<?php echo clean($k['kode_departemen']); ?>)</div>
                <div class="mb-2"><strong>Lokasi Kerja:</strong> <?php echo clean($k['lokasi']); ?></div>
                <div class="mb-2"><strong>Gaji Pokok:</strong> <?php echo format_rupiah($k['gaji_pokok']); ?></div>
                <div class="mb-2"><strong>Status Karyawan:</strong> <?php echo clean($k['status_karyawan']); ?></div>
                <div><strong>Tanggal Masuk:</strong> <?php echo format_tanggal($k['tanggal_masuk']); ?></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold text-primary"><i class="bi bi-person-lines-fill me-2"></i>Biodata Lengkap</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Jenis Kelamin</small>
                        <strong><?php echo ($k['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Tempat, Tanggal Lahir</small>
                        <strong><?php echo clean($k['tempat_lahir']); ?>, <?php echo format_tanggal($k['tanggal_lahir']); ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Email</small>
                        <strong><?php echo clean($k['email']); ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Nomor Telepon</small>
                        <strong><?php echo clean($k['no_telepon']); ?></strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Alamat Tinggal</small>
                        <div><?php echo nl2br(clean($k['alamat'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold text-secondary"><i class="bi bi-clock-history me-2"></i>Riwayat Pengajuan Cuti</h5>
                <span class="badge bg-light text-dark border"><?php echo mysql_num_rows($q_cuti); ?> Pengajuan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pengajuan</th>
                            <th>Jenis Cuti</th>
                            <th>Periode Cuti</th>
                            <th>Durasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysql_num_rows($q_cuti) > 0): ?>
                            <?php while ($c = mysql_fetch_array($q_cuti)): ?>
                            <tr>
                                <td class="font-monospace fw-bold"><?php echo clean($c['nomor_pengajuan']); ?></td>
                                <td><?php echo clean($c['jenis_cuti']); ?></td>
                                <td><?php echo format_tanggal($c['tanggal_mulai']); ?> s/d <?php echo format_tanggal($c['tanggal_selesai']); ?></td>
                                <td><?php echo (int)$c['jumlah_hari']; ?> Hari</td>
                                <td>
                                    <?php 
                                    $st = $c['status'];
                                    $badge = ($st == 'Disetujui') ? 'bg-success' : (($st == 'Menunggu') ? 'bg-warning text-dark' : 'bg-danger');
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo clean($st); ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Karyawan ini belum memiliki riwayat pengajuan cuti.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?>
