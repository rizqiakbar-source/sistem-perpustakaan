-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Jun 2026 pada 16.00
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `anggota_id` int(11) NOT NULL,
  `nis_nim` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tanggal_daftar` date NOT NULL,
  `status_aktif` enum('aktif','nonaktif','blokir') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`anggota_id`, `nis_nim`, `nama_lengkap`, `email`, `no_telepon`, `alamat`, `jenis_kelamin`, `tanggal_daftar`, `status_aktif`, `created_at`) VALUES
(1, '2023001', 'Ahmad Fauzi', 'ahmad@email.com', '08123456789', 'Jl. Merdeka No. 10', 'L', '2024-01-15', 'aktif', '2026-06-19 13:02:57'),
(2, '2023002', 'Dewi Kartika', 'dewi@email.com', '08129876543', 'Jl. Sudirman No. 20', 'P', '2024-02-01', 'aktif', '2026-06-19 13:02:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `buku_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` int(4) DEFAULT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 1,
  `lokasi_rak` varchar(20) DEFAULT NULL,
  `status` enum('tersedia','dipinjam','rusak','hilang') NOT NULL DEFAULT 'tersedia',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`buku_id`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `isbn`, `kategori_id`, `stok`, `lokasi_rak`, `status`, `created_at`) VALUES
(1, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, '9789793062792', 1, 5, 'A1', 'tersedia', '2026-06-19 13:02:56'),
(2, 'Sapiens', 'Yuval Noah Harari', 'Gramedia', 2015, '9780062316097', 2, 3, 'B2', 'tersedia', '2026-06-19 13:02:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_peminjaman`
--

CREATE TABLE `detail_peminjaman` (
  `detail_id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `tanggal_kembali_aktual` date DEFAULT NULL,
  `denda` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status_kembali` enum('belum','sudah') NOT NULL DEFAULT 'belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_peminjaman`
--

INSERT INTO `detail_peminjaman` (`detail_id`, `peminjaman_id`, `buku_id`, `tanggal_kembali_aktual`, `denda`, `status_kembali`) VALUES
(1, 1, 1, '2026-06-19', 0.00, 'sudah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_buku`
--

CREATE TABLE `kategori_buku` (
  `kategori_id` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_buku`
--

INSERT INTO `kategori_buku` (`kategori_id`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Fiksi', 'Karya sastra berupa cerita imajinatif'),
(2, 'Non-Fiksi', 'Buku berdasarkan fakta dan realita'),
(3, 'Pendidikan', 'Buku pelajaran dan akademis'),
(4, 'Teknologi', 'Buku tentang teknologi dan komputer');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `peminjaman_id` int(11) NOT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal_pinjam` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_jatuh_tempo` date NOT NULL,
  `status` enum('dipinjam','dikembalikan','terlambat') NOT NULL DEFAULT 'dipinjam',
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`peminjaman_id`, `kode_transaksi`, `anggota_id`, `user_id`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `status`, `catatan`) VALUES
(1, 'PJM/20260619/790', 1, 3, '2026-06-19 20:53:32', '2026-06-26', 'dikembalikan', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengembalian`
--

CREATE TABLE `pengembalian` (
  `pengembalian_id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal_kembali` datetime NOT NULL DEFAULT current_timestamp(),
  `total_denda` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('lengkap','sebagian') NOT NULL DEFAULT 'lengkap',
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengembalian`
--

INSERT INTO `pengembalian` (`pengembalian_id`, `peminjaman_id`, `user_id`, `tanggal_kembali`, `total_denda`, `status`, `catatan`) VALUES
(1, 1, 3, '2026-06-19 20:57:40', 0.00, 'lengkap', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','petugas') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(3, 'admin', '0192023a7bbd73250516f069df18b500', 'Budi Santoso', 'admin', '2026-06-19 13:36:29');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`anggota_id`),
  ADD UNIQUE KEY `nis_nim_unique` (`nis_nim`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`buku_id`),
  ADD UNIQUE KEY `isbn_unique` (`isbn`);

--
-- Indeks untuk tabel `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  ADD PRIMARY KEY (`detail_id`),
  ADD UNIQUE KEY `unique_pinjam_buku` (`peminjaman_id`,`buku_id`);

--
-- Indeks untuk tabel `kategori_buku`
--
ALTER TABLE `kategori_buku`
  ADD PRIMARY KEY (`kategori_id`),
  ADD UNIQUE KEY `nama_kategori_unique` (`nama_kategori`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`peminjaman_id`),
  ADD UNIQUE KEY `kode_transaksi_unique` (`kode_transaksi`);

--
-- Indeks untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`pengembalian_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username_unique` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `anggota_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `buku_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `detail_peminjaman`
--
ALTER TABLE `detail_peminjaman`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `kategori_buku`
--
ALTER TABLE `kategori_buku`
  MODIFY `kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `peminjaman_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `pengembalian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
