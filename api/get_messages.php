<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Только для авторизованных
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit();
}

$user_id = $_SESSION['user_id'];
$with_user_id = (int)($_GET['with_user_id'] ?? 0);
$last_id = (int)($_GET['last_id'] ?? 0);

if ($with_user_id <= 0) {
    echo json_encode([]);
    exit();
}

// Проверяем, существует ли пользователь
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$with_user_id]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Пользователь не найден']);
    exit();
}

// Получаем сообщения между двумя пользователями
$sql = "
    SELECT m.*
    FROM messages m
    WHERE (m.from_user_id = ? AND m.to_user_id = ?)
       OR (m.from_user_id = ? AND m.to_user_id = ?)
";

$params = [$user_id, $with_user_id, $with_user_id, $user_id];

if ($last_id > 0) {
    $sql .= " AND m.id > ?";
    $params[] = $last_id;
}

$sql .= " ORDER BY m.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Помечаем входящие сообщения как прочитанные
$stmt = $pdo->prepare("
    UPDATE messages 
    SET is_read = TRUE 
    WHERE to_user_id = ? AND from_user_id = ? AND is_read = FALSE
");
$stmt->execute([$user_id, $with_user_id]);

header('Content-Type: application/json');
echo json_encode($messages);