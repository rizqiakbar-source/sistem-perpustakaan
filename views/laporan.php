<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_admin.php');
    exit();
}

require_once '../config/database.php';

// Statistik untuk laporan
$total_buku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$total_anggota = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
$total_pinjam = $pdo->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
$total_denda = $pdo->query("SELECT COALESCE(SUM(total_denda), 0) FROM pengembalian")->fetchColumn();
$total_kembali = $pdo->query("SELECT COUNT(*) FROM pengembalian")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Laporan - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* ===== SAMA DENGAN STYLE SEBELUMNYA ===== */
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
        .navbar-custom { background: #1a237e !important; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; position: relative; z-index: 9998; }
        body.dark-mode .navbar-custom { background: #0d1b2a !important; }
        .navbar-custom .navbar-brand { font-weight: 700; }
        .card-custom {
            background: var(--bg-card);
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            transition: 0.3s;
        }
        .card-custom .card-title { font-size: 16px; font-weight: 600; color: var(--text-color); margin-bottom: 15px; }
        .stat-card {
            background: var(--bg-card);
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
            border: 1px solid var(--border-color);
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-card .info .number { font-size: 28px; font-weight: 700; color: #1a237e; }
        .stat-card .info .label { font-size: 14px; color: #888; }
        .stat-card .icon { font-size: 36px; opacity: 0.7; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .theme-toggle { background: none; border: none; font-size: 20px; cursor: pointer; padding: 5px 10px; border-radius: 50%; transition: 0.3s; color: #fff; }
        .theme-toggle:hover { background: rgba(255,255,255,0.1); }
        .user-info { display: flex; align-items: center; gap: 15px; color: #fff; }
        .user-info .name { font-weight: 600; font-size: 14px; }
        .user-info .logout { color: #ef9a9a; text-decoration: none; font-weight: 600; }
        .user-info .logout:hover { text-decoration: underline; }
        .preview-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; color: #fff; overflow: hidden; border: 2px solid rgba(255,255,255,0.3); background: #1a237e; }
        .preview-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .dropdown-profile .dropdown-toggle::after { display: none; }
        .dropdown-profile .dropdown-menu { border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); padding: 8px 0; min-width: 200px; border: none; z-index: 99999; }
        .dropdown-profile .dropdown-item { padding: 10px 20px; font-size: 14px; transition: 0.2s; cursor: pointer; }
        .dropdown-profile .dropdown-item:hover { background: #f1f4f9; }
        .dropdown-profile .dropdown-item .icon { margin-right: 10px; }
        .dropdown-profile .dropdown-divider { margin: 6px 0; border-color: #eef2f7; }
        .dropdown-profile .dropdown-item.logout-item { color: #c62828; }
        .dropdown-profile .dropdown-item.logout-item:hover { background: #ffebee; }

        .sidebar-toggle { display: none; background: none; border: none; font-size: 28px; color: #fff; cursor: pointer; padding: 8px 12px; border-radius: 8px; transition: 0.3s; }
        .sidebar-toggle:hover { background: rgba(255,255,255,0.1); }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; }
        @media (max-width: 480px) {
            .sidebar-toggle { display: block !important; }
            .sidebar { width: 280px !important; left: -300px !important; transition: left 0.3s ease !important; z-index: 9999 !important; padding: 20px 0 !important; }
            .sidebar.open { left: 0 !important; }
            .sidebar .brand h3 { display: block !important; font-size: 16px !important; }
            .sidebar .brand small { display: block !important; font-size: 10px !important; }
            .sidebar .menu li span { display: inline !important; font-size: 13px !important; }
            .sidebar .menu li { text-align: left !important; padding: 10px 16px !important; font-size: 13px !important; }
            .sidebar .menu li .icon { margin-right: 12px !important; font-size: 16px !important; }
            .sidebar-overlay.open { display: block !important; }
            .main-content { margin-left: 0 !important; padding: 10px !important; }
            .navbar-custom { padding: 8px 12px !important; }
            .navbar-custom .navbar-brand { font-size: 14px !important; }
            .user-info .name { font-size: 12px !important; }
            .preview-avatar { width: 30px !important; height: 30px !important; font-size: 14px !important; }
            .card-custom { padding: 12px 14px !important; margin-bottom: 12px !important; }
            .card-custom .card-title { font-size: 14px !important; }
            .stats-grid { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
            .stat-card { padding: 12px !important; }
            .stat-card .info .number { font-size: 18px !important; }
            .stat-card .info .label { font-size: 11px !important; }
            .stat-card .icon { font-size: 24px !important; }
            .dropdown-profile .dropdown-menu { min-width: 160px !important; }
        }
        @media (min-width: 481px) and (max-width: 768px) {
            .sidebar-toggle { display: block !important; }
            .sidebar { width: 280px !important; left: -300px !important; transition: left 0.3s ease !important; z-index: 9999 !important; }
            .sidebar.open { left: 0 !important; }
            .sidebar .brand h3 { display: block !important; }
            .sidebar .brand small { display: block !important; }
            .sidebar .menu li span { display: inline !important; }
            .sidebar .menu li { text-align: left !important; padding: 10px 16px !important; }
            .sidebar .menu li .icon { margin-right: 12px !important; }
            .sidebar-overlay.open { display: block !important; }
            .main-content { margin-left: 0 !important; padding: 15px !important; }
            .stats-grid { grid-template-columns: 1fr 1fr !important; gap: 15px !important; }
            .stat-card { padding: 15px !important; }
            .stat-card .info .number { font-size: 22px !important; }
        }
        @media (min-width: 769px) {
            .sidebar-toggle { display: none !important; }
            .sidebar-overlay { display: none !important; }
            .sidebar { left: 0 !important; width: 240px !important; }
            .main-content { margin-left: 240px !important; }
        }
    </style>
</head>
<body id="body">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<?php include 'sidebar_admin.php'; ?>

<div class="main-content">

    <nav class="navbar navbar-dark navbar-custom">
        <div class="container-fluid">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                ☰
            </button>
            <span class="navbar-brand">📚 SISTEM PERPUSTAKAAN</span>
            <div class="user-info">
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="dropdown dropdown-profile">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="preview-avatar">
                            <?php 
                            $user_id = $_SESSION['user_id'];
                            $foto_path = '../upload/foto_admin/' . $user_id . '.jpg';
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
                                <small style="color: #888; font-size: 12px;"><?= htmlspecialchars($_SESSION['username']) ?></small>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="profil_admin.php"><span class="icon">👤</span> My Profile</a></li>
                        <li><a class="dropdown-item" href="ubah_foto_admin.php"><span class="icon">📷</span> Ubah Foto</a></li>
                        <li><a class="dropdown-item" href="ubah_password_admin.php"><span class="icon">🔒</span> Ubah Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item logout-item" href="../logout.php"><span class="icon">🚪</span> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="card-custom">
        <div class="card-title"><span class="icon">📊</span> Laporan Statistik</div>
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="info">
                    <div class="number"><?= $total_buku ?></div>
                    <div class="label">Total Buku</div>
                </div>
                <div class="icon">📖</div>
            </div>
            <div class="stat-card green">
                <div class="info">
                    <div class="number"><?= $total_anggota ?></div>
                    <div class="label">Total Anggota</div>
                </div>
                <div class="icon">👤</div>
            </div>
            <div class="stat-card orange">
                <div class="info">
                    <div class="number"><?= $total_pinjam ?></div>
                    <div class="label">Total Peminjaman</div>
                </div>
                <div class="icon">📝</div>
            </div>
            <div class="stat-card red">
                <div class="info">
                    <div class="number">Rp <?= number_format($total_denda, 0, ',', '.') ?></div>
                    <div class="label">Total Denda</div>
                </div>
                <div class="icon">💰</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="info">
                        <div class="number"><?= $total_kembali ?></div>
                        <div class="label">Total Pengembalian</div>
                    </div>
                    <div class="icon">↩️</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="info">
                        <div class="number"><?= $total_pinjam - $total_kembali ?></div>
                        <div class="label">Buku Belum Dikembalikan</div>
                    </div>
                    <div class="icon">📋</div>
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
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebar = document.querySelector('.sidebar');

    function toggleSidebar() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('open');
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('open');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
</script>

</body>
</html>