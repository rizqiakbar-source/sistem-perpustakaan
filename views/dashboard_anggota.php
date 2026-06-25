<?php
// views/dashboard_anggota.php - Dashboard Anggota
session_start();
if (!isset($_SESSION['is_anggota']) || $_SESSION['is_anggota'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';

$anggota_id = $_SESSION['anggota_id'];

// Ambil data anggota
$sql = "SELECT * FROM anggota WHERE anggota_id = :anggota_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$data_anggota = $stmt->fetch();

// Total buku
$total_buku = $pdo->query("SELECT COUNT(*) FROM buku")->fetchColumn();

// Riwayat peminjaman anggota
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM detail_peminjaman WHERE peminjaman_id = p.peminjaman_id) as jumlah_buku
        FROM peminjaman p
        WHERE p.anggota_id = :anggota_id
        ORDER BY p.peminjaman_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$riwayat = $stmt->fetchAll();

// Peminjaman aktif
$sql = "SELECT COUNT(*) FROM peminjaman WHERE anggota_id = :anggota_id AND status = 'dipinjam'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$aktif = $stmt->fetchColumn();

// Buku yang sedang dipinjam
$sql = "SELECT b.judul, p.tanggal_jatuh_tempo, p.kode_transaksi
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.peminjaman_id = p.peminjaman_id
        JOIN buku b ON dp.buku_id = b.buku_id
        WHERE p.anggota_id = :anggota_id AND dp.status_kembali = 'belum'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$dipinjam = $stmt->fetchAll();

// ===== GRAFIK: Data 6 bulan terakhir =====
$grafik = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $nama_bulan = date('M', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) FROM peminjaman 
            WHERE anggota_id = :anggota_id 
            AND DATE_FORMAT(tanggal_pinjam, '%Y-%m') = :bulan";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['anggota_id' => $anggota_id, 'bulan' => $bulan]);
    $grafik[] = ['bulan' => $nama_bulan, 'jumlah' => $stmt->fetchColumn()];
}

// ===== BUKU POPULER =====
$sql = "SELECT b.judul, b.penulis, COUNT(dp.detail_id) as total
        FROM detail_peminjaman dp
        JOIN buku b ON dp.buku_id = b.buku_id
        GROUP BY b.buku_id
        ORDER BY total DESC
        LIMIT 5";
$populer = $pdo->query($sql)->fetchAll();

// ===== REKOMENDASI =====
$sql = "SELECT b.kategori_id, COUNT(dp.detail_id) as total
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.peminjaman_id = p.peminjaman_id
        JOIN buku b ON dp.buku_id = b.buku_id
        WHERE p.anggota_id = :anggota_id
        GROUP BY b.kategori_id
        ORDER BY total DESC
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$fav_kategori = $stmt->fetch();

$rekomendasi = [];
if ($fav_kategori) {
    $sql = "SELECT * FROM buku WHERE kategori_id = :kategori_id AND status = 'tersedia' ORDER BY RAND() LIMIT 4";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['kategori_id' => $fav_kategori['kategori_id']]);
    $rekomendasi = $stmt->fetchAll();
}

// ===== PERINGKAT ANGGOTA =====
$sql = "SELECT a.nama_lengkap, COUNT(p.peminjaman_id) as total
        FROM anggota a
        JOIN peminjaman p ON a.anggota_id = p.anggota_id
        GROUP BY a.anggota_id
        ORDER BY total DESC
        LIMIT 10";
$ranking = $pdo->query($sql)->fetchAll();

// ===== TOTAL BUKU DIBACA =====
$total_baca = count($riwayat);

