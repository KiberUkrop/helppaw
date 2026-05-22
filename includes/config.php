<?php
$host = 'lejifdokiechon.beget.app';
$port = '5432';
$dbname = 'default_db';
$user = 'KiberUkrop';
$password = 'VikopKeK2006)';         // Пароль

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