<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Получаем список диалогов
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username, u.is_blocked,
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
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ID выбранного пользователя для чата
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$selected_user = null;

if ($selected_user_id) {
    $stmt = $pdo->prepare("SELECT id, username, is_blocked FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_user = $stmt->fetch();
    if (!$selected_user) {
        $selected_user_id = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сообщения - HelpPaw</title>
    <link rel="stylesheet" href="css/chat.css">
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
        <h1>Сообщения</h1>
        
        <div class="chat-layout">
            <!-- Левая колонка - список диалогов -->
            <div class="chat-sidebar">
                <div class="sidebar-header">
                    <h3>Диалоги</h3>
                </div>
                <div class="conversations-list" id="conversationsList">
                    <?php if (empty($conversations)): ?>
                        <div class="no-conversations">У вас пока нет диалогов</div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="messages.php?user_id=<?= $conv['id'] ?>" 
                            class="conversation-item <?= ($selected_user_id == $conv['id']) ? 'active' : '' ?> <?= $conv['unread_count'] > 0 ? 'unread' : '' ?>"
                            data-user-id="<?= $conv['id'] ?>">
                                <div class="conv-info">
                                    <div class="conv-name"><?= htmlspecialchars($conv['username']) ?></div>
                                    <div class="conv-last-message">
                                        <?= htmlspecialchars(mb_substr($conv['last_message'] ?: '', 0, 40)) ?>
                                    </div>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <div class="conv-unread"><?= $conv['unread_count'] ?></div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Правая колонка - переписка -->
            <div class="chat-main">
                <?php if ($selected_user_id && $selected_user): ?>
                    <div class="chat-header">
                        <div class="chat-with"><?= htmlspecialchars($selected_user['username']) ?></div>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <div class="loading-messages">Загрузка сообщений...</div>
                    </div>
                    
                    <div class="chat-input-area">
                        <textarea id="messageInput" placeholder="Введите сообщение..." rows="3"></textarea>
                        <button id="sendMessageBtn" data-to-id="<?= $selected_user_id ?>">Отправить</button>
                    </div>
                <?php else: ?>
                    <div class="no-chat-selected">
                        <p>Выберите диалог из списка слева</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        let currentUserId = <?= $selected_user_id ?: 0 ?>;
        let lastMessageId = 0;
        let updateInterval = null;
        
        // Загрузка списка диалогов
        function loadConversations() {
            fetch('api/get_conversations.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('conversationsList');
                    if (!container) return;
                    
                    if (data.length === 0) {
                        container.innerHTML = '<div class="no-conversations">У вас пока нет диалогов</div>';
                        return;
                    }
                    
                    container.innerHTML = data.map(conv => `
                        <a href="messages.php?user_id=${conv.id}" 
                        class="conversation-item ${(currentUserId == conv.id) ? 'active' : ''} ${conv.unread_count > 0 ? 'unread' : ''}"
                        data-user-id="${conv.id}">
                            <div class="conv-info">
                                <div class="conv-name">${escapeHtml(conv.username)}</div>
                                <div class="conv-last-message">
                                    ${escapeHtml((conv.last_message || '').substring(0, 40))}
                                </div>
                            </div>
                            ${conv.unread_count > 0 ? `<div class="conv-unread">${conv.unread_count}</div>` : ''}
                        </a>
                    `).join('');
                })
                .catch(error => console.error('Ошибка загрузки диалогов:', error));
        }
        
        // Загрузка сообщений
        function loadMessages() {
            if (!currentUserId) return;
            
            let url = `api/get_messages.php?with_user_id=${currentUserId}`;
            if (lastMessageId > 0) {
                url += `&last_id=${lastMessageId}`;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    
                    const container = document.getElementById('chatMessages');
                    if (!container) return;
                    
                    if (data.length === 0 && lastMessageId === 0) {
                        container.innerHTML = '<div class="no-messages">Нет сообщений. Напишите что-нибудь!</div>';
                        return;
                    }
                    
                    // При первой загрузке показываем все сообщения
                    if (lastMessageId === 0) {
                        container.innerHTML = data.map(msg => `
                            <div class="message ${msg.from_user_id == <?= $user_id ?> ? 'message-out' : 'message-in'}">
                                <div class="message-text">${escapeHtml(msg.message)}</div>
                                <div class="message-time">${new Date(msg.created_at).toLocaleString()}</div>
                            </div>
                        `).join('');
                        if (data.length > 0) {
                            lastMessageId = Math.max(...data.map(m => m.id));
                        }
                        scrollToBottom();
                    } else if (data.length > 0) {
                        // Добавляем только новые сообщения
                        data.forEach(msg => {
                            if (msg.id > lastMessageId) {
                                const messageHtml = `
                                    <div class="message ${msg.from_user_id == <?= $user_id ?> ? 'message-out' : 'message-in'}">
                                        <div class="message-text">${escapeHtml(msg.message)}</div>
                                        <div class="message-time">${new Date(msg.created_at).toLocaleString()}</div>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', messageHtml);
                                lastMessageId = Math.max(lastMessageId, msg.id);
                            }
                        });
                        scrollToBottom();
                    }
                })
                .catch(error => console.error('Ошибка загрузки сообщений:', error));
        }
        
        // Отправка сообщения
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (!message || !currentUserId) return;
            
            fetch('api/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    to_user_id: currentUserId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    lastMessageId = 0;
                    loadMessages();
                    loadConversations();
                } else {
                    alert(data.error || 'Ошибка отправки');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Ошибка при отправке сообщения');
            });
        }
        
        function scrollToBottom() {
            const container = document.getElementById('chatMessages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Обработчики событий
        const sendBtn = document.getElementById('sendMessageBtn');
        if (sendBtn) {
            sendBtn.addEventListener('click', sendMessage);
        }
        
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }
        
        // Запускаем обновления
        if (currentUserId) {
            loadMessages();
            updateInterval = setInterval(() => {
                loadMessages();
                loadConversations();
            }, 5000);
        }
        
        loadConversations();
        
        // Очищаем интервал при уходе со страницы
        window.addEventListener('beforeunload', function() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    </script>
    <script src="js/theme_burger.js"></script>
</body>
</html>