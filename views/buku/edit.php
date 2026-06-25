<?php
// views/buku/edit.php - Edit Buku
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

// Ambil data buku
$sql = "SELECT * FROM buku WHERE buku_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$buku = $stmt->fetch();

if (!$buku) {
    header('Location: index.php');
    exit();
}

// Ambil data kategori
$kategori = $pdo->query("SELECT * FROM kategori_buku ORDER BY nama_kategori")->fetchAll();

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $kategori_id = $_POST['kategori_id'] ?: null;
    $stok = $_POST['stok'];
    $lokasi_rak = $_POST['lokasi_rak'];
    $status = $_POST['status'];

    $sql = "UPDATE buku SET 
            judul = :judul,
            penulis = :penulis,
            penerbit = :penerbit,
            tahun_terbit = :tahun_terbit,
            isbn = :isbn,
            kategori_id = :kategori_id,
            stok = :stok,
            lokasi_rak = :lokasi_rak,
            status = :status
            WHERE buku_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'judul' => $judul,
        'penulis' => $penulis,
        'penerbit' => $penerbit,
        'tahun_terbit' => $tahun_terbit,
        'isbn' => $isbn,
        'kategori_id' => $kategori_id,
        'stok' => $stok,
        'lokasi_rak' => $lokasi_rak,
        'status' => $status,
        'id' => $id
    ]);

    header('Location: index.php?success=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">📚 SISTEM PERPUSTAKAAN</span>
            <span class="text-white">👤 <?= $_SESSION['nama_lengkap'] ?> | <a href="../../logout.php" class="text-white">Logout</a></span>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>✏️ Edit Buku</h3>
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Judul Buku *</label>
                            <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($buku['judul']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penulis *</label>
                            <input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($buku['penulis']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" value="<?= htmlspecialchars($buku['penerbit']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" class="form-control" value="<?= $buku['tahun_terbit'] ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($buku['isbn']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori_id" class="form-control">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori as $row): ?>
                                    <option value="<?= $row['kategori_id'] ?>" <?= $row['kategori_id'] == $buku['kategori_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok *</label>
                            <input type="number" name="stok" class="form-control" value="<?= $buku['stok'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi Rak</label>
                            <input type="text" name="lokasi_rak" class="form-control" value="<?= htmlspecialchars($buku['lokasi_rak']) ?>" placeholder="Contoh: A1">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="tersedia" <?= $buku['status'] == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                <option value="dipinjam" <?= $buku['status'] == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                                <option value="rusak" <?= $buku['status'] == 'rusak' ? 'selected' : '' ?>>Rusak</option>
                                <option value="hilang" <?= $buku['status'] == 'hilang' ? 'selected' : '' ?>>Hilang</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>