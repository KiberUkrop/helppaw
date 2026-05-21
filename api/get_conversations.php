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

$sql = "
    SELECT 
        u.id,
        u.username,
        u.is_blocked,
        COALESCE(
            (SELECT message FROM messages 
             WHERE (from_user_id = ? AND to_user_id = u.id) 
                OR (from_user_id = u.id AND to_user_id = ?)
             ORDER BY created_at DESC LIMIT 1), 
            ''
        ) as last_message,
        (SELECT created_at FROM messages 
         WHERE (from_user_id = ? AND to_user_id = u.id) 
            OR (from_user_id = u.id AND to_user_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages 
         WHERE to_user_id = ? AND from_user_id = u.id AND is_read = FALSE) as unread_count
    FROM messages m
    JOIN users u ON (u.id = m.from_user_id OR u.id = m.to_user_id)
    WHERE (m.from_user_id = ? OR m.to_user_id = ?) AND u.id != ?
    GROUP BY u.id, u.username, u.is_blocked
    ORDER BY last_message_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Преобразуем NULL в пустую строку для last_message
foreach ($conversations as &$conv) {
    if ($conv['last_message'] === null) {
        $conv['last_message'] = '';
    }
}

header('Content-Type: application/json');
echo json_encode($conversations);