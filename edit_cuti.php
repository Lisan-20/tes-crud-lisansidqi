<?php
$page_title = "Edit Transaksi Cuti";
require_once "header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('danger', "ID Pengajuan Cuti tidak valid!");
    header("Location: cuti.php");
    exit;
}

// Ambil data pengajuan cuti saat ini
$q_cuti = mysql_query("SELECT c.*, k.nama_lengkap, k.nik, d.nama_departemen 
                       FROM pengajuan_cuti c
                       JOIN karyawan k ON c.id_karyawan = k.id_karyawan
                       JOIN departemen d ON k.id_departemen = d.id_departemen
                       WHERE c.id_cuti = $id");

if (!$q_cuti || mysql_num_rows($q_cuti) == 0) {
    set_flash('danger', "Data pengajuan cuti #$id tidak ditemukan.");
    header("Location: cuti.php");
    exit;
}

$cuti_data = mysql_fetch_array($q_cuti);
$errors = array();

$nomor_pengajuan   = $cuti_data['nomor_pengajuan'];
$id_karyawan       = (int)$cuti_data['id_karyawan'];
$jenis_cuti        = $cuti_data['jenis_cuti'];
$tanggal_mulai     = $cuti_data['tanggal_mulai'];
$tanggal_selesai   = $cuti_data['tanggal_selesai'];
$jumlah_hari       = (int)$cuti_data['jumlah_hari'];
$keterangan        = $cuti_data['keterangan'];
$tanggal_pengajuan = $cuti_data['tanggal_pengajuan'];
$status            = $cuti_data['status'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitasi input (Aman dari SQL Injection)
    $nomor_pengajuan   = escape($_POST['nomor_pengajuan']);
    $id_karyawan       = (int)$_POST['id_karyawan'];
    $jenis_cuti        = escape($_POST['jenis_cuti']);
    $tanggal_mulai     = escape($_POST['tanggal_mulai']);
    $tanggal_selesai   = escape($_POST['tanggal_selesai']);
    $keterangan        = escape($_POST['keterangan']);
    $tanggal_pengajuan = escape($_POST['tanggal_pengajuan']);
    $status            = escape($_POST['status']);

    // 2. Validasi Wajib
    if (empty($nomor_pengajuan)) {
        $errors[] = "Nomor Pengajuan Cuti wajib diisi.";
    }
    if ($id_karyawan <= 0) {
        $errors[] = "Pilih Karyawan.";
    }
    if (empty($jenis_cuti)) {
        $errors[] = "Jenis Cuti wajib dipilih.";
    }
    if (empty($tanggal_mulai)) {
        $errors[] = "Tanggal Mulai Cuti wajib diisi.";
    }
    if (empty($tanggal_selesai)) {
        $errors[] = "Tanggal Selesai Cuti wajib diisi.";
    }
    if (empty($keterangan)) {
        $errors[] = "Keterangan / Alasan Cuti wajib diisi.";
    }
    if (empty($tanggal_pengajuan)) {
        $errors[] = "Tanggal Pengajuan wajib diisi.";
    }
    if (!in_array($status, array('Menunggu', 'Disetujui', 'Ditolak'))) {
        $status = 'Menunggu';
    }

    // 3. Validasi Format & Logika Tanggal
    if (!empty($tanggal_mulai) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_mulai)) {
        $errors[] = "Format Tanggal Mulai tidak valid.";
    }
    if (!empty($tanggal_selesai) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_selesai)) {
        $errors[] = "Format Tanggal Selesai tidak valid.";
    }

    if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
        $time_mulai   = strtotime($tanggal_mulai);
        $time_selesai = strtotime($tanggal_selesai);

        if ($time_selesai < $time_mulai) {
            $errors[] = "Tanggal Selesai cuti tidak boleh lebih awal dari Tanggal Mulai.";
        } else {
            $jumlah_hari = floor(($time_selesai - $time_mulai) / 86400) + 1;
        }
    }

    // 4. Validasi Unik: Nomor pengajuan tidak dobel
    if (!empty($nomor_pengajuan)) {
        $chk_nomor = mysql_query("SELECT id_cuti FROM pengajuan_cuti WHERE nomor_pengajuan = '$nomor_pengajuan' AND id_cuti != $id");
        if (mysql_num_rows($chk_nomor) > 0) {
            $errors[] = "Nomor Pengajuan Cuti '$nomor_pengajuan' sudah digunakan pada transaksi lain.";
        }
    }

    // Jika validasi sukses, lakukan UPDATE
    if (empty($errors)) {
        $update_sql = "UPDATE pengajuan_cuti SET 
                            nomor_pengajuan = '$nomor_pengajuan',
                            id_karyawan = $id_karyawan,
                            jenis_cuti = '$jenis_cuti',
                            tanggal_mulai = '$tanggal_mulai',
                            tanggal_selesai = '$tanggal_selesai',
                            jumlah_hari = $jumlah_hari,
                            keterangan = '$keterangan',
                            status = '$status',
                            tanggal_pengajuan = '$tanggal_pengajuan'
                       WHERE id_cuti = $id";

        if (mysql_query($update_sql)) {
            set_flash('success', "Transaksi pengajuan cuti <strong>" . clean($nomor_pengajuan) . "</strong> berhasil diperbarui!");
            header("Location: cuti.php");
            exit;
        } else {
            $errors[] = "Gagal memperbarui data pengajuan: " . mysql_error();
        }
    }
}

