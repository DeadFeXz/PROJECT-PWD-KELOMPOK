-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Bulan Mei 2026 pada 04.48
-- Versi server: 10.4.17-MariaDB
-- Versi PHP: 8.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `makananupn_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `harga_asli` int(11) DEFAULT NULL,
  `resto` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `terjual` int(11) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 4.0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id`, `nama`, `kategori`, `harga`, `harga_asli`, `resto`, `deskripsi`, `gambar`, `terjual`, `rating`, `created_at`) VALUES
(1, 'Bubur Ayam Special', 'bubur-ayam', 12000, 18000, 'Bulu Ayam Special', NULL, 'assets/bubur ayam special.png', 85, '4.7', '2026-04-01 04:53:48'),
(2, 'Bubur Ayam Komplit', 'bubur-ayam', 15000, 22500, 'Bulu Ayam Komplit', NULL, 'assets/bubur ayam komplit.png', 79, '4.5', '2026-04-01 04:53:48'),
(3, 'Mie Ayam Bakso', 'mie', 13000, 19500, 'Mie Ayam 88', NULL, 'assets/mie ayam bakso.png', 125, '4.8', '2026-04-01 04:53:48'),
(4, 'Mie Ayam Pangsit', 'mie', 14000, 21000, 'Mie Ayam Mas Yono', NULL, 'assets/mie ayam pangsit.png', 115, '4.6', '2026-04-01 04:53:48'),
(5, 'Nasi Ayam Geprek', 'nasi-ayam', 18000, 27000, 'Geprek Bensu', NULL, 'assets/nasi ayam geprek.png', 210, '4.9', '2026-04-01 04:53:48'),
(6, 'Nasi Ayam Bakar', 'nasi-ayam', 20000, 30000, 'Ayam Bakar Pak Boss', NULL, 'assets/nasi ayam bakar.png', 156, '4.7', '2026-04-01 04:53:48'),
(7, 'Nasi Goreng Spesial', 'nasi-goreng', 17000, 25500, 'Warung Makmur', NULL, 'assets/nasi goreng special.png', 188, '4.7', '2026-04-01 04:53:48'),
(8, 'Nasi Goreng Seafood', 'nasi-goreng', 19000, 28500, 'Nasi Goreng Kebon Nanas', NULL, 'assets/nasi goreng seafood.png', 143, '4.6', '2026-04-01 04:53:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `menu_id` int(11) NOT NULL,
  `menu_nama` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `nama_pemesan` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `no_telpon` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `no_telpon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `fk_orders_user` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
