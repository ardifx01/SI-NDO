-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2025 at 05:35 AM
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
-- Database: `sindo`
--

-- --------------------------------------------------------

--
-- Table structure for table `acara`
--

CREATE TABLE `acara` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acara`
--

INSERT INTO `acara` (`id`, `judul`, `deskripsi`, `tanggal_mulai`, `tanggal_selesai`, `lokasi`, `user_id`) VALUES
(1, 'Seminar Motivasi', 'Motivasi Hidup', '2025-08-14 14:18:00', '2025-08-14 19:18:00', 'Online Via Zoom', 1);

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_kuliah`
--

CREATE TABLE `jadwal_kuliah` (
  `id` int(11) NOT NULL,
  `mk_id` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_kuliah`
--

INSERT INTO `jadwal_kuliah` (`id`, `mk_id`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(1, 1, 'Rabu', '07:30:00', '10:00:00'),
(2, 2, 'Jumat', '14:40:00', '16:20:00'),
(3, 3, 'Senin', '07:30:00', '10:50:00'),
(4, 4, 'Selasa', '09:00:00', '11:40:00'),
(5, 5, 'Selasa', '10:40:00', '12:20:00'),
(6, 6, 'Rabu', '14:40:00', '16:20:00'),
(7, 7, 'Kamis', '10:00:00', '12:30:00'),
(8, 8, 'Selasa', '07:30:00', '09:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL,
  `dosen` varchar(100) NOT NULL,
  `ruangan` varchar(20) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id`, `kode_mk`, `nama_mk`, `sks`, `dosen`, `ruangan`, `user_id`) VALUES
(1, 'INF622107', 'Matematika Diskrit', 3, 'Masjudin, S.T., M.Eng.', 'BR 301', 1),
(2, 'INF622105', 'Dasar Dasar Pemrograman', 2, 'Nanang Krisdianto, S.T., M.Kom.', 'BR 301', 1),
(3, 'INF622101', 'Algoritma Dan Pemrograman', 4, 'Alim Herdiansyah, S.T., M.Kom. & Febriyanti Darnis, S.ST., M.Kom.', 'Virtual (Online)', 1),
(4, 'INF622103', 'Bahasa Pemrograman I', 2, 'Fitri Damyati, S.T., M.M.', 'Lab. Informatika', 1),
(5, 'INF622109', 'Sistem Digital', 2, 'Yulian Ansori, S. Kom, M. Kom', 'BR 301', 1),
(6, 'UNI622403', 'Teknologi Dan Transformasi Digital', 2, 'Febriyanti Darnis, S. ST., M.Kom. & Czidni Sika Azkia, M.Kom.', 'BR 302', 1),
(7, 'TEK622101', 'Kalkulus', 3, 'Mukhtar, S.Si., M.Pd., Ph.D.', 'COE1.304', 1),
(8, 'UNI622101', 'Pendidikan Agama', 2, 'Nurul Fadhilah, S.Pd., M.Pd.', 'Virtual (Online)', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 1, '68be5b6577e7db3ede44364366c514a9aa1e56c3a3e7145f2568a3b2f426ddbc', '2025-08-11 10:30:00', '2025-08-11 07:30:00'),
(2, 1, '2401e077a4e58ce97489b7e4e103fb09e8dd48e2c19c120b090efe18b84df281', '2025-08-11 10:30:09', '2025-08-11 07:30:09'),
(3, 1, 'd40c0ba1b26c9940c3206093fe4a13ed4d09daa2df3c8f6f1195e5aef6fa9e6c', '2025-08-11 10:44:51', '2025-08-11 07:44:51');

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `mk_id` int(11) NOT NULL,
  `deadline` datetime NOT NULL,
  `status` enum('Belum Dimulai','Dalam Pengerjaan','Selesai') DEFAULT 'Belum Dimulai',
  `prioritas` enum('Rendah','Sedang','Tinggi') DEFAULT 'Sedang',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`id`, `judul`, `deskripsi`, `mk_id`, `deadline`, `status`, `prioritas`, `user_id`, `created_at`) VALUES
(1, 'Membuat Halaman Login', 'Buat dengan PHP dan Sql', 2, '2025-08-12 06:00:00', 'Selesai', 'Sedang', 1, '2025-08-10 14:57:25'),
(2, 'buat logout.php', 'pake vscode', 3, '2025-08-12 14:05:00', 'Selesai', 'Sedang', 1, '2025-08-11 07:05:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `prodi` varchar(50) DEFAULT NULL,
  `fakultas` varchar(50) DEFAULT NULL,
  `semester` int(2) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `nim`, `prodi`, `fakultas`, `semester`, `email`, `created_at`) VALUES
(1, 'firdyridho', '$2y$10$YIdfYOH0kRRu3kld/GpWf.Sz1O0VNXe6nBYceFzfQqTOMGmOjFAYe', 'firdyridho', '3337250018', 'Informatika', 'Teknik', 1, 'firdyridho9@gmail.com', '2025-08-10 13:30:44'),
(2, 'ndo', '$2y$10$FNtLGQxuqStFqfrw192mLeLIzrn0ZajaCWHsmGeG90.PdAktGb8ne', 'ridho', NULL, NULL, NULL, NULL, 'ndo@gmail.com', '2025-08-11 06:42:37'),
(3, 'zaki123', '$2y$10$ImjEu1/SliyMsUDtcfSafeZfbGinrLJWyg16FId3uiFYEakYRZ.f.', 'zakie', NULL, 'Hukum Tata Negara', 'Fakultas Hukum', 1, 'zakie@gmail.com', '2025-08-17 14:37:07'),
(4, 'Fanujiya', '$2y$10$N0ClRsIex.3s/S9Ldu1icO.Z9LAF7yyHRFlYRLTfnXlpuZXC9KQfy', 'Fanujiya Kidna Khasal', NULL, NULL, NULL, NULL, 'khasalfanujiya30@gmail.com', '2025-08-18 00:22:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acara`
--
ALTER TABLE `acara`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mk_id` (`mk_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_mk` (`kode_mk`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mk_id` (`mk_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acara`
--
ALTER TABLE `acara`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acara`
--
ALTER TABLE `acara`
  ADD CONSTRAINT `acara_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  ADD CONSTRAINT `jadwal_kuliah_ibfk_1` FOREIGN KEY (`mk_id`) REFERENCES `mata_kuliah` (`id`);

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD CONSTRAINT `mata_kuliah_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`mk_id`) REFERENCES `mata_kuliah` (`id`),
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
