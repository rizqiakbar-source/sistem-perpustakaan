<?php
// index.php - Dashboard Admin (LENGKAP DENGAN DROPDOWN PROFIL + PIE CHART)

session_start();

// ===== CEK SESSION ADMIN =====
if (!isset($_SESSION['user_id'])) {
    header('Location: views/login_admin.php');  // ← DIPERBAIKI
    exit();
}

require_once 'config/database.php';

// ===== STATISTIK =====
$total_buku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();
$total_anggota = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
$total_pinjam = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam'")->fetchColumn();
$total_denda = $pdo->query("SELECT COALESCE(SUM(total_denda), 0) FROM pengembalian")->fetchColumn();

// Buku baru bulan ini
$bulan_ini = date('Y-m');
$buku_baru = $pdo->query("SELECT COUNT(*) FROM buku WHERE DATE_FORMAT(created_at, '%Y-%m') = '$bulan_ini'")->fetchColumn();

// Anggota baru bulan ini
$anggota_baru = $pdo->query("SELECT COUNT(*) FROM anggota WHERE DATE_FORMAT(created_at, '%Y-%m') = '$bulan_ini'")->fetchColumn();

// Buku hampir habis (stok <= 1)
$buku_habis = $pdo->query("SELECT COUNT(*) FROM buku WHERE stok <= 1 AND status = 'tersedia'")->fetchColumn();

// Buku terlambat
$terlambat = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'terlambat'")->fetchColumn();

// Peminjaman hari ini
$hari_ini = date('Y-m-d');
$pinjam_hari_ini = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE DATE(tanggal_pinjam) = '$hari_ini'")->fetchColumn();

// Pengembalian hari ini
$kembali_hari_ini = $pdo->query("SELECT COUNT(*) FROM pengembalian WHERE DATE(tanggal_kembali) = '$hari_ini'")->fetchColumn();

// ===== BUKU SEDANG DIPINJAM =====
$sql = "SELECT b.judul, a.nama_lengkap as peminjam, p.tanggal_jatuh_tempo, p.kode_transaksi
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.peminjaman_id = p.peminjaman_id
        JOIN buku b ON dp.buku_id = b.buku_id
        JOIN anggota a ON p.anggota_id = a.anggota_id
        WHERE dp.status_kembali = 'belum'
        LIMIT 10";
$dipinjam = $pdo->query($sql)->fetchAll();

// ===== BUKU POPULER =====
$sql = "SELECT b.judul, b.penulis, COUNT(dp.detail_id) as total
        FROM detail_peminjaman dp
        JOIN buku b ON dp.buku_id = b.buku_id
        GROUP BY b.buku_id
        ORDER BY total DESC
        LIMIT 5";
$populer = $pdo->query($sql)->fetchAll();

// ===== ANGGOTA TERAKTIF =====
$sql = "SELECT a.nama_lengkap, a.nis_nim, COUNT(p.peminjaman_id) as total
        FROM anggota a
        JOIN peminjaman p ON a.anggota_id = p.anggota_id
        GROUP BY a.anggota_id
        ORDER BY total DESC
        LIMIT 5";
$aktif = $pdo->query($sql)->fetchAll();

// ===== BUKU HAMPIR HABIS (STOK <= 1) =====
$sql = "SELECT judul, penulis, stok FROM buku WHERE stok <= 1 AND status = 'tersedia' ORDER BY stok ASC LIMIT 5";
$hampir_habis = $pdo->query($sql)->fetchAll();

// ===== BUKU TERLAMBAT (detail) =====
$sql = "SELECT b.judul, a.nama_lengkap as peminjam, p.tanggal_jatuh_tempo, p.kode_transaksi,
        DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) as hari_terlambat
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.peminjaman_id = p.peminjaman_id
        JOIN buku b ON dp.buku_id = b.buku_id
        JOIN anggota a ON p.anggota_id = a.anggota_id
        WHERE dp.status_kembali = 'belum' AND p.tanggal_jatuh_tempo < CURDATE()
        ORDER BY p.tanggal_jatuh_tempo ASC
        LIMIT 5";
$buku_terlambat = $pdo->query($sql)->fetchAll();

// ===== GRAFIK 6 BULAN TERAKHIR =====
$grafik = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $nama_bulan = date('M', strtotime("-$i months"));
    $jumlah = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE DATE_FORMAT(tanggal_pinjam, '%Y-%m') = '$bulan'")->fetchColumn();
    $grafik[] = ['bulan' => $nama_bulan, 'jumlah' => $jumlah];
}

