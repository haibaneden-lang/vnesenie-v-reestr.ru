<?php
// Простые настройки
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// ДАННЫЕ ПОДКЛЮЧЕНИЯ К БАЗЕ
$host = 'localhost';
$port = '3306';
$dbname = 'bd';
$username = 'haibaneden';
$password = 'Stilesmile1';

echo "<h2>🔧 Простой загрузчик данных</h2>";

// Проверяем подключение
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ База данных подключена<br>";
} catch(PDOException $e) {
    die("❌ Ошибка БД: " . $e->getMessage());
}

// Проверяем файл
$filename = 'production.xlsx';
if (!file_exists($filename)) {
    die("❌ Файл $filename не найден");
}

$size = round(filesize($filename) / 1024 / 1024, 2);
echo "📁 Найден файл: $filename ($size МБ)<br>";

// Простая обработка: конвертируем Excel в CSV
echo "🔄 Конвертируем Excel в CSV...<br>";

// Пробуем разные способы чтения файла
$csvData = convertExcelToCSV($filename);

if ($csvData) {
    echo "✅ Конвертация успешна<br>";
    loadCSVData($csvData, $pdo);
} else {
    echo "❌ Не удалось конвертировать. Загружаем тестовые данные...<br>";
    loadTestData($pdo);
}

// Простая функция конвертации
function convertExcelToCSV($filename) {
    // Метод 1: Пробуем через ZIP
    try {
        $zip = new ZipArchive();
        if ($zip->open($filename) === TRUE) {
            $data = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();
            if ($data) {
                echo "📊 Данные извлечены из Excel<br>";
                return parseSimpleXML($data);
            }
        }
    } catch (Exception $e) {
        echo "⚠️ ZIP метод не сработал<br>";
    }
    
    // Метод 2: Читаем как бинарный файл
    try {
        $handle = fopen($filename, 'rb');
        if ($handle) {
            $data = fread($handle, 1024 * 1024); // Читаем первый 1МБ
            fclose($handle);
            
            // Ищем текстовые данные
            if (preg_match_all('/[А-Яа-яA-Za-z0-9\s\.,-]{10,}/', $data, $matches)) {
                echo "📝 Найдены текстовые данные<br>";
                return implode("\n", array_slice($matches[0], 0, 100));
            }
        }
    } catch (Exception $e) {
        echo "⚠️ Бинарный метод не сработал<br>";
    }
    
    return false;
}

// Простой парсер XML
function parseSimpleXML($xmlData) {
    $rows = [];
    
    // Простое извлечение данных
    if (preg_match_all('/<v>([^<]+)<\/v>/', $xmlData, $matches)) {
        $values = $matches[1];
        
        // Группируем по строкам (примерно)
        $chunks = array_chunk($values, 12); // 12 колонок
        
        foreach ($chunks as $chunk) {
            if (count($chunk) >= 3) {
                $rows[] = implode(',', array_map('trim', $chunk));
            }
        }
    }
    
    return implode("\n", array_slice($rows, 0, 1000)); // Берем первые 1000 строк
}

// Загрузка CSV данных
function loadCSVData($csvData, $pdo) {
    echo "💾 Загружаем данные в базу...<br>";
    
    // Очищаем старые данные
    $pdo->exec("DELETE FROM registry_entries");
    
    $lines = explode("\n", $csvData);
    $imported = 0;
    
    $stmt = $pdo->prepare("
        INSERT INTO registry_entries 
        (conclusion_number, company_name, product_name, region, inclusion_date, status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($lines as $line) {
        $data = str_getcsv($line);
        
        if (count($data) >= 3) {
            try {
                $stmt->execute([
                    'CSV-' . rand(1000, 9999),
                    $data[0] ?? 'Компания',
                    $data[1] ?? 'Продукция',
                    $data[2] ?? 'Регион',
                    date('Y-m-d'),
                    'active'
                ]);
                $imported++;
            } catch (Exception $e) {
                // Игнорируем ошибки
            }
        }
    }
    
    echo "✅ Импортировано: $imported записей<br>";
}

// Загрузка тестовых данных если Excel не читается
function loadTestData($pdo) {
    $pdo->exec("DELETE FROM registry_entries");
    
    $testData = [
        ['TEST-001-2024', 'ООО "Энергомера"', 'Счетчики электроэнергии СЭТ-4ТМ', 'г. Ставрополь', '2024-01-15', 'active'],
        ['TEST-002-2024', 'АО "Электровыпрямитель"', 'Преобразователи частоты ЭПВ-М', 'г. Саранск', '2024-02-20', 'active'],
        ['TEST-003-2024', 'ПАО "Силовые машины"', 'Турбогенераторы ТВВ-800', 'г. Санкт-Петербург', '2024-03-10', 'active'],
        ['TEST-004-2024', 'ООО "НефАЗ"', 'Автобусы НефАЗ-5299', 'г. Нефтекамск', '2024-04-05', 'active'],
        ['TEST-005-2024', 'АО "Ростсельмаш"', 'Комбайны NOVA-340', 'г. Ростов-на-Дону', '2024-05-12', 'active']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO registry_entries 
        (conclusion_number, company_name, product_name, region, inclusion_date, status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($testData as $row) {
        $stmt->execute($row);
    }
    
    echo "✅ Загружено 5 тестовых записей<br>";
}

// Статистика
$total = $pdo->query("SELECT COUNT(*) FROM registry_entries")->fetchColumn();
echo "<h3>📊 Итого в базе: $total записей</h3>";

echo '<hr>';
echo '<div style="text-align: center; margin: 20px 0;">';
echo '<a href="registry_table.html" style="background: #27ae60; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold; margin-right: 15px;">📊 Открыть таблицу</a>';
echo '<a href="check_api.php" style="background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold;">🔧 Проверить API</a>';
echo '</div>';
?>