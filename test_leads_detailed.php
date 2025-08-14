<?php
/**
 * Детальный тест функциональности leads с логированием
 */

// Включаем отображение ошибок для тестирования
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Детальный тест функциональности leads</h1>";

// Создаем папку для логов если её нет
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

// Функция для логирования
function logMessage($message) {
    $log = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents('logs/test_leads.log', $log, FILE_APPEND);
    echo $message . "<br>";
}

logMessage("=== НАЧАЛО ТЕСТА ===");

// 1. Проверяем подключение к базе данных
echo "<h2>1. Тест подключения к базе данных</h2>";
require_once __DIR__ . '/config/database.php';

try {
    if (testDbConnection()) {
        logMessage("✅ Подключение к базе данных успешно!");
    } else {
        logMessage("❌ Ошибка подключения к базе данных");
        exit;
    }
} catch (Exception $e) {
    logMessage("❌ Исключение при подключении: " . $e->getMessage());
    exit;
}

// 2. Проверяем класс Lead
echo "<h2>2. Тест класса Lead</h2>";
require_once __DIR__ . '/models/Lead.php';

try {
    $lead = new Lead();
    logMessage("✅ Класс Lead создан успешно");
    
    if ($lead->testConnection()) {
        logMessage("✅ Тест подключения через класс Lead прошел");
    } else {
        logMessage("❌ Тест подключения через класс Lead не прошел");
    }
} catch (Exception $e) {
    logMessage("❌ Ошибка создания класса Lead: " . $e->getMessage());
    exit;
}

// 3. Проверяем структуру таблицы leads
echo "<h2>3. Проверка структуры таблицы leads</h2>";
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("DESCRIBE leads");
    $columns = $stmt->fetchAll();
    
    logMessage("📋 Структура таблицы leads:");
    foreach ($columns as $column) {
        logMessage("&nbsp;&nbsp;• " . $column['Field'] . " - " . $column['Type'] . " (" . $column['Null'] . ")");
    }
} catch (Exception $e) {
    logMessage("❌ Ошибка при проверке структуры таблицы: " . $e->getMessage());
}

// 4. Тестируем сохранение lead с детальным логированием
echo "<h2>4. Детальный тест сохранения lead</h2>";
$testData = [
    'name' => 'Тест Тестович',
    'phone' => '+7 (999) 123-45-67',
    'email' => 'test@example.com',
    'company' => 'Тестовая компания',
    'message' => 'Это тестовое сообщение для проверки функциональности leads',
    'service' => 'Тестовая услуга'
];

logMessage("📝 Тестовые данные: " . json_encode($testData, JSON_UNESCAPED_UNICODE));

try {
    // Пробуем сохранить через класс Lead
    logMessage("🔄 Пытаемся сохранить через класс Lead...");
    $result = $lead->save($testData, 'Тестовая услуга');
    
    if ($result) {
        logMessage("✅ Тестовый lead успешно сохранен через класс Lead!");
    } else {
        logMessage("❌ Не удалось сохранить через класс Lead");
    }
    
    // Пробуем сохранить через функцию
    logMessage("🔄 Пытаемся сохранить через функцию saveLeadToDatabase...");
    $result2 = saveLeadToDatabase($testData, 'Тестовая услуга');
    
    if ($result2) {
        logMessage("✅ Тестовый lead успешно сохранен через функцию!");
    } else {
        logMessage("❌ Не удалось сохранить через функцию");
    }
    
} catch (Exception $e) {
    logMessage("❌ Исключение при сохранении lead: " . $e->getMessage());
    logMessage("📋 Stack trace: " . $e->getTraceAsString());
}

// 5. Проверяем, действительно ли lead сохранился
echo "<h2>5. Проверка сохранения в базе</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leads");
    $count = $stmt->fetch()['total'];
    logMessage("📊 Общее количество leads в базе: " . $count);
    
            if ($count > 0) {
            $stmt = $pdo->query("SELECT * FROM leads ORDER BY date_created DESC LIMIT 1");
            $lastLead = $stmt->fetch();
            logMessage("📝 Последний lead:");
            logMessage("&nbsp;&nbsp;• ID: " . $lastLead['id']);
            logMessage("&nbsp;&nbsp;• Имя: " . $lastLead['name']);
            logMessage("&nbsp;&nbsp;• Email: " . $lastLead['email']);
            logMessage("&nbsp;&nbsp;• Сообщение: " . substr($lastLead['message'], 0, 100) . "...");
            logMessage("&nbsp;&nbsp;• Дата: " . $lastLead['date_created']);
        }
} catch (Exception $e) {
    logMessage("❌ Ошибка при проверке сохранения: " . $e->getMessage());
}

// 6. Проверяем права доступа к таблице
echo "<h2>6. Проверка прав доступа</h2>";
try {
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    $grants = $stmt->fetchAll();
    
    logMessage("🔐 Права пользователя:");
    foreach ($grants as $grant) {
        logMessage("&nbsp;&nbsp;• " . $grant['Grants for ' . DB_USER . '@' . DB_HOST]);
    }
} catch (Exception $e) {
    logMessage("❌ Ошибка при проверке прав: " . $e->getMessage());
}

logMessage("=== ТЕСТ ЗАВЕРШЕН ===");
echo "<hr>";
echo "<p><strong>Тест завершен! Проверьте лог файл logs/test_leads.log для деталей.</strong></p>";
echo "<p><a href='logs/test_leads.log' target='_blank'>📄 Открыть лог файл</a></p>";
?>
