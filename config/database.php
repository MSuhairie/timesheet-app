<?php
/**
 * Konfigurasi koneksi database (PDO + MySQL)
 * Sesuaikan DB_HOST, DB_NAME, DB_USER, DB_PASS dengan environment kamu.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'timesheet_app');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Nama perusahaan default untuk header export (bisa juga diambil dari kolom users.perusahaan)
define('APP_NAME', 'Timesheet App');

// Aplikasi ini tanpa login (single user). Semua data terikat ke user id berikut.
define('CURRENT_USER_ID', 1);

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Jangan tampilkan detail koneksi di production
            die('Koneksi database gagal. Periksa config/database.php. (' . $e->getMessage() . ')');
        }
    }

    return $pdo;
}
