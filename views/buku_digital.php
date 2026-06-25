<?php
// views/buku_digital.php - Perpustakaan Digital (E-Book)
session_start();

// Cek apakah admin ATAU anggota yang login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_anggota'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

// Tentukan apakah user adalah admin
$is_admin = isset($_SESSION['user_id']);

// Hapus ebook (hanya admin)
if (isset($_GET['hapus']) && $is_admin) {
    $ebook_id = $_GET['hapus'];
    $sql = "SELECT file_path FROM ebook WHERE ebook_id = :ebook_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['ebook_id' => $ebook_id]);
    $ebook = $stmt->fetch();
    if ($ebook && file_exists($ebook['file_path'])) {
        unlink($ebook['file_path']);
    }
    $sql = "DELETE FROM ebook WHERE ebook_id = :ebook_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['ebook_id' => $ebook_id]);
    header('Location: buku_digital.php?success=3');
    exit();
}

// Upload E-Book (hanya admin)
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_ebook']) && $is_admin) {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $file = $_FILES['file_ebook'];
    
    if (empty($judul) || empty($penulis) || empty($file['name'])) {
        $error = "❌ Semua field wajib diisi!";
    } else {
        $upload_dir = '../upload/ebook/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
        $file_path = $upload_dir . $file_name;
        
        $file_type = mime_content_type($file['tmp_name']);
        if ($file_type != 'application/pdf' && $file_type != 'application/x-pdf') {
            $error = "❌ Hanya file PDF yang diperbolehkan!";
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $error = "❌ Ukuran file maksimal 10MB!";
        } elseif (move_uploaded_file($file['tmp_name'], $file_path)) {
            $sql = "INSERT INTO ebook (judul, penulis, file_path, upload_by) 
                    VALUES (:judul, :penulis, :file_path, :upload_by)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'judul' => $judul,
                'penulis' => $penulis,
                'file_path' => $file_path,
                'upload_by' => $_SESSION['user_id']
            ]);
            $success = "✅ E-Book berhasil diupload!";
        } else {
            $error = "❌ Gagal upload file!";
        }
    }
}

