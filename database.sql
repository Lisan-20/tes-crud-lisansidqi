-- ========================================================
-- Database & Schema: SIMPEG (Sistem Informasi Kepegawaian)
-- Lokasi: C:\xampp_php5.6\htdocs\aplikasi_test\database.sql
-- Kompatibel: MySQL 5.5+ / MariaDB 10.1+
-- ========================================================

CREATE DATABASE IF NOT EXISTS `db_kepegawaian` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `db_kepegawaian`;

-- --------------------------------------------------------
-- 1. Tabel Master: Departemen
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pengajuan_cuti`;
DROP TABLE IF EXISTS `karyawan`;
DROP TABLE IF EXISTS `jabatan`;
DROP TABLE IF EXISTS `departemen`;

CREATE TABLE `departemen` (
  `id_departemen` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_departemen` VARCHAR(10) NOT NULL,
  `nama_departemen` VARCHAR(100) NOT NULL,
  `lokasi` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_departemen`),
  UNIQUE KEY `idx_kode_departemen` (`kode_departemen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data Contoh Tabel Departemen (5 data)
INSERT INTO `departemen` (`id_departemen`, `kode_departemen`, `nama_departemen`, `lokasi`) VALUES
(1, 'DEP-IT', 'Information Technology', 'Gedung A - Lantai 3'),
(2, 'DEP-HRD', 'Human Resources Development', 'Gedung A - Lantai 2'),
(3, 'DEP-FIN', 'Finance & Accounting', 'Gedung B - Lantai 1'),
(4, 'DEP-MKT', 'Marketing & Sales', 'Gedung B - Lantai 2'),
(5, 'DEP-OPS', 'Operations & Logistics', 'Gedung C - Lantai 1');

