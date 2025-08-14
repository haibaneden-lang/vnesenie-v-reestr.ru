// Функция для загрузки HTML компонентов
async function includeHTML() {
    console.log('Начинаем загрузку компонентов...');
    const elements = document.querySelectorAll('[data-include]');
    console.log('Найдено элементов для загрузки:', elements.length);
    
    for (let element of elements) {
        const file = element.getAttribute('data-include');
        console.log('Загружаем файл:', file);
        
        if (file) {
            try {
                // Убираем протокол для автоматического выбора HTTP/HTTPS
                const url = new URL(file, window.location.origin);
                
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache'
                    }
                });
                
                console.log('Ответ сервера для', file, ':', response.status);
                
                if (response.ok) {
                    const html = await response.text();
                    element.innerHTML = html;
                    element.removeAttribute('data-include');
                    console.log('Успешно загружен:', file);
                } else {
                    console.error('Ошибка загрузки файла:', file, response.status);
                    // Вместо показа ошибки, используем fallback
                    loadFallbackContent(element, file);
                }
            } catch (error) {
                console.error('Ошибка загрузки компонента:', file, error);
                // Используем fallback при любой ошибке
                loadFallbackContent(element, file);
            }
        }
    }
    
    console.log('Загрузка компонентов завершена, инициализируем функции...');
    // Инициализируем функциональность после загрузки компонентов
    setTimeout(initializeComponents, 100);
}

// Функция для загрузки fallback контента
function loadFallbackContent(element, filename) {
    console.log('Загружаем fallback для:', filename);
    
    if (filename.includes('header')) {
        element.innerHTML = getHeaderHTML();
    } else if (filename.includes('footer')) {
        element.innerHTML = getFooterHTML();
    } else if (filename.includes('modal')) {
        element.innerHTML = getModalHTML();
    }
    
    element.removeAttribute('data-include');
}

// Инициализация компонентов после загрузки
function initializeComponents() {
    console.log('Инициализация компонентов...');
    
    // Мобильное меню
    initializeMobileMenu();
    
    // Эффект прокрутки для шапки
    initializeHeaderScroll();
    
    // Dropdown меню для десктопа
    initializeDropdownMenus();
    
    // Инициализация селектора городов
    initializeCitySelector();
    
    // Переинициализируем функции из script.js если они есть
    if (typeof initializeModalFunctions === 'function') {
        initializeModalFunctions();
    }
    
    console.log('Инициализация завершена');
}

// ИСПРАВЛЕННОЕ МОБИЛЬНОЕ МЕНЮ
function initializeMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    console.log('Мобильное меню - toggle:', !!mobileToggle, 'menu:', !!navMenu);
    
    if (mobileToggle && navMenu) {
        // Удаляем старые обработчики если есть
        mobileToggle.replaceWith(mobileToggle.cloneNode(true));
        const newMobileToggle = document.querySelector('.mobile-menu-toggle');
        
        newMobileToggle.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('Клик по мобильному меню');
            
            const isActive = newMobileToggle.classList.contains('active');
            
            if (isActive) {
                // Закрываем меню
                closeMobileMenu(newMobileToggle, navMenu);
            } else {
                // Открываем меню
                openMobileMenu(newMobileToggle, navMenu);
            }
        });
        
        // Закрыть меню при клике на ссылку (кроме dropdown toggle)
        navMenu.addEventListener('click', (e) => {
            if (e.target.tagName === 'A' && !e.target.classList.contains('nav-dropdown-toggle')) {
                closeMobileMenu(newMobileToggle, navMenu);
            }
        });
        
        // Закрыть меню при клике вне его
        document.addEventListener('click', (e) => {
            if (!newMobileToggle.contains(e.target) && !navMenu.contains(e.target)) {
                closeMobileMenu(newMobileToggle, navMenu);
            }
        });
        
        // Закрыть меню при изменении размера экрана
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeMobileMenu(newMobileToggle, navMenu);
            }
        });
        
        // Закрыть меню при нажатии Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && newMobileToggle.classList.contains('active')) {
                closeMobileMenu(newMobileToggle, navMenu);
            }
        });
        
        console.log('Мобильное меню инициализировано');
    }
}

// Функция открытия мобильного меню
function openMobileMenu(toggle, menu) {
    toggle.classList.add('active');
    menu.classList.add('active');
    document.body.classList.add('menu-open');
    
    // Сохраняем позицию прокрутки
    const scrollY = window.scrollY;
    document.body.style.top = `-${scrollY}px`;
    
    // Фокус на первом элементе меню для доступности
    const firstLink = menu.querySelector('a');
    if (firstLink) {
        setTimeout(() => firstLink.focus(), 100);
    }
}

// Функция закрытия мобильного меню
function closeMobileMenu(toggle, menu) {
    toggle.classList.remove('active');
    menu.classList.remove('active');
    document.body.classList.remove('menu-open');
    
    // Восстанавливаем позицию прокрутки
    const scrollY = document.body.style.top;
    document.body.style.top = '';
    if (scrollY) {
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
    }
}

// Эффект прокрутки для шапки
function initializeHeaderScroll() {
    // Удаляем старый обработчик если есть
    window.removeEventListener('scroll', headerScrollHandler);
    window.addEventListener('scroll', headerScrollHandler);
    console.log('Обработчик прокрутки шапки добавлен');
}

