<?php
set_time_limit(0); // Убираем лимит времени
ini_set('memory_limit', '1024M'); // Увеличиваем память до 1 ГБ
ini_set('max_execution_time', 0);

// ДАННЫЕ ПОДКЛЮЧЕНИЯ К БАЗЕ
$host = 'localhost';
$port = '3306';
$dbname = 'bd';
$username = 'haibaneden';
$password = 'Stilesmile1';
$charset = 'utf8mb4';

echo "<h2>📊 Загрузчик реальных данных из production.xlsx</h2>";
echo "<div id='progress-container' style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<div id='progress-bar' style='background: #3498db; height: 20px; width: 0%; border-radius: 10px; transition: width 0.3s;'></div>";
echo "<div id='progress-text' style='text-align: center; margin-top: 10px;'>Подготовка...</div>";
echo "</div>";

// Подключаемся к базе данных
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Подключение к базе успешно<br>";
} catch(PDOException $e) {
    die("❌ Ошибка подключения: " . $e->getMessage());
}

// Создаем новую структуру таблицы под реальные данные
function createNewTableStructure($pdo) {
    echo "🔧 Создаем новую структуру таблицы...<br>";
    
    $createTable = "
    CREATE TABLE IF NOT EXISTS registry_entries_new (
        id INT AUTO_INCREMENT PRIMARY KEY,
        enterprise VARCHAR(500),
        inn VARCHAR(20),
        ogrn VARCHAR(20),
        actual_address TEXT,
        inclusion_date DATE,
        validity_period VARCHAR(100),
        actual_name TEXT,
        okpd2 VARCHAR(50),
        tn_vzd VARCHAR(50),
        manufacturer TEXT,
        points VARCHAR(20),
        compliance_info TEXT,
        conclusion_number VARCHAR(100),
        status VARCHAR(50) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_enterprise (enterprise(255)),
        INDEX idx_inn (inn),
        INDEX idx_ogrn (ogrn),
        INDEX idx_inclusion_date (inclusion_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTable);
    echo "✅ Новая таблица создана<br>";
}

// Простой парсер Excel через ZIP (без библиотек)
function parseExcelAsZip($filename) {
    echo "📂 Открываем Excel файл как ZIP архив...<br>";
    
    $zip = new ZipArchive();
    if ($zip->open($filename) !== TRUE) {
        throw new Exception("Не удалось открыть Excel файл");
    }
    
    // Читаем shared strings (текстовые данные)
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    $sharedStrings = [];
    
    if ($sharedStringsXml) {
        $xml = simplexml_load_string($sharedStringsXml);
        foreach ($xml->si as $index => $si) {
            $sharedStrings[$index] = (string)$si->t;
        }
        echo "📝 Загружено текстовых строк: " . count($sharedStrings) . "<br>";
    }
    
    // Читаем данные первого листа
    $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    
    if (!$worksheetXml) {
        throw new Exception("Не удалось найти данные листа");
    }
    
    return parseWorksheetXml($worksheetXml, $sharedStrings);
}

// Парсинг XML данных листа
function parseWorksheetXml($xmlString, $sharedStrings) {
    echo "🔄 Парсим данные листа...<br>";
    
    $xml = simplexml_load_string($xmlString);
    $rows = [];
    $rowCount = 0;
    
    foreach ($xml->sheetData->row as $row) {
        $rowData = [];
        $cellIndex = 0;
        
        foreach ($row->c as $cell) {
            $value = '';
            
            if (isset($cell->v)) {
                $cellValue = (string)$cell->v;
                
                // Проверяем тип ячейки
                $cellType = (string)$cell['t'];
                
                if ($cellType === 's' && isset($sharedStrings[$cellValue])) {
                    // Это ссылка на shared string
                    $value = $sharedStrings[$cellValue];
                } else {
                    $value = $cellValue;
                }
            }
            
            $rowData[] = $value;
            $cellIndex++;
        }
        
        $rows[] = $rowData;
        $rowCount++;
        
        // Показываем прогресс каждые 1000 строк
        if ($rowCount % 1000 == 0) {
            echo "<script>
                document.getElementById('progress-text').innerHTML = 'Парсинг: $rowCount строк...';
                document.getElementById('progress-bar').style.width = '30%';
            </script>";
            flush();
            ob_flush();
        }
    }
    
    echo "✅ Распarsено строк: $rowCount<br>";
    return $rows;
}

// Импорт данных в базу порциями
function importDataToDatabase($rows, $pdo) {
    echo "💾 Импортируем данные в базу...<br>";
    
    // Очищаем старую таблицу
    $pdo->exec("DROP TABLE IF EXISTS registry_entries_old");
    $pdo->exec("RENAME TABLE registry_entries TO registry_entries_old");
    $pdo->exec("RENAME TABLE registry_entries_new TO registry_entries");
    
    $totalRows = count($rows);
    $batchSize = 500; // Обрабатываем по 500 строк
    $imported = 0;
    $errors = 0;
    
    // Подготавливаем запрос
    $sql = "INSERT INTO registry_entries 
            (enterprise, inn, ogrn, actual_address, inclusion_date, validity_period, 
             actual_name, okpd2, tn_vzd, manufacturer, points, compliance_info, conclusion_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // Пропускаем заголовок
    $isHeader = true;
    
    foreach ($rows as $index => $row) {
        if ($isHeader) {
            echo "📋 Заголовки: " . implode(" | ", array_slice($row, 0, 5)) . "...<br>";
            $isHeader = false;
            continue;
        }
        
        try {
            // Извлекаем данные из строки (настраиваем под ваши колонки)
            $enterprise = trim($row[0] ?? '');
            $inn = trim($row[1] ?? '');
            $ogrn = trim($row[2] ?? '');
            $actualAddress = trim($row[3] ?? '');
            $inclusionDate = parseDate($row[4] ?? '');
            $validityPeriod = trim($row[5] ?? '');
            $actualName = trim($row[6] ?? '');
            $okpd2 = trim($row[7] ?? '');
            $tnVzd = trim($row[8] ?? '');
            $manufacturer = trim($row[9] ?? '');
            $points = trim($row[10] ?? '');
            $complianceInfo = trim($row[11] ?? '');
            
            // Генерируем номер заключения если его нет
            $conclusionNumber = generateConclusionNumber($inn, $inclusionDate);
            
            // Пропускаем пустые строки
            if (empty($enterprise) && empty($actualName)) {
                continue;
            }
            
            $stmt->execute([
                $enterprise,
                $inn,
                $ogrn,
                $actualAddress,
                $inclusionDate,
                $validityPeriod,
                $actualName,
                $okpd2,
                $tnVzd,
                $manufacturer,
                $points,
                $complianceInfo,
                $conclusionNumber
            ]);
            
            $imported++;
            
            // Обновляем прогресс
            if ($imported % 100 == 0) {
                $progress = round(($imported / $totalRows) * 100);
                echo "<script>
                    document.getElementById('progress-text').innerHTML = 'Импортировано: $imported из $totalRows записей ($progress%)';
                    document.getElementById('progress-bar').style.width = '{$progress}%';
                </script>";
                flush();
                ob_flush();
            }
            
        } catch (Exception $e) {
            $errors++;
            if ($errors < 10) { // Показываем только первые 10 ошибок
                echo "⚠️ Ошибка в строке " . ($index + 1) . ": " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "✅ Импорт завершен!<br>";
    echo "📊 Импортировано: $imported записей<br>";
    echo "⚠️ Ошибок: $errors<br>";
    
    return $imported;
}

// Функция парсинга даты
function parseDate($dateStr) {
    if (empty($dateStr) || $dateStr === '0') {
        return null;
    }
    
    // Пробуем разные форматы даты
    $formats = ['Y-m-d', 'd.m.Y', 'd/m/Y', 'Y-m-d H:i:s'];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateStr);
        if ($date) {
            return $date->format('Y-m-d');
        }
    }
    
    // Если это Excel timestamp
    if (is_numeric($dateStr) && $dateStr > 25569) {
        $unixTimestamp = ($dateStr - 25569) * 86400;
        return date('Y-m-d', $unixTimestamp);
    }
    
    return null;
}

// Генерация номера заключения
function generateConclusionNumber($inn, $date) {
    $year = $date ? date('Y', strtotime($date)) : date('Y');
    $shortInn = substr($inn, -4);
    $random = rand(100, 999);
    return "$shortInn.$random.$year";
}

// Обновление API для новой структуры
function updateApiFile() {
    $apiContent = '<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$port = "3306";
$dbname = "bd";
$username = "haibaneden";
$password = "Stilesmile1";
$charset = "utf8mb4";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "error" => "DB Error"]);
    exit;
}

$page = max(1, (int)($_GET["page"] ?? 1));
$limit = min(max(1, (int)($_GET["limit"] ?? 50)), 100);
$search = trim($_GET["search"] ?? "");
$status = trim($_GET["status"] ?? "");

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(enterprise LIKE ? OR actual_name LIKE ? OR inn LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$countSql = "SELECT COUNT(*) FROM registry_entries $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();

$offset = ($page - 1) * $limit;
$dataSql = "
    SELECT 
        conclusion_number,
        enterprise as company_name,
        actual_name as product_name,
        actual_address as region,
        inclusion_date,
        status,
        inn,
        ogrn,
        okpd2,
        manufacturer
    FROM registry_entries 
    $whereClause 
    ORDER BY inclusion_date DESC 
    LIMIT $limit OFFSET $offset
";

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
        "total_records" => $totalRecords,
        "total_pages" => ceil($totalRecords / $limit),
        "per_page" => $limit
    ]
]);
?>';
    
    file_put_contents('registry_api.php', $apiContent);
    echo "✅ API файл обновлен для новой структуры<br>";
}

