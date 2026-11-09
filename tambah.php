<?php
$page_title = "Tambah Karyawan";
require_once "header.php";

$errors = array();

// Inisialisasi nilai awal input
$nik            = '';
$nama_lengkap   = '';
$email          = '';
$jenis_kelamin  = '';
$tempat_lahir   = '';
$tanggal_lahir  = '';
$no_telepon     = '';
$alamat         = '';
$id_departemen  = 0;
$id_jabatan     = 0;
$tanggal_masuk  = date('Y-m-d');
$status_karyawan= 'Tetap';

// Proses saat form disubmit via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitasi dan amankan input (Cegah SQL Injection)
    $nik            = escape($_POST['nik']);
    $nama_lengkap   = escape($_POST['nama_lengkap']);
    $email          = escape($_POST['email']);
    $jenis_kelamin  = escape($_POST['jenis_kelamin']);
    $tempat_lahir   = escape($_POST['tempat_lahir']);
    $tanggal_lahir  = escape($_POST['tanggal_lahir']);
    $no_telepon     = escape($_POST['no_telepon']);
    $alamat         = escape($_POST['alamat']);
    $id_departemen  = (int)$_POST['id_departemen'];
    $id_jabatan     = (int)$_POST['id_jabatan'];
    $tanggal_masuk  = escape($_POST['tanggal_masuk']);
    $status_karyawan= escape($_POST['status_karyawan']);

    // 2. Validasi Wajib: Semua field wajib diisi
    if (empty($nik)) {
        $errors[] = "NIK (Nomor Induk Karyawan) wajib diisi.";
    }
    if (empty($nama_lengkap)) {
        $errors[] = "Nama Lengkap wajib diisi.";
    }
    if (empty($email)) {
        $errors[] = "Alamat Email wajib diisi.";
    }
    if (empty($jenis_kelamin) || !in_array($jenis_kelamin, array('L', 'P'))) {
        $errors[] = "Jenis Kelamin wajib dipilih (Laki-laki atau Perempuan).";
    }
    if (empty($tempat_lahir)) {
        $errors[] = "Tempat Lahir wajib diisi.";
    }
    if (empty($tanggal_lahir)) {
        $errors[] = "Tanggal Lahir wajib diisi.";
    }
    if (empty($no_telepon)) {
        $errors[] = "Nomor Telepon wajib diisi.";
    }
    if (empty($alamat)) {
        $errors[] = "Alamat Lengkap wajib diisi.";
    }
    if ($id_departemen <= 0) {
        $errors[] = "Departemen wajib dipilih dari daftar yang tersedia.";
    }
    if ($id_jabatan <= 0) {
        $errors[] = "Jabatan wajib dipilih dari daftar yang tersedia.";
    }
    if (empty($tanggal_masuk)) {
        $errors[] = "Tanggal Masuk kerja wajib diisi.";
    }
    if (empty($status_karyawan) || !in_array($status_karyawan, array('Tetap', 'Kontrak', 'Magang'))) {
        $errors[] = "Status Karyawan tidak valid.";
    }

    // 3. Validasi Format: Email
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format alamat email tidak valid (contoh: nama@perusahaan.com).";
    }

    // 4. Validasi Format: Nomor Telepon (hanya angka dan karakter standar telp 10-15 digit)
    if (!empty($no_telepon) && !preg_match('/^[0-9+ -]{10,18}$/', $no_telepon)) {
        $errors[] = "Format Nomor Telepon tidak valid. Gunakan angka 10-18 digit.";
    }

    // 5. Validasi Format & Logika: Tanggal Lahir (minimal usia 17 tahun)
    if (!empty($tanggal_lahir)) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_lahir)) {
            $errors[] = "Format Tanggal Lahir harus YYYY-MM-DD.";
        } else {
            $umur = (time() - strtotime($tanggal_lahir)) / (365.25 * 24 * 60 * 60);
            if ($umur < 17) {
                $errors[] = "Usia karyawan minimal harus 17 tahun.";
            }
        }
    }

    // 6. Validasi Format: Tanggal Masuk
    if (!empty($tanggal_masuk)) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_masuk)) {
            $errors[] = "Format Tanggal Masuk harus YYYY-MM-DD.";
        } elseif (!empty($tanggal_lahir) && strtotime($tanggal_masuk) <= strtotime($tanggal_lahir)) {
            $errors[] = "Tanggal Masuk kerja tidak boleh sebelum atau sama dengan Tanggal Lahir.";
        }
    }

    // 7. Validasi Data Unik: NIK tidak boleh ganda
    if (!empty($nik)) {
        $check_nik = mysql_query("SELECT id_karyawan FROM karyawan WHERE nik = '$nik'");
        if (mysql_num_rows($check_nik) > 0) {
            $errors[] = "NIK '{$nik}' sudah terdaftar. Gunakan NIK lain.";
        }
    }

    // 8. Validasi Data Unik: Email tidak boleh ganda
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check_email = mysql_query("SELECT id_karyawan FROM karyawan WHERE email = '$email'");
        if (mysql_num_rows($check_email) > 0) {
            $errors[] = "Email '{$email}' sudah terdaftar untuk karyawan lain.";
        }
    }

    // Jika seluruh validasi lolos, simpan ke database
    if (empty($errors)) {
        $insert_sql = "INSERT INTO karyawan (
                            nik, nama_lengkap, email, jenis_kelamin, 
                            tempat_lahir, tanggal_lahir, no_telepon, alamat, 
                            id_departemen, id_jabatan, tanggal_masuk, status_karyawan
                       ) VALUES (
                            '$nik', '$nama_lengkap', '$email', '$jenis_kelamin', 
                            '$tempat_lahir', '$tanggal_lahir', '$no_telepon', '$alamat', 
                            $id_departemen, $id_jabatan, '$tanggal_masuk', '$status_karyawan'
                       )";

        if (mysql_query($insert_sql)) {
            set_flash('success', "Data karyawan <strong>" . clean($nama_lengkap) . "</strong> (NIK: " . clean($nik) . ") berhasil disimpan!");
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan data ke database: " . mysql_error();
        }
    }
}