function headerScrollHandler() {
    const header = document.querySelector('.header');
    if (header) {
        if (window.scrollY > 100) {
            header.style.background = 'rgba(30, 60, 114, 0.95)';
            header.style.backdropFilter = 'blur(10px)';
        } else {
            header.style.background = 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)';
            header.style.backdropFilter = 'none';
        }
    }
}

// ИСПРАВЛЕННОЕ DROPDOWN МЕНЮ
function initializeDropdownMenus() {
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    console.log('Найдено dropdown меню:', dropdowns.length);
    
    dropdowns.forEach((dropdown, index) => {
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        const submenu = dropdown.querySelector('.nav-submenu');
        
        if (toggle && submenu) {
            // Очищаем старые обработчики
            toggle.replaceWith(toggle.cloneNode(true));
            const newToggle = dropdown.querySelector('.nav-dropdown-toggle');
            
            // Обработчик для мобильных устройств
            newToggle.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Проверяем, мобильная ли версия
                if (window.innerWidth <= 768) {
                    const isActive = dropdown.classList.contains('mobile-submenu-active');
                    
                    // Закрываем все другие подменю
                    document.querySelectorAll('.nav-dropdown').forEach(item => {
                        if (item !== dropdown) {
                            item.classList.remove('mobile-submenu-active');
                        }
                    });
                    
                    // Переключаем текущее подменю
                    if (isActive) {
                        dropdown.classList.remove('mobile-submenu-active');
                    } else {
                        dropdown.classList.add('mobile-submenu-active');
                    }
                    
                    console.log('Переключено мобильное подменю', index, !isActive ? 'открыто' : 'закрыто');
                }
            });
            
            // Обработчики для десктопа (hover) - только если не мобильная версия
            let timeout;
            
            dropdown.addEventListener('mouseenter', () => {
                if (window.innerWidth > 768) {
                    clearTimeout(timeout);
                    submenu.style.display = 'block';
                    console.log('Открыто dropdown', index);
                }
            });
            
            dropdown.addEventListener('mouseleave', () => {
                if (window.innerWidth > 768) {
                    timeout = setTimeout(() => {
                        submenu.style.display = 'none';
                        console.log('Закрыто dropdown', index);
                    }, 300);
                }
            });
        }
    });
}

// УЛУЧШЕННАЯ ИНИЦИАЛИЗАЦИЯ СЕЛЕКТОРА ГОРОДОВ
function initializeCitySelector() {
    const cityBtn = document.getElementById('cityBtn');
    const cityDropdown = document.getElementById('cityDropdown');
    const currentCitySpan = document.getElementById('currentCity');
    const cityDropdownItems = document.querySelectorAll('.city-dropdown-item');
    
    console.log('Инициализация селектора городов:', {
        cityBtn: !!cityBtn,
        cityDropdown: !!cityDropdown,
        currentCitySpan: !!currentCitySpan,
        itemsCount: cityDropdownItems.length
    });
    
    if (!cityBtn || !cityDropdown || !currentCitySpan) {
        console.log('Селектор городов не найден');
        return;
    }
    
    // Определяем текущий город по URL
    setCurrentCityFromUrl();
    
    // Функция для определения направления открытия dropdown
    function getDropdownDirection() {
        const btnRect = cityBtn.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const spaceAbove = btnRect.top;
        const spaceBelow = viewportHeight - btnRect.bottom;
        const dropdownHeight = 400; // примерная высота dropdown
        
        // Если снизу места больше или если сверху недостаточно места
        if (spaceBelow > dropdownHeight || spaceAbove < dropdownHeight) {
            return 'down';
        }
        return 'up';
    }
    
    // Функция для позиционирования dropdown
    function positionDropdown() {
        const direction = getDropdownDirection();
        cityDropdown.classList.remove('dropdown-up', 'dropdown-down');
        
        if (direction === 'up') {
            cityDropdown.classList.add('dropdown-up');
        } else {
            cityDropdown.classList.add('dropdown-down');
        }
    }
    
    // Обработка клика/тача по кнопке города
    function toggleCityDropdown(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const isActive = cityBtn.classList.contains('active');
        
        if (isActive) {
            cityBtn.classList.remove('active');
            cityDropdown.classList.remove('show');
        } else {
            // Позиционируем dropdown перед открытием
            positionDropdown();
            cityBtn.classList.add('active');
            cityDropdown.classList.add('show');
        }
        
        console.log('Селектор городов:', isActive ? 'закрыт' : 'открыт');
    }
    
    // Универсальные обработчики для всех устройств
    cityBtn.addEventListener('click', toggleCityDropdown);
    cityBtn.addEventListener('touchstart', toggleCityDropdown, { passive: false });
    
    // Закрытие выпадающего списка при клике/таче вне его
    function closeDropdownOnOutsideClick(e) {
        if (!cityBtn.contains(e.target) && !cityDropdown.contains(e.target)) {
            cityBtn.classList.remove('active');
            cityDropdown.classList.remove('show');
        }
    }
    
    document.addEventListener('click', closeDropdownOnOutsideClick);
    document.addEventListener('touchstart', closeDropdownOnOutsideClick, { passive: true });
    
    // Обработка выбора города
    cityDropdownItems.forEach(item => {
        function selectCity(e) {
            console.log('Выбран город:', item.getAttribute('data-city'));
            cityBtn.classList.remove('active');
            cityDropdown.classList.remove('show');
            // Не preventDefault(), чтобы ссылка сработала
        }
        
        item.addEventListener('click', selectCity);
        item.addEventListener('touchend', selectCity, { passive: true });
    });
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cityBtn.classList.remove('active');
            cityDropdown.classList.remove('show');
        }
    });
    
    // Перепозиционирование при изменении размера экрана или ориентации
    function handleResize() {
        if (cityDropdown.classList.contains('show')) {
            positionDropdown();
        }
    }
    
    window.addEventListener('resize', handleResize);
    window.addEventListener('orientationchange', () => {
        setTimeout(handleResize, 100); // Задержка для iOS
    });
    
    console.log('Селектор городов инициализирован');
}