// Ambil daftar ebook
$ebooks = $pdo->query("SELECT e.*, u.nama_lengkap as uploader 
                        FROM ebook e 
                        LEFT JOIN users u ON e.upload_by = u.user_id 
                        ORDER BY e.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Book - Perpustakaan</title>
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
        .ebook-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .ebook-card {
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: 0.3s;
            text-align: center;
            padding: 20px 15px;
        }
        .ebook-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .ebook-card .icon { font-size: 64px; color: #1a237e; }
        .ebook-card .judul { font-weight: 600; font-size: 14px; margin-top: 10px; }
        .ebook-card .penulis { font-size: 12px; color: #888; }
        .ebook-card .btn-baca { margin-top: 10px; }
        .btn-primary { background: #1a237e; color: #fff; padding: 8px 18px; border: none; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary:hover { background: #0d1555; }
        .btn-secondary { background: #e0e0e0; color: #555; padding: 8px 18px; border: none; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-secondary:hover { background: #c0c0c0; }
        .btn-sm { padding: 4px 10px; font-size: 12px; border: none; border-radius: 6px; text-decoration: none; display: inline-block; }
        .btn-danger { background: #c62828; color: #fff; }
        .btn-danger:hover { background: #b71c1c; }
        .btn-success { background: #2e7d32; color: #fff; }
        .btn-success:hover { background: #1b5e20; }
        
        /* DROPDOWN PROFIL */
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

<!-- SIDEBAR -->
<?php if ($is_admin): ?>
<!-- Sidebar Admin -->
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
        <li class="active"><span class="icon">📱</span> <span>E-Book</span></li>
    </ul>
</div>
<?php else: ?>
<!-- Sidebar Anggota -->
<div class="sidebar">
    <div class="brand">
        <h3>📚 PERPUSTAKAAN</h3>
        <small>Politeknik Negeri Lampung</small>
    </div>
    <ul class="menu">
        <li onclick="location.href='dashboard_anggota.php'"><span class="icon">📊</span> <span>Dashboard</span></li>
        <li onclick="location.href='buku_anggota.php'"><span class="icon">📚</span> <span>Daftar Buku</span></li>
        <li onclick="location.href='riwayat_anggota.php'"><span class="icon">📝</span> <span>Riwayat Pinjam</span></li>
        <li onclick="location.href='wishlist_anggota.php'"><span class="icon">⭐</span> <span>Wishlist</span></li>
        <li onclick="location.href='scan_qr.php'"><span class="icon">📷</span> <span>Scan QR</span></li>
        <li class="active"><span class="icon">📱</span> <span>E-Book</span></li>
    </ul>
</div>
<?php endif; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- NAVBAR -->
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
                            if ($is_admin) {
                                $user_id = $_SESSION['user_id'];
                                $foto_path = '../upload/foto_admin/' . $user_id . '.jpg';
                            } else {
                                $anggota_id = $_SESSION['anggota_id'];
                                $foto_path = '../upload/foto_anggota/' . $anggota_id . '.jpg';
                            }
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
                                <small style="color: #888; font-size: 12px;">
                                    <?= $is_admin ? htmlspecialchars($_SESSION['username']) : htmlspecialchars($_SESSION['nis_nim']) ?>
                                </small>
                            </div>
                        </li>
                        <?php if ($is_admin): ?>
                            <li><a class="dropdown-item" href="profil_admin.php"><span class="icon">👤</span> My Profile</a></li>
                            <li><a class="dropdown-item" href="ubah_foto_admin.php"><span class="icon">📷</span> Ubah Foto</a></li>
                            <li><a class="dropdown-item" href="ubah_password_admin.php"><span class="icon">🔒</span> Ubah Password</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="profil_anggota.php"><span class="icon">👤</span> My Profile</a></li>
                            <li><a class="dropdown-item" href="ubah_foto_anggota.php"><span class="icon">📷</span> Ubah Foto</a></li>
                            <li><a class="dropdown-item" href="ubah_password_anggota.php"><span class="icon">🔒</span> Ubah Password</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item logout-item" href="<?= $is_admin ? '../logout.php' : 'logout_anggota.php' ?>">
                                <span class="icon">🚪</span> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="top-header">
        <div class="page-title">
            <h2>📱 Perpustakaan Digital (E-Book)</h2>
            <small>Baca buku digital kapan saja dan di mana saja</small>
        </div>
    </div>

    <!-- FORM UPLOAD (HANYA ADMIN) -->
    <?php if ($is_admin): ?>
    <div class="card-custom">
        <h5>📤 Upload E-Book</h5>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="judul" class="form-control" placeholder="Judul Buku" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="penulis" class="form-control" placeholder="Penulis" required>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="file" name="file_ebook" class="form-control" accept=".pdf" required>
                    <small class="text-muted">Max 10MB, format PDF</small>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-success w-100">📤 Upload</button>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- DAFTAR E-BOOK -->
    <div class="card-custom">
        <div class="card-title">📚 Daftar E-Book</div>
        <?php if (count($ebooks) > 0): ?>
            <div class="ebook-grid">
                <?php foreach ($ebooks as $ebook): ?>
                <div class="ebook-card">
                    <div class="icon">📄</div>
                    <div class="judul"><?= htmlspecialchars($ebook['judul']) ?></div>
                    <div class="penulis"><?= htmlspecialchars($ebook['penulis']) ?></div>
                    <?php if ($is_admin): ?>
                        <small class="text-muted">Upload: <?= htmlspecialchars($ebook['uploader']) ?></small>
                    <?php endif; ?>
                    <div class="btn-baca">
                        <a href="<?= $ebook['file_path'] ?>" target="_blank" class="btn btn-primary btn-sm">📖 Baca</a>
                        <?php if ($is_admin): ?>
                            <a href="?hapus=<?= $ebook['ebook_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus e-book ini?')">🗑️</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted text-center py-4">Belum ada e-book tersedia. <?= $is_admin ? 'Upload sekarang!' : '' ?></p>
        <?php endif; ?>
    </div>

    <div style="text-align:center; color:#aaa; font-size:12px; padding:20px 0 10px 0; border-top:1px solid var(--border-color); margin-top:20px;">
        &copy; 2026 Politeknik Negeri Lampung
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/darkmode.js"></script>

</body>
</html>