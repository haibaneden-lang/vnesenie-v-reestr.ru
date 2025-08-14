<?php
/**
 * Расширенная RSS лента для Яндекс Дзен
 * Размещается в корне сайта как rss-zen-advanced.php
 * Доступ: https://vnesenie-v-reestr.ru/rss-zen-advanced.php
 */

// Подключаем модели и конфигурацию
require_once __DIR__ . '/models/News.php';

// Подключаем конфигурацию (создайте файл config/rss-zen-config.php)
$config_file = __DIR__ . '/config/rss-zen-config.php';
$config = file_exists($config_file) ? require($config_file) : [];

// Дефолтная конфигурация если файл не найден
$default_config = [
    'channel' => [
        'title' => 'Реестр Гарант - Новости',
        'link' => 'https://vnesenie-v-reestr.ru/',
        'description' => 'Актуальные новости о включении в реестр Минпромторга',
        'language' => 'ru'
    ],
    'publication' => [
        'type' => 'native',
        'max_items' => 50,
        'min_content_length' => 300,
        'cache_time' => 3600
    ],
    'images' => [
        'min_width' => 700,
        'default_width' => 1200,
        'default_height' => 630,
        'default_type' => 'image/jpeg'
    ],
    'allowed_html_tags' => ['p', 'a', 'img', 'figure', 'br', 'strong', 'em'],
    'zen_categories' => [],
    'filters' => ['max_days_old' => 90],
    'additional_elements' => ['add_author' => true, 'default_author' => 'Реестр Гарант']
];

// Объединяем конфигурации
$config = array_merge_recursive($default_config, $config);

// Устанавливаем заголовки
header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: max-age=' . $config['publication']['cache_time']);

/**
 * Класс для генерации RSS для Дзена
 */
class ZenRSSGenerator {
    private $config;
    private $newsModel;
    private $categoryModel;
    
    public function __construct($config) {
        $this->config = $config;
        $this->newsModel = new News();
        $this->categoryModel = new NewsCategory();
    }
    
