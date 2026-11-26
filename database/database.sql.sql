-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Nov 26, 2026 at 05:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apotek_smart_ed`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventaris`
--

CREATE TABLE `inventaris` (
  `id` int(11) NOT NULL,
  `nama_obat` varchar(100) DEFAULT NULL,
  `no_batch` varchar(50) DEFAULT NULL,
  `sisa_stok` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `expired` date DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT '-',
  `supplier` varchar(100) DEFAULT '-',
  `lokasi_penyimpanan` varchar(100) DEFAULT '-',
  `tanggal_input` datetime DEFAULT current_timestamp(),
  `satuan` varchar(30) DEFAULT 'Tablet'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventaris`
--

INSERT INTO `inventaris` (`id`, `nama_obat`, `no_batch`, `sisa_stok`, `status`, `expired`, `kategori`, `lokasi`, `supplier`, `lokasi_penyimpanan`, `tanggal_input`, `satuan`) VALUES
(1, 'Amoxicillin 500mg', 'B-29930-AX', 20, 'Tersedia', '2026-03-10', 'Tablet & Kapsul', 'Rak B-2', 'PT Kimi Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(8, 'Vitamin C 1000mg', 'BATCH-003-VIT', 100, 'Tersedia', '2028-12-12', 'Tablet & Kapsul', 'Rak A-3', 'PT. Sanbe Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(11, 'cetirizin 500mg', 'C-CE0192', 100, 'Tersedia', '2029-12-12', 'Tablet & Kapsul', 'Rak A-3', 'PT. Sanbe Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(13, 'Paracetamol 500mg', 'B-101-PCT', 10, 'Tersedia', '2029-08-15', 'Tablet & Kapsul', 'Rak A-1', 'PT. Kimia Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(14, 'Omeprazole 20mg', 'B-404-OMP', 120, 'Tersedia', '2028-05-15', 'Tablet & Kapsul', 'Rak A-2', 'PT. Dexa Medica', '-', '2026-11-25 13:16:42', 'Tablet'),
(15, 'Antasida Doen', 'B-606-ATD', 10, 'Karantina', '2026-07-10', 'Tablet & Kapsul', 'Rak A-2', 'PT. Kimia Farma', 'Gudang Karantina', '2026-11-25 13:16:42', 'Tablet'),
(16, 'Ranitidine 150mg', 'B-707-RNT', 90, 'Tersedia', '2026-06-25', 'Tablet & Kapsul', 'Rak B-3', 'PT. Hexpharm Jaya', '-', '2026-11-25 13:16:42', 'Tablet'),
(17, 'Sirup Ibuprofen 120mg/5ml', 'B-808-IBU', 45, 'Tersedia', '2027-01-21', 'Tablet & Kapsul', 'Rak D-1', 'PT. Sanbe Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(18, 'Metformin 500mg', 'B-909-MTF', 200, 'Tersedia', '2026-07-01', 'Tablet & Kapsul', 'Rak B-4', 'PT. Kalbe Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(19, 'Acyclovir 5%', 'B-010-ACY', 35, 'Karantina', '2026-06-12', 'Tablet & Kapsul', 'Rak E-1', 'PT. Kimia Farma', 'Gudang Karantina', '2026-11-25 13:16:42', 'Tablet'),
(20, 'Asam Mefenamat 500mg', 'B-111-ASM', 90, 'Karantina', '2026-03-10', 'Tablet & Kapsul', 'Rak B-2', 'PT. Kimia Farma', 'Gudang Karantina', '2026-11-25 13:16:42', 'Tablet'),
(21, 'Captopril 25mg', 'B-222-CPT', 300, 'Karantina', '2026-01-12', 'Tablet & Kapsul', 'Rak A-1', 'PT. Dexa Medica', 'Gudang Karantina', '2026-11-25 13:16:42', 'Tablet'),
(22, 'Simvastatin 10mg', 'B-444-SMV', 180, 'Karantina', '2026-04-15', 'Tablet & Kapsul', 'Rak A-3', 'PT. Hexpharm Jaya', 'Gudang Karantina', '2026-11-25 13:16:42', 'Tablet'),
(23, 'Neurobion Forte (Vit B)', 'B-555-NRB', 40, 'Karantina', '2025-10-10', 'Tablet & Kapsul', 'Rak C-2', 'PT. Merck Tbk', 'Gudang Karantina', '2026-11-25 13:16:42', 'Tablet'),
(24, 'Betadine Solution 30ml', 'B-666-BTD', 40, 'Karantina', '2026-02-01', 'Tablet & Kapsul', 'Rak B-3', 'PT. Mahakam Beta Farma', 'Gudang Karantina', '2026-11-25 13:16:42', 'Tablet'),
(25, 'Zinc', 'ZN1000C', 10, 'Tersedia', '2029-07-11', 'Tablet & Kapsul', 'Rak A-3', 'PT Kimia Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(26, 'Zinc', 'ZN1000B', 100, 'Tersedia', '2027-01-10', 'Tablet & Kapsul', 'Rak A-3', 'PT Kimia Farma', '-', '2026-11-25 13:16:42', 'Tablet'),
(27, 'Promag', 'P091HO', 20, 'Tersedia', '2028-07-29', 'Tablet & Kapsul', 'Rak A-3', 'PT Kimia Farma', '-', '2026-11-25 22:14:09', 'Tablet');

-- --------------------------------------------------------

--
-- Table structure for table `setting_profile`
--

CREATE TABLE `setting_profile` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT '',
  `jabatan` varchar(100) DEFAULT '',
  `email` varchar(150) DEFAULT '',
  `sipa` varchar(100) DEFAULT '',
  `nama_apotek` varchar(150) DEFAULT 'Apotek Bintang Surya',
  `telp_apotek` varchar(50) DEFAULT '',
  `alamat_apotek` text DEFAULT NULL,
  `password_akun` varchar(255) DEFAULT '',
  `stok_min` int(11) DEFAULT 20,
  `ed_alert` int(11) DEFAULT 3,
  `last_email_sent` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `setting_profile`
--

INSERT INTO `setting_profile` (`id`, `nama_lengkap`, `jabatan`, `email`, `sipa`, `nama_apotek`, `telp_apotek`, `alamat_apotek`, `password_akun`, `stok_min`, `ed_alert`, `last_email_sent`) VALUES
(1, 'muhammad azis', 'Apoteker Utama & Manajer Operasional', 'amin665@smk.belajar.id', '', 'Apotek Bintang Surya', '', NULL, '', 20, 3, '2026-11-26 17:23:33');

-- --------------------------------------------------------

--
-- Table structure for table `stok_obat`
--

CREATE TABLE `stok_obat` (
  `id` int(11) NOT NULL,
  `nama_obat` varchar(100) NOT NULL,
  `no_batch` varchar(50) NOT NULL,
  `stok_masuk` int(11) NOT NULL,
  `satuan` varchar(30) NOT NULL,
  `expired` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` varchar(30) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `role`, `username`, `email`, `password`) VALUES
(1, 'muhammad azis', 'Admin', 'adminajis01', 'amin665@smk.belajar.id', '$2y$10$htFHIAknN3KWvO2Wr1NAn.6R210BnyEkjnmn4Ee1AL.oXd2v.opyS'),
(4, 'Sagit Fatur', 'Admin', 'Sagit F', 'sagit123@apotek.gmail.com', '$2y$10$YXm1n5jsA0x9kQohke51IOHJIBBnT6G8svcml0OetNc7Oc47ytBN2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `setting_profile`
--
ALTER TABLE `setting_profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stok_obat`
--
ALTER TABLE `stok_obat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `stok_obat`
--
ALTER TABLE `stok_obat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
