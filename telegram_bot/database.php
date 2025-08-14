<?php
/**
 * Функции для работы с базой данных
 * Файл: database.php
 */

require_once 'config.php';

/**
 * Инициализация базы данных
 */
function initDatabase() {
    try {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Создание таблицы для отслеживания опубликованных новостей
        $sql = "CREATE TABLE IF NOT EXISTS published_news (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            news_id VARCHAR(255) UNIQUE NOT NULL,
            title TEXT NOT NULL,
            url TEXT NOT NULL,
            published_at DATETIME NOT NULL,
            telegram_message_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        
        // Создание таблицы для логов
        $sql_logs = "CREATE TABLE IF NOT EXISTS bot_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            context TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql_logs);
        
        logMessage('INFO', 'База данных инициализирована успешно');
        return $pdo;
        
    } catch (PDOException $e) {
        logMessage('ERROR', 'Ошибка инициализации базы данных: ' . $e->getMessage());
        return false;
    }
}

/**
 * Проверка, была ли новость уже опубликована
 */
function isNewsPublished($newsId) {
    try {
        $pdo = initDatabase();
        if (!$pdo) return false;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM published_news WHERE news_id = ?");
        $stmt->execute([$newsId]);
        
        return $stmt->fetchColumn() > 0;
        
    } catch (PDOException $e) {
        logMessage('ERROR', 'Ошибка проверки публикации новости: ' . $e->getMessage());
        return false;
    }
}

/**
 * Сохранение информации о опубликованной новости
 */
function savePublishedNews($newsId, $title, $url, $telegramMessageId = null) {
    try {
        $pdo = initDatabase();
        if (!$pdo) return false;
        
        $stmt = $pdo->prepare("INSERT INTO published_news (news_id, title, url, published_at, telegram_message_id) 
                              VALUES (?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $newsId,
            $title,
            $url,
            date('Y-m-d H:i:s'),
            $telegramMessageId
        ]);
        
        if ($result) {
            logMessage('INFO', "Новость сохранена в БД: {$title}");
        }
        
        return $result;
        
    } catch (PDOException $e) {
        logMessage('ERROR', 'Ошибка сохранения новости в БД: ' . $e->getMessage());
        return false;
    }
}

/**
 * Получение последних опубликованных новостей
 */
function getLastPublishedNews($limit = 10) {
    try {
        $pdo = initDatabase();
        if (!$pdo) return [];
        
        $stmt = $pdo->prepare("SELECT * FROM published_news ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logMessage('ERROR', 'Ошибка получения последних новостей: ' . $e->getMessage());
        return [];
    }
}

/**
 * Логирование сообщений
 */
function logMessage($level, $message, $context = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$level}: {$message}";
    
    if ($context) {
        $logEntry .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    
    $logEntry .= PHP_EOL;
    
    // Запись в файл
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Запись в базу данных
    try {
        $pdo = initDatabase();
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO bot_logs (level, message, context) VALUES (?, ?, ?)");
            $stmt->execute([$level, $message, $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : null]);
        }
    } catch (Exception $e) {
        // Игнорируем ошибки логирования в БД
    }
    
    // Вывод в консоль если включен режим отладки
    if (DEBUG_MODE) {
        echo $logEntry;
    }
}

/**
 * Очистка старых логов (старше 30 дней)
 */
function cleanOldLogs() {
    try {
        $pdo = initDatabase();
        if (!$pdo) return false;
        
        $stmt = $pdo->prepare("DELETE FROM bot_logs WHERE created_at < datetime('now', '-30 days')");
        $deletedLogs = $stmt->execute();
        
        $stmt = $pdo->prepare("DELETE FROM published_news WHERE created_at < datetime('now', '-90 days')");
        $deletedNews = $stmt->execute();
        
        logMessage('INFO', 'Очистка старых записей выполнена');
        return true;
        
    } catch (PDOException $e) {
        logMessage('ERROR', 'Ошибка очистки старых записей: ' . $e->getMessage());
        return false;
    }
}

?>