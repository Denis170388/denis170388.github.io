<?php
// Ganti 'PASSWORD_ANDA_DI_SINI' dengan password baru yang Anda inginkan
$password_baru = 'admin123';

// Proses hashing
$hash = password_hash($password_baru, PASSWORD_DEFAULT);

echo "Password Anda: " . $password_baru . "<br>";
echo "Hash yang dihasilkan (copy ini ke phpMyAdmin):<br>";
echo "<textarea rows='3' cols='80'>" . $hash . "</textarea>";
