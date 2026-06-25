<!-- views/sidebar_anggota.php -->
<div class="sidebar">
    <div class="brand">
        <h3>📚 PERPUSTAKAAN</h3>
        <small>Politeknik Negeri Lampung</small>
    </div>
    <ul class="menu">
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard_anggota.php' ? 'active' : '' ?>" 
            onclick="location.href='dashboard_anggota.php'">
            <span class="icon">📊</span> <span>Dashboard</span>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'buku_anggota.php' ? 'active' : '' ?>" 
            onclick="location.href='buku_anggota.php'">
            <span class="icon">📚</span> <span>Daftar Buku</span>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'riwayat_anggota.php' ? 'active' : '' ?>" 
            onclick="location.href='riwayat_anggota.php'">
            <span class="icon">📝</span> <span>Riwayat Pinjam</span>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'wishlist_anggota.php' ? 'active' : '' ?>" 
            onclick="location.href='wishlist_anggota.php'">
            <span class="icon">⭐</span> <span>Wishlist</span>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'scan_qr.php' ? 'active' : '' ?>" 
            onclick="location.href='scan_qr.php'">
            <span class="icon">📷</span> <span>Scan QR</span>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'buku_digital.php' ? 'active' : '' ?>" 
            onclick="location.href='../views/buku_digital.php'">
            <span class="icon">📱</span> <span>E-Book</span>
        </li>
    </ul>
</div>