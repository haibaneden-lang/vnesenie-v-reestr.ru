<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Отладка методов Database</h1>";

try {
    require_once __DIR__ . '/config/database.php';
    
    $db = getDatabase();
    echo "<p>✅ База данных подключена</p>";
    echo "<p>Тип объекта: " . get_class($db) . "</p>";
    
    echo "<h3>Доступные методы:</h3>";
    $methods = get_class_methods($db);
    echo "<ul>";
    foreach ($methods as $method) {
        echo "<li>$method</li>";
    }
    echo "</ul>";
    
    echo "<h3>Проверяем нужные методы:</h3>";
    echo "<p>execute(): " . (method_exists($db, 'execute') ? '✅ есть' : '❌ нет') . "</p>";
    echo "<p>query(): " . (method_exists($db, 'query') ? '✅ есть' : '❌ нет') . "</p>";
    echo "<p>fetchOne(): " . (method_exists($db, 'fetchOne') ? '✅ есть' : '❌ нет') . "</p>";
    echo "<p>fetchAll(): " . (method_exists($db, 'fetchAll') ? '✅ есть' : '❌ нет') . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Ошибка: " . $e->getMessage() . "</p>";
}
?>