// Определение текущего города по URL
function setCurrentCityFromUrl() {
    const currentPath = window.location.pathname;
    const cityMatch = currentPath.match(/\/vkljuchenie-v-reestr-minpromtorga\/([^\/]+)/);
    
    const currentCitySpan = document.getElementById('currentCity');
    if (!currentCitySpan) return;
    
    const cityNames = {
        'moskva': 'Москва',
        'sankt-peterburg': 'Санкт-Петербург',
        'novosibirsk': 'Новосибирск',
        'yekaterinburg': 'Екатеринбург',
        'kazan': 'Казань',
        'nizhniy-novgorod': 'Нижний Новгород',
        'chelyabinsk': 'Челябинск',
        'samara': 'Самара',
        'omsk': 'Омск',
        'rostov-na-donu': 'Ростов-на-Дону',
        'ufa': 'Уфа',
        'krasnoyarsk': 'Красноярск',
        'voronezh': 'Воронеж',
        'perm': 'Пермь',
        'volgograd': 'Волгоград',
        'krasnodar': 'Краснодар',
        'saratov': 'Саратов',
        'tyumen': 'Тюмень',
        'tolyatti': 'Тольятти',
        'izhevsk': 'Ижевск',
        'barnaul': 'Барнаул',
        'ulyanovsk': 'Ульяновск',
        'irkutsk': 'Иркутск',
        'habarovsk': 'Хабаровск',
        'vladivostok': 'Владивосток'
    };
    
    if (cityMatch) {
        const citySlug = cityMatch[1];
        
        if (cityNames[citySlug]) {
            currentCitySpan.textContent = cityNames[citySlug];
            console.log('Текущий город установлен:', cityNames[citySlug]);
            
            // Отмечаем активный город в выпадающем списке
            const cityDropdownItems = document.querySelectorAll('.city-dropdown-item');
            cityDropdownItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('data-city') === citySlug) {
                    item.classList.add('active');
                }
            });
        }
    } else {
        currentCitySpan.textContent = 'Россия';
        console.log('Текущий город: Россия (по умолчанию)');
        
        // Отмечаем "Россия" как активный
        const cityDropdownItems = document.querySelectorAll('.city-dropdown-item');
        cityDropdownItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-city') === 'russia') {
                item.classList.add('active');
            }
        });
    }
}

