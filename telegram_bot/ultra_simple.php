<?php
/**
 * Улучшенный парсер с пагинацией и красивыми постами
 * Файл: enhanced_parser.php
 */

set_time_limit(20);
ini_set('memory_limit', '64M');

// Настройки
$BOT_TOKEN = '7739849524:AAFpk9zQZ27LV_sw-NQt1D1vlUDlJhHLdCs';
$CHAT_ID = '-1002836639801';
$SITE_URL = 'https://vnesenie-v-reestr.ru';

$action = $_GET['action'] ?? 'menu';
$page = $_GET['page'] ?? 1;

// Функция получения страницы
function getPageContent($url, $timeout = 10) {
    if (!extension_loaded('curl')) {
        return false;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; NewsBot/1.0)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($content && $httpCode == 200) ? $content : false;
}

// Функция получения описания статьи
function getArticleDescription($articleUrl) {
    $content = getPageContent($articleUrl, 8);
    if (!$content) {
        return '';
    }
    
    $description = '';
    
    // Способ 1: meta description
    if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
        $description = trim(strip_tags($matches[1]));
    }
    
    // Способ 2: первый абзац статьи
    if (empty($description)) {
        if (preg_match('/<p[^>]*>([^<]+(?:<[^>]*>[^<]*<\/[^>]*>[^<]*)*)<\/p>/i', $content, $matches)) {
            $description = trim(strip_tags($matches[1]));
        }
    }
    
    // Способ 3: любой текст в div с классом content, article, post
    if (empty($description)) {
        if (preg_match('/<div[^>]*class=["\'][^"\']*(?:content|article|post)[^"\']*["\'][^>]*>(.*?)<\/div>/si', $content, $matches)) {
            $text = strip_tags($matches[1]);
            $sentences = explode('.', $text);
            if (count($sentences) > 0) {
                $description = trim($sentences[0]) . '.';
            }
        }
    }
    
    // Обрезаем до разумной длины
    if (strlen($description) > 300) {
        $description = substr($description, 0, 300) . '...';
    }
    
    return $description;
}

// Функция поиска новостей с поддержкой пагинации
function findNewsWithPagination($maxPages = 3) {
    global $SITE_URL;
    
    $allNews = [];
    
    for ($page = 1; $page <= $maxPages; $page++) {
        // Разные варианты URL пагинации
        $urls = [
            "{$SITE_URL}/news/?page={$page}",
            "{$SITE_URL}/news/page/{$page}/",
            "{$SITE_URL}/news/?p={$page}",
            "{$SITE_URL}/news/{$page}/",
        ];
        
        if ($page == 1) {
            array_unshift($urls, "{$SITE_URL}/news/");
        }
        
        $pageContent = false;
        $workingUrl = '';
        
        // Пробуем разные URL до первого рабочего
        foreach ($urls as $url) {
            $pageContent = getPageContent($url);
            if ($pageContent) {
                $workingUrl = $url;
                break;
            }
        }
        
        if (!$pageContent) {
            if ($page == 1) {
                echo "❌ Не удалось получить первую страницу новостей<br>";
                return [];
            } else {
                echo "ℹ️ Страница {$page} недоступна, останавливаемся<br>";
                break;
            }
        }
        
        echo "✅ Страница {$page}: {$workingUrl}<br>";
        
        // Парсим новости на странице
        $pageNews = parseNewsFromContent($pageContent);
        
        echo "📰 Найдено на странице {$page}: " . count($pageNews) . " новостей<br>";
        
        if (empty($pageNews) && $page > 1) {
            echo "ℹ️ На странице {$page} новостей не найдено, останавливаемся<br>";
            break;
        }
        
        $allNews = array_merge($allNews, $pageNews);
        
        // Небольшая пауза между запросами
        if ($page < $maxPages) {
            sleep(1);
        }
    }
    
    // Убираем дубликаты
    $uniqueNews = [];
    $seenUrls = [];
    
    foreach ($allNews as $news) {
        if (!in_array($news['url'], $seenUrls)) {
            $uniqueNews[] = $news;
            $seenUrls[] = $news['url'];
        }
    }
    
    return $uniqueNews;
}

