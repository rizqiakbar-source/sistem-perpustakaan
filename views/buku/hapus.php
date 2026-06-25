<?php
// views/buku/hapus.php - Hapus Buku
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

$sql = "DELETE FROM buku WHERE buku_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

header('Location: index.php');
exit();
?>