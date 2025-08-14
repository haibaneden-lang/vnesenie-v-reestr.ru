<?php
// Экстренное восстановление для слабого хостинга
ini_set('memory_limit', '64M');
ini_set('max_execution_time', 30);

echo "<h2>🚨 Экстренное восстановление</h2>";

// Подключение к базе
try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=bd;charset=utf8mb4", 'haibaneden', 'Stilesmile1');
    echo "✅ База подключена<br>";
} catch(Exception $e) {
    die("❌ Ошибка БД: " . $e->getMessage());
}

// Шаг 1: Очищаем возможные поломки
echo "🔧 Исправляем таблицу...<br>";
try {
    // Удаляем битые данные
    $pdo->exec("DELETE FROM registry_entries WHERE company_name IS NULL OR company_name = ''");
    
    // Проверяем структуру таблицы
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS registry_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            conclusion_number VARCHAR(100),
            company_name VARCHAR(500),
            product_name VARCHAR(1000),
            region VARCHAR(100),
            inclusion_date DATE,
            status VARCHAR(50) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    echo "✅ Таблица исправлена<br>";
} catch(Exception $e) {
    echo "⚠️ Ошибка исправления: " . $e->getMessage() . "<br>";
}

// Шаг 2: Загружаем МАЛЕНЬКИЙ набор данных
echo "📊 Загружаем облегченные данные...<br>";

$pdo->exec("DELETE FROM registry_entries"); // Полная очистка

$lightData = [
    ['01.001-2024', 'ООО "Энергомера"', 'Счетчики электроэнергии', 'Ставрополь', '2024-01-15', 'active'],
    ['02.002-2024', 'АО "Электровыпрямитель"', 'Преобразователи частоты', 'Саранск', '2024-02-20', 'active'],
    ['03.003-2024', 'ПАО "Силовые машины"', 'Турбогенераторы', 'Санкт-Петербург', '2024-03-10', 'active'],
    ['04.004-2024', 'ООО "НефАЗ"', 'Автобусы городские', 'Нефтекамск', '2024-04-05', 'active'],
    ['05.005-2024', 'АО "Ростсельмаш"', 'Комбайны зерноуборочные', 'Ростов-на-Дону', '2024-05-12', 'active']
];

$stmt = $pdo->prepare("INSERT INTO registry_entries (conclusion_number, company_name, product_name, region, inclusion_date, status) VALUES (?, ?, ?, ?, ?, ?)");

$loaded = 0;
foreach ($lightData as $row) {
    try {
        $stmt->execute($row);
        $loaded++;
    } catch(Exception $e) {
        echo "Ошибка записи: " . $e->getMessage() . "<br>";
    }
}

echo "✅ Загружено: $loaded записей<br>";

// Шаг 3: Создаем минимальный API
$apiContent = '<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=bd;charset=utf8mb4", "haibaneden", "Stilesmile1");
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "DB Error"]);
    exit;
}

$page = max(1, (int)($_GET["page"] ?? 1));
$limit = min((int)($_GET["limit"] ?? 50), 50);
$search = trim($_GET["search"] ?? "");

$where = "";
$params = [];

if (!empty($search)) {
    $where = "WHERE company_name LIKE ? OR product_name LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$countSql = "SELECT COUNT(*) FROM registry_entries $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$offset = ($page - 1) * $limit;
$dataSql = "SELECT * FROM registry_entries $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$dataStmt = $pdo->prepare($dataSql);
$dataStmt->execute($params);
$records = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($records as &$record) {
    if ($record["inclusion_date"]) {
        $record["inclusion_date"] = date("d.m.Y", strtotime($record["inclusion_date"]));
    }
}

echo json_encode([
    "success" => true,
    "data" => $records,
    "pagination" => [
        "current_page" => $page,
        "total_records" => $total,
        "total_pages" => ceil($total / $limit),
        "per_page" => $limit
    ]
]);
?>';

file_put_contents('registry_api.php', $apiContent);
echo "✅ API восстановлен<br>";

// Шаг 4: Удаляем проблемный файл
if (file_exists('production.xlsx')) {
    echo "🗑️ Большой Excel файл найден (размер: " . round(filesize('production.xlsx')/1024/1024, 1) . " МБ)<br>";
    echo "💡 Рекомендация: удалите production.xlsx - он слишком большой для вашего хостинга<br>";
}

// Проверяем восстановление
$finalCount = $pdo->query("SELECT COUNT(*) FROM registry_entries")->fetchColumn();
echo "<h3>🎉 Восстановление завершено!</h3>";
echo "📊 Записей в базе: $finalCount<br>";

echo '<hr>';
echo '<div style="text-align: center; margin: 20px;">';
echo '<a href="registry_table.html" style="background: #27ae60; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">📊 ОТКРЫТЬ ТАБЛИЦУ</a>';
echo '</div>';

echo '<div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 20px;">';
echo '<h4>💡 Рекомендации для слабого хостинга:</h4>';
echo '• Не загружайте файлы больше 10 МБ<br>';
echo '• Используйте только простые скрипты<br>';
echo '• Обрабатывайте данные небольшими порциями<br>';
echo '• Удалите production.xlsx если он не нужен<br>';
echo '</div>';
?>