<?php
// test_login.php - Test Login Admin
session_start();
require_once 'config/database.php';

$username = 'admin';
$password = 'admin123';

echo "=== TEST LOGIN ADMIN ===<br><br>";

$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user) {
    echo "✅ User ditemukan: " . $user['username'] . "<br>";
    echo "Nama: " . $user['nama_lengkap'] . "<br>";
    echo "Role: " . $user['role'] . "<br><br>";
    
    if (password_verify($password, $user['password'])) {
        echo "✅ PASSWORD COCOK! <br>";
        echo "Login akan berhasil!";
    } else {
        echo "❌ PASSWORD TIDAK COCOK! <br>";
        echo "Password di database: " . $user['password'] . "<br>";
        echo "Password yang dimasukkan: " . $password;
    }
} else {
    echo "❌ User 'admin' tidak ditemukan!";
}
?>