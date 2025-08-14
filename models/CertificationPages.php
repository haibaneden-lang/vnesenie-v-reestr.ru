<?php
/**
 * Модель для работы с коммерческими страницами сертификации
 * Файл: /models/CertificationPages.php
 */

require_once __DIR__ . '/../config/database.php';

class CertificationPages 
{
    private $db;

    public function __construct() 
    {
        $this->db = getDatabase();
    }

    /**
     * Получить соединение с базой данных
     */
    public function getConnection() 
    {
        return $this->db->getConnection();
    }

    /**
     * Получить все страницы для админки
     */
    public function getAllPages($page = 1, $limit = 20, $category = '', $status = '', $search = '') 
    {
        $offset = ($page - 1) * $limit;
        $conditions = ['1=1'];
        $params = [];

        // Фильтр по категории
        if (!empty($category)) {
            $conditions[] = 'category = ?';
            $params[] = $category;
        }

        // Фильтр по статусу
        if ($status === 'active') {
            $conditions[] = 'is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'is_active = 0';
        }

        // Поиск
        if (!empty($search)) {
            $conditions[] = '(title LIKE ? OR certificate_name LIKE ? OR content LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT * FROM certification_pages 
                WHERE {$whereClause}
                ORDER BY is_featured DESC, sort_order ASC, created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Получить количество страниц для пагинации
     */
    public function getPagesCount($category = '', $status = '', $search = '') 
    {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($category)) {
            $conditions[] = 'category = ?';
            $params[] = $category;
        }

        if ($status === 'active') {
            $conditions[] = 'is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'is_active = 0';
        }

        if (!empty($search)) {
            $conditions[] = '(title LIKE ? OR certificate_name LIKE ? OR content LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $conditions);
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM certification_pages WHERE {$whereClause}", $params);
        
        return $result['count'] ?? 0;
    }

    /**
     * Получить страницу по ID
     */
    public function getPageById($id) 
    {
        return $this->db->fetchOne(
            "SELECT * FROM certification_pages WHERE id = ?", 
            [$id]
        );
    }

    /**
     * Получить страницу по slug (старый метод - для совместимости)
     */
    public function getPageBySlug($slug) 
    {
        return $this->db->fetchOne(
            "SELECT * FROM certification_pages WHERE slug = ? AND is_active = 1", 
            [$slug]
        );
    }

    /**
     * Получить страницу по slug (новый метод - для публичных страниц)
     */
    public function getBySlug($slug) 
    {
        return $this->db->fetchOne(
            "SELECT cp.*, 
                    ROUND((cp.orders_count * 100.0 / NULLIF(cp.views_count, 0)), 1) as conversion_rate
             FROM certification_pages cp 
             WHERE cp.slug = ? AND cp.is_active = 1
             LIMIT 1", 
            [$slug]
        );
    }

    /**
     * Получить страницу по ID с дополнительной информацией
     */
    public function getById($id) 
    {
        return $this->db->fetchOne(
            "SELECT cp.*, 
                    ROUND((cp.orders_count * 100.0 / NULLIF(cp.views_count, 0)), 1) as conversion_rate
             FROM certification_pages cp 
             WHERE cp.id = ?
             LIMIT 1", 
            [$id]
        );
    }

    /**
     * Создать новую страницу
     */
    public function createPage($data) 
    {
        $createdBy = 1;
        try {
            $currentAdmin = getCurrentAdmin();
            $createdBy = $currentAdmin['id'] ?? 1;
        } catch (Exception $e) {
            // Игнорируем ошибку авторизации
        }
        
        // Проверяем наличие необходимых полей в таблице
        $sql = "INSERT INTO certification_pages (
                    title, slug, meta_description, meta_keywords,
                    document_type, certificate_name, price, price_old, currency,
                    featured_image, certificate_image,
                    short_description, content, requirements, documents_needed,
                    duration, validity_period, guarantee,
                    category, subcategory, tags,
                    is_active, is_featured, show_price, show_order_button,
                    order_button_text, order_email, order_phone, consultation_available,
                    sort_order, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['title'],
            $data['slug'],
            $data['meta_description'] ?? '',
            $data['meta_keywords'] ?? '',
            $data['document_type'] ?? '',
            $data['certificate_name'] ?? '',
            $data['price'] ?? 0,
            $data['price_old'] ?? null,
            $data['currency'] ?? 'RUB',
            $data['featured_image'] ?? '',
            $data['certificate_image'] ?? '',
            $data['short_description'] ?? '',
            $data['content'] ?? '',
            $data['requirements'] ?? '',
            $data['documents_needed'] ?? '',
            $data['duration'] ?? '',
            $data['validity_period'] ?? '',
            $data['guarantee'] ?? '',
            $data['category'] ?? '',
            $data['subcategory'] ?? '',
            $data['tags'] ?? '',
            $data['is_active'] ?? 1,
            $data['is_featured'] ?? 0,
            $data['show_price'] ?? 1,
            $data['show_order_button'] ?? 1,
            $data['order_button_text'] ?? 'Заказать сертификат',
            $data['order_email'] ?? '',
            $data['order_phone'] ?? '',
            $data['consultation_available'] ?? 1,
            $data['sort_order'] ?? 0,
            $createdBy
        ];

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $this->db->getConnection()->lastInsertId();
    }

    /**
     * Обновить страницу
     */
    public function updatePage($id, $data) 
    {
        $updatedBy = 1;
        try {
            $currentAdmin = getCurrentAdmin();
            $updatedBy = $currentAdmin['id'] ?? 1;
        } catch (Exception $e) {
            // Игнорируем ошибку авторизации
        }
        
        $sql = "UPDATE certification_pages SET 
                    title = ?, h1 = ?, slug = ?, meta_description = ?, meta_keywords = ?,
                    document_type = ?, certificate_name = ?, price = ?, price_old = ?, currency = ?,
                    featured_image = ?, certificate_image = ?, gallery_images = ?,
                    short_description = ?, content = ?, advantages = ?, requirements = ?, documents_needed = ?,
                    duration = ?, validity_period = ?, guarantee = ?,
                    category = ?, subcategory = ?, tags = ?,
                    is_active = ?, is_featured = ?, show_price = ?, show_order_button = ?,
                    order_button_text = ?, order_email = ?, order_phone = ?, consultation_available = ?,
                    sort_order = ?, updated_by = ?
                WHERE id = ?";

        $params = [
            $data['title'],
            $data['h1'] ?: $data['title'],
            $data['slug'],
            $data['meta_description'] ?? '',
            $data['meta_keywords'] ?? '',
            $data['document_type'] ?? '',
            $data['certificate_name'] ?? '',
            $data['price'] ?? 0,
            $data['price_old'] ?? null,
            $data['currency'] ?? 'RUB',
            $data['featured_image'] ?? '',
            $data['certificate_image'] ?? '',
            $data['gallery_images'] ?? null,
            $data['short_description'] ?? '',
            $data['content'] ?? '',
            $data['advantages'] ?? null,
            $data['requirements'] ?? '',
            $data['documents_needed'] ?? '',
            $data['duration'] ?? '',
            $data['validity_period'] ?? '',
            $data['guarantee'] ?? '',
            $data['category'] ?? '',
            $data['subcategory'] ?? '',
            $data['tags'] ?? '',
            $data['is_active'] ?? 1,
            $data['is_featured'] ?? 0,
            $data['show_price'] ?? 1,
            $data['show_order_button'] ?? 1,
            $data['order_button_text'] ?? 'Заказать сертификат',
            $data['order_email'] ?? '',
            $data['order_phone'] ?? '',
            $data['consultation_available'] ?? 1,
            $data['sort_order'] ?? 0,
            $updatedBy,
            $id
        ];

        return $this->db->query($sql, $params);
    }

    /**
     * Удалить страницу
     */
    public function deletePage($id) 
    {
        return $this->db->query("DELETE FROM certification_pages WHERE id = ?", [$id]);
    }

    /**
     * Переключить статус активности
     */
    public function toggleActiveStatus($id) 
    {
        $updatedBy = 1;
        try {
            $currentAdmin = getCurrentAdmin();
            $updatedBy = $currentAdmin['id'] ?? 1;
        } catch (Exception $e) {
            // Игнорируем ошибку авторизации
        }
        
        return $this->db->query(
            "UPDATE certification_pages SET is_active = !is_active, updated_by = ? WHERE id = ?", 
            [$updatedBy, $id]
        );
    }

    /**
     * Увеличить счетчик просмотров - ИСПРАВЛЕНО
     */
    public function incrementViews($id) 
    {
        return $this->db->query(
            "UPDATE certification_pages SET views_count = views_count + 1 WHERE id = ?", 
            [$id]
        );
    }

    /**
     * Увеличить счетчик заказов - ИСПРАВЛЕНО
     */
    public function incrementOrders($id) 
    {
        $this->db->query(
            "UPDATE certification_pages SET orders_count = orders_count + 1 WHERE id = ?", 
            [$id]
        );
        
        // Пересчитываем конверсию
        $this->updateConversionRate($id);
    }

    /**
     * Обновить конверсию - ИСПРАВЛЕНО
     */
    private function updateConversionRate($id) 
    {
        $page = $this->getPageById($id);
        if ($page && $page['views_count'] > 0) {
            $conversionRate = ($page['orders_count'] / $page['views_count']) * 100;
            $this->db->query(
                "UPDATE certification_pages SET conversion_rate = ? WHERE id = ?", 
                [round($conversionRate, 2), $id]
            );
        }
    }

    /**
     * Получить активные страницы для публичного отображения (СТАРЫЙ МЕТОД)
     */
    public function getActivePagesOld($category = '', $featured_only = false, $limit = 0) 
    {
        $conditions = ['is_active = 1'];
        $params = [];

        if (!empty($category)) {
            $conditions[] = 'category = ?';
            $params[] = $category;
        }

        if ($featured_only) {
            $conditions[] = 'is_featured = 1';
        }

        $whereClause = implode(' AND ', $conditions);
        $limitClause = $limit > 0 ? "LIMIT {$limit}" : '';

        return $this->db->fetchAll(
            "SELECT id, title, slug, certificate_name, price, price_old, featured_image, 
                    short_description, category, is_featured, duration, validity_period
             FROM certification_pages 
             WHERE {$whereClause} 
             ORDER BY is_featured DESC, sort_order ASC, created_at DESC 
             {$limitClause}", 
            $params
        );
    }

    /**
     * Получить активные страницы с пагинацией и фильтрами (ДЛЯ КАТАЛОГА)
     */
    public function getActivePages($page = 1, $limit = 12, $category = '', $search = '') 
    {
        $offset = ($page - 1) * $limit;
        $conditions = ['is_active = 1'];
        $params = [];

        // Фильтр по категории
        if (!empty($category)) {
            $conditions[] = 'category = ?';
            $params[] = $category;
        }

        // Поиск
        if (!empty($search)) {
            $conditions[] = '(title LIKE ? OR certificate_name LIKE ? OR content LIKE ? OR tags LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT * FROM certification_pages 
                WHERE {$whereClause}
                ORDER BY sort_order ASC, is_featured DESC, created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Получить количество активных страниц для пагинации (ДЛЯ КАТАЛОГА)
     */
    public function getActivePagesCount($category = '', $search = '') 
    {
        $conditions = ['is_active = 1'];
        $params = [];

        // Фильтр по категории
        if (!empty($category)) {
            $conditions[] = 'category = ?';
            $params[] = $category;
        }

        // Поиск
        if (!empty($search)) {
            $conditions[] = '(title LIKE ? OR certificate_name LIKE ? OR content LIKE ? OR tags LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $conditions);
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM certification_pages WHERE {$whereClause}", $params);
        
        return $result['count'] ?? 0;
    }

    /**
     * Получить рекомендуемые (featured) страницы (ДЛЯ КАТАЛОГА)
     */
    public function getFeaturedPages($limit = 6) 
    {
        return $this->db->fetchAll(
            "SELECT * FROM certification_pages 
             WHERE is_active = 1 AND is_featured = 1 
             ORDER BY sort_order ASC, created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Получить уникальные категории
     */
    public function getCategories() 
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT category, COUNT(*) as count 
             FROM certification_pages 
             WHERE category IS NOT NULL AND category != '' AND is_active = 1
             GROUP BY category 
             ORDER BY count DESC, category ASC"
        );
    }

    /**
     * Проверить уникальность slug
     */
    public function isSlugUnique($slug, $excludeId = null) 
    {
        $sql = "SELECT COUNT(*) as count FROM certification_pages WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] == 0;
    }

    /**
     * Получить статистику
     */
    public function getStatistics() 
    {
        $stats = [];
        
        // Общее количество страниц
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM certification_pages");
        $stats['total_pages'] = $result['count'];
        
        // Активные страницы
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM certification_pages WHERE is_active = 1");
        $stats['active_pages'] = $result['count'];
        
        // Рекомендуемые страницы
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM certification_pages WHERE is_featured = 1");
        $stats['featured_pages'] = $result['count'];
        
        // Общие просмотры
        $result = $this->db->fetchOne("SELECT SUM(views_count) as total FROM certification_pages");
        $stats['total_views'] = $result['total'] ?? 0;
        
        // Общие заказы
        $result = $this->db->fetchOne("SELECT SUM(orders_count) as total FROM certification_pages");
        $stats['total_orders'] = $result['total'] ?? 0;
        
        // Средняя конверсия
        $result = $this->db->fetchOne("SELECT AVG(conversion_rate) as avg_rate FROM certification_pages WHERE views_count > 0");
        $stats['avg_conversion'] = round($result['avg_rate'] ?? 0, 2);
        
        return $stats;
    }

    /**
     * Переключить статус активности (АЛИАС ДЛЯ СОВМЕСТИМОСТИ)
     */
    public function toggleActive($id) 
    {
        return $this->toggleActiveStatus($id);
    }

    /**
     * Получить все активные страницы (простой метод без пагинации)
     */
    public function getAllActivePages() 
    {
        return $this->db->fetchAll(
            "SELECT * FROM certification_pages 
             WHERE is_active = 1 
             ORDER BY sort_order ASC, created_at DESC"
        );
    }
}