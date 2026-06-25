<?php
// views/profil_anggota.php - Profil Anggota
session_start();
if (!isset($_SESSION['is_anggota']) || $_SESSION['is_anggota'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';

$anggota_id = $_SESSION['anggota_id'];

$sql = "SELECT * FROM anggota WHERE anggota_id = :anggota_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$anggota = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            z-index: 9999;
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
            margin-bottom: 20px;
            position: relative;
            z-index: 9998;
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
            cursor: pointer;
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
        .dropdown-profile .dropdown-toggle::after { display: none; }
        .dropdown-profile .dropdown-menu {
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            padding: 8px 0;
            min-width: 200px;
            border: none;
            z-index: 99999;
        }
        .dropdown-profile .dropdown-item {
            padding: 10px 20px;
            font-size: 14px;
            transition: 0.2s;
            cursor: pointer;
        }
        .dropdown-profile .dropdown-item:hover { background: #f1f4f9; }
        .dropdown-profile .dropdown-item .icon { margin-right: 10px; }
        .dropdown-profile .dropdown-divider { margin: 6px 0; border-color: #eef2f7; }
        .dropdown-profile .dropdown-item.logout-item { color: #c62828; }
        .dropdown-profile .dropdown-item.logout-item:hover { background: #ffebee; }
    </style>
</head>
<body id="body">

<?php include 'sidebar_anggota.php'; ?>

<div class="main-content">

    <!-- ===== NAVBAR DENGAN DROPDOWN PROFIL ===== -->
    <nav class="navbar navbar-dark navbar-custom">
        <div class="container-fluid">
            <span class="navbar-brand">📚 SISTEM PERPUSTAKAAN</span>
            <div class="user-info">
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
                
                <!-- ===== DROPDOWN PROFIL ===== -->
                <div class="dropdown dropdown-profile">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="preview-avatar">
                            <?php 
                            $foto_path = '../upload/foto_anggota/' . $anggota_id . '.jpg';
                            if (file_exists($foto_path)): ?>
                                <img src="<?= $foto_path ?>" alt="Foto Profil">
                            <?php else: ?>
                                <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div style="padding: 10px 20px; border-bottom: 1px solid #eef2f7;">
                                <strong><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong><br>
                                <small style="color: #888; font-size: 12px;"><?= htmlspecialchars($_SESSION['nis_nim']) ?></small>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="profil_anggota.php"><span class="icon">👤</span> My Profile</a></li>
                        <li><a class="dropdown-item" href="ubah_foto_anggota.php"><span class="icon">📷</span> Ubah Foto</a></li>
                        <li><a class="dropdown-item" href="ubah_password_anggota.php"><span class="icon">🔒</span> Ubah Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item logout-item" href="logout_anggota.php"><span class="icon">🚪</span> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="top-header">
        <div class="page-title">
            <h2>👤 Profil Saya</h2>
            <small>Data diri Anda</small>
        </div>
        <div>
            <a href="dashboard_anggota.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
        </div>
    </div>

    <div class="profile-card">
        <div class="card-custom">
            <div class="text-center">
                <div class="profile-avatar">
                    <?php 
                    $foto_path = '../upload/foto_anggota/' . $anggota_id . '.jpg';
                    if (file_exists($foto_path)): ?>
                        <img src="<?= $foto_path ?>" alt="Foto Profil">
                    <?php else: ?>
                        <?= strtoupper(substr($anggota['nama_lengkap'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <h4><?= htmlspecialchars($anggota['nama_lengkap']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($anggota['nis_nim']) ?></p>
                <a href="ubah_foto_anggota.php" class="btn btn-primary btn-sm">📷 Ubah Foto</a>
            </div>

            <hr>

            <div class="info-row">
                <div class="label">NIS / NIM</div>
                <div class="value"><?= htmlspecialchars($anggota['nis_nim']) ?></div>
            </div>
            <div class="info-row">
                <div class="label">Nama Lengkap</div>
                <div class="value"><?= htmlspecialchars($anggota['nama_lengkap']) ?></div>
            </div>
            <div class="info-row">
                <div class="label">Email</div>
                <div class="value"><?= htmlspecialchars($anggota['email']) ?></div>
            </div>
            <div class="info-row">
                <div class="label">No. Telepon</div>
                <div class="value"><?= htmlspecialchars($anggota['no_telepon'] ?? '-') ?></div>
            </div>
            <div class="info-row">
                <div class="label">Alamat</div>
                <div class="value"><?= htmlspecialchars($anggota['alamat'] ?? '-') ?></div>
            </div>
            <div class="info-row">
                <div class="label">Jenis Kelamin</div>
                <div class="value"><?= $anggota['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></div>
            </div>
            <div class="info-row">
                <div class="label">Tanggal Daftar</div>
                <div class="value"><?= date('d/m/Y', strtotime($anggota['tanggal_daftar'])) ?></div>
            </div>
            <div class="info-row">
                <div class="label">Status</div>
                <div class="value">
                    <span class="badge-status success"><?= ucfirst($anggota['status_aktif']) ?></span>
                </div>
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