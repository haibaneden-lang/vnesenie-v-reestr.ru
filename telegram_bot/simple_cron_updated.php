<?php
/**
 * Веб-cron для автозапуска с контактными данными
 * Файл: simple_cron_updated.php
 */

// Простая защита
$secret = 'news_bot_contacts_2025'; // Измените на свой ключ
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    die('Access denied');
}

// Минимальные настройки
set_time_limit(30);
ini_set('memory_limit', '64M');

// Устанавливаем заголовки
header('Content-Type: text/plain; charset=utf-8');

echo "🤖 Запуск автопубликации с контактами через веб-cron\n";
echo "===================================================\n";
echo "Время: " . date('d.m.Y H:i:s') . "\n";
echo "Сайт: https://vnesenie-v-reestr.ru\n";
echo "Канал: @reestr_garant\n\n";

// Включаем автопубликацию с контактами
$_GET['mode'] = 'cron';
include 'auto_publish_updated.php';

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔗 Для настройки веб-cron используйте URL:\n";
echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n\n";
echo "📞 Контакты добавлены в каждый пост:\n";
echo "+7 920-898-17-18\n";
echo "reestrgarant@mail.ru\n\n";
echo "✅ Готово!";
?>