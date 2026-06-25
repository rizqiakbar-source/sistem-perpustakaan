<?php
// views/get_buku_by_qr.php - API untuk Scan QR
session_start();
if (!isset($_SESSION['is_anggota']) || $_SESSION['is_anggota'] !== true) {
    echo json_encode(['success' => false]);
    exit();
}
require_once '../config/database.php';

$q = $_GET['q'] ?? '';
$sql = "SELECT * FROM buku WHERE isbn = :isbn OR buku_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['isbn' => $q, 'id' => $q]);
$buku = $stmt->fetch();

if ($buku) {
    echo json_encode([
        'success' => true,
        'buku_id' => $buku['buku_id'],
        'judul' => $buku['judul'],
        'penulis' => $buku['penulis'],
        'status' => $buku['status'],
        'stok' => $buku['stok']
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>