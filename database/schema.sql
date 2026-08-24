-- =========================================================
-- Timesheet App - Database Schema (MVP, tanpa login)
-- =========================================================

CREATE DATABASE IF NOT EXISTS timesheet_app
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE timesheet_app;

-- ---------------------------------------------------------
-- Tabel users (profil karyawan saja, tanpa username/password
-- karena aplikasi ini single user tanpa fitur login)
-- ---------------------------------------------------------
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nama            VARCHAR(100) NOT NULL,
    nik             VARCHAR(50)  DEFAULT NULL,
    departemen      VARCHAR(100) DEFAULT NULL,
    jabatan         VARCHAR(100) DEFAULT NULL,
    project         VARCHAR(100) DEFAULT NULL,
    client          VARCHAR(100) DEFAULT NULL,
    atasan          VARCHAR(100) DEFAULT NULL,
    perusahaan      VARCHAR(150) DEFAULT NULL,
    perusahaan_alamat VARCHAR(255) DEFAULT NULL,
    perusahaan_telp   VARCHAR(100) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel activities (Daily Activity / Check In-Out / Task)
-- ---------------------------------------------------------
CREATE TABLE activities (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    activity_date   DATE NOT NULL,
    check_in        TIME DEFAULT NULL,
    check_out       TIME DEFAULT NULL,
    work_place      ENUM('WFO','WFH') NOT NULL DEFAULT 'WFO',
    task            TEXT NOT NULL,
    notes           TEXT DEFAULT NULL,
    status          ENUM('Planned','In Progress','Completed','Pending','Cancelled')
                        NOT NULL DEFAULT 'Planned',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, activity_date)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Seed profil default (id harus 1, karena app pakai CURRENT_USER_ID = 1
-- di config/database.php)
-- ---------------------------------------------------------
INSERT INTO users
    (id, nama, nik, departemen, jabatan, project, client, atasan, perusahaan, perusahaan_alamat, perusahaan_telp)
VALUES
    (1,
     'M. Suhairie',
     '-',
     'PS Consultant',
     'Java Developer',
     'IT Release Management',
     '-',
     '-',
     'PT. Indocyber Global Teknologi',
     'Komplek Pertokoan Aldiron Hero Blok C No 10, Jl. Daan Mogot Kav. 119, Jakarta Barat 11460',
     'Telp. (+62-21) 566 3705, Fax. (+62-21) 566 3704');

-- ---------------------------------------------------------
-- MIGRASI (jalankan ini kalau database sudah ada sebelumnya,
-- cukup jalankan 2 baris ALTER berikut tanpa perlu install ulang)
-- ---------------------------------------------------------
-- ALTER TABLE users ADD COLUMN perusahaan_alamat VARCHAR(255) DEFAULT NULL AFTER perusahaan;
-- ALTER TABLE users ADD COLUMN perusahaan_telp   VARCHAR(100) DEFAULT NULL AFTER perusahaan_alamat;
