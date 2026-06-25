<?php
// views/wishlist_toggle.php - API Tambah/Hapus Wishlist
session_start();
if (!isset($_SESSION['is_anggota']) || $_SESSION['is_anggota'] !== true) {
    echo json_encode(['success' => false]);
    exit();
}
require_once '../config/database.php';

$anggota_id = $_SESSION['anggota_id'];
$buku_id = $_POST['buku_id'] ?? 0;
$action = $_POST['action'] ?? 'tambah';

if ($action == 'tambah') {
    $sql = "INSERT IGNORE INTO wishlist (anggota_id, buku_id) VALUES (:anggota_id, :buku_id)";
} else {
    $sql = "DELETE FROM wishlist WHERE anggota_id = :anggota_id AND buku_id = :buku_id";
}

$stmt = $pdo->prepare($sql);
$stmt->execute(['anggota_id' => $anggota_id, 'buku_id' => $buku_id]);

echo json_encode(['success' => true]);
?>