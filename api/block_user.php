<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещён']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;
$block = (bool)($data['block'] ?? false);

// Нельзя заблокировать самого себя
if ($id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Нельзя заблокировать себя']);
    exit();
}

$stmt = $pdo->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
$stmt->execute([$block, $id]);

echo json_encode(['success' => true]);
?>