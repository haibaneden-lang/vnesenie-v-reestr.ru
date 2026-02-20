<?php
/**
 * Карта основных страниц сайта (без городов и новостей)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('max_execution_time', 30);

register_shutdown_function(function() {
    if (!headers_sent()) {
        $output = ob_get_contents();
        if ($output && strpos($output, '</urlset>') === false) {
            echo "</urlset>" . PHP_EOL;
        }
    }
});

header('Content-Type: application/xml; charset=utf-8');
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
        <loc>https://vnesenie-v-reestr.ru/paketnoe-predlozhenie-dlya-ekonomii-vremeni-i-usiliy</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/pricing</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Регистрация в РКН -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/roskomnadzor-registration</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/roskomnadzor-preparation-expanded</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/roskomnadzor-services</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Включение в реестр Минпромторга (главная страница) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/vkljuchenie-v-reestr-minpromtorga/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Основные разделы мультигео (без городов) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-utilizatorov/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/status-rezidenta-skolkovo/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/vklyuchenie-v-reestr-mtk/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/akkreditatsiya-it-kompaniy/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/tendernoe-soprovozhdenie/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/vnesenie-v-reestr-turoperatorov</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Раздел новостей (главная страница) -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/news/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

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

    <!-- Реестры и базы -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/gisp-minpromtorg</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://vnesenie-v-reestr.ru/navigator-mer-podderzhki-gisp</loc>
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

    <!-- Страницы займов -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/vysokotekh</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/promyshlennaya-ipoteka</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/programma-1764-minekonomrazvitiya</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/programma-proekty-razvitiya</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/programma-psk-1764</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/selkhoz-zaymy</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Страницы документов -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/tehnologicheskaya-dokumentatsiya</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/akt-ekspertizy-tpp</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/razrabotka-konstruktorskoy-dokumentatsii</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Дополнительные страницы -->
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

    <!-- Контакты и информационные страницы -->
    <url>
        <loc>https://vnesenie-v-reestr.ru/contacts</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>https://vnesenie-v-reestr.ru/about</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

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
</urlset>
