<?php
/**
 * Парсер новостей с сайта vnesenie-v-reestr.ru
 * Файл: parser.php
 */

require_once 'config.php';
require_once 'database.php';

/**
 * Получение HTML страницы
 */
function getPageContent($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]
    ]);
    
    $content = file_get_contents($url, false, $context);
    
    if ($content === false) {
        logMessage('ERROR', "Не удалось загрузить страницу: {$url}");
        return false;
    }
    
    return $content;
}

/**
 * Парсинг главной страницы новостей
 */
function parseNewsPage() {
    $newsUrl = SITE_URL . '/news/';
    $content = getPageContent($newsUrl);
    
    if (!$content) {
        return [];
    }
    
    $news = [];
    
    // Создаем DOMDocument для парсинга HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Подавляем ошибки HTML
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    
    // Ищем блоки новостей (адаптируем под структуру сайта)
    $newsBlocks = $xpath->query("//article[contains(@class, 'news-item')] | //div[contains(@class, 'news-item')] | //div[contains(@class, 'article')]");
    
    if ($newsBlocks->length == 0) {
        // Альтернативный поиск ссылок на новости
        $newsLinks = $xpath->query("//a[contains(@href, '/news/') and not(contains(@href, '/news/?'))]");
        
        foreach ($newsLinks as $link) {
            $href = $link->getAttribute('href');
            
            // Пропускаем служебные ссылки
            if (strpos($href, 'category=') !== false || strpos($href, '?') !== false) {
                continue;
            }
            
            // Получаем полный URL
            if (strpos($href, 'http') !== 0) {
                $href = SITE_URL . $href;
            }
            
            // Получаем заголовок
            $title = trim($link->textContent);
            if (empty($title)) continue;
            
            // Генерируем ID новости из URL
            $newsId = md5($href);
            
            $news[] = [
                'id' => $newsId,
                'title' => $title,
                'url' => $href,
                'excerpt' => '',
                'image' => '',
                'date' => date('Y-m-d H:i:s')
            ];
            
            // Ограничиваем количество новостей
            if (count($news) >= MAX_NEWS_PER_CHECK) {
                break;
            }
        }
    } else {
        // Парсим блоки новостей
        foreach ($newsBlocks as $block) {
            $newsItem = parseNewsBlock($block, $xpath);
            if ($newsItem) {
                $news[] = $newsItem;
                
                if (count($news) >= MAX_NEWS_PER_CHECK) {
                    break;
                }
            }
        }
    }
    
    logMessage('INFO', 'Найдено новостей на главной странице: ' . count($news));
    return $news;
}

/**
 * Парсинг отдельного блока новости
 */
function parseNewsBlock($block, $xpath) {
    // Ищем ссылку на статью
    $linkNodes = $xpath->query(".//a[contains(@href, '/news/')]", $block);
    if ($linkNodes->length == 0) {
        return null;
    }
    
    $link = $linkNodes->item(0);
    $href = $link->getAttribute('href');
    
    // Получаем полный URL
    if (strpos($href, 'http') !== 0) {
        $href = SITE_URL . $href;
    }
    
    // Получаем заголовок
    $title = '';
    $titleNodes = $xpath->query(".//h1 | .//h2 | .//h3 | .//h4", $block);
    if ($titleNodes->length > 0) {
        $title = trim($titleNodes->item(0)->textContent);
    } else {
        $title = trim($link->textContent);
    }
    
    if (empty($title)) {
        return null;
    }
    
    // Получаем анонс
    $excerpt = '';
    $excerptNodes = $xpath->query(".//p[not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3)]", $block);
    if ($excerptNodes->length > 0) {
        $excerpt = trim($excerptNodes->item(0)->textContent);
    }
    
    // Получаем изображение
    $image = '';
    $imgNodes = $xpath->query(".//img", $block);
    if ($imgNodes->length > 0) {
        $imgSrc = $imgNodes->item(0)->getAttribute('src');
        if (!empty($imgSrc)) {
            if (strpos($imgSrc, 'http') !== 0) {
                $image = SITE_URL . $imgSrc;
            } else {
                $image = $imgSrc;
            }
        }
    }
    
    // Генерируем ID новости
    $newsId = md5($href);
    
    return [
        'id' => $newsId,
        'title' => $title,
        'url' => $href,
        'excerpt' => $excerpt,
        'image' => $image,
        'date' => date('Y-m-d H:i:s')
    ];
}

/**
 * Парсинг отдельной страницы новости для получения полной информации
 */
