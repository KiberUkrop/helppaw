<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Только для авторизованных
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit();
}

// Получаем данные
$data = json_decode(file_get_contents('php://input'), true);
$to_user_id = (int)($data['to_user_id'] ?? 0);
$message = trim($data['message'] ?? '');

$from_user_id = $_SESSION['user_id'];

// Валидация
$errors = [];

if ($to_user_id <= 0) {
    $errors[] = 'Не указан получатель';
}

if ($from_user_id == $to_user_id) {
    $errors[] = 'Нельзя отправить сообщение самому себе';
}

if (empty($message)) {
    $errors[] = 'Введите сообщение';
} elseif (mb_strlen($message) > 1000) {
    $errors[] = 'Сообщение не должно превышать 1000 символов';
}

// Проверяем, существует ли получатель и не заблокирован ли он
if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id, is_blocked FROM users WHERE id = ?");
    $stmt->execute([$to_user_id]);
    $recipient = $stmt->fetch();
    
    if (!$recipient) {
        $errors[] = 'Получатель не найден';
    } elseif ($recipient['is_blocked']) {
        $errors[] = 'Пользователь заблокирован';
    }
}

// Проверяем, не заблокирован ли отправитель
if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT is_blocked FROM users WHERE id = ?");
    $stmt->execute([$from_user_id]);
    $sender = $stmt->fetch();
    
    if ($sender && $sender['is_blocked']) {
        $errors[] = 'Вы заблокированы. Невозможно отправить сообщение.';
    }
}

// Отправляем сообщение
if (empty($errors)) {
    $stmt = $pdo->prepare("
        INSERT INTO messages (from_user_id, to_user_id, message, is_read, created_at) 
        VALUES (?, ?, ?, FALSE, NOW())
    ");
    
    if ($stmt->execute([$from_user_id, $to_user_id, $message])) {
        echo json_encode([
            'success' => true,
            'message_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка при отправке']);
    }
} else {
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
}