// Функции для получения HTML контента - HEADER БЕЗ ГОРОДОВ
function getHeaderHTML() {
    return `
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <div class="logo">
                        <div class="logo-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <!-- Документ/реестр -->
                                <rect x="4" y="3" width="12" height="16" rx="1" fill="white" opacity="0.9"/>
                                <!-- Строки реестра -->
                                <line x1="6" y1="7" x2="14" y2="7" stroke="#667eea" stroke-width="0.8"/>
                                <line x1="6" y1="9" x2="14" y2="9" stroke="#667eea" stroke-width="0.8"/>
                                <line x1="6" y1="11" x2="14" y2="11" stroke="#667eea" stroke-width="0.8"/>
                                <line x1="6" y1="13" x2="14" y2="13" stroke="#667eea" stroke-width="0.8"/>
                                <!-- Галочка гарантии -->
                                <circle cx="18" cy="6" r="3" fill="#27ae60"/>
                                <path d="M16.5 6l1 1 2-2" stroke="white" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                                <!-- Щит защиты/гарантии -->
                                <path d="M2 12c0-2 1-3 2-3s2 1 2 3c0 2-1 4-2 4s-2-2-2-4z" fill="#ffa500" opacity="0.8"/>
                            </svg>
                        </div>
                        <a href="/" style="color: white; text-decoration: none;">
                            <span>Реестр Гарант</span>
                        </a>
                    </div>
                    
                    <!-- Навигационное меню -->
                    <nav class="main-navigation">
                        <ul class="nav-menu">
                            <li><a href="/">Главная</a></li>
                           <li class="nav-dropdown">
    <a href="#" class="nav-dropdown-toggle">Реестры <span class="dropdown-arrow">▼</span></a>
    <ul class="nav-submenu">
        <li class="nav-dropdown">
            <a href="/gisp-minpromtorg" class="nav-dropdown-toggle">Включение в Минпромторг <span class="dropdown-arrow">▼</span></a>
            <ul class="nav-submenu nav-submenu-nested">
                <li><a href="/industrial">Промышленная продукция</a></li>
                <li><a href="/software">Программное обеспечение</a></li>
                <li><a href="/radioelectronic">Радиоэлектронная продукция</a></li>
                <li><a href="/medical-devices">Медицинские изделия</a></li>
                <li><a href="/telecom-equipment">Телекоммуникационное оборудование</a></li>
                <li><a href="/oil-gas-equipment">Нефтегазовое оборудование</a></li>
                <li><a href="/PP-RF-№-102">Реестр фармацевтической продукции</a></li>
                <li><a href="/reestr-proizvoditelej-avtokomponentov">Реестр производителей автокомпонентов</a></li>
                <li><a href="/paketnoe-predlozhenie-dlya-ekonomii-vremeni-i-usiliy">Пакетное предложение</a></li>
                <li><a href="/pricing">Стоимость услуг</a></li>
            </ul>
        </li>
        <li class="nav-dropdown">
            <a href="/roskomnadzor-registration" class="nav-dropdown-toggle">Регистрация в РКН <span class="dropdown-arrow">▼</span></a>
            <ul class="nav-submenu nav-submenu-nested">
                <li><a href="/roskomnadzor-preparation-expanded">Подготовка к проверке Роскомнадзора</a></li>
                <li><a href="/roskomnadzor-services">Стоимость регистрации в реестре РКН</a></li>
            </ul>
        </li>
        <li><a href="/tendernoe-soprovozhdenie">Тендерное сопровождение</a></li>
        <li><a href="/vnesenie-v-reestr-turoperatorov">Включение в реестр туроператоров</a></li>
    </ul>
</li>
                           
                            <li><a href="/about">О нас</a></li>
                            <li><a href="/contacts">Контакты</a></li>
                        </ul>
                        
                        <!-- Мобильное меню кнопка -->
                        <div class="mobile-menu-toggle">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </nav>
                    
                    <div class="contact-info">
                        <div class="phone">
                            <a href="tel:+79208981718">+7 920-898-17-18</a>
                        </div>
                        <div class="whatsapp">
                            <a href="https://wa.me/79208981718" target="_blank" title="Написать в WhatsApp" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.516" fill="white"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                        </div>
                        <div class="telegram">
                            <a href="https://t.me/reestr_garant" target="_blank" title="Наш канал в Telegram" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.11.02-1.91 1.21-5.4 3.56-.51.35-.97.52-1.38.51-.45-.01-1.32-.26-1.97-.47-.8-.26-1.43-.4-1.38-.85.03-.23.36-.47.99-.72 3.88-1.69 6.48-2.81 7.82-3.35 3.73-1.55 4.5-1.82 5.01-1.83.11 0 .36.03.52.17.13.12.17.27.19.38-.01.06-.01.24-.01.24z" fill="white"/>
                                </svg>
                                <span>Telegram</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    `;
}

function getModalHTML() {
    return `
        <div id="consultationModal" class="modal">
            <div class="modal-content">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <h3 id="modalTitle">Получить консультацию</h3>
                <p id="modalDescription">Заполните форму и мы свяжемся с вами в течение 30 минут</p>
                
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Имя *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Телефон *</label>
                        <input type="tel" id="phone" name="phone" required placeholder="+7 (___) ___-__-__">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="company">Название компании</label>
                        <input type="text" id="company" name="company">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Сообщение</label>
                        <textarea id="message" name="message" placeholder="Расскажите о вашей продукции и задачах..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <p style="font-size: 14px; color: #666; line-height: 1.5;">
                            Отправляя форму, вы соглашаетесь с 
                            <a href="/privacy" target="_blank" style="color: #667eea;">политикой конфиденциальности</a>
                        </p>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">Отправить заявку</button>
                    
                    <div class="success-message" id="successMessage">
                        ✅ Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.
                    </div>
                    
                    <div class="error-message" id="errorMessage">
                        ❌ Произошла ошибка при отправке. Попробуйте еще раз или свяжитесь с нами по телефону.
                    </div>
                </form>
            </div>
        </div>
    `;
}

