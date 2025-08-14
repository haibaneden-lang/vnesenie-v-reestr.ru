<?php
/**
 * Автоматическая публикация новостей с контактными данными
 * Файл: auto_publish_updated.php
 */

set_time_limit(20);
ini_set('memory_limit', '64M');

// Настройки
$BOT_TOKEN = '7739849524:AAFpk9zQZ27LV_sw-NQt1D1vlUDlJhHLdCs';
$CHAT_ID = '-1002836639801';
$SITE_URL = 'https://vnesenie-v-reestr.ru';

// Файл для отслеживания опубликованных новостей (вместо БД)
$PUBLISHED_FILE = __DIR__ . '/published.txt';

// Функция логирования
function writeLog($message) {
    $logFile = __DIR__ . '/bot.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

// Функция проверки дубликатов
function isAlreadyPublished($newsId) {
    global $PUBLISHED_FILE;
    if (!file_exists($PUBLISHED_FILE)) {
        return false;
    }
    $published = file_get_contents($PUBLISHED_FILE);
    return strpos($published, $newsId) !== false;
}

// Функция сохранения ID опубликованной новости
function markAsPublished($newsId, $title) {
    global $PUBLISHED_FILE;
    $record = date('Y-m-d H:i:s') . "|{$newsId}|" . substr($title, 0, 50) . "\n";
    file_put_contents($PUBLISHED_FILE, $record, FILE_APPEND | LOCK_EX);
    
    // Очистка старых записей (оставляем только последние 100)
    $lines = file($PUBLISHED_FILE, FILE_IGNORE_NEW_LINES);
    if (count($lines) > 100) {
        $recentLines = array_slice($lines, -100);
        file_put_contents($PUBLISHED_FILE, implode("\n", $recentLines) . "\n");
    }
}

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

// Функция отправки в Telegram
function sendToTelegram($message) {
    global $BOT_TOKEN, $CHAT_ID;
    
    if (!extension_loaded('curl')) {
        writeLog("ERROR: cURL не доступен");
        return false;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'chat_id' => $CHAT_ID,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($result && $httpCode == 200) {
        $response = json_decode($result, true);
        if (isset($response['ok']) && $response['ok']) {
            writeLog("SUCCESS: Сообщение отправлено, ID: " . $response['result']['message_id']);
            return $response['result']['message_id'];
        } else {
            writeLog("ERROR: Telegram API: " . ($response['description'] ?? 'неизвестная ошибка'));
            return false;
        }
    } else {
        writeLog("ERROR: HTTP {$httpCode}, нет ответа от Telegram");
        return false;
    }
}

// Функция получения новостей с пагинацией
function getLatestNewsWithPagination($maxPages = 2) {
    global $SITE_URL;
    
    writeLog("INFO: Начинаем поиск новостей на {$SITE_URL}/news/ (страниц: {$maxPages})");
    
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
                writeLog("ERROR: Не удалось получить первую страницу новостей");
                return [];
            } else {
                writeLog("INFO: Страница {$page} недоступна, останавливаемся");
                break;
            }
        }
        
        writeLog("INFO: Страница {$page}: {$workingUrl}");
        
        // Парсим новости на странице
        $pageNews = parseNewsFromContent($pageContent);
        
        writeLog("INFO: Найдено на странице {$page}: " . count($pageNews) . " новостей");
        
        if (empty($pageNews) && $page > 1) {
            writeLog("INFO: На странице {$page} новостей не найдено, останавливаемся");
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
    
    writeLog("INFO: Итого уникальных новостей: " . count($uniqueNews));
    return array_slice($uniqueNews, 0, 10); // Ограничиваем до 10 новостей за раз
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
                'url' => $url
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
                'url' => $url
            ];
        }
    }
    
    return $foundNews;
}

