<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT username, email, created_at, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('logout.php');
}

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    
    $errors = [];
    
    if (empty($new_username)) {
        $errors[] = 'Введите имя пользователя';
    } elseif (strlen($new_username) < 3) {
        $errors[] = 'Имя пользователя должно содержать минимум 3 символа';
    } elseif (strlen($new_username) > 50) {
        $errors[] = 'Имя пользователя не должно превышать 50 символов';
    }
    
    if (empty($new_email)) {
        $errors[] = 'Введите email';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($errors) && ($new_username !== $user['username'] || $new_email !== $user['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$new_username, $new_email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = 'Имя пользователя или email уже заняты';
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$new_username, $new_email, $user_id])) {
            $_SESSION['username'] = $new_username;
            $user['username'] = $new_username;
            $user['email'] = $new_email;
            $success = 'Профиль успешно обновлён';
        } else {
            $error = 'Ошибка при обновлении профиля';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Обработка смены пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($current_password)) {
        $errors[] = 'Введите текущий пароль';
    }
    
    if (empty($new_password)) {
        $errors[] = 'Введите новый пароль';
    } elseif (strlen($new_password) < 4) {
        $errors[] = 'Новый пароль должен содержать минимум 4 символа';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        
        if (password_verify($current_password, $user_data['password_hash'])) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$new_hash, $user_id])) {
                $success = 'Пароль успешно изменён';
            } else {
                $error = 'Ошибка при смене пароля';
            }
        } else {
            $error = 'Неверный текущий пароль';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - HelpPaw</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

    <header>
        <a class="logo" href="index.php">
            <svg viewBox="0 0 70 70" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M27.3794 43.9807C34.8743 29.9935 37.8837 29.6756 38.4513 31.2649V34.4441C48.6071 33.6543 51.1942 41.4966 49.5553 44.7148L49.5235 44.7755C52.9302 46.3651 52.0785 47.9544 51.2269 49.544C49.1829 53.3587 39.587 53.7827 35.0446 53.5176C16.3073 51.1335 11.1971 58.2861 11.1972 63.8493C63.6495 67.7965 65.6807 54.4921 66.5402 48.8618C66.5461 48.8238 66.5516 48.7865 66.5575 48.7492C69.2828 29.6756 57.1888 25.7018 56.3372 26.4966L57.1888 5.83334C46.2869 5.83334 45.2649 12.1913 45.2649 13.7807C24.1429 13.7807 21.4175 20.1387 20.5658 25.7018H6.08698C6.08698 41.5966 12.9005 43.1862 18.8624 43.9807H27.3794ZM33.3413 25.7018C33.3413 27.0186 32.1974 28.086 30.7863 28.086C29.3749 28.086 28.2311 27.0186 28.2311 25.7018C28.2311 24.3851 29.3749 23.3176 30.7863 23.3176C32.1974 23.3176 33.3413 24.3851 33.3413 25.7018ZM46.9682 43.9807C46.9682 44.8586 46.2058 45.5703 45.2649 45.5703C44.3243 45.5703 43.5616 44.8586 43.5616 43.9807C43.5616 43.1031 44.3243 42.3914 45.2649 42.3914C46.2058 42.3914 46.9682 43.1031 46.9682 43.9807Z"/>
            </svg>
            <h1>Help <br> Paw</h1>
        </a>
        
        <button class="burger-menu" id="burgerMenu" aria-label="Меню">
            <span class="burger-line"></span>
            <span class="burger-line"></span>
            <span class="burger-line"></span>
        </button>
        
        <nav id="navMenu">
            <a href="add_ad.php">Добавить объявление</a>
            <a href="my_ads.php">Мои объявления</a>
            <a href="messages.php">Сообщения</a>
            <a href="profile.php">Профиль</a>
            <?php if (isAdmin()): ?>
                <a href="admin_panel.php">Админ-панель</a>
            <?php endif; ?>
            <button id="themeToggle" class="theme-toggle" aria-label="Переключить тему">🌙</button>
        </nav>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </header>

    <main>
        <h1>Профиль</h1>
        <div class="profile-container">
                <!-- Левая колонка - информация -->
                <div class="profile-info">
                    <div class="avatar-placeholder">
                        <?= mb_substr($user['username'], 0, 1) ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Имя пользователя</span>
                        <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Зарегистрирован</span>
                        <span class="info-value"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Роль</span>
                        <span class="info-value"><?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></span>
                    </div>
                    
                    <!-- Кнопка выхода -->
                    <div class="logout-wrapper">
                        <a href="logout.php" class="btn-logout" onclick="return confirm('Вы уверены, что хотите выйти?')">Выйти из аккаунта</a>
                    </div>
                </div>
                
                <!-- Правая колонка - формы -->
                <div class="profile-forms">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    
                    <!-- Форма редактирования профиля -->
                    <div class="form-card">
                        <h2>Редактировать профиль</h2>
                        <form method="POST" class="profile-form">
                            <div class="form-group">
                                <label for="username">Имя пользователя</label>
                                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="btn-primary">Сохранить изменения</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Форма смены пароля -->
                    <div class="form-card">
                        <h2>Сменить пароль</h2>
                        <form method="POST" class="profile-form">
                            <div class="form-group">
                                <label for="current_password">Текущий пароль</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Новый пароль</label>
                                <input type="password" id="new_password" name="new_password" required>
                                <small>Минимум 4 символа</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Подтверждение пароля</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="change_password" class="btn-secondary">Сменить пароль</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </main>

    <footer>
        <p>© 2026 HelpPaw - Поможем животным вместе</p>
        <p>
            <a href="privacy.php">Политика конфиденциальности</a>
        </p>
    </footer>
    <script src="js/theme_burger.js"></script>
</body>
</html>