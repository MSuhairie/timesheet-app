<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$userId = CURRENT_USER_ID;
$today  = date('Y-m-d');
$now    = date('H:i:s');

$db = getDB();

$stmt = $db->prepare('SELECT id, check_in, check_out FROM activities WHERE user_id = ? AND activity_date = ?');
$stmt->execute([$userId, $today]);
$existing = $stmt->fetch();

if (!$existing || !$existing['check_in']) {
    echo json_encode(['success' => false, 'message' => 'Anda belum check in hari ini.']);
    exit;
}

if ($existing['check_out']) {
    echo json_encode(['success' => false, 'message' => 'Anda sudah check out hari ini.']);
    exit;
}

$upd = $db->prepare('UPDATE activities SET check_out = ? WHERE id = ?');
$upd->execute([$now, $existing['id']]);

echo json_encode(['success' => true, 'message' => 'Check out berhasil.', 'time' => $now]);
