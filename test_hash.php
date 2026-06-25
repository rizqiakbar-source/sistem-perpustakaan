<?php
// test_hash.php - Cek apakah password sudah terupdate
require_once 'config/database.php';

$username = 'admin';
$password = 'admin123';

$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

echo "<h3>🔍 Verifikasi Password Admin</h3>";

if ($user) {
    echo "✅ User ditemukan: <b>" . $user['username'] . "</b><br>";
    echo "Nama: " . $user['nama_lengkap'] . "<br>";
    echo "Role: " . $user['role'] . "<br><br>";
    
    echo "Password hash di database: <br><code>" . $user['password'] . "</code><br><br>";
    
    // Cek apakah hash adalah bcrypt
    if (strlen($user['password']) == 60 && strpos($user['password'], '$2y$') === 0) {
        echo "✅ Format hash: bcrypt (benar!)<br><br>";
    }
    
    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        echo "✅ <b style='color:green;'>PASSWORD COCOK!</b><br>";
        echo "Login akan berhasil dengan:<br>";
        echo "- Username: <b>admin</b><br>";
        echo "- Password: <b>admin123</b>";
    } else {
        echo "❌ <b style='color:red;'>PASSWORD TIDAK COCOK!</b><br>";
        echo "Ada masalah dengan hash.";
    }
} else {
    echo "❌ User 'admin' tidak ditemukan!";
}
?>