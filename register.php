<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Если уже авторизован, перенаправляем на главную
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Валидация
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Введите имя пользователя';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Имя пользователя должно содержать минимум 3 символа';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Имя пользователя не должно превышать 50 символов';
    }
    
    if (empty($email)) {
        $errors[] = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($password)) {
        $errors[] = 'Введите пароль';
    } elseif (strlen($password) < 4) {
        $errors[] = 'Пароль должен содержать минимум 4 символа';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    // Проверка на существующего пользователя
    if (empty($errors)) {
        // Проверяем, не занято ли имя
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Это имя пользователя уже занято';
        }
        
        // Проверяем, не занят ли email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Этот email уже зарегистрирован';
        }
    }
    
    // Если ошибок нет — создаём пользователя
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role) 
            VALUES (?, ?, ?, 'user')
        ");
        
        if ($stmt->execute([$username, $email, $password_hash])) {
            $_SESSION['registration_success'] = 'Регистрация прошла успешно! Теперь вы можете войти.';
            redirect('login.php');
        } else {
            $errors[] = 'Ошибка при регистрации. Попробуйте позже.';
        }
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация - HelpPaw</title>
    <link rel="stylesheet" href="css/login_and_register_style.css">
</head>
<body>
    <div class="auth-container">
        <div class="welcome_icon">
            <h1>Добро </br> пожаловать</h1>
            <svg width="70" height="70" viewBox="0 0 70 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M27.3794 43.9807C34.8743 29.9935 37.8837 29.6756 38.4513 31.2649V34.4441C48.6071 33.6543 51.1942 41.4966 49.5553 44.7148L49.5235 44.7755C52.9302 46.3651 52.0785 47.9544 51.2269 49.544C49.1829 53.3587 39.587 53.7827 35.0446 53.5176C16.3073 51.1335 11.1971 58.2861 11.1972 63.8493C63.6495 67.7965 65.6807 54.4921 66.5402 48.8618C66.5461 48.8238 66.5516 48.7865 66.5575 48.7492C69.2828 29.6756 57.1888 25.7018 56.3372 26.4966L57.1888 5.83334C46.2869 5.83334 45.2649 12.1913 45.2649 13.7807C24.1429 13.7807 21.4175 20.1387 20.5658 25.7018H6.08698C6.08698 41.5966 12.9005 43.1862 18.8624 43.9807H27.3794ZM33.3413 25.7018C33.3413 27.0186 32.1974 28.086 30.7863 28.086C29.3749 28.086 28.2311 27.0186 28.2311 25.7018C28.2311 24.3851 29.3749 23.3176 30.7863 23.3176C32.1974 23.3176 33.3413 24.3851 33.3413 25.7018ZM46.9682 43.9807C46.9682 44.8586 46.2058 45.5703 45.2649 45.5703C44.3243 45.5703 43.5616 44.8586 43.5616 43.9807C43.5616 43.1031 44.3243 42.3914 45.2649 42.3914C46.2058 42.3914 46.9682 43.1031 46.9682 43.9807Z"/>
            </svg>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="input_field">
                <input type="text" name="username" placeholder="Имя пользователя" value="<?= h($username ?? '') ?>" required minlength="3" maxlength="50">
                <input type="email" name="email" placeholder="Email" value="<?= h($email ?? '') ?>" required>
                <input type="password" name="password" placeholder="Пароль" required minlength="4">
                <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
            </div>
            <div class="button_auth">
                <label class="custom-checkbox">
                    <input type="checkbox" name="agree" id="agreeCheckbox" required>
                    <span class="checkmark"></span>
                    Согласен(а) на обработку<br>персональных данных
                </label>
                <button type="submit" class="btn-primary">Зарегистрироваться</button>
                <a href="login.php">Войти</a>
            </div>
        </form>
    </div>
</body>
</html>