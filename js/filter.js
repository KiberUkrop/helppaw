// filter.js - AJAX фильтрация объявлений

document.addEventListener('DOMContentLoaded', function() {
    const animalTypeSelect = document.getElementById('filter-animal-type');
    const statusSelect = document.getElementById('filter-status');
    const applyBtn = document.getElementById('apply-filter');
    const resetBtn = document.getElementById('reset-filter');
    const adsContainer = document.getElementById('pets-container');
    
    if (!adsContainer) return;
    
    function loadAds(animalType, status) {
        let url = 'api/filter_ads.php?';
        if (animalType) url += `animal_type=${animalType}&`;
        if (status) url += `status=${status}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    adsContainer.innerHTML = '<p class="no-pets">Объявления не найдены</p>';
                    return;
                }
                
                let html = '';
                for (const ad of data) {
                    const photoHtml = ad.photo_path 
                        ? `<img src="${escapeHtml(ad.photo_path)}" alt="Фото животного" class="pet-photo">`
                        : '<div class="pet-photo-placeholder">Нет фото</div>';
                    
                    const statusText = ad.status === 'lost' ? 'Потерялся' : 'Нашёлся';
                    const currentUserId = window.currentUserId || 0;
                    const messageLink = (currentUserId && currentUserId != ad.user_id) 
                        ? `<a href="messages.php?user_id=${ad.user_id}" class="pet-message-btn">Написать автору</a>`
                        : '';
                    
                    html += `
                        <div class="pet-card">
                            <div class="pet-card-left">
                                ${photoHtml}
                            </div>
                            <div class="pet-card-right">
                                <h3>${escapeHtml(ad.title)}</h3>
                                <div class="pet-details">
                                    <p><strong>Статус:</strong> ${statusText}</p>
                                    <p><strong>Описание:</strong> ${escapeHtml(ad.description).replace(/\n/g, '<br>')}</p>
                                    <p><strong>Место:</strong> ${escapeHtml(ad.location || 'Не указано')}</p>
                                    <p><strong>Автор:</strong> ${escapeHtml(ad.username)}</p>
                                </div>
                                ${messageLink}
                            </div>
                        </div>
                    `;
                }
                adsContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Ошибка:', error);
                adsContainer.innerHTML = '<p class="no-pets">Ошибка загрузки объявлений</p>';
            });
    }
    
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            const animalType = animalTypeSelect.value;
            const status = statusSelect.value;
            loadAds(animalType, status);
        });
    }
    
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            animalTypeSelect.value = '';
            statusSelect.value = '';
            loadAds('', '');
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});