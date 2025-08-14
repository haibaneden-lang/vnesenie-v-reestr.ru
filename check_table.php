<?php
/**
 * Простая проверка таблицы leads
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Проверка таблицы leads</h1>";

require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDbConnection();
    
    if (!$pdo) {
        echo "❌ Нет подключения к базе данных";
        exit;
    }
    
    echo "✅ Подключение к базе данных успешно<br>";
    
    // Проверяем существование таблицы
    $stmt = $pdo->query("SHOW TABLES LIKE 'leads'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ Таблица 'leads' существует<br>";
        
        // Показываем структуру
        $stmt = $pdo->query("DESCRIBE leads");
        $columns = $stmt->fetchAll();
        
        echo "<h3>📋 Структура таблицы leads:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th><th>По умолчанию</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Показываем количество записей
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM leads");
        $count = $stmt->fetch()['total'];
        echo "<h3>📊 Количество записей: " . $count . "</h3>";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
            $leads = $stmt->fetchAll();
            
            echo "<h3>📝 Последние 5 leads:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Имя</th><th>Email</th><th>Услуга</th><th>Дата</th></tr>";
            
            foreach ($leads as $lead) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($lead['id']) . "</td>";
                echo "<td>" . htmlspecialchars($lead['name']) . "</td>";
                echo "<td>" . htmlspecialchars($lead['email']) . "</td>";
                echo "<td>" . htmlspecialchars($lead['service']) . "</td>";
                echo "<td>" . htmlspecialchars($lead['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "❌ Таблица 'leads' НЕ существует<br>";
        echo "<h3>🔧 Создание таблицы leads:</h3>";
        
        $sql = "CREATE TABLE leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            company VARCHAR(255),
            message TEXT,
            service VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            page_url TEXT,
            utm_source VARCHAR(255),
            utm_medium VARCHAR(255),
            utm_campaign VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            site_name VARCHAR(255) DEFAULT 'Реестр Гарант'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $pdo->exec($sql);
            echo "✅ Таблица 'leads' успешно создана!<br>";
        } catch (Exception $e) {
            echo "❌ Ошибка создания таблицы: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