function getFooterHTML() {
    return `
        <footer class="footer" id="contacts">
            <div class="container">
                <div class="footer-grid">
                    <!-- Основная информация -->
                    <div class="footer-column">
                        <div class="footer-logo">
                            <div class="logo-icon">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="4" y="3" width="12" height="16" rx="1" fill="white" opacity="0.9"/>
                                    <line x1="6" y1="7" x2="14" y2="7" stroke="#667eea" stroke-width="0.8"/>
                                    <line x1="6" y1="9" x2="14" y2="9" stroke="#667eea" stroke-width="0.8"/>
                                    <line x1="6" y1="11" x2="14" y2="11" stroke="#667eea" stroke-width="0.8"/>
                                    <line x1="6" y1="13" x2="14" y2="13" stroke="#667eea" stroke-width="0.8"/>
                                    <circle cx="18" cy="6" r="3" fill="#27ae60"/>
                                    <path d="M16.5 6l1 1 2-2" stroke="white" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                                    <path d="M2 12c0-2 1-3 2-3s2 1 2 3c0 2-1 4-2 4s-2-2-2-4z" fill="#ffa500" opacity="0.8"/>
                                </svg>
                            </div>
                            <span>Реестр Гарант</span>
                        </div>
                        <p class="footer-description">Профессиональная помощь при внесении в реестры</p>
                    </div>

                    <!-- Услуги -->
                    <div class="footer-column">
                        <h4>Наши услуги</h4>
                        <ul class="footer-links">
                            <li><a href="/industrial">Промышленная продукция</a></li>
                            <li><a href="/radioelectronic">Радиоэлектронная продукция</a></li>
                            <li><a href="/software">Программное обеспечение</a></li>
                            <li><a href="/medical-devices">Медицинские изделия</a></li>
                            <li><a href="/telecom-equipment">Телекоммуникационное оборудование</a></li>
                            <li><a href="/oil-gas-equipment">Нефтегазовое оборудование</a></li>
                            <li><a href="/roskomnadzor-services">Стоимость регитсрации в реестре РКН</a></li>
                            
                        </ul>
                    </div>

                    <!-- Информация -->
                    <div class="footer-column">
                        <h4>Информация</h4>
                        <ul class="footer-links">
                            <li><a href="#services">О компании</a></li>
                            <li><a href="/news/">Новости</a></li>
                            <li><a href="/pricing">Стоимость услуг</a></li>
                            <li><a href="#reviews">Отзывы клиентов</a></li>
                            <li><a href="/news/?category=6">Часто задаваемые вопросы</a></li>
                            <li><a href="#geography">География работы</a></li>
                            <li><a href="/gisp-minpromtorg">Реестр Минпромторга</a></li>
                        </ul>
                        
                         <h4>Направления деятельности</h4>
                        <ul class="footer-links">
                            <li><a href="https://vnesenie-v-reestr.ru/">Включение в реестры</a></li>
                            <li><a href="https://certification.vnesenie-v-reestr.ru/">Сертификация</a></li>
                          
                            
                        </ul>
                        
                    </div>

                    <!-- Контакты -->
                    <div class="footer-column">
                        <h4>Контакты</h4>
                        <div class="footer-contacts">
                            <div class="contact-item">
                                <div class="contact-details">
                                    <a href="tel:+79208981718">+7 920-898-17-18</a>
                                    <span>Звонки принимаем с 9:00 до 18:00</span>
                                </div>
                            </div>
                            <div class="contact-item email">
                                <div class="contact-details">
                                    <a href="mailto:reestrgarant@mail.ru">reestrgarant@mail.ru</a>
                                    <span>Ответим на email в течение часа</span>
                                </div>
                            </div>
                            <div class="contact-item time">
                                <div class="contact-details">
                                    <span>Пн-Пт: 9:00-18:00</span>
                                    <span>Сб-Вс: по предварительной записи</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Селектор городов в футере -->
                <div class="footer-city-section">
                    <h4>Наши услуги по городам</h4>
                    <div class="city-selector">
                        <div class="city-dropdown">
                            <button class="city-btn" id="cityBtn">
                                <span class="city-icon">📍</span>
                                <span class="city-name" id="currentCity">Россия</span>
                                <span class="city-arrow">▼</span>
                            </button>
                            <div class="city-dropdown-menu" id="cityDropdown">
                                <div class="city-dropdown-header">
                                    <h4>Выберите город</h4>
                                </div>
                                <div class="city-dropdown-content">
                                    <a href="/" class="city-dropdown-item" data-city="russia">
                                        <span class="city-icon">🏢</span>
                                        <span>Россия</span>
                                    </a>
                                    <div class="city-dropdown-separator"></div>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/moskva" class="city-dropdown-item" data-city="moskva">
                                        <span class="city-icon">🏢</span>
                                        <span>Москва</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/sankt-peterburg" class="city-dropdown-item" data-city="sankt-peterburg">
                                        <span class="city-icon">🏢</span>
                                        <span>Санкт-Петербург</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/novosibirsk" class="city-dropdown-item" data-city="novosibirsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Новосибирск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/yekaterinburg" class="city-dropdown-item" data-city="yekaterinburg">
                                        <span class="city-icon">🏢</span>
                                        <span>Екатеринбург</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/kazan" class="city-dropdown-item" data-city="kazan">
                                        <span class="city-icon">🏢</span>
                                        <span>Казань</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/nizhniy-novgorod" class="city-dropdown-item" data-city="nizhniy-novgorod">
                                        <span class="city-icon">🏢</span>
                                        <span>Нижний Новгород</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/chelyabinsk" class="city-dropdown-item" data-city="chelyabinsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Челябинск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/samara" class="city-dropdown-item" data-city="samara">
                                        <span class="city-icon">🏢</span>
                                        <span>Самара</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/omsk" class="city-dropdown-item" data-city="omsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Омск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/rostov-na-donu" class="city-dropdown-item" data-city="rostov-na-donu">
                                        <span class="city-icon">🏢</span>
                                        <span>Ростов-на-Дону</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/ufa" class="city-dropdown-item" data-city="ufa">
                                        <span class="city-icon">🏢</span>
                                        <span>Уфа</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/krasnoyarsk" class="city-dropdown-item" data-city="krasnoyarsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Красноярск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/voronezh" class="city-dropdown-item" data-city="voronezh">
                                        <span class="city-icon">🏢</span>
                                        <span>Воронеж</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/perm" class="city-dropdown-item" data-city="perm">
                                        <span class="city-icon">🏢</span>
                                        <span>Пермь</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/volgograd" class="city-dropdown-item" data-city="volgograd">
                                        <span class="city-icon">🏢</span>
                                        <span>Волгоград</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/krasnodar" class="city-dropdown-item" data-city="krasnodar">
                                        <span class="city-icon">🏢</span>
                                        <span>Краснодар</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/saratov" class="city-dropdown-item" data-city="saratov">
                                        <span class="city-icon">🏢</span>
                                        <span>Саратов</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/tyumen" class="city-dropdown-item" data-city="tyumen">
                                        <span class="city-icon">🏢</span>
                                        <span>Тюмень</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/tolyatti" class="city-dropdown-item" data-city="tolyatti">
                                        <span class="city-icon">🏢</span>
                                        <span>Тольятти</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/izhevsk" class="city-dropdown-item" data-city="izhevsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Ижевск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/barnaul" class="city-dropdown-item" data-city="barnaul">
                                        <span class="city-icon">🏢</span>
                                        <span>Барнаул</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/ulyanovsk" class="city-dropdown-item" data-city="ulyanovsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Ульяновск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/irkutsk" class="city-dropdown-item" data-city="irkutsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Иркутск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/habarovsk" class="city-dropdown-item" data-city="habarovsk">
                                        <span class="city-icon">🏢</span>
                                        <span>Хабаровск</span>
                                    </a>
                                    <a href="/vkljuchenie-v-reestr-minpromtorga/vladivostok" class="city-dropdown-item" data-city="vladivostok">
                                        <span class="city-icon">🏢</span>
                                        <span>Владивосток</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Быстрая заявка -->
                <div class="footer-cta">
                    <div class="footer-cta-content">
                        <h3>Получите бесплатную консультацию прямо сейчас</h3>
                        <p>Узнайте возможности включения вашей продукции в реестры</p>
                        <button class="btn btn-primary" onclick="openModal('consultation')">
                            Получить консультацию
                        </button>
                    </div>
                </div>

                <!-- Копирайт -->
                <div class="footer-bottom">
                    <div class="footer-bottom-content">
                        <p>&copy; 2025 Реестр Гарант. Все права защищены.</p>
                        <div class="footer-legal">
                            <a href="/privacy">Политика конфиденциальности</a>
                            <a href="/terms">Пользовательское соглашение</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <style>
        /* ИСПРАВЛЕННЫЕ CSS СТИЛИ - ПОЛНАЯ КРОССПЛАТФОРМЕННОСТЬ */
        
        /* Основные стили футера */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 0 0;
            margin-top: 80px;
        }

        .footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ДОБАВЛЕНЫ ОТСУТСТВУЮЩИЕ СТИЛИ ДЛЯ FOOTER-GRID */
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 50px;
        }

        .footer-column h4 {
            color: white;
            margin: 0 0 20px 0;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 2px solid #16a085;
            padding-bottom: 10px;
            display: inline-block;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .footer-logo .logo-icon {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
        }

        .footer-logo .logo-icon svg {
            width: 100%;
            height: 100%;
        }

        .footer-logo span {
            font-size: 20px;
            font-weight: 700;
            color: white;
        }

        .footer-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #16a085;
        }

        .footer-contacts {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .contact-item {
            display: flex;
            flex-direction: column;
        }

        .contact-details a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .contact-details span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
        }

        .contact-details a:hover {
            color: #16a085;
        }

        /* Селектор городов в футере - ИСПРАВЛЕННОЕ ПОЗИЦИОНИРОВАНИЕ */
        .footer-city-section {
            padding: 40px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin: 40px 0;
            text-align: center;
        }

        .footer-city-section h4 {
            color: white;
            margin: 0 0 25px 0;
            font-size: 20px;
            font-weight: 600;
            border: none;
            padding: 0;
            display: block;
        }

        .footer .city-selector {
            position: relative;
            margin: 0 auto;
            display: inline-block;
        }

        .footer .city-dropdown {
            position: relative;
            display: inline-block;
        }

        .footer .city-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 25px;
            background: #16a085;
            border: 2px solid #16a085;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            font-weight: 600;
            color: white;
            box-shadow: 0 4px 15px rgba(22, 160, 133, 0.3);
            min-width: 200px;
            /* Улучшенная кнопка для touch */
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .footer .city-btn:hover,
        .footer .city-btn:focus {
            background: #138d75;
            border-color: #138d75;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 160, 133, 0.4);
        }

        .footer .city-btn:active {
            transform: translateY(0);
        }

        .footer .city-btn.active {
            background: #fff;
            color: #16a085;
            border-color: #fff;
        }

        .footer .city-btn.active .city-arrow {
            transform: rotate(180deg);
        }

        .footer .city-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .footer .city-name {
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
        }

        .footer .city-arrow {
            font-size: 12px;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        /* ИСПРАВЛЕННОЕ ПОЗИЦИОНИРОВАНИЕ DROPDOWN С АВТООПРЕДЕЛЕНИЕМ */
        .footer .city-dropdown-menu {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            max-height: 350px;
            overflow-y: auto;
            min-width: 280px;
            
            /* Улучшенная прокрутка */
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #16a085 #f1f1f1;
        }

        /* Динамическое позиционирование */
        .footer .city-dropdown-menu.dropdown-up {
            bottom: 100%;
            margin-bottom: 15px;
        }

        .footer .city-dropdown-menu.dropdown-down {
            top: 100%;
            margin-top: 15px;
        }

        .footer .city-dropdown-menu.show {
            display: block;
            animation: dropdownFadeIn 0.3s ease;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        /* Стили для WebKit scrollbar */
        .footer .city-dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }

        .footer .city-dropdown-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .footer .city-dropdown-menu::-webkit-scrollbar-thumb {
            background: #16a085;
            border-radius: 3px;
        }

        .footer .city-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #138d75;
        }

        .footer .city-dropdown-header {
            padding: 20px 25px 15px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .footer .city-dropdown-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            border: none;
            padding: 0;
        }

        .footer .city-dropdown-content {
            padding: 10px 0;
        }

        .footer .city-dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 25px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 15px;
            
            /* Улучшено для touch */
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .footer .city-dropdown-item:hover,
        .footer .city-dropdown-item:focus {
            background: #f8f9fa;
            color: #16a085;
            transform: translateX(5px);
        }

        .footer .city-dropdown-item:active {
            background: #e8f8f5;
        }

        .footer .city-dropdown-item.active {
            background: #e8f8f5;
            color: #16a085;
            font-weight: 600;
            border-left: 3px solid #16a085;
        }

        .footer .city-dropdown-separator {
            height: 1px;
            background: #eee;
            margin: 8px 0;
        }

        /* CTA секция */
        .footer-cta {
            background: linear-gradient(135deg, #16a085 0%, #138d75 100%);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin: 40px 0;
            box-shadow: 0 10px 30px rgba(22, 160, 133, 0.2);
        }

        .footer-cta h3 {
            color: white;
            margin: 0 0 15px 0;
            font-size: 24px;
            font-weight: 700;
        }

        .footer-cta p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0 0 25px 0;
            font-size: 16px;
        }

        .footer-cta .btn {
            background: white;
            color: #16a085;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .footer-cta .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
        }

        /* Копирайт */
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 0;
        }

        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-bottom p {
            margin: 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .footer-legal {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .footer-legal a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-legal a:hover {
            color: #16a085;
        }

        /* АДАПТИВНОСТЬ - ИСПРАВЛЕНА ДЛЯ ВСЕХ УСТРОЙСТВ */
        
        /* Планшеты (768px - 1024px) */
        @media (max-width: 1024px) and (min-width: 769px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
            
            .footer .city-dropdown-menu {
                min-width: 250px;
            }
        }

        /* Мобильные устройства (481px - 768px) */
        @media (max-width: 768px) {
            .footer {
                padding: 40px 0 0;
            }

            .footer .container {
                padding: 0 15px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 25px;
                margin-bottom: 30px;
            }

            .footer-city-section {
                padding: 30px 0;
                margin: 30px 0;
            }

            .footer-city-section h4 {
                font-size: 18px;
                margin-bottom: 20px;
            }

            .footer .city-btn {
                padding: 12px 20px;
                font-size: 14px;
                min-width: 180px;
            }
            
            .footer .city-dropdown-menu {
                min-width: 250px;
                max-height: 300px;
                left: 50%;
                right: auto;
                transform: translateX(-50%);
            }

            .footer-cta {
                padding: 30px 20px;
                margin: 30px 0;
            }

            .footer-cta h3 {
                font-size: 20px;
            }

            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .footer-legal {
                justify-content: center;
            }
        }

        /* Маленькие мобильные устройства (320px - 480px) */
        @media (max-width: 480px) {
            .footer {
                padding: 30px 0 0;
            }

            .footer .container {
                padding: 0 10px;
            }

            .footer-grid {
                gap: 20px;
            }

            .footer-column h4 {
                font-size: 16px;
            }

            .footer .city-btn {
                padding: 10px 16px;
                font-size: 13px;
                min-width: 160px;
                flex-direction: column;
                gap: 5px;
            }

            .footer .city-name {
                font-size: 14px;
            }
            
            .footer .city-dropdown-menu {
                left: 50%;
                right: auto;
                transform: translateX(-50%);
                min-width: calc(100vw - 40px);
                max-width: 280px;
                max-height: 250px;
            }

            .footer .city-dropdown-header {
                padding: 15px 20px 10px;
            }

            .footer .city-dropdown-item {
                padding: 12px 20px;
                font-size: 14px;
            }

            .footer-cta {
                padding: 25px 15px;
                border-radius: 10px;
            }

            .footer-cta h3 {
                font-size: 18px;
                line-height: 1.3;
            }

            .footer-cta p {
                font-size: 14px;
            }

            .footer-cta .btn {
                padding: 12px 25px;
                font-size: 14px;
            }

            .footer-legal {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Очень маленькие экраны (менее 320px) */
        @media (max-width: 320px) {
            .footer .container {
                padding: 0 5px;
            }

            .footer .city-dropdown-menu {
                min-width: calc(100vw - 20px);
                left: 50%;
                transform: translateX(-50%);
            }

            .footer-cta h3 {
                font-size: 16px;
            }

            .footer-cta .btn {
                padding: 10px 20px;
                font-size: 13px;
            }
        }

        /* Landscape ориентация для мобильных */
        @media (max-height: 500px) and (orientation: landscape) {
            .footer .city-dropdown-menu {
                max-height: 200px;
            }

            .footer .city-dropdown-menu.dropdown-up {
                margin-bottom: 10px;
            }

            .footer .city-dropdown-menu.dropdown-down {
                margin-top: 10px;
            }
        }

        /* Поддержка старых браузеров */
        @supports not (display: grid) {
            .footer-grid {
                display: flex;
                flex-wrap: wrap;
            }

            .footer-column {
                flex: 1 1 250px;
                margin-bottom: 30px;
            }
        }

        /* Fallback для браузеров без CSS Grid */
        .no-grid .footer-grid {
            display: block;
        }

        .no-grid .footer-column {
            margin-bottom: 30px;
        }

        @media (min-width: 768px) {
            .no-grid .footer-column {
                float: left;
                width: 25%;
                box-sizing: border-box;
                padding-right: 20px;
            }
        }

        /* Принудительный clearfix */
        .footer-grid::after {
            content: "";
            display: table;
            clear: both;
        }

        /* iOS специфичные исправления */
        @supports (-webkit-touch-callout: none) {
            .footer .city-dropdown-menu {
                -webkit-overflow-scrolling: touch;
            }
            
            .footer .city-btn {
                -webkit-appearance: none;
            }
        }

        /* Высокий DPI */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .footer .city-btn {
                border-width: 1px;
            }
        }
        </style>

        <!-- Подключение чата и счетчиков -->
        <script src="//code.jivo.ru/widget/sheSSFdMoT" async></script>

        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
           (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
           m[i].l=1*new Date();
           for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
           k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
           (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

           ym(102244644, "init", {
                clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true
           });
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/102244644" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
    `;
}

