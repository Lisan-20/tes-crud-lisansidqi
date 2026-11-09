<?php
/**
 * Konfigurasi Koneksi Database SIMPEG
 * Menggunakan ekstensi mysql_* sesuai spesifikasi teknis PHP 5.6
 */

// Sembunyikan pesan deprecation E_DEPRECATED khusus PHP 5.6 agar tampilan UI tetap bersih
error_reporting(E_ALL ^ E_DEPRECATED);

if (session_id() == '') {
    session_start();
}

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "db_kepegawaian";

// 1. Membuka koneksi menggunakan mysql_connect
$koneksi = @mysql_connect($db_host, $db_user, $db_pass);

if (!$koneksi) {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>
            <h3>Gagal Terhubung ke Database MySQL!</h3>
            <p><strong>Error:</strong> " . mysql_error() . "</p>
            <p>Pastikan layanan MySQL di XAMPP Control Panel sudah dalam keadaan <strong>Running (Aktif)</strong>.</p>
         </div>");
}

// 2. Memilih database menggunakan mysql_select_db
$db_terpilih = @mysql_select_db($db_name, $koneksi);

if (!$db_terpilih) {
    // Jika database belum ada, berikan instruksi ramah
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 5px; margin: 20px;'>
            <h3>Database '$db_name' Belum Ditemukan!</h3>
            <p>Silakan buat database terlebih dahulu di <strong>phpMyAdmin</strong> atau import file <code>database.sql</code> yang tersedia di dalam folder aplikasi ini.</p>
            <p><a href='/phpmyadmin' target='_blank' style='display:inline-block; padding:8px 15px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:4px;'>Buka phpMyAdmin</a></p>
         </div>");
}

// Set charset UTF-8 menggunakan mysql_query
mysql_query("SET NAMES 'utf8'", $koneksi);

/**
 * Fungsi Pengamanan SQL Injection
 * Membersihkan input string sebelum masuk ke query database
 */
function escape($str) {
    global $koneksi;
    if (is_null($str)) {
        return '';
    }
    return mysql_real_escape_string(trim($str), $koneksi);
}

/**
 * Fungsi Pengamanan XSS (Cross Site Scripting)
 * Mengamankan output sebelum ditampilkan ke browser
 */
function clean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Format Rupiah untuk gaji/biaya
 */
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Format Tanggal Indonesia (contoh: 15 Januari 2026)
 */
function format_tanggal($tgl) {
    if (!$tgl || $tgl == '0000-00-00') return '-';
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecah = explode('-', $tgl);
    if (count($pecah) == 3) {
        $b = (int)$pecah[1];
        $nama_b = isset($bulan[$b]) ? $bulan[$b] : $pecah[1];
        return $pecah[2] . ' ' . $nama_b . ' ' . $pecah[0];
    }
    return $tgl;
}

/**
 * Helper Flash Message (Notifikasi antar halaman)
 */
function set_flash($tipe, $pesan) {
    $_SESSION['flash'] = array('tipe' => $tipe, 'pesan' => $pesan);
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $tipe = $_SESSION['flash']['tipe'];
        $pesan = $_SESSION['flash']['pesan'];
        unset($_SESSION['flash']);

        $alert_class = ($tipe == 'success') ? 'alert-success' : (($tipe == 'danger') ? 'alert-danger' : 'alert-warning');
        $icon = ($tipe == 'success') ? 'bi-check-circle-fill' : (($tipe == 'danger') ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');

        echo "<div class='alert {$alert_class} alert-dismissible fade show d-flex align-items-center shadow-sm' role='alert'>
                <i class='bi {$icon} me-2 fs-5'></i>
                <div>{$pesan}</div>
                <button type='button' class='btn-close ms-auto' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}
?>
