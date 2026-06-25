<?php
// views/test_anggota.php
require_once '../config/database.php';

$nis_nim = '2023001';
$password_input = 'anggota123';

echo "=== TEST LOGIN ANGGOTA ===<br><br>";

// Cek apakah NIS/NIM ada
$sql = "SELECT * FROM anggota WHERE nis_nim = :nis_nim";
$stmt = $pdo->prepare($sql);
$stmt->execute(['nis_nim' => $nis_nim]);
$anggota = $stmt->fetch();

if ($anggota) {
    echo "✅ Anggota ditemukan: " . $anggota['nama_lengkap'] . "<br>";
    echo "NIS/NIM: " . $anggota['nis_nim'] . "<br>";
    echo "Password di database: " . $anggota['password'] . "<br><br>";
    
    // Test password_verify
    if (password_verify($password_input, $anggota['password'])) {
        echo "✅ PASSWORD COCOK! <br>";
        echo "Login akan berhasil!";
    } else {
        echo "❌ PASSWORD TIDAK COCOK! <br>";
        echo "Password yang dimasukkan: " . $password_input . "<br>";
        
        // Test dengan MD5
        echo "<br>--- Test dengan MD5 ---<br>";
        echo "MD5('anggota123') = " . md5('anggota123') . "<br>";
        echo "Password di database: " . $anggota['password'] . "<br>";
        if (md5($password_input) == $anggota['password']) {
            echo "✅ MD5 COCOK! (gunakan md5)";
        } else {
            echo "❌ MD5 TIDAK COCOK!";
        }
    }
} else {
    echo "❌ Anggota dengan NIS/NIM $nis_nim tidak ditemukan!";
}
?>