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

$stmt = $pdo->prepare("UPDATE ads SET is_approved = FALSE WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
?>