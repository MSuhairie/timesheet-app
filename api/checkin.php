<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$workPlace = in_array($input['work_place'] ?? 'WFO', ['WFO', 'WFH'], true) ? $input['work_place'] : 'WFO';

$userId = CURRENT_USER_ID;
$today  = date('Y-m-d');
$now    = date('H:i:s');

$db = getDB();

$stmt = $db->prepare('SELECT id, check_in FROM activities WHERE user_id = ? AND activity_date = ?');
$stmt->execute([$userId, $today]);
$existing = $stmt->fetch();

if ($existing && $existing['check_in']) {
    echo json_encode(['success' => false, 'message' => 'Anda sudah check in hari ini.']);
    exit;
}

if ($existing) {
    // Baris sudah ada (mungkin dibuat manual lewat Daily Activity) tapi belum check in
    $upd = $db->prepare('UPDATE activities SET check_in = ?, work_place = ?, status = "In Progress" WHERE id = ?');
    $upd->execute([$now, $workPlace, $existing['id']]);
    $activityId = $existing['id'];
} else {
    $ins = $db->prepare('INSERT INTO activities (user_id, activity_date, check_in, work_place, task, status)
                          VALUES (?, ?, ?, ?, "", "In Progress")');
    $ins->execute([$userId, $today, $now, $workPlace]);
    $activityId = $db->lastInsertId();
}

echo json_encode(['success' => true, 'message' => 'Check in berhasil.', 'activity_id' => $activityId, 'time' => $now]);
