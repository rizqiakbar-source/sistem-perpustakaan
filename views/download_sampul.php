<?php
// views/download_sampul.php - Download sampul buku dari Open Library
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');
require_once '../config/database.php';

// Ambil semua buku yang belum punya sampul
$sql = "SELECT buku_id, isbn FROM buku WHERE isbn IS NOT NULL AND isbn != ''";
$stmt = $pdo->query($sql);
$buku = $stmt->fetchAll();

$berhasil = 0;
$gagal = 0;

// Buat folder upload/sampul jika belum ada
$upload_dir = '../upload/sampul/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

foreach ($buku as $row) {
    $buku_id = $row['buku_id'];
    $isbn = $row['isbn'];
    
    // Bersihkan ISBN (hapus tanda strip)
    $isbn_clean = preg_replace('/[^0-9]/', '', $isbn);
    
    // URL sampul dari Open Library
    $url = "https://covers.openlibrary.org/b/isbn/{$isbn_clean}-L.jpg";
    
    // Tujuan penyimpanan (pakai folder upload)
    $destination = "../upload/sampul/{$buku_id}.jpg";
    
    // Cek apakah file sudah ada
    if (!file_exists($destination)) {
        // Download gambar
        $image = @file_get_contents($url);
        if ($image !== false) {
            // Cek apakah gambar valid (bukan placeholder)
            $temp_file = tempnam(sys_get_temp_dir(), 'cover');
            file_put_contents($temp_file, $image);
            $info = @getimagesize($temp_file);
            unlink($temp_file);
            
            if ($info !== false && $info[0] > 10 && $info[1] > 10) {
                file_put_contents($destination, $image);
                $berhasil++;
                echo "✅ Berhasil download sampul untuk buku ID: {$buku_id} (ISBN: {$isbn})<br>";
                flush();
                ob_flush();
            } else {
                $gagal++;
                echo "❌ Gagal: Sampul tidak ditemukan untuk buku ID: {$buku_id}<br>";
                flush();
                ob_flush();
            }
        } else {
            $gagal++;
            echo "❌ Gagal download untuk buku ID: {$buku_id}<br>";
            flush();
            ob_flush();
        }
    } else {
        echo "⏭️ Sampul sudah ada untuk buku ID: {$buku_id}<br>";
        flush();
        ob_flush();
    }
    
    // Beri jeda 0.3 detik agar tidak overload server
    usleep(300000);
}

echo "<hr>";
echo "<h3>📊 RINGKASAN</h3>";
echo "✅ Berhasil: {$berhasil} buku<br>";
echo "❌ Gagal: {$gagal} buku<br>";
?>