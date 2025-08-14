// Глобальные функции для модальных окон - объявляем сразу
window.openModal = function(type) {
    console.log('🔥 Открываем модальное окно:', type);
    const modal = document.getElementById('consultationModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    
    if (!modal) {
        console.error('❌ Модальное окно не найдено!');
        alert('Модальное окно не найдено. Свяжитесь с нами по телефону: +7 920-898-17-18');
        return;
    }
    
    console.log('✅ Модальное окно найдено');
    
    if (type === 'consultation') {
        if (modalTitle) modalTitle.textContent = 'Получить бесплатную консультацию';
        if (modalDescription) modalDescription.textContent = 'Заполните форму и мы свяжемся с вами в течение 30 минут для бесплатной консультации';
    } else if (type === 'order') {
        if (modalTitle) modalTitle.textContent = 'Заказать услугу';
        if (modalDescription) modalDescription.textContent = 'Заполните форму и мы подготовим для вас индивидуальное предложение';
    }
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    console.log('✅ Модальное окно открыто');
};

window.closeModal = function() {
    console.log('🔒 Закрываем модальное окно');
    const modal = document.getElementById('consultationModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        
        // Reset form
        const form = document.getElementById('contactForm');
        if (form) {
            form.reset();
        }
        
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');
        
        if (successMessage) successMessage.style.display = 'none';
        if (errorMessage) errorMessage.style.display = 'none';
        
        console.log('✅ Модальное окно закрыто');
    }
};

// Функция инициализации модальных окон
function initializeModalFunctions() {
    console.log('🚀 Инициализация модальных функций...');
    
    // Переинициализируем формы
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        // Удаляем старые обработчики
        contactForm.removeEventListener('submit', handleFormSubmit);
        contactForm.addEventListener('submit', handleFormSubmit);
        console.log('✅ Обработчик формы добавлен');
    }
    
    // Инициализируем форматирование телефона
    initializePhoneFormatting();
    
    // FAQ аккордеон
    initializeFAQ();
    
    // Анимации при прокрутке
    initializeScrollAnimations();
    
    // Обработчик закрытия модального окна при клике вне его
    initializeModalCloseHandlers();
    
    console.log('✅ Модальные функции инициализированы');
}

// Close modal when clicking outside
function initializeModalCloseHandlers() {
    // Удаляем старые обработчики
    document.removeEventListener('click', modalOutsideClickHandler);
    document.addEventListener('click', modalOutsideClickHandler);
}

function modalOutsideClickHandler(event) {
    const modal = document.getElementById('consultationModal');
    if (event.target === modal) {
        window.closeModal();
    }
}

// Phone number formatting
function initializePhoneFormatting() {
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        // Удаляем старый обработчик
        phoneInput.removeEventListener('input', phoneInputHandler);
        phoneInput.addEventListener('input', phoneInputHandler);
        console.log('✅ Форматирование телефона инициализировано');
    }
}

function phoneInputHandler(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0) {
        if (value.length <= 1) {
            value = '+7 (' + value;
        } else if (value.length <= 4) {
            value = '+7 (' + value.slice(1);
        } else if (value.length <= 7) {
            value = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4);
        } else if (value.length <= 9) {
            value = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7) + '-' + value.slice(7);
        } else {
            value = '+7 (' + value.slice(1, 4) + ') ' + value.slice(4, 7) + '-' + value.slice(7, 9) + '-' + value.slice(9, 11);
        }
    }
    e.target.value = value;
}

// Form submission handler
async function handleFormSubmit(e) {
    e.preventDefault();
    console.log('📧 Отправка формы...');
    
    const submitBtn = document.getElementById('submitBtn');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    if (!submitBtn || !successMessage || !errorMessage) {
        console.error('❌ Элементы формы не найдены');
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Отправляется...';
    
    // Get form data
    const formData = new FormData(e.target);
    const data = {
        name: formData.get('name'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        company: formData.get('company'),
        message: formData.get('message'),
        service: document.getElementById('modalTitle')?.textContent || 'Консультация'
    };
    
    console.log('📋 Данные формы:', data);
    
    try {
        // РЕАЛЬНАЯ отправка на сервер - ИСПРАВЛЕННЫЙ ПУТЬ (всегда корень сайта)
        const response = await fetch('/send-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        console.log('📡 Ответ сервера:', response.status);
        
        if (response.ok) {
            const result = await response.json();
            console.log('📋 Результат:', result);
            
            if (result.success) {
                successMessage.style.display = 'block';
                errorMessage.style.display = 'none';
                
                // Reset form after delay
                setTimeout(() => {
                    window.closeModal();
                }, 3000);
            } else {
                throw new Error(result.error || 'Server error');
            }
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
        
    } catch (error) {
        console.error('❌ Ошибка отправки:', error);
        // Show error message
        errorMessage.style.display = 'block';
        successMessage.style.display = 'none';
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = 'Отправить заявку';
    }
}

// FAQ Accordion functionality
function initializeFAQ() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    console.log('❓ Найдено FAQ вопросов:', faqQuestions.length);
    
    faqQuestions.forEach((question, index) => {
        // Удаляем старые обработчики
        question.replaceWith(question.cloneNode(true));
    });
    
    // Добавляем новые обработчики
    document.querySelectorAll('.faq-question').forEach((question, index) => {
        question.addEventListener('click', () => {
            const faqItem = question.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            console.log('❓ Клик по FAQ', index, 'активен:', isActive);
            
            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
            }
        });
    });
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        // Удаляем старые обработчики
        anchor.removeEventListener('click', smoothScrollHandler);
        anchor.addEventListener('click', smoothScrollHandler);
    });
}

function smoothScrollHandler(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Animate elements on scroll
function initializeScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe feature cards, service items, reviews, and pricing cards
    const elementsToAnimate = document.querySelectorAll('.feature-card, .service-item, .review-card, .pricing-card, .faq-item');
    console.log('🎬 Элементов для анимации:', elementsToAnimate.length);
    
    elementsToAnimate.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
}

// Главная функция инициализации при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Script.js: DOM загружен');
    
    // Инициализируем базовые функции
    initializeSmoothScrolling();
    
    // Проверяем доступность функций модального окна
    console.log('🔍 Проверка функций модального окна:');
    console.log('openModal:', typeof window.openModal);
    console.log('closeModal:', typeof window.closeModal);
    
    // Ждем загрузки компонентов и инициализируем остальное
    setTimeout(() => {
        initializeModalFunctions();
        
        // Финальная проверка
        setTimeout(() => {
            console.log('🎯 Финальная проверка функций:');
            console.log('openModal доступна:', typeof window.openModal === 'function');
            console.log('closeModal доступна:', typeof window.closeModal === 'function');
            
            // Обновляем отладочную информацию
            if (typeof updateDebugInfo === 'function') {
                updateDebugInfo('openModal function: ' + (typeof window.openModal === 'function' ? '✅' : '❌'));
            }
        }, 1000);
    }, 500);
});

// Экспортируем функции для глобального доступа
window.initializeModalFunctions = initializeModalFunctions;