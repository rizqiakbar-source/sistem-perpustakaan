<?php
// login_proses.php - Proses Login Admin
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // HAPUS session anggota jika ada
        unset($_SESSION['anggota_id']);
        unset($_SESSION['nis_nim']);
        unset($_SESSION['is_anggota']);
        
        // SET session admin
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];

        header('Location: index.php');
        exit();
    } else {
        header('Location: views/login_admin.php?error=1');
        exit();
    }
} else {
    header('Location: views/login_admin.php');
    exit();
}
?>