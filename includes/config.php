<?php
$host = 'localhost';
$port = '5432';
$dbname = 'helpaw';
$user = 'postgres';
$password = '6745';  // пароль, который вводила при установке PostgreSQL 14

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    session_start();
} catch (PDOException $e) {
    die("❌ Ошибка: " . $e->getMessage());
}
?>