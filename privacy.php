<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика конфиденциальности - HelpPaw</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .privacy-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: var(--color-beige);
            border-radius: 30px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .privacy-container h1 {
            font-size: 32px;
            font-family: var(--font-h1);
            color: var(--color-dark-green);
            margin-bottom: 10px;
        }
        
        .privacy-container h2 {
            font-size: 22px;
            font-family: var(--font-h1);
            color: var(--color-dark-green);
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
        .privacy-container p {
            font-size: 16px;
            font-family: var(--font-h2);
            color: var(--color-dark-green);
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .privacy-container ul {
            padding-left: 30px;
            margin-bottom: 20px;
        }
        
        .privacy-container li {
            font-size: 16px;
            font-family: var(--font-h2);
            color: var(--color-dark-green);
            margin-bottom: 8px;
        }
        
        .privacy-date {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--color-light-beige);
            font-size: 14px;
            color: var(--color-dark-green);
            opacity: 0.7;
        }
        
        @media (max-width: 768px) {
            .privacy-container {
                padding: 25px;
            }
            
            .privacy-container h1 {
                font-size: 28px;
            }
            
            .privacy-container h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

    <header>
        <a class="logo" href="index.php">
            <svg viewBox="0 0 70 70" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M27.3794 43.9807C34.8743 29.9935 37.8837 29.6756 38.4513 31.2649V34.4441C48.6071 33.6543 51.1942 41.4966 49.5553 44.7148L49.5235 44.7755C52.9302 46.3651 52.0785 47.9544 51.2269 49.544C49.1829 53.3587 39.587 53.7827 35.0446 53.5176C16.3073 51.1335 11.1971 58.2861 11.1972 63.8493C63.6495 67.7965 65.6807 54.4921 66.5402 48.8618C66.5461 48.8238 66.5516 48.7865 66.5575 48.7492C69.2828 29.6756 57.1888 25.7018 56.3372 26.4966L57.1888 5.83334C46.2869 5.83334 45.2649 12.1913 45.2649 13.7807C24.1429 13.7807 21.4175 20.1387 20.5658 25.7018H6.08698C6.08698 41.5966 12.9005 43.1862 18.8624 43.9807H27.3794ZM33.3413 25.7018C33.3413 27.0186 32.1974 28.086 30.7863 28.086C29.3749 28.086 28.2311 27.0186 28.2311 25.7018C28.2311 24.3851 29.3749 23.3176 30.7863 23.3176C32.1974 23.3176 33.3413 24.3851 33.3413 25.7018ZM46.9682 43.9807C46.9682 44.8586 46.2058 45.5703 45.2649 45.5703C44.3243 45.5703 43.5616 44.8586 43.5616 43.9807C43.5616 43.1031 44.3243 42.3914 45.2649 42.3914C46.2058 42.3914 46.9682 43.1031 46.9682 43.9807Z"/>
            </svg>
            <h1>Help <br> Paw</h1>
        </a>
        
        <input type="checkbox" id="burger-checkbox" class="burger-checkbox">
        <label class="burger" for="burger-checkbox"></label>
        
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
            <div class="theme-switch-wrapper">
                <label class="theme-switch">
                    <input type="checkbox" id="themeCheckbox">
                    <span class="theme-switch-slider"></span>
                </label>
            </div>
        </nav>
        
        <div class="menu-overlay"></div>
    </header>

    <main>
        <div class="privacy-container">
            <h1>Политика конфиденциальности</h1>
            
            <p>Настоящая Политика конфиденциальности определяет порядок сбора, хранения, обработки и защиты персональных данных пользователей сайта «HelpPaw» (далее — Сайт).</p>
            
            <h2>1. Какие данные мы собираем</h2>
            <p>При регистрации на Сайте вы предоставляете следующие данные:</p>
            <ul>
                <li>имя пользователя (логин);</li>
                <li>адрес электронной почты;</li>
                <li>пароль (хранится в зашифрованном виде).</li>
            </ul>
            <p>При создании объявления вы можете дополнительно указать:</p>
            <ul>
                <li>описание животного;</li>
                <li>местоположение;</li>
                <li>фотографию животного.</li>
            </ul>
            
            <h2>2. Как мы используем ваши данные</h2>
            <p>Собранные данные используются исключительно для обеспечения работы Сайта:</p>
            <ul>
                <li>для авторизации и идентификации пользователя;</li>
                <li>для публикации объявлений о потерянных и найденных животных;</li>
                <li>для работы чата между пользователями;</li>
                <li>для связи с вами в случае необходимости (восстановление пароля).</li>
            </ul>
            
            <h2>3. Передача данных третьим лицам</h2>
            <p>Мы не передаём ваши персональные данные третьим лицам, за исключением случаев, предусмотренных законодательством Российской Федерации.</p>
            
            <h2>4. Хранение и защита данных</h2>
            <p>Ваши данные хранятся на защищённых серверах. Пароли пользователей хранятся в зашифрованном виде с использованием алгоритма bcrypt. Передача данных между вашим браузером и сервером осуществляется по защищённому протоколу HTTPS.</p>
            
            <h2>5. Ваши права</h2>
            <p>Вы имеете право:</p>
            <ul>
                <li>получить информацию о том, какие данные о вас хранятся;</li>
                <li>изменить свои данные в личном кабинете;</li>
                <li>удалить свою учётную запись (обратившись к администратору).</li>
            </ul>
            
            <h2>6. Срок хранения данных</h2>
            <p>Ваши данные хранятся до момента удаления вашей учётной записи. После удаления аккаунта все ваши объявления и сообщения также удаляются.</p>
            
            <h2>7. Изменение Политики конфиденциальности</h2>
            <p>Мы оставляем за собой право вносить изменения в настоящую Политику конфиденциальности. Обновлённая версия публикуется на этой странице.</p>
            
            <div class="privacy-date">
                <p>Дата последнего обновления: 21 мая 2026 года</p>
                <p>Контакты для вопросов: helppaw@gmail.com</p>
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