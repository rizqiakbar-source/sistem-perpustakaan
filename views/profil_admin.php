<?php
// views/profil_admin.php - Profil Admin
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --bg-body: #f1f4f9;
            --bg-card: #ffffff;
            --text-color: #333;
            --border-color: #eef2f7;
            --shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        body.dark-mode {
            --bg-body: #1a1a2e;
            --bg-card: #16213e;
            --text-color: #e8e8e8;
            --border-color: #2a2a4a;
            --shadow: 0 2px 12px rgba(0,0,0,0.3);
        }
        body {
            background: var(--bg-body);
            color: var(--text-color);
            transition: 0.3s;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100%;
            background: #1a237e;
            color: #fff;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
        }
        body.dark-mode .sidebar { background: #0d1b2a; }
        .sidebar .brand { text-align: center; padding: 10px 0 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar .brand h3 { font-size: 18px; font-weight: 700; }
        .sidebar .brand small { font-size: 11px; opacity: 0.7; }
        .sidebar .menu { list-style: none; padding: 0 15px; }
        .sidebar .menu li { padding: 12px 16px; margin: 4px 0; border-radius: 10px; font-size: 14px; cursor: pointer; transition: 0.3s; color: rgba(255,255,255,0.7); }
        .sidebar .menu li:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar .menu li.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 600; }
        .sidebar .menu li .icon { margin-right: 12px; }
        .main-content { margin-left: 240px; padding: 20px 30px; min-height: 100vh; }
        @media (max-width: 768px) {
            .sidebar { width: 60px; padding: 10px 0; }
            .sidebar .brand h3 { display: none; }
            .sidebar .brand small { display: none; }
            .sidebar .menu li span { display: none; }
            .sidebar .menu li { text-align: center; padding: 12px 0; }
            .main-content { margin-left: 60px; padding: 15px; }
            .top-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
        .navbar-custom {
            background: #1a237e !important;
            padding: 12px 20px;
            border-radius: 10px;
        }
        body.dark-mode .navbar-custom { background: #0d1b2a !important; }
        .navbar-custom .navbar-brand { font-weight: 700; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #fff;
        }
        .user-info .name { font-weight: 600; font-size: 14px; }
        .user-info .role { font-size: 12px; opacity: 0.8; }
        .user-info .logout { color: #ef9a9a; text-decoration: none; font-weight: 600; }
        .user-info .logout:hover { text-decoration: underline; }
        .preview-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: #fff;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.3);
            background: #1a237e;
        }
        .preview-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .theme-toggle {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 50%;
            transition: 0.3s;
            color: #fff;
        }
        .theme-toggle:hover { background: rgba(255,255,255,0.1); }
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0 25px 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 25px;
        }
        .top-header .page-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a237e;
        }
        body.dark-mode .top-header .page-title h2 { color: #90caf9; }
        .top-header .page-title small { font-size: 13px; color: #888; }
        .card-custom {
            background: var(--bg-card);
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            transition: 0.3s;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #1a237e;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            margin: 0 auto 15px;
            overflow: hidden;
            border: 4px solid var(--border-color);
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-card { max-width: 600px; margin: 0 auto; }
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .info-row .label { width: 150px; font-weight: 600; color: #555; }
        .info-row .value { flex: 1; color: var(--text-color); }
        .badge-status {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-status.success { background: #e8f5e9; color: #2e7d32; }
        body.dark-mode .badge-status.success { background: #1b5e20; color: #a5d6a7; }
    </style>
</head>
<body id="body">

<div class="sidebar">
    <div class="brand">
        <h3>📚 PERPUSTAKAAN</h3>
        <small>Politeknik Negeri Lampung</small>
    </div>
    <ul class="menu">
        <li onclick="location.href='../index.php'"><span class="icon">📊</span> <span>Dashboard</span></li>
        <li onclick="location.href='buku/index.php'"><span class="icon">📚</span> <span>Buku</span></li>
        <li onclick="location.href='anggota/index.php'"><span class="icon">👤</span> <span>Anggota</span></li>
        <li onclick="location.href='peminjaman/index.php'"><span class="icon">📝</span> <span>Peminjaman</span></li>
        <li onclick="location.href='pengembalian/index.php'"><span class="icon">↩️</span> <span>Pengembalian</span></li>
        <li onclick="location.href='laporan/index.php'"><span class="icon">📊</span> <span>Laporan</span></li>
    </ul>
</div>

<div class="main-content">

    <nav class="navbar navbar-dark navbar-custom">
        <div class="container-fluid">
            <span class="navbar-brand">📚 SISTEM PERPUSTAKAAN</span>
            <div class="user-info">
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="preview-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?></div>
                <div>
                    <div class="name"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                    <div class="role"><?= $_SESSION['role'] ?></div>
                </div>
                <a href="../logout.php" class="logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="top-header">
        <div class="page-title">
            <h2>👤 Profil Admin</h2>
            <small>Data diri Anda</small>
        </div>
        <div>
            <a href="../index.php" class="btn btn-secondary btn-sm">⬅ Kembali ke Dashboard</a>
        </div>
    </div>

    <div class="profile-card">
        <div class="card-custom">
            <div class="text-center">
                <div class="profile-avatar">
                    <?php 
                    $foto_path = '../upload/foto_admin/' . $user_id . '.jpg';
                    if (file_exists($foto_path)): ?>
                        <img src="<?= $foto_path ?>" alt="Foto Profil">
                    <?php else: ?>
                        <?= strtoupper(substr($admin['nama_lengkap'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <h4><?= htmlspecialchars($admin['nama_lengkap']) ?></h4>
                <p class="text-muted">@<?= htmlspecialchars($admin['username']) ?></p>
                <a href="ubah_foto_admin.php" class="btn btn-primary btn-sm">📷 Ubah Foto</a>
                <a href="ubah_password_admin.php" class="btn btn-warning btn-sm ms-2">🔒 Ubah Password</a>
                <a href="ubah_username_admin.php" class="btn btn-info btn-sm ms-2">✏️ Ubah Username</a>
            </div>

            <hr>

            <div class="info-row">
                <div class="label">Username</div>
                <div class="value"><?= htmlspecialchars($admin['username']) ?></div>
            </div>
            <div class="info-row">
                <div class="label">Nama Lengkap</div>
                <div class="value"><?= htmlspecialchars($admin['nama_lengkap']) ?></div>
            </div>
            <div class="info-row">
                <div class="label">Role</div>
                <div class="value">
                    <span class="badge-status success"><?= ucfirst($admin['role']) ?></span>
                </div>
            </div>
            <div class="info-row">
                <div class="label">Bergabung Sejak</div>
                <div class="value"><?= date('d/m/Y H:i', strtotime($admin['created_at'])) ?></div>
            </div>
        </div>
    </div>

    <div style="text-align:center; color:#aaa; font-size:12px; padding:20px 0 10px 0; border-top:1px solid var(--border-color); margin-top:20px;">
        &copy; 2026 Politeknik Negeri Lampung
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/darkmode.js"></script>

</body>
</html>