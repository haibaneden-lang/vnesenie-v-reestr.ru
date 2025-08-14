<?php
/**
 * Главная страница админки сертификации - ИСПРАВЛЕНО
 * Файл: /admin/certification/index.php
 */

require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/CertificationPages.php';

// Проверяем авторизацию
requireAuth();

$certModel = new CertificationPages();
$current_admin = getCurrentAdmin();

// Параметры для фильтрации и пагинации
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Получаем данные
try {
    $pages = $certModel->getAllPages($page, $limit, $category, $status, $search);
    $total_count = $certModel->getPagesCount($category, $status, $search);
    $total_pages = ceil($total_count / $limit);
    $stats = $certModel->getStatistics();
} catch (Exception $e) {
    $pages = [];
    $total_count = 0;
    $total_pages = 0;
    $stats = [];
    error_log('Ошибка в админке сертификации: ' . $e->getMessage());
}

// Список категорий
$categories_list = [
    'industrial' => 'ИСО',
    'medical' => 'Экологическая сертификация',
    'radioelectronic' => 'Лицензирование',
    'software' => 'Программное обеспечение',
    'telecom' => 'Телекоммуникационное оборудование',
    'oil_gas' => 'Нефтегазовое оборудование',
    'other' => 'Другое'
];

// Обработка сообщений
$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'page_deleted':
            $title = $_GET['title'] ?? '';
            $message = 'Страница "' . htmlspecialchars($title) . '" успешно удалена';
            $message_type = 'success';
            break;
        case 'status_changed':
            $message = 'Статус страницы изменен';
            $message_type = 'success';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'page_not_found':
            $message = 'Страница не найдена';
            $message_type = 'error';
            break;
        case 'no_permissions':
            $message = 'Недостаточно прав для выполнения операции';
            $message_type = 'error';
            break;
        case 'delete_failed':
            $message = 'Ошибка при удалении страницы';
            $message_type = 'error';
            break;
        default:
            $message = 'Произошла ошибка';
            $message_type = 'error';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сертификация | Админ-панель</title>
    <link rel="stylesheet" href="/admin/admin-styles.css">
    <style>
        /* Дополнительные стили для таблицы сертификации */
        .cert-admin-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .cert-admin-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .cert-admin-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .cert-admin-table tr:hover {
            background: #f8f9ff;
        }

        .cert-page-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .cert-page-slug {
            font-size: 12px;
            color: #666;
            font-family: monospace;
        }

        .cert-status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .cert-status-active {
            background: #d4edda;
            color: #155724;
        }

        .cert-status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .cert-featured-badge {
            background: #fff3cd;
            color: #856404;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 5px;
        }

        .cert-stats-box {
            text-align: center;
            font-size: 12px;
        }

        .cert-stats-number {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .cert-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .cert-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .cert-btn-edit {
            background: #007bff;
            color: white;
        }

        .cert-btn-edit:hover {
            background: #0056b3;
        }

        .cert-btn-view {
            background: #28a745;
            color: white;
        }

        .cert-btn-view:hover {
            background: #1e7e34;
        }

        .cert-btn-toggle {
            background: #ffc107;
            color: #212529;
        }

        .cert-btn-toggle:hover {
            background: #e0a800;
        }

        .cert-btn-delete {
            background: #dc3545;
            color: white;
        }

        .cert-btn-delete:hover {
            background: #c82333;
        }

        .cert-price {
            font-weight: 600;
            color: #28a745;
        }

        .cert-price-old {
            text-decoration: line-through;
            color: #666;
            font-size: 12px;
        }

        .cert-category {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }

        /* Фильтры */
        .cert-filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .cert-filter-group {
            display: flex;
            flex-direction: column;
        }

        .cert-filter-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .cert-filter-input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .cert-filter-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: white;
        }

        .cert-filter-buttons {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        /* Модальное окно подтверждения удаления */
        .cert-delete-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .cert-delete-modal.show {
            display: flex;
        }

        .cert-delete-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .cert-delete-icon {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .cert-delete-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .cert-delete-text {
            color: #666;
            margin-bottom: 25px;
        }

        .cert-delete-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .cert-admin-table {
                font-size: 12px;
            }
            
            .cert-admin-table th,
            .cert-admin-table td {
                padding: 8px 5px;
            }
            
            .cert-actions {
                flex-direction: column;
            }
            
            .cert-filters {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Боковая панель -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <div class="logo-icon">📋</div>
                <span>Админ-панель</span>
            </div>

            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/">📊 Dashboard</a></li>
                    <li><a href="/admin/news/">📰 Новости</a></li>
                    <li><a href="/admin/categories/">📁 Категории</a></li>
                    <li><a href="/admin/certification/" class="active">🏆 Сертификация</a></li>
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
                <h1>🏆 Сертификация</h1>
                <div class="admin-actions">
                    <a href="/admin/certification/add.php" class="btn btn-primary">➕ Создать страницу</a>
                    <a href="/certification/" target="_blank" class="btn btn-secondary">👁️ Каталог</a>
                </div>
            </header>

            <div class="admin-content">
                <!-- Сообщения -->
                <?php if ($message): ?>
                    <div class="<?php echo $message_type === 'success' ? 'success' : 'errors'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Статистика -->
                <?php if (!empty($stats)): ?>
                    <div class="admin-stats">
                        <div class="stat-card">
                            <div class="stat-icon">📄</div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($stats['total_pages'] ?? 0); ?></div>
                                <div class="stat-label">Всего страниц</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">✅</div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($stats['active_pages'] ?? 0); ?></div>
                                <div class="stat-label">Активных</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">⭐</div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($stats['featured_pages'] ?? 0); ?></div>
                                <div class="stat-label">Рекомендуемых</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">👁️</div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($stats['total_views'] ?? 0); ?></div>
                                <div class="stat-label">Просмотров</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📋</div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
                                <div class="stat-label">Заказов</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Фильтры -->
                <form class="cert-filters" method="GET">
                    <div class="cert-filter-group">
                        <label class="cert-filter-label">Поиск</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Название, slug, описание..." class="cert-filter-input">
                    </div>
                    
                    <div class="cert-filter-group">
                        <label class="cert-filter-label">Категория</label>
                        <select name="category" class="cert-filter-select">
                            <option value="">Все категории</option>
                            <?php foreach ($categories_list as $key => $name): ?>
                                <option value="<?php echo htmlspecialchars($key); ?>" 
                                        <?php echo $category === $key ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="cert-filter-group">
                        <label class="cert-filter-label">Статус</label>
                        <select name="status" class="cert-filter-select">
                            <option value="">Все статусы</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Активные</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Неактивные</option>
                        </select>
                    </div>
                    
                    <div class="cert-filter-buttons">
                        <button type="submit" class="btn btn-primary">🔍 Найти</button>
                        <a href="/admin/certification/" class="btn btn-secondary">🔄 Сбросить</a>
                    </div>
                </form>

                <!-- Таблица страниц -->
                <?php if (empty($pages)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📄</div>
                        <h3>Страниц не найдено</h3>
                        <p>Создайте первую страницу сертификации или измените фильтры поиска</p>
                        <a href="/admin/certification/add.php" class="btn btn-primary">➕ Создать страницу</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="cert-admin-table">
                            <thead>
                                <tr>
                                    <th>Страница</th>
                                    <th>Категория</th>
                                    <th>Цена</th>
                                    <th>Статус</th>
                                    <th>Статистика</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pages as $page): ?>
                                    <tr>
                                        <td>
                                            <div class="cert-page-title">
                                                <?php echo htmlspecialchars($page['title']); ?>
                                                <?php if ($page['is_featured']): ?>
                                                    <span class="cert-featured-badge">⭐ Рекомендуемая</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="cert-page-slug">
                                                /certification/<?php echo htmlspecialchars($page['slug']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($page['category'] && isset($categories_list[$page['category']])): ?>
                                                <span class="cert-category">
                                                    <?php echo htmlspecialchars($categories_list[$page['category']]); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #999;">Без категории</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="cert-price">
                                                <?php echo number_format($page['price']); ?> <?php echo $page['currency']; ?>
                                            </div>
                                            <?php if ($page['price_old']): ?>
                                                <div class="cert-price-old">
                                                    <?php echo number_format($page['price_old']); ?> <?php echo $page['currency']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="cert-status-badge <?php echo $page['is_active'] ? 'cert-status-active' : 'cert-status-inactive'; ?>">
                                                <?php echo $page['is_active'] ? '✅ Активна' : '❌ Неактивна'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="cert-stats-box">
                                                <span class="cert-stats-number"><?php echo number_format($page['views_count']); ?></span>
                                                <span style="font-size: 10px; color: #666;">просмотров</span>
                                            </div>
                                            <div class="cert-stats-box" style="margin-top: 5px;">
                                                <span class="cert-stats-number"><?php echo number_format($page['orders_count']); ?></span>
                                                <span style="font-size: 10px; color: #666;">заказов</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px;">
                                                <?php echo date('d.m.Y', strtotime($page['created_at'])); ?>
                                            </div>
                                            <?php if ($page['updated_at'] > $page['created_at']): ?>
                                                <div style="font-size: 10px; color: #666;">
                                                    изм. <?php echo date('d.m.Y', strtotime($page['updated_at'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="cert-actions">
                                                <a href="/admin/certification/edit.php?id=<?php echo $page['id']; ?>" 
                                                   class="cert-btn cert-btn-edit" title="Редактировать">
                                                    ✏️ Редактировать
                                                </a>
                                                
                                                <a href="/certification/<?php echo htmlspecialchars($page['slug']); ?>" 
                                                   target="_blank" class="cert-btn cert-btn-view" title="Просмотр">
                                                    👁️ Просмотр
                                                </a>
                                                
                                                <a href="/admin/certification/toggle.php?id=<?php echo $page['id']; ?>" 
                                                   class="cert-btn cert-btn-toggle" 
                                                   title="<?php echo $page['is_active'] ? 'Деактивировать' : 'Активировать'; ?>"
                                                   onclick="return confirm('Изменить статус страницы?')">
                                                    <?php echo $page['is_active'] ? '⏸️ Скрыть' : '▶️ Показать'; ?>
                                                </a>
                                                
                                                <?php if ($current_admin['role'] === 'admin'): ?>
                                                    <button class="cert-btn cert-btn-delete" 
                                                            onclick="showDeleteModal(<?php echo $page['id']; ?>, '<?php echo htmlspecialchars($page['title'], ENT_QUOTES); ?>')"
                                                            title="Удалить">
                                                        🗑️ Удалить
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            $base_url = '/admin/certification/?';
                            if ($category) $base_url .= 'category=' . urlencode($category) . '&';
                            if ($status) $base_url .= 'status=' . urlencode($status) . '&';
                            if ($search) $base_url .= 'search=' . urlencode($search) . '&';
                            ?>
                            
                            <?php if ($page > 1): ?>
                                <a href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>" class="pagination-btn">← Предыдущая</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="<?php echo $base_url; ?>page=<?php echo $i; ?>" 
                                   class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>" class="pagination-btn">Следующая →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="cert-delete-modal" id="deleteModal">
        <div class="cert-delete-content">
            <div class="cert-delete-icon">🗑️</div>
            <h3 class="cert-delete-title">Удалить страницу?</h3>
            <p class="cert-delete-text" id="deletePageTitle">
                Вы действительно хотите удалить эту страницу? Это действие нельзя будет отменить.
            </p>
            <div class="cert-delete-actions">
                <button class="btn btn-secondary" onclick="hideDeleteModal()">Отмена</button>
                <a href="#" id="deletePageLink" class="btn" style="background: #dc3545; color: white;">Удалить</a>
            </div>
        </div>
    </div>

    <script>
        // Функции для модального окна удаления
        function showDeleteModal(pageId, pageTitle) {
            const modal = document.getElementById('deleteModal');
            const titleElement = document.getElementById('deletePageTitle');
            const deleteLink = document.getElementById('deletePageLink');
            
            titleElement.textContent = `Вы действительно хотите удалить страницу "${pageTitle}"? Это действие нельзя будет отменить.`;
            deleteLink.href = `/admin/certification/delete.php?id=${pageId}`;
            
            modal.classList.add('show');
            
            // Закрытие по клику вне модального окна
            modal.onclick = function(e) {
                if (e.target === modal) {
                    hideDeleteModal();
                }
            };
        }
        
        function hideDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('show');
        }
        
        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideDeleteModal();
            }
        });
        
        console.log('Админка сертификации загружена. Страниц: <?php echo count($pages); ?>');
    </script>
</body>
</html>