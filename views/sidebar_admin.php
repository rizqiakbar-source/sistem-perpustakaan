<!-- views/sidebar_admin.php -->
<?php
// Tentukan base path
$base = (strpos($_SERVER['PHP_SELF'], 'views/') !== false) ? '../' : '';
?>
<div class="sidebar">
    <div class="brand">
        <h3>📚 PERPUSTAKAAN</h3>
        <small>Politeknik Negeri Lampung</small>
    </div>
    <ul class="menu">
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" 
            onclick="location.href='<?= $base ?>index.php'">
            <span class="icon">📊</span> <span>Dashboard</span>
        </li>
        <li onclick="location.href='<?= $base ?>views/buku/index.php'">
            <span class="icon">📚</span> <span>Buku</span>
        </li>
        <li onclick="location.href='<?= $base ?>views/anggota/index.php'">
            <span class="icon">👤</span> <span>Anggota</span>
        </li>
        <li onclick="location.href='<?= $base ?>views/peminjaman/index.php'">
            <span class="icon">📝</span> <span>Peminjaman</span>
        </li>
        <li onclick="location.href='<?= $base ?>views/pengembalian/index.php'">
            <span class="icon">↩️</span> <span>Pengembalian</span>
        </li>
        <li onclick="location.href='<?= $base ?>views/laporan/index.php'">
            <span class="icon">📊</span> <span>Laporan</span>
        </li>
        <li onclick="location.href='<?= $base ?>views/buku_digital.php'">
            <span class="icon">📱</span> <span>E-Book</span>
        </li>
    </ul>
</div>