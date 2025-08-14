<?php
/**
 * RSS лента для Яндекс Дзен - по официальному образцу Яндекса
 * Размещается в корне сайта как rss-zen.php
 * Доступ: https://vnesenie-v-reestr.ru/rss-zen.php
 */

// Отключаем вывод ошибок в продакшене
ob_start();

try {
    // Подключаем модели
    require_once __DIR__ . '/models/News.php';
    
    // Устанавливаем правильный Content-Type для RSS
    header('Content-Type: application/rss+xml; charset=utf-8');
    header('Cache-Control: max-age=3600');
    
    // Инициализация моделей
    $newsModel = new News();
    
    // Проверяем, существует ли класс NewsCategory
    $categoryModel = null;
    if (class_exists('NewsCategory')) {
        try {
            $categoryModel = new NewsCategory();
        } catch (Exception $e) {
            // Игнорируем ошибки создания категорий
        }
    }
    
    // Получаем новости (минимум 15 для Dzen)
    $news = [];
    try {
        $news = $newsModel->getPublishedNews(1, 60);
    } catch (Exception $e) {
        error_log("Ошибка получения новостей в RSS: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    // Критическая ошибка - возвращаем минимальную RSS ленту
    error_log("Критическая ошибка в RSS: " . $e->getMessage());
    
    ob_clean();
    header('Content-Type: application/rss+xml; charset=utf-8');
    
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<rss version="2.0"><channel>';
    echo '<title>Реестр Гарант - Временная ошибка</title>';
    echo '<link>https://vnesenie-v-reestr.ru/</link>';
    echo '<language>ru</language>';
    echo '<item>';
    echo '<title>Сайт временно недоступен</title>';
    echo '<link>https://vnesenie-v-reestr.ru/</link>';
    echo '<guid>error-' . date('YmdHis') . '</guid>';
    echo '<pubDate>' . date('r') . '</pubDate>';
    echo '<category>native-draft</category>';
    echo '<content:encoded><![CDATA[<p>Попробуйте позже</p>]]></content:encoded>';
    echo '</item>';
    echo '</channel></rss>';
    exit;
}

/**
 * Создание описания (не используется в примере Яндекса, но может быть полезно)
 */
function createDescription($item) {
    if (empty($item)) {
        return '';
    }
    
    try {
        $text = '';
        
        if (!empty($item['excerpt'])) {
            $text = $item['excerpt'];
        } elseif (!empty($item['content'])) {
            $text = $item['content'];
        }
        
        // Убираем HTML
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (mb_strlen($text) > 200) {
            $text = mb_substr($text, 0, 197) . '...';
        }
        
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Подготовка контента строго по образцу Яндекса
 */
function prepareZenContent($content, $featuredImage = null) {
    if (empty($content) || !is_string($content)) {
        $content = '<p>Полный текст статьи доступен на сайте vnesenie-v-reestr.ru</p>';
    }
    
    try {
        // Удаление CSS блоков
        $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);
        
        // Удаление CSS атрибутов (но сохраняем class="copyright" для подписей)
        $content = preg_replace('/\s+style\s*=\s*["\'][^"\']*["\']/', '', $content);
        $content = preg_replace('/\s+class\s*=\s*["\'][^"\']*["\'](?![^>]*copyright)/', '', $content);
        $content = preg_replace('/\s+data-[^=]*\s*=\s*["\'][^"\']*["\']/', '', $content);
        
        // Разрешенные теги по образцу Яндекса
        $allowed_tags = '<p><a><figure><img><figcaption><span><ul><ol><li><i><b><u><s><strong><em><h2><h3><h4><h5><h6><br><blockquote>';
        $content = strip_tags($content, $allowed_tags);
        
        // Исправление ссылок на абсолютные
        $content = preg_replace('/href\s*=\s*["\']\/(?!\/)/i', 'href="https://vnesenie-v-reestr.ru/', $content);
        $content = preg_replace('/src\s*=\s*["\']\/(?!\/)/i', 'src="https://vnesenie-v-reestr.ru/', $content);
        
        // Если есть featured image и нет изображений в контенте - добавляем
        if ($featuredImage && !preg_match('/<img/i', $content)) {
            $imageTag = '<figure><img src="' . htmlspecialchars($featuredImage, ENT_QUOTES, 'UTF-8') . '"><figcaption>Иллюстрация к статье</figcaption></figure>';
            $content = '<p>Включение промышленной продукции в реестр Минпромторга - важная процедура для российских производителей.</p>' . $imageTag . $content;
        }
        
        // Убираем пустые теги
        $content = preg_replace('/<(?!img|br)([^>]+)>\s*<\/\1>/', '', $content);
        
        // Очистка пробелов
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        // Минимальная длина контента
        if (mb_strlen(strip_tags($content)) < 300) {
            $content .= '<p>Получите профессиональную консультацию по включению продукции в реестр Минпромторга РФ. Наши специалисты помогут пройти все этапы процедуры.</p>';
        }
        
        return $content;
        
    } catch (Exception $e) {
        return '<p>Статья о включении продукции в реестр Минпромторга РФ.</p>';
    }
}

/**
 * Получение первого изображения для enclosure (по образцу Яндекса)
 */
function getFirstImage($item) {
    try {
        // Приоритет: featured_image
        if (!empty($item['featured_image'])) {
            $imageUrl = 'https://vnesenie-v-reestr.ru' . $item['featured_image'];
            if (isSupportedImageFormat($imageUrl)) {
                return $imageUrl;
            }
        }
        
        // Из контента - первое изображение
        if (!empty($item['content'])) {
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $item['content'], $matches)) {
                $url = $matches[1];
                if (strpos($url, 'http') !== 0) {
                    $url = (strpos($url, '/') === 0) 
                        ? 'https://vnesenie-v-reestr.ru' . $url
                        : 'https://vnesenie-v-reestr.ru/' . $url;
                }
                
                if (isSupportedImageFormat($url)) {
                    return $url;
                }
            }
        }
        
        return null;
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Проверка поддерживаемых форматов (БЕЗ WebP!)
 */
function isSupportedImageFormat($imageUrl) {
    $ext = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']); // WebP исключен!
}

/**
 * Определение MIME типа (без WebP)
 */
function getImageMimeType($imageUrl) {
    $ext = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
    
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return 'image/jpeg';
        case 'png':
            return 'image/png';
        case 'gif':
            return 'image/gif';
        default:
            return 'image/jpeg'; // По умолчанию
    }
}

/**
 * Генерация GUID по образцу Яндекса
 */
function generateGuid($item) {
    $string = ($item['slug'] ?? 'news') . '-' . ($item['id'] ?? time());
    return md5($string);
}

// Очищаем буфер и генерируем XML
ob_clean();

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss">
<channel>
    <title>Реестр Гарант</title>
    <link>https://vnesenie-v-reestr.ru/</link>
    <language>ru</language>
    
    <?php if (!empty($news)): ?>
        <?php foreach ($news as $index => $item): ?>
            <?php
            try {
                $title = isset($item['title']) ? htmlspecialchars(trim($item['title']), ENT_QUOTES, 'UTF-8') : 'Новость о реестре Минпromторга';
                $slug = isset($item['slug']) ? htmlspecialchars(trim($item['slug']), ENT_QUOTES, 'UTF-8') : 'news-' . $index;
                $link = 'https://vnesenie-v-reestr.ru/news/' . $slug;
                $pdalink = 'https://vnesenie-v-reestr.ru/news/' . $slug; // мобильная версия (может быть такой же)
                
                $guid = generateGuid($item);
                
                // Дата в правильном формате
                $pub_date = isset($item['published_at']) ? $item['published_at'] : (isset($item['created_at']) ? $item['created_at'] : date('Y-m-d H:i:s'));
                $pubDate = date('r', strtotime($pub_date));
                
                // Первое изображение для enclosure
                $firstImage = getFirstImage($item);
                
                // Контент
                $content = prepareZenContent($item['content'] ?? '', $firstImage);
                
            } catch (Exception $e) {
                error_log("Ошибка обработки новости {$index}: " . $e->getMessage());
                continue;
            }
            ?>
            
            <item>
                <title><?php echo $title; ?></title>
                <link><?php echo $link; ?></link>
                <pdalink><?php echo $pdalink; ?></pdalink>
                <guid><?php echo $guid; ?></guid>
                <pubDate><?php echo $pubDate; ?></pubDate>
                <media:rating scheme="urn:simple">nonadult</media:rating>
                <category>native-draft</category>
                
                <?php if ($firstImage): ?>
                <enclosure url="<?php echo htmlspecialchars($firstImage, ENT_QUOTES, 'UTF-8'); ?>" type="<?php echo getImageMimeType($firstImage); ?>"/>
                <?php endif; ?>
                
                <content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
            </item>
            
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Заглушки если нет новостей -->
        <?php 
        $default_articles = [
            [
                'title' => 'Включение продукции в реестр Минпромторга - полное руководство',
                'slug' => 'vklyuchenie-produktsii-v-reestr-minpromtorga',
                'content' => '<p>Включение промышленной продукции в реестр Минпромторга - важная процедура для российских производителей.</p><figure><img src="https://vnesenie-v-reestr.ru/images/reestr-guide.jpg"><figcaption>Процедура включения в реестр Минпромторга</figcaption></figure><p>Процедура включения требует тщательной подготовки документов и соблюдения всех требований действующего законодательства.</p><p>Основные этапы процедуры:</p><ul><li><b>Подготовка документов</b>;</li><li><i>Подача заявления</i>;</li><li><u>Рассмотрение заявки</u>;</li><li>Получение решения.</li></ul><p>Наши специалисты помогут пройти все этапы процедуры и получить положительное решение о включении продукции в реестр.</p>'
            ],
            [
                'title' => 'Требования к документам для включения в реестр',
                'slug' => 'trebovaniya-k-dokumentam-dlya-vklyucheniya',
                'content' => '<p>Для успешного включения продукции в реестр необходимо подготовить полный пакет документов.</p><figure><img src="https://vnesenie-v-reestr.ru/images/documents.jpg"><figcaption>Документы для включения в реестр <span class="copyright">Реестр Гарант</span></figcaption></figure><p>Требования к документам строго регламентированы <a href="https://vnesenie-v-reestr.ru/">законодательством РФ</a> и требуют профессионального подхода к их подготовке.</p><p>Получите консультацию наших экспертов для правильной подготовки всех необходимых документов.</p>'
            ]
        ];
        
        foreach ($default_articles as $index => $article): ?>
            <item>
                <title><?php echo $article['title']; ?></title>
                <link>https://vnesenie-v-reestr.ru/news/<?php echo $article['slug']; ?></link>
                <pdalink>https://vnesenie-v-reestr.ru/news/<?php echo $article['slug']; ?></pdalink>
                <guid><?php echo md5($article['slug']); ?></guid>
                <pubDate><?php echo date('r', strtotime('-' . $index . ' hours')); ?></pubDate>
                <media:rating scheme="urn:simple">nonadult</media:rating>
                <category>native-draft</category>
                <enclosure url="https://vnesenie-v-reestr.ru/images/<?php echo $index == 0 ? 'reestr-guide.jpg' : 'documents.jpg'; ?>" type="image/jpeg"/>
                <content:encoded><![CDATA[<?php echo $article['content']; ?>]]></content:encoded>
            </item>
        <?php endforeach; ?>
    <?php endif; ?>
    
</channel>
</rss>