// ===== PIE CHART: KATEGORI BUKU POPULER =====
$sql = "SELECT k.nama_kategori, COUNT(b.buku_id) as total
        FROM kategori_buku k
        JOIN buku b ON k.kategori_id = b.kategori_id
        GROUP BY k.kategori_id
        ORDER BY total DESC";
$pie_data = $pdo->query($sql)->fetchAll();

// ===== ACTIVITY LOG (10 aktivitas terakhir) =====
$activity = [];
// Peminjaman terbaru
$sql = "SELECT 'peminjaman' as tipe, kode_transaksi as kode, a.nama_lengkap as nama, tanggal_pinjam as waktu 
        FROM peminjaman p 
        JOIN anggota a ON p.anggota_id = a.anggota_id 
        ORDER BY p.peminjaman_id DESC LIMIT 5";
$activity_pinjam = $pdo->query($sql)->fetchAll();

// Pengembalian terbaru
$sql = "SELECT 'pengembalian' as tipe, p.kode_transaksi as kode, a.nama_lengkap as nama, pg.tanggal_kembali as waktu 
        FROM pengembalian pg 
        JOIN peminjaman p ON pg.peminjaman_id = p.peminjaman_id 
        JOIN anggota a ON p.anggota_id = a.anggota_id 
        ORDER BY pg.pengembalian_id DESC LIMIT 5";
$activity_kembali = $pdo->query($sql)->fetchAll();

// Gabung dan urutkan
$activity = array_merge($activity_pinjam, $activity_kembali);
usort($activity, function($a, $b) {
    return strtotime($b['waktu']) - strtotime($a['waktu']);
});
$activity = array_slice($activity, 0, 10);

// ===== NOTIFIKASI (HANYA YANG PENTING) =====
$notifikasi = [];

// Buku jatuh tempo hari ini (MAX 3)
$sql = "SELECT b.judul, a.nama_lengkap as peminjam 
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.peminjaman_id = p.peminjaman_id
        JOIN buku b ON dp.buku_id = b.buku_id
        JOIN anggota a ON p.anggota_id = a.anggota_id
        WHERE dp.status_kembali = 'belum' AND p.tanggal_jatuh_tempo = CURDATE()
        LIMIT 3";
$jatuh_tempo = $pdo->query($sql)->fetchAll();
foreach ($jatuh_tempo as $row) {
    $notifikasi[] = "⚠️ Buku '{$row['judul']}' milik {$row['peminjam']} jatuh tempo HARI INI!";
}

// Buku terlambat (MAX 3)
foreach (array_slice($buku_terlambat, 0, 3) as $row) {
    $notifikasi[] = "🚨 Buku '{$row['judul']}' terlambat {$row['hari_terlambat']} hari oleh {$row['peminjam']}";
}

// Buku hampir habis (MAX 2)
foreach (array_slice($hampir_habis, 0, 2) as $row) {
    $notifikasi[] = "📦 Stok buku '{$row['judul']}' tersisa {$row['stok']} (segera restok!)";
}

