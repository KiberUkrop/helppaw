<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Только для авторизованных
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $animal_type = $_POST['animal_type'] ?? '';
    $status = $_POST['status'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    
    $errors = [];
    
    // Валидация
    if (empty($title)) {
        $errors[] = 'Введите заголовок';
    } elseif (strlen($title) < 3) {
        $errors[] = 'Заголовок должен содержать минимум 3 символа';
    }
    
    if (empty($animal_type)) {
        $errors[] = 'Выберите тип животного';
    }
    
    if (empty($status)) {
        $errors[] = 'Выберите статус';
    }
    
    if (empty($description)) {
        $errors[] = 'Введите описание';
    }
    
    // Обработка фото
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/ads/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Можно загружать только JPG, PNG, GIF, WEBP';
        } else {
            $filename = 'ad_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $target = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photo_path = $target;
            } else {
                $errors[] = 'Ошибка при загрузке фото';
            }
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO ads (user_id, title, animal_type, status, description, location, photo_path, is_approved) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $is_approved = 'false';
        
        if ($stmt->execute([$_SESSION['user_id'], $title, $animal_type, $status, $description, $location, $photo_path, $is_approved])) {
            $success = 'Объявление создано! Оно будет опубликовано после проверки администратором.';
            $_POST = [];
        } else {
            $error = 'Ошибка при создании объявления. Попробуйте позже.';
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
    <title>Добавить объявление - HelpPaw</title>
    <link rel="stylesheet" href="css/add_ad.css">
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
            <a href="add_ad.php">Добавить объявление</a>
            <a href="my_ads.php">Мои объявления</a>
            <a href="messages.php">Сообщения</a>
            <a href="profile.php">Профиль</a>
            <?php if (isAdmin()): ?>
                <a href="admin_panel.php">Админ-панель</a>
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
        <h1>Создание объявления</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="create-form">
            <div class="form-row-group">
                <div class="form-left">
                    <div class="form-group">
                        <div class="photo-upload" id="photoUpload">
                            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="photo-preview">
                                <svg viewBox="0 0 48 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M46 34C46 35.0609 45.5786 36.0783 44.8284 36.8284C44.0783 37.5786 43.0609 38 42 38H6C4.93913 38 3.92172 37.5786 3.17157 36.8284C2.42143 36.0783 2 35.0609 2 34V12C2 10.9391 2.42143 9.92172 3.17157 9.17157C3.92172 8.42143 4.93913 8 6 8H14L18 2H30L34 8H42C43.0609 8 44.0783 8.42143 44.8284 9.17157C45.5786 9.92172 46 10.9391 46 12V34Z" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M24 30C28.4183 30 32 26.4183 32 22C32 17.5817 28.4183 14 24 14C19.5817 14 16 17.5817 16 22C16 26.4183 19.5817 30 24 30Z" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <p>Нажмите для загрузки</p>
                                <span class="preview-hint">JPG, PNG, GIF, WEBP до 5MB</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-right">
                    <div class="form-group">
                        <label for="title">Заголовок</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="Например: Потерялась собака" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="animal_type">Тип животного</label>
                            <select id="animal_type" name="animal_type" required>
                                <option value="">Выберите</option>
                                <option value="dog" <?= (($_POST['animal_type'] ?? '') == 'dog') ? 'selected' : '' ?>>Собака</option>
                                <option value="cat" <?= (($_POST['animal_type'] ?? '') == 'cat') ? 'selected' : '' ?>>Кошка</option>
                                <option value="other" <?= (($_POST['animal_type'] ?? '') == 'other') ? 'selected' : '' ?>>Другое</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select id="status" name="status" required>
                                <option value="">Выберите</option>
                                <option value="lost" <?= (($_POST['status'] ?? '') == 'lost') ? 'selected' : '' ?>>Потерялся</option>
                                <option value="found" <?= (($_POST['status'] ?? '') == 'found') ? 'selected' : '' ?>>Нашёлся</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Место</label>
                        <input type="text" id="location" name="location" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" placeholder="Город, район, ориентир">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea id="description" name="description" rows="5" placeholder="Опишите приметы животного, особые детали, кличку..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Опубликовать</button>
        </form>
    </main>

    <script>
        const photoInput = document.getElementById('photo');
        const photoPreview = document.querySelector('.photo-preview');
        
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    photoPreview.innerHTML = `<img src="${event.target.result}" class="preview-image">`;
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.innerHTML = `
                    <svg viewBox="0 0 48 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M46 34C46 35.0609 45.5786 36.0783 44.8284 36.8284C44.0783 37.5786 43.0609 38 42 38H6C4.93913 38 3.92172 37.5786 3.17157 36.8284C2.42143 36.0783 2 35.0609 2 34V12C2 10.9391 2.42143 9.92172 3.17157 9.17157C3.92172 8.42143 4.93913 8 6 8H14L18 2H30L34 8H42C43.0609 8 44.0783 8.42143 44.8284 9.17157C45.5786 9.92172 46 10.9391 46 12V34Z" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M24 30C28.4183 30 32 26.4183 32 22C32 17.5817 28.4183 14 24 14C19.5817 14 16 17.5817 16 22C16 26.4183 19.5817 30 24 30Z" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p>Нажмите для загрузки</p>
                    <span class="preview-hint">JPG, PNG, GIF, WEBP до 5MB</span>
                `;
            }
        });
    </script>
    <script src="js/theme_burger.js"></script>

</body>
</html>