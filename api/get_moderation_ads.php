<?php
header('Cache-Control: max-age=5');
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещён']);
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.*, u.username 
    FROM ads a
    JOIN users u ON a.user_id = u.id
    WHERE a.is_approved = FALSE
    ORDER BY a.created_at DESC
");
$stmt->execute();
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($ads);
?>