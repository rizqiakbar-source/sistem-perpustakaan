<?php
// views/laporan/export_excel.php - Export Excel (PASTI BERHASIL)
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/database.php';

// Ambil data
$data = $pdo->query("SELECT p.kode_transaksi, a.nama_lengkap as anggota, p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.status 
                     FROM peminjaman p 
                     JOIN anggota a ON p.anggota_id = a.anggota_id 
                     ORDER BY p.peminjaman_id DESC")->fetchAll();

// Header untuk Excel (format HTML yang bisa dibaca Excel)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan_Perpustakaan_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

echo '<html>';
echo '<head><meta charset="UTF-8"><title>Laporan Perpustakaan</title></head>';
echo '<body>';

echo '<h1 style="color:#1a237e; text-align:center;">📚 LAPORAN PERPUSTAKAAN</h1>';
echo '<p style="text-align:center;">Periode: ' . date('d/m/Y') . '</p>';

echo '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">';
echo '<tr style="background:#1a237e; color:#fff; font-weight:bold;">';
echo '<th>No</th>';
echo '<th>Kode Transaksi</th>';
echo '<th>Anggota</th>';
echo '<th>Tanggal Pinjam</th>';
echo '<th>Jatuh Tempo</th>';
echo '<th>Status</th>';
echo '</tr>';

$no = 1;
foreach ($data as $row) {
    echo '<tr>';
    echo '<td>' . $no++ . '</td>';
    echo '<td>' . htmlspecialchars($row['kode_transaksi']) . '</td>';
    echo '<td>' . htmlspecialchars($row['anggota']) . '</td>';
    echo '<td>' . $row['tanggal_pinjam'] . '</td>';
    echo '<td>' . $row['tanggal_jatuh_tempo'] . '</td>';
    echo '<td>' . ucfirst($row['status']) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '<p style="margin-top:20px; color:#888; font-size:12px; text-align:center;">Dicetak: ' . date('d/m/Y H:i:s') . ' | &copy; 2026 Politeknik Negeri Lampung</p>';

echo '</body></html>';
exit();
?>