    /**
     * Получение новостей с фильтрацией
     */
    public function getFilteredNews() {
        try {
            $max_items = $this->config['publication']['max_items'];
            $news = $this->newsModel->getPublishedNews(1, $max_items);
            
            // Применяем фильтры
            return $this->applyFilters($news);
            
        } catch (Exception $e) {
            error_log("Ошибка получения новостей для RSS Дзена: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Применение фильтров к новостям
     */
    private function applyFilters($news) {
        $filtered = [];
        $now = time();
        
        foreach ($news as $item) {
            // Фильтр по возрасту
            $published_time = strtotime($item['published_at']);
            $days_old = ($now - $published_time) / (24 * 3600);
            
            if (isset($this->config['filters']['max_days_old']) && 
                $days_old > $this->config['filters']['max_days_old']) {
                continue;
            }
            
            if (isset($this->config['filters']['min_days_old']) && 
                $days_old < $this->config['filters']['min_days_old']) {
                continue;
            }
            
            // Фильтр по категориям
            if (!empty($this->config['filters']['exclude_categories']) && 
                in_array($item['category_id'], $this->config['filters']['exclude_categories'])) {
                continue;
            }
            
            if (!empty($this->config['filters']['include_only_categories']) && 
                !in_array($item['category_id'], $this->config['filters']['include_only_categories'])) {
                continue;
            }
            
            $filtered[] = $item;
        }
        
        return $filtered;
    }
    
    /**
     * Очистка контента под требования Дзена
     */
    public function cleanContentForZen($content) {
        // Формируем строку разрешенных тегов
        $allowed_tags = '<' . implode('><', $this->config['allowed_html_tags']) . '>';
        
        // Очищаем HTML
        $content = strip_tags($content, $allowed_tags);
        
        // Обрабатываем изображения
        $content = $this->processImages($content);
        
        // Убираем пустые параграфы и лишние пробелы
        $content = preg_replace('/<p>[\s\&nbsp;]*<\/p>/i', '', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Проверяем минимальную длину
        if (mb_strlen(strip_tags($content)) < $this->config['publication']['min_content_length']) {
            $content .= '<p>Полная версия статьи доступна на нашем сайте.</p>';
        }
        
        return trim($content);
    }
    
    /**
     * Обработка изображений в контенте
     */
    private function processImages($content) {
        // Заворачиваем img в figure если не завернуты
        $content = preg_replace_callback('/<img([^>]*?)>/i', function($matches) {
            $img_tag = $matches[0];
            
            // Проверяем, не находится ли уже в figure
            if (strpos($content, '<figure>' . $img_tag) === false && 
                strpos($content, '<figure ') === false) {
                return '<figure>' . $img_tag . '</figure>';
            }
            
            return $img_tag;
        }, $content);
        
        // Преобразуем относительные URL в абсолютные
        $content = preg_replace_callback('/src=["\']([^"\']+)["\']/i', function($matches) {
            $url = $matches[1];
            if (strpos($url, 'http') !== 0) {
                if (strpos($url, '/') === 0) {
                    $url = 'https://vnesenie-v-reestr.ru' . $url;
                } else {
                    $url = 'https://vnesenie-v-reestr.ru/' . $url;
                }
            }
            return 'src="' . $url . '"';
        }, $content);
        
        return $content;
    }
    
    /**
     * Извлечение изображений из контента
     */
    public function extractMainImage($item) {
        // Приоритет: featured_image -> первое изображение из контента
        if ($item['featured_image']) {
            return 'https://vnesenie-v-reestr.ru' . $item['featured_image'];
        }
        
        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $item['content'], $matches);
        if (!empty($matches[1])) {
            $url = $matches[1];
            if (strpos($url, 'http') !== 0) {
                if (strpos($url, '/') === 0) {
                    $url = 'https://vnesenie-v-reestr.ru' . $url;
                } else {
                    $url = 'https://vnesenie-v-reestr.ru/' . $url;
                }
            }
            return $url;
        }
        
        return null;
    }
    
    /**
     * Получение категории для Дзена
     */
    public function getZenCategory($category_id) {
        if (isset($this->config['zen_categories'][$category_id])) {
            return $this->config['zen_categories'][$category_id];
        }
        
        // Попытаемся получить оригинальное название категории
        try {
            $category = $this->categoryModel->getCategoryById($category_id);
            return $category ? $category['name'] : '';
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * Форматирование ссылки с UTM метками
     */
    public function formatLink($slug) {
        $link = 'https://vnesenie-v-reestr.ru/news/' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');
        
        if ($this->config['seo']['add_utm_tags']) {
            $utm_params = [
                'utm_source' => $this->config['seo']['utm_source'],
                'utm_medium' => $this->config['seo']['utm_medium'],
                'utm_campaign' => $this->config['seo']['utm_campaign']
            ];
            $link .= '?' . http_build_query($utm_params);
        }
        
        return $link;
    }
    
    /**
     * Генерация XML
     */
    public function generateXML() {
        $news = $this->getFilteredNews();
        $hasEnoughNews = count($news) >= 10;
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        ?>
        <rss version="2.0" 
             xmlns:content="http://purl.org/rss/1.0/modules/content/" 
             xmlns:dc="http://purl.org/dc/elements/1.1/" 
             xmlns:media="http://search.yahoo.com/mrss/"
             xmlns:atom="http://www.w3.org/2005/Atom">
             
            <channel>
                <title><![CDATA[<?php echo $this->config['channel']['title']; ?>]]></title>
                <link><?php echo $this->config['channel']['link']; ?></link>
                <description><![CDATA[<?php echo $this->config['channel']['description']; ?>]]></description>
                <language><?php echo $this->config['channel']['language']; ?></language>
                <pubDate><?php echo date('r'); ?></pubDate>
                <lastBuildDate><?php echo date('r'); ?></lastBuildDate>
                
                <?php if (isset($this->config['channel']['generator'])): ?>
                <generator><?php echo $this->config['channel']['generator']; ?></generator>
                <?php endif; ?>
                
                <?php if (isset($this->config['channel']['webmaster'])): ?>
                <webMaster><?php echo $this->config['channel']['webmaster']; ?></webMaster>
                <?php endif; ?>
                
                <atom:link href="https://vnesenie-v-reestr.ru/rss-zen-advanced.php" rel="self" type="application/rss+xml"/>
                
                <?php if (!$hasEnoughNews): ?>
                <!-- ВНИМАНИЕ: Лента содержит менее 10 новостей. Дзен требует минимум 10 материалов -->
                <?php endif; ?>
                
                <?php if (!empty($news)): ?>
                    <?php foreach ($news as $item): ?>
                        <?php $this->renderNewsItem($item); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php $this->renderFallbackItem(); ?>
                <?php endif; ?>
                
            </channel>
        </rss>
        <?php
    }
    
    /**
     * Рендеринг элемента новости
     */
    private function renderNewsItem($item) {
        $title = htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8');
        $link = $this->formatLink($item['slug']);
        $description = htmlspecialchars($item['excerpt'] ?: mb_substr(strip_tags($item['content']), 0, 200), ENT_QUOTES, 'UTF-8');
        $pubDate = date('r', strtotime($item['published_at']));
        $guid = $link;
        $content = $this->cleanContentForZen($item['content']);
        $mainImage = $this->extractMainImage($item);
        $category = $this->getZenCategory($item['category_id']);
        ?>
        
        <item>
            <title><![CDATA[<?php echo $title; ?>]]></title>
            <link><?php echo $link; ?></link>
            <description><![CDATA[<?php echo $description; ?>]]></description>
            <pubDate><?php echo $pubDate; ?></pubDate>
            <guid isPermaLink="true"><?php echo $guid; ?></guid>
            
            <?php if ($category): ?>
            <category><![CDATA[<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>]]></category>
            <?php endif; ?>
            
            <?php if ($mainImage): ?>
            <enclosure url="<?php echo htmlspecialchars($mainImage, ENT_QUOTES, 'UTF-8'); ?>" 
                       length="<?php echo $this->config['images']['default_width'] * $this->config['images']['default_height']; ?>" 
                       type="<?php echo $this->config['images']['default_type']; ?>"/>
            <?php endif; ?>
            
            <content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
            
            <?php if ($this->config['additional_elements']['add_author']): ?>
            <dc:creator><![CDATA[<?php echo $this->config['additional_elements']['default_author']; ?>]]></dc:creator>
            <?php endif; ?>
            
            <category domain="<?php echo $this->config['channel']['link']; ?>"><![CDATA[<?php echo $this->config['publication']['type']; ?>]]></category>
            
        </item>
        
        <?php
    }
    
    /**
     * Рендеринг заглушки
     */
    private function renderFallbackItem() {
        $fallback = $this->config['fallback_item'];
        ?>
        <item>
            <title><![CDATA[<?php echo $fallback['title']; ?>]]></title>
            <link><?php echo $this->config['channel']['link']; ?></link>
            <description><![CDATA[<?php echo $fallback['description']; ?>]]></description>
            <pubDate><?php echo date('r'); ?></pubDate>
            <guid isPermaLink="true"><?php echo $this->config['channel']['link']; ?></guid>
            <content:encoded><![CDATA[<?php echo $fallback['content']; ?>]]></content:encoded>
            <category domain="<?php echo $this->config['channel']['link']; ?>"><![CDATA[<?php echo $this->config['publication']['type']; ?>]]></category>
        </item>
        <?php
    }
}

// Генерируем RSS
try {
    $generator = new ZenRSSGenerator($config);
    $generator->generateXML();
} catch (Exception $e) {
    error_log("Критическая ошибка RSS для Дзена: " . $e->getMessage());
    
    // Возвращаем минимальную валидную RSS ленту в случае ошибки
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<rss version="2.0"><channel>';
    echo '<title>Реестр Гарант - Ошибка</title>';
    echo '<link>https://vnesenie-v-reestr.ru/</link>';
    echo '<description>Временная ошибка RSS ленты</description>';
    echo '<item><title>Сайт временно недоступен</title><link>https://vnesenie-v-reestr.ru/</link><description>Попробуйте позже</description></item>';
    echo '</channel></rss>';
}
?>