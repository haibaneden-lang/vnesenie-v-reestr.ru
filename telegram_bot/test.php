<?php
/**
 * Простой тест доступности
 * Файл: test.php
 */
echo "✅ PHP работает!<br>";
echo "📁 Текущая папка: " . __DIR__ . "<br>";
echo "🕐 Время: " . date('d.m.Y H:i:s') . "<br>";

// Проверяем наличие файлов
$files = ['config.php', 'database.php', 'telegram.php', 'parser.php', 'bot.php', 'setup_cron.php'];

echo "<h3>📋 Проверка файлов:</h3>";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} - найден<br>";
    } else {
        echo "❌ {$file} - НЕ НАЙДЕН<br>";
    }
}

// Проверяем права доступа
echo "<h3>🔐 Права доступа:</h3>";
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "{$file} - {$perms}<br>";
    }
}

echo "<h3>🧪 Следующий шаг:</h3>";
echo "Если все файлы найдены, перейдите на: <a href='setup_cron.php'>setup_cron.php</a>";
?>