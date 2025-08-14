<?php
echo "<h1>🔍 Диагностика структуры сайта</h1>";

echo "<h3>Информация о сервере:</h3>";
echo "<p><strong>Текущая папка:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Документ рут:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<h3>Проверка папок:</h3>";
$folders_to_check = ['admin', 'models', 'news', 'certification', 'certificatio'];

foreach ($folders_to_check as $folder) {
    $path = __DIR__ . '/' . $folder;
    $exists = is_dir($path);
    $readable = $exists ? is_readable($path) : false;
    
    echo "<p><strong>$folder/:</strong> ";
    if ($exists) {
        echo "✅ Существует";
        if ($readable) {
            echo " (доступна для чтения)";
        } else {
            echo " ❌ (НЕ доступна для чтения)";
        }
    } else {
        echo "❌ НЕ существует";
    }
    echo "</p>";
}

echo "<h3>Проверка файлов:</h3>";
$files_to_check = [
    'models/CertificationPages.php',
    'certification/index.php',
    'certification/test.php',
    'certificatio/index.php'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    
    echo "<p><strong>$file:</strong> ";
    if ($exists) {
        echo "✅ Существует";
        if ($readable) {
            echo " (доступен для чтения)";
        } else {
            echo " ❌ (НЕ доступен для чтения)";
        }
    } else {
        echo "❌ НЕ существует";
    }
    echo "</p>";
}

echo "<h3>Содержимое корневой папки:</h3>";
$files = scandir(__DIR__);
echo "<pre>";
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $is_dir = is_dir(__DIR__ . '/' . $file);
        echo ($is_dir ? '📁 ' : '📄 ') . $file . "\n";
    }
}
echo "</pre>";
?>