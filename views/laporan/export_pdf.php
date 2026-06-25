<?php
// views/laporan/export_pdf.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil data
$total_peminjaman = $pdo->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
$total_pengembalian = $pdo->query("SELECT COUNT(*) FROM pengembalian")->fetchColumn();
$total_terlambat = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'terlambat'")->fetchColumn();
$total_denda = $pdo->query("SELECT COALESCE(SUM(total_denda), 0) FROM pengembalian")->fetchColumn();

$detail = $pdo->query("SELECT p.kode_transaksi, a.nama_lengkap as anggota, p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.status 
                       FROM peminjaman p 
                       JOIN anggota a ON p.anggota_id = a.anggota_id 
                       ORDER BY p.peminjaman_id DESC LIMIT 50")->fetchAll();

// HTML untuk PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Perpustakaan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
        h1 { text-align: center; color: #1a237e; }
        .sub { text-align: center; color: #888; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #1a237e; color: #fff; }
        .footer { text-align: center; margin-top: 30px; color: #888; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge-success { background: #e8f5e9; color: #2e7d32; padding: 2px 10px; border-radius: 12px; }
        .badge-danger { background: #ffebee; color: #c62828; padding: 2px 10px; border-radius: 12px; }
        .badge-warning { background: #fff3e0; color: #e65100; padding: 2px 10px; border-radius: 12px; }
    </style>
</head>
<body>
    <h1>📚 LAPORAN PERPUSTAKAAN</h1>
    <p class="sub">Periode: ' . date('d/m/Y') . '</p>

    <h3>📊 REKAP TRANSAKSI</h3>
    <table>
        <tr><td><strong>Total Peminjaman</strong></td><td>' . $total_peminjaman . '</td></tr>
        <tr><td><strong>Total Pengembalian</strong></td><td>' . $total_pengembalian . '</td></tr>
        <tr><td><strong>Terlambat</strong></td><td>' . $total_terlambat . '</td></tr>
        <tr><td><strong>Total Denda</strong></td><td>Rp ' . number_format($total_denda, 0, ',', '.') . '</td></tr>
    </table>

    <h3>📋 DETAIL TRANSAKSI TERBARU</h3>
    <table>
        <thead><tr><th>Kode</th><th>Anggota</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Status</th></tr></thead>
        <tbody>';

foreach ($detail as $row) {
    $badge = 'badge-warning';
    $label = 'Dipinjam';
    if ($row['status'] == 'dikembalikan') { $badge = 'badge-success'; $label = 'Kembali'; }
    elseif ($row['status'] == 'terlambat') { $badge = 'badge-danger'; $label = 'Terlambat'; }
    $html .= '<tr>
        <td>' . htmlspecialchars($row['kode_transaksi']) . '</td>
        <td>' . htmlspecialchars($row['anggota']) . '</td>
        <td>' . date('d/m/Y', strtotime($row['tanggal_pinjam'])) . '</td>
        <td>' . date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) . '</td>
        <td><span class="' . $badge . '">' . $label . '</span></td>
    </tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">Dicetak: ' . date('d/m/Y H:i:s') . ' | &copy; 2026 Politeknik Negeri Lampung</div>
</body>
</html>';

// Generate PDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Perpustakaan_" . date('Y-m-d') . ".pdf", array("Attachment" => true));
exit();
?>