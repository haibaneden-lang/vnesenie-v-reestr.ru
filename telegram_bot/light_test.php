<?php
/**
 * Облегченное тестирование компонентов по частям
 * Файл: light_test.php
 */

// Увеличиваем лимиты
set_time_limit(60);
ini_set('memory_limit', '128M');

require_once 'config.php';
require_once 'database.php';

echo "🧪 ПОШАГОВОЕ ТЕСТИРОВАНИЕ БОТА<br>";
echo "================================<br><br>";

// Шаг 1: Тест базы данных
echo "<h3>1️⃣ Тестирование базы данных...</h3>";
try {
    $pdo = initDatabase();
    if ($pdo) {
        echo "✅ База данных работает<br>";
    } else {
        echo "❌ Ошибка базы данных<br>";
    }
} catch (Exception $e) {
    echo "❌ Ошибка БД: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<br>";

// Шаг 2: Тест Telegram API (простой)
echo "<h3>2️⃣ Тестирование Telegram API...</h3>";
try {
    $telegramUrl = TELEGRAM_API_URL . 'getMe';
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET'
        ]
    ]);
    
    $result = file_get_contents($telegramUrl, false, $context);
    
    if ($result === false) {
        echo "❌ Не удалось подключиться к Telegram API<br>";
        echo "🔍 Возможно, хостинг блокирует внешние запросы<br>";
    } else {
        $response = json_decode($result, true);
        if ($response['ok']) {
            echo "✅ Telegram API работает<br>";
            echo "🤖 Бот: " . htmlspecialchars($response['result']['first_name']) . "<br>";
            echo "👤 Username: @" . htmlspecialchars($response['result']['username']) . "<br>";
        } else {
            echo "❌ Ошибка Telegram: " . htmlspecialchars($response['description']) . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Ошибка Telegram: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<br>";

// Шаг 3: Тест парсинга сайта (простой)
echo "<h3>3️⃣ Тестирование доступа к сайту...</h3>";
try {
    $siteUrl = SITE_URL . '/news/';
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'Mozilla/5.0 (compatible; NewsBot/1.0)'
        ]
    ]);
    
    $content = file_get_contents($siteUrl, false, $context);
    
    if ($content === false) {
        echo "❌ Не удалось получить страницу новостей<br>";
        echo "🔍 URL: " . htmlspecialchars($siteUrl) . "<br>";
    } else {
        echo "✅ Страница новостей доступна<br>";
        echo "📏 Размер: " . strlen($content) . " байт<br>";
        
        // Простая проверка наличия ссылок на новости
        $newsCount = substr_count($content, '/news/');
        echo "🔗 Найдено ссылок на новости: {$newsCount}<br>";
    }
} catch (Exception $e) {
    echo "❌ Ошибка парсинга: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<br>";

// Шаг 4: Проверка настроек PHP
echo "<h3>4️⃣ Настройки PHP:</h3>";
echo "⏱️ Максимальное время выполнения: " . ini_get('max_execution_time') . " сек<br>";
echo "💾 Лимит памяти: " . ini_get('memory_limit') . "<br>";
echo "🌐 allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Включено' : 'Отключено') . "<br>";
echo "🔗 cURL: " . (extension_loaded('curl') ? 'Доступен' : 'Недоступен') . "<br>";

echo "<br>";

// Следующие шаги
echo "<h3>📋 Результат диагностики:</h3>";

if ($result !== false && $content !== false) {
    echo "✅ <strong>Все компоненты работают!</strong><br>";
    echo "🎯 Можно попробовать <a href='simple_run.php'>упрощенный запуск</a><br>";
} else {
    echo "⚠️ <strong>Есть проблемы с внешними запросами</strong><br>";
    echo "💡 Обратитесь к хостинг-провайдеру для разрешения внешних запросов<br>";
}

echo "<br><a href='manual_run.php'>← Вернуться к управлению</a>";
?>