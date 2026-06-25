<?php
// views/login_anggota_proses.php - Proses Login Anggota
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis_nim = $_POST['nis_nim'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM anggota WHERE nis_nim = :nis_nim AND status_aktif = 'aktif'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nis_nim' => $nis_nim]);
    $anggota = $stmt->fetch();

    if ($anggota && password_verify($password, $anggota['password'])) {
        // HAPUS session admin jika ada
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        
        // SET session anggota
        $_SESSION['anggota_id'] = $anggota['anggota_id'];
        $_SESSION['nis_nim'] = $anggota['nis_nim'];
        $_SESSION['nama_lengkap'] = $anggota['nama_lengkap'];
        $_SESSION['is_anggota'] = true;

        header('Location: dashboard_anggota.php');
        exit();
    } else {
        header('Location: login.php?error=1');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>