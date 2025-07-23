<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIVIJANDA - <?php echo ucfirst($_SESSION['role']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <?php echo $_SESSION['role'] == 'admin' ? 'Panel Admin SIVIJANDA' : 'Panel Desa SIVIJANDA'; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nama_lengkap']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-body">
        <aside class="sidebar">
            <ul class="nav flex-column">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="bi bi-house-door-fill me-2"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dokumen_masuk.php') ? 'active' : ''; ?>" href="dokumen_masuk.php">
                            <i class="bi bi-inbox-fill me-2"></i> Dokumen Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'verifikasi_dokumen.php') ? 'active' : ''; ?>" href="verifikasi_dokumen.php">
                            <i class="bi bi-patch-check-fill me-2"></i> Verifikasi Dokumen
                        </a>
                    </li>
                <?php elseif ($_SESSION['role'] == 'desa'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="bi bi-house-door-fill me-2"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'upload.php') ? 'active' : ''; ?>" href="upload.php">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Upload Dokumen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'dokumen_saya.php') ? 'active' : ''; ?>" href="dokumen_saya.php">
                            <i class="bi bi-file-earmark-text-fill me-2"></i> Daftar Dokumen
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </aside>

        <main class="main-content">