function parseFullNews($newsUrl) {
    $content = getPageContent($newsUrl);
    
    if (!$content) {
        return null;
    }
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    
    // Получаем заголовок
    $title = '';
    $titleNodes = $xpath->query("//h1 | //title");
    if ($titleNodes->length > 0) {
        $title = trim($titleNodes->item(0)->textContent);
        // Очищаем заголовок от названия сайта
        $title = str_replace([' | vnesenie-v-reestr.ru', ' - vnesenie-v-reestr.ru'], '', $title);
    }
    
    // Получаем описание/анонс
    $excerpt = '';
    $metaDesc = $xpath->query("//meta[@name='description']");
    if ($metaDesc->length > 0) {
        $excerpt = $metaDesc->item(0)->getAttribute('content');
    }
    
    // Если нет meta description, берем первый абзац
    if (empty($excerpt)) {
        $paragraphs = $xpath->query("//div[contains(@class, 'content')]//p | //article//p | //main//p");
        if ($paragraphs->length > 0) {
            $excerpt = trim($paragraphs->item(0)->textContent);
        }
    }
    
    // Получаем изображение
    $image = '';
    
    // Ищем featured image
    $ogImage = $xpath->query("//meta[@property='og:image']");
    if ($ogImage->length > 0) {
        $image = $ogImage->item(0)->getAttribute('content');
    }
    
    // Если нет OG image, ищем первое изображение в контенте
    if (empty($image)) {
        $imgNodes = $xpath->query("//div[contains(@class, 'content')]//img | //article//img | //main//img");
        if ($imgNodes->length > 0) {
            $imgSrc = $imgNodes->item(0)->getAttribute('src');
            if (!empty($imgSrc)) {
                if (strpos($imgSrc, 'http') !== 0) {
                    $image = SITE_URL . $imgSrc;
                } else {
                    $image = $imgSrc;
                }
            }
        }
    }
    
    // Получаем дату публикации
    $date = date('Y-m-d H:i:s');
    $dateNodes = $xpath->query("//time | //span[contains(@class, 'date')] | //div[contains(@class, 'date')]");
    if ($dateNodes->length > 0) {
        $dateText = trim($dateNodes->item(0)->textContent);
        $parsedDate = strtotime($dateText);
        if ($parsedDate) {
            $date = date('Y-m-d H:i:s', $parsedDate);
        }
    }
    
    $newsId = md5($newsUrl);
    
    return [
        'id' => $newsId,
        'title' => $title,
        'url' => $newsUrl,
        'excerpt' => $excerpt,
        'image' => $image,
        'date' => $date
    ];
}

/**
 * Получение новостей через RSS (альтернативный метод)
 */
function parseRSSFeed() {
    $rssUrl = SITE_URL . '/rss.xml'; // Предполагаемый URL RSS
    
    $rssContent = getPageContent($rssUrl);
    if (!$rssContent) {
        logMessage('INFO', 'RSS лента не найдена или недоступна');
        return [];
    }
    
    $news = [];
    
    try {
        $xml = simplexml_load_string($rssContent);
        
        if ($xml === false) {
            logMessage('ERROR', 'Не удалось распарсить RSS');
            return [];
        }
        
        foreach ($xml->channel->item as $item) {
            $title = (string)$item->title;
            $link = (string)$item->link;
            $description = (string)$item->description;
            $pubDate = (string)$item->pubDate;
            
            $date = date('Y-m-d H:i:s');
            if (!empty($pubDate)) {
                $parsedDate = strtotime($pubDate);
                if ($parsedDate) {
                    $date = date('Y-m-d H:i:s', $parsedDate);
                }
            }
            
            $newsId = md5($link);
            
            $news[] = [
                'id' => $newsId,
                'title' => $title,
                'url' => $link,
                'excerpt' => strip_tags($description),
                'image' => '',
                'date' => $date
            ];
            
            if (count($news) >= MAX_NEWS_PER_CHECK) {
                break;
            }
        }
        
        logMessage('INFO', 'Получено новостей из RSS: ' . count($news));
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Ошибка парсинга RSS: ' . $e->getMessage());
    }
    
    return $news;
}

/**
 * Основная функция получения новостей
 */
function getLatestNews() {
    logMessage('INFO', 'Начинаем получение последних новостей');
    
    $news = [];
    
    // Сначала пробуем RSS
    $rssNews = parseRSSFeed();
    if (!empty($rssNews)) {
        $news = array_merge($news, $rssNews);
    }
    
    // Если RSS не дал результатов, парсим HTML
    if (empty($news)) {
        $htmlNews = parseNewsPage();
        $news = array_merge($news, $htmlNews);
    }
    
    // Убираем дубликаты по ID
    $uniqueNews = [];
    $seenIds = [];
    
    foreach ($news as $item) {
        if (!in_array($item['id'], $seenIds)) {
            $uniqueNews[] = $item;
            $seenIds[] = $item['id'];
        }
    }
    
    // Сортируем по дате (новые сначала)
    usort($uniqueNews, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // Ограничиваем количество
    $uniqueNews = array_slice($uniqueNews, 0, MAX_NEWS_PER_CHECK);
    
    logMessage('INFO', 'Итого найдено уникальных новостей: ' . count($uniqueNews));
    
    return $uniqueNews;
}

?>