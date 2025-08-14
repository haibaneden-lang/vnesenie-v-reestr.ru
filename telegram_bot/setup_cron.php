<?php
/**
 * Настройка автоматического запуска через cron
 * Файл: setup_cron.php
 * 
 * Этот файл помогает настроить автоматический запуск бота
 */

require_once 'config.php';

echo "🔧 НАСТРОЙКА АВТОМАТИЧЕСКОГО ЗАПУСКА БОТА\n";
echo "==========================================\n\n";

$currentDir = __DIR__;
$botFile = $currentDir . '/bot.php';
$phpPath = exec('which php') ?: '/usr/bin/php';

echo "📁 Директория: {$currentDir}\n";
echo "🐘 PHP путь: {$phpPath}\n";
echo "🤖 Файл бота: {$botFile}\n\n";

// Проверяем существование файлов
$requiredFiles = ['config.php', 'database.php', 'telegram.php', 'parser.php', 'bot.php'];
$missingFiles = [];

foreach ($requiredFiles as $file) {
    if (!file_exists($currentDir . '/' . $file)) {
        $missingFiles[] = $file;
    }
}

if (!empty($missingFiles)) {
    echo "❌ Отсутствуют необходимые файлы:\n";
    foreach ($missingFiles as $file) {
        echo "   - {$file}\n";
    }
    echo "\nЗагрузите все файлы на сервер и запустите setup_cron.php заново.\n";
    exit(1);
}

echo "✅ Все необходимые файлы найдены\n\n";

// Генерируем команды для cron
echo "📅 НАСТРОЙКА CRON ЗАДАЧ\n";
echo "=======================\n\n";

echo "Добавьте следующие строки в crontab:\n\n";

// Основная задача - каждые 30 минут
$cronCommand = "*/30 * * * * {$phpPath} {$botFile} run >> {$currentDir}/cron.log 2>&1";
echo "1️⃣ Проверка новостей каждые 30 минут:\n";
echo "{$cronCommand}\n\n";

// Очистка данных - раз в неделю
$cleanupCommand = "0 2 * * 0 {$phpPath} {$botFile} cleanup >> {$currentDir}/cron.log 2>&1";
echo "2️⃣ Очистка старых данных каждое воскресенье в 02:00:\n";
echo "{$cleanupCommand}\n\n";

// Тестовая задача - для проверки
$testCommand = "*/5 * * * * {$phpPath} {$botFile} test >> {$currentDir}/test.log 2>&1";
echo "3️⃣ Тестовая задача (запускать временно для проверки):\n";
echo "{$testCommand}\n\n";

echo "📋 ИНСТРУКЦИЯ ПО НАСТРОЙКЕ CRON:\n";
echo "================================\n\n";

echo "1. Подключитесь к серверу по SSH\n";
echo "2. Выполните команду: crontab -e\n";
echo "3. Добавьте строки из пункта 1 и 2\n";
echo "4. Сохраните изменения (Ctrl+O, Enter, Ctrl+X для nano)\n";
echo "5. Проверьте добавленные задачи: crontab -l\n\n";

echo "🔍 АЛЬТЕРНАТИВНЫЕ СПОСОБЫ ЗАПУСКА:\n";
echo "==================================\n\n";

echo "Если cron недоступен, можно использовать:\n\n";

echo "1️⃣ Веб-cron (через URL):\n";
$webCronUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/web_cron.php";
echo "   URL: {$webCronUrl}\n";
echo "   Настройте внешний сервис веб-cron для вызова этого URL каждые 30 минут\n\n";

echo "2️⃣ Ручной запуск через браузер:\n";
$manualUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/manual_run.php";
echo "   URL: {$manualUrl}\n";
echo "   Открывайте эту ссылку для ручного запуска бота\n\n";

// Создаем файл web_cron.php
$webCronContent = '<?php
/**
 * Веб-интерфейс для запуска бота через URL
 * Файл: web_cron.php
 */

// Проверка безопасности (опционально)
$secret = "telegram_bot_2025"; // Измените на свой секретный ключ
if (!isset($_GET["key"]) || $_GET["key"] !== $secret) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

require_once "bot.php";

// Устанавливаем заголовки
header("Content-Type: text/plain; charset=utf-8");

echo "🤖 Запуск Telegram бота через веб-интерфейс\n";
echo "============================================\n\n";

$startTime = microtime(true);

// Запускаем бота
$result = runBot();

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "\n⏱️ Время выполнения: {$executionTime} секунд\n";
echo "📊 Результат: " . ($result ? "Успешно" : "Ошибка") . "\n";
echo "🕐 Время: " . date("d.m.Y H:i:s") . "\n";
';

file_put_contents($currentDir . '/web_cron.php', $webCronContent);

// Создаем файл manual_run.php
$manualRunContent = '<?php
/**
 * Веб-интерфейс для ручного запуска бота
 * Файл: manual_run.php
 */