-- --------------------------------------------------------
-- 2. Tabel Master: Jabatan
-- --------------------------------------------------------
CREATE TABLE `jabatan` (
  `id_jabatan` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_jabatan` VARCHAR(100) NOT NULL,
  `gaji_pokok` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id_jabatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data Contoh Tabel Jabatan (5 data)
INSERT INTO `jabatan` (`id_jabatan`, `nama_jabatan`, `gaji_pokok`) VALUES
(1, 'IT Manager', 15000000.00),
(2, 'Senior Software Engineer', 12000000.00),
(3, 'HR Specialist', 8000000.00),
(4, 'Finance Officer', 8500000.00),
(5, 'Digital Marketing Strategist', 7500000.00);

-- --------------------------------------------------------
-- 3. Tabel Utama: Karyawan
-- --------------------------------------------------------
CREATE TABLE `karyawan` (
  `id_karyawan` INT(11) NOT NULL AUTO_INCREMENT,
  `nik` VARCHAR(20) NOT NULL,
  `nama_lengkap` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `jenis_kelamin` ENUM('L','P') NOT NULL,
  `tempat_lahir` VARCHAR(100) NOT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `no_telepon` VARCHAR(20) NOT NULL,
  `alamat` TEXT NOT NULL,
  `id_departemen` INT(11) NOT NULL,
  `id_jabatan` INT(11) NOT NULL,
  `tanggal_masuk` DATE NOT NULL,
  `status_karyawan` ENUM('Tetap','Kontrak','Magang') NOT NULL DEFAULT 'Tetap',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_karyawan`),
  UNIQUE KEY `idx_karyawan_nik` (`nik`),
  UNIQUE KEY `idx_karyawan_email` (`email`),
  KEY `fk_karyawan_departemen` (`id_departemen`),
  KEY `fk_karyawan_jabatan` (`id_jabatan`),
  CONSTRAINT `fk_karyawan_departemen` FOREIGN KEY (`id_departemen`) REFERENCES `departemen` (`id_departemen`) ON UPDATE CASCADE,
  CONSTRAINT `fk_karyawan_jabatan` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id_jabatan`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data Contoh Tabel Karyawan (6 data)
INSERT INTO `karyawan` (`id_karyawan`, `nik`, `nama_lengkap`, `email`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `no_telepon`, `alamat`, `id_departemen`, `id_jabatan`, `tanggal_masuk`, `status_karyawan`) VALUES
(1, 'KRY-2023001', 'Budi Santoso', 'budi.santoso@perusahaan.com', 'L', 'Jakarta', '1990-05-14', '081234567890', 'Jl. Jend. Sudirman No. 12, Jakarta Selatan', 1, 1, '2020-01-15', 'Tetap'),
(2, 'KRY-2023002', 'Siti Rahmawati', 'siti.rahmawati@perusahaan.com', 'P', 'Bandung', '1993-08-22', '081234567891', 'Jl. Dago Atas No. 45, Bandung', 2, 3, '2021-03-01', 'Tetap'),
(3, 'KRY-2023003', 'Ahmad Fauzi', 'ahmad.fauzi@perusahaan.com', 'L', 'Surabaya', '1995-11-10', '081234567892', 'Jl. Pemuda No. 78, Surabaya', 1, 2, '2022-06-10', 'Tetap'),
(4, 'KRY-2023004', 'Dewi Lestari', 'dewi.lestari@perusahaan.com', 'P', 'Yogyakarta', '1996-02-18', '081234567893', 'Jl. Malioboro No. 23, Yogyakarta', 3, 4, '2023-02-01', 'Kontrak'),
(5, 'KRY-2023005', 'Rizky Pratama', 'rizky.pratama@perusahaan.com', 'L', 'Semarang', '1998-07-30', '081234567894', 'Jl. Pandanaran No. 56, Semarang', 4, 5, '2024-01-10', 'Kontrak'),
(6, 'KRY-2023006', 'Nurul Hidayah', 'nurul.hidayah@perusahaan.com', 'P', 'Medan', '1999-12-05', '081234567895', 'Jl. Gatot Subroto No. 89, Medan', 5, 3, '2024-05-02', 'Magang');

-- --------------------------------------------------------
-- 4. Tabel Transaksi: Pengajuan Cuti Karyawan
-- --------------------------------------------------------
CREATE TABLE `pengajuan_cuti` (
  `id_cuti` INT(11) NOT NULL AUTO_INCREMENT,
  `nomor_pengajuan` VARCHAR(30) NOT NULL,
  `id_karyawan` INT(11) NOT NULL,
  `jenis_cuti` ENUM('Cuti Tahunan','Cuti Sakit','Cuti Melahirkan','Cuti Menikah','Cuti Alasan Penting') NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `jumlah_hari` INT(11) NOT NULL,
  `keterangan` TEXT NOT NULL,
  `status` ENUM('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `tanggal_pengajuan` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cuti`),
  UNIQUE KEY `idx_nomor_pengajuan` (`nomor_pengajuan`),
  KEY `fk_cuti_karyawan` (`id_karyawan`),
  CONSTRAINT `fk_cuti_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data Contoh Tabel Transaksi Pengajuan Cuti (5 data)
INSERT INTO `pengajuan_cuti` (`id_cuti`, `nomor_pengajuan`, `id_karyawan`, `jenis_cuti`, `tanggal_mulai`, `tanggal_selesai`, `jumlah_hari`, `keterangan`, `status`, `tanggal_pengajuan`) VALUES
(1, 'CUTI-2026-001', 3, 'Cuti Tahunan', '2026-01-10', '2026-01-12', 3, 'Liburan keluarga ke luar kota', 'Disetujui', '2026-01-02'),
(2, 'CUTI-2026-002', 2, 'Cuti Sakit', '2026-02-05', '2026-02-06', 2, 'Istirahat pemulihan sakit tifus dengan surat dokter', 'Disetujui', '2026-02-05'),
(3, 'CUTI-2026-003', 5, 'Cuti Menikah', '2026-02-20', '2026-02-22', 3, 'Pernikahan pribadi di Solo', 'Disetujui', '2026-02-10'),
(4, 'CUTI-2026-004', 4, 'Cuti Tahunan', '2026-03-15', '2026-03-19', 5, 'Keperluan keluarga di luar daerah', 'Menunggu', '2026-03-01'),
(5, 'CUTI-2026-005', 1, 'Cuti Alasan Penting', '2026-03-25', '2026-03-26', 2, 'Pengurusan dokumen kependudukan keluarga', 'Menunggu', '2026-03-20');
