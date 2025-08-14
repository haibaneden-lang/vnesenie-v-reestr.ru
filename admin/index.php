<?php
/**
 * Обновленный главный dashboard админки с добавлением сертификации
 * Файл: /admin/index.php (обновление существующего файла)
 */

require_once __DIR__ . '/../models/AdminAuth.php';
require_once __DIR__ . '/../models/News.php';
require_once __DIR__ . '/../models/CertificationPages.php'; // Добавляем модель сертификации

// Проверяем авторизацию
requireAuth();

$newsModel = new News();
$categoryModel = new NewsCategory();
$certModel = new CertificationPages(); // Добавляем модель сертификации

// Получаем статистику
$stats = [
    'total_news' => $newsModel->getAllNewsCount(),
    'published_news' => $newsModel->getPublishedNewsCount(),
    'categories' => count($categoryModel->getAllCategories()),
    // Добавляем статистику сертификации
    'total_cert_pages' => 0,
    'active_cert_pages' => 0,
    'cert_views' => 0,
    'cert_orders' => 0
];

// Пытаемся получить статистику сертификации (с обработкой ошибок на случай если таблица еще не создана)
try {
    $cert_stats = $certModel->getStatistics();
    $stats['total_cert_pages'] = $cert_stats['total_pages'];
    $stats['active_cert_pages'] = $cert_stats['active_pages']; 
    $stats['cert_views'] = $cert_stats['total_views'];
    $stats['cert_orders'] = $cert_stats['total_orders'];
} catch (Exception $e) {
    // Если таблица сертификации еще не создана, оставляем значения по умолчанию
    error_log("Ошибка получения статистики сертификации: " . $e->getMessage());
}

