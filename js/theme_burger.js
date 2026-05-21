// theme.js - переключение светлой/тёмной темы и бургер-меню

(function() {
    // ===== ФУНКЦИИ ТЕМЫ =====
    function setTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            localStorage.setItem('theme', 'dark');
            const toggleBtn = document.getElementById('themeToggle');
            if (toggleBtn) {
                toggleBtn.textContent = '☀️';
                toggleBtn.setAttribute('aria-label', 'Включить светлую тему');
            }
        } else {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('theme', 'light');
            const toggleBtn = document.getElementById('themeToggle');
            if (toggleBtn) {
                toggleBtn.textContent = '🌙';
                toggleBtn.setAttribute('aria-label', 'Включить тёмную тему');
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
    
    // ===== ФУНКЦИИ БУРГЕР-МЕНЮ =====
    function closeMenu() {
        const burger = document.getElementById('burgerMenu');
        const nav = document.getElementById('navMenu');
        const overlay = document.getElementById('menuOverlay');
        
        if (burger) burger.classList.remove('active');
        if (nav) nav.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function openMenu() {
        const burger = document.getElementById('burgerMenu');
        const nav = document.getElementById('navMenu');
        const overlay = document.getElementById('menuOverlay');
        
        if (burger) burger.classList.add('active');
        if (nav) nav.classList.add('active');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function toggleMenu() {
        const nav = document.getElementById('navMenu');
        if (nav && nav.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    }
    
    // ===== ИНИЦИАЛИЗАЦИЯ =====
    document.addEventListener('DOMContentLoaded', function() {
        // Кнопка переключения темы
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleTheme);
        }
        
        // Бургер-меню
        const burger = document.getElementById('burgerMenu');
        const overlay = document.getElementById('menuOverlay');
        
        if (burger) {
            burger.addEventListener('click', toggleMenu);
        }
        
        if (overlay) {
            overlay.addEventListener('click', closeMenu);
        }
        
        // Закрываем меню при клике на ссылку в nav
        const navLinks = document.querySelectorAll('#navMenu a');
        navLinks.forEach(link => {
            link.addEventListener('click', closeMenu);
        });
        
        // Закрываем меню при изменении размера окна
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMenu();
            }
        });
    });
})();