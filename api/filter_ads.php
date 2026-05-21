<?php
require_once '../includes/config.php';

$sql = "SELECT a.*, u.username FROM ads a JOIN users u ON a.user_id = u.id WHERE a.is_approved = TRUE";
$params = [];

// Фильтрация по типу животного
if (!empty($_GET['animal_type'])) {
    $sql .= " AND a.animal_type = ?";
    $params[] = $_GET['animal_type'];
}

// Фильтрация по статусу
if (!empty($_GET['status'])) {
    $sql .= " AND a.status = ?";
    $params[] = $_GET['status'];
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($ads);