// ОСНОВНАЯ ЛОГИКА
echo "<hr>";

try {
    // Проверяем наличие файла
    $excelFile = 'production.xlsx';
    if (!file_exists($excelFile)) {
        throw new Exception("Файл production.xlsx не найден в корне сайта");
    }
    
    $fileSize = filesize($excelFile);
    echo "📁 Найден файл: $excelFile (" . round($fileSize/1024/1024, 2) . " МБ)<br>";
    
    // Создаем новую структуру таблицы
    createNewTableStructure($pdo);
    
    // Парсим Excel файл
    echo "<script>
        document.getElementById('progress-text').innerHTML = 'Читаем Excel файл...';
        document.getElementById('progress-bar').style.width = '10%';
    </script>";
    flush();
    
    $rows = parseExcelAsZip($excelFile);
    
    echo "<script>
        document.getElementById('progress-text').innerHTML = 'Импортируем в базу данных...';
        document.getElementById('progress-bar').style.width = '50%';
    </script>";
    flush();
    
    // Импортируем данные
    $imported = importDataToDatabase($rows, $pdo);
    
    // Обновляем API
    updateApiFile();
    
    echo "<script>
        document.getElementById('progress-text').innerHTML = 'Готово! Импортировано: $imported записей';
        document.getElementById('progress-bar').style.width = '100%';
    </script>";
    
    // Записываем время обновления
    file_put_contents('last_update.txt', date('Y-m-d H:i:s') . " - Real data from production.xlsx");
    
    // Статистика
    echo "<hr>";
    echo "<h3>📈 Финальная статистика:</h3>";
    
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(DISTINCT inn) as unique_companies,
            COUNT(DISTINCT okpd2) as unique_okpd,
            MIN(inclusion_date) as earliest_date,
            MAX(inclusion_date) as latest_date
        FROM registry_entries
    ")->fetch();
    
    echo "📊 <strong>Всего записей:</strong> {$stats['total']}<br>";
    echo "🏢 <strong>Уникальных предприятий:</strong> {$stats['unique_companies']}<br>";
    echo "📋 <strong>Уникальных ОКПД2:</strong> {$stats['unique_okpd']}<br>";
    echo "📅 <strong>Период данных:</strong> {$stats['earliest_date']} - {$stats['latest_date']}<br>";
    
    // Примеры записей
    echo "<h4>📋 Примеры загруженных записей:</h4>";
    $examples = $pdo->query("SELECT * FROM registry_entries ORDER BY inclusion_date DESC LIMIT 3")->fetchAll();
    foreach ($examples as $example) {
        echo "• <strong>{$example['enterprise']}</strong><br>";
        echo "&nbsp;&nbsp;{$example['actual_name']}<br>";
        echo "&nbsp;&nbsp;📍 ИНН: {$example['inn']} | 📅 {$example['inclusion_date']}<br><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
    echo "💡 Убедитесь, что файл production.xlsx загружен в корень сайта<br>";
}

echo '<hr>';
echo '<div style="text-align: center; margin-top: 30px;">';
echo '<a href="registry_table.html" style="background: linear-gradient(45deg, #27ae60, #229954); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold; margin-right: 15px;">📊 Открыть таблицу с РЕАЛЬНЫМИ данными</a>';
echo '</div>';
?>

<script>
// Автообновление страницы статистики
setTimeout(function() {
    if (document.getElementById('progress-bar').style.width === '100%') {
        console.log('Импорт завершен успешно!');
    }
}, 1000);
</script>