// Data Karyawan untuk dropdown
$q_dropdown_karyawan = mysql_query("SELECT k.id_karyawan, k.nik, k.nama_lengkap, d.nama_departemen 
                                    FROM karyawan k 
                                    JOIN departemen d ON k.id_departemen = d.id_departemen 
                                    ORDER BY k.nama_lengkap ASC");
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1">Edit Transaksi Cuti</h2>
                <p class="text-muted mb-0">Perbarui data permohonan atau ubah status persetujuan cuti.</p>
            </div>
            <a href="cuti.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Cuti
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi Kesalahan Pengisian:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo clean($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-semibold text-warning text-dark">
                    <i class="bi bi-pencil-square me-2"></i>Formulir Perubahan Transaksi Cuti
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="edit_cuti.php?id=<?php echo $id; ?>" novalidate id="formCuti">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Pengajuan <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_pengajuan" class="form-control font-monospace" value="<?php echo clean($nomor_pengajuan); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengajuan" class="form-control" value="<?php echo clean($tanggal_pengajuan); ?>" required>
                        </div>
                    </div>

                    <!-- Dropdown Karyawan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Karyawan Pemohon <span class="text-danger">*</span></label>
                        <select name="id_karyawan" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Karyawan --</option>
                            <?php while ($kry = mysql_fetch_array($q_dropdown_karyawan)): ?>
                                <option value="<?php echo $kry['id_karyawan']; ?>" <?php echo ($id_karyawan == $kry['id_karyawan']) ? 'selected' : ''; ?>>
                                    <?php echo clean($kry['nama_lengkap']); ?> (NIK: <?php echo clean($kry['nik']); ?> - Dept: <?php echo clean($kry['nama_departemen']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Cuti <span class="text-danger">*</span></label>
                            <select name="jenis_cuti" class="form-select" required>
                                <option value="Cuti Tahunan" <?php echo ($jenis_cuti == 'Cuti Tahunan') ? 'selected' : ''; ?>>Cuti Tahunan</option>
                                <option value="Cuti Sakit" <?php echo ($jenis_cuti == 'Cuti Sakit') ? 'selected' : ''; ?>>Cuti Sakit</option>
                                <option value="Cuti Melahirkan" <?php echo ($jenis_cuti == 'Cuti Melahirkan') ? 'selected' : ''; ?>>Cuti Melahirkan</option>
                                <option value="Cuti Menikah" <?php echo ($jenis_cuti == 'Cuti Menikah') ? 'selected' : ''; ?>>Cuti Menikah</option>
                                <option value="Cuti Alasan Penting" <?php echo ($jenis_cuti == 'Cuti Alasan Penting') ? 'selected' : ''; ?>>Cuti Alasan Penting</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Persetujuan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select fw-bold" required>
                                <option value="Menunggu" class="text-warning" <?php echo ($status == 'Menunggu') ? 'selected' : ''; ?>>⏳ Menunggu Review (Pending)</option>
                                <option value="Disetujui" class="text-success" <?php echo ($status == 'Disetujui') ? 'selected' : ''; ?>>✔ Disetujui (Approved)</option>
                                <option value="Ditolak" class="text-danger" <?php echo ($status == 'Ditolak') ? 'selected' : ''; ?>>✖ Ditolak (Rejected)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 bg-light p-3 rounded-3 border">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Tanggal Mulai Cuti <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="<?php echo clean($tanggal_mulai); ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Tanggal Selesai Cuti <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="<?php echo clean($tanggal_selesai); ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Total Hari</label>
                            <div class="input-group">
                                <input type="text" id="durasi_hari" class="form-control fw-bold text-center bg-white" value="<?php echo (int)$jumlah_hari; ?>" readonly>
                                <span class="input-group-text">Hari</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Keterangan / Alasan Permohonan Cuti <span class="text-danger">*</span></label>
                        <textarea name="keterangan" class="form-control" rows="3" required><?php echo clean($keterangan); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="cuti.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 text-dark fw-bold">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan Cuti
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function hitungHari() {
    var tglMulai = document.getElementById('tanggal_mulai').value;
    var tglSelesai = document.getElementById('tanggal_selesai').value;
    var durasiInput = document.getElementById('durasi_hari');
    
    if (tglMulai && tglSelesai) {
        var start = new Date(tglMulai);
        var end = new Date(tglSelesai);
        var diffTime = end - start;
        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        if (diffDays > 0) {
            durasiInput.value = diffDays;
        } else {
            durasiInput.value = "Error";
        }
    }
}
document.getElementById('tanggal_mulai').addEventListener('change', hitungHari);
document.getElementById('tanggal_selesai').addEventListener('change', hitungHari);
</script>

<?php require_once "footer.php"; ?>
