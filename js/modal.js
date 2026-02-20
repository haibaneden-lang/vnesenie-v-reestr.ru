/**
 * Функции для работы с модальными окнами
 * Должен загружаться ПЕРЕД include.js чтобы функции были доступны сразу
 */

(function() {
    'use strict';
    
    let leadModalFetchStarted = false;

    // Определяем функции сразу при загрузке скрипта
    window.openModal = function(type) {
        console.log('🔥 Открываем модальное окно:', type);
        
        // Пробуем найти модальное окно
        let modal = document.getElementById('consultationModal');
        
        // Если модальное окно еще не загружено:
        // - на части страниц футер/модалка рендерятся JS'ом (getFooterHTML/getModalHTML)
        // - на части страниц футер грузится как HTML
        // Чтобы работало ВЕЗДЕ, если модалки нет — подгружаем каноничный шаблон из /forms/lead-modal
        if (!modal) {
            console.log('⏳ Модальное окно еще не загружено, пытаемся подгрузить...');

            if (!leadModalFetchStarted) {
                leadModalFetchStarted = true;
                fetch('/forms/lead-modal', { method: 'GET', headers: { 'Cache-Control': 'no-cache' } })
                    .then(r => r.ok ? r.text() : Promise.reject(new Error('HTTP ' + r.status)))
                    .then(html => {
                        if (!document.getElementById('consultationModal')) {
                            const wrap = document.createElement('div');
                            wrap.innerHTML = html;
                            while (wrap.firstChild) document.body.appendChild(wrap.firstChild);
                            console.log('✅ Единая модалка подгружена из /forms/lead-modal');
                            if (typeof window.__bindLeadForm === 'function') window.__bindLeadForm();
                        }
                    })
                    .catch(err => {
                        console.error('❌ Не удалось подгрузить /forms/lead-modal:', err);
                    });
            }

            // Ждем до 2 секунд пока модальное окно загрузится
            let attempts = 0;
            const checkInterval = setInterval(function() {
                attempts++;
                modal = document.getElementById('consultationModal');
                if (modal) {
                    clearInterval(checkInterval);
                    console.log('✅ Модальное окно найдено, открываем');
                    openModalInternal(modal, type);
                } else if (attempts > 20) {
                    clearInterval(checkInterval);
                    console.error('❌ Модальное окно не найдено после ожидания!');
                    alert('Модальное окно не найдено. Свяжитесь с нами по телефону: +7 920-898-17-18');
                }
            }, 100);
            return;
        }
        
        openModalInternal(modal, type);
    };
    
    function openModalInternal(modal, type) {
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');
        const serviceInput = document.getElementById('lead_service') || document.getElementById('service') || document.querySelector('input[name="service"]');
        const pageUrlInput = document.getElementById('lead_page_url') || document.querySelector('input[name="page_url"]');
        const sourceInput = document.getElementById('lead_source') || document.querySelector('input[name="source"]');

        if (pageUrlInput) pageUrlInput.value = window.location.href;
        if (sourceInput) sourceInput.value = type || 'modal';
        
        if (type === 'consultation') {
            if (modalTitle) modalTitle.textContent = 'Получить бесплатную консультацию';
            if (modalDescription) modalDescription.textContent = 'Заполните форму и мы свяжемся с вами в течение 30 минут в рабочее время';
            if (serviceInput) serviceInput.value = 'Консультация';
        } else if (type === 'order') {
            if (modalTitle) modalTitle.textContent = 'Заказать услугу';
            if (modalDescription) modalDescription.textContent = 'Оставьте контакты — подготовим предложение и свяжемся с вами в течение 30 минут';
            if (serviceInput) serviceInput.value = 'Заказ услуги';
        } else {
            if (serviceInput && !serviceInput.value) serviceInput.value = 'Консультация';
        }
        
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        console.log('✅ Модальное окно открыто');
    }
    
    // Совместимость: допускаем вызов closeModal с любыми аргументами
    window.closeModal = function() {
        console.log('🔒 Закрываем модальное окно');
        const modal = document.getElementById('consultationModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            
            const form = modal.querySelector('#leadForm');
            if (form) {
                form.reset();
                const successMessage = form.querySelector('.success-message');
                const errorMessage = form.querySelector('.error-message');
                if (successMessage) successMessage.style.display = 'none';
                if (errorMessage) errorMessage.style.display = 'none';
            }
            
            console.log('✅ Модальное окно закрыто');
        }
    };
    
    console.log('✅ Функции openModal и closeModal объявлены и доступны глобально');
})();

