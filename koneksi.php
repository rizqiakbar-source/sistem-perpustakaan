<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'perpustakaan';

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Optional: set karakter agar tidak error saat input data Indonesia
mysqli_set_charset($koneksi, "utf8");
?>