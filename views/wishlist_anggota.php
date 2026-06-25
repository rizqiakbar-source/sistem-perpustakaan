<?php
// views/wishlist_anggota.php - Wishlist Anggota
session_start();
if (!isset($_SESSION['is_anggota']) || $_SESSION['is_anggota'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';

$anggota_id = $_SESSION['anggota_id'];

// Ambil wishlist
$sql = "SELECT b.*, w.created_at as tgl_wishlist 
        FROM wishlist w
        JOIN buku b ON w.buku_id = b.buku_id
        WHERE w.anggota_id = :anggota_id
        ORDER BY w.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id]);
$wishlist = $stmt->fetchAll();

// Ambil semua data buku untuk modal
$sql_buku = "SELECT b.*, k.nama_kategori 
            FROM buku b 
            LEFT JOIN kategori_buku k ON b.kategori_id = k.kategori_id";
$buku_all = $pdo->query($sql_buku)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - Perpustakaan</title>
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
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .book-card {
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: 0.3s;
            cursor: pointer;
            position: relative;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .book-card .cover {
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            position: relative;
            overflow: hidden;
        }
        .book-card .cover img { width: 100%; height: 100%; object-fit: cover; }
        .book-card .cover .placeholder {
            font-size: 48px;
            font-weight: 700;
            color: #fff;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .book-card .cover .btn-hapus {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(198, 40, 40, 0.9);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .book-card .cover .btn-hapus:hover { background: #c62828; transform: scale(1.1); }
        .book-card .info { padding: 12px 14px; }
        .book-card .info .judul {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .book-card .info .penulis { font-size: 12px; color: #888; }
        .book-card .info .kategori { font-size: 11px; color: #aaa; margin-top: 2px; }
        .book-card .info .status-stok {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }
        .badge-status {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-status.success { background: #e8f5e9; color: #2e7d32; }
        .badge-status.danger { background: #ffebee; color: #c62828; }
        body.dark-mode .badge-status.success { background: #1b5e20; color: #a5d6a7; }
        body.dark-mode .badge-status.danger { background: #b71c1c; color: #ef9a9a; }
        .btn-pinjam-wishlist {
            padding: 4px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-pinjam-wishlist.primary { background: #1a237e; color: #fff; }
        .btn-pinjam-wishlist.primary:hover { background: #0d1555; }
        .btn-pinjam-wishlist.secondary { background: #e0e0e0; color: #888; cursor: not-allowed; }
        .color-1 { background: #1a237e; }
        .color-2 { background: #2e7d32; }
        .color-3 { background: #c62828; }
        .color-4 { background: #e65100; }
        .color-5 { background: #0d47a1; }
        .color-6 { background: #4a148c; }
        .color-7 { background: #004d40; }
        .color-8 { background: #880e4f; }
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
        .modal-content {
            background: var(--bg-card);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 16px;
        }
        .modal-header { border-bottom: 1px solid var(--border-color); }
        .modal-footer { border-top: 1px solid var(--border-color); }
        .modal-book-cover {
            width: 150px;
            height: 210px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .modal-book-cover-placeholder {
            width: 150px;
            height: 210px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1a237e;
            color: #fff;
            border-radius: 8px;
            font-size: 64px;
            font-weight: 700;
        }
        .detail-label { font-weight: 600; color: #888; font-size: 13px; }
        .detail-value { font-size: 15px; color: var(--text-color); }
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
            <h2>⭐ Wishlist Saya</h2>
            <small>Buku favorit yang ingin dibaca</small>
        </div>
    </div>

    <div class="book-grid">
        <?php if (count($wishlist) > 0): ?>
            <?php foreach ($wishlist as $row): 
                $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8'];
                $rand_color = $colors[array_rand($colors)];
                $sampul_path = "../upload/sampul/" . $row['buku_id'] . ".jpg";
            ?>
            <div class="book-card" onclick="openDetail(<?= $row['buku_id'] ?>)">
                <div class="cover">
                    <?php if (file_exists($sampul_path)): ?>
                        <img src="<?= $sampul_path ?>" alt="Sampul">
                    <?php else: ?>
                        <div class="placeholder <?= $rand_color ?>">
                            <?= strtoupper(substr($row['judul'], 0, 2)) ?>
                        </div>
                    <?php endif; ?>
                    <button class="btn-hapus" onclick="event.stopPropagation(); hapusWishlist(<?= $row['buku_id'] ?>)" title="Hapus dari wishlist">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="info">
                    <div class="judul"><?= htmlspecialchars($row['judul']) ?></div>
                    <div class="penulis"><?= htmlspecialchars($row['penulis']) ?></div>
                    <div class="kategori"><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></div>
                    <div class="status-stok">
                        <span class="badge-status <?= $row['status'] == 'tersedia' ? 'success' : 'danger' ?>">
                            <?= $row['status'] == 'tersedia' ? 'Tersedia' : 'Dipinjam' ?>
                        </span>
                        <span style="font-size:12px; color:#888;">Stok: <?= $row['stok'] ?></span>
                    </div>
                    <?php if ($row['status'] == 'tersedia' && $row['stok'] > 0): ?>
                        <a href="peminjaman/pinjam_satu.php?buku=<?= $row['buku_id'] ?>" class="btn-pinjam-wishlist primary w-100 text-center mt-2" onclick="event.stopPropagation();">
                            📝 Pinjam Sekarang
                        </a>
                    <?php else: ?>
                        <button class="btn-pinjam-wishlist secondary w-100 text-center mt-2" onclick="event.stopPropagation();" disabled>
                            🚫 Tidak Tersedia
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center" style="padding:60px 20px;">
                    <div style="font-size:64px;">⭐</div>
                    <h4 class="mt-3">Wishlist masih kosong</h4>
                    <p class="text-muted">Yuk cari buku favoritmu dan tambahkan ke wishlist!</p>
                    <a href="buku_anggota.php" class="btn btn-primary mt-2">📚 Lihat Daftar Buku</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div style="text-align:center; color:#aaa; font-size:12px; padding:20px 0 10px 0; border-top:1px solid var(--border-color); margin-top:20px;">
        &copy; 2026 Politeknik Negeri Lampung
    </div>

</div>

<!-- MODAL DETAIL BUKU -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">📖 Detail Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="modalBodyContent">
                    <div class="col-md-4 text-center" id="modalCoverContainer">
                        <img id="modalCoverImage" src="" class="modal-book-cover" style="display:none;">
                        <div class="modal-book-cover-placeholder" id="modalCoverPlaceholder" style="font-size:64px;">📚</div>
                    </div>
                    <div class="col-md-8">
                        <h4 id="modalJudul" class="fw-bold">-</h4>
                        <p class="text-muted" id="modalPenulis">-</p>
                        <hr>
                        <div class="row">
                            <div class="col-6 mb-2"><div class="detail-label">Kategori</div><div class="detail-value" id="modalKategori">-</div></div>
                            <div class="col-6 mb-2"><div class="detail-label">Tahun Terbit</div><div class="detail-value" id="modalTahun">-</div></div>
                            <div class="col-6 mb-2"><div class="detail-label">Stok</div><div class="detail-value" id="modalStok">-</div></div>
                            <div class="col-6 mb-2"><div class="detail-label">Status</div><div class="detail-value" id="modalStatus">-</div></div>
                            <div class="col-12 mb-2"><div class="detail-label">ISBN</div><div class="detail-value" id="modalIsbn">-</div></div>
                            <div class="col-12 mb-2"><div class="detail-label">Penerbit</div><div class="detail-value" id="modalPenerbit">-</div></div>
                            <div class="col-12 mb-2"><div class="detail-label">Lokasi Rak</div><div class="detail-value" id="modalRak">-</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="modalPinjamBtn">📝 Pinjam Buku</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/darkmode.js"></script>
<script>
    const bukuData = <?= json_encode($buku_all) ?>;

    function openDetail(bukuId) {
        const buku = bukuData.find(b => b.buku_id == bukuId);
        if (!buku) return;
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        document.getElementById('modalJudul').textContent = buku.judul;
        document.getElementById('modalPenulis').textContent = '✍️ ' + buku.penulis;
        document.getElementById('modalKategori').textContent = buku.nama_kategori || '-';
        document.getElementById('modalTahun').textContent = buku.tahun_terbit || '-';
        document.getElementById('modalStok').textContent = buku.stok;
        document.getElementById('modalIsbn').textContent = buku.isbn || '-';
        document.getElementById('modalPenerbit').textContent = buku.penerbit || '-';
        document.getElementById('modalRak').textContent = buku.lokasi_rak || '-';
        const statusEl = document.getElementById('modalStatus');
        const status = buku.status;
        statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusEl.className = 'detail-value badge-status ' + (status == 'tersedia' ? 'success' : 'danger');
        const coverImg = document.getElementById('modalCoverImage');
        const coverPlaceholder = document.getElementById('modalCoverPlaceholder');
        const coverPath = '../upload/sampul/' + buku.buku_id + '.jpg';
        fetch(coverPath, { method: 'HEAD' })
            .then(res => {
                if (res.ok) {
                    coverImg.src = coverPath;
                    coverImg.style.display = 'block';
                    coverPlaceholder.style.display = 'none';
                } else {
                    coverImg.style.display = 'none';
                    coverPlaceholder.style.display = 'flex';
                    coverPlaceholder.textContent = buku.judul.charAt(0).toUpperCase();
                }
            })
            .catch(() => {
                coverImg.style.display = 'none';
                coverPlaceholder.style.display = 'flex';
                coverPlaceholder.textContent = buku.judul.charAt(0).toUpperCase();
            });
        const pinjamBtn = document.getElementById('modalPinjamBtn');
        if (buku.status == 'tersedia' && buku.stok > 0) {
            pinjamBtn.style.display = 'inline-block';
            pinjamBtn.onclick = function() {
                window.location.href = 'peminjaman/pinjam_satu.php?buku=' + buku.buku_id;
            };
        } else {
            pinjamBtn.style.display = 'none';
        }
        modal.show();
    }

    function hapusWishlist(buku_id) {
        if (!confirm('Hapus buku dari wishlist?')) return;
        fetch('wishlist_toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'buku_id=' + buku_id + '&action=hapus'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus!');
            }
        });
    }
</script>

</body>
</html>