// Jika tidak ada notifikasi
if (empty($notifikasi)) {
    $notifikasi[] = "✅ Semua dalam keadaan baik! Tidak ada notifikasi.";
}
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
        body.dark-mode .sidebar {
            background: #0d1b2a;
        }
        .sidebar .brand {
            text-align: center;
            padding: 10px 0 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar .brand h3 {
            font-size: 18px;
            font-weight: 700;
        }
        .sidebar .brand small {
            font-size: 11px;
            opacity: 0.7;
        }
        .sidebar .menu {
            list-style: none;
            padding: 0 15px;
        }
        .sidebar .menu li {
            padding: 12px 16px;
            margin: 4px 0;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            color: rgba(255,255,255,0.7);
        }
        .sidebar .menu li:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar .menu li.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-weight: 600;
        }
        .sidebar .menu li .icon {
            margin-right: 12px;
        }
        .main-content {
            margin-left: 240px;
            padding: 20px 30px;
            min-height: 100vh;
        }
        .navbar-custom {
            background: #1a237e !important;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            position: relative;
            z-index: 9998;
        }
        body.dark-mode .navbar-custom {
            background: #0d1b2a !important;
        }
        .navbar-custom .navbar-brand { font-weight: 700; }
        .card-stats {
            border: none;
            border-radius: 14px;
            transition: 0.3s;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
        }
        .card-stats:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .card-stats .card-body { padding: 20px 22px; }
        .card-stats .icon { font-size: 32px; opacity: 0.7; }
        .card-stats .number { font-size: 26px; font-weight: 700; }
        .card-stats .label { color: #888; font-size: 13px; }
        .card-stats .sub-info { font-size: 12px; color: #aaa; margin-top: 4px; }
        .card-custom {
            background: var(--bg-card);
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            transition: 0.3s;
        }
        .card-custom .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 15px;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .table-custom thead th {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 2px solid var(--border-color);
            color: #888;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-custom tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--border-color);
        }
        .table-custom tbody tr:hover {
            background: var(--bg-body);
        }
        .badge-status {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-status.success { background: #e8f5e9; color: #2e7d32; }
        .badge-status.danger { background: #ffebee; color: #c62828; }
        .badge-status.warning { background: #fff3e0; color: #e65100; }
        .badge-status.info { background: #e3f2fd; color: #0d47a1; }
        body.dark-mode .badge-status.success { background: #1b5e20; color: #a5d6a7; }
        body.dark-mode .badge-status.danger { background: #b71c1c; color: #ef9a9a; }
        body.dark-mode .badge-status.warning { background: #e65100; color: #ffe0b2; }
        body.dark-mode .badge-status.info { background: #0d47a1; color: #90caf9; }
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
        .theme-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
        .notif-item {
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 6px;
            background: var(--bg-body);
            border-left: 4px solid #1a237e;
            font-size: 13px;
        }
        .notif-item.danger { border-left-color: #c62828; }
        .notif-item.warning { border-left-color: #e65100; }
        .notif-item.info { border-left-color: #0d47a1; }
        .notif-item.success { border-left-color: #2e7d32; }
        .activity-item {
            padding: 6px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-item .icon { font-size: 18px; }
        .activity-item .time { color: #888; font-size: 12px; margin-left: auto; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }
        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        @media (max-width: 768px) {
            .sidebar { width: 60px; padding: 10px 0; }
            .sidebar .brand h3 { display: none; }
            .sidebar .brand small { display: none; }
            .sidebar .menu li span { display: none; }
            .sidebar .menu li { text-align: center; padding: 12px 0; }
            .main-content { margin-left: 60px; padding: 15px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .dashboard-grid { grid-template-columns: 1fr; }
            .chart-grid { grid-template-columns: 1fr; }
        }
        .bg-primary-custom { background: #1a237e; }
        .bg-success-custom { background: #2e7d32; }
        .bg-warning-custom { background: #e65100; }
        .bg-danger-custom { background: #c62828; }
        .bg-info-custom { background: #0d47a1; }
        .text-primary-custom { color: #1a237e; }
        .text-success-custom { color: #2e7d32; }
        .text-warning-custom { color: #e65100; }
        .text-danger-custom { color: #c62828; }
        .text-info-custom { color: #0d47a1; }
        body.dark-mode .text-primary-custom { color: #90caf9; }
        body.dark-mode .text-success-custom { color: #a5d6a7; }
        body.dark-mode .text-warning-custom { color: #ffcc80; }
        body.dark-mode .text-danger-custom { color: #ef9a9a; }
        body.dark-mode .text-info-custom { color: #90caf9; }

        /* DROPDOWN PROFIL */
        .dropdown-profile .dropdown-toggle::after {
            display: none;
        }
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
        .dropdown-profile .dropdown-item:hover {
            background: #f1f4f9;
        }
        .dropdown-profile .dropdown-item .icon {
            margin-right: 10px;
        }
        .dropdown-profile .dropdown-divider {
            margin: 6px 0;
            border-color: #eef2f7;
        }
        .dropdown-profile .dropdown-item.logout-item {
            color: #c62828;
        }
        .dropdown-profile .dropdown-item.logout-item:hover {
            background: #ffebee;
        }
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
        .preview-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #fff;
        }
        .user-info .name {
            font-weight: 600;
            font-size: 14px;
        }
        .user-info .role {
            font-size: 12px;
            opacity: 0.8;
        }
        .user-info .logout {
            color: #ef9a9a;
            text-decoration: none;
            font-weight: 600;
        }
        .user-info .logout:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body id="body">

<!-- SIDEBAR ADMIN -->
<?php include 'views/sidebar_admin.php'; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- ===== NAVBAR ===== -->
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
                            $user_id = $_SESSION['user_id'];
                            $foto_path = 'upload/foto_admin/' . $user_id . '.jpg';
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
                        <li>
                            <a class="dropdown-item" href="views/profil_admin.php">
                                <span class="icon">👤</span> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="views/ubah_foto_admin.php">
                                <span class="icon">📷</span> Ubah Foto
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="views/ubah_password_admin.php">
                                <span class="icon">🔒</span> Ubah Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item logout-item" href="logout.php">
                                <span class="icon">🚪</span> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== NOTIFIKASI (MAX 5) ===== -->
    <div class="card-custom">
        <div class="card-title"><span class="icon">🔔</span> Notifikasi</div>
        <?php foreach (array_slice($notifikasi, 0, 5) as $notif): ?>
            <?php
            $class = 'info';
            if (strpos($notif, '⚠️') !== false) $class = 'warning';
            if (strpos($notif, '🚨') !== false) $class = 'danger';
            if (strpos($notif, '✅') !== false) $class = 'success';
            if (strpos($notif, '📦') !== false) $class = 'info';
            ?>
            <div class="notif-item <?= $class ?>"><?= $notif ?></div>
        <?php endforeach; ?>
        <?php if (count($notifikasi) > 5): ?>
            <div class="notif-item info">📌 +<?= count($notifikasi) - 5 ?> notifikasi lainnya</div>
        <?php endif; ?>
    </div>

    <!-- ===== STATISTIK ===== -->
    <div class="stats-grid">
        <div class="card card-stats">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="number text-primary-custom"><?= $total_buku ?></div>
                    <div class="label">Total Buku</div>
                    <div class="sub-info">+<?= $buku_baru ?> baru bulan ini</div>
                </div>
                <div class="icon">📖</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="number text-success-custom"><?= $total_anggota ?></div>
                    <div class="label">Total Anggota</div>
                    <div class="sub-info">+<?= $anggota_baru ?> baru bulan ini</div>
                </div>
                <div class="icon">👤</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="number text-warning-custom"><?= $total_pinjam ?></div>
                    <div class="label">Sedang Dipinjam</div>
                    <div class="sub-info"><?= $pinjam_hari_ini ?> pinjam hari ini</div>
                </div>
                <div class="icon">📝</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="number text-danger-custom">Rp <?= number_format($total_denda, 0, ',', '.') ?></div>
                    <div class="label">Total Denda</div>
                    <div class="sub-info"><?= $terlambat ?> buku terlambat</div>
                </div>
                <div class="icon">💰</div>
            </div>
        </div>
    </div>

    <!-- ===== STATISTIK TAMBAHAN ===== -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-stats">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="number text-info-custom"><?= $kembali_hari_ini ?></div>
                        <div class="label">Pengembalian Hari Ini</div>
                    </div>
                    <div class="icon">↩️</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="number text-warning-custom"><?= $buku_habis ?></div>
                        <div class="label">Buku Hampir Habis (≤1)</div>
                    </div>
                    <div class="icon">📦</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="number text-danger-custom"><?= $terlambat ?></div>
                        <div class="label">Buku Terlambat</div>
                    </div>
                    <div class="icon">🚨</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== CHART GRID (BAR CHART + PIE CHART) ===== -->
    <div class="chart-grid">
        <!-- GRAFIK BAR -->
        <div class="card-custom">
            <div class="card-title"><span class="icon">📈</span> Grafik Peminjaman 6 Bulan Terakhir</div>
            <canvas id="chartPinjam" height="200"></canvas>
        </div>

        <!-- PIE CHART -->
        <div class="card-custom">
            <div class="card-title"><span class="icon">📊</span> Kategori Buku Populer</div>
            <canvas id="pieChart" height="200"></canvas>
        </div>
    </div>

    <!-- ===== BUKU POPULER ===== -->
    <div class="card-custom">
        <div class="card-title"><span class="icon">🔥</span> Buku Populer</div>
        <?php if (count($populer) > 0): ?>
            <?php foreach ($populer as $key => $row): ?>
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid var(--border-color);">
                <span>🏆 <?= $key+1 ?>. <?= htmlspecialchars($row['judul']) ?></span>
                <span class="badge bg-primary"><?= $row['total'] ?>x</span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Belum ada data</p>
        <?php endif; ?>
    </div>

    <!-- ===== ROW 2 ===== -->
    <div class="row">
        <div class="col-md-4">
            <div class="card-custom">
                <div class="card-title"><span class="icon">🚨</span> Buku Terlambat</div>
                <?php if (count($buku_terlambat) > 0): ?>
                    <?php foreach ($buku_terlambat as $row): ?>
                    <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid var(--border-color); font-size:13px;">
                        <span><?= htmlspecialchars($row['judul']) ?></span>
                        <span class="text-danger">+<?= $row['hari_terlambat'] ?> hari</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">✅ Tidak ada buku terlambat</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom">
                <div class="card-title"><span class="icon">🏅</span> Anggota Teraktif</div>
                <?php if (count($aktif) > 0): ?>
                    <?php foreach ($aktif as $key => $row): ?>
                    <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid var(--border-color); font-size:13px;">
                        <span><?= $key+1 ?>. <?= htmlspecialchars($row['nama_lengkap']) ?></span>
                        <span class="badge bg-success"><?= $row['total'] ?> buku</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Belum ada data</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom">
                <div class="card-title"><span class="icon">📦</span> Buku Hampir Habis</div>
                <?php if (count($hampir_habis) > 0): ?>
                    <?php foreach ($hampir_habis as $row): ?>
                    <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid var(--border-color); font-size:13px;">
                        <span><?= htmlspecialchars($row['judul']) ?></span>
                        <span class="badge bg-warning">Stok: <?= $row['stok'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">✅ Semua buku stok aman</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== BUKU SEDANG DIPINJAM ===== -->
    <div class="card-custom">
        <div class="card-title"><span class="icon">📋</span> Buku Sedang Dipinjam</div>
        <table class="table-custom">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Peminjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dipinjam) > 0): ?>
                    <?php $no = 1; foreach ($dipinjam as $row): 
                        $status = 'Dipinjam';
                        $badge = 'warning';
                        if (strtotime($row['tanggal_jatuh_tempo']) < time()) {
                            $status = 'Terlambat';
                            $badge = 'danger';
                        } elseif (strtotime($row['tanggal_jatuh_tempo']) == strtotime(date('Y-m-d'))) {
                            $status = 'Jatuh Tempo Hari Ini';
                            $badge = 'info';
                        }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['peminjam']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
                        <td><span class="badge-status <?= $badge ?>"><?= $status ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Tidak ada buku yang sedang dipinjam</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== ACTIVITY LOG ===== -->
    <div class="card-custom">
        <div class="card-title"><span class="icon">📜</span> Activity Log Terbaru</div>
        <?php if (count($activity) > 0): ?>
            <?php foreach ($activity as $row): ?>
            <div class="activity-item">
                <span class="icon"><?= $row['tipe'] == 'peminjaman' ? '📝' : '↩️' ?></span>
                <span>
                    <strong><?= htmlspecialchars($row['nama']) ?></strong>
                    <?= $row['tipe'] == 'peminjaman' ? 'meminjam buku' : 'mengembalikan buku' ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($row['kode']) ?></span>
                </span>
                <span class="time"><?= date('d/m/Y H:i', strtotime($row['waktu'])) ?></span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Belum ada aktivitas</p>
        <?php endif; ?>
    </div>

    <!-- ===== FOOTER ===== -->
    <div style="text-align:center; color:#aaa; font-size:12px; padding:20px 0 10px 0; border-top:1px solid var(--border-color); margin-top:20px;">
        &copy; 2026 Politeknik Negeri Lampung
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="assets/js/darkmode.js"></script>
<script>
    // ===== GRAFIK BAR =====
    const ctx = document.getElementById('chartPinjam').getContext('2d');
    const grafikData = <?= json_encode($grafik) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: grafikData.map(d => d.bulan),
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: grafikData.map(d => d.jumlah),
                backgroundColor: '#1a237e',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // ===== PIE CHART =====
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieData = <?= json_encode($pie_data) ?>;
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: pieData.map(d => d.nama_kategori),
            datasets: [{
                data: pieData.map(d => d.total),
                backgroundColor: ['#1a237e', '#2e7d32', '#e65100', '#c62828', '#0d47a1', '#4a148c', '#004d40', '#880e4f'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        color: '#333',
                        font: { size: 12 }
                    }
                }
            }
        }
    });
</script>

</body>
</html>