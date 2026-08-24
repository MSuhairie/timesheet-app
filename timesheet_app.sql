-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 24, 2026 at 02:02 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `timesheet_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `activity_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `work_place` enum('WFO','WFH') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'WFO',
  `task` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Planned','In Progress','Completed','Pending','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Planned',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `activity_date`, `check_in`, `check_out`, `work_place`, `task`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-08-03', '08:07:00', '17:31:00', 'WFO', 'Melakukan perkenalan dengan tim serta mempelajari modul SISS, workflow dan konfigurasi sistem', '', 'Completed', '2026-08-21 06:07:09', '2026-08-21 06:07:09'),
(2, 1, '2026-08-04', '07:59:00', '17:31:00', 'WFO', 'Melakukan instalasi Visual Studio Community 2017 dan SSDT versi 15.9.10, serta verifikasi untuk memastikan instalasi berhasil dan dapat digunakan.', '', 'Completed', '2026-08-21 06:08:30', '2026-08-21 06:08:30'),
(3, 1, '2026-08-05', '08:02:00', '17:31:00', 'WFO', 'Melakukan instalasi SSMS dan Office 2021 LTSC 64 bit, serta verifikasi untuk memastikan instalasi berhasil dan dapat digunakan.', '', 'Completed', '2026-08-21 06:08:57', '2026-08-21 06:08:57'),
(4, 1, '2026-08-06', '08:00:00', '17:40:00', 'WFO', 'Melakukan instalasi PL/SQL 2016 32 bit, OCI 11g 32 bit dan Visual Studio Code, serta verifikasi untuk memastikan instalasi berhasil dan dapat digunakan.', '', 'Completed', '2026-08-21 06:09:52', '2026-08-21 06:09:52'),
(5, 1, '2026-08-07', '08:03:00', '18:26:00', 'WFO', 'Mempelajari fitur Visual Studio, SSMS, dan PL/SQL, serta memahami alur proses pembiayaan kendaraan dan dana.', '', 'Completed', '2026-08-21 06:10:21', '2026-08-21 06:10:21'),
(6, 1, '2026-08-10', '08:11:00', '17:31:00', 'WFO', 'Melakukan monitoring job serta mempelajari tools PL/SQL dan Oracle untuk memahami proses dan pengelolaan database.', '', 'Completed', '2026-08-21 06:11:25', '2026-08-21 06:11:25'),
(7, 1, '2026-08-11', '08:09:00', '17:32:00', 'WFO', 'Melakukan monitoring job berkala serta pengecekan ORA-00001 Error pada job sync_coll_rpt_recovery.', '', 'Completed', '2026-08-21 06:11:51', '2026-08-21 06:11:51'),
(8, 1, '2026-08-12', '08:06:00', '17:35:00', 'WFO', 'Melakukan monitoring job berkala serta pengecekan ORA-06512 Error pada Job CMS-Report_Detail_KYC dan ORA-06550 Error pada Job JF-Report Aging Core Non Aktif dan Deployment SSIS Package – Report Portfolio, Deskcoll RoboAI, dan CMS RPT Entry Result', '', 'Completed', '2026-08-21 06:12:19', '2026-08-21 06:12:19'),
(9, 1, '2026-08-13', '08:03:00', '18:21:00', 'WFO', 'Melakukan Monitoring Job, pengecekan error pada job Robo-Deskcoll_RoboAi_AG, serta melakukan deployment SSIS Package `Proses_Installment_MF_BMRI.dtsx`.', '', 'Completed', '2026-08-21 06:13:39', '2026-08-21 06:13:39'),
(10, 1, '2026-08-14', '08:07:00', '17:31:00', 'WFO', 'Melakukan monitoring job, disable job CMS-Report_Detail_KYC, serta pengecekan error pada job JF-Repo_SOLD di step Proses_WO_PMT (6) dan Proses_WO_BMRI (5).', '', 'Completed', '2026-08-21 06:14:15', '2026-08-21 06:14:15'),
(11, 1, '2026-08-15', '08:30:00', '17:30:00', 'WFH', 'Melakukan monitoring job dan Pengecekan Error pada Job sync_coll_rpt_recovery', '', 'Completed', '2026-08-21 06:14:41', '2026-08-24 10:26:44'),
(12, 1, '2026-08-16', '07:00:00', '22:00:00', 'WFO', 'Melakukan monitoring job, mengikuti meeting upgrade Oracle Muftech Prod, melakukan disable dan enable job TOAD berdasarkan list yang telah dibuat, serta menjalankan job Report-Aging_QuickCount_Daily_AM.', '', 'Completed', '2026-08-21 06:15:08', '2026-08-21 06:15:08'),
(13, 1, '2026-08-18', '08:05:00', '16:49:00', 'WFO', 'Melakukan monitoring job secara berkala', '', 'Completed', '2026-08-24 03:10:23', '2026-08-24 03:10:23'),
(17, 1, '2026-08-19', '08:00:00', '17:33:00', 'WFO', 'Melakukan monitoring job, Disable dan enable Job TOAD berdasarkan daftar job yang telah dibuat, serta menjalankan Job Report-Aging_QuickCount_Daily_AM. Melakukan pengecekan Job Error AM-InsrInvoiceAM-B2BInsrInvoice-AM2AM dan AM-RekonInsrAM-B2b_Insr_Reconsile-AM2AM, serta menjalankan kedua job tersebut.', '', 'Completed', '2026-08-24 03:15:41', '2026-08-24 03:15:41'),
(18, 1, '2026-08-20', '08:12:00', '17:31:00', 'WFO', 'Melakukan pengecekan Job Error JF-BATCH_2_PAY BMR, Step Proses_Fee_BMR, dengan error exact fetch returns more than requested number of rows.', '', 'Completed', '2026-08-24 03:17:08', '2026-08-24 03:17:08'),
(19, 1, '2026-08-21', '08:03:00', '17:32:00', 'WFO', 'Melakukan monitoring job serta pengecekan Job Error JF-Disburse_New_JF_BMR dan Report-PPD_Count.', '', 'Completed', '2026-08-24 03:17:54', '2026-08-24 10:30:30'),
(22, 1, '2026-08-24', '07:34:00', '17:38:00', 'WFO', 'Melakukan monitoring job dan Pengecekan Job Error JF-BATCH_2_PAY BMR Step Proses_Fee_BMR exact fetch returns more than requested number of rows', '', 'Completed', '2026-08-24 13:14:54', '2026-08-24 13:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departemen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atasan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perusahaan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perusahaan_alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perusahaan_telp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `nik`, `departemen`, `jabatan`, `project`, `client`, `atasan`, `perusahaan`, `perusahaan_alamat`, `perusahaan_telp`, `created_at`, `updated_at`) VALUES
(1, 'M. Suhairie', '15997811', 'PS Consultant', 'Java Developer', 'IT Release Management', '-', '-', 'PT. Indocyber Global Teknologi', 'Komplek Pertokoan Aldiron Hero Blok C No 10 Jl. Daan Mogot Kav. 119, Jakarta Barat 11460', 'Telp. (+62-21) 566 3705, Fax. (+62-21) 566 3704', '2026-08-21 04:36:00', '2026-08-24 10:32:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`activity_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
