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

// Получаем путь к фото
$stmt = $pdo->prepare("SELECT photo_path FROM ads WHERE id = ?");
$stmt->execute([$id]);
$ad = $stmt->fetch();

// Удаляем фото
if ($ad && !empty($ad['photo_path']) && file_exists($ad['photo_path'])) {
    unlink($ad['photo_path']);
}

// Удаляем объявление
$stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
?>