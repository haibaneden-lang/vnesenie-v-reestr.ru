<?php
require_once __DIR__ . '/../config/database.php';

class News {
    private $db;

    public function __construct() {
        $this->db = getDatabase();
    }

    /**
     * Получить все опубликованные новости с пагинацией
     */
    public function getPublishedNews($page = 1, $limit = 10, $category_id = null) {
        $offset = ($page - 1) * $limit;
        
        $where = "WHERE n.is_published = 1";
        $params = [];
        
        if ($category_id) {
            $where .= " AND n.category_id = :category_id";
            $params['category_id'] = $category_id;
        }
        
        $sql = "SELECT n.*, c.name as category_name, c.slug as category_slug 
                FROM news n 
                LEFT JOIN news_categories c ON n.category_id = c.id 
                {$where}
                ORDER BY n.is_featured DESC, n.published_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

  
 
public function getAllPublishedNews() {
    $sql = "SELECT slug, published_at, updated_at 
            FROM news 
            WHERE is_published = 1 
            ORDER BY published_at DESC";
    
    return $this->db->fetchAll($sql);
}
   
    /**
     * Получить общее количество опубликованных новостей
     */
    public function getPublishedNewsCount($category_id = null) {
        $where = "WHERE is_published = 1";
        $params = [];
        
        if ($category_id) {
            $where .= " AND category_id = :category_id";
            $params['category_id'] = $category_id;
        }
        
        $sql = "SELECT COUNT(*) as count FROM news {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'];
    }

    /**
     * Получить новость по slug
     */
    public function getNewsBySlug($slug) {
        $sql = "SELECT n.*, c.name as category_name, c.slug as category_slug 
                FROM news n 
                LEFT JOIN news_categories c ON n.category_id = c.id 
                WHERE n.slug = :slug AND n.is_published = 1";
        
        $news = $this->db->fetchOne($sql, ['slug' => $slug]);
        
        // Увеличиваем счетчик просмотров
        if ($news) {
            $this->incrementViews($news['id']);
        }
        
        return $news;
    }

    /**
     * Получить новость по ID
     */
    public function getNewsById($id) {
        $sql = "SELECT n.*, c.name as category_name 
                FROM news n 
                LEFT JOIN news_categories c ON n.category_id = c.id 
                WHERE n.id = :id";
        
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Получить все новости для админки
     */
    public function getAllNews($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT n.*, c.name as category_name 
                FROM news n 
                LEFT JOIN news_categories c ON n.category_id = c.id 
                ORDER BY n.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Получить общее количество новостей
     */
    public function getAllNewsCount() {
        $sql = "SELECT COUNT(*) as count FROM news";
        $result = $this->db->fetchOne($sql);
        return $result['count'];
    }

    /**
     * Создать новую новость
     */
    public function createNews($data) {
        $sql = "INSERT INTO news (category_id, title, h1, slug, meta_description, excerpt, content, 
                featured_image, is_published, is_featured, published_at) 
                VALUES (:category_id, :title, :h1, :slug, :meta_description, :excerpt, :content, 
                :featured_image, :is_published, :is_featured, :published_at)";
        
        $params = [
            'category_id' => $data['category_id'] ?: null,
            'title' => $data['title'],
            'h1' => $data['h1'],
            'slug' => $this->generateSlug($data['slug'] ?: $data['title']),
            'meta_description' => $data['meta_description'] ?: null,
            'excerpt' => $data['excerpt'] ?: null,
            'content' => $data['content'],
            'featured_image' => $data['featured_image'] ?: null,
            'is_published' => $data['is_published'] ? 1 : 0,
            'is_featured' => $data['is_featured'] ? 1 : 0,
            'published_at' => $data['is_published'] ? ($data['published_at'] ?: date('Y-m-d H:i:s')) : null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Обновить новость
     */
    public function updateNews($id, $data) {
        $sql = "UPDATE news SET 
                category_id = :category_id,
                title = :title,
                h1 = :h1,
                slug = :slug,
                meta_description = :meta_description,
                excerpt = :excerpt,
                content = :content,
                featured_image = :featured_image,
                is_published = :is_published,
                is_featured = :is_featured,
                published_at = :published_at,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $params = [
            'id' => $id,
            'category_id' => $data['category_id'] ?: null,
            'title' => $data['title'],
            'h1' => $data['h1'],
            'slug' => $this->generateSlug($data['slug'] ?: $data['title'], $id),
            'meta_description' => $data['meta_description'] ?: null,
            'excerpt' => $data['excerpt'] ?: null,
            'content' => $data['content'],
            'featured_image' => $data['featured_image'] ?: null,
            'is_published' => $data['is_published'] ? 1 : 0,
            'is_featured' => $data['is_featured'] ? 1 : 0,
            'published_at' => $data['is_published'] ? ($data['published_at'] ?: date('Y-m-d H:i:s')) : null
        ];
        
        return $this->db->query($sql, $params);
    }

    /**
     * Удалить новость
     */
    public function deleteNews($id) {
        $sql = "DELETE FROM news WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Увеличить счетчик просмотров
     */
    public function incrementViews($id) {
        $sql = "UPDATE news SET views_count = views_count + 1 WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Получить похожие новости
     */
    public function getRelatedNews($category_id, $current_id, $limit = 5) {
        $sql = "SELECT id, title, slug, excerpt, published_at, featured_image
                FROM news 
                WHERE category_id = :category_id AND id != :current_id AND is_published = 1
                ORDER BY published_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'category_id' => $category_id,
            'current_id' => $current_id,
            'limit' => $limit
        ]);
    }

    /**
     * Поиск новостей
     */
    public function searchNews($query, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT n.*, c.name as category_name, c.slug as category_slug,
                MATCH(n.title, n.excerpt, n.content) AGAINST(:query) as relevance
                FROM news n 
                LEFT JOIN news_categories c ON n.category_id = c.id 
                WHERE n.is_published = 1 AND MATCH(n.title, n.excerpt, n.content) AGAINST(:query)
                ORDER BY relevance DESC, n.published_at DESC 
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Получить новости для админки с фильтрами
     */
    public function getAdminNews($page = 1, $limit = 15, $category_id = null, $status = '', $search = '') {
        $offset = ($page - 1) * $limit;
        
        $where_conditions = [];
        $params = [];
        
        // Фильтр по категории
        if ($category_id) {
            $where_conditions[] = "n.category_id = :category_id";
            $params['category_id'] = $category_id;
        }
        
        // Фильтр по статусу
        if ($status === 'published') {
            $where_conditions[] = "n.is_published = 1";
        } elseif ($status === 'draft') {
            $where_conditions[] = "n.is_published = 0";
        }
        
        // Поиск
        if ($search) {
            $where_conditions[] = "(n.title LIKE :search OR n.content LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT n.*, c.name as category_name 
                FROM news n 
                LEFT JOIN news_categories c ON n.category_id = c.id 
                {$where_clause}
                ORDER BY n.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Получить количество новостей для админки с фильтрами
     */
    public function getAdminNewsCount($category_id = null, $status = '', $search = '') {
        $where_conditions = [];
        $params = [];
        
        // Фильтр по категории
        if ($category_id) {
            $where_conditions[] = "category_id = :category_id";
            $params['category_id'] = $category_id;
        }
        
        // Фильтр по статусу
        if ($status === 'published') {
            $where_conditions[] = "is_published = 1";
        } elseif ($status === 'draft') {
            $where_conditions[] = "is_published = 0";
        }
        
        // Поиск
        if ($search) {
            $where_conditions[] = "(title LIKE :search OR content LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT COUNT(*) as count FROM news {$where_clause}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'];
    }

    /**
     * Переключить статус публикации
     */
    public function togglePublishStatus($id) {
        $sql = "UPDATE news SET 
                is_published = NOT is_published,
                published_at = CASE 
                    WHEN is_published = 0 AND published_at IS NULL 
                    THEN CURRENT_TIMESTAMP 
                    ELSE published_at 
                END,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Генерация уникального slug
     */
    private function generateSlug($text, $exclude_id = null) {
        // Транслитерация и очистка
        $slug = $this->transliterate($text);
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
        $slug = preg_replace('/\-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Проверка уникальности
        $original_slug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $exclude_id)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Проверка существования slug
     */
    private function slugExists($slug, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM news WHERE slug = :slug";
        $params = ['slug' => $slug];
        
        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $exclude_id;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Транслитерация
     */
    private function transliterate($text) {
        $transliteration = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];
        
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, $transliteration);
        $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
        $text = preg_replace('/[\s]+/', '-', $text);
        
        return $text;
    }
}

class NewsCategory {
    private $db;

    public function __construct() {
        $this->db = getDatabase();
    }

    /**
     * Получить все активные категории
     */
    public function getActiveCategories() {
        $sql = "SELECT * FROM news_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Получить все категории
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM news_categories ORDER BY sort_order ASC, name ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Получить категорию по slug
     */
    public function getCategoryBySlug($slug) {
        $sql = "SELECT * FROM news_categories WHERE slug = :slug AND is_active = 1";
        return $this->db->fetchOne($sql, ['slug' => $slug]);
    }

    /**
     * Получить категорию по ID
     */
    public function getCategoryById($id) {
        $sql = "SELECT * FROM news_categories WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
}
?>