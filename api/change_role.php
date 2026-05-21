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
$role = $data['role'] ?? 'user';

if (!in_array($role, ['user', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Недопустимая роль']);
    exit();
}

// Нельзя изменить роль самому себе
if ($id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Нельзя изменить свою роль']);
    exit();
}

$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([$role, $id]);

echo json_encode(['success' => true]);
?>