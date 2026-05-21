<?php
// includes/functions.php

// Проверка, авторизован ли пользователь
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Проверка, админ ли пользователь
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Перенаправление на другую страницу
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Безопасный вывод текста (защита от XSS)
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Генерация CSRF токена для защиты форм
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Проверка CSRF токена
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>