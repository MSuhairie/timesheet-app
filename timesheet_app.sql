-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 01, 2026 at 05:49 AM
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `statusenabled` enum('t','f') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 't'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `activity_date`, `check_in`, `check_out`, `work_place`, `task`, `notes`, `status`, `created_at`, `updated_at`, `statusenabled`) VALUES
(1, 1, '2026-08-03', '08:07:00', '17:31:00', 'WFO', 'Melakukan perkenalan dengan tim serta mempelajari modul SISS, workflow dan konfigurasi sistem', '', 'Completed', '2026-08-21 06:07:09', '2026-08-21 06:07:09', 't'),
(2, 1, '2026-08-04', '07:59:00', '17:31:00', 'WFO', 'Melakukan instalasi Visual Studio Community 2017 dan SSDT versi 15.9.10, serta verifikasi untuk memastikan instalasi berhasil dan dapat digunakan.', '', 'Completed', '2026-08-21 06:08:30', '2026-08-21 06:08:30', 't'),
(3, 1, '2026-08-05', '08:02:00', '17:31:00', 'WFO', 'Melakukan instalasi SSMS dan Office 2021 LTSC 64 bit, serta verifikasi untuk memastikan instalasi berhasil dan dapat digunakan.', '', 'Completed', '2026-08-21 06:08:57', '2026-08-21 06:08:57', 't'),
(4, 1, '2026-08-06', '08:00:00', '17:40:00', 'WFO', 'Melakukan instalasi PL/SQL 2016 32 bit, OCI 11g 32 bit dan Visual Studio Code, serta verifikasi untuk memastikan instalasi berhasil dan dapat digunakan.', '', 'Completed', '2026-08-21 06:09:52', '2026-08-21 06:09:52', 't'),
(5, 1, '2026-08-07', '08:03:00', '18:26:00', 'WFO', 'Mempelajari fitur Visual Studio, SSMS, dan PL/SQL, serta memahami alur proses pembiayaan kendaraan dan dana.', '', 'Completed', '2026-08-21 06:10:21', '2026-08-21 06:10:21', 't'),
(6, 1, '2026-08-10', '08:11:00', '17:31:00', 'WFO', 'Melakukan monitoring job serta mempelajari tools PL/SQL dan Oracle untuk memahami proses dan pengelolaan database.', '', 'Completed', '2026-08-21 06:11:25', '2026-08-21 06:11:25', 't'),
(7, 1, '2026-08-11', '08:09:00', '17:32:00', 'WFO', 'Melakukan monitoring job berkala serta pengecekan ORA-00001 Error pada job sync_coll_rpt_recovery.', '', 'Completed', '2026-08-21 06:11:51', '2026-08-21 06:11:51', 't'),
(8, 1, '2026-08-12', '08:06:00', '17:35:00', 'WFO', 'Melakukan monitoring job berkala serta pengecekan ORA-06512 Error pada Job CMS-Report_Detail_KYC dan ORA-06550 Error pada Job JF-Report Aging Core Non Aktif dan Deployment SSIS Package – Report Portfolio, Deskcoll RoboAI, dan CMS RPT Entry Result', '', 'Completed', '2026-08-21 06:12:19', '2026-08-21 06:12:19', 't'),
(9, 1, '2026-08-13', '08:03:00', '18:21:00', 'WFO', 'Melakukan Monitoring Job, pengecekan error pada job Robo-Deskcoll_RoboAi_AG, serta melakukan deployment SSIS Package `Proses_Installment_MF_BMRI.dtsx`.', '', 'Completed', '2026-08-21 06:13:39', '2026-08-21 06:13:39', 't'),
(10, 1, '2026-08-14', '08:07:00', '17:31:00', 'WFO', 'Melakukan monitoring job, disable job CMS-Report_Detail_KYC, serta pengecekan error pada job JF-Repo_SOLD di step Proses_WO_PMT (6) dan Proses_WO_BMRI (5).', '', 'Completed', '2026-08-21 06:14:15', '2026-08-21 06:14:15', 't'),
(11, 1, '2026-08-15', '08:30:00', '17:30:00', 'WFH', 'Melakukan monitoring job dan Pengecekan Error pada Job sync_coll_rpt_recovery', 'Standby Weekend', 'Completed', '2026-08-21 06:14:41', '2026-08-26 08:08:50', 't'),
(12, 1, '2026-08-16', '07:00:00', '22:00:00', 'WFO', 'Melakukan monitoring job, mengikuti meeting upgrade Oracle Muftech Prod, melakukan disable dan enable job TOAD berdasarkan list yang telah dibuat, serta menjalankan job Report-Aging_QuickCount_Daily_AM.', 'Lembur Deploy Oracle Muftech Production', 'Completed', '2026-08-21 06:15:08', '2026-08-26 02:17:29', 't'),
(13, 1, '2026-08-18', '08:05:00', '16:49:00', 'WFO', 'Melakukan monitoring job secara berkala', '', 'Completed', '2026-08-24 03:10:23', '2026-08-24 03:10:23', 't'),
(17, 1, '2026-08-19', '08:00:00', '17:33:00', 'WFO', 'Melakukan monitoring job, Disable dan enable Job TOAD berdasarkan daftar job yang telah dibuat, serta menjalankan Job Report-Aging_QuickCount_Daily_AM. Melakukan pengecekan Job Error AM-InsrInvoiceAM-B2BInsrInvoice-AM2AM dan AM-RekonInsrAM-B2b_Insr_Reconsile-AM2AM, serta menjalankan kedua job tersebut.', '', 'Completed', '2026-08-24 03:15:41', '2026-08-24 03:15:41', 't'),
(18, 1, '2026-08-20', '08:12:00', '17:31:00', 'WFO', 'Melakukan pengecekan Job Error JF-BATCH_2_PAY BMR, Step Proses_Fee_BMR, dengan error exact fetch returns more than requested number of rows.', '', 'Completed', '2026-08-24 03:17:08', '2026-08-24 03:17:08', 't'),
(23, 1, '2026-08-21', '08:03:00', '17:32:00', 'WFO', 'Melakukan monitoring job serta Pengecekan Job Error JF-Disburse_New_JF_BMR dan Report-PPD_Count.', '', 'Completed', '2026-08-26 02:12:18', '2026-08-26 02:40:02', 't'),
(24, 1, '2026-08-24', '07:34:00', '17:38:00', 'WFO', 'Melakukan monitoring job serta Pengecekan Job Error JF-BATCH_2_PAY BMR Step Proses_Fee_BMR exact fetch returns more than requested number of rows', '', 'Completed', '2026-08-26 02:13:16', '2026-08-26 02:44:13', 't'),
(25, 1, '2026-08-26', '07:50:00', '17:38:00', 'WFO', 'Melakukan monitoring Job, pengecekan standarisasi Job SSIS Object Slik_Pelunasan_Daily.dtsx pada UAT Environment E, mapping Object SSIS sesuai path, serta menjalankan Job Disburse_New_JF_BMR.dtsx dengan task Bridging Data dalam kondisi disabled.', '', 'Completed', '2026-08-26 08:04:10', '2026-08-26 10:38:19', 't'),
(26, 1, '2026-08-27', '08:16:00', '17:31:00', 'WFO', 'Melakukan monitoring job serta Pengecekan Job Error JF-PAYMENTTOBNK Step Proses_Fee_BMR exact fetch returns more than requested number of rows, Pengecekan standarisasi Job SSIS Object Report_Gagal_Integrasi_MASS_to_AM.dtsx pada UAT Env B, serta menjalankan Job Disburse_New_JF_BMR.dtsx dengan task Bridging Data dalam kondisi disabled.', '', 'Completed', '2026-08-27 04:10:46', '2026-08-27 08:32:57', 't'),
(27, 1, '2026-08-28', '07:09:00', '17:32:00', 'WFO', 'Melakukan monitoring job secara berkala', '', 'Completed', '2026-08-28 03:34:45', '2026-08-31 09:45:21', 't'),
(28, 1, '2026-08-31', '07:52:00', '17:30:00', 'WFO', 'Melakukan monitoring job serta deploy dan pengecekan standarisasi Job SSIS Object Disburse_New_JF_BMR - VERSI U.dtsx pada UAT Env E, pengecekan standarisasi maintenance CRMS terkait perbedaan data customer antara Core dan MUFCMS akibat customer number duplikat pada Job SSIS Object cms_sync_cont_cust_obj_am2cms.dtsx di UAT Env C, serta melakukan running manual job TXT dan patching data JF PT Niramas. Selain itu, melakukan proses hold/unhold Job Disburse JF BMRI sesuai kebutuhan monitoring.', '', 'Completed', '2026-08-31 09:56:46', '2026-08-31 09:57:11', 't'),
(29, 1, '2026-09-01', '08:02:00', '17:31:00', 'WFO', '-', '', 'Completed', '2026-09-01 01:38:31', '2026-09-01 01:38:31', 't');

-- --------------------------------------------------------

--
-- Table structure for table `special_dates`
--

CREATE TABLE `special_dates` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('libur','cuti_pengganti') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'libur',
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `special_dates`
--

INSERT INTO `special_dates` (`id`, `user_id`, `tanggal`, `jenis`, `keterangan`, `created_at`) VALUES
(1, 1, '2026-08-17', 'libur', 'Hari Kemerdekaan', '2026-08-28 02:26:18'),
(2, 1, '2026-08-25', 'libur', 'Maulid Nabi Muhammad SAW', '2026-08-28 02:27:21');

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
-- Indexes for table `special_dates`
--
ALTER TABLE `special_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_tanggal` (`user_id`,`tanggal`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `special_dates`
--
ALTER TABLE `special_dates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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

--
-- Constraints for table `special_dates`
--
ALTER TABLE `special_dates`
  ADD CONSTRAINT `special_dates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