// Функция парсинга новостей из контента
function parseNewsFromContent($content) {
    global $SITE_URL;
    
    $foundNews = [];
    
    // Способ 1: article.php?slug=
    preg_match_all('/<a[^>]+href="([^"]*article\.php\?slug=[^"]*)"[^>]*>([^<]+)<\/a>/i', $content, $matches1);
    
    for ($i = 0; $i < count($matches1[1]); $i++) {
        $url = $matches1[1][$i];
        $title = trim(strip_tags($matches1[2][$i]));
        
        if (strlen($title) > 10) {
            if (strpos($url, 'http') !== 0) {
                $url = $SITE_URL . $url;
            }
            
            $foundNews[] = [
                'title' => $title,
                'url' => $url,
                'type' => 'article.php'
            ];
        }
    }
    
    // Способ 2: прямые ссылки /news/название/
    preg_match_all('/<a[^>]+href="([^"]*\/news\/[^"\/?]+[^"]*)"[^>]*>([^<]+)<\/a>/i', $content, $matches2);
    
    for ($i = 0; $i < count($matches2[1]); $i++) {
        $url = $matches2[1][$i];
        $title = trim(strip_tags($matches2[2][$i]));
        
        if (strlen($title) > 10 && 
            $url !== '/news/' && 
            $url !== '/news' &&
            strpos($url, '?') === false &&
            strpos($url, 'category') === false) {
            
            if (strpos($url, 'http') !== 0) {
                $url = $SITE_URL . $url;
            }
            
            $foundNews[] = [
                'title' => $title,
                'url' => $url,
                'type' => 'direct'
            ];
        }
    }
    
    // Способ 3: заголовки с ссылками
    preg_match_all('/<h[1-6][^>]*>.*?<a[^>]+href="([^"]*\/news\/[^"]*)"[^>]*>([^<]+)<\/a>.*?<\/h[1-6]>/i', $content, $matches3);
    
    for ($i = 0; $i < count($matches3[1]); $i++) {
        $url = $matches3[1][$i];
        $title = trim(strip_tags($matches3[2][$i]));
        
        if (strlen($title) > 10 && strpos($url, '?') === false) {
            if (strpos($url, 'http') !== 0) {
                $url = $SITE_URL . $url;
            }
            
            $foundNews[] = [
                'title' => $title,
                'url' => $url,
                'type' => 'header'
            ];
        }
    }
    
    return $foundNews;
}

