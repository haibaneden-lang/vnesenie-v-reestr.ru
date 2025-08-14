<?php
/**
 * Файл для диагностики проблем с RSS
 * Разместить в корне сайта как debug-rss.php
 * Доступ: https://vnesenie-v-reestr.ru/debug-rss.php
 */

// Включаем отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Диагностика RSS системы</h1>\n";

// 1. Проверяем существование файлов
echo "<h2>1. Проверка файлов</h2>\n";

$files_to_check = [
    'models/News.php',
    'config/rss-zen-config.php',
    'rss-zen.php',
    'rss-zen-advanced.php'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ {$file} - найден<br>\n";
    } else {
        echo "❌ {$file} - НЕ НАЙДЕН<br>\n";
    }
}

// 2. Проверяем подключение модели
echo "<h2>2. Проверка модели News</h2>\n";

try {
    if (file_exists(__DIR__ . '/models/News.php')) {
        require_once __DIR__ . '/models/News.php';
        echo "✅ Модель News.php подключена успешно<br>\n";
        
        // Проверяем класс News
        if (class_exists('News')) {
            echo "✅ Класс News существует<br>\n";
            
            try {
                $newsModel = new News();
                echo "✅ Экземпляр класса News создан успешно<br>\n";
                
                // Проверяем методы
                $methods = ['getPublishedNews', 'getNewsBySlug', 'getAllPublishedNews'];
                foreach ($methods as $method) {
                    if (method_exists($newsModel, $method)) {
                        echo "✅ Метод {$method} существует<br>\n";
                    } else {
                        echo "❌ Метод {$method} НЕ НАЙДЕН<br>\n";
                    }
                }
                
            } catch (Exception $e) {
                echo "❌ Ошибка создания экземпляра News: " . $e->getMessage() . "<br>\n";
            }
        } else {
            echo "❌ Класс News НЕ НАЙДЕН<br>\n";
        }
        
        // Проверяем класс NewsCategory
        if (class_exists('NewsCategory')) {
            echo "✅ Класс NewsCategory существует<br>\n";
            
            try {
                $categoryModel = new NewsCategory();
                echo "✅ Экземпляр класса NewsCategory создан успешно<br>\n";
                
                // Проверяем методы
                $methods = ['getCategoryById', 'getActiveCategories'];
                foreach ($methods as $method) {
                    if (method_exists($categoryModel, $method)) {
                        echo "✅ Метод {$method} существует<br>\n";
                    } else {
                        echo "❌ Метод {$method} НЕ НАЙДЕН<br>\n";
                    }
                }
                
            } catch (Exception $e) {
                echo "❌ Ошибка создания экземпляра NewsCategory: " . $e->getMessage() . "<br>\n";
            }
        } else {
            echo "❌ Класс NewsCategory НЕ НАЙДЕН<br>\n";
        }
        
    } else {
        echo "❌ Файл models/News.php не найден<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка подключения модели: " . $e->getMessage() . "<br>\n";
}

// 3. Тестируем получение новостей
echo "<h2>3. Тестирование получения новостей</h2>\n";

try {
    if (isset($newsModel)) {
        $news = $newsModel->getPublishedNews(1, 5);
        echo "✅ Получено новостей: " . count($news) . "<br>\n";
        
        if (!empty($news)) {
            echo "<h3>Пример новости:</h3>\n";
            $first_news = $news[0];
            echo "<pre>" . print_r($first_news, true) . "</pre>\n";
        }
    } else {
        echo "❌ Модель News не инициализирована<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка получения новостей: " . $e->getMessage() . "<br>\n";
}

// 4. Проверяем базу данных
echo "<h2>4. Проверка соединения с базой данных</h2>\n";

try {
    // Попробуем найти конфигурацию базы данных
    $db_configs = [
        'config/database.php',
        'database.php',
        'config/config.php'
    ];
    
    $db_config_found = false;
    foreach ($db_configs as $config) {
        if (file_exists(__DIR__ . '/' . $config)) {
            echo "✅ Найден конфигурационный файл: {$config}<br>\n";
            $db_config_found = true;
            break;
        }
    }
    
    if (!$db_config_found) {
        echo "❌ Конфигурационный файл базы данных не найден<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка проверки конфигурации: " . $e->getMessage() . "<br>\n";
}

// 5. Проверяем права доступа
echo "<h2>5. Проверка прав доступа</h2>\n";

$paths_to_check = [
    __DIR__ . '/logs',
    __DIR__ . '/models',
    __DIR__ . '/config'
];

foreach ($paths_to_check as $path) {
    if (is_dir($path)) {
        if (is_readable($path)) {
            echo "✅ {$path} - доступен для чтения<br>\n";
        } else {
            echo "❌ {$path} - НЕ доступен для чтения<br>\n";
        }
        
        if (is_writable($path)) {
            echo "✅ {$path} - доступен для записи<br>\n";
        } else {
            echo "⚠️ {$path} - НЕ доступен для записи<br>\n";
        }
    } else {
        echo "❌ {$path} - директория не существует<br>\n";
    }
}

// 6. Проверяем RSS ленты
echo "<h2>6. Проверка RSS лент</h2>\n";

$rss_files = ['rss-zen.php', 'rss-zen-advanced.php'];

foreach ($rss_files as $rss_file) {
    if (file_exists(__DIR__ . '/' . $rss_file)) {
        echo "✅ {$rss_file} существует<br>\n";
        echo "🔗 <a href='/{$rss_file}' target='_blank'>Проверить {$rss_file}</a><br>\n";
    } else {
        echo "❌ {$rss_file} НЕ НАЙДЕН<br>\n";
    }
}

// 7. Проверяем PHP расширения
echo "<h2>7. Проверка PHP расширений</h2>\n";

$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'simplexml'];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Расширение {$ext} загружено<br>\n";
    } else {
        echo "❌ Расширение {$ext} НЕ ЗАГРУЖЕНО<br>\n";
    }
}

echo "<h2>Диагностика завершена</h2>\n";
echo "<p>После устранения ошибок удалите этот файл!</p>\n";
?>