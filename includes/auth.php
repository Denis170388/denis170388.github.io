<?php
// Pastikan file config sudah dipanggil sebelumnya
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}
?>