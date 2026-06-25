<?php
// views/laporan/export_csv.php - Export CSV
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/database.php';

$data = $pdo->query("SELECT p.kode_transaksi, a.nama_lengkap as anggota, p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.status 
                     FROM peminjaman p 
                     JOIN anggota a ON p.anggota_id = a.anggota_id 
                     ORDER BY p.peminjaman_id DESC")->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Laporan_Perpustakaan_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['No', 'Kode Transaksi', 'Anggota', 'Tanggal Pinjam', 'Jatuh Tempo', 'Status']);

$no = 1;
foreach ($data as $row) {
    fputcsv($output, [
        $no++,
        $row['kode_transaksi'],
        $row['anggota'],
        $row['tanggal_pinjam'],
        $row['tanggal_jatuh_tempo'],
        ucfirst($row['status'])
    ]);
}
fclose($output);
exit();
?>