<?php
/**
 * /vkljuchenie-v-reestr-minpromtorga/index.php
 * Главный файл для обработки городских страниц
 */

// Подключаем данные о городах
require_once __DIR__ . '/cities.php';

// Получаем slug города из URL
$citySlug = $_GET['city'] ?? '';

// Если нет параметра city, показываем простую 404 страницу
if (empty($citySlug)) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Страница не найдена</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { color: #e74c3c; }
            a { color: #16a085; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>404 - Страница не найдена</h1>
        <p>Пожалуйста, выберите город из списка.</p>
        <a href="/">← Перейти на главную</a>
    </body>
    </html>
    <?php
    exit;
}

// Получаем данные о городе
$cityData = getCityBySlug($citySlug);

// Если город не найден, показываем 404
if (!$cityData) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Город не найден</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { color: #e74c3c; }
            a { color: #16a085; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>404 - Город не найден</h1>
        <p>Город "<?= htmlspecialchars($citySlug) ?>" не найден в нашем списке.</p>
        <a href="/">← Перейти на главную</a>
    </body>
    </html>
    <?php
    exit;
}

// Генерируем мета-данные для SEO
$pageTitle = getCityTitle($cityData);
$metaDescription = getCityMetaDescription($cityData);
$keywords = getCityKeywords($cityData);
$canonicalUrl = getCityCanonicalUrl($cityData['slug']);
$h1 = getCityH1($cityData);
$localText = getCityLocalText($cityData);
$jsonLd = getCityJsonLd($cityData);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Фавиконы -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
    
    <!-- Open Graph для соцсетей -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:image" content="https://vnesenie-v-reestr.ru/og-image.jpg">
    
    <!-- SEO -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="Реестр Гарант">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    
    <!-- Подключение CSS файлов -->
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    
    <!-- Структурированные данные для Google -->
    <script type="application/ld+json">
    <?= json_encode($jsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
    </script>

    <style>
        .custom-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .custom-main-content {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .custom-header {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 30px;
            line-height: 1.2;
            text-align: center;
        }
        .custom-section-title {
            color: #2c3e50;
            font-size: 1.8rem;
            margin: 40px 0 20px 0;
            border-bottom: 3px solid #16a085;
            padding-bottom: 10px;
        }
        .custom-paragraph {
            line-height: 1.7;
            margin-bottom: 20px;
            color: #555;
            text-align: justify;
        }
        .custom-list {
            margin: 20px 0;
            padding-left: 30px;
        }
        .custom-list-item {
            margin-bottom: 10px;
            line-height: 1.6;
            color: #555;
        }
        .custom-highlight {
            color: #2c3e50;
            font-weight: 600;
        }
        .custom-city-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
        }
        .custom-city-info h3 {
            color: white;
            margin: 0 0 15px 0;
        }
        .custom-city-info p {
            color: rgba(255,255,255,0.9);
            margin: 0;
        }
        .custom-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .custom-stat-item {
            text-align: center;
            padding: 25px;
            background: linear-gradient(135deg, #16a085 0%, #138d75 100%);
            color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .custom-stat-number {
            display: block;
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
        }
        .custom-stat-label {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
        }
        .custom-contacts-block {
            background: #34495e;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            color: white;
        }
        .custom-contacts-block h3 {
            color: white;
            margin: 0 0 20px 0;
        }
        .custom-contacts-block p {
            margin: 10px 0;
            color: rgba(255,255,255,0.9);
        }
        .custom-contacts-block strong {
            color: white;
        }
        .custom-contacts-block a {
            color: #3498db;
            text-decoration: none;
        }
        .custom-contacts-block a:hover {
            text-decoration: underline;
        }
        .custom-services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .custom-service-card {
            background: #fff;
            border: 2px solid #16a085;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .custom-service-card:hover {
            border-color: #138d75;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .custom-service-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: #16a085;
        }
        .custom-service-card h4 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 1.2rem;
        }
        .custom-service-card p {
            color: #666;
            margin: 0;
            font-size: 0.9rem;
        }
        .custom-process-steps {
            margin: 30px 0;
        }
        .custom-process-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 25px;
            background: #f0f9ff;
            border-radius: 10px;
            border-left: 4px solid #16a085;
        }
        .custom-step-number {
            background: #16a085;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .custom-step-content h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .custom-step-content p {
            margin: 0;
            color: #666;
        }
        .custom-breadcrumb {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .custom-breadcrumb-list {
            display: flex;
            align-items: center;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
        }
        .custom-breadcrumb-item {
            margin-right: 15px;
            color: #666;
            font-size: 14px;
        }
        .custom-breadcrumb-item a {
            color: #16a085;
            text-decoration: none;
        }
        .custom-breadcrumb-item a:hover {
            text-decoration: underline;
        }
        .custom-breadcrumb-item::after {
            content: "→";
            margin-left: 15px;
            color: #999;
        }
        .custom-breadcrumb-item:last-child::after {
            display: none;
        }
        .custom-breadcrumb-item:last-child {
            color: #2c3e50;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .custom-main-content {
                padding: 25px;
            }
            .custom-services-grid {
                grid-template-columns: 1fr;
            }
            .custom-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .custom-breadcrumb-item {
                font-size: 12px;
                margin-right: 10px;
            }
            .custom-breadcrumb-item::after {
                margin-left: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Подключение шапки через JavaScript систему -->
    <div data-include="/header.html"></div>
    
    <!-- Подключение модалки -->
    <div data-include="/modal.html"></div>

    <!-- Breadcrumb -->
    <div class="custom-container">
        <div class="custom-breadcrumb">
            <ul class="custom-breadcrumb-list">
                <li class="custom-breadcrumb-item"><a href="/">Главная</a></li>
                <li class="custom-breadcrumb-item"><a href="/">Включение в реестр Минпромторга</a></li>
                <li class="custom-breadcrumb-item"><?= htmlspecialchars($cityData['name']) ?></li>
            </ul>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="custom-hero">
        <div class="custom-container">
            <div class="custom-main-content">
                <h1 class="custom-header"><?= htmlspecialchars($h1) ?></h1>
                
                <div class="custom-city-info">
                    <h3>Профессиональная помощь производителям <?= htmlspecialchars($cityData['name_genitive']) ?></h3>
                    <p><?= htmlspecialchars($localText) ?></p>
                </div>

                <div class="custom-stats-grid">
                    <div class="custom-stat-item">
                        <span class="custom-stat-number">7-9</span>
                        <span class="custom-stat-label">недель на включение</span>
                    </div>
                    <div class="custom-stat-item">
                        <span class="custom-stat-number">95%</span>
                        <span class="custom-stat-label">успешных заявок</span>
                    </div>
                    <div class="custom-stat-item">
                        <span class="custom-stat-number">300+</span>
                        <span class="custom-stat-label">довольных клиентов</span>
                    </div>
                    <div class="custom-stat-item">
                        <span class="custom-stat-number">5+</span>
                        <span class="custom-stat-label">лет опыта</span>
                    </div>
                </div>

                <p class="custom-paragraph">Включение в реестр Минпромторга в <?= htmlspecialchars($cityData['name_prepositional']) ?> открывает производителям <?= htmlspecialchars($cityData['region']) ?> доступ к государственным заказам, льготам и преференциям. Наша компания предоставляет профессиональную помощь в <a href="/" title="Включение в реестр Минпромторга">включении в реестр Минпромторга</a> для предприятий всех отраслей промышленности.</p>

                <h2 class="custom-section-title">Что дает включение в реестр Минпромторга производителям <?= htmlspecialchars($cityData['name_genitive']) ?></h2>
                <p class="custom-paragraph">Внесение продукции в реестр Минпромторга предоставляет производителям <?= htmlspecialchars($cityData['region']) ?> значительные конкурентные преимущества:</p>
                
                <ul class="custom-list">
                    <li class="custom-list-item">Участие в государственных и муниципальных закупках с приоритетом для российской продукции</li>
                    <li class="custom-list-item">Доступ к мерам государственной поддержки и субсидированию</li>
                    <li class="custom-list-item">Возможность получения льготных кредитов и финансирования</li>
                    <li class="custom-list-item">Повышение статуса компании как надежного поставщика</li>
                    <li class="custom-list-item">Расширение рынков сбыта через государственные каналы</li>
                    <li class="custom-list-item">Участие в региональных программах поддержки производителей <?= htmlspecialchars($cityData['region']) ?></li>
                </ul>

                <h2 class="custom-section-title">Как попасть в реестр Минпромторга в <?= htmlspecialchars($cityData['name_prepositional']) ?></h2>
                <p class="custom-paragraph">Процедура включения в реестр Минпромторга российских производителей из <?= htmlspecialchars($cityData['name_genitive']) ?> требует тщательной подготовки документов и соблюдения всех требований законодательства. Наши эксперты знают особенности работы с предприятиями <?= htmlspecialchars($cityData['region']) ?> и помогут пройти все этапы процедуры.</p>

                <div class="custom-process-steps">
                    <div class="custom-process-step">
                        <div class="custom-step-number">1</div>
                        <div class="custom-step-content">
                            <h4>Подготовительный этап</h4>
                            <p>Анализ продукции производителя из <?= htmlspecialchars($cityData['name_genitive']) ?> на соответствие критериям российского происхождения и промышленного производства</p>
                        </div>
                    </div>
                    <div class="custom-process-step">
                        <div class="custom-step-number">2</div>
                        <div class="custom-step-content">
                            <h4>Сбор документации</h4>
                            <p>Подготовка полного пакета документов с учетом специфики предприятий <?= htmlspecialchars($cityData['region']) ?></p>
                        </div>
                    </div>
                    <div class="custom-process-step">
                        <div class="custom-step-number">3</div>
                        <div class="custom-step-content">
                            <h4>Подача заявления</h4>
                            <p>Подача заявления в систему ГИСП Минпромторга с полным пакетом документов</p>
                        </div>
                    </div>
                    <div class="custom-process-step">
                        <div class="custom-step-number">4</div>
                        <div class="custom-step-content">
                            <h4>Сопровождение процедуры</h4>
                            <p>Взаимодействие с ведомствами на всех этапах рассмотрения заявления</p>
                        </div>
                    </div>
                </div>

                <h2 class="custom-section-title">Наши услуги по включению в реестр Минпромторга в <?= htmlspecialchars($cityData['name_prepositional']) ?></h2>
                <div class="custom-services-grid">
                    <div class="custom-service-card">
                        <span class="custom-service-icon">💼</span>
                        <h4>Консультационные услуги</h4>
                        <p>Анализ возможности включения продукции производителей <?= htmlspecialchars($cityData['name_genitive']) ?> в реестр</p>
                    </div>
                    <div class="custom-service-card">
                        <span class="custom-service-icon">📄</span>
                        <h4>Подготовка документов</h4>
                        <p>Полная подготовка документации с учетом региональных особенностей <?= htmlspecialchars($cityData['region']) ?></p>
                    </div>
                    <div class="custom-service-card">
                        <span class="custom-service-icon">🤝</span>
                        <h4>Сопровождение процедуры</h4>
                        <p>Профессиональное сопровождение на всех этапах включения в реестр</p>
                    </div>
                    <div class="custom-service-card">
                        <span class="custom-service-icon">🏭</span>
                        <h4>Специализированные услуги</h4>
                        <p>Помощь по отраслевым реестрам: <a href="/industrial" title="Промышленная продукция">промышленная продукция</a>, <a href="/radioelectronic" title="Радиоэлектронная продукция">радиоэлектроника</a>, <a href="/software" title="Программное обеспечение">ПО</a></p>
                    </div>
                </div>

                <h2 class="custom-section-title">Почему выбирают нас производители <?= htmlspecialchars($cityData['name_genitive']) ?></h2>
                <ul class="custom-list">
                    <li class="custom-list-item"><span class="custom-highlight">Региональная экспертиза:</span> знание особенностей работы с предприятиями <?= htmlspecialchars($cityData['region']) ?></li>
                    <li class="custom-list-item"><span class="custom-highlight">Гарантия результата:</span> 95% успешных заявок от производителей <?= htmlspecialchars($cityData['name_genitive']) ?></li>
                    <li class="custom-list-item"><span class="custom-highlight">Быстрые сроки:</span> включение в реестр за 7-9 недель</li>
                    <li class="custom-list-item"><span class="custom-highlight">Полное сопровождение:</span> от анализа до получения заключения</li>
                    <li class="custom-list-item"><span class="custom-highlight">Опытные эксперты:</span> более 5 лет работы с реестрами Минпромторга</li>
                </ul>

                <h2 class="custom-section-title">Особенности работы с производителями <?= htmlspecialchars($cityData['name_genitive']) ?></h2>
                <p class="custom-paragraph">Предприятия <?= htmlspecialchars($cityData['region']) ?> имеют свои отраслевые особенности, которые мы учитываем при подготовке документов для включения в реестр Минпромторга:</p>
                
                <ul class="custom-list">
                    <li class="custom-list-item">Учет региональных стандартов и требований</li>
                    <li class="custom-list-item">Знание специфики промышленности <?= htmlspecialchars($cityData['region']) ?></li>
                    <li class="custom-list-item">Опыт работы с местными органами власти</li>
                    <li class="custom-list-item">Понимание логистических и производственных особенностей региона</li>
                </ul>

                <div class="custom-contacts-block">
                    <h3>Получить консультацию по включению в реестр Минпромторга в <?= htmlspecialchars($cityData['name_prepositional']) ?></h3>
                    <p><strong>Телефон:</strong> <a href="tel:+79208981718">+7 920-898-17-18</a></p>
                    <p><strong>Email:</strong> <a href="mailto:reestrgarant@mail.ru">reestrgarant@mail.ru</a></p>
                    <p><strong>Время работы:</strong> Пн-Пт: 9:00-18:00 (по московскому времени)</p>
                    <p><strong>Обслуживаем:</strong> Все районы <?= htmlspecialchars($cityData['name_genitive']) ?> и <?= htmlspecialchars($cityData['region']) ?></p>
                </div>

                <p class="custom-paragraph"><strong>Обращайтесь к нашим специалистам за профессиональной помощью во включении в реестр Минпромторга в <?= htmlspecialchars($cityData['name_prepositional']) ?>. Мы гарантируем качественное сопровождение на всех этапах процедуры и успешное достижение поставленной цели для производителей <?= htmlspecialchars($cityData['region']) ?>.</strong></p>
            </div>
        </div>
    </section>

    <!-- Подключение футера через JavaScript систему -->
    <div data-include="/footer.html"></div>
    
    <!-- Подключение скриптов -->
    <script src="/include.js"></script>
    <script src="/script.js"></script>
</body>
</html>