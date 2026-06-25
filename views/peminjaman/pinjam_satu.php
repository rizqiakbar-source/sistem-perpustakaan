<?php
// views/peminjaman/pinjam_satu.php - Pinjam 1 Buku Langsung
session_start();

if (!isset($_SESSION['is_anggota']) || $_SESSION['is_anggota'] !== true) {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';

$anggota_id = $_SESSION['anggota_id'];
$buku_id = $_GET['buku'] ?? 0;

if ($buku_id == 0) {
    die("Buku tidak ditemukan!");
}

// Ambil data buku
$sql = "SELECT * FROM buku WHERE buku_id = :buku_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['buku_id' => $buku_id]);
$buku = $stmt->fetch();

if (!$buku) {
    die("Buku tidak ditemukan!");
}

// Proses peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'];
    
    // Generate kode transaksi
    $kode = 'PJM/' . date('Ymd') . '/' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    
    // Insert ke peminjaman
    $sql = "INSERT INTO peminjaman (kode_transaksi, anggota_id, user_id, tanggal_jatuh_tempo, status) 
            VALUES (:kode, :anggota_id, 1, :tgl, 'dipinjam')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'kode' => $kode,
        'anggota_id' => $anggota_id,
        'tgl' => $tanggal_jatuh_tempo
    ]);
    $peminjaman_id = $pdo->lastInsertId();
    
    // Insert detail peminjaman
    $sql = "INSERT INTO detail_peminjaman (peminjaman_id, buku_id) VALUES (:peminjaman_id, :buku_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['peminjaman_id' => $peminjaman_id, 'buku_id' => $buku_id]);
    
    // Update stok dan status buku
    $sql = "UPDATE buku SET stok = stok - 1, status = 'dipinjam' WHERE buku_id = :buku_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['buku_id' => $buku_id]);
    
    header('Location: ../dashboard_anggota.php?success=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body {
            background: #f1f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card-pinjam {
            max-width: 500px;
            width: 100%;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        .card-pinjam h3 {
            color: #1a237e;
            text-align: center;
            margin-bottom: 5px;
        }
        .card-pinjam .sub {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
        }
        .book-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .book-info .label {
            font-size: 12px;
            color: #888;
        }
        .book-info .value {
            font-size: 16px;
            font-weight: 600;
        }
        .book-cover {
            width: 80px;
            height: 110px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .book-cover-placeholder {
            width: 80px;
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1a237e;
            color: #fff;
            border-radius: 6px;
            font-size: 32px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="card-pinjam">
        <h3>📝 Konfirmasi Pinjam</h3>
        <p class="sub">Pastikan data buku yang akan dipinjam</p>

        <div class="text-center mb-3">
            <?php 
            $sampul_path = "../../upload/sampul/" . $buku_id . ".jpg";
            if (file_exists($sampul_path)): ?>
                <img src="<?= $sampul_path ?>" class="book-cover" alt="Sampul">
            <?php else: ?>
                <div class="book-cover-placeholder" style="margin:0 auto;">
                    <?= strtoupper(substr($buku['judul'], 0, 2)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="book-info">
            <div class="label">Judul Buku</div>
            <div class="value"><?= htmlspecialchars($buku['judul']) ?></div>
            <hr>
            <div class="label">Penulis</div>
            <div class="value"><?= htmlspecialchars($buku['penulis']) ?></div>
            <hr>
            <div class="label">Stok Tersedia</div>
            <div class="value"><?= $buku['stok'] ?></div>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">📅 Tanggal Jatuh Tempo</label>
                <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                <small class="text-muted">Buku harus dikembalikan sebelum tanggal ini</small>
            </div>
            <button type="submit" class="btn btn-primary w-100">✅ Konfirmasi Pinjam</button>
            <a href="../buku_anggota.php" class="btn btn-secondary w-100 mt-2">⬅ Batal</a>
        </form>
    </div>
</body>
</html>