// Ambil data untuk dropdown departemen
$q_departemen = mysql_query("SELECT * FROM departemen ORDER BY nama_departemen ASC");

// Ambil data untuk dropdown jabatan
$q_jabatan = mysql_query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1">Tambah Karyawan Baru</h2>
                <p class="text-muted mb-0">Lengkapi formulir di bawah ini dengan data yang valid dan lengkap.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terjadi Kesalahan Pengisian Form:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo clean($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-semibold text-primary">
                    <i class="bi bi-person-lines-fill me-2"></i>Formulir Biodata Karyawan
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="tambah.php" novalidate>
                    <!-- Bagian Identitas Utama -->
                    <h6 class="fw-bold text-secondary text-uppercase mb-3 border-bottom pb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        1. Informasi Identitas
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">NIK (Nomor Induk Karyawan) <span class="text-danger">*</span></label>
                            <input type="text" name="nik" class="form-control" placeholder="Contoh: KRY-2026001" value="<?php echo clean($nik); ?>" required>
                            <div class="form-text">Harus unik, tidak boleh sama dengan karyawan lain.</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap karyawan" value="<?php echo clean($nama_lengkap); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div class="pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" <?php echo ($jenis_kelamin == 'L') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="jk_l">Laki-laki</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" <?php echo ($jenis_kelamin == 'P') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="jk_p">Perempuan</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" name="tempat_lahir" class="form-control" placeholder="Kota kelahiran" value="<?php echo clean($tempat_lahir); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo clean($tanggal_lahir); ?>" required>
                            <div class="form-text">Format: YYYY-MM-DD (Usia minimal 17 tahun).</div>
                        </div>
                    </div>

                    <!-- Bagian Kontak & Alamat -->
                    <h6 class="fw-bold text-secondary text-uppercase mb-3 border-bottom pb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        2. Kontak & Domisili
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Perusahaan/Pribadi <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="nama@perusahaan.com" value="<?php echo clean($email); ?>" required>
                            <div class="form-text">Harus format email valid dan unik.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="no_telepon" class="form-control" placeholder="Contoh: 081234567890" value="<?php echo clean($no_telepon); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, RT/RW, kelurahan, kecamatan, kota..." required><?php echo clean($alamat); ?></textarea>
                        </div>
                    </div>

                    <!-- Bagian Penempatan & Kepegawaian -->
                    <h6 class="fw-bold text-secondary text-uppercase mb-3 border-bottom pb-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        3. Penempatan & Status Kerja
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                            <select name="id_departemen" class="form-select" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php while ($dep = mysql_fetch_array($q_departemen)): ?>
                                    <option value="<?php echo $dep['id_departemen']; ?>" <?php echo ($id_departemen == $dep['id_departemen']) ? 'selected' : ''; ?>>
                                        <?php echo clean($dep['nama_departemen']); ?> (<?php echo clean($dep['kode_departemen']); ?> - <?php echo clean($dep['lokasi']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">Data diambil dinamis dari tabel master <code>departemen</code>.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                            <select name="id_jabatan" class="form-select" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <?php while ($jab = mysql_fetch_array($q_jabatan)): ?>
                                    <option value="<?php echo $jab['id_jabatan']; ?>" <?php echo ($id_jabatan == $jab['id_jabatan']) ? 'selected' : ''; ?>>
                                        <?php echo clean($jab['nama_jabatan']); ?> (Gaji: <?php echo format_rupiah($jab['gaji_pokok']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">Data diambil dinamis dari tabel master <code>jabatan</code>.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Masuk Bekerja <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_masuk" class="form-control" value="<?php echo clean($tanggal_masuk); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Karyawan <span class="text-danger">*</span></label>
                            <select name="status_karyawan" class="form-select" required>
                                <option value="Tetap" <?php echo ($status_karyawan == 'Tetap') ? 'selected' : ''; ?>>Karyawan Tetap</option>
                                <option value="Kontrak" <?php echo ($status_karyawan == 'Kontrak') ? 'selected' : ''; ?>>Karyawan Kontrak</option>
                                <option value="Magang" <?php echo ($status_karyawan == 'Magang') ? 'selected' : ''; ?>>Karyawan Magang (Internship)</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Data Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?>
