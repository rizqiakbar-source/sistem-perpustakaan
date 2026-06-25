<?php
// views/anggota/hapus.php - Hapus Anggota
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

$sql = "DELETE FROM anggota WHERE anggota_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

header('Location: index.php');
exit();
?>