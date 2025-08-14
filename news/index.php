<?php
require_once __DIR__ . '/../models/News.php';

// Инициализация
$newsModel = new News();
$categoryModel = new NewsCategory();

// Параметры пагинации
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Получение данных
if ($search) {
    $news = $newsModel->searchNews($search, $page, $limit);
    $total_news = count($news); // Для поиска пока без подсчета
} else {
    $news = $newsModel->getPublishedNews($page, $limit, $category_id);
    $total_news = $newsModel->getPublishedNewsCount($category_id);
}

$categories = $categoryModel->getActiveCategories();
$total_pages = ceil($total_news / $limit);

// Получение текущей категории для заголовка
$current_category = null;
if ($category_id) {
    $current_category = $categoryModel->getCategoryById($category_id);
}

// Формирование заголовка страницы
$page_title = 'Новости';
if ($search) {
    $page_title = 'Поиск: ' . htmlspecialchars($search);
} elseif ($current_category) {
    $page_title = $current_category['name'];
}

// Мета-описание
$meta_description = 'Актуальные новости о включении в реестр Минпромторга, изменениях в законодательстве, советы и инструкции для производителей.';
if ($current_category) {
    $meta_description = $current_category['description'] ?: $meta_description;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | Реестр Гарант</title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    
    <!-- Фавиконы -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- Стили -->
    <link rel="stylesheet" href="/styles-new.css">
    <link rel="stylesheet" href="/components-styles.css">
    <link rel="stylesheet" href="/news/news-styles.css">
</head>
<body>
    <!-- Подключение шапки - ИСПРАВЛЕНО: убран слеш в начале -->
    <div data-include="../header.html"></div>

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <a href="/">Главная</a>
            <span>→</span>
            <a href="/news/">Новости</a>
            <?php if ($current_category): ?>
                <span>→</span>
                <span><?php echo htmlspecialchars($current_category['name']); ?></span>
            <?php elseif ($search): ?>
                <span>→</span>
                <span>Поиск</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <main class="news-content">
        <div class="container">
            <div class="news-layout">
                <!-- Sidebar -->
                <aside class="news-sidebar">
                    <!-- Поиск -->
               

                    <!-- Категории -->
                    <div class="sidebar-widget">
                        <h3>Категории</h3>
                        <ul class="categories-list">
                            <li>
                                <a href="/news/" class="<?php echo !$category_id ? 'active' : ''; ?>">
                                    Все новости
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="/news/?category=<?php echo $category['id']; ?>" 
                                       class="<?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Контакты -->
                    <div class="sidebar-widget contact-widget">
                        <h3>Нужна помощь?</h3>
                        <p>Получите бесплатную консультацию по включению в реестр Минпромторга</p>
                        <a href="tel:+79208981718" class="contact-phone">+7 920-898-17-18</a>
                        <a href="mailto:reestrgarant@mail.ru" class="contact-email">reestrgarant@mail.ru</a>
                        <button class="btn btn-primary" onclick="openModal('consultation')" 
                                style="width: 100%; margin-top: 15px;">
                            Получить консультацию
                        </button>
                    </div>
                </aside>

                <!-- News List -->
                <div class="news-main">
                    <div class="news-header">
                        <h1><?php echo htmlspecialchars($page_title); ?></h1>
                        <?php if ($search): ?>
                            <p class="search-results">Найдено результатов: <?php echo count($news); ?></p>
                        <?php elseif ($current_category && $current_category['description']): ?>
                            <p class="category-description"><?php echo htmlspecialchars($current_category['description']); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($news)): ?>
                        <div class="no-news">
                            <h3>Новости не найдены</h3>
                            <p>В данной категории пока нет опубликованных новостей.</p>
                            <a href="/news/" class="btn btn-primary">Вернуться ко всем новостям</a>
                        </div>
                    <?php else: ?>
                        <div class="news-grid">
                            <?php foreach ($news as $item): ?>
                                <article class="news-card <?php echo $item['is_featured'] ? 'featured' : ''; ?>">
                                    <?php if ($item['featured_image']): ?>
                                        <div class="news-image">
                                            <img src="<?php echo htmlspecialchars($item['featured_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="news-content">
                                        <?php if ($item['is_featured']): ?>
                                            <span class="featured-badge">Рекомендуем</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['category_name']): ?>
                                            <div class="news-category">
                                                <a href="/news/?category=<?php echo $item['category_id']; ?>">
                                                    <?php echo htmlspecialchars($item['category_name']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <h2 class="news-title">
                                            <a href="/news/<?php echo htmlspecialchars($item['slug']); ?>">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                            </a>
                                        </h2>
                                        
                                        <?php if ($item['excerpt']): ?>
                                            <p class="news-excerpt">
                                                <?php echo htmlspecialchars($item['excerpt']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="news-meta">
                                            <span class="news-date">
                                                <?php echo date('d.m.Y', strtotime($item['published_at'])); ?>
                                            </span>
                                            <span class="news-views">
                                                👁 <?php echo number_format($item['views_count']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- Пагинация -->
                        <?php if ($total_pages > 1 && !$search): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="/news/?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" 
                                       class="pagination-btn">← Предыдущая</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="/news/?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" 
                                       class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="/news/?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" 
                                       class="pagination-btn">Следующая →</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Подключение модального окна - ИСПРАВЛЕНО: убран слеш в начале -->
    <div data-include="../modal.html"></div>

    <!-- Подключение футера - ИСПРАВЛЕНО: убран слеш в начале и использовать .html -->
    <div data-include="../footer.html"></div>

    <!-- Подключение JavaScript файлов -->
    <script src="/include.js"></script>
    <script src="/script.js"></script>
</body>
</html>