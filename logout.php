<?php
// Memanggil file konfigurasi yang akan memulai sesi
require_once 'includes/config.php';

// 1. Menghapus semua variabel sesi
$_SESSION = array();

// 2. Menghancurkan sesi di server
session_destroy();

// 3. Mengarahkan pengguna kembali ke halaman login
header('Location: login.php');

// 4. Memastikan tidak ada kode lain yang dieksekusi setelah pengalihan
exit();
