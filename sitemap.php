<?php
/**
 * Автоматическая генерация sitemap.xml
 */

// Подключаем модель новостей
require_once __DIR__ . '/models/News.php';

// Устанавливаем правильный Content-Type
header('Content-Type: application/xml; charset=utf-8');

// Начинаем генерацию XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Главная страница -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Основные разделы услуг -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/radioelectronic</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc>https://vnesenie-v-reestr.ru/industrial</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc>https://vnesenie-v-reestr.ru/software</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc>https://vnesenie-v-reestr.ru/medical-devices</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc>https://vnesenie-v-reestr.ru/telecom-equipment</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc>https://vnesenie-v-reestr.ru/oil-gas-equipment</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <!-- Раздел новостей -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/news/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

<?php
// Получаем все опубликованные новости из базы данных
try {
    $newsModel = new News();
    $categoryModel = new NewsCategory();
    
    // Получаем все новости (без пагинации для sitemap)
    $allNews = $newsModel->getAllPublishedNews();
    
    // Добавляем URL для каждой новости
    foreach ($allNews as $news) {
        $lastmod = $news['updated_at'] ? date('Y-m-d', strtotime($news['updated_at'])) : date('Y-m-d', strtotime($news['published_at']));
        echo "    <url>" . PHP_EOL;
        echo "        <loc>https://vnesenie-v-reestr.ru/news/" . htmlspecialchars($news['slug']) . "</loc>" . PHP_EOL;
        echo "        <lastmod>" . $lastmod . "</lastmod>" . PHP_EOL;
        echo "        <changefreq>monthly</changefreq>" . PHP_EOL;
        echo "        <priority>0.7</priority>" . PHP_EOL;
        echo "    </url>" . PHP_EOL;
    }
    
    // Получаем все активные категории новостей
    $categories = $categoryModel->getActiveCategories();
    
    // Добавляем URL для каждой категории новостей
    foreach ($categories as $category) {
        echo "    <url>" . PHP_EOL;
        echo "        <loc>https://vnesenie-v-reestr.ru/news/?category=" . $category['id'] . "</loc>" . PHP_EOL;
        echo "        <lastmod>" . date('Y-m-d') . "</lastmod>" . PHP_EOL;
        echo "        <changefreq>weekly</changefreq>" . PHP_EOL;
        echo "        <priority>0.6</priority>" . PHP_EOL;
        echo "    </url>" . PHP_EOL;
    }
    
} catch (Exception $e) {
    // В случае ошибки с базой данных, просто продолжаем без новостей
    error_log("Ошибка генерации sitemap: " . $e->getMessage());
}
?>

    <!-- Дополнительные страницы -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/privacy</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <url>
        <loc>https://vnesenie-v-reestr.ru/terms</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <!-- Файл для Яндекс верификации -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/yandex_91a39aef3e2de27a.html</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>never</changefreq>
        <priority>0.1</priority>
    </url>
</urlset>