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

// Нельзя удалить самого себя
if ($id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Нельзя удалить себя']);
    exit();
}

// Получаем все объявления пользователя для удаления фото
$stmt = $pdo->prepare("SELECT photo_path FROM ads WHERE user_id = ?");
$stmt->execute([$id]);
$ads = $stmt->fetchAll();

foreach ($ads as $ad) {
    if (!empty($ad['photo_path']) && file_exists($ad['photo_path'])) {
        unlink($ad['photo_path']);
    }
}

// Удаляем пользователя (сообщения и объявления удалятся каскадно)
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
?>