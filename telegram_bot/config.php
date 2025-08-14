<?php
/**
 * Конфигурация для автоматической публикации новостей в Telegram
 * Файл: config.php
 */

// Настройки Telegram бота
define('TELEGRAM_BOT_TOKEN', '7739849524:AAFpk9zQZ27LV_sw-NQt1D1vlUDlJhHLdCs');
define('TELEGRAM_CHAT_ID', '-1002836639801'); // ID канала @reestr_garant
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/');

// Настройки сайта
define('SITE_URL', 'https://vnesenie-v-reestr.ru');
define('NEWS_RSS_URL', SITE_URL . '/news/rss.php'); // RSS лента новостей
define('NEWS_JSON_URL', SITE_URL . '/news/api.php'); // JSON API новостей

// Настройки базы данных (SQLite для простоты)
define('DB_FILE', __DIR__ . '/published_news.db');

// Настройки парсинга
define('CHECK_INTERVAL_MINUTES', 30); // Проверка каждые 30 минут
define('MAX_NEWS_PER_CHECK', 5); // Максимум новостей за одну проверку
define('NEWS_EXCERPT_LENGTH', 200); // Длина анонса новости

// Настройки сообщений
define('MESSAGE_TEMPLATE', "🔔 *Новая статья на vnesenie-v-reestr.ru*\n\n📝 *{title}*\n\n{excerpt}\n\n👆 [Читать полностью]({link})\n\n#реестр #минпромторг #новости");

// Логирование
define('LOG_FILE', __DIR__ . '/telegram_bot.log');
define('DEBUG_MODE', true); // Включить для отладки

// Временная зона
date_default_timezone_set('Europe/Moscow');

?>