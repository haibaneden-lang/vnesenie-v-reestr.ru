<?php
/**
 * Автоматическая генерация sitemap.xml
 */

// Включаем обработку ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('max_execution_time', 30); // Увеличиваем время выполнения

// Регистрируем обработчик для гарантированного закрытия XML
register_shutdown_function(function() {
    $error = error_get_last();
    // Всегда выводим закрывающий тег при завершении
    if (!headers_sent()) {
        $output = ob_get_contents();
        if ($output && strpos($output, '</urlset>') === false) {
            echo "</urlset>" . PHP_EOL;
        }
    }
});

// Подключаем модель новостей
try {
    require_once __DIR__ . '/models/News.php';
} catch (Exception $e) {
    error_log("Ошибка подключения News.php: " . $e->getMessage());
}

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

    <url>
        <loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-utilizatorov/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
<?php
// Страницы «Включение в реестр утилизаторов» по городам
try {
    if (file_exists(__DIR__ . '/vklyuchenie-v-reestr-utilizatorov/cities.php')) {
        require_once __DIR__ . '/vklyuchenie-v-reestr-utilizatorov/cities.php';
        if (function_exists('getAllCities')) {
            $utilizatorCities = getAllCities();
            if (!empty($utilizatorCities) && is_array($utilizatorCities)) {
                foreach ($utilizatorCities as $slug => $cityData) {
                    if (empty($slug)) continue;
                    echo "    <url>" . PHP_EOL;
                    echo "        <loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-utilizatorov/" . htmlspecialchars($slug) . "</loc>" . PHP_EOL;
                    echo "        <lastmod>" . date('Y-m-d') . "</lastmod>" . PHP_EOL;
                    echo "        <changefreq>monthly</changefreq>" . PHP_EOL;
                    echo "        <priority>0.8</priority>" . PHP_EOL;
                    echo "    </url>" . PHP_EOL;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Ошибка генерации городов утилизаторов в sitemap: " . $e->getMessage());
}
?>
    <!-- Статус резидента Сколково (мультигео) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/moskva</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/sankt-peterburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/novosibirsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/yekaterinburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/kazan</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/nizhniy-novgorod</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/chelyabinsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/samara</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/omsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/rostov-na-donu</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/ufa</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/krasnoyarsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/voronezh</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/perm</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/volgograd</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/krasnodar</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/saratov</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/tyumen</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/tolyatti</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/izhevsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/barnaul</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/ulyanovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/irkutsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/habarovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/vladivostok</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <!-- Включение в реестр МТК (мультигео) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/moskva</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/sankt-peterburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/novosibirsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/yekaterinburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/kazan</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/nizhniy-novgorod</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/chelyabinsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/samara</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/omsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/rostov-na-donu</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/ufa</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/krasnoyarsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/voronezh</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/perm</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/volgograd</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/krasnodar</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/saratov</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/tyumen</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/tolyatti</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/izhevsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/barnaul</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/ulyanovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/irkutsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/habarovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/vladivostok</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <!-- Аккредитация IT-компаний (мультигео) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/moskva</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/sankt-peterburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/novosibirsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/yekaterinburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/kazan</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/nizhniy-novgorod</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/chelyabinsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/samara</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/omsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/rostov-na-donu</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/ufa</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/krasnoyarsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/voronezh</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/perm</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/volgograd</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/krasnodar</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/saratov</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/tyumen</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/tolyatti</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/izhevsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/barnaul</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/ulyanovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/irkutsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/habarovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/vladivostok</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <!-- Тендерное сопровождение (мультигео) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/moskva</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/sankt-peterburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/novosibirsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/yekaterinburg</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/kazan</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/nizhniy-novgorod</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/chelyabinsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/samara</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/omsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/rostov-na-donu</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/ufa</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/krasnoyarsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/voronezh</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/perm</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/volgograd</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/krasnodar</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/saratov</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/tyumen</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/tolyatti</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/izhevsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/barnaul</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/ulyanovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/irkutsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/habarovsk</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <url><loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/vladivostok</loc><lastmod><?php echo date('Y-m-d'); ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
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
    
    // Получаем все новости (без пагинации для sitemap)
    $allNews = $newsModel->getAllPublishedNews();

    // Добавляем URL для каждой новости
    if (!empty($allNews)) {
        foreach ($allNews as $news) {
            if (empty($news['slug'])) continue;
            $lastmod = !empty($news['updated_at']) ? date('Y-m-d', strtotime($news['updated_at'])) : (!empty($news['published_at']) ? date('Y-m-d', strtotime($news['published_at'])) : date('Y-m-d'));
            echo "    <url>" . PHP_EOL;
            echo "        <loc>https://vnesenie-v-reestr.ru/news/" . htmlspecialchars($news['slug']) . "</loc>" . PHP_EOL;
            echo "        <lastmod>" . htmlspecialchars($lastmod) . "</lastmod>" . PHP_EOL;
            echo "        <changefreq>monthly</changefreq>" . PHP_EOL;
            echo "        <priority>0.7</priority>" . PHP_EOL;
            echo "    </url>" . PHP_EOL;
        }
    }

    // Получаем все активные категории новостей
    try {
        $categoryModel = new NewsCategory();
        $categories = $categoryModel->getActiveCategories();
        
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if (empty($category['id'])) continue;
                echo "    <url>" . PHP_EOL;
                echo "        <loc>https://vnesenie-v-reestr.ru/news/?category=" . htmlspecialchars($category['id']) . "</loc>" . PHP_EOL;
                echo "        <lastmod>" . date('Y-m-d') . "</lastmod>" . PHP_EOL;
                echo "        <changefreq>weekly</changefreq>" . PHP_EOL;
                echo "        <priority>0.6</priority>" . PHP_EOL;
                echo "    </url>" . PHP_EOL;
            }
        }
    } catch (Exception $e) {
        // Если категории недоступны, просто пропускаем их
        error_log("Ошибка генерации категорий в sitemap: " . $e->getMessage());
    }

} catch (Exception $e) {
    // В случае ошибки с базой данных, просто продолжаем без новостей
    error_log("Ошибка генерации sitemap: " . $e->getMessage());
}
?>

    <!-- Раздел ОКПД2 -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/okpd2/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Раздел реестра ПО -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/software-registry/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Каталог реестров и баз -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/reestry-i-bazy/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Реестры и базы (новые разделы) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/gisp-minpromtorg</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/reestr-licenziy-proizvodstvo-lekarstvennyh-sredstv</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/reestr-licenziy-vneshnyaya-torgovlya</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/reestr-krupneyshih-proizvoditeley-kts</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/reestr-rezidentov-skolkovo</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/proizvoditeli-promyshlennoy-produkcii-rf</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/perechen-uchastnikov-tax-free</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/sertifikaty-gmp</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/gosudarstvennyy-reestr-zaklyucheniy-gmp</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/svedeniya-o-vydannyh-dokumentah-srr</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Раздел калькуляторов -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Калькуляторы -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/localization/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/gisp-process/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/price-advantage/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/procurement-share/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/scoring/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/competitors/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/service-cost/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/revenue-impact/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/product-type/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/pre-audit/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/registries-compare/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/tax-risk/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/modernization-roi/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/tenders-monitor/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/calculators/documents-checklist/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Страницы грантов -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/start</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/razvitie</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/commertsializatsia</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/granti-v-ramkah-767-pprf</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/granti-v-ramkah-550-555-pprf</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Дополнительные страницы: прайс и документы -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/price-list</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/ekspluatatsionnaya-dokumentatsiya</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

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
    <url>
        <loc>https://vnesenie-v-reestr.ru/doverennoe-po</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://vnesenie-v-reestr.ru/navigator-mer-podderzhki-gisp</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
</urlset>
<?php
// Гарантируем закрытие XML даже при ошибках
if (ob_get_level()) {
    ob_end_flush();
}
?>