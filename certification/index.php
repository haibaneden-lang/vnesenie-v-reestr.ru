<?php
/**
 * Публичные страницы сертификации
 * Файл: /certification/index.php
 */

// Подключаем модель
require_once __DIR__ . '/../models/CertificationPages.php';
$certModel = new CertificationPages();

// Получаем slug из URL
$slug = $_GET['slug'] ?? '';

// Если есть slug - показываем отдельную страницу
if (!empty($slug)) {
    // Получаем страницу
    $page = $certModel->getBySlug($slug);

    if (!$page || !$page['is_active']) {
        header("HTTP/1.0 404 Not Found");
        include __DIR__ . '/../404.html';
        exit;
    }

    // Увеличиваем счетчик просмотров
    $certModel->incrementViews($page['id']);
    
    // Подключаем отдельную страницу сертификата
    include __DIR__ . '/document-page.php';
    exit;
}

// Если нет slug - показываем каталог
$category = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');
$page_num = max(1, intval($_GET['page'] ?? 1));
$limit = 12;

// Получаем все активные страницы сертификации
$all_pages = $certModel->getActivePages($page_num, $limit, $category, $search);
$total_pages_count = $certModel->getActivePagesCount($category, $search);
$total_pages = ceil($total_pages_count / $limit);

// Получаем статистику
$stats = $certModel->getStatistics();

