<?php
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
</html>