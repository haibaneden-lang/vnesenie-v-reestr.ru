<?php
/**
 * Шаблон для отображения отдельной страницы документа сертификации
 * Файл: /certification/document-page.php
 * Подключается из index.php когда есть slug
 */

// Этот файл подключается из index.php, переменные уже определены:
// $page, $order_sent, $order_error

// Обработка формы заказа
$order_sent = false;
$order_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Простая валидация
    if (empty($name) || empty($email) || empty($phone)) {
        $order_error = 'Заполните обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $order_error = 'Неверный формат email';
    } else {
        // Формируем письмо
        $to = "reestrgarant@mail.ru";
        $subject = "=?UTF-8?B?" . base64_encode("Заявка с сертификации: " . $page['title']) . "?=";
        
        $email_body = "Новая заявка с сайта vnesenie-v-reestr.ru\n\n";
        $email_body .= "=== ИСТОЧНИК ===\n";
        $email_body .= "Страница: " . $_SERVER['REQUEST_URI'] . "\n";
        $email_body .= "Услуга: " . $page['title'] . "\n\n";
        $email_body .= "=== ДАННЫЕ КЛИЕНТА ===\n";
        $email_body .= "Имя: " . $name . "\n";
        $email_body .= "Телефон: " . $phone . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Компания: " . ($company ?: 'Не указана') . "\n";
        $email_body .= "Сообщение: " . ($message ?: 'Не указано') . "\n\n";
        $email_body .= "Время: " . date('d.m.Y H:i:s') . "\n";
        $email_body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'неизвестен') . "\n";
        
        $headers = "From: noreply@vnesenie-v-reestr.ru\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        if (mail($to, $subject, $email_body, $headers)) {
            $order_sent = true;
            // Увеличиваем счетчик заказов
            if (isset($certModel) && isset($page['id'])) {
                $certModel->incrementOrders($page['id']);
            }
        } else {
            $order_error = 'Ошибка отправки. Попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?> - Сертификация | Реестр Гарант</title>
    <meta name="description" content="<?php echo htmlspecialchars($page['meta_description'] ?: substr(strip_tags($page['content']), 0, 160)); ?>">
    <?php if (!empty($page['meta_keywords'])): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($page['meta_keywords']); ?>">
    <?php endif; ?>
    
    <!-- Фавиконы -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <!-- Подключаем основные стили сайта -->
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "<?php echo addslashes($page['title']); ?>",
        "description": "<?php echo addslashes($page['meta_description'] ?: substr(strip_tags($page['content']), 0, 160)); ?>",
        "provider": {
            "@type": "Organization",
            "name": "Реестр Гарант",
            "url": "https://vnesenie-v-reestr.ru",
            "telephone": "+7-920-898-17-18",
            "email": "reestrgarant@mail.ru"
        },
        "offers": {
            "@type": "Offer",
            "price": "<?php echo $page['price']; ?>",
            "priceCurrency": "RUB"
        }
    }
    </script>
    
    <style>
        /* Основной контейнер страницы */
        .cert-page-wrapper {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 0;
            padding-top: 80px;
        }
        
        .cert-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Breadcrumbs стили */
        .cert-breadcrumbs {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .cert-breadcrumbs-inner {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .cert-breadcrumbs a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
            font-weight: 500;
        }
        
        .cert-breadcrumbs a:hover {
            color: #4c63d2;
            text-decoration: underline;
        }
        
        .cert-breadcrumbs-separator {
            color: #999;
            font-weight: bold;
        }
        
        .cert-breadcrumbs-current {
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* Основная структура */
        .cert-main-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        /* Левая колонка с контентом */
        .cert-content-column {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        /* Заголовок страницы */
        .cert-page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .cert-page-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .cert-page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }
        
        /* Топ секция: фото + цена + параметры */
        .cert-top-section {
            padding: 30px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        /* Блок с фото документа */
        .cert-document-photo {
            text-align: center;
        }
        
        .cert-document-image {
            width: 100%;
            max-width: 280px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
            cursor: pointer;
            border: 3px solid #e9ecef;
        }
        
        .cert-document-image:hover {
            transform: scale(1.05);
        }
        
        .cert-document-caption {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        
        /* Блок с ценой и параметрами */
        .cert-info-block {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        /* Цена */
        .cert-price-section {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cert-price-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }
        
        .cert-price-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .cert-price-old {
            text-decoration: line-through;
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .cert-price-current {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 10px 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .cert-price-note {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        /* Параметры документа */
        .cert-params-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .cert-param-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .cert-param-title {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .cert-param-value {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        /* Контент статьи */
        .cert-article-content {
            padding: 30px;
        }
        
        .cert-article-content h2,
        .cert-article-content h3,
        .cert-article-content h4 {
            color: #2c3e50;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .cert-article-content h2 {
            font-size: 1.6rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .cert-article-content h3 {
            font-size: 1.4rem;
        }
        
        .cert-article-content p {
            line-height: 1.7;
            margin-bottom: 16px;
            color: #555;
        }
        
        .cert-article-content ul,
        .cert-article-content ol {
            margin: 20px 0;
            padding-left: 0;
        }
        
        .cert-article-content li {
            margin-bottom: 10px;
            line-height: 1.6;
            position: relative;
            padding-left: 25px;
            color: #555;
        }
        
        .cert-article-content ul li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
        }
        
        /* Правая колонка - форма заказа */
        .cert-sidebar-column {
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        /* Форма заказа */
        .cert-order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border: 2px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .cert-order-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .cert-order-header h3 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 1.4rem;
        }
        
        .cert-order-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        
        .cert-form-group {
            margin-bottom: 20px;
        }
        
        .cert-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .cert-form-group input,
        .cert-form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-family: inherit;
        }
        
        .cert-form-group input:focus,
        .cert-form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .cert-btn-order {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .cert-btn-order::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .cert-btn-order:hover::before {
            left: 100%;
        }
        
        .cert-btn-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        /* Контактная информация */
        .cert-contact-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #667eea;
        }
        
        .cert-contact-item {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cert-contact-icon {
            width: 20px;
            text-align: center;
        }
        
        .cert-contact-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .cert-contact-link:hover {
            text-decoration: underline;
        }
        
        /* Messages */
        .cert-success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            font-weight: 500;
        }
        
        .cert-error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            font-weight: 500;
        }
        
        /* Features Section */
        .cert-features-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin: 40px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .cert-features-title {
            text-align: center;
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .cert-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .cert-feature-item {
            text-align: center;
            padding: 25px;
            border: 2px solid #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .cert-feature-item:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }
        
        .cert-feature-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .cert-feature-item h4 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 1.1rem;
        }
        
        .cert-feature-item p {
            color: #666;
            margin: 0;
            line-height: 1.5;
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .cert-main-layout {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .cert-sidebar-column {
                position: static;
                order: -1;
            }
            
            .cert-top-section {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .cert-params-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .cert-container {
                padding: 0 15px;
            }
            
            .cert-page-title {
                font-size: 1.6rem;
            }
            
            .cert-top-section {
                padding: 20px;
            }
            
            .cert-article-content {
                padding: 20px;
            }
            
            .cert-order-card {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .cert-page-title {
                font-size: 1.4rem;
            }
            
            .cert-price-current {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body class="cert-page-wrapper">
    <!-- Подключение шапки -->
    <div data-include="../header.html"></div>

    <!-- Breadcrumbs -->
    <div class="cert-breadcrumbs">
        <div class="cert-breadcrumbs-inner">
            <a href="/">🏠 Главная</a>
            <span class="cert-breadcrumbs-separator">→</span>
            <a href="/certification/">Сертификация</a>
            <span class="cert-breadcrumbs-separator">→</span>
            <span class="cert-breadcrumbs-current"><?php echo htmlspecialchars($page['certificate_name'] ?: $page['title']); ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="cert-container">
        <!-- Основная структура -->
        <div class="cert-main-layout">
            <!-- Левая колонка с контентом -->
            <div class="cert-content-column">
                <!-- Заголовок страницы -->
                <div class="cert-page-header">
                    <h1 class="cert-page-title"><?php echo htmlspecialchars($page['h1'] ?: $page['title']); ?></h1>
                    <?php if (!empty($page['certificate_name'])): ?>
                        <p class="cert-page-subtitle"><?php echo htmlspecialchars($page['certificate_name']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Топ секция: фото + цена + параметры -->
                <div class="cert-top-section">
                    <!-- Блок с фото документа -->
                    <div class="cert-document-photo">
                        <?php if (!empty($page['certificate_image'])): ?>
                            <img src="<?php echo htmlspecialchars($page['certificate_image']); ?>" 
                                 alt="Образец документа - <?php echo htmlspecialchars($page['certificate_name'] ?: $page['title']); ?>" 
                                 class="cert-document-image"
                                 onclick="openImageModal(this.src)">
                            <div class="cert-document-caption">
                                📜 Образец документа<br>
                                <small>Нажмите для увеличения</small>
                            </div>
                        <?php else: ?>
                            <div style="width: 280px; height: 200px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 2px dashed #dee2e6;">
                                <div style="text-align: center; color: #666;">
                                    📄<br>
                                    <small>Образец документа<br>будет добавлен</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Блок с ценой и параметрами -->
                    <div class="cert-info-block">
                        <!-- Цена -->
                        <?php if ($page['show_price'] && $page['price'] > 0): ?>
                            <div class="cert-price-section">
                                <div class="cert-price-badge">💰 Стоимость услуги от</div>
                                
                                <?php if (!empty($page['price_old']) && $page['price_old'] > $page['price']): ?>
                                    <div class="cert-price-old"><?php echo number_format($page['price_old'], 0, ',', ' '); ?> ₽</div>
                                <?php endif; ?>
                                
                                <div class="cert-price-current"><?php echo number_format($page['price'], 0, ',', ' '); ?> ₽</div>
                                <div class="cert-price-note">Полная стоимость с документами</div>
                            </div>
                        <?php endif; ?>

                        <!-- Параметры документа -->
                        <div class="cert-params-grid">
                            <?php if (!empty($page['duration'])): ?>
                                <div class="cert-param-item">
                                    <div class="cert-param-title">⏱️ Срок оформления</div>
                                    <div class="cert-param-value"><?php echo htmlspecialchars($page['duration']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($page['validity_period'])): ?>
                                <div class="cert-param-item">
                                    <div class="cert-param-title">📅 Срок действия</div>
                                    <div class="cert-param-value"><?php echo htmlspecialchars($page['validity_period']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($page['guarantee'])): ?>
                                <div class="cert-param-item">
                                    <div class="cert-param-title">✅ Гарантии</div>
                                    <div class="cert-param-value"><?php echo htmlspecialchars($page['guarantee']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($page['document_type'])): ?>
                                <div class="cert-param-item">
                                    <div class="cert-param-title">📋 Тип документа</div>
                                    <div class="cert-param-value"><?php echo htmlspecialchars($page['document_type']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Контент статьи -->
                <div class="cert-article-content">
                    <!-- Краткое описание -->
                    <?php if (!empty($page['short_description'])): ?>
                        <div style="background: #f8f9ff; padding: 20px; border-radius: 10px; border-left: 4px solid #667eea; margin-bottom: 30px;">
                            <strong>📋 Краткое описание:</strong><br>
                            <?php echo htmlspecialchars($page['short_description']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Основное содержимое -->
                    <?php echo $page['content']; ?>
                    
                    <!-- Требования -->
                    <?php if (!empty($page['requirements'])): ?>
                        <h3>📋 Требования и условия</h3>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #667eea;">
                            <?php echo nl2br(htmlspecialchars($page['requirements'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Необходимые документы -->
                    <?php if (!empty($page['documents_needed'])): ?>
                        <h3>📄 Необходимые документы</h3>
                        <div style="background: #f0f8f0; padding: 20px; border-radius: 10px; border-left: 4px solid #27ae60;">
                            <?php echo nl2br(htmlspecialchars($page['documents_needed'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Правая колонка - форма заказа -->
            <div class="cert-sidebar-column">
                <!-- Форма заказа -->
                <?php if ($page['show_order_button']): ?>
                    <div class="cert-order-card">
                        <div class="cert-order-header">
                            <h3>🎯 Заказать документ</h3>
                            <p>Получите консультацию и расчет стоимости</p>
                        </div>
                        
                        <?php if ($order_sent): ?>
                            <div class="cert-success-message">
                                ✅ <strong>Заявка успешно отправлена!</strong><br>
                                Мы свяжемся с вами в ближайшее время для уточнения деталей.
                            </div>
                        <?php else: ?>
                            <?php if ($order_error): ?>
                                <div class="cert-error-message">
                                    ❌ <strong>Ошибка:</strong> <?php echo htmlspecialchars($order_error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="certOrderForm">
                                <input type="hidden" name="submit_order" value="1">
                                
                                <div class="cert-form-group">
                                    <label for="name">👤 Ваше имя *</label>
                                    <input type="text" id="name" name="name" required 
                                           placeholder="Введите ваше имя">
                                </div>
                                
                                <div class="cert-form-group">
                                    <label for="email">📧 Email адрес *</label>
                                    <input type="email" id="email" name="email" required 
                                           placeholder="your@email.com">
                                </div>
                                
                                <div class="cert-form-group">
                                    <label for="phone">📞 Телефон *</label>
                                    <input type="tel" id="phone" name="phone" required 
                                           placeholder="+7 (999) 123-45-67">
                                </div>
                                
                                <div class="cert-form-group">
                                    <label for="company">🏢 Компания</label>
                                    <input type="text" id="company" name="company" 
                                           placeholder="Название организации">
                                </div>
                                
                                <div class="cert-form-group">
                                    <label for="message">💬 Дополнительная информация</label>
                                    <textarea id="message" name="message" rows="3" 
                                              placeholder="Опишите специфику вашей продукции"></textarea>
                                </div>
                                
                                <button type="submit" class="cert-btn-order">
                                    📞 <?php echo htmlspecialchars($page['order_button_text'] ?: 'Заказать консультацию'); ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Контактная информация -->
                <div class="cert-contact-card">
                    <h4 style="margin: 0 0 15px 0; color: #2c3e50;">📞 Контакты</h4>
                    
                    <div class="cert-contact-item">
                        <span class="cert-contact-icon">📞</span>
                        <a href="tel:+79208981718" class="cert-contact-link">+7 920-898-17-18</a>
                    </div>
                    
                    <div class="cert-contact-item">
                        <span class="cert-contact-icon">📧</span>
                        <a href="mailto:reestrgarant@mail.ru" class="cert-contact-link">reestrgarant@mail.ru</a>
                    </div>
                    
                    <div class="cert-contact-item">
                        <span class="cert-contact-icon">⏰</span>
                        <span>Пн-Пт: 9:00-18:00</span>
                    </div>
                    
                    <div class="cert-contact-item">
                        <span class="cert-contact-icon">🌍</span>
                        <span>Работаем по всей России</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="cert-container">
        <div class="cert-features-section">
            <h2 class="cert-features-title">🏆 Преимущества работы с нами</h2>
            
            <div class="cert-features-grid">
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">⚡</span>
                    <h4>Быстрое оформление</h4>
                    <p>Минимальные сроки получения документов благодаря отлаженным процессам</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">🎯</span>
                    <h4>Гарантия результата</h4>
                    <p>100% гарантия получения сертификата или возврат денежных средств</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">👨‍💼</span>
                    <h4>Опытные специалисты</h4>
                    <p>Команда экспертов с многолетним опытом в сфере сертификации</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">📋</span>
                    <h4>Полное сопровождение</h4>
                    <p>Берем на себя всю работу от подачи документов до получения сертификата</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">💰</span>
                    <h4>Прозрачные цены</h4>
                    <p>Фиксированная стоимость без скрытых платежей и доплат</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">🛡️</span>
                    <h4>Конфиденциальность</h4>
                    <p>Полная защита ваших данных и коммерческой информации</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Services Section -->
    <div class="cert-container">
        <div class="cert-features-section">
            <h2 class="cert-features-title">🔗 Связанные услуги</h2>
            
            <div class="cert-features-grid">
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">🏭</span>
                    <h4><a href="/industrial" style="color: #2c3e50; text-decoration: none;">Промышленная продукция</a></h4>
                    <p>Включение в реестр промышленной продукции Минпромторга</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">📡</span>
                    <h4><a href="/radioelectronic" style="color: #2c3e50; text-decoration: none;">Радиоэлектронная продукция</a></h4>
                    <p>Реестр радиоэлектронной продукции (РЭП) Минпромторга</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">💻</span>
                    <h4><a href="/software" style="color: #2c3e50; text-decoration: none;">Программное обеспечение</a></h4>
                    <p>Включение ПО в реестр российского программного обеспечения</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">🏥</span>
                    <h4><a href="/medical-devices" style="color: #2c3e50; text-decoration: none;">Медицинские изделия</a></h4>
                    <p>Регистрация и сертификация медицинских изделий</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">📞</span>
                    <h4><a href="/telecom-equipment" style="color: #2c3e50; text-decoration: none;">Телеком оборудование</a></h4>
                    <p>Сертификация телекоммуникационного оборудования</p>
                </div>
                
                <div class="cert-feature-item">
                    <span class="cert-feature-icon">⛽</span>
                    <h4><a href="/oil-gas-equipment" style="color: #2c3e50; text-decoration: none;">Нефтегазовое оборудование</a></h4>
                    <p>Сертификация оборудования для нефтегазовой отрасли</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Подключение модального окна -->
    <div data-include="../modal.html"></div>

    <!-- Подключение футера -->
    <div data-include="../footer.html"></div>

    <!-- JavaScript -->
    <script src="/include.js"></script>
    <script src="/script.js"></script>
    
    <script>
        // Модальное окно для просмотра изображения документа
        function openImageModal(imageSrc) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            const img = document.createElement('img');
            img.src = imageSrc;
            img.style.cssText = `
                max-width: 90%;
                max-height: 90%;
                border-radius: 10px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.5);
                transform: scale(0.8);
                transition: transform 0.3s ease;
            `;
            
            const closeBtn = document.createElement('div');
            closeBtn.innerHTML = '✕';
            closeBtn.style.cssText = `
                position: absolute;
                top: 20px;
                right: 30px;
                color: white;
                font-size: 30px;
                font-weight: bold;
                cursor: pointer;
                z-index: 10001;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(0,0,0,0.5);
                border-radius: 50%;
                transition: background 0.3s ease;
            `;
            
            closeBtn.addEventListener('mouseenter', () => {
                closeBtn.style.background = 'rgba(255,255,255,0.2)';
            });
            
            closeBtn.addEventListener('mouseleave', () => {
                closeBtn.style.background = 'rgba(0,0,0,0.5)';
            });
            
            modal.appendChild(img);
            modal.appendChild(closeBtn);
            document.body.appendChild(modal);
            
            // Анимация появления
            setTimeout(() => {
                modal.style.opacity = '1';
                img.style.transform = 'scale(1)';
            }, 10);
            
            // Закрытие по клику
            modal.addEventListener('click', function(e) {
                if (e.target === modal || e.target === closeBtn) {
                    modal.style.opacity = '0';
                    img.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        document.body.removeChild(modal);
                    }, 300);
                }
            });
            
            // Закрытие по ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modal.style.opacity = '0';
                    img.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        if (document.body.contains(modal)) {
                            document.body.removeChild(modal);
                        }
                    }, 300);
                }
            });
        }

        // Маска для телефона
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (value[0] === '8') value = '7' + value.slice(1);
                    if (!value.startsWith('7')) value = '7' + value;
                }
                if (value.length > 11) value = value.slice(0, 11);
                
                let formattedValue = '';
                if (value.length > 0) {
                    formattedValue = '+7';
                    if (value.length > 1) formattedValue += ' (' + value.slice(1, 4);
                    if (value.length > 4) formattedValue += ') ' + value.slice(4, 7);
                    if (value.length > 7) formattedValue += '-' + value.slice(7, 9);
                    if (value.length > 9) formattedValue += '-' + value.slice(9, 11);
                }
                
                e.target.value = formattedValue;
            });
        }

        // Плавная прокрутка для внутренних ссылок
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Анимация появления элементов при скролле
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

        // Применяем анимацию к карточкам
        document.querySelectorAll('.cert-feature-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        console.log('Страница документа сертификации загружена');
    </script>
</body>
</html>