// Основная функция
function runAutoPublish() {
    writeLog("INFO: ========== ЗАПУСК АВТОПУБЛИКАЦИИ С КОНТАКТАМИ ==========");
    
    $news = getLatestNewsWithPagination(2); // Проверяем 2 страницы
    
    if (empty($news)) {
        writeLog("INFO: Новых новостей не найдено");
        echo "ℹ️ Новых новостей не найдено\n";
        return;
    }
    
    $published = 0;
    $skipped = 0;
    
    foreach ($news as $item) {
        writeLog("INFO: Обрабатываем: " . $item['title']);
        
        $newsId = md5($item['url']);
        
        // Проверка дубликатов
        if (isAlreadyPublished($newsId)) {
            writeLog("INFO: Уже опубликована, пропускаем: " . $item['title']);
            $skipped++;
            continue;
        }
        
        // Получаем описание статьи
        echo "🔍 Получаем описание для: " . htmlspecialchars($item['title']) . "\n";
        $description = getArticleDescription($item['url']);
        
        // Формирование сообщения с контактами
        $message = "🔔 *Новая статья на vnesenie-v-reestr.ru*\n\n";
        $message .= "📝 *" . str_replace(['_', '*', '[', ']', '(', ')'], ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)'], $item['title']) . "*\n\n";
        
        if ($description) {
            $cleanDescription = str_replace(['_', '*', '[', ']', '(', ')'], ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)'], $description);
            $message .= "📄 " . $cleanDescription . "\n\n";
        }
        
$message .= "👆 [Читать полностью](" . $item['url'] . ")\n\n";
$message .= "#реестр #минпромторг #новости\n\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$message .= "📞 Телефон\n";
$message .= "+7 920-898-17-18\n";
$message .= "✉️ reestrgarant@mail.ru\n\n";


        
        // Отправка
        $messageId = sendToTelegram($message);
        
        if ($messageId) {
            markAsPublished($newsId, $item['title']);
            writeLog("SUCCESS: Опубликована с контактами: " . $item['title']);
            $published++;
            
            echo "✅ Опубликована: " . htmlspecialchars($item['title']) . "\n";
            echo "📊 Длина сообщения: " . strlen($message) . " символов\n";
            
            // Пауза между отправками
            sleep(3);
        } else {
            writeLog("ERROR: Не удалось опубликовать: " . $item['title']);
            echo "❌ Ошибка: " . htmlspecialchars($item['title']) . "\n";
        }
        
        // Ограничение - не более 3 новостей за раз для безопасности
        if ($published >= 3) {
            echo "⚠️ Достигнут лимит публикаций за раз (3)\n";
            break;
        }
    }
    
    // Сводка
    $summary = "📊 Результат автопубликации с контактами:\n";
    $summary .= "✅ Опубликовано: {$published}\n";
    $summary .= "⏭️ Пропущено: {$skipped}\n";
    $summary .= "🕐 " . date('d.m.Y H:i:s');
    
    writeLog("INFO: " . str_replace("\n", " | ", $summary));
    echo $summary . "\n";
    
    // Отправляем сводку если есть публикации
    if ($published > 0) {
        $summaryMessage = "📈 *Сводка автопубликации*\n\n✅ Опубликовано новостей: *{$published}*\n📅 " . date('d.m.Y H:i:s') . "\n\n🤖 Автоматический режим с контактными данными";
        sendToTelegram($summaryMessage);
    }
    
    writeLog("INFO: ========== АВТОПУБЛИКАЦИЯ ЗАВЕРШЕНА ==========");
}

// Запуск
$mode = $_GET['mode'] ?? 'web';

if ($mode == 'web') {
    // Веб-интерфейс
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Автопубликация с контактами</title>";
    echo "<style>body{font-family:Arial;margin:20px;background:#f5f5f5;} .container{max-width:700px;margin:0 auto;background:white;padding:20px;border-radius:10px;} .btn{padding:10px 20px;margin:5px;border:none;border-radius:5px;background:#007bff;color:white;text-decoration:none;display:inline-block;} .log{background:#f8f9fa;padding:15px;border-radius:5px;font-family:monospace;white-space:pre-wrap;} .example{background:#e7f3ff;padding:15px;border-radius:8px;border-left:4px solid #2196f3;margin:15px 0;}</style>";
    echo "</head><body><div class='container'>";
    echo "<h1>🤖 Автоматическая публикация с контактными данными</h1>";
    
    if (isset($_GET['run'])) {
        echo "<div class='log'>";
        ob_start();
        runAutoPublish();
        $output = ob_get_clean();
        echo htmlspecialchars($output);
        echo "</div>";
        echo "<p><a href='?' class='btn'>🔄 Запустить еще раз</a></p>";
    } else {
        echo "<p>Новый формат публикаций включает контактную информацию для привлечения клиентов:</p>";
        
        echo "<div class='example'>";
        echo "<h4>📱 Пример поста в Telegram:</h4>";
        echo "<strong>🔔 Новая статья на vnesenie-v-reestr.ru</strong><br><br>";
        echo "<strong>📝 Заголовок новости</strong><br><br>";
        echo "<strong>📄</strong> Описание статьи с ключевыми моментами...<br><br>";
        echo "<strong>👆 Читать полностью</strong><br><br>";
        echo "#реестр #минпромторг #новости<br><br>";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>";
        echo "<strong>📞 Телефон</strong><br>";
        echo "<strong>+7 920-898-17-18</strong><br>";
        echo "<strong>✉️ reestrgarant@mail.ru</strong><br><br>";
        echo "<strong>⏰ Ответим на ваше письмо в течение часа в рабочее время</strong>";
        echo "</div>";
        
        echo "<a href='?run=1' class='btn' style='background:#28a745;'>🚀 Запустить автопубликацию с контактами</a>";
        echo "<a href='enhanced_parser.php' class='btn'>← Ручной режим</a>";
        
        // Показываем последние логи
        $logFile = __DIR__ . '/bot.log';
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $logLines = explode("\n", $logs);
            $lastLogs = array_slice($logLines, -8);
            echo "<h3>📋 Последние записи лога:</h3>";
            echo "<div class='log'>" . htmlspecialchars(implode("\n", $lastLogs)) . "</div>";
        }
    }
    
    echo "</div></body></html>";
} else {
    // Режим командной строки (для cron)
    header('Content-Type: text/plain; charset=utf-8');
    runAutoPublish();
}
?>