require_once "bot.php";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление Telegram ботом</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .log { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
        .status { padding: 10px; border-radius: 5px; margin: 10px 0; }
        .status.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Управление Telegram ботом</h1>
        
        <div class="actions">
            <h3>Действия:</h3>
            <a href="?action=run" class="btn btn-primary">🚀 Запустить проверку новостей</a>
            <a href="?action=test" class="btn btn-success">🧪 Тестировать бота</a>
            <a href="?action=cleanup" class="btn btn-warning">🧹 Очистить старые данные</a>
            <a href="?action=logs" class="btn btn-danger">📋 Показать логи</a>
        </div>

        <?php
        if (isset($_GET["action"])) {
            echo "<div class=\"status\">";
            
            switch ($_GET["action"]) {
                case "run":
                    echo "<h3>🚀 Запуск проверки новостей...</h3>";
                    ob_start();
                    $result = runBot();
                    $output = ob_get_clean();
                    
                    if ($result) {
                        echo "<div class=\"status success\">✅ Бот выполнен успешно</div>";
                    } else {
                        echo "<div class=\"status error\">❌ Ошибка выполнения бота</div>";
                    }
                    
                    if ($output) {
                        echo "<div class=\"log\">" . htmlspecialchars($output) . "</div>";
                    }
                    break;
                    
                case "test":
                    echo "<h3>🧪 Тестирование бота...</h3>";
                    ob_start();
                    $result = testBot();
                    $output = ob_get_clean();
                    
                    echo "<div class=\"log\">" . htmlspecialchars($output) . "</div>";
                    break;
                    
                case "cleanup":
                    echo "<h3>🧹 Очистка данных...</h3>";
                    $result = cleanupOldData();
                    
                    if ($result) {
                        echo "<div class=\"status success\">✅ Очистка выполнена успешно</div>";
                    } else {
                        echo "<div class=\"status error\">❌ Ошибка при очистке</div>";
                    }
                    break;
                    
                case "logs":
                    echo "<h3>📋 Последние логи:</h3>";
                    if (file_exists(LOG_FILE)) {
                        $logs = file_get_contents(LOG_FILE);
                        $logLines = explode("\n", $logs);
                        $lastLogs = array_slice($logLines, -50); // Последние 50 строк
                        echo "<div class=\"log\">" . htmlspecialchars(implode("\n", $lastLogs)) . "</div>";
                    } else {
                        echo "<div class=\"status error\">Файл логов не найден</div>";
                    }
                    break;
            }
            
            echo "</div>";
        }
        ?>
        
        <div class="info">
            <h3>ℹ️ Информация:</h3>
            <p><strong>Сайт:</strong> <?php echo SITE_URL; ?></p>
            <p><strong>Telegram канал:</strong> @reestr_garant</p>
            <p><strong>Интервал проверки:</strong> каждые 30 минут</p>
            <p><strong>Последняя проверка:</strong> <?php echo file_exists(LOG_FILE) ? date("d.m.Y H:i:s", filemtime(LOG_FILE)) : "Никогда"; ?></p>
        </div>
    </div>
</body>
</html>';

file_put_contents($currentDir . '/manual_run.php', $manualRunContent);

echo "✅ Созданы дополнительные файлы:\n";
echo "   - web_cron.php (для веб-cron сервисов)\n";
echo "   - manual_run.php (для ручного управления)\n\n";

echo "🔐 НАСТРОЙКИ БЕЗОПАСНОСТИ:\n";
echo "=========================\n\n";
echo "1. В файле web_cron.php измените секретный ключ\n";
echo "2. Ограничьте доступ к служебным файлам через .htaccess\n";
echo "3. Не публикуйте токен бота в открытом доступе\n\n";

// Создаем .htaccess для безопасности
$htaccessContent = '# Защита служебных файлов
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "database.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.db">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>

# Разрешаем доступ только к основным файлам
<FilesMatch "^(web_cron|manual_run|setup_cron)\.php$">
    Order allow,deny
    Allow from all
</FilesMatch>';

file_put_contents($currentDir . '/.htaccess', $htaccessContent);

echo "✅ Создан файл .htaccess для безопасности\n\n";

echo "🎉 НАСТРОЙКА ЗАВЕРШЕНА!\n";
echo "=======================\n\n";
echo "Следующие шаги:\n";
echo "1. Настройте cron задачи (инструкция выше)\n";
echo "2. Протестируйте бота: {$manualUrl}\n";
echo "3. Проверьте логи через несколько минут\n\n";

echo "📞 В случае проблем проверьте:\n";
echo "- Корректность токена бота\n";
echo "- Права доступа к файлам (644 для .php, 666 для .db и .log)\n";
echo "- Наличие PHP расширений: curl, sqlite3, dom\n";

?>