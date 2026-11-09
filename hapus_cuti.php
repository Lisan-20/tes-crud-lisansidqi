<?php
require_once "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('danger', "ID Pengajuan Cuti tidak valid.");
    header("Location: cuti.php");
    exit;
}

// Periksa keberadaan transaksi cuti
$q_check = mysql_query("SELECT c.nomor_pengajuan, k.nama_lengkap 
                       FROM pengajuan_cuti c
                       JOIN karyawan k ON c.id_karyawan = k.id_karyawan
                       WHERE c.id_cuti = $id");

if (!$q_check || mysql_num_rows($q_check) == 0) {
    set_flash('danger', "Data pengajuan cuti tidak ditemukan.");
    header("Location: cuti.php");
    exit;
}

$cuti = mysql_fetch_array($q_check);
$nomor_cuti = $cuti['nomor_pengajuan'];
$nama = $cuti['nama_lengkap'];

$hapus_sql = "DELETE FROM pengajuan_cuti WHERE id_cuti = $id";
$exec = mysql_query($hapus_sql);

if ($exec) {
    set_flash('success', "Transaksi pengajuan cuti <strong>" . clean($nomor_cuti) . "</strong> atas nama <strong>" . clean($nama) . "</strong> berhasil dihapus.");
} else {
    set_flash('danger', "Gagal menghapus transaksi cuti: " . mysql_error());
}

header("Location: cuti.php");
exit;
?>
