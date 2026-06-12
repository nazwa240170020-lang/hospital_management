-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 04:29 PM
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
-- Database: `db_rumahsakit`
--

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `nip` int(11) NOT NULL,
  `nama_dokter` varchar(100) NOT NULL,
  `spesialis` varchar(50) DEFAULT NULL,
  `no_telp` varchar(18) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`nip`, `nama_dokter`, `spesialis`, `no_telp`, `alamat`, `id_user`) VALUES
(101, 'Dr. Arka Pratama', 'Spesialis Penyakit Dalam', '081234567890', 'Jl. Sakura No.12,Medan', NULL),
(102, 'Dr. Naya Putri', 'Spesialis Anak', '081298765432', 'Jl. Magnolia No.8, Bandung', NULL),
(103, 'Dr. Reyhan Aulia', 'Spesialis Bedah', '081345678901', 'Jl. Kenanga No.5, Surabaya', NULL),
(104, 'Dr. Kaela Larasati', 'Spesialis Kandungan', '081456789012', 'Jl. Anggrek No.10, Yogyakarta', NULL),
(105, 'Dr. Zaki Maulana', 'Spesialis THT', '081567890123', 'Jl. Melur No.7, Semarang', NULL),
(106, 'Dr. Alana Febrianti', 'Spesialis Saraf', '081678901234', 'Jl. Dahlia No.3, Medan', NULL),
(107, 'Dr. Nazwa Putri Aulia', 'Penyakit Bagian Dalam', '0896-5452-1599', 'Langkat', 3);

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_dokter`
--

CREATE TABLE `jadwal_dokter` (
  `id_jadwal` int(11) NOT NULL,
  `nip` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_dokter`
--