// Категории для фильтра
$categories = [
    '' => 'Все услуги',
    'industrial' => 'ИСО',
    'medical' => 'Экологическая сертификация', 
    'radioelectronic' => 'Лицензирование',
    'software' => 'Программное обеспечение',
    'telecom' => 'Телекоммуникационное оборудование',
    'oil_gas' => 'Нефтегазовое оборудование',
    'other' => 'Другие услуги'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $search ? "Поиск: " . htmlspecialchars($search) : ($category ? $categories[$category] : "Услуги сертификации"); ?> | Реестр Гарант</title>
    <meta name="description" content="Полный каталог услуг сертификации и документооборота. Сертификаты соответствия, декларации, заключения для всех видов продукции.">
    <meta name="keywords" content="сертификация, сертификат соответствия, декларация соответствия, документы для бизнеса">
    
    <!-- Фавиконы -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <!-- Подключаем основные стили сайта -->
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    
    <style>
        /* === СТИЛИ КАТАЛОГА СЕРТИФИКАЦИИ === */
        
        .catalog-page-wrapper {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 0;
        }
        
        .catalog-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Breadcrumbs */
        .catalog-breadcrumbs {
            background: white;
            padding: 15px 0;
            margin-top: 80px;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .catalog-breadcrumbs-inner {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .catalog-breadcrumbs a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .catalog-breadcrumbs a:hover {
            text-decoration: underline;
        }
        
        /* Hero Section */
        .catalog-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        
        .catalog-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23pattern)"/></svg>');
        }
        
        .catalog-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .catalog-hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .catalog-hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        /* Фильтры и поиск */
        .catalog-filters {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .filters-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 20px;
            align-items: end;
        }
        
        .search-group {
            position: relative;
        }
        
        .search-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .category-select {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category-select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .filter-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        /* Статистика */
        .catalog-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        /* Сетка карточек */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .service-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
        
        .service-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .service-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0 0 10px 0;
            line-height: 1.4;
        }
        
        .service-type {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .service-body {
            padding: 25px;
        }
        
        .service-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .service-params {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .service-param {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        
        .param-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .param-value {
            font-size: 13px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .service-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .service-price {
            color: #27ae60;
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        .service-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .service-btn:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
        }
        
        /* Пагинация */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 40px 0;
        }
        
        .pagination-btn {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .pagination-btn:hover,
        .pagination-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .empty-text {
            color: #666;
            margin-bottom: 20px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .catalog-hero-title {
                font-size: 2.5rem;
            }
            
            .filters-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .service-params {
                grid-template-columns: 1fr;
            }
            
            .catalog-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .catalog-container {
                padding: 0 15px;
            }
            
            .catalog-hero-title {
                font-size: 2rem;
            }
            
            .catalog-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="catalog-page-wrapper">
    <!-- Подключение шапки -->
    <div data-include="header.html"></div>

    <!-- Breadcrumbs -->
    <div class="catalog-breadcrumbs">
        <div class="catalog-breadcrumbs-inner">
            <a href="/">🏠 Главная</a>
            <span>→</span>
            <span>Услуги сертификации</span>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="catalog-hero">
        <div class="catalog-container">
            <div class="catalog-hero-content">
                <h1 class="catalog-hero-title">Услуги сертификации</h1>
                <p class="catalog-hero-subtitle">
                    Полный спектр услуг по сертификации и получению разрешительных документов. 
                    Работаем со всеми видами продукции и гарантируем результат.
                </p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="catalog-container">
        
        <!-- Статистика -->
        <div class="catalog-stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['total_pages']; ?></span>
                <span class="stat-label">Видов документов</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['active_pages']; ?></span>
                <span class="stat-label">Активных услуг</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format($stats['total_views']); ?></span>
                <span class="stat-label">Просмотров</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['total_orders']; ?></span>
                <span class="stat-label">Успешных заказов</span>
            </div>
        </div>

        <!-- Фильтры и поиск -->
        <div class="catalog-filters">
            <form method="GET" action="/certification/">
                <div class="filters-row">
                    <div class="search-group">
                        <label for="search">🔍 Поиск услуг</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               class="search-input"
                               placeholder="Введите название документа или услуги..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div>
                        <label for="category">📂 Категория</label>
                        <select id="category" name="category" class="category-select">
                            <?php foreach ($categories as $key => $name): ?>
                                <option value="<?php echo $key; ?>" <?php echo $category === $key ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="filter-btn">Найти</button>
                </div>
            </form>
        </div>

        <!-- Результаты поиска -->
        <?php if ($search || $category): ?>
            <div style="margin: 20px 0; padding: 15px; background: #e3f2fd; border-radius: 10px; border-left: 4px solid #2196f3;">
                <strong>Результаты поиска:</strong>
                <?php if ($search): ?>
                    по запросу "<?php echo htmlspecialchars($search); ?>"
                <?php endif; ?>
                <?php if ($category): ?>
                    в категории "<?php echo htmlspecialchars($categories[$category]); ?>"
                <?php endif; ?>
                - найдено <?php echo $total_pages_count; ?> услуг
                <a href="/certification/" style="margin-left: 15px; color: #1976d2;">Сбросить фильтры</a>
            </div>
        <?php endif; ?>

        <!-- Сетка услуг -->
        <?php if (!empty($all_pages)): ?>
            <div class="services-grid">
                <?php foreach ($all_pages as $service): ?>
                    <div class="service-card">
                        <div class="service-header">
                            <h3 class="service-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                            <?php if (!empty($service['document_type'])): ?>
                                <p class="service-type"><?php echo htmlspecialchars($service['document_type']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="service-body">
                            <?php if (!empty($service['short_description'])): ?>
                                <p class="service-description">
                                    <?php echo htmlspecialchars($service['short_description']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="service-params">
                                <?php if (!empty($service['duration'])): ?>
                                    <div class="service-param">
                                        <div class="param-label">⏱️ Срок оформления</div>
                                        <div class="param-value"><?php echo htmlspecialchars($service['duration']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($service['validity_period'])): ?>
                                    <div class="service-param">
                                        <div class="param-label">📅 Срок действия</div>
                                        <div class="param-value"><?php echo htmlspecialchars($service['validity_period']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($service['guarantee'])): ?>
                                    <div class="service-param">
                                        <div class="param-label">✅ Гарантии</div>
                                        <div class="param-value"><?php echo htmlspecialchars($service['guarantee']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($service['category'])): ?>
                                    <div class="service-param">
                                        <div class="param-label">📂 Категория</div>
                                        <div class="param-value"><?php echo htmlspecialchars($categories[$service['category']] ?? 'Другое'); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="service-footer">
                                <?php if ($service['show_price'] && $service['price'] > 0): ?>
                                    <div class="service-price">
                                        <?php echo number_format($service['price'], 0, ',', ' '); ?> ₽
                                    </div>
                                <?php else: ?>
                                    <div class="service-price" style="color: #666; font-size: 1rem;">
                                        По запросу
                                    </div>
                                <?php endif; ?>
                                
                                <a href="/certification/<?php echo $service['slug']; ?>" class="service-btn">
                                    Подробнее →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);
$base_url = '/certification/' . ($query_string ? '?' . $query_string : '');
$separator = $query_string ? '&' : '?';
?>

<?php if ($page_num > 1): ?>
    <a href="<?php echo $base_url . $separator; ?>page=<?php echo $page_num - 1; ?>" 
       class="pagination-btn">← Предыдущая</a>
<?php endif; ?>

<?php for ($i = max(1, $page_num - 2); $i <= min($total_pages, $page_num + 2); $i++): ?>
    <a href="<?php echo $base_url . $separator; ?>page=<?php echo $i; ?>" 
       class="pagination-btn <?php echo $i == $page_num ? 'active' : ''; ?>">
        <?php echo $i; ?>
    </a>
<?php endfor; ?>

<?php if ($page_num < $total_pages): ?>
    <a href="<?php echo $base_url . $separator; ?>page=<?php echo $page_num + 1; ?>" 
       class="pagination-btn">Следующая →</a>
<?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Пустое состояние -->
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3 class="empty-title">Услуги не найдены</h3>
                <p class="empty-text">Попробуйте изменить параметры поиска или сбросить фильтры</p>
                <a href="/certification/" class="filter-btn">Показать все услуги</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Подключение модального окна -->
    <div data-include="modal.html"></div>

    <!-- Подключение футера -->
    <div data-include="footer.html"></div>

    <!-- JavaScript -->
    <script src="/include.js"></script>
    <script src="/script.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Анимация появления карточек
            const cards = document.querySelectorAll('.service-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Живой поиск с задержкой
            const searchInput = document.getElementById('search');
            let searchTimeout;
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        // Можно добавить AJAX поиск здесь
                        console.log('Поиск:', this.value);
                    }, 500);
                });
            }
            
            console.log('Каталог сертификации загружен');
            console.log('Всего услуг:', <?php echo $total_pages_count; ?>);
        });
    </script>
</body>
</html>