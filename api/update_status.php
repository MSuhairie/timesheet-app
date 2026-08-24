<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = (int)($input['id'] ?? 0);
$status = $input['status'] ?? '';

$validStatus = ['Planned', 'In Progress', 'Completed', 'Pending', 'Cancelled'];
if (!$id || !in_array($status, $validStatus, true)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare('UPDATE activities SET status = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$status, $id, CURRENT_USER_ID]);

echo json_encode(['success' => true]);