// Функция для принудительной инициализации счетчиков
function initializeCounters() {
    console.log('📊 Инициализируем счетчики...');
    
    // Инициализация Yandex Metrika
    if (!window.ym) {
        console.log('📊 Загружаем Yandex Metrika...');
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(102244644, "init", {
             clickmap:true,
             trackLinks:true,
             accurateTrackBounce:true
        });
        console.log('✅ Yandex Metrika инициализирована');
    } else {
        console.log('⚠️ Yandex Metrika уже загружена');
    }
    
    // Инициализация Jivo Chat
    if (!window.jivo_api) {
        console.log('💬 Загружаем Jivo Chat...');
        const jivoScript = document.createElement('script');
        jivoScript.src = '//code.jivo.ru/widget/sheSSFdMoT';
        jivoScript.async = true;
        document.head.appendChild(jivoScript);
        console.log('✅ Jivo Chat инициализирован');
    } else {
        console.log('⚠️ Jivo Chat уже загружен');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    console.log('🚀 DOM загружен, начинаем инициализацию...');
    
    // Даем время для полной загрузки DOM
    setTimeout(() => {
        // Ищем элементы с любыми путями к header, modal и footer
        const headerContainer = document.querySelector('[data-include*="header.html"]');
        const modalContainer = document.querySelector('[data-include*="modal.html"]');
        const footerContainer = document.querySelector('[data-include*="footer.html"]') || 
                              document.querySelector('[data-include*="footer.php"]');
        
        console.log('🔍 Поиск контейнеров:');
        console.log('Header:', !!headerContainer, headerContainer);
        console.log('Modal:', !!modalContainer, modalContainer);
        console.log('Footer:', !!footerContainer, footerContainer);
        
        // Проверяем, что хотя бы что-то найдено
        if (!headerContainer && !modalContainer && !footerContainer) {
            console.log('❌ Ни один контейнер не найден! Проверьте HTML разметку.');
            console.log('🔍 Все элементы с data-include:', document.querySelectorAll('[data-include]'));
            return;
        }
        
        if (headerContainer) {
            console.log('📝 Загружаем header...');
            headerContainer.innerHTML = getHeaderHTML();
            headerContainer.removeAttribute('data-include');
            console.log('✅ Header загружен');
        } else {
            console.log('❌ Header контейнер не найден');
        }
        
        if (modalContainer) {
            console.log('📝 Загружаем modal...');
            modalContainer.innerHTML = getModalHTML();
            modalContainer.removeAttribute('data-include');
            console.log('✅ Modal загружен');
        } else {
            console.log('❌ Modal контейнер не найден');
        }
        
        if (footerContainer) {
            console.log('📝 Загружаем footer...');
            footerContainer.innerHTML = getFooterHTML();
            footerContainer.removeAttribute('data-include');
            console.log('✅ Footer загружен');
            
            // Принудительно инициализируем счетчики после загрузки футера
            setTimeout(() => {
                initializeCounters();
            }, 1000);
        } else {
            console.log('❌ Footer контейнер не найден');
        }
        
        // Инициализируем компоненты
        console.log('🔧 Инициализируем компоненты...');
        setTimeout(initializeComponents, 200);
        
        console.log('🎉 Загрузка завершена!');
    }, 100); // Даем время DOM полностью загрузиться
});