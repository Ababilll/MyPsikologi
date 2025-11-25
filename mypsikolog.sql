-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 25, 2025 at 04:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mypsikolog`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `antrian`
--

CREATE TABLE `antrian` (
  `id_antrian` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_konseling` int(11) NOT NULL,
  `waktuDaftar` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_konseling`
--

CREATE TABLE `hasil_konseling` (
  `id_hasil` int(11) NOT NULL,
  `id_konseling` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `rekomendasi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `id_psikolog` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_mulai` time(3) NOT NULL,
  `waktu_selesai` time(3) NOT NULL,
  `kuota` int(11) DEFAULT 4,
  `terisi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `id_psikolog`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `kuota`, `terisi`) VALUES
(1, 1, '2025-11-26', '08:00:00.000', '12:00:00.000', 4, 0),
(2, 2, '2025-11-26', '08:00:00.000', '12:00:00.000', 4, 0),
(3, 3, '2025-11-26', '08:00:00.000', '12:00:00.000', 4, 0),
(4, 1, '2025-11-27', '08:00:00.000', '12:00:00.000', 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `konseling`
--

CREATE TABLE `konseling` (
  `id_konseling` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_psikolog` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `periode` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_konseling` int(11) NOT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `jumlah` double NOT NULL,
  `tanggal` datetime NOT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nomor_telepon` varchar(20) NOT NULL,
  `tanggal_pesan` datetime DEFAULT current_timestamp(),
  `status_pemesanan` varchar(50) DEFAULT 'Menunggu Pembayaran'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expired` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama`, `email`, `username`, `password`, `reset_token`, `reset_expired`) VALUES
(1, '', 'jokowi@gmail.com', 'owiowi', '$2y$10$FDxAMlo/6sIxFY2.zb1zkuYW3R27d3O9srH0pWGbfEDgPCXrgWBqK', NULL, NULL),
(2, '', 'dhafapaksi@gmail.com', 'wowo', '$2y$10$M5dA/oPV8lamfYcUmafSJek5MOEWTgxbu2r7Lo6xBYJWAPdBLRMwW', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `psikolog`
--

CREATE TABLE `psikolog` (
  `id_psikolog` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `spesialisasi` varchar(255) DEFAULT NULL,
  `jadwalTersedia` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `psikolog`
--

INSERT INTO `psikolog` (`id_psikolog`, `nama`, `spesialisasi`, `jadwalTersedia`) VALUES
(1, 'Wito', 'Psikolog Klinis', NULL),
(2, 'Cessia', 'Psikolog Pendidikan', NULL),
(3, 'ZhanQ', 'Psikolog Konseling', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rekam_medis`
--

CREATE TABLE `rekam_medis` (
  `id_rekam` int(11) NOT NULL,
  `id_psikolog` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `diagnosa` text DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- Indexes for table `antrian`
--
ALTER TABLE `antrian`
  ADD PRIMARY KEY (`id_antrian`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_konseling` (`id_konseling`);

--
-- Indexes for table `hasil_konseling`
--
ALTER TABLE `hasil_konseling`
  ADD PRIMARY KEY (`id_hasil`),
  ADD KEY `id_konseling` (`id_konseling`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_psikolog` (`id_psikolog`);

--
-- Indexes for table `konseling`
--
ALTER TABLE `konseling`
  ADD PRIMARY KEY (`id_konseling`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_psikolog` (`id_psikolog`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_konseling` (`id_konseling`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email_2` (`email`,`username`);

--
-- Indexes for table `psikolog`
--
ALTER TABLE `psikolog`
  ADD PRIMARY KEY (`id_psikolog`);

--
-- Indexes for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD PRIMARY KEY (`id_rekam`),
  ADD KEY `id_psikolog` (`id_psikolog`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `antrian`
--
ALTER TABLE `antrian`
  MODIFY `id_antrian` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hasil_konseling`
--
ALTER TABLE `hasil_konseling`
  MODIFY `id_hasil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `konseling`
--
ALTER TABLE `konseling`
  MODIFY `id_konseling` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `psikolog`
--
ALTER TABLE `psikolog`
  MODIFY `id_psikolog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `id_rekam` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrian`
--
ALTER TABLE `antrian`
  ADD CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `antrian_ibfk_2` FOREIGN KEY (`id_konseling`) REFERENCES `konseling` (`id_konseling`);

--
-- Constraints for table `hasil_konseling`
--
ALTER TABLE `hasil_konseling`
  ADD CONSTRAINT `hasil_konseling_ibfk_1` FOREIGN KEY (`id_konseling`) REFERENCES `konseling` (`id_konseling`),
  ADD CONSTRAINT `hasil_konseling_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_psikolog`) REFERENCES `psikolog` (`id_psikolog`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `konseling`
--
ALTER TABLE `konseling`
  ADD CONSTRAINT `konseling_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `konseling_ibfk_2` FOREIGN KEY (`id_psikolog`) REFERENCES `psikolog` (`id_psikolog`),
  ADD CONSTRAINT `konseling_ibfk_3` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_konseling`) REFERENCES `konseling` (`id_konseling`);

--
-- Constraints for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`),
  ADD CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Constraints for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`id_psikolog`) REFERENCES `psikolog` (`id_psikolog`),
  ADD CONSTRAINT `rekam_medis_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