// Функция отправки в Telegram
function sendToTelegram($message) {
    global $BOT_TOKEN, $CHAT_ID;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'chat_id' => $CHAT_ID,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result) {
        $response = json_decode($result, true);
        if (isset($response['ok']) && $response['ok']) {
            return $response['result']['message_id'];
        }
    }
    
    return false;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Улучшенный парсер новостей</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; background: #007bff; color: white; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-small { font-size: 12px; padding: 5px 10px; }
        .result { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 14px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .news-item { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; background: #fff; }
        .news-title { font-weight: bold; color: #333; font-size: 16px; margin-bottom: 8px; }
        .news-description { color: #666; font-size: 14px; margin-bottom: 8px; line-height: 1.4; }
        .news-url { font-size: 11px; color: #999; word-break: break-all; margin-bottom: 8px; }
        .news-type { font-size: 10px; color: #999; background: #f1f1f1; padding: 2px 6px; border-radius: 3px; display: inline-block; }
        .pagination { text-align: center; margin: 20px 0; }
        .pagination a { margin: 0 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📰 Улучшенный парсер новостей</h1>
        
        <?php if ($action == 'menu'): ?>
            <p>Выберите действие:</p>
            <a href="?action=quick_search" class="btn">🔍 Быстрый поиск (1 страница)</a>
            <a href="?action=full_search" class="btn btn-warning">📚 Полный поиск (3 страницы)</a>
            <a href="?action=test_description" class="btn btn-success">🧪 Тест описаний</a>
            
        <?php elseif ($action == 'quick_search'): ?>
            <h3>🔍 Быстрый поиск новостей</h3>
            <div class="result">
                <?php
                echo "Поиск на первой странице {$SITE_URL}/news/<br><br>";
                
                $news = findNewsWithPagination(1);
                
                echo "<br><strong>Найдено уникальных новостей: " . count($news) . "</strong><br><br>";
                
                foreach (array_slice($news, 0, 10) as $index => $item) {
                    echo "<div class='news-item'>";
                    echo "<div class='news-title'>📰 " . htmlspecialchars($item['title']) . "</div>";
                    echo "<div class='news-url'>🔗 " . htmlspecialchars($item['url']) . "</div>";
                    echo "<span class='news-type'>" . $item['type'] . "</span>";
                    echo "<br><br>";
                    echo "<a href='?action=publish&url=" . urlencode($item['url']) . "&title=" . urlencode($item['title']) . "' class='btn btn-small'>📤 Опубликовать</a>";
                    echo "<a href='?action=preview&url=" . urlencode($item['url']) . "&title=" . urlencode($item['title']) . "' class='btn btn-small' style='background: #17a2b8;'>👁️ Предпросмотр</a>";
                    echo "</div>";
                }
                ?>
            </div>
            <a href="?" class="btn">← Назад</a>
            
        <?php elseif ($action == 'full_search'): ?>
            <h3>📚 Полный поиск новостей (3 страницы)</h3>
            <div class="result">
                <?php
                echo "Поиск на нескольких страницах {$SITE_URL}/news/<br><br>";
                
                $news = findNewsWithPagination(3);
                
                echo "<br><strong>🎉 Всего найдено уникальных новостей: " . count($news) . "</strong><br><br>";
                
                $perPage = 20;
                $currentPage = (int)$page;
                $totalPages = ceil(count($news) / $perPage);
                $offset = ($currentPage - 1) * $perPage;
                $newsToShow = array_slice($news, $offset, $perPage);
                
                echo "<div class='pagination'>";
                if ($currentPage > 1) {
                    echo "<a href='?action=full_search&page=" . ($currentPage - 1) . "' class='btn btn-small'>← Предыдущая</a>";
                }
                echo "<span>Страница {$currentPage} из {$totalPages}</span>";
                if ($currentPage < $totalPages) {
                    echo "<a href='?action=full_search&page=" . ($currentPage + 1) . "' class='btn btn-small'>Следующая →</a>";
                }
                echo "</div>";
                
                foreach ($newsToShow as $index => $item) {
                    $globalIndex = $offset + $index + 1;
                    echo "<div class='news-item'>";
                    echo "<div class='news-title'>📰 {$globalIndex}. " . htmlspecialchars($item['title']) . "</div>";
                    echo "<div class='news-url'>🔗 " . htmlspecialchars($item['url']) . "</div>";
                    echo "<span class='news-type'>" . $item['type'] . "</span>";
                    echo "<br><br>";
                    echo "<a href='?action=publish&url=" . urlencode($item['url']) . "&title=" . urlencode($item['title']) . "' class='btn btn-small'>📤 Опубликовать</a>";
                    echo "<a href='?action=preview&url=" . urlencode($item['url']) . "&title=" . urlencode($item['title']) . "' class='btn btn-small' style='background: #17a2b8;'>👁️ Предпросмотр</a>";
                    echo "</div>";
                }
                ?>
            </div>
            <a href="?" class="btn">← Назад</a>
            
        <?php elseif ($action == 'preview'): ?>
            <h3>👁️ Предпросмотр публикации</h3>
            <div class="result">
                <?php
                $title = $_GET['title'] ?? '';
                $url = $_GET['url'] ?? '';
                
                if (empty($title) || empty($url)) {
                    echo "<span class='error'>❌ Нет данных для предпросмотра</span>";
                } else {
                    echo "📰 <strong>" . htmlspecialchars($title) . "</strong><br>";
                    echo "🔗 " . htmlspecialchars($url) . "<br><br>";
                    
                    echo "🔍 Получаем описание статьи...<br>";
                    $description = getArticleDescription($url);
                    
                    if ($description) {
                        echo "✅ Описание получено: " . strlen($description) . " символов<br><br>";
                        echo "<strong>📝 Описание:</strong><br>";
                        echo "<div style='background: #fff; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0;'>";
                        echo htmlspecialchars($description);
                        echo "</div>";
                    } else {
                        echo "⚠️ Описание не найдено, будет использован только заголовок<br><br>";
                    }
                    
                    // Формирование итогового сообщения
                    $message = "🔔 *Новая статья на vnesenie-v-reestr.ru*\n\n";
                    $message .= "📝 *" . str_replace(['_', '*', '[', ']', '(', ')'], ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)'], $title) . "*\n\n";
                    
                    if ($description) {
                        $cleanDescription = str_replace(['_', '*', '[', ']', '(', ')'], ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)'], $description);
                        $message .= "📄 " . $cleanDescription . "\n\n";
                    }
                    
                    $message .= "👆 [Читать полностью](" . $url . ")\n\n";
                    $message .= "#реестр #минпромторг #новости";
                    
                    echo "<strong>📱 Как будет выглядеть в Telegram:</strong><br>";
                    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3; margin: 10px 0; font-family: monospace; white-space: pre-wrap;'>";
                    echo htmlspecialchars($message);
                    echo "</div>";
                    
                    echo "<strong>📊 Статистика:</strong><br>";
                    echo "• Длина: " . strlen($message) . " символов<br>";
                    echo "• Строк: " . substr_count($message, "\n") . "<br><br>";
                    
                    echo "<a href='?action=publish&url=" . urlencode($url) . "&title=" . urlencode($title) . "&description=" . urlencode($description) . "' class='btn btn-success'>📤 Опубликовать</a>";
                }
                ?>
            </div>
            <a href="?action=full_search" class="btn">← К списку новостей</a>
            
        <?php elseif ($action == 'publish'): ?>
            <h3>📤 Публикация новости</h3>
            <div class="result">
                <?php
                $title = $_GET['title'] ?? '';
                $url = $_GET['url'] ?? '';
                $description = $_GET['description'] ?? '';
                
                if (empty($title) || empty($url)) {
                    echo "<span class='error'>❌ Нет данных для публикации</span>";
                } else {
                    echo "📰 " . htmlspecialchars($title) . "<br>";
                    echo "🔗 " . htmlspecialchars($url) . "<br><br>";
                    
                    // Получаем описание если его нет
                    if (empty($description)) {
                        echo "🔍 Получаем описание...<br>";
                        $description = getArticleDescription($url);
                    }
                    
                    // Формируем сообщение
                    $message = "🔔 *Новая статья на vnesenie-v-reestr.ru*\n\n";
                    $message .= "📝 *" . str_replace(['_', '*', '[', ']', '(', ')'], ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)'], $title) . "*\n\n";
                    
                    if ($description) {
                        $cleanDescription = str_replace(['_', '*', '[', ']', '(', ')'], ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)'], $description);
                        $message .= "📄 " . $cleanDescription . "\n\n";
                    }
                    
                    $message .= "👆 [Читать полностью](" . $url . ")\n\n";
                    $message .= "#реестр #минпромторг #новости";
                    
                    echo "📤 Отправляем в Telegram...<br>";
                    
                    $messageId = sendToTelegram($message);
                    
                    if ($messageId) {
                        echo "<span class='success'>✅ Новость опубликована!</span><br>";
                        echo "📨 Message ID: {$messageId}<br>";
                        echo "📺 Канал: @reestr_garant<br>";
                        echo "📊 Длина сообщения: " . strlen($message) . " символов<br>";
                    } else {
                        echo "<span class='error'>❌ Ошибка публикации</span><br>";
                    }
                }
                ?>
            </div>
            <a href="?action=full_search" class="btn">← К списку новостей</a>
            <a href="?" class="btn">← Главная</a>
            
        <?php elseif ($action == 'test_description'): ?>
            <h3>🧪 Тест получения описаний</h3>
            <div class="result">
                <?php
                echo "Тестируем получение описаний статей...<br><br>";
                
                $testNews = findNewsWithPagination(1);
                
                foreach (array_slice($testNews, 0, 3) as $index => $item) {
                    echo "<strong>" . ($index + 1) . ". " . htmlspecialchars($item['title']) . "</strong><br>";
                    echo "🔗 " . htmlspecialchars($item['url']) . "<br>";
                    
                    $description = getArticleDescription($item['url']);
                    
                    if ($description) {
                        echo "✅ Описание (" . strlen($description) . " символов): " . htmlspecialchars($description) . "<br>";
                    } else {
                        echo "❌ Описание не найдено<br>";
                    }
                    
                    echo "<br>";
                    sleep(1); // Пауза между запросами
                }
                ?>
            </div>
            <a href="?" class="btn">← Назад</a>
            
        <?php endif; ?>
    </div>
</body>
</html>