INSERT INTO `jadwal_dokter` (`id_jadwal`, `nip`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(1, 106, 'Senin', '03:00:00', '09:00:00'),
(2, 107, 'Selasa', '06:00:00', '08:00:00'),
(3, 102, 'Kamis', '08:00:00', '09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `kamar`
--

CREATE TABLE `kamar` (
  `nomor_kamar` int(11) NOT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `status` enum('Kosong','Terisi') DEFAULT 'Kosong',
  `tipe_kamar` varchar(100) DEFAULT NULL,
  `lantai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kamar`
--

INSERT INTO `kamar` (`nomor_kamar`, `harga`, `status`, `tipe_kamar`, `lantai`) VALUES
(101, 5000000.00, 'Kosong', 'VVIP', 1),
(102, 5000000.00, 'Terisi', 'VVIP', 1),
(103, 3500000.00, 'Kosong', 'VIP', 1),
(104, 3500000.00, 'Terisi', 'VIP', 1),
(201, 2500000.00, 'Terisi', 'Kelas 1', 2),
(202, 2500000.00, 'Kosong', 'Kelas 1', 2),
(203, 1500000.00, 'Kosong', 'Kelas 2', 2),
(204, 1500000.00, 'Kosong', 'Kelas 2', 2),
(205, 1000000.00, 'Kosong', 'Kelas 3', 2),
(206, 1000000.00, 'Kosong', 'Kelas 3', 2),
(301, 7000000.00, 'Terisi', 'ICU', 3),
(302, 7000000.00, 'Terisi', 'ICU', 3),
(303, 5000000.00, 'Kosong', 'Suite', 3),
(304, 3500000.00, 'Terisi', 'VIP', 3),
(305, 2500000.00, 'Kosong', 'Kelas 1', 3),
(306, 1500000.00, 'Kosong', 'Kelas 2', 3),
(307, 1000000.00, 'Kosong', 'Kelas 3', 3),
(308, 3500000.00, 'Kosong', 'VIP', 4),
(309, 5000000.00, 'Terisi', 'VVIP', 4),
(310, 7000000.00, 'Kosong', 'ICU', 4);

-- --------------------------------------------------------

--
-- Table structure for table `obat`
--

CREATE TABLE `obat` (
  `kode_obat` int(11) NOT NULL,
  `nama_obat` varchar(100) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `jenis_obat` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `obat`
--

INSERT INTO `obat` (`kode_obat`, `nama_obat`, `harga`, `stok`, `jenis_obat`) VALUES
(241, 'Amoxilliin', 5000.00, 99, 'Kapsul'),
(242, 'Cefadroxil', 5000.00, 98, 'Kapsul'),
(243, 'Paracetamol', 5000.00, 100, 'Analgesik'),
(244, 'Cefadroxil', 12000.00, 50, 'Antibiotik'),
(245, 'Vitamin C', 8000.00, 200, 'Suplemen'),
(246, 'Ibuprofen', 7000.00, 75, 'Analgesik'),
(247, 'Cetirizine', 6000.00, 151, 'Antihistamin'),
(248, 'Metformin', 15000.00, 60, 'Antidiabetik'),
(249, 'Amoxicillin-Clavulanate', 18000.00, 39, 'Antibiotik'),
(250, 'Omeprazole', 10000.00, 90, 'Antasida'),
(251, 'Aspirin', 5500.00, 120, 'Analgesik'),
(252, 'Loratadine', 7500.00, 80, 'Antihistamin'),
(5001, 'Paracetamol', 2500.00, 100, 'Tablet'),
(5002, 'Amoxicillin', 5000.00, 48, 'Kapsul'),
(5003, 'Vitamin C', 1500.00, 200, 'Tablet'),
(5004, 'Ibuprofen', 3500.00, 80, 'Tablet'),
(5005, 'Cough Syrup', 20000.00, 61, 'Sirup'),
(5006, 'Metformin', 4000.00, 70, 'Tablet'),
(5007, 'Cetirizine', 3000.00, 121, 'Tablet'),
(5008, 'Salbutamol', 18000.00, 40, 'Inhaler'),
(5009, 'Omeprazole', 7000.00, 90, 'Kapsul'),
(5010, 'Multivitamin', 10000.00, 150, 'Tablet');

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `kode_pasien` int(11) NOT NULL,
  `nama_pasien` varchar(100) NOT NULL,
  `umur` int(3) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(18) DEFAULT NULL,
  `gol_darah` varchar(3) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nip` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`kode_pasien`, `nama_pasien`, `umur`, `jenis_kelamin`, `alamat`, `no_telp`, `gol_darah`, `id_user`, `nip`) VALUES
(2026001, 'Aurelius Sky', 25, 'L', 'Jl. Merpati No. 12, Jakarta', '081234567890', 'AB', NULL, NULL),
(2026002, 'Luna Vega', 30, 'P', 'Jl. Kenanga No. 8, Bandung', '081298765432', 'A', NULL, NULL),
(2026003, 'Kai Orion', 28, 'L', 'Jl. Melati No. 21, Surabaya', '081212345678', 'B', NULL, NULL),
(2026004, 'Nova Celeste', 22, 'P', 'Jl. Mawar No. 5, Yogyakarta', '081276543210', 'AB', NULL, NULL),
(2026005, 'Rafael Storm', 35, 'L', 'Jl. Anggrek No. 14, Medan', '081223344556', 'O', NULL, NULL),
(2026006, 'Zara Phoenix', 27, 'P', 'Jl. Dahlia No. 9, Semarang', '081234443322', 'A', NULL, NULL),
(2026007, 'Leo Titan', 31, 'L', 'Jl. Sakura No. 3, Bali', '081255566677', 'B', NULL, NULL),
(2026008, 'Nazwa Putri Aulia', 19, 'P', 'Langkat', '089654521599', '0', 2, NULL),
(2026009, 'Dimas Kurniawan', 21, 'L', 'Lhokseumawe', '081234567890', 'B', 7, NULL),
(2026010, 'Fachril Akbar', 19, 'L', 'Jl. Mangga No.4', '081234567890', 'B', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rawat_inap`
--

CREATE TABLE `rawat_inap` (
  `kode_rawat` int(11) NOT NULL,
  `kode_pasien` int(11) NOT NULL,
  `nip` int(11) NOT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `keluhan` varchar(200) DEFAULT NULL,
  `diagnosa` text DEFAULT NULL,
  `biaya` decimal(10,2) DEFAULT 0.00,
  `biaya_pemeriksaan` decimal(10,2) DEFAULT 0.00,
  `biaya_obat` decimal(10,2) DEFAULT 0.00,
  `nomor_kamar` int(11) DEFAULT NULL,
  `status` enum('Proses','Selesai') DEFAULT 'Proses',
  `kode_obat` int(11) DEFAULT NULL,
  `id_resep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rawat_inap`
--

INSERT INTO `rawat_inap` (`kode_rawat`, `kode_pasien`, `nip`, `tanggal_masuk`, `tanggal_keluar`, `keluhan`, `diagnosa`, `biaya`, `biaya_pemeriksaan`, `biaya_obat`, `nomor_kamar`, `status`, `kode_obat`, `id_resep`) VALUES
(2, 2026001, 106, '2026-01-03', '2026-01-06', NULL, 'Lambung', 45000.00, 0.00, 0.00, NULL, 'Proses', NULL, NULL),
(17, 2026008, 107, '2026-01-06', '2026-01-08', 'Pusing terus menerus', 'Darah rendah', 300000.00, 200000.00, 0.00, 102, 'Proses', NULL, NULL),
(19, 2026008, 107, '2026-01-06', NULL, 'Muntah-muntah', '', 3500000.00, 0.00, 0.00, 304, 'Proses', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `resep_obat`
--

CREATE TABLE `resep_obat` (
  `id_resep` int(11) NOT NULL,
  `kode_rawat` int(11) NOT NULL,
  `kode_obat` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resep_obat`
--

INSERT INTO `resep_obat` (`id_resep`, `kode_rawat`, `kode_obat`, `jumlah`, `subtotal`) VALUES
(37, 17, 5002, 2, 10000),
(38, 17, 249, 1, 18000),
(39, 17, 241, 1, 5000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','dokter','pasien') DEFAULT NULL,
  `nama` int(11) NOT NULL,
  `tanggal_daftar` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama`, `tanggal_daftar`) VALUES
(1, 'adminsaya', 'admin123', 'admin', 0, '2026-01-05'),
(2, 'pasiensaya', 'pasien123', 'pasien', 0, '2026-01-05'),
(3, 'doktersaya', 'dokter123', 'dokter', 0, '2026-01-05'),
(7, 'sayapasien', 'pasien321', 'pasien', 0, '2026-01-07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`nip`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `fk_jadwal_dokter` (`nip`);

--
-- Indexes for table `kamar`
--
ALTER TABLE `kamar`
  ADD PRIMARY KEY (`nomor_kamar`);

--
-- Indexes for table `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`kode_obat`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`kode_pasien`),
  ADD UNIQUE KEY `id_user` (`id_user`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `rawat_inap`
--
ALTER TABLE `rawat_inap`
  ADD PRIMARY KEY (`kode_rawat`),
  ADD UNIQUE KEY `nomor_kamar` (`nomor_kamar`),
  ADD UNIQUE KEY `kode_obat` (`kode_obat`),
  ADD UNIQUE KEY `resep_obat` (`id_resep`),
  ADD KEY `idx_kode_pasien` (`kode_pasien`),
  ADD KEY `idx_nip` (`nip`);

--
-- Indexes for table `resep_obat`
--
ALTER TABLE `resep_obat`
  ADD PRIMARY KEY (`id_resep`),
  ADD KEY `fk_resep_rawat` (`kode_rawat`),
  ADD KEY `fk_resep_obat` (`kode_obat`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `nip` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kamar`
--
ALTER TABLE `kamar`
  MODIFY `nomor_kamar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=311;

--
-- AUTO_INCREMENT for table `obat`
--
ALTER TABLE `obat`
  MODIFY `kode_obat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5011;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `kode_pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2026011;

--
-- AUTO_INCREMENT for table `rawat_inap`
--
ALTER TABLE `rawat_inap`
  MODIFY `kode_rawat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `resep_obat`
--
ALTER TABLE `resep_obat`
  MODIFY `id_resep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD CONSTRAINT `fk_jadwal_dokter` FOREIGN KEY (`nip`) REFERENCES `dokter` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rawat_inap`
--
ALTER TABLE `rawat_inap`
  ADD CONSTRAINT `rawat_inap_ibfk_1` FOREIGN KEY (`kode_pasien`) REFERENCES `pasien` (`kode_pasien`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rawat_inap_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `dokter` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resep_obat`
--
ALTER TABLE `resep_obat`
  ADD CONSTRAINT `fk_resep_obat` FOREIGN KEY (`kode_obat`) REFERENCES `obat` (`kode_obat`),
  ADD CONSTRAINT `fk_resep_rawat` FOREIGN KEY (`kode_rawat`) REFERENCES `rawat_inap` (`kode_rawat`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
