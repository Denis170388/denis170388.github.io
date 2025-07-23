<?php
require_once 'includes/config.php';

// Cek apakah user sudah login
if (isset($_SESSION['user_id'])) {
    // Arahkan berdasarkan role
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: desa/dashboard.php');
    }
} else {
    // Jika belum login, arahkan ke halaman login
    header('Location: login.php');
}
exit();
