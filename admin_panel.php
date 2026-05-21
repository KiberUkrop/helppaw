<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Только для администратора
if (!isAdmin()) {
    redirect('index.php');
}

// Добавляем колонку is_blocked в таблицу users, если её нет
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_blocked BOOLEAN DEFAULT FALSE");
} catch (PDOException $e) {
    // Колонка уже существует или ошибка — игнорируем
}

// Получаем статистику
$stats = [];

// Всего пользователей
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

// Всего объявлений
$stmt = $pdo->query("SELECT COUNT(*) FROM ads");
$stats['total_ads'] = $stmt->fetchColumn();

// Объявлений на модерации
$stmt = $pdo->query("SELECT COUNT(*) FROM ads WHERE is_approved = FALSE");
$stats['pending_ads'] = $stmt->fetchColumn();

// Одобренных объявлений
$stmt = $pdo->query("SELECT COUNT(*) FROM ads WHERE is_approved = TRUE");
$stats['approved_ads'] = $stmt->fetchColumn();

// Заблокированных пользователей
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_blocked = TRUE");
$stats['blocked_users'] = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - HelpPaw</title>
    <link rel="stylesheet" href="css/admin.css">
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
            <a href="admin_panel.php">Админ-панель</a>
            <button id="themeToggle" class="theme-toggle" aria-label="Переключить тему">🌙</button>
        </nav>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </header>

    <main>
        <h1>Админ-панель</h1>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Всего пользователей</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_ads'] ?></div>
                <div class="stat-label">Всего объявлений</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['pending_ads'] ?></div>
                <div class="stat-label">На модерации</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['approved_ads'] ?></div>
                <div class="stat-label">Одобрено</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['blocked_users'] ?></div>
                <div class="stat-label">Заблокировано</div>
            </div>
        </div>
        
        <!-- Вкладки -->
        <div class="admin-tabs">
            <button class="tab-btn active" data-tab="moderation">Модерация</button>
            <button class="tab-btn" data-tab="all-ads">Все объявления</button>
            <button class="tab-btn" data-tab="users">Пользователи</button>
        </div>
        
        <div class="tab-content active" id="tab-moderation">
            <h2>Объявления на модерации</h2>
            <div id="moderation-list">
                <div class="loading">Загрузка...</div>
            </div>
        </div>
        
        <div class="tab-content" id="tab-all-ads">
            <h2>Все объявления</h2>
            <div id="all-ads-list">
                <div class="loading">Загрузка...</div>
            </div>
        </div>
        
        <div class="tab-content" id="tab-users">
            <h2>Пользователи</h2>
            <div id="users-list">
                <div class="loading">Загрузка...</div>
            </div>
        </div>
    </main>

    <script>
        // Переключение вкладок
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(`tab-${tabId}`).classList.add('active');
                
                if (tabId === 'moderation') {
                    loadModerationAds();
                } else if (tabId === 'all-ads') {
                    loadAllAds();
                } else if (tabId === 'users') {
                    loadUsers();
                }
            });
        });
        
        function loadModerationAds() {
            fetch('api/get_moderation_ads.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('moderation-list');
                    if (data.length === 0) {
                        container.innerHTML = '<p class="no-data">Нет объявлений на модерации</p>';
                        return;
                    }
                    container.innerHTML = data.map(ad => `
                        <div class="admin-card" data-id="${ad.id}">
                            <div class="admin-card-left">
                                ${ad.photo_path ? `<img src="${ad.photo_path}" class="admin-photo">` : '<div class="admin-photo-placeholder">Нет фото</div>'}
                            </div>
                            <div class="admin-card-right">
                                <h3>${escapeHtml(ad.title)}</h3>
                                <p><strong>Автор:</strong> ${escapeHtml(ad.username)}</p>
                                <p><strong>Дата:</strong> ${ad.created_at}</p>
                                <p><strong>Описание:</strong> ${escapeHtml((ad.description || '').substring(0, 150))}${(ad.description || '').length > 150 ? '...' : ''}</p>
                                <div class="admin-actions">
                                    <button class="approve-btn" data-id="${ad.id}">Одобрить</button>
                                    <button class="reject-btn" data-id="${ad.id}">Отклонить</button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    
                    document.querySelectorAll('.approve-btn').forEach(btn => {
                        btn.addEventListener('click', () => moderateAd(btn.dataset.id, 'approve'));
                    });
                    document.querySelectorAll('.reject-btn').forEach(btn => {
                        btn.addEventListener('click', () => rejectAd(btn.dataset.id));
                    });
                });
        }
        
        function loadAllAds() {
            fetch('api/get_all_ads.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('all-ads-list');
                    if (data.length === 0) {
                        container.innerHTML = '<p class="no-data">Объявления не найдены</p>';
                        return;
                    }
                    container.innerHTML = data.map(ad => `
                        <div class="admin-card" data-id="${ad.id}">
                            <div class="admin-card-left">
                                ${ad.photo_path ? `<img src="${ad.photo_path}" class="admin-photo">` : '<div class="admin-photo-placeholder">Нет фото</div>'}
                            </div>
                            <div class="admin-card-right">
                                <h3>${escapeHtml(ad.title)}</h3>
                                <p><strong>Автор:</strong> ${escapeHtml(ad.username)}</p>
                                <p><strong>Статус:</strong> ${ad.is_approved ? 'Одобрено' : 'На модерации'}</p>
                                <p><strong>Дата:</strong> ${ad.created_at}</p>
                                <div class="admin-actions">
                                    ${ad.is_approved ? `<button class="unpublish-btn" data-id="${ad.id}">Снять с публикации</button>` : ''}
                                    <button class="delete-ad-btn" data-id="${ad.id}">Удалить</button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    
                    document.querySelectorAll('.unpublish-btn').forEach(btn => {
                        btn.addEventListener('click', () => unpublishAd(btn.dataset.id));
                    });
                    document.querySelectorAll('.delete-ad-btn').forEach(btn => {
                        btn.addEventListener('click', () => deleteAd(btn.dataset.id));
                    });
                });
        }
        
        function loadUsers() {
        fetch('api/get_users.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('users-list');
                if (data.length === 0) {
                    container.innerHTML = '<p class="no-data">Пользователи не найдены</p>';
                    return;
                }
                container.innerHTML = data.map(user => {
                    const isCurrentUser = user.id == <?= $_SESSION['user_id'] ?>;
                    return `
                        <div class="user-card" data-id="${user.id}">
                            <div class="user-info">
                                <strong>${escapeHtml(user.username)}</strong><br>
                                ${escapeHtml(user.email)}<br>
                                Роль: ${user.role === 'admin' ? 'Администратор' : 'Пользователь'}<br>
                                Статус: ${user.is_blocked ? 'Заблокирован' : 'Активен'}<br>
                                Зарегистрирован: ${user.created_at}
                            </div>
                            <div class="user-actions">
                                ${!isCurrentUser ? `
                                    ${user.role === 'admin' ? 
                                        `<button class="remove-admin-btn" data-id="${user.id}">Снять роль админа</button>` :
                                        `<button class="make-admin-btn" data-id="${user.id}">Сделать админом</button>`
                                    }
                                    ${user.is_blocked ? 
                                        `<button class="unblock-user-btn" data-id="${user.id}">Разблокировать</button>` :
                                        `<button class="block-user-btn" data-id="${user.id}">Заблокировать</button>`
                                    }
                                    <button class="delete-user-btn" data-id="${user.id}">Удалить</button>
                                ` : '<span class="current-user-badge">Это вы</span>'}
                            </div>
                        </div>
                    `;
                }).join('');
                
                // Привязываем обработчики только если есть кнопки
                document.querySelectorAll('.make-admin-btn').forEach(btn => {
                    btn.addEventListener('click', () => changeUserRole(btn.dataset.id, 'admin'));
                });
                document.querySelectorAll('.remove-admin-btn').forEach(btn => {
                    btn.addEventListener('click', () => changeUserRole(btn.dataset.id, 'user'));
                });
                document.querySelectorAll('.block-user-btn').forEach(btn => {
                    btn.addEventListener('click', () => blockUser(btn.dataset.id, true));
                });
                document.querySelectorAll('.unblock-user-btn').forEach(btn => {
                    btn.addEventListener('click', () => blockUser(btn.dataset.id, false));
                });
                document.querySelectorAll('.delete-user-btn').forEach(btn => {
                    btn.addEventListener('click', () => deleteUser(btn.dataset.id));
                });
            });
    }
        
        function moderateAd(id, action) {
            fetch('api/moderate_ad.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, action })
            }).then(() => loadModerationAds());
        }
        
        function rejectAd(id) {
            const reason = prompt('Укажите причину отклонения:');
            if (reason) {
                fetch('api/reject_ad.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, reason })
                }).then(() => loadModerationAds());
            } else {
                alert('Отмена');
            }
        }
        
        function unpublishAd(id) {
            if (confirm('Снять объявление с публикации?')) {
                fetch('api/unpublish_ad.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                }).then(() => loadAllAds());
            }
        }
        
        function deleteAd(id) {
            if (confirm('Удалить объявление? Действие необратимо.')) {
                fetch('api/delete_ad.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                }).then(() => {
                    loadAllAds();
                    loadModerationAds();
                });
            }
        }
        
        function changeUserRole(id, role) {
            fetch('api/change_role.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, role })
            }).then(() => loadUsers());
        }
        
        function blockUser(id, block) {
            fetch('api/block_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, block })
            }).then(() => loadUsers());
        }
        
        function deleteUser(id) {
            if (confirm('Удалить пользователя? Все его объявления и сообщения будут удалены.')) {
                fetch('api/delete_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                }).then(() => loadUsers());
            }
        }
        
        // Загружаем первую вкладку по умолчанию
        loadModerationAds();
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    <script src="js/theme_burger.js"></script>
</body>
</html>