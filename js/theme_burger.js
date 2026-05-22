// theme.js - переключение светлой/тёмной темы

(function() {
    // ===== ФУНКЦИИ ТЕМЫ =====
    function setTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            localStorage.setItem('theme', 'dark');
            const themeCheckbox = document.getElementById('themeCheckbox');
            if (themeCheckbox) {
                themeCheckbox.checked = true;
            }
        } else {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('theme', 'light');
            const themeCheckbox = document.getElementById('themeCheckbox');
            if (themeCheckbox) {
                themeCheckbox.checked = false;
            }
        }
    }
    
    function toggleTheme() {
        if (document.body.classList.contains('dark-theme')) {
            setTheme('light');
        } else {
            setTheme('dark');
        }
    }
    
    // Загрузка сохранённой темы
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        setTheme('dark');
    } else if (savedTheme === 'light') {
        setTheme('light');
    } else {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDark) {
            setTheme('dark');
        } else {
            setTheme('light');
        }
    }
    
    // ===== ИНИЦИАЛИЗАЦИЯ =====
    document.addEventListener('DOMContentLoaded', function() {
        // Кнопка переключения темы (тумблер)
        const themeCheckbox = document.getElementById('themeCheckbox');
        if (themeCheckbox) {
            themeCheckbox.addEventListener('change', toggleTheme);
        }
        
        // Закрываем меню при клике на ссылку в nav
        const navLinks = document.querySelectorAll('#navMenu a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                const burgerCheckbox = document.getElementById('burger-checkbox');
                if (burgerCheckbox) {
                    burgerCheckbox.checked = false;
                }
            });
        });
        
        // Закрываем меню при клике на оверлей
        const overlay = document.querySelector('.menu-overlay');
        if (overlay) {
            overlay.addEventListener('click', function() {
                const burgerCheckbox = document.getElementById('burger-checkbox');
                if (burgerCheckbox) {
                    burgerCheckbox.checked = false;
                }
            });
        }
        
        // Закрываем меню при изменении размера окна
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const burgerCheckbox = document.getElementById('burger-checkbox');
                if (burgerCheckbox) {
                    burgerCheckbox.checked = false;
                }
            }
        });
    });
})();