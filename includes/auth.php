<?php
/**
 * Helper umum aplikasi.
 * Aplikasi ini TIDAK memakai sistem login — single user, id tetap
 * mengikuti CURRENT_USER_ID di config/database.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // dipakai untuk flash message (notifikasi sukses/gagal antar halaman)
}

require_once __DIR__ . '/../config/database.php';

function currentUser(): ?array
{
    static $user = null;

    if ($user === null) {
        $stmt = getDB()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([CURRENT_USER_ID]);
        $user = $stmt->fetch() ?: null;
    }

    return $user;
}

/**
 * Base path relatif project, supaya link tetap benar
 * kalau project diletakkan di subfolder (mis. /timesheet-app).
 */
function basePath(): string
{
    $script = $_SERVER['SCRIPT_NAME'];
    $dir    = str_replace('\\', '/', dirname($script));

    // Buang folder /pages atau /api dari path kalau ada
    $dir = preg_replace('#/(pages|api)$#', '', $dir);

    return rtrim($dir, '/');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Konversi file gambar (path absolut di server) jadi base64 data URI,
 * supaya logo tetap tampil walau file .doc/.pdf dibuka terpisah dari
 * server (path relatif tidak akan berfungsi begitu file di-download).
 * Return string kosong kalau file tidak ditemukan.
 */
function imageToBase64(string $absolutePath): string
{
    if (!is_file($absolutePath)) {
        return '';
    }

    $mimeTypes = [
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
    ];
    $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
    $mime = $mimeTypes[$ext] ?? null;

    if (!$mime) {
        return '';
    }

    $data = @file_get_contents($absolutePath);
    if ($data === false) {
        return '';
    }

    return 'data:' . $mime . ';base64,' . base64_encode($data);
}

/**
 * Cari file logo di assets/img/ dengan nama dasar tertentu, ekstensi apa saja
 * yang didukung. Return path absolut kalau ketemu, null kalau tidak ada.
 */
function findLogoFile(string $baseName): ?string
{
    $dir = __DIR__ . '/../assets/img/';
    foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $ext) {
        $path = $dir . $baseName . '.' . $ext;
        if (is_file($path)) {
            return $path;
        }
    }
    return null;
}
