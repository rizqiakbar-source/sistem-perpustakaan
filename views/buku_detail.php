<?php
// views/buku_detail.php - Detail Buku dengan Rating & Review
session_start();
if (!isset($_SESSION['is_anggota']) || $_SESSION['is_anggota'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';

$buku_id = $_GET['id'] ?? 0;
$anggota_id = $_SESSION['anggota_id'];

// Ambil data buku
$sql = "SELECT b.*, k.nama_kategori FROM buku b 
        LEFT JOIN kategori_buku k ON b.kategori_id = k.kategori_id 
        WHERE b.buku_id = :buku_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['buku_id' => $buku_id]);
$buku = $stmt->fetch();

if (!$buku) {
    die("Buku tidak ditemukan!");
}

// Ambil rating & review
$sql = "SELECT r.*, a.nama_lengkap 
        FROM rating r 
        JOIN anggota a ON r.anggota_id = a.anggota_id 
        WHERE r.buku_id = :buku_id 
        ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['buku_id' => $buku_id]);
$reviews = $stmt->fetchAll();

// Hitung rata-rata rating
$avg_rating = $pdo->query("SELECT COALESCE(AVG(rating), 0) FROM rating WHERE buku_id = $buku_id")->fetchColumn();

// Cek apakah user sudah pernah rating
$sql = "SELECT * FROM rating WHERE buku_id = :buku_id AND anggota_id = :anggota_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['buku_id' => $buku_id, 'anggota_id' => $anggota_id]);
$user_rating = $stmt->fetch();

// Proses simpan rating
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $review = $_POST['review'];
    
    if ($user_rating) {
        $sql = "UPDATE rating SET rating = :rating, review = :review WHERE rating_id = :rating_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['rating' => $rating, 'review' => $review, 'rating_id' => $user_rating['rating_id']]);
    } else {
        $sql = "INSERT INTO rating (buku_id, anggota_id, rating, review) VALUES (:buku_id, :anggota_id, :rating, :review)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['buku_id' => $buku_id, 'anggota_id' => $anggota_id, 'rating' => $rating, 'review' => $review]);
    }
    header('Location: buku_detail.php?id=' . $buku_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .rating-stars { color: #f9a825; font-size: 24px; cursor: pointer; }
        .rating-stars .star { transition: 0.2s; }
        .rating-stars .star:hover { transform: scale(1.2); }
        .review-card { border-left: 4px solid #1a237e; padding: 10px 15px; margin-bottom: 10px; background: #f8fafc; }
        body.dark-mode .review-card { background: #1a1a2e; }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="brand">
            <h3>📚 PERPUSTAKAAN</h3>
            <small>Politeknik Negeri Lampung</small>
        </div>
        <ul class="menu">
            <li onclick="location.href='dashboard_anggota.php'"><span class="icon">📊</span> <span>Dashboard</span></li>
            <li class="active"><span class="icon">📚</span> <span>Daftar Buku</span></li>
            <li onclick="location.href='riwayat_anggota.php'"><span class="icon">📝</span> <span>Riwayat Pinjam</span></li>
            <li onclick="location.href='wishlist_anggota.php'"><span class="icon">⭐</span> <span>Wishlist</span></li>
            <li onclick="location.href='scan_qr.php'"><span class="icon">📷</span> <span>Scan QR</span></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container">
            <h2><?= htmlspecialchars($buku['judul']) ?></h2>
            <p><strong>Penulis:</strong> <?= htmlspecialchars($buku['penulis']) ?></p>
            <p><strong>Kategori:</strong> <?= htmlspecialchars($buku['nama_kategori'] ?? '-') ?></p>
            <p><strong>Stok:</strong> <?= $buku['stok'] ?></p>
            <p><strong>Status:</strong> <?= ucfirst($buku['status']) ?></p>

            <hr>
            <h4>⭐ Rating: <?= number_format($avg_rating, 1) ?> / 5</h4>

            <!-- Form Rating -->
            <div class="card-custom">
                <h5><?= $user_rating ? 'Ubah Rating' : 'Berikan Rating' ?></h5>
                <form method="POST">
                    <div class="mb-2">
                        <label class="form-label">Pilih Bintang</label>
                        <div class="rating-stars" id="starContainer">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" data-value="<?= $i ?>" onclick="setRating(<?= $i ?>)">
                                    <i class="fas fa-star" id="star<?= $i ?>" style="color:<?= ($user_rating && $user_rating['rating'] >= $i) ? '#f9a825' : '#ddd' ?>"></i>
                                </span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="<?= $user_rating['rating'] ?? 0 ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Review</label>
                        <textarea name="review" class="form-control" rows="3"><?= htmlspecialchars($user_rating['review'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Simpan Rating</button>
                </form>
            </div>

            <!-- Daftar Review -->
            <h5>📝 Review Lainnya</h5>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <strong><?= htmlspecialchars($review['nama_lengkap']) ?></strong>
                    <span style="color:#f9a825;"><?= str_repeat('⭐', $review['rating']) ?></span>
                    <p><?= htmlspecialchars($review['review']) ?></p>
                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>

            <a href="buku_anggota.php" class="btn btn-secondary">⬅ Kembali</a>
        </div>
    </div>

    <script>
        let selectedRating = <?= $user_rating['rating'] ?? 0 ?>;

        function setRating(value) {
            selectedRating = value;
            document.getElementById('ratingInput').value = value;
            for (let i = 1; i <= 5; i++) {
                document.getElementById('star' + i).style.color = i <= value ? '#f9a825' : '#ddd';
            }
        }
    </script>
</body>
</html>