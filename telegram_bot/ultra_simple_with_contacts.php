<?php
/**
 * Простая версия с контактными данными
 * Файл: ultra_simple_with_contacts.php
 */

set_time_limit(5);
ini_set('memory_limit', '16M');

// Прямые настройки
$BOT_TOKEN = '7739849524:AAFpk9zQZ27LV_sw-NQt1D1vlUDlJhHLdCs';
$CHAT_ID = '-1002836639801';
$SITE_URL = 'https://vnesenie-v-reestr.ru';

$action = $_GET['action'] ?? 'menu';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Простой бот с контактами</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; background: #007bff; color: white; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: black; }
        .result { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .example { background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3; margin: 15px 0; }
        .news-item { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .news-title { font-weight: bold; color: #333; }
        .news-url { font-size: 11px; color: #666; word-break: break-all; }
        textarea { width: 95%; height: 80px; margin: 10px 0; }
        input[type="text"], input[type="url"] { width: 95%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Простой Telegram бот с контактами</h1>
        
        <?php if ($action == 'menu'): ?>
            <div class="example">
                <h4>📱 Новый формат постов:</h4>
                <strong>🔔 Новая статья на vnesenie-v-reestr.ru</strong><br><br>
                <strong>📝 Заголовок новости</strong><br><br>
                <strong>👆 Читать полностью</strong><br><br>
                #реестр #минпромторг #новости<br><br>
                ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━<br>
                <strong>📞 Телефон</strong><br>
                <strong>+7 920-898-17-18</strong><br>
                <strong>✉️ reestrgarant@mail.ru</strong><br><br>
                <strong>⏰ Ответим на ваше письмо в течение часа в рабочее время</strong>
            </div>
            
            <p>Выберите действие:</p>
            <a href="?action=test" class="btn">🧪 Тест подключения</a>
            <a href="?action=send" class="btn btn-success">📤 Отправить сообщение</a>
            <a href="?action=news" class="btn btn-warning">📰 Найти новости</a>
            
        <?php elseif ($action == 'test'): ?>
            <h3>🧪 Тестирование подключения...</h3>
            <div class="result">
                <?php
                if (extension_loaded('curl')) {
                    echo "✅ cURL доступен<br>";
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/getMe");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    
                    $result = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($result && $httpCode == 200) {
                        $response = json_decode($result, true);
                        if (isset($response['ok']) && $response['ok']) {
                            echo "<span class='success'>✅ Telegram API работает!</span><br>";
                            echo "🤖 Бот: " . htmlspecialchars($response['result']['first_name']) . "<br>";
                            echo "👤 @" . htmlspecialchars($response['result']['username']) . "<br>";
                            
                            // Тестовое сообщение с контактами
                            echo "<br>📤 Отправляем тестовое сообщение с контактами...<br>";
                            
                            $testMessage = "🧪 *Тест бота с контактами*\n\nБот работает!\n⏰ " . date('d.m.Y H:i:s') . "\n\n";
                            $testMessage .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                            $testMessage .= "📞 *Телефон*\n";
                            $testMessage .= "*\\+7 920\\-898\\-17\\-18*\n";
                            $testMessage .= "✉️ info@vnesenie\\-v\\-reestr\\.ru\n\n";
                            $testMessage .= "⏰ Ответим на ваше письмо в течение часа в рабочее время";
                            
                            $ch2 = curl_init();
                            curl_setopt($ch2, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
                            curl_setopt($ch2, CURLOPT_POST, true);
                            curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query([
                                'chat_id' => $CHAT_ID,
                                'text' => $testMessage,
                                'parse_mode' => 'Markdown'
                            ]));
                            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
                            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                            
                            $result2 = curl_exec($ch2);
                            curl_close($ch2);
                            
                            if ($result2) {
                                $response2 = json_decode($result2, true);
                                if (isset($response2['ok']) && $response2['ok']) {
                                    echo "<span class='success'>✅ Тестовое сообщение с контактами отправлено!</span><br>";
                                    echo "📨 Message ID: " . $response2['result']['message_id'] . "<br>";
                                } else {
                                    echo "<span class='error'>❌ Ошибка отправки: " . (isset($response2['description']) ? htmlspecialchars($response2['description']) : 'Неизвестная ошибка') . "</span><br>";
                                }
                            } else {
                                echo "<span class='error'>❌ Не удалось отправить сообщение</span><br>";
                            }
                        } else {
                            echo "<span class='error'>❌ Ошибка API: " . (isset($response['description']) ? htmlspecialchars($response['description']) : 'Неизвестная ошибка') . "</span><br>";
                        }
                    } else {
                        echo "<span class='error'>❌ Нет ответа от Telegram (HTTP: {$httpCode})</span><br>";
                    }
                } else {
                    echo "<span class='error'>❌ cURL недоступен</span><br>";
                }
                ?>
            </div>
            <a href="?" class="btn">← Назад</a>
            
        <?php elseif ($action == 'send'): ?>
            <h3>📤 Отправка сообщения</h3>
            
            <?php if (isset($_POST['message'])): ?>
                <div class="result">
                    <?php
                    $message = trim($_POST['message']);
                    if (empty($message)) {
                        echo "<span class='error'>❌ Введите текст сообщения!</span>";
                    } else {
                        if (extension_loaded('curl')) {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                                'chat_id' => $CHAT_ID,
                                'text' => $message,
                                'parse_mode' => 'Markdown'
                            ]));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            
                            $result = curl_exec($ch);
                            curl_close($ch);
                            
                            if ($result) {
                                $response = json_decode($result, true);
                                if (isset($response['ok']) && $response['ok']) {
                                    echo "<span class='success'>✅ Сообщение отправлено!</span><br>";
                                    echo "📨 Message ID: " . $response['result']['message_id'] . "<br>";
                                } else {
                                    echo "<span class='error'>❌ Ошибка: " . (isset($response['description']) ? htmlspecialchars($response['description']) : 'Неизвестная ошибка') . "</span><br>";
                                }
                            } else {
                                echo "<span class='error'>❌ Ошибка отправки</span><br>";
                            }
                        } else {
                            echo "<span class='error'>❌ cURL недоступен</span><br>";
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <label>Текст сообщения:</label><br>
                <textarea name="message" placeholder="Введите текст сообщения..."></textarea><br>
                <button type="submit" class="btn btn-success">📤 Отправить</button>
            </form>
            
            <a href="?" class="btn">← Назад</a>
            
        <?php elseif ($action == 'news'): ?>
            <h3>📰 Поиск новостей с контактами</h3>
            <div class="result">
                <?php
                if (extension_loaded('curl')) {
                    echo "🔍 Ищем новости на {$SITE_URL}/news/<br><br>";
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "{$SITE_URL}/news/");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Bot/1.0)');
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    
                    $content = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($content && $httpCode == 200) {
                        echo "<span class='success'>✅ Страница получена</span><br><br>";
                        
                        // Простейший поиск ссылок
                        preg_match_all('/<a[^>]+href="([^"]*\/news\/[^"]*)"[^>]*>([^<]+)<\/a>/i', $content, $matches);
                        
                        $found = 0;
                        for ($i = 0; $i < min(5, count($matches[1])); $i++) {
                            $url = $matches[1][$i];
                            $title = trim(strip_tags($matches[2][$i]));
                            
                            if (strlen($title) < 10 || strpos($url, '?') !== false || $url === '/news/' || $url === '/news') continue;
                            
                            if (strpos($url, 'http') !== 0) {
                                $url = $SITE_URL . $url;
                            }
                            
                            echo "<div class='news-item'>";
                            echo "<div class='news-title'>📰 " . htmlspecialchars($title) . "</div>";
                            echo "<div class='news-url'>🔗 " . htmlspecialchars($url) . "</div>";
                            echo "<a href='?action=publish&url=" . urlencode($url) . "&title=" . urlencode($title) . "' class='btn' style='font-size: 12px; padding: 5px 10px;'>📤 Опубликовать с контактами</a>";
                            echo "</div>";
                            
                            $found++;
                        }
                        
                        if ($found == 0) {
                            echo "<span class='error'>❌ Новости не найдены</span>";
                        }
                    } else {
                        echo "<span class='error'>❌ Не удалось получить страницу (код: {$httpCode})</span>";
                    }
                } else {
                    echo "<span class='error'>❌ cURL недоступен</span>";
                }
                ?>
            </div>
            <a href="?" class="btn">← Назад</a>
            
        <?php elseif ($action == 'publish'): ?>
            <h3>📤 Публикация новости с контактами</h3>
            <div class="result">
                <?php
                $title = $_GET['title'] ?? '';
                $url = $_GET['url'] ?? '';
                
                if (empty($title) || empty($url)) {
                    echo "<span class='error'>❌ Нет данных для публикации</span>";
                } else {
                    echo "📰 " . htmlspecialchars($title) . "<br>";
                    echo "🔗 " . htmlspecialchars($url) . "<br><br>";
                    
                    $message = "🔔 *Новая статья на vnesenie-v-reestr.ru*\n\n";
                    $message .= "📝 *" . str_replace(['_', '*', '[', ']', '(', ')'], ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)'], $title) . "*\n\n";
                    $message .= "👆 [Читать полностью](" . $url . ")\n\n";
                    $message .= "#реестр #минпромторг #новости\n\n";
                    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                    $message .= "📞 *Телефон*\n";
                    $message .= "*\\+7 920\\-898\\-17\\-18*\n";
                    $message .= "✉️ info@vnesenie\\-v\\-reestr\\.ru\n\n";
                    $message .= "⏰ Ответим на ваше письмо в течение часа в рабочее время";
                    
                    if (extension_loaded('curl')) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                            'chat_id' => $CHAT_ID,
                            'text' => $message,
                            'parse_mode' => 'Markdown'
                        ]));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        
                        $result = curl_exec($ch);
                        curl_close($ch);
                        
                        if ($result) {
                            $response = json_decode($result, true);
                            if (isset($response['ok']) && $response['ok']) {
                                echo "<span class='success'>✅ Новость с контактами опубликована!</span><br>";
                                echo "📨 Message ID: " . $response['result']['message_id'] . "<br>";
                                echo "📺 Канал: @reestr_garant<br>";
                                echo "📊 Длина сообщения: " . strlen($message) . " символов<br>";
                            } else {
                                echo "<span class='error'>❌ Ошибка: " . (isset($response['description']) ? htmlspecialchars($response['description']) : 'Неизвестная ошибка') . "</span><br>";
                            }
                        } else {
                            echo "<span class='error'>❌ Ошибка отправки</span><br>";
                        }
                    } else {
                        echo "<span class='error'>❌ cURL недоступен</span><br>";
                    }
                }
                ?>
            </div>
            <a href="?action=news" class="btn">← К новостям</a>
            <a href="?" class="btn">← Главная</a>
            
        <?php endif; ?>
    </div>
</body>
</html>