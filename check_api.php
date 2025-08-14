<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔧 Диагностика системы</h2>";

// Проверяем файлы
$files = ['registry_api.php', 'registry_table.html', 'production.xlsx'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file - найден<br>";
    } else {
        echo "❌ $file - отсутствует<br>";
    }
}

// Проверяем API
echo "<h3>🔌 Тест API:</h3>";
try {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/registry_api.php?page=1&limit=5';
    $response = @file_get_contents($url);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "✅ API работает<br>";
            echo "📊 Записей: " . ($data['pagination']['total_records'] ?? 0) . "<br>";
        } else {
            echo "⚠️ API возвращает некорректные данные<br>";
            echo "Ответ: " . substr($response, 0, 200) . "...<br>";
        }
    } else {
        echo "❌ API недоступен<br>";
    }
} catch (Exception $e) {
    echo "❌ Ошибка API: " . $e->getMessage() . "<br>";
}

// Проверяем базу данных
echo "<h3>💾 Тест базы данных:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=bd;charset=utf8mb4", 'haibaneden', 'Stilesmile1');
    echo "✅ Подключение к БД успешно<br>";
    
    $count = $pdo->query("SELECT COUNT(*) FROM registry_entries")->fetchColumn();
    echo "📊 Записей в таблице: $count<br>";
    
    if ($count > 0) {
        $sample = $pdo->query("SELECT * FROM registry_entries LIMIT 1")->fetch();
        echo "📝 Пример записи: " . ($sample['company_name'] ?? 'N/A') . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка БД: " . $e->getMessage() . "<br>";
}

// Проверяем лимиты сервера
echo "<h3>⚙️ Настройки сервера:</h3>";
echo "💾 Лимит памяти: " . ini_get('memory_limit') . "<br>";
echo "⏱️ Лимит времени: " . ini_get('max_execution_time') . "<br>";
echo "📁 Максимальный размер файла: " . ini_get('upload_max_filesize') . "<br>";
echo "🔧 Версия PHP: " . phpversion() . "<br>";

echo '<hr>';
echo '<a href="simple_excel_loader.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔄 Перезагрузить данные</a>';
?>