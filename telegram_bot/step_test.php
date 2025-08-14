<?php
/**
 * Поэтапное тестирование без превышения лимитов
 * Файл: step_test.php
 */

set_time_limit(10);
ini_set('memory_limit', '32M');

$step = $_GET['step'] ?? '1';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поэтапное тестирование бота</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .step { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .step.current { background: #e7f3ff; border: 2px solid #007bff; }
        .step.completed { background: #d4edda; border: 2px solid #28a745; }
        .step.pending { background: #f8f9fa; border: 2px solid #dee2e6; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; background: #007bff; color: white; }
        .result { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Поэтапное тестирование Telegram бота</h1>
        
        <div class="step <?php echo $step == '1' ? 'current' : ($step > '1' ? 'completed' : 'pending'); ?>">
            <h3>Шаг 1: Проверка файлов и настроек</h3>
            <?php if ($step >= '1'): ?>
                <div class="result">
                    <?php
                    echo "📁 Текущая папка: " . __DIR__ . "<br>";
                    echo "🕐 Время: " . date('d.m.Y H:i:s') . "<br><br>";
                    
                    $files = ['config.php', 'database.php', 'telegram.php', 'parser.php', 'bot.php'];
                    $allFilesExist = true;
                    
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            echo "<span class='success'>✅ {$file}</span><br>";
                        } else {
                            echo "<span class='error'>❌ {$file}</span><br>";
                            $allFilesExist = false;
                        }
                    }
                    
                    if ($allFilesExist) {
                        echo "<br><span class='success'>✅ Все файлы найдены!</span>";
                    }
                    ?>
                </div>
                <?php if ($step == '1'): ?>
                    <a href="?step=2" class="btn">Следующий шаг →</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="step <?php echo $step == '2' ? 'current' : ($step > '2' ? 'completed' : 'pending'); ?>">
            <h3>Шаг 2: Проверка конфигурации</h3>
            <?php if ($step >= '2'): ?>
                <div class="result">
                    <?php
                    if (file_exists('config.php')) {
                        require_once 'config.php';
                        echo "🤖 Токен бота: " . substr(TELEGRAM_BOT_TOKEN, 0, 10) . "...<br>";
                        echo "📺 Chat ID: " . TELEGRAM_CHAT_ID . "<br>";
                        echo "🌐 Сайт: " . SITE_URL . "<br>";
                        echo "<br><span class='success'>✅ Конфигурация загружена!</span>";
                    } else {
                        echo "<span class='error'>❌ Файл config.php не найден</span>";
                    }
                    ?>
                </div>
                <?php if ($step == '2'): ?>
                    <a href="?step=3" class="btn">Следующий шаг →</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="step <?php echo $step == '3' ? 'current' : ($step > '3' ? 'completed' : 'pending'); ?>">
            <h3>Шаг 3: Проверка базы данных</h3>
            <?php if ($step >= '3'): ?>
                <div class="result">
                    <?php
                    try {
                        require_once 'database.php';
                        $pdo = initDatabase();
                        if ($pdo) {
                            echo "<span class='success'>✅ База данных создана успешно!</span><br>";
                            
                            // Проверяем таблицы
                            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
                            foreach ($tables as $table) {
                                echo "📋 Таблица: " . $table['name'] . "<br>";
                            }
                        } else {
                            echo "<span class='error'>❌ Ошибка создания базы данных</span>";
                        }
                    } catch (Exception $e) {
                        echo "<span class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</span>";
                    }
                    ?>
                </div>
                <?php if ($step == '3'): ?>
                    <a href="?step=4" class="btn">Следующий шаг →</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="step <?php echo $step == '4' ? 'current' : ($step > '4' ? 'completed' : 'pending'); ?>">
            <h3>Шаг 4: Тест Telegram API</h3>
            <?php if ($step >= '4'): ?>
                <div class="result">
                    <?php
                    // Простейшая проверка через cURL если доступен
                    if (extension_loaded('curl')) {
                        echo "🔗 cURL доступен, пробуем подключение...<br>";
                        
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, TELEGRAM_API_URL . 'getMe');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        
                        $result = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        if ($result && $httpCode == 200) {
                            $response = json_decode($result, true);
                            if ($response['ok']) {
                                echo "<span class='success'>✅ Telegram API работает!</span><br>";
                                echo "🤖 Бот: " . htmlspecialchars($response['result']['first_name']) . "<br>";
                                echo "👤 @" . htmlspecialchars($response['result']['username']) . "<br>";
                            } else {
                                echo "<span class='error'>❌ Ошибка API: " . htmlspecialchars($response['description']) . "</span>";
                            }
                        } else {
                            echo "<span class='error'>❌ Нет ответа от Telegram (код: {$httpCode})</span>";
                        }
                    } else {
                        echo "<span class='error'>❌ cURL недоступен</span><br>";
                        echo "💡 Попробуем через file_get_contents...<br>";
                        
                        if (ini_get('allow_url_fopen')) {
                            echo "✅ allow_url_fopen включен<br>";
                        } else {
                            echo "<span class='error'>❌ allow_url_fopen отключен</span>";
                        }
                    }
                    ?>
                </div>
                <?php if ($step == '4'): ?>
                    <a href="?step=5" class="btn">Следующий шаг →</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="step <?php echo $step == '5' ? 'current' : ($step > '5' ? 'completed' : 'pending'); ?>">
            <h3>Шаг 5: Отправка тестового сообщения</h3>
            <?php if ($step >= '5'): ?>
                <div class="result">
                    <?php
                    if (extension_loaded('curl')) {
                        $message = "🧪 *Тестовое сообщение*\n\nПоэтапная проверка бота прошла успешно!\n\n⏰ " . date('d.m.Y H:i:s');
                        
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, TELEGRAM_API_URL . 'sendMessage');
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                            'chat_id' => TELEGRAM_CHAT_ID,
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
                            if ($response['ok']) {
                                echo "<span class='success'>✅ Сообщение отправлено в канал @reestr_garant!</span><br>";
                                echo "📨 Message ID: " . $response['result']['message_id'] . "<br>";
                            } else {
                                echo "<span class='error'>❌ Ошибка отправки: " . htmlspecialchars($response['description']) . "</span>";
                            }
                        } else {
                            echo "<span class='error'>❌ Ошибка HTTP: {$httpCode}</span>";
                        }
                    } else {
                        echo "<span class='error'>❌ cURL недоступен для отправки</span>";
                    }
                    ?>
                </div>
                <?php if ($step == '5'): ?>
                    <a href="mini_publish.php" class="btn">🚀 Попробовать мини-публикацию</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px;">
            <a href="?step=1" class="btn">🔄 Начать заново</a>
            <a href="manual_run.php" class="btn">← Назад к управлению</a>
        </div>
    </div>
</body>
</html>