// ===== NOTIFIKASI JATUH TEMPO =====
$sql = "SELECT b.judul, p.tanggal_jatuh_tempo, DATEDIFF(p.tanggal_jatuh_tempo, CURDATE()) as sisa_hari
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.peminjaman_id = p.peminjaman_id
        JOIN buku b ON dp.buku_id = b.buku_id
        WHERE p.anggota_id = :anggota_id AND dp.status_kembali = 'belum' AND p.tanggal_jatuh_tempo <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        ORDER BY p.tanggal_jatuh_tempo ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$notif_jatuh_tempo = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
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
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-card .info .number { font-size: 28px; font-weight: 700; color: #1a237e; }
        .stat-card .info .label { font-size: 14px; color: #888; }
        .stat-card .icon { font-size: 36px; opacity: 0.7; }
        .stat-card.blue .number { color: #1a237e; }
        .stat-card.green .number { color: #2e7d32; }
        .stat-card.orange .number { color: #e65100; }
        .stat-card.red .number { color: #c62828; }
        body.dark-mode .stat-card .number { color: #90caf9; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .badge-status {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-status.success { background: #e8f5e9; color: #2e7d32; }
        .badge-status.warning { background: #fff3e0; color: #e65100; }
        .badge-status.danger { background: #ffebee; color: #c62828; }
        body.dark-mode .badge-status.success { background: #1b5e20; color: #a5d6a7; }
        body.dark-mode .badge-status.warning { background: #e65100; color: #ffcc80; }
        body.dark-mode .badge-status.danger { background: #b71c1c; color: #ef9a9a; }
        .rank-badge {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
        }
        .rank-1 { background: #ffd700; color: #333; }
        .rank-2 { background: #c0c0c0; color: #333; }
        .rank-3 { background: #cd7f32; color: #333; }
        .rank-other { background: #1a237e; }
        .book-card-mini {
            transition: 0.3s;
            border-radius: 10px;
            padding: 12px;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
        }
        .book-card-mini:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .book-card-mini .book-title { font-weight: 600; font-size: 14px; margin-bottom: 3px; }
        .book-card-mini .book-author { font-size: 12px; color: #888; }
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
        .table-custom tbody tr:hover { background: var(--bg-body); }
        .notif-item { padding: 8px 12px; border-radius: 8px; margin-bottom: 6px; background: var(--bg-body); font-size: 13px; }
        .notif-item.danger { border-left: 4px solid #c62828; }
        .notif-item.warning { border-left: 4px solid #e65100; }
        .notif-item.info { border-left: 4px solid #0d47a1; }
    </style>
</head>
<body id="body">

<?php include 'sidebar_anggota.php'; ?>

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
            <h2>Dashboard Anggota</h2>
            <small>Selamat datang di Sistem Manajemen Perpustakaan</small>
        </div>
    </div>

    <!-- ===== NOTIFIKASI JATUH TEMPO ===== -->
    <?php if (count($notif_jatuh_tempo) > 0): ?>
    <div class="card-custom" style="border-left: 4px solid #c62828;">
        <div class="card-title"><span class="icon">🔔</span> Peringatan Jatuh Tempo</div>
        <?php foreach ($notif_jatuh_tempo as $notif): ?>
            <div class="notif-item <?= $notif['sisa_hari'] < 0 ? 'danger' : ($notif['sisa_hari'] <= 1 ? 'warning' : 'info') ?>" style="border-left:4px solid <?= $notif['sisa_hari'] < 0 ? '#c62828' : ($notif['sisa_hari'] <= 1 ? '#e65100' : '#0d47a1') ?>;">
                <?php if ($notif['sisa_hari'] < 0): ?>
                    🚨 Buku "<?= htmlspecialchars($notif['judul']) ?>" sudah terlambat <?= abs($notif['sisa_hari']) ?> hari!
                <?php elseif ($notif['sisa_hari'] == 0): ?>
                    ⚠️ Buku "<?= htmlspecialchars($notif['judul']) ?>" jatuh tempo HARI INI!
                <?php else: ?>
                    📚 Buku "<?= htmlspecialchars($notif['judul']) ?>" akan jatuh tempo dalam <?= $notif['sisa_hari'] ?> hari
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="info">
                <div class="number"><?= $total_buku ?></div>
                <div class="label">Total Buku</div>
            </div>
            <div class="icon">📖</div>
        </div>
        <div class="stat-card orange">
            <div class="info">
                <div class="number"><?= $aktif ?></div>
                <div class="label">Sedang Dipinjam</div>
            </div>
            <div class="icon">📝</div>
        </div>
        <div class="stat-card green">
            <div class="info">
                <div class="number"><?= $total_baca ?></div>
                <div class="label">Total Buku Dibaca</div>
            </div>
            <div class="icon">📚</div>
        </div>
        <div class="stat-card red">
            <div class="info">
                <div class="number">#<?= array_search($_SESSION['nama_lengkap'], array_column($ranking, 'nama_lengkap')) + 1 ?></div>
                <div class="label">Peringkat</div>
            </div>
            <div class="icon">🏆</div>
        </div>
    </div>

    <!-- GRAFIK + BUKU POPULER -->
    <div class="row">
        <div class="col-md-7">
            <div class="card-custom">
                <div class="card-title"><span class="icon">📈</span> Grafik Peminjaman 6 Bulan Terakhir</div>
                <canvas id="chartPinjam" height="180"></canvas>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card-custom">
                <div class="card-title"><span class="icon">🔥</span> Buku Populer</div>
                <?php if (count($populer) > 0): ?>
                    <?php foreach ($populer as $row): ?>
                    <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid var(--border-color);">
                        <span><?= htmlspecialchars($row['judul']) ?></span>
                        <span class="badge bg-primary"><?= $row['total'] ?>x</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Belum ada data</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- REKOMENDASI BUKU -->
    <div class="card-custom">
        <div class="card-title"><span class="icon">💡</span> Rekomendasi Buku untuk Anda</div>
        <div class="row">
            <?php if (count($rekomendasi) > 0): ?>
                <?php foreach ($rekomendasi as $row): ?>
                <div class="col-md-3 col-6 mb-2">
                    <div class="book-card-mini text-center">
                        <div style="font-size:32px;">📘</div>
                        <div class="book-title"><?= htmlspecialchars($row['judul']) ?></div>
                        <div class="book-author"><?= htmlspecialchars($row['penulis']) ?></div>
                        <span class="badge bg-<?= $row['status'] == 'tersedia' ? 'success' : 'danger' ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Belum ada rekomendasi. Pinjam buku dulu ya!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- PERINGKAT ANGGOTA -->
    <div class="card-custom">
        <div class="card-title"><span class="icon">🏆</span> Top 10 Peminjam Terbanyak</div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <?php foreach ($ranking as $key => $row): ?>
            <div style="display:flex; align-items:center; gap:10px; padding:6px 10px; background:var(--bg-body); border-radius:8px;">
                <span class="rank-badge <?= $key == 0 ? 'rank-1' : ($key == 1 ? 'rank-2' : ($key == 2 ? 'rank-3' : 'rank-other')) ?>">
                    <?= $key + 1 ?>
                </span>
                <span style="flex:1; font-size:13px; font-weight:600;"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                <span class="badge bg-secondary"><?= $row['total'] ?> buku</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- BUKU SEDANG DIPINJAM -->
    <div class="card-custom">
        <div class="card-title"><span class="icon">📋</span> Buku Sedang Anda Pinjam</div>
        <?php if (count($dipinjam) > 0): ?>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Buku</th>
                        <th>Kode Transaksi</th>
                        <th>Jatuh Tempo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($dipinjam as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['kode_transaksi']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Anda tidak sedang meminjam buku</p>
        <?php endif; ?>
    </div>

    <div style="text-align:center; color:#aaa; font-size:12px; padding:20px 0 10px 0; border-top:1px solid var(--border-color); margin-top:20px;">
        &copy; 2026 Politeknik Negeri Lampung
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../assets/js/darkmode.js"></script>
<script>
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
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>

</body>
</html>