$current_admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Админ-панель</title>
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Боковая панель -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="3" width="12" height="16" rx="1" fill="white" opacity="0.9"/>
                        <line x1="6" y1="7" x2="14" y2="7" stroke="#667eea" stroke-width="0.8"/>
                        <line x1="6" y1="9" x2="14" y2="9" stroke="#667eea" stroke-width="0.8"/>
                        <line x1="6" y1="11" x2="14" y2="11" stroke="#667eea" stroke-width="0.8"/>
                        <line x1="6" y1="13" x2="14" y2="13" stroke="#667eea" stroke-width="0.8"/>
                        <circle cx="18" cy="6" r="3" fill="#27ae60"/>
                        <path d="M16.5 6l1 1 2-2" stroke="white" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                <span>Админ-панель</span>
            </div>

            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/" class="active">📊 Dashboard</a></li>
                    <li><a href="/admin/news/">📰 Новости</a></li>
                    <li><a href="/admin/categories/">📁 Категории</a></li>
                    <li><a href="/admin/certification/">🏆 Сертификация</a></li> <!-- Добавляем ссылку на сертификацию -->
                    <?php if ($current_admin['role'] === 'admin'): ?>
                        <li><a href="/admin/admins/">👥 Администраторы</a></li>
                    <?php endif; ?>
                    <li><a href="/admin/profile/">👤 Профиль</a></li>
                    <li><a href="/admin/logout.php">🚪 Выход</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Основной контент -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    Добро пожаловать, <?php echo htmlspecialchars($current_admin['name']); ?>!
                </div>
            </header>

            <div class="admin-content">
                <!-- Расширенная статистика -->
                <div class="stats-grid">
                    <!-- Статистика новостей -->
                    <div class="stat-card">
                        <div class="stat-icon">📰</div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_news']; ?></div>
                            <div class="stat-label">Всего новостей</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['published_news']; ?></div>
                            <div class="stat-label">Опубликовано</div>
                        </div>
                    </div>

                    <!-- Статистика сертификации -->
                    <div class="stat-card">
                        <div class="stat-icon">🏆</div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_cert_pages']; ?></div>
                            <div class="stat-label">Страниц сертификации</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['active_cert_pages']; ?></div>
                            <div class="stat-label">Активных страниц</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">👁</div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo number_format($stats['cert_views']); ?></div>
                            <div class="stat-label">Просмотров</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🛒</div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo number_format($stats['cert_orders']); ?></div>
                            <div class="stat-label">Заказов</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📁</div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['categories']; ?></div>
                            <div class="stat-label">Категорий</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-info">
                            <div class="stat-number">
                                <?php 
                                $conversion = $stats['cert_views'] > 0 ? 
                                    round(($stats['cert_orders'] / $stats['cert_views']) * 100, 1) : 0;
                                echo $conversion; 
                                ?>%
                            </div>
                            <div class="stat-label">Конверсия заказов</div>
                        </div>
                    </div>
                </div>

                <!-- Расширенные быстрые действия -->
                <div class="quick-actions">
                    <h2>Быстрые действия</h2>
                    <div class="actions-grid">
                        <!-- Новости -->
                        <a href="/admin/news/add.php" class="action-card">
                            <div class="action-icon">➕</div>
                            <div class="action-title">Добавить новость</div>
                            <div class="action-desc">Создать новую статью</div>
                        </a>

                        <!-- Сертификация -->
                        <a href="/admin/certification/add.php" class="action-card">
                            <div class="action-icon">🏆</div>
                            <div class="action-title">Новая сертификация</div>
                            <div class="action-desc">Добавить страницу сертификата</div>
                        </a>

                        <a href="/admin/categories/add.php" class="action-card">
                            <div class="action-icon">📂</div>
                            <div class="action-title">Новая категория</div>
                            <div class="action-desc">Добавить категорию новостей</div>
                        </a>

                        <a href="/admin/media/manager.php" class="action-card">
                            <div class="action-icon">🖼️</div>
                            <div class="action-title">Медиа файлы</div>
                            <div class="action-desc">Управление изображениями</div>
                        </a>

                        <a href="/news/" target="_blank" class="action-card">
                            <div class="action-icon">👁</div>
                            <div class="action-title">Просмотр сайта</div>
                            <div class="action-desc">Открыть публичную часть</div>
                        </a>

                        <?php if ($current_admin['role'] === 'admin'): ?>
                            <a href="/admin/admins/add.php" class="action-card">
                                <div class="action-icon">👤</div>
                                <div class="action-title">Новый админ</div>
                                <div class="action-desc">Добавить администратора</div>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Последние новости -->
                <div class="recent-news">
                    <h2>Последние новости</h2>
                    <div class="news-table">
                        <?php
                        $recent_news = $newsModel->getAllNews(1, 5);
                        if (empty($recent_news)):
                        ?>
                            <div class="no-data">
                                <p>Новостей пока нет. <a href="/admin/news/add.php">Добавить первую новость</a></p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Заголовок</th>
                                        <th>Категория</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_news as $news): ?>
                                        <tr>
                                            <td>
                                                <a href="/admin/news/edit.php?id=<?php echo $news['id']; ?>">
                                                    <?php echo htmlspecialchars($news['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($news['category_name'] ?? 'Без категории'); ?></td>
                                            <td>
                                                <span class="status <?php echo $news['is_published'] ? 'published' : 'draft'; ?>">
                                                    <?php echo $news['is_published'] ? 'Опубликовано' : 'Черновик'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></td>
                                            <td>
                                                <a href="/admin/news/edit.php?id=<?php echo $news['id']; ?>" class="btn-small">Править</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="table-footer">
                                <a href="/admin/news/" class="btn btn-secondary">Все новости</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Последние страницы сертификации -->
                <div class="recent-news">
                    <h2>Последние страницы сертификации</h2>
                    <div class="news-table">
                        <?php
                        // Пытаемся получить последние страницы сертификации
                        try {
                            $recent_cert_pages = $certModel->getAllPages(1, 5);
                            if (empty($recent_cert_pages)):
                        ?>
                                <div class="no-data">
                                    <p>Страниц сертификации пока нет. <a href="/admin/certification/add.php">Добавить первую страницу</a></p>
                                </div>
                        <?php else: ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Тип документа</th>
                                            <th>Цена</th>
                                            <th>Статус</th>
                                            <th>Просмотры</th>
                                            <th>Заказы</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_cert_pages as $page): ?>
                                            <tr>
                                                <td>
                                                    <a href="/admin/certification/edit.php?id=<?php echo $page['id']; ?>">
                                                        <?php echo htmlspecialchars($page['title']); ?>
                                                    </a>
                                                    <?php if ($page['is_featured']): ?>
                                                        <span style="color: #ff6b6b; font-size: 0.8rem; margin-left: 5px;">⭐ Рекомендуем</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($page['document_type'] ?: 'Не указан'); ?></td>
                                                <td>
                                                    <strong style="color: #28a745;">
                                                        <?php echo number_format($page['price'], 0, ',', ' '); ?> ₽
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="status <?php echo $page['is_active'] ? 'published' : 'draft'; ?>">
                                                        <?php echo $page['is_active'] ? 'Активна' : 'Неактивна'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo number_format($page['views_count']); ?></td>
                                                <td>
                                                    <?php echo number_format($page['orders_count']); ?>
                                                    <?php if ($page['views_count'] > 0): ?>
                                                        <small style="color: #6c757d;">
                                                            (<?php echo round(($page['orders_count'] / $page['views_count']) * 100, 1); ?>%)
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="/admin/certification/edit.php?id=<?php echo $page['id']; ?>" class="btn-small">Править</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="table-footer">
                                    <a href="/admin/certification/" class="btn btn-secondary">Все страницы сертификации</a>
                                </div>
                        <?php 
                            endif;
                        } catch (Exception $e) {
                            // Если таблица сертификации еще не создана
                        ?>
                            <div class="no-data">
                                <p style="color: #856404; background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffeaa7;">
                                    <strong>⚠️ Модуль сертификации не настроен</strong><br>
                                    Для использования функций сертификации необходимо создать таблицу в базе данных.<br>
                                    <a href="/admin/certification/" style="color: #667eea;">Перейти к настройке</a>
                                </p>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Быстрая статистика в нижней части -->
                <div style="margin-top: 40px; padding: 20px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 15px; color: white;">
                    <h3 style="margin: 0 0 15px 0; color: white;">📊 Краткая сводка</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold;"><?php echo $stats['total_news'] + $stats['total_cert_pages']; ?></div>
                            <div style="opacity: 0.9;">Всего контента</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold;"><?php echo $stats['published_news'] + $stats['active_cert_pages']; ?></div>
                            <div style="opacity: 0.9;">Активного контента</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold;"><?php echo number_format($stats['cert_views']); ?></div>
                            <div style="opacity: 0.9;">Просмотров за все время</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold;"><?php echo number_format($stats['cert_orders']); ?></div>
                            <div style="opacity: 0.9;">Заказов получено</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Проверка статуса модулей
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Dashboard загружен');
            console.log('📊 Статистика:');
            console.log('   Новости: <?php echo $stats['total_news']; ?> (<?php echo $stats['published_news']; ?> опубликовано)');
            console.log('   Сертификация: <?php echo $stats['total_cert_pages']; ?> (<?php echo $stats['active_cert_pages']; ?> активно)');
            console.log('   Заказы: <?php echo $stats['cert_orders']; ?> из <?php echo $stats['cert_views']; ?> просмотров');
            
            // Показываем уведомление если есть новые заказы
            <?php if ($stats['cert_orders'] > 0): ?>
            setTimeout(() => {
                showNotification('📈 У вас <?php echo $stats['cert_orders']; ?> заказов по сертификации!', 'info');
            }, 2000);
            <?php endif; ?>
        });

        // Простые уведомления
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 10000;
                background: ${type === 'info' ? '#d1ecf1' : '#d4edda'};
                color: ${type === 'info' ? '#0c5460' : '#155724'};
                border: 1px solid ${type === 'info' ? '#bee5eb' : '#c3e6cb'};
                padding: 15px 20px; border-radius: 8px; max-width: 350px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 14px;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;

            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = 'float: right; margin-left: 10px; cursor: pointer; font-size: 18px; opacity: 0.7;';
            closeBtn.onclick = () => notification.remove();
            notification.appendChild(closeBtn);

            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 8000);
        }

        // Добавляем стили анимации
        const styles = document.createElement('style');
        styles.innerHTML = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(styles);
    </script>
</body>
</html>