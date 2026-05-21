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
$reason = $data['reason'] ?? '';

// Удаляем объявление
$stmt = $pdo->prepare("SELECT photo_path FROM ads WHERE id = ?");
$stmt->execute([$id]);
$ad = $stmt->fetch();

if ($ad && !empty($ad['photo_path']) && file_exists($ad['photo_path'])) {
    unlink($ad['photo_path']);
}

$stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
$stmt->execute([$id]);

// Здесь можно сохранить причину отклонения в отдельную таблицу, если нужно

echo json_encode(['success' => true]);
?>