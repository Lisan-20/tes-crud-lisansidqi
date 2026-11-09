<?php
require_once "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    set_flash('danger', "ID Karyawan tidak valid.");
    header("Location: index.php");
    exit;
}

// Periksa apakah data karyawan ada di database
$q_check = mysql_query("SELECT nama_lengkap, nik FROM karyawan WHERE id_karyawan = $id");
if (!$q_check || mysql_num_rows($q_check) == 0) {
    set_flash('danger', "Data karyawan dengan ID #$id tidak ditemukan.");
    header("Location: index.php");
    exit;
}

$karyawan = mysql_fetch_array($q_check);
$nama_karyawan = $karyawan['nama_lengkap'];
$nik_karyawan = $karyawan['nik'];

// Jika request dipicu langsung (misalnya via link yang sudah dikonfirmasi JS confirm di index.php atau form POST)
$hapus_sql = "DELETE FROM karyawan WHERE id_karyawan = $id";
$exec = mysql_query($hapus_sql);

if ($exec) {
    set_flash('success', "Data karyawan <strong>" . clean($nama_karyawan) . "</strong> (NIK: " . clean($nik_karyawan) . ") dan riwayat cuti terkait berhasil dihapus.");
} else {
    set_flash('danger', "Gagal menghapus data karyawan: " . mysql_error());
}

header("Location: index.php");
exit;
?>
