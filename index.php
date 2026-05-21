<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Получаем все объявления для первоначальной загрузки
$stmt = $pdo->prepare("
    SELECT a.*, u.username 
    FROM ads a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.is_approved = TRUE 
    ORDER BY a.created_at DESC
");
$stmt->execute();
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpPaw - Потерянные и найденные животные</title>
    <link rel="stylesheet" href="css/style.css">
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
            <?php if (isLoggedIn()): ?>
                <a href="add_ad.php">Добавить объявление</a>
                <a href="my_ads.php">Мои объявления</a>
                <a href="messages.php">Сообщения</a>
                <a href="profile.php">Профиль</a>
                <?php if (isAdmin()): ?>
                    <a href="admin_panel.php">Админ-панель</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
            <button id="themeToggle" class="theme-toggle" aria-label="Переключить тему">🌙</button>
        </nav>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </header>
    <main>
        <h1>Объявления</h1>

        <!-- Фильтры (AJAX) -->
        <div class="filter-block">
            <div class="filter-form">
                <select id="filter-animal-type">
                    <option value="">Все животные</option>
                    <option value="dog">Собака</option>
                    <option value="cat">Кошка</option>
                    <option value="other">Другое</option>
                </select>
                <select id="filter-status">
                    <option value="">Все статусы</option>
                    <option value="lost">Потерялся</option>
                    <option value="found">Нашёлся</option>
                </select>
                <button type="button" id="apply-filter">Применить</button>
                <button type="button" id="reset-filter" class="reset-btn">Сбросить</button>
            </div>
        </div>

        <!-- Список объявлений -->
        <div class="pet-list" id="pets-container">
            <?php if (empty($ads)): ?>
                <p class="no-pets">Пока нет объявлений</p>
            <?php else: ?>
                <?php foreach ($ads as $ad): ?>
                    <div class="pet-card">
                        <div class="pet-card-left">
                            <?php if (!empty($ad['photo_path'])): ?>
                                <img src="<?= htmlspecialchars($ad['photo_path']) ?>" alt="Фото животного" class="pet-photo">
                            <?php else: ?>
                                <div class="pet-photo-placeholder">Нет фото</div>
                            <?php endif; ?>
                        </div>
                        <div class="pet-card-right">
                            <h3><?= htmlspecialchars($ad['title']) ?></h3>
                            <div class="pet-details">
                                <p><strong>Статус:</strong> <?= $ad['status'] === 'lost' ? 'Потерялся' : 'Нашёлся' ?></p>
                                <p><strong>Описание:</strong> <?= nl2br(htmlspecialchars($ad['description'])) ?></p>
                                <p><strong>Место:</strong> <?= htmlspecialchars($ad['location'] ?: 'Не указано') ?></p>
                                <p><strong>Автор:</strong> <?= htmlspecialchars($ad['username']) ?></p>
                            </div>
                            <?php if (isLoggedIn() && $_SESSION['user_id'] != $ad['user_id']): ?>
                                <a href="messages.php?user_id=<?= $ad['user_id'] ?>" class="pet-message-btn">
                                    Написать автору
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2026 HelpPaw - Поможем животным вместе</p>
        <p>
            <a href="privacy.php">Политика конфиденциальности</a>
        </p>
    </footer>

    <script>
        window.currentUserId = <?= json_encode($_SESSION['user_id'] ?? 0) ?>;
    </script>
    <script src="js/filter.js"></script>
    <script src="js/theme_burger.js"></script>
</body>
</html>