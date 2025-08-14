<?php
/**
 * Быстрый тест одной функции за раз
 * Файл: quick_test.php
 */

set_time_limit(3);
ini_set('memory_limit', '8M');

$test = $_GET['test'] ?? 'curl';

echo "<h2>⚡ Быстрый тест</h2>";

if ($test == 'curl') {
    echo "<h3>🔧 Проверка cURL:</h3>";
    if (extension_loaded('curl')) {
        echo "✅ cURL загружен<br>";
        echo "📋 Версия: " . curl_version()['version'] . "<br>";
        echo "<a href='?test=telegram'>→ Тест Telegram</a>";
    } else {
        echo "❌ cURL не загружен<br>";
        echo "💡 Попробуйте обратиться к хостинг-провайдеру";
    }
}

elseif ($test == 'telegram') {
    echo "<h3>📱 Тест Telegram API:</h3>";
    
    $token = '7739849524:AAFpk9zQZ27LV_sw-NQt1D1vlUDlJhHLdCs';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/getMe");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($result) {
        $data = json_decode($result, true);
        if (isset($data['ok']) && $data['ok']) {
            echo "✅ API работает!<br>";
            echo "🤖 " . htmlspecialchars($data['result']['first_name']) . "<br>";
            echo "<a href='?test=send'>→ Тест отправки</a>";
        } else {
            echo "❌ Ошибка API: " . htmlspecialchars($data['description'] ?? 'неизвестно') . "<br>";
        }
    } else {
        echo "❌ Нет ответа: " . htmlspecialchars($error) . "<br>";
    }
}

elseif ($test == 'send') {
    echo "<h3>📤 Тест отправки сообщения:</h3>";
    
    $token = '7739849524:AAFpk9zQZ27LV_sw-NQt1D1vlUDlJhHLdCs';
    $chatId = '-1002836639801';
    $message = "⚡ Быстрый тест\n" . date('H:i:s');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "chat_id={$chatId}&text=" . urlencode($message));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result) {
        $data = json_decode($result, true);
        if (isset($data['ok']) && $data['ok']) {
            echo "✅ Сообщение отправлено!<br>";
            echo "📨 ID: " . $data['result']['message_id'] . "<br>";
            echo "<a href='ultra_simple.php'>→ Полная версия</a>";
        } else {
            echo "❌ Ошибка: " . htmlspecialchars($data['description'] ?? 'неизвестно') . "<br>";
        }
    } else {
        echo "❌ Нет ответа<br>";
    }
}

echo "<br><hr>";
echo "<a href='?test=curl'>🔧 cURL</a> | ";
echo "<a href='?test=telegram'>📱 Telegram</a> | ";
echo "<a href='?test=send'>📤 Отправка</a>";
?>