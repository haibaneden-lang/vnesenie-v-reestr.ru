<?php
require_once __DIR__ . '/../../models/AdminAuth.php';
require_once __DIR__ . '/../../models/News.php';

// Проверяем авторизацию
requireAuth();

$newsModel = new News();
$categoryModel = new NewsCategory();

// Параметры пагинации и фильтрации
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$category_filter = intval($_GET['category'] ?? 0);
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

// Получаем новости с учетом фильтров
$news = $newsModel->getAdminNews($page, $limit, $category_filter, $status_filter, $search);
$total_news = $newsModel->getAdminNewsCount($category_filter, $status_filter, $search);
$total_pages = ceil($total_news / $limit);

// Получаем категории для фильтра
$categories = $categoryModel->getAllCategories();

$current_admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление новостями | Админ-панель</title>
    <link rel="stylesheet" href="/admin/admin-styles.css">
    <style>
        .news-filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 2fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .news-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .news-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .news-table th,
        .news-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .news-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .news-table tr:hover {
            background: #f8f9fa;
        }
        
        .news-title {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .news-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .news-title a:hover {
            color: #667eea;
        }
        
        .news-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 4px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-published {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .featured-badge {
            background: #ff6b6b;
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-left: 8px;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .action-edit {
            background: #667eea;
            color: white;
        }
        
        .action-edit:hover {
            background: #5a67d8;
        }
        
        .action-delete {
            background: #dc3545;
            color: white;
        }
        
        .action-delete:hover {
            background: #c82333;
        }
        
        .action-toggle {
            background: #28a745;
            color: white;
        }
        
        .action-toggle:hover {
            background: #218838;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-decoration: none;
            color: #495057;
            min-width: 40px;
            text-align: center;
        }
        
        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination a:hover {
            background: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .news-table {
                font-size: 0.9rem;
            }
            
            .news-table th,
            .news-table td {
                padding: 10px;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
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
                    <li><a href="/admin/">📊 Dashboard</a></li>
                    <li><a href="/admin/news/" class="active">📰 Новости</a></li>
                    <li><a href="/admin/categories/">📁 Категории</a></li>
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
                <h1>Управление новостями</h1>
                <div class="admin-actions">
                    <a href="/admin/news/add.php" class="btn btn-primary">➕ Добавить новость</a>
                </div>
            </header>

            <div class="admin-content">
                <!-- Фильтры -->
                <div class="news-filters">
                    <form method="GET" class="filters-grid">
                        <div class="filter-group">
                            <label>Категория</label>
                            <select name="category">
                                <option value="">Все категории</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Статус</label>
                            <select name="status">
                                <option value="">Все статусы</option>
                                <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
                                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Сортировка</label>
                            <select name="sort">
                                <option value="newest">Новые первыми</option>
                                <option value="oldest">Старые первыми</option>
                                <option value="title">По названию</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Поиск</label>
                            <input type="text" name="search" placeholder="Поиск по заголовку..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <div class="filter-group">
                            <button type="submit" class="btn btn-secondary">Применить</button>
                        </div>
                    </form>
                </div>

                <!-- Список новостей -->
                <div class="news-table-container">
                    <?php if (empty($news)): ?>
                        <div class="no-data">
                            <p>Новости не найдены.</p>
                            <a href="/admin/news/add.php" class="btn btn-primary">Добавить первую новость</a>
                        </div>
                    <?php else: ?>
                        <table class="news-table">
                            <thead>
                                <tr>
                                    <th>Заголовок</th>
                                    <th>Категория</th>
                                    <th>Статус</th>
                                    <th>Просмотры</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="news-title">
                                                <a href="/admin/news/edit.php?id=<?php echo $item['id']; ?>">
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </a>
                                                <?php if ($item['is_featured']): ?>
                                                    <span class="featured-badge">Рекомендуем</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="news-meta">
                                                Slug: <?php echo htmlspecialchars($item['slug']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['category_name'] ?? 'Без категории'); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $item['is_published'] ? 'status-published' : 'status-draft'; ?>">
                                                <?php echo $item['is_published'] ? 'Опубликовано' : 'Черновик'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo number_format($item['views_count']); ?>
                                        </td>
                                        <td>
                                            <?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="/admin/news/edit.php?id=<?php echo $item['id']; ?>" 
                                                   class="action-btn action-edit">Править</a>
                                                
                                                <a href="/admin/news/toggle.php?id=<?php echo $item['id']; ?>" 
                                                   class="action-btn action-toggle"
                                                   onclick="return confirm('Изменить статус публикации?')">
                                                    <?php echo $item['is_published'] ? 'Скрыть' : 'Опубликовать'; ?>
                                                </a>
                                                
                                                <a href="/admin/news/delete.php?id=<?php echo $item['id']; ?>" 
                                                   class="action-btn action-delete"
                                                   onclick="return confirm('Удалить новость? Это действие нельзя отменить!')">
                                                    Удалить
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Пагинация -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="/admin/news/?page=<?php echo $page - 1; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        ← Назад
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="/admin/news/?page=<?php echo $i; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="/admin/news/?page=<?php echo $page + 1; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        Вперед →
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Статистика -->
                <div style="margin-top: 20px; text-align: center; color: #6c757d;">
                    Всего новостей: <?php echo $total_news; ?> 
                    | Показано: <?php echo count($news); ?>
                    | Страница <?php echo $page; ?> из <?php echo $total_pages; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Автоотправка формы при изменении фильтров
        document.querySelectorAll('.news-filters select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>