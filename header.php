<?php
require_once "koneksi.php";

// Mendapatkan nama file saat ini untuk penanda menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? clean($page_title) . " - " : ""; ?>SIMPEG | Sistem Informasi Kepegawaian</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bs-primary: #1e40af;
            --bs-primary-rgb: 30, 64, 175;
        }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .table > :not(caption) > * > * {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
        }
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
        }
        .badge {
            font-weight: 600;
            padding: 0.4em 0.75em;
            border-radius: 0.375rem;
        }
        .content-wrapper {
            flex: 1 0 auto;
        }
        .footer {
            flex-shrink: 0;
            background-color: #ffffff;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="bi bi-person-badge me-2 fs-4"></i>
            <span>SIMPEG <small class="fw-normal opacity-75 fs-6">| Kepegawaian</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active fw-bold' : ''; ?>" href="index.php">
                        <i class="bi bi-people me-1"></i> Data Karyawan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'tambah.php') ? 'active fw-bold' : ''; ?>" href="tambah.php">
                        <i class="bi bi-person-plus me-1"></i> Tambah Karyawan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'cuti.php') ? 'active fw-bold' : ''; ?>" href="cuti.php">
                        <i class="bi bi-calendar2-check me-1"></i> Pengajuan Cuti
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'tambah_cuti.php') ? 'active fw-bold' : ''; ?>" href="tambah_cuti.php">
                        <i class="bi bi-calendar-plus me-1"></i> Ajukan Cuti Baru
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-white-50 hover-light" href="/phpmyadmin" target="_blank" title="Buka Database di phpMyAdmin">
                        <i class="bi bi-database-gear me-1"></i> phpMyAdmin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="content-wrapper py-4">
    <div class="container">
        <?php display_flash(); ?>
